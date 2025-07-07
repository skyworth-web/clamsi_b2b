<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\Webhook;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\InstallerController;
use App\Http\Controllers\Seller\MediaController as SellerMediaController;
use App\Http\Controllers\Seller\AreaController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\StoreController;
use App\Http\Controllers\Seller\CategoryController;


use App\Livewire\Home;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

// ---------------------------------------------------------------------------------------------------------------------------
Route::get('/sitemap', function () {
    Artisan::call('sitemap:generate');
    return redirect()->back()->with('message', 'Sitemap generated successfully!');
});
Route::get('/clear-cache', function () {
    Artisan::call('optimize:clear');
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    return redirect()->back()->with('message', 'Cache cleared successfully.');
});
Route::get('/version', function () {

   // return app()->version();
   return "version";
});

Route::get('storage-link', function () {
    Artisan::call('storage:link');
});

Route::get('/install', [InstallerController::class, 'index'])->middleware('guest');

Route::post('/installer/config-db', [InstallerController::class, 'config_db'])->middleware('guest');

Route::post('/installer/install', [InstallerController::class, 'install'])->middleware('guest');

Route::get('admin/web_product_card_style', [StoreController::class, 'webProductCardStyle']);
Route::get('admin/web_categories_style', [StoreController::class, 'webCategoriesStyle']);
Route::get('admin/web_brands_style', [StoreController::class, 'webBrandsStyle']);
Route::get('admin/web_wishlist_style', [StoreController::class, 'webWishlistStyle']);
Route::get('admin/web_home_page_theme', [StoreController::class, 'webHomePageTheme']);

Route::get('/manifest', function () {
    return response()->json(config('manifest'));
})->name('manifest');

Route::get('/product/upload', [ProductController::class, 'uploadProduct']);
// Route::get('/product/upload', function(){
//     return response()->json(config('manifest'));
// })->name('manifest');

Route::middleware(['CheckInstallation'])->group(function () {
    // Route::get('/', function () {
    //     return redirect()->route('admin.home');
    // });
    Route::get('/', Home::class)->name('home');
    Route::get('admin/register', [UserController::class, 'create']);

    Route::post('admin/users', [UserController::class, 'store']);

    Route::get('admin/logout', [UserController::class, 'logout'])->name('admin.logout');

    Route::post('/admin/users/authenticate', [UserController::class, 'authenticate'])->name('admin.authenticate');

    Route::get('admin/login', [UserController::class, 'login'])->name('admin.login');

    Route::get('admin/home', [HomeController::class, 'index'])->name('admin.home');

    // Routs for forgot password and reset password

    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password-mail', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'ResetPassword'])->name('admin.password.update');

    Route::get('/admin', function () {
        if (Auth::check()) {
            // User is logged in, redirect to the admin home page
            return redirect()->route('admin.home');
            // return redirect()->route('admin.login');
        } else {
            // User is not logged in, redirect to the admin login page
            return redirect()->route('admin.login');
        }
    });



    // seller routes
    Route::get('/seller', function () {
        return redirect()->route('seller.login');
    });

    Route::get('seller/login', [UserController::class, 'seller_login'])->name('seller.login');
    Route::get('seller/register', [UserController::class, 'seller_register'])->name('seller.register');
    Route::get('seller/get_zones', [AreaController::class, 'get_zones'])->name('seller.get_zones');
    Route::get('seller/logout', [UserController::class, 'seller_logout'])->name('seller.logout');
    Route::get('seller/categories/get_category_details', [CategoryController::class, 'getCategoryDetails']);
    Route::post('seller/store', [UserController::class, 'sellerStore'])->name('seller.register.store')->middleware(['demo_restriction']);

    // delivery boy routes
    Route::get('/delivery_boy', function () {
        return redirect()->route('delivery_boy.login');
    });

    Route::get('delivery_boy/login', [UserController::class, 'delivery_boy_login'])->name('delivery_boy.login');
    Route::get('delivery_boy/logout', [UserController::class, 'delivery_boy_logout'])->name('delivery_boy.logout');



    // system policies pages

    Route::get("admin/privacy_policy/privacy_policy_page", [SettingController::class, 'privacy_policy'])->name('privacy_policy.view');

    Route::get("admin/terms_and_conditions/terms_and_condition_page", [SettingController::class, 'terms_and_conditions'])->name('terms_and_conditions.view');

    Route::get("admin/shipping_policy/shipping_policy_page", [SettingController::class, 'shipping_policy'])->name('shipping_policy.view');

    Route::get("admin/return_policy/return_policy_page", [SettingController::class, 'return_policy'])->name('return_policy.view');

    //admin & seller policies page

    Route::get("admin/privacy_policy/seller_privacy_policy_page", [SettingController::class, 'seller_privacy_policy'])->name('seller_privacy_policy.view');

    Route::get("admin/terms_and_condition/seller_terms_and_condition_page", [SettingController::class, 'seller_terms_and_condition'])->name('seller_terms_and_conditions.view');

    // delivery boy policies page

    Route::get("admin/privacy_policy/delivery_boy_privacy_page", [SettingController::class, 'delivery_boy_privacy_policy'])->name('delivery_boy_privacy_policy.view');

    Route::get("admin/terms_and_conditions/delivery_boy_terms_and_condition_page", [SettingController::class, 'delivery_boy_terms_and_conditions'])->name('delivery_boy_terms_and_conditions.view');

    // admin routes file

    Route::group(['middleware' => ['auth']], function () {
        // Routes that only admins can access
        include_once("admin_routes.php");
        include_once("seller_routes.php");
        include_once("delivery_boy_routes.php");
    });

    Route::get('admin/media/image', [MediaController::class, 'dynamic_image'])->name('admin.dynamic_image');
    Route::get('/media/image', [MediaController::class, 'dynamic_image'])->name('front_end.dynamic_image');



    // media route

    Route::get('/admin/media/list', [MediaController::class, 'list'])->name('admin.media.list');

    Route::get('/seller/media/list', [SellerMediaController::class, 'list']);

    include_once("front_end_routes.php");

    //webhook route

    Route::get('admin/webhook/razorpay_webhook', [Webhook::class, 'razorpay_webhook'])->name('admin.razorpay_webhook');
    Route::get('admin/webhook/paystack_webhook', [Webhook::class, 'paystack_webhook'])->name('admin.paystack_webhook');
    Route::post('admin/webhook/stripe_webhook', [Webhook::class, 'stripe_webhook'])->name('admin.stripe_webhook');
    Route::get('admin/webhook/phonepe_webhook', [Webhook::class, 'phonepe_webhook'])->name('admin.phonepe_webhook');
    Route::get('admin/webhook/spr_webhook', [Webhook::class, 'spr_webhook'])->name('admin.spr_webhook');
});
Route::get('admin/orders/generat_invoice_PDF/{id}', [OrderController::class, 'generatInvoicePDF'])->name('admin.orders.generatInvoicePDF');
Route::get('/admin/stores', [StoreController::class, 'index'])->name('admin.stores.index');
Route::post('admin/store', [StoreController::class, 'store'])->middleware(['demo_restriction'])->middleware('permissions:create store')->name('admin.stores.store');
Route::get("settings/registration", [SettingController::class, 'registration'])->name('admin.system_registration');
Route::post("settings/system_registration", [SettingController::class, 'systemRegister'])->name('admin.system_register')->middleware(['demo_restriction']);
Route::post("settings/web_system_registration", [SettingController::class, 'WebsystemRegister'])->name('admin.web_system_register')->middleware(['demo_restriction']);

// Add this route for deleting/moving category with products
Route::post('/categories/delete-with-products', [\App\Http\Controllers\Admin\CategoryController::class, 'deleteWithProducts']);

// Add this route for reordering categories (for product upload UI drag-and-drop)
Route::post('/categories/reorder', [\App\Http\Controllers\CategoryController::class, 'reorder'])->name('categories.reorder');

// Add this route for creating categories (for product upload UI)
Route::post('/categories', [\App\Http\Controllers\CategoryController::class, 'store'])->name('categories.store');
