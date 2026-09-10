<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:api', 'timezone'])->group(function () {
    Route::get('get-tables', [Modules\Connector\Http\Controllers\Api\WaiterTableController::class, 'getTables']);
    Route::get('tables/{id}', [Modules\Connector\Http\Controllers\Api\WaiterTableController::class, 'show']);
    Route::post('change-status/{id}', [Modules\Connector\Http\Controllers\Api\WaiterTableController::class, 'changeStatus']);
    Route::post('new-order', [Modules\Connector\Http\Controllers\Api\WaiterTableController::class, 'newOrder']);
    Route::post('update-orders/{id}', [Modules\Connector\Http\Controllers\Api\WaiterTableController::class, 'updateOrder']);
    Route::post('cancel-order', [Modules\Connector\Http\Controllers\Api\WaiterTableController::class, 'cancelOrder']);
});
