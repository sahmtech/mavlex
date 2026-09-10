<?php

namespace App\Http\Controllers\Restaurant;

use App\BusinessLocation;
use App\Restaurant\ResFloor;
use App\Restaurant\ResTable;
use App\Services\TableOrderService;
use Datatables;
use DNS2D;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Schema;

class TableController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        if (! auth()->user()->can('access_tables')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $select = [
                'res_tables.name as name',
                'BL.name as location',
                'RF.name as floor_name',
                'res_tables.seats',
                'res_tables.description',
                'res_tables.id',
            ];

            if (Schema::hasColumn('res_tables', 'status')) {
                $select[] = 'res_tables.status';
            }
            if (Schema::hasColumn('res_tables', 'reserved_guest_name')) {
                $select[] = 'res_tables.reserved_guest_name';
                $select[] = 'res_tables.reserved_guest_phone';
            }

            $tables = ResTable::where('res_tables.business_id', $business_id)
                        ->join('business_locations AS BL', 'res_tables.location_id', '=', 'BL.id')
                        ->leftJoin('res_floors AS RF', 'res_tables.floor_id', '=', 'RF.id')
                        ->select($select)
                        ->selectRaw('(SELECT COUNT(*) FROM transactions
                            WHERE transactions.res_table_id = res_tables.id
                            AND transactions.type = "sell"
                            AND transactions.status IN ("final", "draft")
                            AND (transactions.res_order_status IS NULL OR transactions.res_order_status NOT IN ("served", "cancelled"))
                        ) as open_order_count');

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $tables->whereIn('res_tables.location_id', $permitted_locations);
            }

            return Datatables::of($tables)
                ->editColumn('floor_name', function ($row) {
                    return $row->floor_name ?: '—';
                })
                ->editColumn('seats', function ($row) {
                    if (empty($row->seats)) {
                        return '—';
                    }

                    return '<span class="rt-seat-pill">'.$row->seats.'</span>';
                })
                ->addColumn('status', function ($row) {
                    $locked = ! empty($row->open_order_count);
                    $status = $locked ? 2 : (int) ($row->status ?? 0);
                    $disabled = $locked ? 'disabled' : '';
                    $title = $locked ? e(__('restaurant.cannot_change_status_with_open_order')) : '';
                    $guestName = e($row->reserved_guest_name ?? '');
                    $guestPhone = e($row->reserved_guest_phone ?? '');

                    $html = '<select class="rt-status-select" data-id="'.$row->id.'" data-prev="'.$status.'" data-guest-name="'.$guestName.'" data-guest-phone="'.$guestPhone.'" title="'.$title.'" '.$disabled.'>
                        <option value="0"'.($status === 0 ? ' selected' : '').'>'.e(__('restaurant.available')).' (0)</option>
                        <option value="1"'.($status === 1 ? ' selected' : '').'>'.e(__('restaurant.reserved')).' (1)</option>
                        <option value="2"'.($status === 2 ? ' selected' : '').'>'.e(__('restaurant.occupied')).' (2)</option>
                    </select>';
                    if ($status === 1 && $guestName !== '') {
                        $html .= '<div class="rt-guest-line">'.$guestName.($guestPhone !== '' ? ' · '.$guestPhone : '').'</div>';
                    }

                    return $html;
                })
                ->addColumn(
                    'action',
                    '<div class="rt-actions">
                    @role("Admin#'.$business_id.'")
                    <button type="button" data-href="{{action(\'App\Http\Controllers\Restaurant\TableController@edit\', [$id])}}" class="rt-badge rt-badge-edit edit_table_button"><i class="fa fa-edit"></i> @lang("messages.edit")</button>
                    <button type="button" data-href="{{action(\'App\Http\Controllers\Restaurant\TableController@destroy\', [$id])}}" class="rt-badge rt-badge-delete delete_table_button"><i class="fa fa-trash"></i> @lang("messages.delete")</button>
                    @endrole
                    <button type="button" data-href="{{action(\'App\Http\Controllers\Restaurant\TableController@qr\', [$id])}}" class="rt-badge rt-badge-qr qr_table_button"><i class="fa fa-qrcode"></i> QR</button>
                    </div>'
                )
                ->removeColumn('id')
                ->rawColumns(['action', 'seats', 'status'])
                ->make(true);
        }

        $business_id = request()->session()->get('user.business_id');
        $business_locations = BusinessLocation::forDropdown($business_id);

        return view('restaurant.table.index')
            ->with(compact('business_locations'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        if (! auth()->user()->can('access_tables')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $business_locations = BusinessLocation::forDropdown($business_id);

        return view('restaurant.table.create')
            ->with(compact('business_locations'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('access_tables')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:191',
            'location_id' => 'required|integer',
            'floor_id' => 'nullable|integer',
            'seats' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'status' => 'nullable|integer|in:0,1,2',
        ]);

        try {
            $input = $request->only(['name', 'description', 'location_id', 'floor_id', 'seats', 'status']);
            $business_id = $request->session()->get('user.business_id');
            $input['business_id'] = $business_id;
            $input['created_by'] = $request->session()->get('user.id');
            $input['floor_id'] = $this->validatedFloorId($business_id, $input['location_id'], $input['floor_id'] ?? null);
            $input['seats'] = $request->filled('seats') ? (int) $request->input('seats') : null;
            if (Schema::hasColumn('res_tables', 'status')) {
                $input['status'] = (int) ($request->input('status', 0));
            } else {
                unset($input['status']);
            }

            if (Schema::hasColumn('res_tables', 'reserved_guest_name')) {
                if ((int) ($input['status'] ?? 0) === 1) {
                    $guest_name = trim((string) $request->input('reserved_guest_name'));
                    if ($guest_name === '') {
                        return [
                            'success' => false,
                            'msg' => __('restaurant.reservation_guest_required'),
                        ];
                    }
                    $input['reserved_guest_name'] = $guest_name;
                    $input['reserved_guest_phone'] = $request->input('reserved_guest_phone') ?: null;
                    $input['reserved_note'] = $request->input('reserved_note') ?: null;
                } else {
                    $input['reserved_guest_name'] = null;
                    $input['reserved_guest_phone'] = null;
                    $input['reserved_note'] = null;
                }
            }

            $table = ResTable::create($input);
            $output = ['success' => true,
                'data' => $table,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    /**
     * Show the specified resource.
     *
     * @return Response
     */
    public function show()
    {
        if (! auth()->user()->can('access_tables')) {
            abort(403, 'Unauthorized action.');
        }

        return view('restaurant.table.show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit($id)
    {
        if (! auth()->user()->can('access_tables')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $table = ResTable::where('business_id', $business_id)->find($id);
            $business_locations = BusinessLocation::forDropdown($business_id);
            $floors = ResFloor::where('business_id', $business_id)
                ->where('location_id', $table->location_id)
                ->pluck('name', 'id');
            $has_open_order = ! empty(app(TableOrderService::class)->getOpenOrder($business_id, $table->id));

            return view('restaurant.table.edit')
                ->with(compact('table', 'business_locations', 'floors', 'has_open_order'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update(Request $request, $id)
    {
        if (! auth()->user()->can('access_tables')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $request->validate([
                'name' => 'required|string|max:191',
                'location_id' => 'required|integer',
                'floor_id' => 'nullable|integer',
                'seats' => 'nullable|integer|min:1',
                'description' => 'nullable|string',
                'status' => 'nullable|integer|in:0,1,2',
            ]);

            try {
                $input = $request->only(['name', 'description', 'location_id', 'floor_id', 'seats']);
                $business_id = $request->session()->get('user.business_id');

                $table = ResTable::where('business_id', $business_id)->findOrFail($id);
                $table->name = $input['name'];
                $table->description = $input['description'] ?? null;
                $table->location_id = $input['location_id'];
                $table->seats = $request->filled('seats') ? (int) $request->input('seats') : null;
                $table->floor_id = $this->validatedFloorId($business_id, $input['location_id'], $input['floor_id'] ?? null);
                if (Schema::hasColumn('res_tables', 'status') && $request->has('status')) {
                    $open = app(TableOrderService::class)->getOpenOrder($business_id, $table->id);
                    if ($open) {
                        return [
                            'success' => false,
                            'msg' => __('restaurant.cannot_change_status_with_open_order'),
                        ];
                    }
                    $table->status = (int) $request->input('status');
                    if ((int) $table->status === 0) {
                        $table->assigned_waiter_id = null;
                    }
                    if (Schema::hasColumn('res_tables', 'reserved_guest_name')) {
                        if ((int) $table->status === 1) {
                            $guest_name = trim((string) $request->input('reserved_guest_name'));
                            if ($guest_name === '') {
                                return [
                                    'success' => false,
                                    'msg' => __('restaurant.reservation_guest_required'),
                                ];
                            }
                            $table->reserved_guest_name = $guest_name;
                            $table->reserved_guest_phone = $request->input('reserved_guest_phone') ?: null;
                            $table->reserved_note = $request->input('reserved_note') ?: null;
                        } else {
                            $table->reserved_guest_name = null;
                            $table->reserved_guest_phone = null;
                            $table->reserved_note = null;
                        }
                    }
                }
                $table->save();

                $output = ['success' => true,
                    'msg' => __('lang_v1.updated_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output = ['success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy($id)
    {
        if (! auth()->user()->can('access_tables')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->user()->business_id;

                $table = ResTable::where('business_id', $business_id)->findOrFail($id);
                $table->delete();

                $output = ['success' => true,
                    'msg' => __('lang_v1.deleted_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output = ['success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * QR preview modal for a table.
     */
    public function qr($id)
    {
        if (! auth()->user()->can('access_tables')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $table = ResTable::where('business_id', $business_id)
            ->with(['floor'])
            ->findOrFail($id);

        $payload = $this->tableQrPayload($table);
        $qr_png = DNS2D::getBarcodePNG($payload, 'QRCODE', 8, 8, [15, 23, 42]);

        return view('restaurant.table.qr')
            ->with(compact('table', 'qr_png', 'payload'));
    }

    /**
     * Download table QR as PNG.
     */
    public function qrDownload($id)
    {
        if (! auth()->user()->can('access_tables')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $table = ResTable::where('business_id', $business_id)->findOrFail($id);
        $payload = $this->tableQrPayload($table);
        $png = base64_decode(DNS2D::getBarcodePNG($payload, 'QRCODE', 12, 12, [15, 23, 42]));
        $filename = 'table-qr-'.preg_replace('/[^A-Za-z0-9_\-]/', '-', $table->name).'.png';

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    protected function tableQrPayload(ResTable $table)
    {
        return url('/table/'.$table->id);
    }

    protected function validatedFloorId($business_id, $location_id, $floor_id)
    {
        if (empty($floor_id)) {
            return null;
        }

        $exists = ResFloor::where('business_id', $business_id)
            ->where('location_id', $location_id)
            ->where('id', $floor_id)
            ->exists();

        return $exists ? $floor_id : null;
    }
}
