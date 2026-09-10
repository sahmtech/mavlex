<?php

namespace Modules\Connector\Http\Controllers\Api;

use App\Services\TableOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WaiterTableController extends ApiController
{
    protected $tableOrders;

    public function __construct(TableOrderService $tableOrders)
    {
        parent::__construct();
        $this->tableOrders = $tableOrders;
    }

    public function getTables(Request $request)
    {
        try {
            $user = Auth::user();
            $location_id = $request->get('establishment_id', $request->get('location_id'));
            $waiter_id = $request->get('waiter_id');
            $floor_id = $request->get('floor_id');

            return $this->respond([
                'success' => true,
                'data' => $this->tableOrders->listTables($user->business_id, $location_id, $waiter_id, $floor_id),
            ]);
        } catch (\Exception $e) {
            return $this->otherExceptions($e);
        }
    }

    public function show($id)
    {
        try {
            $user = Auth::user();

            return $this->respond([
                'success' => true,
                'data' => $this->tableOrders->tableDetails($user->business_id, $id, request()->only(['location_id', 'establishment_id', 'floor_id'])),
            ]);
        } catch (\Exception $e) {
            return $this->otherExceptions($e);
        }
    }

    public function changeStatus(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $status = $request->input('status', $request->input('table_status'));
            $table = $this->tableOrders->changeStatus($user->business_id, $id, $status, $user->id, $request->all());

            return $this->respond([
                'success' => true,
                'data' => $table,
            ]);
        } catch (\Exception $e) {
            return $this->otherExceptions($e);
        }
    }

    public function newOrder(Request $request)
    {
        try {
            $user = Auth::user();
            $result = $this->tableOrders->newOrUpdateOrder($user->business_id, $user->id, $request->all(), true);

            return $this->respond([
                'success' => true,
                'created' => $result['created'],
                'data' => $result['order'],
                'table' => $result['table'],
            ]);
        } catch (\Exception $e) {
            return $this->otherExceptions($e);
        }
    }

    public function updateOrder(Request $request, $id)
    {
        try {
            $user = Auth::user();
            if (in_array($request->input('status'), ['served', 'completed'], true) || $request->input('res_order_status') === 'served') {
                $order = $this->tableOrders->serveOrder($user->business_id, $id);

                return $this->respond([
                    'success' => true,
                    'data' => $order,
                ]);
            }
            $result = $this->tableOrders->updateOrderById($user->business_id, $user->id, $id, $request->all(), true);

            return $this->respond([
                'success' => true,
                'data' => $result['order'],
                'table' => $result['table'],
            ]);
        } catch (\Exception $e) {
            return $this->otherExceptions($e);
        }
    }

    public function cancelOrder(Request $request)
    {
        try {
            $user = Auth::user();
            $this->tableOrders->cancelOrder($user->business_id, $request->all());

            return $this->respond([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            return $this->otherExceptions($e);
        }
    }
}
