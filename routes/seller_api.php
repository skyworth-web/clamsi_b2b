<?php

use App\Http\Controllers\Seller\v1\ApiController;
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
Route::get('get_stores', [ApiController::class, 'get_stores']);
Route::get('get_languages', [ApiController::class, 'get_languages']);
Route::get('get_language_labels', [ApiController::class, 'get_language_labels']);
Route::get('get_cities', [ApiController::class, 'get_cities'])->middleware('language');
Route::get('get_zipcodes', [ApiController::class, 'get_zipcodes'])->middleware('language');
Route::get('get_taxes', [ApiController::class, 'get_taxes'])->middleware('language');
Route::get('get_countries_data', [ApiController::class, 'get_countries_data']);
Route::get('get_brand_list', [ApiController::class, 'get_brand_list'])->middleware('language');
Route::get('get_settings', [ApiController::class, 'get_settings']);
Route::get('verify_user', [ApiController::class, 'verify_user'])->middleware('language');
Route::post('reset_password', [ApiController::class, 'reset_password']);
Route::get('get_user_details', [ApiController::class, 'get_user_details']);
Route::get('get_zones', [ApiController::class, 'get_zones'])->name('get_zones')->middleware('language');
Route::get('get_all_categories', [ApiController::class, 'get_all_categories']);

Route::group(['middleware' => ['check_token', 'auth:sanctum']], function () {
    Route::get('get_notifications', [ApiController::class, 'get_notifications']);
    Route::post('update_user', [ApiController::class, 'update_user'])->middleware('language');
    Route::get('get_orders', [ApiController::class, 'get_orders'])->middleware('language');
    Route::get('get_order_items', [ApiController::class, 'get_order_items'])->middleware('language');
    Route::put('update_order_item_status', [ApiController::class, 'update_order_item_status']);
    Route::get('get_categories', [ApiController::class, 'get_categories'])->middleware('language');
    Route::get('get_products', [ApiController::class, 'get_products'])->middleware('language');
    Route::get('get_transactions', [ApiController::class, 'get_transactions']);
    Route::get('get_statistics', [ApiController::class, 'get_statistics'])->middleware('language');
    Route::put('update_fcm', [ApiController::class, 'update_fcm']);
    Route::post('send_withdrawal_request', [ApiController::class, 'send_withdrawal_request']);
    Route::get('get_withdrawal_request', [ApiController::class, 'get_withdrawal_request']);
    Route::get('get_attributes', [ApiController::class, 'get_attributes']);
    Route::get('get_attribute_values', [ApiController::class, 'get_attribute_values']);
    Route::get('get_media', [ApiController::class, 'get_media']);
    Route::post('add_products', [ApiController::class, 'add_products'])->middleware('language');
    Route::get('get_seller_details', [ApiController::class, 'get_seller_details'])->middleware('language');
    Route::delete('delete_product', [ApiController::class, 'delete_product']);
    Route::put('update_products', [ApiController::class, 'update_products'])->middleware('language');
    Route::get('get_delivery_boys', [ApiController::class, 'get_delivery_boys']);
    Route::post('upload_media', [ApiController::class, 'upload_media']);
    Route::get('get_product_rating', [ApiController::class, 'get_product_rating']);
    Route::get('get_combo_product_rating', [ApiController::class, 'get_combo_product_rating']);
    Route::get('get_order_tracking', [ApiController::class, 'get_order_tracking']);
    Route::put('edit_order_tracking', [ApiController::class, 'edit_order_tracking']);
    Route::get('get_sales_list', [ApiController::class, 'get_sales_list']);
    Route::put('update_product_status', [ApiController::class, 'update_product_status']);
    Route::post('add_product_faqs', [ApiController::class, 'add_product_faqs']);
    Route::get('get_product_faqs', [ApiController::class, 'get_product_faqs'])->middleware('language');
    Route::delete('delete_product_faq', [ApiController::class, 'delete_product_faq']);
    Route::put('edit_product_faq', [ApiController::class, 'edit_product_faq']);
    Route::put('manage_stock', [ApiController::class, 'manage_stock']);
    Route::put('manage_combo_stock', [ApiController::class, 'manage_combo_stock']);
    Route::post('add_pickup_location', [ApiController::class, 'add_pickup_location']);
    Route::get('get_pickup_locations', [ApiController::class, 'get_pickup_locations']);
    Route::post('create_shiprocket_order', [ApiController::class, 'create_shiprocket_order']);
    Route::post('generate_awb', [ApiController::class, 'generate_awb']);
    Route::post('send_pickup_request', [ApiController::class, 'send_pickup_request']);
    Route::post('generate_label', [ApiController::class, 'generate_label']);
    Route::post('generate_invoice', [ApiController::class, 'generate_invoice']);
    Route::put('cancel_shiprocket_order', [ApiController::class, 'cancel_shiprocket_order']);
    Route::get('download_label', [ApiController::class, 'download_label']);
    Route::get('download_invoice', [ApiController::class, 'download_invoice']);
    Route::get('shiprocket_order_tracking', [ApiController::class, 'shiprocket_order_tracking']);
    Route::get('get_shiprocket_order', [ApiController::class, 'get_shiprocket_order']);
    Route::delete('delete_order', [ApiController::class, 'delete_order']);
    Route::delete('delete_seller', [ApiController::class, 'delete_seller']);
    Route::get('get_seller_stores', [ApiController::class, 'get_seller_stores'])->middleware('language');
    Route::get('get_combo_products', [ApiController::class, 'get_combo_products'])->middleware('language');
    Route::post('add_combo_product', [ApiController::class, 'add_combo_product'])->middleware('language');
    Route::delete('delete_combo_product', [ApiController::class, 'delete_combo_product']);
    Route::put('update_combo_product', [ApiController::class, 'update_combo_product'])->middleware('language');
    Route::post('add_seller_store', [ApiController::class, 'add_seller_store'])->middleware('language');
    Route::get('get_total_data', [ApiController::class, 'get_total_data']);
    Route::get('get_overview_statistic', [ApiController::class, 'get_overview_statistic']);
    Route::get('most_selling_categories', [ApiController::class, 'most_selling_categories'])->middleware('language');
    Route::get('top_selling_products', [ApiController::class, 'top_selling_products'])->middleware('language');
    Route::get('download_order_invoice', [ApiController::class, 'download_order_invoice']);
    Route::get('get_all_parcels', [ApiController::class, 'get_all_parcels'])->name('get_all_parcels');
    Route::post('create_order_parcel', [ApiController::class, 'create_order_parcel'])->name('create_order_parcel');
    Route::delete('delete_order_parcel', [ApiController::class, 'delete_order_parcel']);
    Route::put('update_parcel_order_status', [ApiController::class, 'update_parcel_order_status']);
    Route::put('update_shiprocket_order_status', [ApiController::class, 'update_shiprocket_order_status']);
    Route::get('download_parcel_invoice', [ApiController::class, 'download_parcel_invoice']);
    Route::post('update_product_deliverability', [ApiController::class, 'update_product_deliverability']);
    Route::post('update_combo_product_deliverability', [ApiController::class, 'update_combo_product_deliverability']);
});
