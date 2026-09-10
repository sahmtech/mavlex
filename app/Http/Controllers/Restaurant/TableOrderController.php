<?php

namespace App\Http\Controllers\Restaurant;

use App\Services\TableOrderService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TableOrderController extends Controller
{
    protected $tableOrders;

    public function __construct(TableOrderService $tableOrders)
    {
        $this->tableOrders = $tableOrders;
    }

    protected function authorizeTables()
    {
        if (! auth()->user()->can('access_tables')) {
            abort(403, 'Unauthorized action.');
        }
    }

    public function floorState(Request $request)
    {
        $this->authorizeTables();
        $business_id = $request->session()->get('user.business_id');

        return response()->json([
            'success' => true,
            'data' => $this->tableOrders->floorState(
                $business_id,
                $request->get('location_id') ?: $request->get('establishment_id'),
                $request->get('waiter_id'),
                $request->get('floor_id')
            ),
        ]);
    }

    public function products(Request $request)
    {
        $this->authorizeTables();
        $business_id = $request->session()->get('user.business_id');

        return response()->json([
            'success' => true,
            'data' => $this->tableOrders->searchProducts(
                $business_id,
                $request->get('location_id'),
                $request->get('term')
            ),
        ]);
    }

    public function show($id)
    {
        $this->authorizeTables();
        $business_id = request()->session()->get('user.business_id');

        return response()->json([
            'success' => true,
            'data' => $this->tableOrders->tableDetails($business_id, $id, request()->only(['location_id', 'establishment_id', 'floor_id'])),
        ]);
    }

    public function newOrder(Request $request)
    {
        $this->authorizeTables();
        $business_id = $request->session()->get('user.business_id');
        $user_id = $request->session()->get('user.id');

        try {
            $result = $this->tableOrders->newOrUpdateOrder($business_id, $user_id, $request->all(), false);

            return response()->json([
                'success' => true,
                'msg' => $result['created'] ? __('restaurant.order_sent_to_kitchen') : __('restaurant.order_updated'),
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'msg' => $e->getMessage(),
            ], 422);
        }
    }

    public function changeStatus(Request $request, $id)
    {
        $this->authorizeTables();
        $business_id = $request->session()->get('user.business_id');
        $user_id = $request->session()->get('user.id');

        try {
            $table = $this->tableOrders->changeStatus($business_id, $id, $request->input('status'), $user_id, $request->all());

            return response()->json([
                'success' => true,
                'msg' => __('lang_v1.updated_success'),
                'data' => $table,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'msg' => $e->getMessage(),
            ], 422);
        }
    }

    public function serve(Request $request, $id)
    {
        $this->authorizeTables();
        $business_id = $request->session()->get('user.business_id');

        try {
            $order = $this->tableOrders->serveOrder($business_id, $id);

            return response()->json([
                'success' => true,
                'msg' => __('restaurant.order_successfully_marked_served'),
                'data' => $order,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'msg' => $e->getMessage(),
            ], 422);
        }
    }

    public function cancel(Request $request)
    {
        $this->authorizeTables();
        $business_id = $request->session()->get('user.business_id');

        try {
            $this->tableOrders->cancelOrder($business_id, $request->all());

            return response()->json([
                'success' => true,
                'msg' => __('restaurant.order_cancelled'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'msg' => $e->getMessage(),
            ], 422);
        }
    }
}
