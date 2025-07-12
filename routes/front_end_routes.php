<?php

use App\Livewire\Home;
use App\Livewire\Test;
use App\Livewire\Pages\Faq;
use Illuminate\Http\Request;
use App\Livewire\Onboard\Onboard;
use App\Livewire\Compare\View;
use App\Livewire\Cart\Checkout;
use App\Livewire\Pages\AboutUs;
use App\Livewire\Pages\ContactUs;
use App\Livewire\Payments\Status;
use App\Livewire\MyAccount\Wallet;
use App\Livewire\Products\Details;
use App\Livewire\Products\Listing;
use App\Livewire\Products\Reviews;
use App\Http\Controllers\Customers;
use App\Livewire\MyAccount\Profile;
use App\Livewire\MyAccount\Support;
use App\Livewire\Pages\ReturnPolicy;
use App\Livewire\MyAccount\Addresses;
use App\Livewire\MyAccount\Dashboard;
use App\Livewire\MyAccount\Favorites;
use App\Livewire\Pages\PrivacyPolicy;
use Illuminate\Support\Facades\Route;
use App\Livewire\Offers\OffersSection;
use App\Livewire\Pages\ShippingPolicy;
use App\Http\Controllers\CartController;
use App\Livewire\MyAccount\Transactions;
use App\Livewire\RegisterAndLogin\Login;
use App\Livewire\MyAccount\Notifications;
use App\Livewire\Pages\TermAndConditions;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\ProductController;
use App\Livewire\RegisterAndLogin\Register;
use App\Http\Controllers\PaymentsController;
use App\Livewire\Cart\Listing as CartListing;
use App\Http\Controllers\NotificationsController;
use App\Livewire\Brands\Listing as BrandsListing;
use App\Livewire\Orders\Details as OrdersDetails;
use App\Livewire\Orders\Listing as OrdersListing;
use App\Livewire\RegisterAndLogin\ForgetPassword;
use App\Livewire\Sellers\Details as SellersDetails;
use App\Livewire\Sellers\Listing as SellersListing;
use App\Http\Controllers\Admin\Webhook as AdminWebhook;
use App\Http\Controllers\AuthController;
use App\Livewire\Categories\Listing as CategoriesListing;
use App\Http\Controllers\OrderInvoice;
use App\Http\Controllers\SettingController as ControllersSettingController;
use App\Http\Controllers\UserController;
use App\Livewire\Blogs\Details as BlogsDetails;
use App\Livewire\Blogs\Listing as BlogsListing;
use App\Livewire\MyAccount\LiveChat;
use App\Livewire\Products\ComboProductDetails;
use App\Livewire\Products\ComboProductListing;
use App\Livewire\Wizard\WelcomeWizard;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::post('cart/cart-sync', [CartController::class, 'cart_sync'])->name('cart-sync');
Route::middleware(['auth'])->group(function () {
    // cart
    Route::get('/cart', CartListing::class)->name('cart');
    Route::post('cart/add-to-cart', [CartController::class, 'add_to_cart'])->name('add-to-cart');
    Route::post('cart/manage-cart', [CartController::class, 'manage_cart'])->name('manage-cart');
    Route::post('cart/remove-from-cart', [CartController::class, 'removeFromCart'])->name('remove-from-cart');
    Route::get('/cart/checkout', Checkout::class)->name('cart.checkout');
    Route::post('/cart/place_order', [CartController::class, 'place_order'])->name('cart.place_order');

    //wallet
    Route::post('/wallet/refill', [WalletController::class, 'refill'])->name('wallet.refill');
    Route::post('/wallet/withdrawal', [WalletController::class, 'withdrawal'])->name('wallet.withdrawal');


    Route::post('/payments/phonepe', [PaymentsController::class, 'phonepe'])->name('payments.phonepe');
    Route::post('/payments/stripe', [PaymentsController::class, 'stripe'])->name('payments.stripe');
    Route::get('/payments/stripe-response', [PaymentsController::class, 'stripe_response'])->name('payments.stripe_response');
    Route::post('/payments/razorpay', [PaymentsController::class, 'razorpay'])->name('payments.razorpay');

    // orders
    Route::get('/orders', OrdersListing::class)->name('orders');
    Route::get('/orders/{id}', OrdersDetails::class)->name('orders.details');

    Route::get('/orders/{id}/invoice', [OrderInvoice::class, 'index'])->name('orders.invoice');

    Route::get('/orders/generat_invoice_PDF/{id}', [OrderInvoice::class, 'generatInvoicePDF'])->name('front_end.orders.generatInvoicePDF');

    Route::post('/orders/update-order-item-status', [OrdersDetails::class, 'update_order_item_status'])->name('orders.update-order-item-status');

    // MyAccount
    Route::get('/my-account', Dashboard::class)->name('my-account');
    Route::get('/my-account/addresses', Addresses::class)->name('my-account.addresses');
    Route::post('addresses/add_address', [Addresses::class, 'add_address'])->name('address.add_address');
    Route::post('addresses/set_default', [Addresses::class, 'setDefault'])->name('address.set_default');
    Route::get('my-account/addresses/edit_address', [Addresses::class, 'edit_address'])->name('address.edit_address');
    Route::post('addresses/delete_address/{id}', [Addresses::class, 'deleteAddress'])->name('address.delete_address');
    Route::get('/my-account/favorites', Favorites::class)->name('my-account.favorites');
    Route::get('/my-account/profile', Profile::class)->name('my-account.profile');

    Route::post('/my-account/profile_update', [Profile::class, 'Update_profile'])->name('profile.update');
    Route::post('/my-account/change-password', [Profile::class, 'Update_password'])->name('password.update');
    Route::get('/my-account/get_Countries', [Profile::class, 'get_Countries'])->name('my-account.get_countries');
    Route::get('/my-account/get_Cities', [Profile::class, 'get_Cities'])->name('my-account.get_cities');

    Route::get('/my-account/wallet_transactions', [Customers::class, 'getUserTransactionList'])->name('my-account.user_wallet_transactions');
    Route::get('/my-account/wallet_withdrawal_request', [Customers::class, 'wallet_withdrawal_request'])->name('my-account.wallet_withdrawal_request');
    Route::get('/my-account/get_transaction', [Customers::class, 'get_transaction'])->name('my-account.get_transaction');
    Route::get('/my-account/get_notifications', [NotificationsController::class, 'get_notifications'])->name('my-account.get_notifications');

    Route::get('/my-account/transactions', Transactions::class)->name('my-account.transactions');
    Route::get('/my-account/wallet', Wallet::class)->name('my-account.wallet');
    Route::get('/my-account/notifications', Notifications::class)->name('my-account.notifications');

    Route::get('/my-account/support', Support::class)->name('my-account.support');

    Route::get('/my-account/live-customer-support', LiveChat::class)->name('my-account.livechat');
    Route::post('/my-account/support/add-ticket', [Support::class, 'add_ticket'])->name('my-account.support.add-ticket');
    Route::post('/my-account/support/get-ticket', [Support::class, 'get_ticket_by_id'])->name('my-account.support.get-ticket');

    Route::get('/login/logout', [UserController::class, 'web_logout'])->name('logout');
});
Route::middleware(['guest'])->group(function () {
    //register & login
    Route::get('/onboard', Onboard::class)->name('onboard');
    Route::get('/register/supplier', WelcomeWizard::class)->name('register.supplier');
    
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
    Route::post('/register/submit', [Register::class, "store"])->name('register.submit');
    Route::post('/register/check-number', [Register::class, "check_mobile_number"])->name('register.check_number');
    Route::get('/password-recovery', ForgetPassword::class)->name('password-recovery');
    Route::post('/password-recovery/check-number', [ForgetPassword::class, 'check_number'])->name('password-recovery.check_number');
    Route::post('/password-recovery/set-new-password', [ForgetPassword::class, 'new_password'])->name('password-recovery.new-password');
    Route::get('google', function () {

        return view('googleAuth');
    });

    Route::get('auth/google', [Register::class, 'redirectToGoogle'])->name('redirect-to-google');

    Route::get('auth/google/callback', [Register::class, 'handleGoogleCallback'])->name('handle-google-callback');

    Route::get('auth/facebook', [Register::class, 'redirectToFacebook'])->name('redirect-to-facebook');

    Route::get('auth/facebook/callback', [Register::class, 'handleFacebookCallback'])->name('handle-facebook-callback');
});
Route::get('settings/get-firebase-credentials', [ControllersSettingController::class, 'getFirebaseCredentials'])->name('getFirebaseCredentials');

Route::post('auth/send_otp', [AuthController::class, 'send_otp'])->name('auth.send_otp');
Route::post('auth/verify_otp', [AuthController::class, 'verify_otp'])->name('auth.verify_otp');

Route::any('/payments/response', Status::class)->name('payments.payment_response');

Route::any('/webhook/phonepe_webhook', [AdminWebhook::class, 'phonepe_webhook'])->name('payments.phonepe_webhook');

Route::any('/webhook/paypal_webhook', [AdminWebhook::class, 'paypal_webhook'])->name('payments.paypal_webhook');
Route::any('/webhook/paystack_webhook', [AdminWebhook::class, 'paystack_webhook'])->name('payments.paystack_webhook');
//Route::any('/webhook/stripe_webhook', [AdminWebhook::class, 'stripe_webhook'])->name('payments.stripe_webhook');

Route::any('admin/webhook/razorpay-webhook', [AdminWebhook::class, 'razorpay_webhook'])->name('payments.razorpay_webhook');

Route::post('/pre-payment-setup', [CartController::class, 'pre_payment_setup'])->name('pre_payment_setup');

Route::get('/', Home::class)->name('home');

Route::post('set_store', function (Request $request) {
    session(['store_id' => $request->store_id]);
    session(['store_name' => $request->store_name]);
    session(['store_image' => $request->store_image]);
    session(['store_slug' => $request->store_slug]);
    $request->session()->put('show_store_popup', false);
    return response()->json(['success' => true]);
})->name('set_store');


// products
Route::get('/products', Listing::class)->name('products');
Route::get('/section/{section}/{slug}/products', Listing::class)->name('section.products');
Route::get('/products/{slug}', Details::class)->name('products.details');
Route::get('/products/{slug}/reviews', Reviews::class)->name('products.reviews');

// combo product
Route::get('/combo-products', ComboProductListing::class)->name('combo-products');
Route::get('/combo-products/{slug}', ComboProductDetails::class)->name('combo-products.details');
Route::get('/section/{section}/{slug}/combo-products', ComboProductListing::class)->name('section.combo-products');


Route::post('/check-product-deliverability', [Details::class, 'check_product_deliverability'])->name('check-product-deliverability');

// categories
Route::get('/categories', CategoriesListing::class)->name('categories');
Route::get('/categories/{slug}/products', Listing::class)->name('categories.products');

// compare
Route::get('/compare', View::class)->name('compare');

//pages
Route::get('/return-policy', ReturnPolicy::class)->name('return_policy');
Route::get('/shipping-policy', ShippingPolicy::class)->name('shipping_policy');
Route::get('/term-and-conditions', TermAndConditions::class)->name('term_and_conditions');
Route::get('/privacy-policy', PrivacyPolicy::class)->name('privacy_policy');
Route::get('/about-us', AboutUs::class)->name('about_us');
Route::get('/faqs', Faq::class)->name('faqs');
Route::get('/contact-us', ContactUs::class)->name('contact_us');
Route::post('/contact-us/send_contact_us_email', [ContactUs::class, 'send_contact_us_email'])->name('contact_us.send_contact_us_email');
// offers
Route::get('/offers', OffersSection::class)->name('offers');

// brands
Route::get('/brands', BrandsListing::class)->name('brands');

// sellers
Route::get('/sellers', SellersListing::class)->name('sellers');
Route::get('/sellers/{slug}', SellersDetails::class);

// payments
Route::get('/payments', Status::class)->name('payments');
Route::post('product/add-to-favorite', [ProductController::class, 'add_to_favorites']);
Route::post('product/remove-from-favorite', [ProductController::class, 'remove_from_favorite']);
Route::post('product/add-to-compare', [View::class, 'add_to_compare']);

// blogs
Route::get('/blogs', BlogsListing::class)->name('blogs');
Route::get('/blogs/{slug}', BlogsDetails::class)->name("blog.details");
