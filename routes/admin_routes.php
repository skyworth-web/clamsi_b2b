<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\Admin\TaxController;
use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\OfferController;
use App\Http\Controllers\Admin\SellerController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\Admin\ProductFaqController;
use App\Http\Controllers\Admin\Delivery_boyController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\UserPermissionController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\CustomMessageController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\TicketController;
use Illuminate\Notifications\Notification;
use App\Http\Controllers\Admin\FeaturedSectionsController;
use App\Http\Controllers\Admin\ManageStockController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentRequestController;
use App\Http\Controllers\Admin\ReturnRequestController;
use App\Http\Controllers\Admin\StoreController;
use App\Http\Controllers\Admin\AppSettingController;
use App\Http\Controllers\Admin\CashCollectionController;
use App\Http\Controllers\Admin\ComboProductController;
use App\Http\Controllers\Admin\PickupLocationController;
use App\Http\Controllers\Admin\ComboProductAttributeController;
use App\Http\Controllers\Admin\ComboProductFaqController;
use App\Http\Controllers\Admin\CronJobController;
use App\Http\Controllers\Admin\FrontLanguageController;
use App\Http\Controllers\Admin\FundTransferController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\ZoneController;
use App\Http\Controllers\vendor\Chatify\MessagesController;
use App\Http\Controllers\Admin\ReportController;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Event\Telemetry\System;

Route::group(
    ['middleware' => ['auth', 'role:super_admin,admin,editor', 'CheckPurchaseCode', 'CheckStoreNotEmpty']],
    function () {

        Route::get('admin/test', function () {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
        });

        Route::get('admin/home', [HomeController::class, 'index'])->name('admin.home');
        Route::get('admin/cronjob/settleSellerCommission', [CronJobController::class, 'settleSellerCommission'])->middleware(['demo_restriction'])->middleware('permissions:edit seller');
        Route::get('admin/cronjob/settleCashbackDiscount', [CronJobController::class, 'settleCashbackDiscount'])->middleware(['demo_restriction']);

        Route::get('admin/account/{user}', [UserController::class, 'edit']);

        Route::put('admin/users/update/{user}', [UserController::class, 'update'])->middleware(['demo_restriction']);

        Route::get('admin/user/search_user', [UserController::class, 'searchUser']);
        // Route::get('admin/user/search_user', [UserController::class, 'searchSeller']);
        Route::get('admin/user/search_seller', [UserController::class, 'searchSeller']);

        Route::prefix('admin')->group(function () {

            //categories

            Route::resource("categories", CategoryController::class)->except(['show'])
                ->missing(function (Request $request) {
                    return Redirect::route('categories.index');
                })->middleware('CheckDefaultStore');
            Route::post("/categories", [CategoryController::class, 'store'])->middleware(['demo_restriction'])->name('categories.store')->middleware('permissions:create categories');

            Route::get('categories/list', [CategoryController::class, 'list'])->name('categories.list');

            Route::get('categories/category_slider', [CategoryController::class, 'category_slider'])->name('category_slider.index')->middleware('CheckDefaultStore');

            Route::get('categories/categories_data', [CategoryController::class, 'category_data']);

            Route::get('categories/get_categories', [CategoryController::class, 'getCategories']);

            Route::get('categories/getCategories', [CategoryController::class, 'get_categories']);

            Route::get('categories/get_seller_categories', [CategoryController::class, 'getSellerCategories']);

            Route::get('categories/category_order', [CategoryController::class, 'categoryOrder'])->name('category_order.index')->middleware('CheckDefaultStore');

            Route::get('categories/get_category_details', [CategoryController::class, 'getCategoryDetails']);

            Route::get('categories/update_category_order', [CategoryController::class, 'updateCategoryOrder']);

            Route::get('categories/destroy/{id}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy')->middleware(['demo_restriction'])->middleware('permissions:delete categories');

            Route::get('admin/categories/update_status/{id}', [CategoryController::class, 'update_status'])->middleware(['demo_restriction'])->middleware('permissions:edit categories');

            Route::get('categories/edit/{id}', [CategoryController::class, 'edit'])->name('categories.update');

            Route::put('categories/update/{id}', [CategoryController::class, 'update'])->middleware(['demo_restriction'])->middleware('permissions:edit categories');

            Route::post("categories/category_sliders", [CategoryController::class, 'store_category_slider'])->name('category_sliders.store')->middleware(['demo_restriction'])->middleware('permissions:create category_sliders');

            Route::get('categories/category_sliders_list', [CategoryController::class, 'category_sliders_list'])->name('category_sliders.list');

            Route::get('category_sliders/destroy/{id}', [CategoryController::class, 'category_slider_destroy'])->name('admin.category_sliders.destroy')->middleware(['demo_restriction'])->middleware('permissions:delete category_sliders');

            Route::get('/category_sliders/update_status/{id}', [CategoryController::class, 'update_category_slider_status'])->middleware(['demo_restriction'])->middleware('permissions:edit category_sliders');

            Route::get('category_sliders/edit/{id}', [CategoryController::class, 'category_slider_edit'])->name('admin.category_sliders.update');

            Route::put('category_sliders/update/{id}', [CategoryController::class, 'category_slider_update'])->middleware(['demo_restriction'])->middleware('permissions:edit category_sliders');

            Route::get('categories/get_seller_categories_filter', [CategoryController::class, 'get_seller_categories_filter']);

            // blog categories

            Route::resource("blogs", BlogController::class)->names([
                'index' => 'admin.blogs.index',
            ])->except('show')->middleware('CheckDefaultStore');


            Route::post("/blog_categories", [BlogController::class, 'storeCategory'])->name('blog_category.store')->middleware(['demo_restriction'])->middleware('permissions:create blog_categories');

            Route::get('blog_categories/list', [BlogController::class, 'categoryList'])->name('blog_categories.list');

            Route::get('blog_categories/destroy/{id}', [BlogController::class, 'destroyCategory'])->name('admin.blog_categories.destroy')->middleware(['demo_restriction'])->middleware('permissions:delete blog_categories');

            Route::get('admin/blog_categories/update_status/{id}', [BlogController::class, 'updateCategoryStatus'])->middleware(['demo_restriction'])->middleware('permissions:edit blog_categories');

            Route::get('/blog_category/edit/{id}', [BlogController::class, 'editCategory'])->name('blog_categories.edit');

            Route::put('/blog_category/update/{id}', [BlogController::class, 'updateCategory'])->name('blog_categories.update')->middleware(['demo_restriction'])->middleware('permissions:edit blog_categories');

            // blogs

            Route::get('/manage_blogs', [BlogController::class, 'createBlog'])->name('manage_blogs.index');

            Route::post("/admin/manage_blogs", [BlogController::class, 'storeBlog'])->name('blogs.store')->middleware(['demo_restriction'])->middleware('permissions:create blogs');

            Route::get('blogs/get_blog_categories', [BlogController::class, 'getBlogCategories']);

            Route::get('blogs/list', [BlogController::class, 'blogList'])->name('blogs.list');

            Route::get('blogs/destroy/{id}', [BlogController::class, 'destroyBlog'])->name('blogs.destroy')->middleware(['demo_restriction'])->middleware('permissions:delete blogs');

            Route::get('admin/blogs/update_status/{id}', [BlogController::class, 'updateBlogStatus'])->middleware(['demo_restriction'])->middleware('permissions:edit blog_categories');

            Route::get('/blogs/edit/{id}', [BlogController::class, 'editBlog'])->name('blogs.edit');

            Route::put('/blogs/update/{id}', [BlogController::class, 'updateBlog'])->name('blogs.update')->middleware(['demo_restriction'])->middleware('permissions:edit blog_categories');

            //setting

            Route::get('settings/', [SettingController::class, 'index'])->name('settings.index');

            Route::get("settings/system_settings", [SettingController::class, 'systemSettings'])->name('system_settings');

            Route::get("settings/updater", [SettingController::class, 'updater'])->name('updater');
            Route::post("settings/system-update", [SettingController::class, 'systemUpdate'])->name('system-update');

            // Route::get("settings/registration", [SettingController::class, 'registration'])->name('admin.system_registration');
            // Route::post("settings/system_registration", [SettingController::class, 'systemRegister'])->name('admin.system_register');

            Route::post("settings/system_settings", [SettingController::class, 'storeSystemSetting'])->name('system_settings.store')->middleware(['demo_restriction'])->middleware('permissions:edit system_setting');

            Route::post("settings/removeSettingMedia", [SettingController::class, 'removeSettingMedia']);

            Route::get("settings/email_settings", [SettingController::class, 'emailSettings'])->name('email_settings');

            Route::post("settings/email_settings", [SettingController::class, 'storeEmailSetting'])->name('email_settings.store')->middleware(['demo_restriction'])->middleware('permissions:edit smtp_setting');

            Route::get("settings/payment_settings", [SettingController::class, 'paymentSettings'])->name('payment_settings');

            Route::post("settings/payment_settings", [SettingController::class, 'storePaymentSetting'])->name('payment_setting.store')->middleware(['demo_restriction'])->middleware('permissions:edit payment_method_setting');

            Route::get("settings/shipping_settings", [SettingController::class, 'shippingSettings'])->name('shipping_settings');

            Route::post("settings/shipping_settings", [SettingController::class, 'storeShippingSettings'])->name('shipping_settings.store')->middleware(['demo_restriction'])->middleware('permissions:edit shipping_method_setting');

            Route::get("settings/time_slot_settings", [SettingController::class, 'timeSlotSettings'])->name('time_slot_settings');

            Route::post("settings/store_time_slot", [SettingController::class, 'storeTimeSlot'])->name('time_slot.store')->middleware(['demo_restriction']);

            Route::post("settings/time_slot_settings", [SettingController::class, 'storeTimeSlotConfig'])->name('time_slot_config.store')->middleware(['demo_restriction']);

            Route::get('settings/time_slot/list', [SettingController::class, 'timeSlotList'])->name('time_slots.list');

            Route::get('settings/time_slot/destroy/{id}', [SettingController::class, 'timeSlotDestroy'])->name('time_slot.destroy')->middleware(['demo_restriction']);

            Route::get('settings/time_slot/update_status/{id}', [SettingController::class, 'updateTimeSlotStatus'])->middleware(['demo_restriction']);

            Route::get("settings/currency_settings", [SettingController::class, 'currencySettings'])->name('currency_settings');

            Route::post("settings/store_currency_setting", [SettingController::class, 'storeCurrencySetting'])->name('currency_setting.store')->middleware(['demo_restriction'])->middleware('permissions:edit currency_setting');

            Route::post("settings/store_exchange_rate_aap_id", [SettingController::class, 'storeExchangeRateAapId'])->name('exchange_rate_aap_id.store')->middleware(['demo_restriction'])->middleware('permissions:edit currency_setting');

            Route::post("settings/set_default_currency", [SettingController::class, 'setDefaultCurrency'])->name('default_currency.set')->middleware(['demo_restriction'])->middleware('permissions:edit currency_setting');

            Route::get('currency/destroy/{id}', [SettingController::class, 'currencyDestroy'])->name('currency.destroy')->middleware(['demo_restriction'])->middleware('permissions:delete currency_setting');

            Route::get('currency/edit/{id}', [SettingController::class, 'editCurrency'])->name('currency.edit');

            Route::put('currency/update/{id}', [SettingController::class, 'updateCurrency'])->name('currency.update')->middleware(['demo_restriction'])->middleware('permissions:edit currency_setting');

            Route::get('settings/get_exchange_rates/{appId}', [SettingController::class, 'getExchangeRates']);

            Route::get('settings/update_exchange_rates/{app_id}', [SettingController::class, 'updateExchangeRates'])->middleware(['demo_restriction'])->middleware('permissions:edit currency_setting');

            Route::get("settings/notification_and_contact_settings", [SettingController::class, 'notificationAndContactSettings'])->name('notification_and_contact_settings');

            Route::get("settings/pusher_setting", [SettingController::class, 'pusherSetting'])->name('pusher_setting');

            Route::post("settings/pusher_setting", [SettingController::class, 'storePusherSetting'])->name('pusher_setting.store')->middleware(['demo_restriction'])->middleware('permissions:edit pusher_setting');

            Route::get("settings/s3_storage_setting", [SettingController::class, 's3StorageSetting'])->name('s3StorageSetting')->middleware(['demo_restriction']);

            Route::post("settings/s3_storage_setting", [SettingController::class, 'store3StorageSetting'])->name('s3StorageSetting.store')->middleware(['demo_restriction'])->middleware('permissions:edit storage_setting');

            Route::post("settings/notification_settings", [SettingController::class, 'storeNotificationSettings'])->name('notification_settings.store')->middleware(['demo_restriction'])->middleware('permissions:edit contact_setting');

            Route::post("settings/contact_us", [SettingController::class, 'storeContactUs'])->name('contact_us.store')->middleware(['demo_restriction'])->middleware('permissions:edit contact_setting');

            Route::post("settings/about_us", [SettingController::class, 'storeAboutUs'])->name('about_us.store')->middleware(['demo_restriction'])->middleware('permissions:edit contact_setting');

            Route::get(
                '/currency/list',
                [SettingController::class, 'currencyList']
            )->name('currency.list');

            Route::get('/currency/update_status/{id}', [SettingController::class, 'updateCurrencyStatus'])->middleware(['demo_restriction'])->middleware('permissions:edit currency_setting');

            // web settings

            Route::get('web_settings/', [SettingController::class, 'webSettings']);

            Route::post("settings/web_settings", [SettingController::class, 'storeWebSettings'])->name('web_settings.store')->middleware(['demo_restriction'])->middleware('permissions:edit web_general_setting');

            Route::get("web_settings/firebase", [SettingController::class, 'firebase'])->name('firebase');

            Route::get("web_settings/general_settings", [SettingController::class, 'general_settings'])->name('general_settings');

            Route::get("web_settings/pwa_settings", [SettingController::class, 'pwa_settings'])->name('pwa_settings');

            Route::post("settings/pwa_settings", [SettingController::class, 'storePwaSettings'])->name('pwa_settings.store')->middleware(['demo_restriction'])->middleware('permissions:edit pwa_settings');

            Route::post("settings/firebase_settings", [SettingController::class, 'storeFirebaseSettings'])->name('firebase_settings.store')->middleware(['demo_restriction'])->middleware('permissions:edit firebase_setting');

            // theme

            Route::get("web_settings/theme", [SettingController::class, 'theme'])->name('theme');

            Route::get("web_settings/themes/list", [SettingController::class, 'theme_list'])->name('admin.theme_list');

            // sms gateway

            Route::get("settings/sms_gateway", [SettingController::class, 'sms_gateway'])->name('sms_gateway');

            Route::post("/settings/store_sms_data", [SettingController::class, 'store_sms_data'])->name('store_sms_data.store')->middleware(['demo_restriction'])->middleware('permissions:edit sms_gateway_setting');

            // system policies

            Route::get("settings/system_policies", [SettingController::class, 'systemPolicies'])->name('system_policies');

            Route::post("settings/privacy_policy", [SettingController::class, 'storePrivacyPolicy'])->name('privacy_policy.store')->middleware(['demo_restriction'])->middleware('permissions:edit system_policies');

            Route::post("settings/terms_and_conditions", [SettingController::class, 'storeTermsAndCondition'])->name('terms_and_conditions.store')->middleware(['demo_restriction'])->middleware('permissions:edit system_policies');

            Route::post("settings/shipping_policy", [SettingController::class, 'storeShippingPolicy'])->name('shipping_policy.store')->middleware(['demo_restriction'])->middleware('permissions:edit system_policies');

            Route::post("settings/return_policy", [SettingController::class, 'storeReturnPolicy'])->name('return_policy.store')->middleware(['demo_restriction'])->middleware('permissions:edit system_policies');

            // admin , seller & delivery boy policies

            Route::get("settings/admin_and_seller_policies", [SettingController::class, 'adminAndSellerPolicies'])->name('admin_and_seller_policies');

            Route::post("settings/admin_privacy_policy", [SettingController::class, 'storeAdminPrivacyPolicy'])->name('admin_privacy_policy.store')->middleware(['demo_restriction'])->middleware('permissions:edit admin_policies');

            Route::post("settings/admin_terms_and_conditions", [SettingController::class, 'storeAdminTermsAndConditions'])->name('admin_terms_and_conditions.store')->middleware(['demo_restriction'])->middleware('permissions:edit admin_policies');

            Route::post("settings/seller_privacy_policy", [SettingController::class, 'storeSellerPrivacyPolicy'])->name('seller_privacy_policy.store')->middleware(['demo_restriction'])->middleware('permissions:edit admin_policies');

            Route::get("settings/seller_privacy_policy", [SettingController::class, 'sellerPrivacyPolicy'])->name('seller_privacy_policy.view')->middleware(['demo_restriction'])->middleware('permissions:edit admin_policies');

            Route::post("settings/seller_terms_and_conditions", [SettingController::class, 'storeSellerTermsAndConditions'])->name('seller_terms_and_conditions.store')->middleware(['demo_restriction'])->middleware('permissions:edit admin_policies');

            Route::get("settings/seller_terms_and_conditions", [SettingController::class, 'sellerTermsAndCondition'])->name('seller_terms_and_conditions.view')->middleware(['demo_restriction'])->middleware('permissions:edit admin_policies');

            // delivery boy policies

            Route::get("settings/delivery_boy_policies", [SettingController::class, 'deliveryBoyPolicies'])->name('delivery_boy_policies');

            Route::post("settings/delivery_boy_privacy_policy", [SettingController::class, 'storeDeliveryBoyPrivacyPolicy'])->name('delivery_boy_privacy_policy.store')->middleware(['demo_restriction'])->middleware('permissions:edit delivery_boy_policies');

            Route::post("settings/delivery_boy_terms_and_conditions", [SettingController::class, 'storeDeliveryBoyTermsAndConditions'])->name('delivery_boy_terms_and_conditions.store')->middleware(['demo_restriction'])->middleware('permissions:edit delivery_boy_policies');

            // brand

            Route::resource("brands", BrandController::class)->names([
                'index' => 'brands.index',
                'edit' => 'brands.edit',
            ])->except('show')->middleware('CheckDefaultStore');

            Route::post('brands', [BrandController::class, 'store'])->middleware(['demo_restriction'])->name('brands.store')->middleware('permissions:create brands');

            Route::get('/brand/update_status/{id}', [BrandController::class, 'update_status'])->middleware(['demo_restriction'])->middleware('permissions:edit brands');

            Route::get('/brands/list', [BrandController::class, 'list'])->name('brands.list');

            Route::get('brands/destroy/{id}', [BrandController::class, 'destroy'])->name('brands.destroy')->middleware(['demo_restriction'])->middleware('permissions:delete brands');

            Route::get('brands/bulk_upload', [BrandController::class, 'bulk_upload']);

            Route::post("brands/bulk_upload", [BrandController::class, 'process_bulk_upload'])->name('brands.bulk_upload')->middleware(['demo_restriction'])->middleware('permissions:create brands');

            Route::get('brands/edit/{id}', [BrandController::class, 'edit'])->name('brands.edit');

            Route::put('brands/update/{id}', [BrandController::class, 'update'])->middleware(['demo_restriction'])->middleware('permissions:edit brands');

            //taxes

            Route::resource("taxes", TaxController::class)->names([
                'index' => 'taxes.index',
                'edit' => 'taxes.edit',
            ])->except('show');

            Route::get('/tax/update_status/{id}', [TaxController::class, 'update_status'])->middleware(['demo_restriction'])->middleware('permissions:edit tax');

            Route::post('taxes', [TaxController::class, 'store'])->name('taxes.store')->middleware('permissions:create tax')->middleware(['demo_restriction']);
            Route::get(
                '/taxes/list',
                [TaxController::class, 'list']
            )->name('taxes.list');

            Route::get('tax/destroy/{id}', [TaxController::class, 'destroy'])->name('taxes.destroy')->middleware(['demo_restriction'])->middleware('permissions:delete tax');

            Route::get('tax/edit/{id}', [TaxController::class, 'edit'])->name('tax.edit');

            Route::put('tax/update/{id}', [TaxController::class, 'update'])->name('tax.update')->middleware(['demo_restriction'])->middleware('permissions:edit tax');

            Route::get('tax/get_taxes', [TaxController::class, 'getTaxes']);


            //promocode

            Route::resource("promo_codes", PromoCodeController::class)->names([
                'index' => 'promo_codes.index',
            ])->except('show')->middleware('CheckDefaultStore');
            Route::post('promo_codes', [PromoCodeController::class, 'store'])->name('promo_codes.store')->middleware(['demo_restriction'])->middleware('permissions:create promo_code');
            Route::get('/promo_code/update_status/{id}', [PromoCodeController::class, 'update_status'])->middleware(['demo_restriction'])->middleware('permissions:edit promo_code');

            Route::get(
                '/promo_codes/list',
                [PromoCodeController::class, 'list']
            )->name('promo_codes.list');

            Route::get('promo_codes/edit/{id}', [PromoCodeController::class, 'edit'])->name('promo_code.edit');

            Route::put('promo_codes/update/{id}', [PromoCodeController::class, 'update'])->name('promo_code.update')->middleware(['demo_restriction'])->middleware('permissions:edit promo_code');

            Route::get('promo_codes/destroy/{id}', [PromoCodeController::class, 'destroy'])->name('promo_codes.destroy')->middleware(['demo_restriction'])->middleware('permissions:delete promo_code');
        });

        //attributes

        Route::resource("admin/attributes", AttributeController::class)->names([
            'index' => 'admin.attributes.index',
            'edit' => 'admin.attributes.edit',
        ])->except('show')->middleware('CheckDefaultStore');
        Route::get(
            'admin/attributes/list',
            [AttributeController::class, 'list']
        )->name('admin.attributes.list');
        Route::post('admin/attributes', [AttributeController::class, 'store'])->name('admin.attributes.store')->middleware(['demo_restriction'])->middleware('permissions:create attributes');
        Route::get('/attribute/update_status/{id}', [AttributeController::class, 'update_status'])->middleware(['demo_restriction'])->middleware('permissions:edit attributes');
        Route::post('admin/attribute/getAttributes', [AttributeController::class, 'getAttributes']);

        Route::get('attributes/destroy/{id}', [AttributeController::class, 'destroy'])->name('attributes.destroy')->middleware(['demo_restriction'])->middleware('permissions:delete attributes');

        //products


        Route::resource("admin/products", ProductController::class)->names([
            'index' => 'admin.products.index',
            'edit' => 'admin.products.edit',
        ])->except('show')->middleware('CheckDefaultStore');

        Route::get(
            'admin/products/list',
            [ProductController::class, 'list']
        )->name('admin.products.list');
        Route::put('admin/products/update/{id}', [ProductController::class, 'update'])->name('admin.products.update')->middleware(['demo_restriction'])->middleware('permissions:edit product');
        Route::post('admin/products', [ProductController::class, 'store'])->name('admin.products.store')->middleware(['demo_restriction'])->middleware('permissions:create product');

        Route::get('admin/products/update_status/{id}', [ProductController::class, 'update_status'])->middleware(['demo_restriction'])->middleware('permissions:edit product');

        Route::get('admin/product/view_product/{id}', [ProductController::class, 'show'])->name('admin.product.show')->middleware('permissions:view product');

        Route::get('admin/products/destroy/{id}', [ProductController::class, 'destroy'])->name('admin.products.destroy')->middleware(['demo_restriction'])->middleware('permissions:delete product');

        Route::get('admin/products/fetch_attribute_values_by_id', [ProductController::class, 'fetchAttributeValuesById']);

        Route::get('admin/products/fetch_variants_values_by_pid', [ProductController::class, 'fetchVariantsValuesByPid']);

        Route::get('admin/products/get_variants_by_id', [ProductController::class, 'getVariantsById']);

        Route::get('admin/products/get_brands', [ProductController::class, 'getBrands']);

        Route::get('admin/products/fetch_attributes_by_id', [ProductController::class, 'fetchAttributesById']);

        Route::get('admin/products/get_countries', [ProductController::class, 'getCountries']);

        Route::get('admin/products/get_product_details', [ProductController::class, 'getProductdetails']);

        Route::get('admin/products/get_product_details_for_combo', [ProductController::class, 'getProductdetailsForCombo']);

        Route::get('admin/products/get_digital_product_data', [ProductController::class, 'getDigitalProductData']);

        Route::get('admin/products/manage_product', [ProductController::class, 'manageProduct'])->name('admin.products.manage_product');

        Route::post('admin/products/delete_image', [ProductController::class, 'deleteImage'])->middleware(['demo_restriction']);

        Route::get('admin/products/get_attributes', [ProductController::class, 'getAttributes']);

        Route::get('admin/product/product_bulk_upload', [ProductController::class, 'bulk_upload'])->name('admin.product_bulk_upload');

        Route::post("admin/product/bulk_upload", [ProductController::class, 'process_bulk_upload'])->name('admin.product.bulk_upload')->middleware(['demo_restriction'])->middleware('permissions:create product');

        Route::get("admin/product/change_variant_status", [ProductController::class, 'change_variant_status'])->name('admin.product.change_variant_status')->middleware(['demo_restriction'])->middleware('permissions:edit product');

        Route::get("admin/product/delete_variant", [ProductController::class, 'delete_variant'])->name('admin.product.delete_variant')->middleware(['demo_restriction'])->middleware('permissions:delete product');

        //seller

        Route::resource("admin/sellers", SellerController::class)->names([
            'index' => 'sellers.index',
        ])->except('show')->middleware('CheckDefaultStore');

        Route::get('admin/seller/create', [SellerController::class, 'create'])->name('admin.sellers.create');

        Route::post('admin/sellers', [SellerController::class, 'store'])->name('sellers.store')->middleware(['demo_restriction'])->middleware('permissions:create seller');

        Route::get('/sellers/list', [SellerController::class, 'list'])->name('sellers.list');

        Route::get('sellers/update_status/{id}', [SellerController::class, 'update_status'])->middleware(['demo_restriction'])->middleware('permissions:edit seller');

        Route::get('admin/sellers/destroy/{id}', [SellerController::class, 'destroy'])->name('admin.sellers.destroy')->middleware(['demo_restriction'])->middleware('permissions:delete seller');

        Route::get('admin/sellers/edit/{id}', [SellerController::class, 'edit'])->name('admin.sellers.edit');

        Route::put('admin/sellers/update/{id}', [SellerController::class, 'update'])->name('admin.sellers.update')->middleware(['demo_restriction'])->middleware('permissions:edit seller');

        Route::post("admin/sellers/get_seller_commission_data", [SellerController::class, 'getsellerCommissionData']);

        Route::get("admin/sellers/seller_wallet_transaction", [SellerController::class, 'sellerWallet'])->name('admin.sellers.sellerWallet');

        Route::get("admin/sellers/wallet_transactions_list", [SellerController::class, 'seller_wallet_transactions_list'])->name('admin.sellers.wallet_transactions_list');

        Route::get("admin/seller/get_seller_deliverable_type", [SellerController::class, 'get_seller_deliverable_type'])->name('admin.sellers.get_seller_deliverable_type');

        // Feature Section

        Route::resource("admin/feature_section", FeaturedSectionsController::class)->names([
            'index' => 'feature_section.index',
            'edit' => 'feature_section.edit',

        ])->except('show')->middleware('CheckDefaultStore');
        Route::get(
            'admin/feature_section/list',
            [FeaturedSectionsController::class, 'list']
        )->name('feature_section.list');

        Route::put('admin/feature_section/update/{id}', [FeaturedSectionsController::class, 'update'])->name('feature_section.update')->middleware(['demo_restriction'])->middleware('permissions:edit featured_section');

        Route::post("admin/feature_section", [FeaturedSectionsController::class, 'store'])->name('feature_section.store')->middleware(['demo_restriction'])->middleware('permissions:create featured_section');

        Route::get('admin/feature_section/destroy/{id}', [FeaturedSectionsController::class, 'destroy'])->name('feature_section.destroy')->middleware(['demo_restriction'])->middleware('permissions:delete featured_section');

        Route::get('admin/feature_section/section_order', [FeaturedSectionsController::class, 'sectionOrder'])->name('feature_section.section_order');

        Route::get('admin/feature_section/update_section_order', [FeaturedSectionsController::class, 'updateSectionOrder'])->name('feature_section.update_section_order');

        //Pickup loation

        Route::resource("admin/pickup_location", PickupLocationController::class)->names([
            'index' => 'admin.pickup_location.index',
        ])->except('show')->middleware('CheckDefaultStore');

        Route::get('admin/pickup_location/list', [PickupLocationController::class, 'list'])->name('admin.pickup_location.list');
        Route::get('admin/pickup_location/update_status/{id}', [PickupLocationController::class, 'update_status'])->middleware(['demo_restriction'])->middleware('permissions:edit pickup_location');
        Route::put('admin/pickup_location/update/{id}', [PickupLocationController::class, 'update'])->name('admin.pickup_location.update')->middleware(['demo_restriction'])->middleware('permissions:edit pickup_location');

        Route::get('admin/pickup_location/destroy/{id}', [PickupLocationController::class, 'destroy'])->name('admin.pickup_location.destroy')->middleware(['demo_restriction'])->middleware('permissions:delete pickup_location');
        Route::get('admin/pickup_location/edit/{id}', [PickupLocationController::class, 'edit'])->name('admin.pickup_location.edit');
        // Orders Section

        Route::resource("admin/orders", OrderController::class)->names([
            'index' => 'admin.orders.index',
            'edit' => 'admin.orders.edit',
        ])->except('show')->middleware('CheckDefaultStore');

        Route::get('admin/orders/order_tracking', [OrderController::class, 'order_tracking'])->name('admin.orders.order_tracking');
        Route::post('admin/orders/update_order_status', [OrderController::class, 'update_order_status'])->middleware(['demo_restriction'])->middleware('permissions:edit orders');
        Route::post('admin/orders/update_order_tracking', [OrderController::class, 'update_order_tracking'])->name('admin.orders.update_order_tracking')->middleware(['demo_restriction'])->middleware('permissions:edit orders');
        Route::post('admin/orders/create_shiprocket_order', [OrderController::class, 'create_shiprocket_order'])->name('admin.orders.create_shiprocket_order')->middleware(['demo_restriction'])->middleware('permissions:edit orders');
        Route::post('admin/orders/generate_awb', [OrderController::class, 'generate_awb'])->name('admin.orders.generate_awb')->middleware(['demo_restriction'])->middleware('permissions:edit orders');
        Route::post('admin/orders/send_pickup_request', [OrderController::class, 'send_pickup_request'])->name('admin.orders.send_pickup_request')->middleware(['demo_restriction'])->middleware('permissions:edit orders');
        Route::post('admin/orders/cancel_shiprocket_order', [OrderController::class, 'cancel_shiprocket_order'])->name('admin.orders.cancel_shiprocket_order')->middleware(['demo_restriction'])->middleware('permissions:edit orders');
        Route::post('admin/orders/generate_label', [OrderController::class, 'generate_label'])->name('admin.orders.generate_label')->middleware(['demo_restriction'])->middleware('permissions:edit orders');
        Route::post('admin/orders/generate_invoice', [OrderController::class, 'generate_invoice'])->name('admin.orders.generate_invoice')->middleware(['demo_restriction'])->middleware('permissions:edit orders');
        Route::get('admin/orders/get_order_tracking', [OrderController::class, 'get_order_tracking'])->name('admin.orders.get_order_tracking');

        Route::get('admin/orders/list', [OrderController::class, 'list'])->name('admin.orders.list');

        Route::get('admin/order_items', [OrderController::class, 'order_items'])->name('admin.order_items.index');

        Route::get('admin/orders/order_item_list', [OrderController::class, 'order_item_list'])->name('admin.orders.item_list');

        Route::get('admin/order_items/destroy/{id}', [OrderController::class, 'order_item_destroy'])->name('admin.order.items.destroy')->middleware(['demo_restriction'])->middleware('permissions:delete orders');

        Route::get('admin/order/destroy/{id}', [OrderController::class, 'destroy'])->name('admin.orders.destroy')->middleware(['demo_restriction'])->middleware('permissions:delete orders');

        Route::post('admin/orders/send_digital_product', [OrderController::class, 'send_digital_product'])->name('admin.orders.send_digital_product')->middleware(['demo_restriction'])->middleware('permissions:edit orders');

        Route::get('admin/orders/generat_invoice_PDF/{id}', [OrderController::class, 'generatInvoicePDF'])->name('admin.orders.generatInvoicePDF');

        Route::get('admin/orders/delete_receipt/{id}', [OrderController::class, 'destroyReceipt'])->name('admin.orders.delete_receipt')->middleware(['demo_restriction'])->middleware('permissions:edit orders');

        Route::post('admin/orders/update_receipt_status', [OrderController::class, 'update_receipt_status'])->name('admin.orders.update_receipt_status')->middleware(['demo_restriction'])->middleware('permissions:edit orders');

        // Return request

        Route::resource("admin/return_request", ReturnRequestController::class)->names([
            'index' => 'admin.return_request.index',
        ])->except('show');
        Route::post('admin/return_request/update', [ReturnRequestController::class, 'update'])->name('admin.return_request.update')->middleware(['demo_restriction']);
        Route::get('admin/return_request/list', [ReturnRequestController::class, 'list'])->name('admin.return_request.list');

        // Manage Stock

        Route::resource("admin/manage_stock", ManageStockController::class)->names([
            'index' => 'admin.manage_stock.index',
        ])->except('show')->middleware('CheckDefaultStore');


        Route::put('admin/manage_stock/update/{id}', [ManageStockController::class, 'update'])->name('admin.stock.update')->middleware(['demo_restriction'])->middleware('permissions:edit stock');

        Route::get('admin/manage_stock/list', [ManageStockController::class, 'list'])->name('admin.manage_stock.list');

        Route::get('admin/manage_stock/edit/{id}', [ManageStockController::class, 'edit'])->name('admin.stock.edit');

        // Manage Combo Stock


        Route::get('admin/manage_combo_stock', [ManageStockController::class, 'manage_combo_stock'])->name('admin.manage_combo_stock.index')->middleware('CheckDefaultStore');

        Route::put('admin/manage_combo_stock/update/{id}', [ManageStockController::class, 'combo_stock_update'])->name('admin.combo_stock.update')->middleware(['demo_restriction'])->middleware('permissions:edit combo_stock');

        Route::get('admin/manage_combo_stock/list', [ManageStockController::class, 'combo_stock_list'])->name('admin.manage_combo_stock.list');


        Route::get('admin/manage_combo_stock/edit/{id}', [ManageStockController::class, 'combo_stock_edit'])->name('admin.combo_stock.edit');

        // Payment request

        Route::resource("admin/payment_request", PaymentRequestController::class)->names([
            'index' => 'admin.payment_request.index',
        ])->except('show')->middleware('CheckDefaultStore');
        Route::post('admin/payment_request/update', [PaymentRequestController::class, 'update'])->name('admin.payment_request.update')->middleware(['demo_restriction'])->middleware('permissions:edit payment_request');
        Route::get('admin/payment_request/list', [PaymentRequestController::class, 'list'])->name('admin.payment_request.list');


        // slider

        Route::resource("admin/sliders", SliderController::class)->names([
            'index' => 'sliders.index',
            'edit' => 'sliders.edit',
        ])->except('show')->middleware('CheckDefaultStore');
        Route::post('admin/sliders', [SliderController::class, 'store'])->name('sliders.store')->middleware(['demo_restriction'])->middleware('permissions:create slider_images');
        Route::put('admin/sliders/update/{id}', [SliderController::class, 'update'])->name('sliders.update')->middleware(['demo_restriction'])->middleware('permissions:edit slider_images');
        Route::get(
            '/sliders/list',
            [SliderController::class, 'list']
        )->name('sliders.list');

        Route::get('sliders/destroy/{id}', [SliderController::class, 'destroy'])->name('sliders.destroy')->middleware(['demo_restriction'])->middleware('permissions:delete slider_images');

        //Chat

        Route::resource("admin/chat", MessagesController::class)->names([
            'index' => 'admin.chat.index',
        ]);

        //offers

        Route::resource("admin/offers", OfferController::class)->names([
            'index' => 'offers.index',
            'edit' => 'offers.edit',
        ])->except('show')->middleware('CheckDefaultStore');
        Route::put('admin/offers/update/{id}', [OfferController::class, 'update'])->name('offers.update')->middleware(['demo_restriction'])->middleware('permissions:edit offer_images');
        Route::get(
            '/offers/list',
            [OfferController::class, 'list']
        )->name('offers.list');
        Route::post('admin/offers', [OfferController::class, 'store'])->name('offers.store')->middleware(['demo_restriction'])->middleware('permissions:create offer_images');
        Route::get('offers/destroy/{id}', [OfferController::class, 'destroy'])->name('offers.destroy')->middleware(['demo_restriction'])->middleware('permissions:delete offer_images');

        //product faqs

        Route::resource("admin/product_faqs", ProductFaqController::class)->names([
            'index' => 'admin.product_faqs.index',
        ])->except('show')->middleware('CheckDefaultStore');
        Route::post('admin/product_faqs', [ProductFaqController::class, 'store'])->name('product_faqs.store')->middleware(['demo_restriction'])->middleware('permissions:create product_faq');
        Route::get('admin/product_faqs/list', [ProductFaqController::class, 'list'])->name('admin.product_faqs.list');


        Route::get('admin/product_faqs/destroy/{id}', [ProductFaqController::class, 'destroy'])->name('admin.product_faqs.destroy')->middleware(['demo_restriction'])->middleware('permissions:delete product_faq');
        Route::get('admin/product_faqs/edit/{id}', [ProductFaqController::class, 'edit']);
        Route::put('admin/product_faqs/update/{id}', [ProductFaqController::class, 'update'])->middleware(['demo_restriction'])->middleware('permissions:edit product_faq');

        //combo product faqs

        Route::resource("admin/combo_product_faqs", ComboProductFaqController::class)->names([
            'index' => 'admin.combo_product_faqs.index',
        ])->except('show')->middleware('CheckDefaultStore');
        Route::post('admin/combo_product_faqs', [ComboProductFaqController::class, 'store'])->name('combo_product_faqs.store')->middleware(['demo_restriction'])->middleware('permissions:create combo_product_faq');
        Route::get('admin/combo_product_faqs/list', [ComboProductFaqController::class, 'list'])->name('admin.combo_product_faqs.list');
        Route::get('admin/combo_product_faqs/destroy/{id}', [ComboProductFaqController::class, 'destroy'])->name('admin.combo_product_faqs.destroy')->middleware(['demo_restriction'])->middleware('permissions:delete combo_product_faq');
        Route::get('admin/combo_product_faqs/edit/{id}', [ComboProductFaqController::class, 'edit']);
        Route::put('admin/combo_product_faqs/update/{id}', [ComboProductFaqController::class, 'update'])->middleware(['demo_restriction'])->middleware('permissions:edit combo_product_faq');



        // media

        Route::post('/admin/media/upload', [MediaController::class, 'upload'])->name('admin.media.upload')->middleware(['demo_restriction'])->middleware('permissions:create media');

        Route::get('/admin/media', [MediaController::class, 'index'])->name('admin.media');

        Route::get('/admin/media/destroy/{id}', [MediaController::class, 'destroy'])->name('admin.media.destroy')->middleware(['demo_restriction'])->middleware('permissions:delete media');

        Route::get('/admin/storage_type', [MediaController::class, 'storage_type'])->name('admin.storage_type');

        Route::post("admin/media/storage_type", [MediaController::class, 'store_storage_type'])->name('admin.storage_type.store')->middleware(['demo_restriction'])->middleware('permissions:create storage_type');

        Route::get('/admin/storage_type/destroy/{id}', [MediaController::class, 'storage_type_destroy'])->name('admin.storage_type.destroy')->middleware('permissions:delete storage_type')->middleware(['demo_restriction']);

        Route::get('/storage_type/list', [MediaController::class, 'storage_type_list'])->name('admin.storage_type.list');

        Route::get('admin/storage_type/edit/{id}', [MediaController::class, 'storage_type_edit'])->name('admin.storage_type.edit')->middleware('permissions:edit storage_type');

        Route::put('admin/storage_type/update/{id}', [MediaController::class, 'storage_type_update'])->name('admin.storage_type.update')->middleware(['demo_restriction'])->middleware('permissions:edit storage_type');

        Route::get('admin/storage_type/set_default/{id}', [MediaController::class, 'set_default_storage_type'])->middleware(['demo_restriction'])->middleware('permissions:edit storage_type');


        // delivery_boys

        Route::resource("admin/delivery_boys", Delivery_boyController::class)->names([
            'index' => 'delivery_boys.index',
            'edit' => 'admin.delivery_boys.edit',
        ])->except('show')->middleware('CheckDefaultStore');

        Route::post("admin/delivery_boys", [Delivery_boyController::class, 'store'])->name('delivery_boys.store')->middleware(['demo_restriction'])->middleware('permissions:create delivery_boy');

        Route::put('/admin/delivery_boys/update/{id}', [Delivery_boyController::class, 'update'])->name('admin.delivery_boys.update')->middleware(['demo_restriction'])->middleware('permissions:edit delivery_boy');

        Route::get('/delivery_boys/list', [Delivery_boyController::class, 'list'])->name('delivery_boys.list');

        Route::get('delivery_boys/destroy/{id}', [Delivery_boyController::class, 'destroy'])->name('delivery_boys.destroy')->middleware(['demo_restriction'])->middleware('permissions:delete delivery_boy');

        Route::get('admin/delivery_boy/update_status/{id}', [Delivery_boyController::class, 'update_status'])->middleware(['demo_restriction'])->middleware('permissions:edit delivery_boy');

        // zipcodes

        Route::get("admin/area/zipcodes", [AreaController::class, 'displayZipcodes'])->name('admin.display_zipcodes');

        Route::post("admin/area/store_zipcodes", [AreaController::class, 'storeZipcodes'])->name('admin.zipcodes.store')->middleware(['demo_restriction'])->middleware('permissions:create zipcodes');

        Route::get(
            'admin/zipcodes/list',
            [AreaController::class, 'zipcodeList']
        )->name('admin.zipcodes.list');

        Route::get('zipcodes/destroy/{id}', [AreaController::class, 'distroyZipcode'])->name('admin.zipcodes.destroy')->middleware(['demo_restriction'])->middleware('permissions:delete zipcodes');

        Route::get("admin/area/get_zipcodes", [AreaController::class, 'get_zipcodes']);

        Route::get("admin/area/get_zipcode", [AreaController::class, 'getZipcodes']);

        Route::get('admin/zipcodes/edit/{id}', [AreaController::class, 'zipcodesEdit'])->name('admin.zipcodes.edit');

        Route::get('admin/zipcode/{id}', [AreaController::class, 'zipcodeShow']);

        Route::put('admin/zipcodes/update/{id}', [AreaController::class, 'zipcodesUpdate'])->name('admin.zipcodes.update')->middleware(['demo_restriction'])->middleware('permissions:edit zipcodes');

        //city

        Route::get("admin/area/city", [AreaController::class, 'displayCity'])->name('admin.display_city');

        Route::post("admin/area/city", [AreaController::class, 'storeCity'])->name('admin.city.store')->middleware('permissions:create city')->middleware(['demo_restriction'])->middleware('permissions:create city');

        Route::get(
            'admin/city/list',
            [AreaController::class, 'cityList']
        )->name('admin.city.list');

        Route::get("admin/area/get_cities", [AreaController::class, 'getCities']);

        Route::get('admin/city/destroy/{id}', [AreaController::class, 'cityDestroy'])->name('admin.city.destroy')->middleware(['demo_restriction'])->middleware('permissions:delete zipcodes');

        Route::get('admin/city/edit/{id}', [AreaController::class, 'cityEdit'])->name('admin.city.edit');

        Route::put('admin/city/update/{id}', [AreaController::class, 'cityUpdate'])->name('admin.city.update')->middleware(['demo_restriction'])->middleware('permissions:edit zipcodes');

        //area

        Route::get("admin/area/", [AreaController::class, 'displayArea'])->name('admin.display_area');

        Route::post("admin/area/", [AreaController::class, 'storeArea'])->name('admin.area.store')->middleware(['demo_restriction']);

        Route::get(
            'admin/area/list',
            [AreaController::class, 'areaList']
        )->name('admin.area.list');

        Route::get('area/destroy/{id}', [AreaController::class, 'areaDestroy'])->name('admin.area.destroy')->middleware(['demo_restriction']);

        Route::get('admin/area/location_bulk_upload', [AreaController::class, 'location_bulk_upload'])->name('admin.location_bulk_upload.index');

        Route::post("admin/area/location_bulk_upload", [AreaController::class, 'process_bulk_upload'])->name('location.bulk_upload')->middleware(['demo_restriction']);

        Route::get('admin/area/edit/{id}', [AreaController::class, 'areaEdit'])->name('admin.area.edit');

        Route::put('admin/area/update/{id}', [AreaController::class, 'areaUpdate'])->name('admin.area.update')->middleware(['demo_restriction']);

        // permissions and system users


        Route::get('admin/system_users', [UserPermissionController::class, 'index'])->name('admin.system_users.index');

        Route::get('admin/system_users/permissions/{id}', [UserPermissionController::class, 'index'])->name('system_users.permissions');

        Route::put('admin/system_users/permissions_update/{id}', [UserPermissionController::class, 'permissionsUpdate'])->name('system_users.permissions_update')->middleware(['demo_restriction'])->middleware('permissions:edit system_user');

        Route::post("admin/system_users/store", [UserPermissionController::class, 'store'])->name('system_users.store')->middleware(['demo_restriction'])->middleware('permissions:create system_user');

        Route::get(
            'admin/system_users/list',
            [UserPermissionController::class, 'systemUsersList']
        )->name('system_users.list');

        Route::get('admin/manage_system_users/', [UserPermissionController::class, 'manageSystemUsers'])->name('admin.manage_system_users');

        Route::get('admin/system_users/edit/{id}', [UserPermissionController::class, 'edit'])->name('system_user.edit');

        Route::get('system_users/destroy/{id}', [UserPermissionController::class, 'destroy'])->name('system_user.destroy')->middleware(['demo_restriction'])->middleware('permissions:delete system_user');

        // send notification

        Route::get('admin/send_notification/', [NotificationController::class, 'index'])->name('notifications.index');


        Route::post("admin/notification/store", [NotificationController::class, 'store'])->name('notifications.store')->middleware(['demo_restriction'])->middleware('permissions:create send_notification');

        Route::get('admin/notification/list', [NotificationController::class, 'list'])->name('admin.notifications.list');

        Route::get('admin/notification/seller_notification_list', [NotificationController::class, 'seller_notification_list'])->name('admin.seller_notifications.list');

        Route::get('notification/destroy/{id}', [NotificationController::class, 'destroy'])->name('admin.notification.destroy')->middleware(['demo_restriction'])->middleware('permissions:delete send_notification');

        Route::get('admin/send_seller_notification/', [NotificationController::class, 'seller_notification_index'])->name('seller_notifications.index');

        Route::get('admin/seller_email_notification/', [NotificationController::class, 'seller_email_notification_index'])->name('seller_email_notifications.index');

        Route::post("admin/email_notification/store", [NotificationController::class, 'store_email_notification'])->name('email_notifications.store')->middleware(['demo_restriction'])->middleware('permissions:create send_notification');

        Route::resource("admin/faq", FaqController::class)->names([
            'index' => 'faqs.index',
        ])->except('show');

        Route::get(
            '/faqs/list',
            [FaqController::class, 'list']
        )->name('faqs.list');

        Route::post('admin/faq', [FaqController::class, 'store'])->name('faqs.store')->middleware(['demo_restriction'])->middleware('permissions:create faq');

        Route::get('admin/faq/edit/{id}', [FaqController::class, 'edit'])->name('faqs.edit');

        Route::put('admin/faq/update/{id}', [FaqController::class, 'update'])->name('faqs.update')->middleware(['demo_restriction'])->middleware('permissions:edit faq');

        Route::get('faqs/destroy/{id}', [FaqController::class, 'destroy'])->name('faqs.destroy')->middleware(['demo_restriction'])->middleware('permissions:delete faq');

        // ticket system

        Route::resource("admin/tickets/ticket_types", TicketController::class)->names([
            'index' => 'ticket_types.index',
        ])->except('show');

        Route::post('admin/tickets/ticket_types', [TicketController::class, 'store'])->name('ticket_types.store')->middleware(['demo_restriction'])->middleware('permissions:create tickets');

        Route::get('/ticket_types/list', [TicketController::class, 'list'])->name('ticket_types.list');

        Route::get('admin/ticket_types/edit/{id}', [TicketController::class, 'edit'])->name('ticket_types.edit');

        Route::put('admin/ticket_types/update/{id}', [TicketController::class, 'update'])->name('ticket_types.update')->middleware(['demo_restriction'])->middleware('permissions:edit tickets');

        Route::get('tickets/ticket_types/{id}', [TicketController::class, 'destroy'])->name('ticket_types.destroy')->middleware(['demo_restriction'])->middleware('permissions:delete tickets');

        Route::get('tickets/tickets/{id}', [TicketController::class, 'tickets_destroy'])->name('tickets.destroy')->middleware(['demo_restriction'])->middleware('permissions:delete tickets');

        Route::get('admin/tickets/', [TicketController::class, 'viewTickets'])->name('admin.tickets.viewTickets');

        Route::get('admin/tickets/getTicketList', [TicketController::class, 'getTicketList'])->name('admin.tickets.getTicketList');

        Route::get('admin/tickets/get_ticket_messages', [TicketController::class, 'getMessages']);

        Route::post('admin/tickets/sendMessage', [TicketController::class, 'sendMessage'])->name('admin.tickets.sendMessage')->middleware(['demo_restriction'])->middleware('permissions:create tickets');

        Route::post('admin/tickets/editTicketStatus', [TicketController::class, 'editTicketStatus']);



        // custom message

        Route::resource("admin/custom_message", CustomMessageController::class)->names([
            'index' => 'admin.custom_message.index',
        ])->except('show');

        Route::post('admin/custom_message', [CustomMessageController::class, 'store'])->name('custom_message.store')->middleware(['demo_restriction'])->middleware('permissions:create custom_message');

        Route::get(
            '/custom_message/list',
            [CustomMessageController::class, 'list']
        )->name('custom_message.list');

        Route::get('admin/custom_message/edit/{id}', [CustomMessageController::class, 'edit'])->name('custom_message.edit');

        Route::put('admin/custom_message/update/{id}', [CustomMessageController::class, 'update'])->name('custom_message.update')->middleware(['demo_restriction'])->middleware('permissions:edit custom_message');

        Route::get('admin/custom_message/{id}', [CustomMessageController::class, 'destroy'])->name('custom_message.destroy')->middleware(['demo_restriction'])->middleware('permissions:delete custom_message');

        // customers

        Route::get('admin/customers/', [UserController::class, 'customers'])->name('admin.customers');

        Route::get('/customers/list', [UserController::class, 'getCustomersList'])->name('customers.list');

        Route::get('admin/customers/customers_addresses', [UserController::class, 'getCustomersAddresses'])->name('admin.customers.getCustomersAddresses');

        Route::get('/customers/getCustomersAddressesList', [UserController::class, 'getCustomersAddressesList'])->name('admin.customers.getCustomersAddressesList');

        Route::get('admin/customers/view_transactions', [UserController::class, 'viewTransactions'])->name('admin.customers.viewTransactions');

        Route::get('/customers/getTransactionList', [UserController::class, 'getTransactionList'])->name('admin.customers.getTransactionList');

        Route::post('/admin/customers/edit_transactions', [TransactionController::class, 'edit_transactions'])->name('admin.customers.edit_transactions')->middleware(['demo_restriction'])->middleware('permissions:edit customers');

        Route::get('/admin/customers/wallet_transaction', [UserController::class, 'walletTransaction'])->name('admin.customers.walletTransaction');

        Route::post('/admin/customers/updateCustomerWallet', [UserController::class, 'updateCustomerWallet'])->name('admin.customers.updateCustomerWallet')->middleware(['demo_restriction'])->middleware('permissions:edit customer_wallet_transaction');

        Route::get('/admin/customers/update_status/{id}', [UserController::class, 'update_status'])->middleware(['demo_restriction'])->middleware('permissions:edit customers');


        Route::get('admin/customers/{id}', [UserController::class, 'destroy'])->name('customers.destroy')->middleware(['demo_restriction'])->middleware('permissions:delete customers');

        // Route::resource("admin/store", StoreController::class)->names([
        //     'index' => 'admin.stores.index',
        // ])->except('show');

        // Route::post('admin/store', [StoreController::class, 'store'])->middleware(['demo_restriction'])->middleware('permissions:create store')->name('admin.stores.store');

        Route::get('admin/stores/list', [StoreController::class, 'list'])->name('admin.stores.list');

        Route::get('admin/stores/manage_store', [StoreController::class, 'manage_store'])->name('admin.stores.manage_store');

        Route::get('admin/store/edit/{id}', [StoreController::class, 'edit'])->name('admin.store.update');

        Route::get('admin/store/update_status/{id}', [StoreController::class, 'update_status'])->middleware(['demo_restriction'])->middleware('permissions:edit store');

        Route::get('admin/store/set_default_store/{id}', [StoreController::class, 'set_default_store'])->middleware(['demo_restriction'])->middleware('permissions:edit store');

        Route::put('/admin/store/update/{id}', [StoreController::class, 'update'])->middleware(['demo_restriction'])->middleware('permissions:edit store');

        Route::post('admin/set_store', function (Request $request) {

            session(['store_id' => $request->store_id]);
            session(['store_name' => $request->store_name]);
            session(['store_image' => $request->store_image]);
            return response()->json(['success' => true]);
        })->name('set_store');

        Route::get('admin/store/get_stores_list', [StoreController::class, 'get_stores_list']);


        // offer sliders

        Route::post("offers/offer_sliders", [OfferController::class, 'store_offer_slider'])->name('offer_sliders.store')->middleware(['demo_restriction'])->middleware('permissions:create offer_slider');

        Route::get('admin/offer_sliders', [OfferController::class, 'offer_slider'])->name('offer_sliders.index')->middleware('CheckDefaultStore');

        Route::get('admin/offers/offers_data', [OfferController::class, 'offer_data']);

        Route::get('admin/offers/offer_sliders', [OfferController::class, 'offer_sliders_list'])->name('admin.offer_sliders.list');

        Route::get('offer_sliders/destroy/{id}', [OfferController::class, 'offer_slider_destroy'])->name('admin.offer_slider.destroy')->middleware(['demo_restriction'])->middleware('permissions:delete offer_slider');

        Route::get('admin/offer_sliders/update_status/{id}', [OfferController::class, 'update_offer_slider_status'])->middleware(['demo_restriction'])->middleware('permissions:edit offer_slider');

        Route::get('offer_sliders/edit/{id}', [OfferController::class, 'offer_slider_edit'])->name('admin.offer_sliders.update');

        Route::put('admin/offer_sliders/update/{id}', [OfferController::class, 'offer_slider_update'])->middleware(['demo_restriction'])->middleware('permissions:edit offer_slider');

        // combo products attributes

        Route::resource("admin/combo_product_attributes", ComboProductAttributeController::class)->names([
            'index' => 'admin.combo_product_attributes.index',
        ])->except('show')->middleware('CheckDefaultStore');
        Route::get(
            'admin/combo_product_attributes/list',
            [ComboProductAttributeController::class, 'list']
        )->name('admin.combo_product_attributes.list');

        Route::post('admin/combo_product_attributes', [ComboProductAttributeController::class, 'store'])->name('admin.combo_product_attributes.store')->middleware(['demo_restriction'])->middleware('permissions:create combo_attributes');

        Route::get('combo_product_attributes/edit/{id}', [ComboProductAttributeController::class, 'edit'])->name('admin.combo_product_attributes.update');

        Route::get('admin/combo_product_attributes/update_status/{id}', [ComboProductAttributeController::class, 'update_status'])->middleware(['demo_restriction'])->middleware('permissions:edit combo_attributes');

        Route::post('admin/combo_product_attributes/getAttributes', [ComboProductAttributeController::class, 'getAttributes']);

        Route::get('combo_product_attributes/destroy/{id}', [ComboProductAttributeController::class, 'destroy'])->name('admin.combo_product_attributes.destroy')->middleware('permissions:delete combo_attributes')->middleware(['demo_restriction']);

        Route::put('/admin/combo_product_attributes/update/{id}', [ComboProductAttributeController::class, 'update'])->middleware(['demo_restriction'])->middleware('permissions:edit combo_attributes');

        //combo products

        Route::resource("admin/combo_products", ComboProductController::class)->names([
            'index' => 'admin.combo_products.index',
            'edit' => 'admin.combo_products.edit',
            // 'update' => 'admin.combo_products.update',

        ])->except('show')->middleware('CheckDefaultStore');

        Route::get('admin/combo_products/destroy/{id}', [ComboProductController::class, 'destroy'])->name('admin.combo_products.destroy')->middleware(['demo_restriction'])->middleware('permissions:delete combo_product');

        Route::post("admin/combo_products/store", [ComboProductController::class, 'store'])->name('admin.combo_products.store')->middleware(['demo_restriction'])->middleware('permissions:create combo_product');

        Route::get('admin/combo_products/manage_product', [ComboProductController::class, 'manageProduct'])->name('admin.combo_products.manage_product');

        Route::put('admin/combo_products/update/{id}', [ComboProductController::class, 'update'])->middleware(['demo_restriction'])->middleware('permissions:edit combo_product')->name('admin.combo_products.update');

        Route::get(
            'admin/combo_products/list',
            [ComboProductController::class, 'list']
        )->name('admin.combo_products.list');

        Route::get('admin/combo_products/update_status/{id}', [ComboProductController::class, 'update_status'])->middleware(['demo_restriction'])->middleware('permissions:edit combo_product');

        Route::get('admin/combo_products/fetch_attributes_by_id', [ComboProductController::class, 'fetchAttributesById']);

        Route::get('admin/combo_products/get_product_details', [ComboProductController::class, 'getProductdetails']);

        Route::get('admin/combo_products/view_product/{id}', [ComboProductController::class, 'show'])->name('admin.combo_products.show')->middleware('permissions:view combo_product');

        Route::get('admin/combo_product/product_bulk_upload', [ComboProductController::class, 'bulk_upload'])->name('admin.combo.product.bulk_upload');

        Route::post("admin/combo_product/bulk_upload", [ComboProductController::class, 'process_bulk_upload'])->name('admin.combo.product.process_bulk_upload')->middleware(['demo_restriction'])->middleware('permissions:create combo_product');

        // delivery boy cash collection

        Route::get('admin/delivery_boys/manage_cash', [CashCollectionController::class, 'index'])->name('admin.get_cash_collection.index');

        Route::get('admin/delivery_boys/get_cash_collection', [CashCollectionController::class, 'list'])->name('admin.get_cash_collection');

        Route::get('admin/delivery_boys/getDeliveryBoys', [CashCollectionController::class, 'getDeliveryBoys'])->name('admin.cash_collection.getDeliveryBoys');

        Route::post("admin/delivery_boys/manage_cash_collection", [Delivery_boyController::class, 'manage_cash_collection'])->name('admin.manage_cash_collection')->middleware(['demo_restriction'])->middleware('permissions:edit delivery_boy_cash_collection');

        //fund transfer

        Route::post('admin/fund_transfer/add_fund_transfer', [FundTransferController::class, 'store'])->name('admin.add_fund_transfer')->middleware(['demo_restriction'])->middleware('permissions:create delivery_boy');

        Route::get('admin/delivery_boys/fund_transfers', [FundTransferController::class, 'index'])->name('admin.delivery_boys.fund_transfers.index');

        Route::get(
            'admin/fund_transfers/list',
            [FundTransferController::class, 'list']
        )->name('admin.fund.transfer.list');

        // language

        Route::get("admin/settings/language", [LanguageController::class, 'index'])->name('language.index');

        Route::post('admin/settings/language', [LanguageController::class, 'store'])->name('language.store')->middleware(['demo_restriction']);

        Route::put("admin/settings/languages/savelabel", [LanguageController::class, 'savelabel'])->name('savelabel')->middleware(['demo_restriction']);

        Route::get('admin/settings/languages/change', [LanguageController::class, 'change'])->name('changeLang');

        Route::get("admin/settings/set-language/{locale}", [LanguageController::class, 'setLanguage'])->name('admin.set-language');

        // Front Language
        Route::get("admin/web_settings/language", [FrontLanguageController::class, 'index'])->name('web_language');

        Route::post('admin/web_settings/language', [FrontLanguageController::class, 'store'])->name('web_language.store')->middleware(['demo_restriction']);

        Route::put("admin/web_settings/languages/savelabel", [FrontLanguageController::class, 'savelabel'])->name('savelabel')->middleware(['demo_restriction']);

        Route::get('admin/web_settings/languages/change', [FrontLanguageController::class, 'change'])->name('changeLang');

        Route::get("admin/web_settings/set-language/{locale}", [FrontLanguageController::class, 'setLanguage'])->name('front.set-language');

        // delete selected data routes

        Route::delete('/categories/delete', [CategoryController::class, 'delete_selected_data'])->name('categories.delete')->middleware(['demo_restriction']);

        Route::delete('/categories_sliders/delete', [CategoryController::class, 'delete_selected_slider_data'])->name('categories_sliders.delete')->middleware(['demo_restriction']);

        Route::delete('/brands/delete', [BrandController::class, 'delete_selected_data'])->name('brands.delete')->middleware(['demo_restriction']);

        Route::delete('/sellers/delete', [SellerController::class, 'delete_selected_data'])->name('sellers.delete')->middleware(['demo_restriction']);

        Route::delete('/taxes/delete', [TaxController::class, 'delete_selected_data'])->name('taxes.delete')->middleware(['demo_restriction']);

        Route::delete('/products/delete', [ProductController::class, 'delete_selected_data'])->name('products.delete')->middleware(['demo_restriction']);

        Route::delete('/faqs/delete', [ProductFaqController::class, 'delete_selected_data'])->name('faqs.delete')->middleware(['demo_restriction']);

        Route::delete('/combo_products/delete', [ComboProductController::class, 'delete_selected_data'])->name('combo_products.delete')->middleware(['demo_restriction']);

        Route::delete('/combo_faqs/delete', [ComboProductFaqController::class, 'delete_selected_data'])->name('combo_faqs.delete')->middleware(['demo_restriction']);

        Route::delete('/blog_categories/delete', [BlogController::class, 'delete_selected_data'])->name('blog_categories.delete')->middleware(['demo_restriction']);

        Route::delete('/blogs/delete', [BlogController::class, 'delete_selected_blog_data'])->name('blogs.delete')->middleware(['demo_restriction']);

        Route::delete('/sliders/delete', [SliderController::class, 'delete_selected_data'])->name('sliders.delete')->middleware(['demo_restriction']);

        Route::delete('/offers/delete', [OfferController::class, 'delete_selected_data'])->name('offers.delete')->middleware(['demo_restriction']);

        Route::delete('/offer_sliders/delete', [OfferController::class, 'delete_selected_slider_data'])->name('offer_sliders.delete')->middleware(['demo_restriction']);

        Route::delete('/promo_codes/delete', [PromoCodeController::class, 'delete_selected_data'])->name('promo_codes.delete')->middleware(['demo_restriction']);

        Route::delete('/ticket_type/delete', [TicketController::class, 'delete_selected_data'])->name('ticket_type.delete')->middleware(['demo_restriction']);

        Route::delete('/tickets/delete', [TicketController::class, 'delete_selected_ticket_data'])->name('tickets.delete')->middleware(['demo_restriction']);

        Route::delete('/featured_sections/delete', [FeaturedSectionsController::class, 'delete_selected_data'])->name('featured_sections.delete')->middleware(['demo_restriction']);

        Route::delete('/customers/delete', [UserController::class, 'delete_selected_data'])->name('customers.delete')->middleware(['demo_restriction']);

        Route::delete('/delivery_boys/delete', [Delivery_boyController::class, 'delete_selected_data'])->name('delivery_boys.delete')->middleware(['demo_restriction'])->middleware(['demo_restriction']);

        Route::delete('/faqs/delete', [FaqController::class, 'delete_selected_data'])->name('faqs.delete')->middleware(['demo_restriction']);

        Route::delete('/notifications/delete', [NotificationController::class, 'delete_selected_data'])->name('notifications.delete')->middleware(['demo_restriction']);

        Route::delete('/custom_messages/delete', [CustomMessageController::class, 'delete_selected_data'])->name('custom_messages.delete')->middleware(['demo_restriction']);

        Route::delete('/zipcodes/delete', [AreaController::class, 'delete_selected_data'])->name('zipcodes.delete')->middleware(['demo_restriction']);

        Route::delete('/cities/delete', [AreaController::class, 'delete_selected_city_data'])->name('cities.delete')->middleware(['demo_restriction']);

        Route::delete('/system_users/delete', [UserPermissionController::class, 'delete_selected_data'])->name('system_users.delete')->middleware(['demo_restriction']);

        // zones

        Route::resource("admin/zones", ZoneController::class)->names([
            'index' => 'admin.zones.index',
        ])->except('show');
        Route::post('admin/zones', [ZoneController::class, 'store'])->name('admin.zones.store')->middleware(['demo_restriction'])->middleware('permissions:create zones');
        Route::put('admin/zones/update/{id}', [ZoneController::class, 'update'])->name('zones.update')->middleware(['demo_restriction'])->middleware('permissions:edit zones');
        Route::get(
            '/zones/list',
            [ZoneController::class, 'list']
        )->name('admin.zones.list')->middleware(['demo_restriction'])->middleware('permissions:view zones');
        Route::get('admin/zones/update_status/{id}', [ZoneController::class, 'update_status'])->middleware(['demo_restriction'])->middleware('permissions:edit zones');
        Route::get('zones/destroy/{id}', [ZoneController::class, 'destroy'])->name('admin.zones.destroy')->middleware(['demo_restriction'])->middleware('permissions:delete zones');
        Route::delete('/zones/delete', [ZoneController::class, 'delete_selected_data'])->name('zones.delete')->middleware(['demo_restriction']);
        Route::get('admin/zones/zones_data', [ZoneController::class, 'zone_data']);
        Route::get('admin/zones/seller_zones_data', [ZoneController::class, 'seller_zones_data']);

        Route::get('admin/zones/edit/{id}', [ZoneController::class, 'edit'])->name('admin.zones.edit');

        Route::get('admin/reports/sales_reports', [ReportController::class, 'index'])->name('admin.sales_reports.index');
        Route::get('admin/reports/sales_report_list', [ReportController::class, 'list'])->name('admin.sales_reports.list');

        Route::get('/admin/settings/manage_language', [LanguageController::class, 'manageLanguage'])->name('manage_language.index');

        Route::get('/admin/settings/manage_web_language', [FrontLanguageController::class, 'manageLanguage'])->name('front_manage_language.index');

        Route::get('/admin/languages/list', [LanguageController::class, 'list'])->name('languages.list');

        Route::get('/languages/{id}/edit', [LanguageController::class, 'edit'])->name('languages.edit');
        Route::delete('/languages/{id}', [LanguageController::class, 'delete'])->name('languages.destroy')->middleware(['demo_restriction']);
        Route::put('/languages/update/{id}', [LanguageController::class, 'update'])->name('languages.update')->middleware(['demo_restriction']);

        Route::get('admin/language/bulk_translation_upload', [LanguageController::class, 'bulk_upload'])->name('translation_bulk_upload.index')->middleware('demo_restriction');
        Route::post("admin/language/bulk_upload", [LanguageController::class, 'process_bulk_upload'])->name('admin.translation_bulk_upload')->middleware(['demo_restriction']);
        Route::get('admin/export/translation_csv', [LanguageController::class, 'export_translation_csv'])->name('admin.export_translation_csv');

        Route::get('/admin/download-language-labels', function () {
            $path = storage_path('app/public/language_labels.php');

            if (!file_exists($path)) {
                abort(404, 'File not found.');
            }

            return response()->download($path, 'language_labels.php');
        })->name('admin.download-language-labels');

        Route::get('/admin/download-web-language-labels', function () {
            $path = storage_path('app/public/web_language_labels.php');

            if (!file_exists($path)) {
                abort(404, 'File not found.');
            }

            return response()->download($path, 'web_language_labels.php');
        })->name('admin.web-download-language-labels');

        Route::get('/admin/download-language-sample-file', function () {
            $path = storage_path('app/public/admin_labels.php');

            if (!file_exists($path)) {
                abort(404, 'File not found.');
            }

            return response()->download($path, 'admin_labels.php');
        })->name('admin.download-language-sample-file');

        Route::get('/admin/download-web-language-sample-file', function () {
            $path = storage_path('app/public/front_messages.php');

            if (!file_exists($path)) {
                abort(404, 'File not found.');
            }

            return response()->download($path, 'front_messages.php');
        })->name('admin.web-download-language-sample-file');

        Route::get('/download-language-file/{language_code}', [LanguageController::class, 'downloadLanguageFile'])->name('download.language.file');

        Route::get('/download-web-language-file/{language_code}', [FrontLanguageController::class, 'downloadLanguageFile'])->name('web.download.language.file');

    }

);
