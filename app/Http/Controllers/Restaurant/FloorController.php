<?php

namespace App\Http\Controllers\Restaurant;

use App\BusinessLocation;
use App\Restaurant\ResFloor;
use Datatables;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class FloorController extends Controller
{
    public function index()
    {
        if (! auth()->user()->can('access_tables')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $floors = ResFloor::where('res_floors.business_id', $business_id)
                ->join('business_locations AS BL', 'res_floors.location_id', '=', 'BL.id')
                ->select([
                    'res_floors.name as name',
                    'BL.name as location',
                    'res_floors.id',
                ]);

            return Datatables::of($floors)
                ->addColumn(
                    'action',
                    '<div class="rt-actions">
                    @role("Admin#'.$business_id.'")
                    <button type="button" data-href="{{action(\'App\Http\Controllers\Restaurant\FloorController@edit\', [$id])}}" class="rt-badge rt-badge-edit edit_floor_button"><i class="fa fa-edit"></i> @lang("messages.edit")</button>
                    <button type="button" data-href="{{action(\'App\Http\Controllers\Restaurant\FloorController@destroy\', [$id])}}" class="rt-badge rt-badge-delete delete_floor_button"><i class="fa fa-trash"></i> @lang("messages.delete")</button>
                    @endrole
                    </div>'
                )
                ->removeColumn('id')
                ->rawColumns(['action'])
                ->make(true);
        }

        return redirect()->action([TableController::class, 'index']);
    }

    public function create()
    {
        if (! auth()->user()->can('access_tables')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $business_locations = BusinessLocation::forDropdown($business_id);

        return view('restaurant.floor.create')
            ->with(compact('business_locations'));
    }

    public function store(Request $request)
    {
        if (! auth()->user()->can('access_tables')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:191',
            'location_id' => 'required|integer',
        ]);

        try {
            $input = $request->only(['name', 'location_id']);
            $business_id = $request->session()->get('user.business_id');
            $input['business_id'] = $business_id;
            $input['created_by'] = $request->session()->get('user.id');

            $floor = ResFloor::create($input);

            return [
                'success' => true,
                'data' => $floor,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().' Line:'.$e->getLine().' Message:'.$e->getMessage());

            return [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
    }

    public function edit($id)
    {
        if (! auth()->user()->can('access_tables')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $floor = ResFloor::where('business_id', $business_id)->findOrFail($id);
        $business_locations = BusinessLocation::forDropdown($business_id);

        return view('restaurant.floor.edit')
            ->with(compact('floor', 'business_locations'));
    }

    public function update(Request $request, $id)
    {
        if (! auth()->user()->can('access_tables')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:191',
            'location_id' => 'required|integer',
        ]);

        try {
            $business_id = $request->session()->get('user.business_id');
            $floor = ResFloor::where('business_id', $business_id)->findOrFail($id);
            $floor->name = $request->input('name');
            $floor->location_id = $request->input('location_id');
            $floor->save();

            return [
                'success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().' Line:'.$e->getLine().' Message:'.$e->getMessage());

            return [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
    }

    public function destroy($id)
    {
        if (! auth()->user()->can('access_tables')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->user()->business_id;
            $floor = ResFloor::where('business_id', $business_id)->findOrFail($id);

            if ($floor->tables()->exists()) {
                return [
                    'success' => false,
                    'msg' => __('restaurant.cannot_delete_floor_has_tables'),
                ];
            }

            $floor->delete();

            return [
                'success' => true,
                'msg' => __('lang_v1.deleted_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().' Line:'.$e->getLine().' Message:'.$e->getMessage());

            return [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
    }

    /**
     * Floors for a branch — used when adding/editing a table.
     */
    public function forLocation($location_id)
    {
        if (! auth()->user()->can('access_tables')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $floors = ResFloor::where('business_id', $business_id)
            ->where('location_id', $location_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['success' => true, 'data' => $floors]);
    }
}
