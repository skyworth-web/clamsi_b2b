<?php

use App\Http\Controllers\App\v1\ApiController;
use App\Http\Controllers\AIChatController;
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

Route::post('test-email', [ApiController::class, 'test_email']);

// routes which do not required token

Route::post('register_user', [ApiController::class, 'register_user']);
Route::post('login', [ApiController::class, 'login']);
Route::post('test_shiprocket', [ApiController::class, 'test_shiprocket']);
Route::post('sign_up', [ApiController::class, 'sign_up']);
Route::post('verify_user', [ApiController::class, 'verify_user']);
Route::post('verify_otp', [ApiController::class, 'verify_otp']);
Route::post('resend_otp', [ApiController::class, 'resend_otp']);
Route::get('get_cities', [ApiController::class, 'get_cities'])->middleware('language');
Route::get('get_settings', [ApiController::class, 'get_settings'])->middleware('language');
Route::get('get_stores', [ApiController::class, 'get_stores'])->middleware('language');
Route::get('get_products', [ApiController::class, 'get_products'])->middleware('language');
Route::get('get_categories', [ApiController::class, 'get_categories'])->middleware('language');
Route::get('get_slider_images', [ApiController::class, 'get_slider_images'])->middleware('language');
Route::get('get_sections', [ApiController::class, 'get_sections'])->middleware('language');
Route::get('get_offer_images', [ApiController::class, 'get_offer_images'])->middleware('language');
Route::get('get_brands', [ApiController::class, 'get_brands'])->middleware('language');
Route::get('get_sellers', [ApiController::class, 'get_sellers']);
Route::get('get_offers_sliders', [ApiController::class, 'get_offers_sliders'])->middleware('language');
Route::get('get_combo_products', [ApiController::class, 'get_combo_products'])->middleware('language');
Route::get('get_categories_sliders', [ApiController::class, 'get_categories_sliders'])->middleware('language');
Route::get('get_languages', [ApiController::class, 'get_languages']);
Route::get('get_language_labels', [ApiController::class, 'get_language_labels']);
Route::get('get_zipcode_by_city_id', [ApiController::class, 'get_zipcode_by_city_id']);
Route::get('get_faqs', [ApiController::class, 'get_faqs']);
Route::get('get_zipcodes', [ApiController::class, 'get_zipcodes']);
Route::get('top_sellers', [ApiController::class, 'top_sellers']);
Route::get('most_selling_products', [ApiController::class, 'most_selling_products'])->middleware('language');
Route::get('most_popular_products', [ApiController::class, 'most_popular_products'])->middleware('language');
Route::get('best_sellers', [ApiController::class, 'best_sellers']);
Route::post('reset_password', [ApiController::class, 'reset_password']);
Route::get('get_login_identity', [ApiController::class, 'get_login_identity']);
Route::post('validate_refer_code', [ApiController::class, 'validate_refer_code']);
Route::get('app_payment_status', [ApiController::class, 'app_payment_status'])->name('app_payment_status');
Route::post('ipn', [ApiController::class, 'ipn'])->name('ipn');
Route::get('get_similar_products', [ApiController::class, 'get_similar_products'])->middleware('language');
Route::get('get_combo_similar_products', [ApiController::class, 'get_combo_similar_products'])->middleware('language');
Route::post('search_products', [ApiController::class, 'search_products'])->middleware('language');
Route::post('get_most_searched_history', [ApiController::class, 'get_most_searched_history']);
Route::get('get_paypal_link', [ApiController::class, 'get_paypal_link']);
Route::get('/paypal_transaction_webview', [ApiController::class, 'paypal_transaction_webview'])->name('paypal_transaction_webview');
Route::get('get_zones', [ApiController::class, 'get_zones'])->name('get_zones')->middleware('language');
Route::get('test', [ApiController::class, 'test'])->name('test');
Route::get('handle_paystack_callback', [ApiController::class, 'handle_paystack_callback']);


// -------------------------------------------------------------------------------------

Route::group(['middleware' => ['check_token', 'auth:sanctum']], function () {
    Route::put('update_fcm', [ApiController::class, 'update_fcm']);
    Route::post('delete_user', [ApiController::class, 'delete_user']);
    Route::post('update_user', [ApiController::class, 'update_user']);
    Route::post('add_to_favorites', [ApiController::class, 'add_to_favorites']);
    Route::post('remove_from_favorites', [ApiController::class, 'remove_from_favorites']);
    Route::get('get_favorites', [ApiController::class, 'get_favorites'])->middleware('language');
    Route::post('add_address', [ApiController::class, 'add_address']);
    Route::put('update_address', [ApiController::class, 'update_address']);
    Route::post('delete_address', [ApiController::class, 'delete_address']);
    Route::get('get_address', [ApiController::class, 'get_address']);
    Route::get('get_user_cart', [ApiController::class, 'get_user_cart'])->middleware('language');
    Route::get('get_promo_codes', [ApiController::class, 'get_promo_codes'])->middleware('language');
    Route::post('validate_promo_code', [ApiController::class, 'validate_promo_code'])->middleware('language');
    Route::post('place_order', [ApiController::class, 'place_order'])->middleware('language');
    Route::delete('remove_from_cart', [ApiController::class, 'remove_from_cart']);
    Route::post('manage_cart', [ApiController::class, 'manage_cart'])->middleware('language');
    Route::post('clear_cart', [ApiController::class, 'clear_cart']);
    Route::get('get_orders', [ApiController::class, 'get_orders'])->middleware('language');
    Route::post('update_order_item_status', [ApiController::class, 'update_order_item_status']);
    Route::get('get_ticket_types', [ApiController::class, 'get_ticket_types']);
    Route::post('add_ticket', [ApiController::class, 'add_ticket']);
    Route::put('edit_ticket', [ApiController::class, 'edit_ticket']);
    Route::get('get_tickets', [ApiController::class, 'get_tickets']);
    Route::get('get_messages', [ApiController::class, 'get_messages']);
    Route::post('is_product_delivarable', [ApiController::class, 'is_product_delivarable']);
    Route::post('is_seller_delivarable', [ApiController::class, 'is_seller_delivarable']);
    Route::post('check_cart_products_delivarable', [ApiController::class, 'check_cart_products_delivarable'])->middleware('language');
    Route::delete('delete_social_account', [ApiController::class, 'delete_social_account']);
    Route::post('add_product_faqs', [ApiController::class, 'add_product_faqs']);
    Route::get('get_product_faqs', [ApiController::class, 'get_product_faqs']);
    Route::post('send_message', [ApiController::class, 'send_message']);
    Route::put('update_order_status', [ApiController::class, 'update_order_status']);
    Route::delete('delete_order', [ApiController::class, 'delete_order']);
    Route::get('get_notifications', [ApiController::class, 'get_notifications'])->middleware('language');
    Route::post('add_transaction', [ApiController::class, 'add_transaction']);
    Route::get('transactions', [ApiController::class, 'transactions']);
    Route::post('set_product_rating', [ApiController::class, 'set_product_rating']);
    Route::get('get_product_rating', [ApiController::class, 'get_product_rating']);
    Route::delete('delete_product_rating', [ApiController::class, 'delete_product_rating']);
    Route::post('check_shiprocket_serviceability', [ApiController::class, 'check_shiprocket_serviceability']);
    Route::post('send_withdrawal_request', [ApiController::class, 'send_withdrawal_request']);
    Route::get('get_withdrawal_request', [ApiController::class, 'get_withdrawal_request']);
    Route::post('send_bank_transfer_proof', [ApiController::class, 'send_bank_transfer_proof']);
    Route::post('download_link_hash', [ApiController::class, 'download_link_hash']);
    Route::post('set_combo_product_rating', [ApiController::class, 'set_combo_product_rating']);
    Route::get('get_combo_product_rating', [ApiController::class, 'get_combo_product_rating']);
    Route::delete('delete_combo_product_rating', [ApiController::class, 'delete_combo_product_rating']);
    Route::get('download_order_invoice', [ApiController::class, 'download_order_invoice']);
    Route::post('phonepe_app', [ApiController::class, 'phonepe_app']);
    Route::post('razorpay_create_order', [ApiController::class, 'razorpay_create_order']);
    Route::get('paystack_webview', [ApiController::class, 'paystack_webview']);
    // Route::get('get_paypal_link', [ApiController::class, 'get_paypal_link']);
    // Route::get('/paypal_transaction_webview', [ApiController::class, 'paypal_transaction_webview'])->name('paypal_transaction_webview');
});

Route::post('/ai-chat', [AIChatController::class, 'chat']);
Route::post('/ai-chat/clear', [AIChatController::class, 'clearHistory']);
