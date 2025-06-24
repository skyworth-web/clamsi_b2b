<?php

use App\Http\Controllers\Seller\PaymentRequestController;
use App\Http\Controllers\Delivery_boy\CashCollectionController;
use App\Http\Controllers\Delivery_boy\HomeController;
use App\Http\Controllers\Delivery_boy\OrderController;
use App\Http\Controllers\Delivery_boy\UserController;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\Delivery_boy\LanguageController;

Route::group(
    ['middleware' => ['auth', 'role:delivery_boy','CheckPurchaseCode']],
    function () {
        Route::get('delivery_boy/home', [HomeController::class, 'index'])->name('delivery_boy.home');


        // Orders Section

        Route::resource("delivery_boy/orders", OrderController::class)->names([
            'index' => 'delivery_boy.orders.index',
        ])->except('show');
        Route::get('delivery_boy/orders/{order}/edit', [OrderController::class, 'edit'])->name('delivery_boy.orders.edit');
        Route::get('delivery_boy/orders/order_item_list', [OrderController::class, 'order_item_list'])->name('delivery_boy.orders.item_list');
        Route::get('delivery_boy/orders/view_parcels', [OrderController::class, 'view_parcels'])->name('delivery_boy.view_parcels');
        Route::post('delivery_boy/orders/update_order_item_status', [OrderController::class, 'update_order_item_status'])->middleware(['demo_restriction']);
        Route::get('delivery_boy/returned_orders', [OrderController::class, 'returned_orders'])->name('delivery_boy.cash.returned_order');
        Route::get('delivery_boy/returned_orders_list', [OrderController::class, 'returned_orders_list'])->name('delivery_boy.returned_ordres_list');
        Route::post('delivery_boy/orders/update_return_order_item_status', [OrderController::class, 'update_return_order_item_status'])->middleware(['demo_restriction']);
        // Route::get('delivery_boy/orders/returned_orders', [OrderController::class, 'returned_orders_edit'])->name('delivery_boy.returned_orders.edit');
        Route::get('delivery_boy/orders/{order_id}/returned_orders/{order_item_id}', [OrderController::class, 'edit_returned_orders'])
        ->name('delivery_boy.returned_orders.edit');
        Route::get('delivery_boy/cash_collection', [CashCollectionController::class, 'index'])->name('delivery_boy.cash.collection');
        Route::get('delivery_boy/cash_collection/list', [CashCollectionController::class, 'list'])->name('delivery_boy.cash.collection.list');
        Route::get('delivery_boy/fund_transfer', [CashCollectionController::class, 'fund_transfer'])->name('delivery_boy.fund.transfer');
        Route::get('delivery_boy/cash_collection/fund_transfers_list', [CashCollectionController::class, 'fund_transfers_list'])->name('delivery_boy.fund.transfers.list');
        Route::get('delivery_boy/wallet_transaction', [UserController::class, 'walletTransaction'])->name('delivery_boy.walletTransaction');
        Route::get('delivery_boy/getTransactionList', [UserController::class, 'getTransactionList'])->name('delivery_boy.getTransactionList');
        Route::get('delivery_boy/zones/zones_data', [UserController::class, 'zone_data']);
        //User routes

        Route::get('delivery_boy/account/{user}', [UserController::class, 'edit']);
        Route::put('delivery_boy/account/update/{id}', [UserController::class, 'update'])->name('delivery_boy.account.update')->middleware(['demo_restriction']);
        Route::get("delivery_boy/area/get_zipcode", [UserController::class, 'get_zipcodes']);
        Route::get("delivery_boy/area/get_cities", [UserController::class, 'getCities']);
        Route::put('delivery_boy/payment_request/add_withdrawal_request', function (Request $request) {
            return app(PaymentRequestController::class)->add_withdrawal_request($request, true);
        })->name('delivery_boy.payment_request.add_withdrawal_request')->middleware(['demo_restriction']);

        // language

        Route::get("delivery_boy/settings/language", [LanguageController::class, 'index']);

        Route::put("delivery_boy/settings/languages/savelabel", [LanguageController::class, 'savelabel'])->name('savelabel');

        Route::get('delivery_boy/settings/languages/change', [LanguageController::class, 'change'])->name('changeLang');

        Route::get("delivery_boy/settings/set-language/{locale}", [LanguageController::class, 'setLanguage'])->name('set-language'); // language
    }



);
