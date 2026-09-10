<?php

namespace App\Services;

use App\Business;
use App\Contact;
use App\Events\SellCreatedOrModified;
use App\Events\TableFloorRealtime;
use App\Restaurant\Booking;
use App\Restaurant\ResTable;
use App\Transaction;
use App\User;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Variation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TableOrderService
{
    const STATUS_AVAILABLE = 0;

    const STATUS_RESERVED = 1;

    const STATUS_OCCUPIED = 2;

    const SOURCE_LOCAL = 'local';

    const CUSTOM_FIELD_TABLE_ORDER = 'table_order';

    protected $transactionUtil;

    protected $productUtil;

    public function __construct(TransactionUtil $transactionUtil, ProductUtil $productUtil)
    {
        $this->transactionUtil = $transactionUtil;
        $this->productUtil = $productUtil;
    }

    public function statusLabels()
    {
        return [
            self::STATUS_AVAILABLE => 'available',
            self::STATUS_RESERVED => 'reserved',
            self::STATUS_OCCUPIED => 'notAvailable',
        ];
    }

    public function openOrderQuery($business_id, $table_id = null)
    {
        $query = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->whereNotNull('res_table_id')
            ->whereIn('status', ['final', 'draft'])
            ->where(function ($q) {
                $q->whereNull('res_order_status')
                    ->orWhereNotIn('res_order_status', ['served', 'cancelled']);
            });

        if (! empty($table_id)) {
            $query->where('res_table_id', $table_id);
        }

        return $query;
    }

    public function getOpenOrder($business_id, $table_id)
    {
        return $this->openOrderQuery($business_id, $table_id)
            ->with(['sell_lines.product', 'sell_lines.variations', 'service_staff', 'contact'])
            ->orderByDesc('id')
            ->first();
    }

    public function getTable($business_id, $table_id, array $scope = [])
    {
        $query = ResTable::where('business_id', $business_id)->where('id', $table_id);

        if (! empty($scope['location_id']) || ! empty($scope['establishment_id'])) {
            $location_id = $scope['location_id'] ?? $scope['establishment_id'];
            $query->where('location_id', $location_id);
        }

        if (! empty($scope['floor_id'])) {
            $query->where('floor_id', $scope['floor_id']);
        }

        $table = $query->first();
        if (! $table) {
            throw new \Exception(__('restaurant.table_not_in_scope'));
        }

        $this->assertLocationAccess($table->location_id);

        return $table;
    }

    public function applyBusinessScope($query, $business_id, $location_id = null, $floor_id = null)
    {
        $query->where('business_id', $business_id);

        $location_id = $location_id ?: null;
        if (! empty($location_id)) {
            $this->assertLocationAccess($location_id);
            $query->where('location_id', $location_id);
        } else {
            $permitted = $this->permittedLocationIds();
            if ($permitted !== 'all') {
                $query->whereIn('location_id', $permitted ?: [0]);
            }
        }

        if (! empty($floor_id)) {
            $query->where('floor_id', $floor_id);
        }

        return $query;
    }

    public function effectiveStatus(ResTable $table, $openOrder = null)
    {
        if (! empty($openOrder)) {
            return self::STATUS_OCCUPIED;
        }

        if ($this->hasStatusColumn()) {
            return (int) ($table->status ?? self::STATUS_AVAILABLE);
        }

        return self::STATUS_AVAILABLE;
    }

    public function occupyTable(ResTable $table, $waiter_id, Transaction $order)
    {
        if (! $this->hasStatusColumn()) {
            return $table;
        }

        $table->status = self::STATUS_OCCUPIED;
        $table->assigned_waiter_id = $waiter_id;
        $this->clearReservationFields($table);
        $table->save();

        return $table;
    }

    public function releaseTable(ResTable $table)
    {
        if (! $this->hasStatusColumn()) {
            return $table;
        }

        $table->status = self::STATUS_AVAILABLE;
        $table->assigned_waiter_id = null;
        $this->clearReservationFields($table);
        $table->save();

        return $table;
    }

    public function releaseIfNoOpenOrder($business_id, $table_id, $event = 'table:updated')
    {
        if (empty($table_id)) {
            return;
        }

        $open = $this->getOpenOrder($business_id, $table_id);
        if ($open) {
            return;
        }

        $table = ResTable::where('business_id', $business_id)->find($table_id);
        if (! $table) {
            return;
        }

        $this->releaseTable($table);
        $this->broadcast($business_id, $event, [
            'table_id' => (int) $table_id,
            'status' => self::STATUS_AVAILABLE,
        ]);
    }

    public function syncFromSale(Transaction $transaction)
    {
        if ($transaction->type !== 'sell' || empty($transaction->res_table_id)) {
            return;
        }

        $table = ResTable::find($transaction->res_table_id);
        if (! $table) {
            return;
        }

        $closed = in_array($transaction->res_order_status, ['served', 'cancelled'], true);

        if ($closed) {
            $this->releaseIfNoOpenOrder($transaction->business_id, $table->id);

            return;
        }

        $waiter_id = $transaction->res_waiter_id ?: $transaction->created_by;
        $this->occupyTable($table, $waiter_id, $transaction);
        $this->broadcast($transaction->business_id, 'table:updated', [
            'table_id' => (int) $table->id,
            'order_id' => (int) $transaction->id,
            'status' => self::STATUS_OCCUPIED,
        ]);
    }

    public function changeStatus($business_id, $table_id, $status, $user_id, array $payload = [])
    {
        $status = (int) $status;
        $table = $this->getTable($business_id, $table_id, $payload);
        $open = $this->getOpenOrder($business_id, $table_id);

        if ($open) {
            throw new \Exception(__('restaurant.cannot_change_status_with_open_order'));
        }

        if (! in_array($status, [self::STATUS_AVAILABLE, self::STATUS_RESERVED, self::STATUS_OCCUPIED], true)) {
            throw new \Exception(__('messages.something_went_wrong'));
        }

        if ($this->hasStatusColumn()) {
            $table->status = $status;
            if ($status === self::STATUS_AVAILABLE) {
                $table->assigned_waiter_id = null;
                $this->clearReservationFields($table);
            } elseif ($status === self::STATUS_RESERVED) {
                $guest = $this->reservationGuestFromPayload($payload);
                if (empty($guest['name'])) {
                    throw new \Exception(__('restaurant.reservation_guest_required'));
                }
                $this->applyReservationFields($table, $guest);
            } elseif ($status === self::STATUS_OCCUPIED) {
                $this->clearReservationFields($table);
            }
            $table->save();
        }

        $this->broadcast($business_id, 'table:updated', [
            'table_id' => (int) $table->id,
            'status' => $status,
            'updated_by' => (int) $user_id,
            'location_id' => (int) $table->location_id,
            'floor_id' => $table->floor_id ? (int) $table->floor_id : null,
        ]);

        return $this->formatTable($table->fresh(['floor']), $business_id);
    }

    public function newOrUpdateOrder($business_id, $user_id, array $payload, $from_api = false)
    {
        $table_id = $payload['table_id'] ?? $payload['res_table_id'] ?? $payload['table'] ?? null;
        if (empty($table_id)) {
            throw new \Exception(__('restaurant.select_table'));
        }

        $table = $this->getTable($business_id, $table_id, $payload);
        $payload_location = $payload['location_id'] ?? $payload['establishment_id'] ?? null;
        $payload_floor = $payload['floor_id'] ?? null;
        if (! empty($payload_location) && (int) $table->location_id !== (int) $payload_location) {
            throw new \Exception(__('restaurant.table_not_in_scope'));
        }
        if (! empty($payload_floor) && (int) $table->floor_id !== (int) $payload_floor) {
            throw new \Exception(__('restaurant.table_not_in_scope'));
        }
        $location_id = $table->location_id;

        $items = $payload['items'] ?? $payload['products'] ?? [];
        if (empty($items)) {
            throw new \Exception(__('restaurant.order_items_required'));
        }

        $products = $this->buildProductLines($business_id, $items);
        $notes = $payload['notes'] ?? $payload['sale_note'] ?? $payload['additional_notes'] ?? null;
        $staff_note = $payload['staff_note'] ?? null;

        $open = $this->getOpenOrder($business_id, $table->id);
        $created = empty($open);

        DB::beginTransaction();
        try {
            if ($open) {
                $transaction = $this->replaceOrderLines($business_id, $user_id, $open, $products, $notes, $staff_note);
                $event = 'order:updated';
            } else {
                $transaction = $this->createTableOrder($business_id, $user_id, $table, $location_id, $products, $notes, $staff_note, $from_api);
                $event = 'order:created';
            }

            $this->occupyTable($table->fresh(), $user_id, $transaction);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        SellCreatedOrModified::dispatch($transaction->fresh());
        $this->broadcast($business_id, $event, [
            'table_id' => (int) $table->id,
            'order_id' => (int) $transaction->id,
            'status' => self::STATUS_OCCUPIED,
        ]);
        $this->broadcast($business_id, 'table:updated', [
            'table_id' => (int) $table->id,
            'order_id' => (int) $transaction->id,
            'status' => self::STATUS_OCCUPIED,
        ]);

        return [
            'created' => $created,
            'order' => $this->formatOrder($transaction->fresh(['sell_lines.product', 'sell_lines.variations', 'service_staff', 'contact', 'table'])),
            'table' => $this->formatTable($table->fresh(), $business_id),
        ];
    }

    public function updateOrderById($business_id, $user_id, $id, array $payload, $from_api = false)
    {
        $order = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->find($id);

        if ($order && ! empty($order->res_table_id)) {
            $payload['table_id'] = $order->res_table_id;
            if (empty($payload['items']) && empty($payload['products'])) {
                $payload['items'] = $this->linesToPayload($order);
            }

            return $this->newOrUpdateOrder($business_id, $user_id, $payload, $from_api);
        }

        $payload['table_id'] = $payload['table_id'] ?? $id;

        return $this->newOrUpdateOrder($business_id, $user_id, $payload, $from_api);
    }

    public function serveOrder($business_id, $order_id)
    {
        $order = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->findOrFail($order_id);

        $order->res_order_status = 'served';
        $order->save();

        $order->sell_lines()->update(['res_line_order_status' => 'served']);

        if (! empty($order->res_table_id)) {
            $this->releaseIfNoOpenOrder($business_id, $order->res_table_id, 'order:updated');
        }

        $this->broadcast($business_id, 'order:updated', [
            'order_id' => (int) $order->id,
            'table_id' => $order->res_table_id ? (int) $order->res_table_id : null,
            'res_order_status' => 'served',
        ]);

        return $this->formatOrder($order->fresh(['sell_lines.product', 'sell_lines.variations', 'service_staff', 'contact', 'table']));
    }

    public function cancelOrder($business_id, $payload)
    {
        $order_id = $payload['order_id'] ?? $payload['id'] ?? null;
        $table_id = $payload['table_id'] ?? $payload['res_table_id'] ?? null;

        if (empty($order_id) && ! empty($table_id)) {
            $open = $this->getOpenOrder($business_id, $table_id);
            $order_id = $open ? $open->id : null;
        }

        if (empty($order_id)) {
            throw new \Exception(__('restaurant.no_active_order'));
        }

        $order = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->findOrFail($order_id);

        $table_id = $order->res_table_id;
        $paid = $order->payment_status === 'paid' && empty($order->is_suspend);

        if ($paid) {
            $order->res_order_status = 'cancelled';
            $order->save();
            $order->sell_lines()->update(['res_line_order_status' => 'served']);
        } else {
            $this->transactionUtil->deleteSale($business_id, $order->id);
        }

        if (! empty($table_id)) {
            $this->releaseIfNoOpenOrder($business_id, $table_id, 'order:updated');
        }

        $this->broadcast($business_id, 'order:updated', [
            'order_id' => (int) $order_id,
            'table_id' => $table_id ? (int) $table_id : null,
            'cancelled' => true,
        ]);

        return true;
    }

    public function listTables($business_id, $location_id = null, $waiter_id = null, $floor_id = null)
    {
        $query = $this->applyBusinessScope(ResTable::query()->with(['floor']), $business_id, $location_id, $floor_id);

        $tables = $query->orderBy('name')->get();
        $formatted = [];
        foreach ($tables as $table) {
            $row = $this->formatTable($table, $business_id);
            if (! empty($waiter_id) && (int) ($row['assigned_waiter_id'] ?? 0) !== (int) $waiter_id) {
                continue;
            }
            $formatted[] = $row;
        }

        return $formatted;
    }

    public function tableDetails($business_id, $table_id, array $scope = [])
    {
        $table = $this->getTable($business_id, $table_id, $scope);
        $data = $this->formatTable($table->load('floor'), $business_id, true);

        $data['active_reservation'] = $this->activeReservation($business_id, $table_id) ?: $data['reservation'];

        return $data;
    }

    public function floorState($business_id, $location_id = null, $waiter_id = null, $floor_id = null)
    {
        $tables = $this->listTables($business_id, $location_id, $waiter_id, $floor_id);
        $waiters = [];
        foreach ($tables as $table) {
            if (! empty($table['assigned_waiter_id'])) {
                $waiters[$table['assigned_waiter_id']] = $table['assigned_waiter']['name'] ?? ('#'.$table['assigned_waiter_id']);
            }
        }

        $floors = [];
        foreach ($tables as $table) {
            $key = $table['floor_id'] ?: 0;
            if (! isset($floors[$key])) {
                $floors[$key] = [
                    'id' => $table['floor_id'],
                    'name' => $table['floor_name'] ?: __('restaurant.floor'),
                    'tables' => [],
                ];
            }
            $floors[$key]['tables'][] = $table;
        }

        return [
            'tables' => $tables,
            'floors' => array_values($floors),
            'waiters' => collect($waiters)->map(function ($name, $id) {
                return ['id' => (int) $id, 'name' => $name];
            })->values()->all(),
        ];
    }

    public function searchProducts($business_id, $location_id, $term = '')
    {
        $query = Variation::join('products as p', 'variations.product_id', '=', 'p.id')
            ->leftJoin('product_variations as pv', 'variations.product_variation_id', '=', 'pv.id')
            ->where('p.business_id', $business_id)
            ->where('p.not_for_selling', 0)
            ->whereNull('p.deleted_at')
            ->whereNull('variations.deleted_at')
            ->whereIn('p.type', ['single', 'variable']);

        if ($term !== '' && $term !== null) {
            $query->where(function ($q) use ($term) {
                $q->where('p.name', 'like', '%'.$term.'%')
                    ->orWhere('p.sku', 'like', '%'.$term.'%')
                    ->orWhere('variations.sub_sku', 'like', '%'.$term.'%')
                    ->orWhere('variations.name', 'like', '%'.$term.'%');
            });
        }

        return $query->select(
            'variations.id as variation_id',
            'p.id as product_id',
            'p.name as product_name',
            'p.enable_stock',
            'p.type',
            'variations.name as variation_name',
            'variations.sub_sku',
            'variations.sell_price_inc_tax',
            'variations.default_sell_price',
            'pv.name as product_variation_name'
        )->orderBy('p.name')
            ->limit(30)
            ->get()
            ->map(function ($row) {
                $label = $row->product_name;
                if ($row->type === 'variable') {
                    $label .= ' - '.$row->product_variation_name.' - '.$row->variation_name;
                }

                return [
                    'product_id' => (int) $row->product_id,
                    'variation_id' => (int) $row->variation_id,
                    'name' => $label,
                    'sku' => $row->sub_sku,
                    'enable_stock' => (int) $row->enable_stock,
                    'unit_price' => (float) $row->default_sell_price,
                    'unit_price_inc_tax' => (float) $row->sell_price_inc_tax,
                ];
            })->values()->all();
    }

    public function formatTable(ResTable $table, $business_id, $with_lines = false)
    {
        $open = $this->getOpenOrder($business_id, $table->id);
        $status = $this->effectiveStatus($table, $open);
        $waiter = null;
        $waiter_id = $open && $open->res_waiter_id ? $open->res_waiter_id : ($table->assigned_waiter_id ?? null);
        if ($waiter_id) {
            $user = User::find($waiter_id);
            if ($user) {
                $waiter = [
                    'id' => (int) $user->id,
                    'name' => $user->user_full_name,
                ];
            }
        }

        $labels = $this->statusLabels();

        return [
            'id' => (int) $table->id,
            'business_id' => (int) $table->business_id,
            'name' => $table->name,
            'description' => $table->description,
            'establishment_id' => (int) $table->location_id,
            'location_id' => (int) $table->location_id,
            'floor_id' => $table->floor_id ? (int) $table->floor_id : null,
            'floor_name' => $table->floor ? $table->floor->name : null,
            'capacity' => $table->seats ? (int) $table->seats : null,
            'seats' => $table->seats ? (int) $table->seats : null,
            'status' => $status,
            'status_label' => $labels[$status] ?? 'available',
            'assigned_waiter_id' => $waiter_id ? (int) $waiter_id : null,
            'assigned_waiter' => $waiter,
            'reservation' => $this->formatReservation($table, $status),
            'active_order' => $open ? $this->formatOrder($open, $with_lines) : null,
        ];
    }

    public function formatOrder(Transaction $order, $with_lines = true)
    {
        $lines = [];
        if ($with_lines) {
            foreach ($order->sell_lines as $line) {
                if (! empty($line->parent_sell_line_id)) {
                    continue;
                }
                $name = $line->product->name ?? '';
                if (! empty($line->variations) && $line->variations->name && $line->variations->name !== 'DUMMY') {
                    $name .= ' - '.$line->variations->name;
                }
                $lines[] = [
                    'id' => (int) $line->id,
                    'product_id' => (int) $line->product_id,
                    'variation_id' => (int) $line->variation_id,
                    'name' => $name,
                    'quantity' => (float) $line->quantity,
                    'unit_price_inc_tax' => (float) $line->unit_price_inc_tax,
                    'line_total' => (float) $line->quantity * (float) $line->unit_price_inc_tax,
                    'sell_line_note' => $line->sell_line_note,
                ];
            }
        }

        return [
            'id' => (int) $order->id,
            'invoice_no' => $order->invoice_no,
            'source' => $order->source,
            'is_internal_table_order' => $order->source === self::SOURCE_LOCAL && ! empty($order->res_table_id),
            'custom_field_1' => $order->custom_field_1,
            'is_suspend' => (int) $order->is_suspend,
            'is_kitchen_order' => (int) $order->is_kitchen_order,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'res_order_status' => $order->res_order_status,
            'res_table_id' => $order->res_table_id ? (int) $order->res_table_id : null,
            'res_waiter_id' => $order->res_waiter_id ? (int) $order->res_waiter_id : null,
            'waiter_name' => $order->service_staff->user_full_name ?? null,
            'notes' => $order->additional_notes,
            'final_total' => (float) $order->final_total,
            'items_count' => count($lines) ?: $order->sell_lines->whereNull('parent_sell_line_id')->count(),
            'items' => $lines,
            'created_at' => optional($order->transaction_date)->toDateTimeString(),
        ];
    }

    protected function createTableOrder($business_id, $user_id, ResTable $table, $location_id, array $products, $notes, $staff_note, $from_api)
    {
        $contact = $this->walkInCustomer($business_id);
        $invoice_total = $this->productUtil->calculateInvoiceTotal($products, null, null, false);
        $final_total = $invoice_total['final_total'] ?? (($invoice_total['total_before_tax'] ?? 0) + ($invoice_total['tax'] ?? 0));

        $input = [
            'location_id' => $location_id,
            'contact_id' => $contact->id,
            'customer_group_id' => $contact->customer_group_id,
            'status' => 'final',
            'transaction_date' => Carbon::now()->toDateTimeString(),
            'final_total' => $final_total,
            'discount_type' => 'fixed',
            'discount_amount' => 0,
            'tax_rate_id' => null,
            'sale_note' => $notes,
            'staff_note' => $staff_note,
            'source' => self::SOURCE_LOCAL,
            'custom_field_1' => self::CUSTOM_FIELD_TABLE_ORDER,
            'is_suspend' => 1,
            'is_kitchen_order' => 1,
            'res_table_id' => $table->id,
            'res_waiter_id' => $user_id,
            'is_created_from_api' => $from_api ? 1 : 0,
            'commission_agent' => null,
        ];

        $transaction = $this->transactionUtil->createSellTransaction($business_id, $input, $invoice_total, $user_id, false);
        $transaction->res_order_status = 'received';
        $transaction->payment_status = 'due';
        $transaction->save();

        $this->transactionUtil->createOrUpdateSellLines($transaction, $products, $location_id, false, null, [], false);

        foreach ($products as $product) {
            if (! empty($product['enable_stock'])) {
                $this->productUtil->decreaseProductQuantity(
                    $product['product_id'],
                    $product['variation_id'],
                    $location_id,
                    $product['quantity']
                );
            }
        }

        $business = Business::find($business_id);
        $this->transactionUtil->mapPurchaseSell([
            'id' => $business_id,
            'accounting_method' => $business->accounting_method,
            'location_id' => $location_id,
        ], $transaction->sell_lines, 'purchase');

        return $transaction;
    }

    protected function replaceOrderLines($business_id, $user_id, Transaction $transaction, array $products, $notes, $staff_note)
    {
        $status_before = $transaction->status;
        $invoice_total = $this->productUtil->calculateInvoiceTotal($products, null, null, false);
        $final_total = $invoice_total['final_total'] ?? (($invoice_total['total_before_tax'] ?? 0) + ($invoice_total['tax'] ?? 0));

        $input = [
            'location_id' => $transaction->location_id,
            'contact_id' => $transaction->contact_id,
            'customer_group_id' => $transaction->customer_group_id,
            'status' => $transaction->status,
            'final_total' => $final_total,
            'discount_type' => $transaction->discount_type ?: 'fixed',
            'discount_amount' => $transaction->discount_amount ?: 0,
            'tax_rate_id' => $transaction->tax_id,
            'sale_note' => $notes !== null ? $notes : $transaction->additional_notes,
            'staff_note' => $staff_note !== null ? $staff_note : $transaction->staff_note,
            'source' => self::SOURCE_LOCAL,
            'custom_field_1' => self::CUSTOM_FIELD_TABLE_ORDER,
            'is_suspend' => 1,
            'is_kitchen_order' => 1,
            'res_table_id' => $transaction->res_table_id,
            'res_waiter_id' => $user_id,
            'commission_agent' => $transaction->commission_agent,
        ];

        $transaction = $this->transactionUtil->updateSellTransaction($transaction, $business_id, $input, $invoice_total, $user_id, false, false);
        $transaction->res_waiter_id = $user_id;
        $transaction->source = self::SOURCE_LOCAL;
        $transaction->custom_field_1 = self::CUSTOM_FIELD_TABLE_ORDER;
        $transaction->is_kitchen_order = 1;
        if (empty($transaction->res_order_status) || $transaction->res_order_status === 'served') {
            $transaction->res_order_status = 'received';
        }
        $transaction->save();

        $deleted_lines = $this->transactionUtil->createOrUpdateSellLines($transaction, $products, $transaction->location_id, true, $status_before, [], false);
        $this->productUtil->adjustProductStockForInvoice($status_before, $transaction, [
            'products' => $products,
            'location_id' => $transaction->location_id,
        ], false);

        $business = Business::find($business_id);
        $this->transactionUtil->adjustMappingPurchaseSell($status_before, $transaction, [
            'id' => $business_id,
            'accounting_method' => $business->accounting_method,
            'location_id' => $transaction->location_id,
        ], $deleted_lines);

        return $transaction->fresh(['sell_lines']);
    }

    protected function buildProductLines($business_id, array $items)
    {
        $products = [];
        foreach ($items as $item) {
            $variation_id = $item['variation_id'] ?? $item['id'] ?? null;
            if (empty($variation_id)) {
                continue;
            }

            $variation = Variation::with('product')
                ->whereHas('product', function ($q) use ($business_id) {
                    $q->where('business_id', $business_id);
                })
                ->find($variation_id);

            if (! $variation || ! $variation->product) {
                continue;
            }

            $qty = (float) ($item['quantity'] ?? $item['qty'] ?? 1);
            if ($qty <= 0) {
                continue;
            }

            $unit_inc = isset($item['unit_price_inc_tax']) ? (float) $item['unit_price_inc_tax'] : (float) $variation->sell_price_inc_tax;
            $unit = isset($item['unit_price']) ? (float) $item['unit_price'] : (float) $variation->default_sell_price;
            if ($unit <= 0) {
                $unit = $unit_inc;
            }

            $line = [
                'product_id' => $variation->product_id,
                'variation_id' => $variation->id,
                'quantity' => $qty,
                'unit_price' => $unit,
                'unit_price_inc_tax' => $unit_inc,
                'item_tax' => max(0, $unit_inc - $unit),
                'tax_id' => $variation->product->tax ?? null,
                'enable_stock' => $variation->product->enable_stock,
                'sell_line_note' => $item['sell_line_note'] ?? $item['note'] ?? null,
                'line_discount_type' => 'fixed',
                'line_discount_amount' => 0,
            ];

            $modifiers = $item['modifiers'] ?? $item['modifier'] ?? [];
            if (! empty($modifiers) && is_array($modifiers)) {
                foreach ($modifiers as $mod) {
                    $mod_var = $mod['variation_id'] ?? $mod['id'] ?? $mod;
                    if (! is_array($mod)) {
                        $mod = ['variation_id' => $mod_var];
                    }
                    $mvar = Variation::with('product')->find($mod_var);
                    if (! $mvar) {
                        continue;
                    }
                    $line['modifier'][] = $mvar->id;
                    $line['modifier_set_id'][] = $mvar->product_id;
                    $line['modifier_price'][] = $mod['unit_price'] ?? $mvar->sell_price_inc_tax;
                    $line['modifier_quantity'][] = $mod['quantity'] ?? 1;
                }
            }

            $products[] = $line;
        }

        if (empty($products)) {
            throw new \Exception(__('restaurant.order_items_required'));
        }

        return $products;
    }

    protected function linesToPayload(Transaction $order)
    {
        $items = [];
        foreach ($order->sell_lines as $line) {
            if (! empty($line->parent_sell_line_id)) {
                continue;
            }
            $items[] = [
                'variation_id' => $line->variation_id,
                'quantity' => $line->quantity,
                'unit_price' => $line->unit_price,
                'unit_price_inc_tax' => $line->unit_price_inc_tax,
            ];
        }

        return $items;
    }

    protected function walkInCustomer($business_id)
    {
        $contact = Contact::where('business_id', $business_id)
            ->where('is_default', 1)
            ->first();

        if (! $contact) {
            $contact = Contact::where('business_id', $business_id)
                ->whereIn('type', ['customer', 'both'])
                ->orderBy('id')
                ->first();
        }

        if (! $contact) {
            throw new \Exception(__('lang_v1.no_customer_selected'));
        }

        return $contact;
    }

    protected function activeReservation($business_id, $table_id)
    {
        $now = Carbon::now();

        return Booking::where('business_id', $business_id)
            ->where('table_id', $table_id)
            ->whereIn('booking_status', ['booked', 'waiting'])
            ->where('booking_start', '<=', $now)
            ->where('booking_end', '>=', $now)
            ->with('customer')
            ->first();
    }

    protected function formatReservation(ResTable $table, $status)
    {
        if ((int) $status !== self::STATUS_RESERVED || ! $this->hasReservationColumns()) {
            return null;
        }

        if (empty($table->reserved_guest_name)) {
            return null;
        }

        return [
            'guest_name' => $table->reserved_guest_name,
            'guest_phone' => $table->reserved_guest_phone,
            'note' => $table->reserved_note,
        ];
    }

    protected function reservationGuestFromPayload(array $payload)
    {
        $name = trim((string) (
            $payload['reserved_guest_name']
            ?? $payload['guest_name']
            ?? $payload['customer_name']
            ?? ''
        ));
        $phone = trim((string) (
            $payload['reserved_guest_phone']
            ?? $payload['guest_phone']
            ?? $payload['phone']
            ?? $payload['mobile']
            ?? ''
        ));
        $note = trim((string) ($payload['reserved_note'] ?? $payload['note'] ?? $payload['booking_note'] ?? ''));

        return [
            'name' => $name,
            'phone' => $phone !== '' ? $phone : null,
            'note' => $note !== '' ? $note : null,
        ];
    }

    protected function applyReservationFields(ResTable $table, array $guest)
    {
        if (! $this->hasReservationColumns()) {
            return;
        }

        $table->reserved_guest_name = $guest['name'];
        $table->reserved_guest_phone = $guest['phone'];
        $table->reserved_note = $guest['note'];
    }

    protected function clearReservationFields(ResTable $table)
    {
        if (! $this->hasReservationColumns()) {
            return;
        }

        $table->reserved_guest_name = null;
        $table->reserved_guest_phone = null;
        $table->reserved_note = null;
    }

    protected function hasReservationColumns()
    {
        static $cached;
        if ($cached === null) {
            $cached = Schema::hasColumn('res_tables', 'reserved_guest_name');
        }

        return $cached;
    }

    protected function permittedLocationIds()
    {
        $user = auth()->user();
        if (! $user) {
            return 'all';
        }

        return $user->permitted_locations($user->business_id);
    }

    protected function assertLocationAccess($location_id)
    {
        $permitted = $this->permittedLocationIds();
        if ($permitted === 'all') {
            return;
        }

        if (! in_array((int) $location_id, array_map('intval', (array) $permitted), true)) {
            throw new \Exception(__('restaurant.table_not_in_scope'));
        }
    }

    protected function hasStatusColumn()
    {
        static $cached;
        if ($cached === null) {
            $cached = Schema::hasColumn('res_tables', 'status');
        }

        return $cached;
    }

    protected function broadcast($business_id, $event, array $payload = [])
    {
        try {
            event(new TableFloorRealtime($business_id, $event, $payload));
        } catch (\Throwable $e) {
            // Broadcasting is optional; polling still keeps the floor in sync.
        }
    }
}
