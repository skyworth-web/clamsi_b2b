<?php

use App\Http\Controllers\Delivery_boy\v1\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('login', [ApiController::class, 'login'])->middleware('language');
Route::post('register', [ApiController::class, 'register']);
Route::get('get_settings', [ApiController::class, 'get_settings']);
Route::get('get_cities', [ApiController::class, 'get_cities']);
Route::get('get_zipcodes', [ApiController::class, 'get_zipcodes']);
Route::get('get_languages', [ApiController::class, 'get_languages']);
Route::get('get_language_labels', [ApiController::class, 'get_language_labels']);
Route::get('get_zones', [ApiController::class, 'get_zones'])->name('get_zones')->middleware('language');

Route::group(['middleware' => ['check_token', 'auth:sanctum']], function () {
    Route::get('get_delivery_boy_details', [ApiController::class, 'get_delivery_boy_details'])->middleware('language');
    Route::get('get_orders', [ApiController::class, 'get_orders'])->middleware('language');
    Route::get('get_fund_transfers', [ApiController::class, 'get_fund_transfers']);
    Route::put('update_fcm', [ApiController::class, 'update_fcm']);
    Route::post('update_user', [ApiController::class, 'update_user'])->middleware('language'); // use POST method instead of PUT because we need to send file in this API
    Route::get('get_notifications', [ApiController::class, 'get_notifications']);
    Route::get('verify_user', [ApiController::class, 'verify_user']);
    Route::post('send_withdrawal_request', [ApiController::class, 'send_withdrawal_request']);
    Route::get('get_withdrawal_request', [ApiController::class, 'get_withdrawal_request']);
    Route::put('update_order_item_status', [ApiController::class, 'update_order_item_status']);
    Route::get('get_delivery_boy_cash_collection', [ApiController::class, 'get_delivery_boy_cash_collection']);
    Route::delete('delete_delivery_boy', [ApiController::class, 'delete_delivery_boy']);
    Route::get('get_wallet_transaction', [ApiController::class, 'get_wallet_transaction']);
    Route::get('get_returned_order_items', [ApiController::class, 'get_returned_order_items'])->middleware('language');
    Route::put('update_returned_order_item_status', [ApiController::class, 'update_returned_order_item_status']);
    Route::post('reset_password', [ApiController::class, 'reset_password']);
});
