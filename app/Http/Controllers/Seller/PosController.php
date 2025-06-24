<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Admin\AddressController;
use App\Models\Pos;
use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Location;
use App\Models\Modifier;
use App\Models\CartAddOn;
use App\Models\Order_item;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\Product_variants;
use App\Models\User_transaction;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Collection;
use App\Http\Controllers\Partner\CategoryController;
use App\Http\Controllers\Partner\UserTransactionController;
use App\Models\Address;
use App\Models\ComboProduct;
use App\Models\OrderItems;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $user_id = Auth::user()->id;

        $seller_id = Seller::where('user_id', $user_id)->value('id');

        $categories = getSellerCategories($seller_id);

        $currency = Currency::where('is_default', 1)->value('symbol');

        $countries = fetchdetails('countries', '', 'name');

        $zipcodes = fetchdetails('zipcodes', '', 'zipcode');

        $search = trim($request->input('search', ''));

        $users = User::where('role_id', 2)
            ->where(function ($query) use ($search) {
                $query->where('username', 'LIKE', '%' . $search . '%')
                    ->orWhere('mobile', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            })
            ->get();

        return view('seller.pages.forms.pos', ['categories' => $categories, 'zipcodes' => $zipcodes, 'users' => $users, 'currency' => $currency, 'countries' => $countries]);
    }

    public function register_user(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'password' => 'required|string',
            'mobile' => 'required|min:5|unique:users,mobile',
            'country' => 'required',
            'state' => 'required',
            'city' => 'required',
            'address' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->all(),
                'data' => [],
            ]);
        } else {
            $mobile = $request->input('mobile');
            $username = $request->input('name');
            $password = bcrypt($request->input('password'));

            $user = User::create([
                'mobile' => $mobile,
                'username' => $username,
                'password' => $password,
                'role_id' => 2,
            ]);

            User::where('mobile', $mobile)->update(['active' => 1]);

            Address::create([
                'user_id' => $user->id,
                'mobile' => $mobile,
                'name' => $username,
                'type' => 'Home ',
                'city' => $request->input('city'),
                'state' => $request->input('state'),
                'country' => $request->input('country'),
                'address' => $request->input('address'),
            ]);

            if ($user) {
                $error = false;
                $message = 'Registered Successfully';
                return response()->json([
                    'error' => $error,
                    'message' => $message,
                ]);
            }
        }
    }

    public function get_users(Request $request)
    {
        $search = trim($request->input('search', ''));

        $users = User::where('role_id', 2)->where('active', 1)
            ->where(function ($query) use ($search) {
                $query->where('username', 'LIKE', '%' . $search . '%')
                    ->orWhere('mobile', 'like', '%' . $search . '%');
            })
            ->get();



        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'id' => $user->id,
                'text' => $user->username . ' | ' . $user->mobile . ' | ' . $user->email,
                'mobile' => $user->mobile,
                'email' => $user->email,
                'username' => $user->username,
            ];
        }

        return $data;
    }


    public function get_products(Request $request)
    {
        $store_id = getStoreId();
        $max_limit = 8;
        // $max_limit = 25;
        if (Auth::user()->role_id === 4) {
            $user_id = Auth::user()->id;
            $seller_id = Seller::where('user_id', $user_id)->value('id');
        }

        $category_id = (isset($_GET['category_id']) && !empty($_GET['category_id']) && is_numeric($_GET['category_id'])) ? request('category_id') : "";
        $limit = (isset($_GET['limit']) && !empty($_GET['limit']) && is_numeric($_GET['limit']) && $_GET['limit'] <= $max_limit) ? request('limit') : $max_limit;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset']) && is_numeric($_GET['offset'])) ? $_GET['offset'] : 0;

        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'p.id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'desc';
        $filter['search'] = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        $filter['show_only_active_products'] = 1;
        $filter['show_only_physical_product'] = 1;
        $filterBy = $request->input('filter_by') ?? 'p.id';
        // Fetch the products and count the total
        $products = fetchProduct('', $filter, '', $category_id, $limit, $offset, $sort, $order, '', '', $seller_id, '', $store_id);

        $response['error'] = (!empty($products)) ? 'false' : 'true';
        $response['message'] = (!empty($products)) ? "Products fetched successfully" : "No products found";
        $response['products'] = (!empty($products)) ? $products : [];
        $response['total'] = $products['total'] ?? 0;

        print_r(json_encode($response));
    }

    public function get_combo_products()
    {
        $store_id = getStoreId();
        $max_limit = 25;

        $user_id = Auth::user()->id;
        $seller_id = Seller::where('user_id', $user_id)->value('id');


        $limit = (isset($_GET['limit']) && !empty($_GET['limit']) && is_numeric($_GET['limit']) && $_GET['limit'] <= $max_limit) ? request('limit') : $max_limit;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset']) && is_numeric($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'p.id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'desc';
        $filter['search'] = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        $filter['show_only_active_products'] = 1;

        $products = fetchComboProduct('', $filter, '', $limit, $offset, $sort, $order, '', '', $seller_id, $store_id);

        $response['error'] = (!empty($products)) ? 'false' : 'true';
        $response['message'] = (!empty($products)) ? labels('admin_labels.products_fetched_successfully', 'Products fetched successfully')
            :
            labels('admin_labels.no_products_found', 'No products found');
        $response['products'] = (!empty($products)) ? $products : [];

        $response['total'] = $products['total'] ?? 0;

        print_r(json_encode($response));
    }

    public function place_order(Request $request)
    {

        $store_id = getStoreId();
        if (!$request->has('data') || empty($request->input('data'))) {
            $response = [
                'error' => true,
                'message' => 'Cart is empty!!',

            ];
            return response()->json($response);
        }

        if (empty($request->input('payment_method'))) {
            $response = [
                'error' => true,
                'message' =>
                labels('admin_labels.select_at_least_one_payment_method', 'Please select at least one payment method'),

            ];
            return response()->json($response);
        }


        if ($request->has('payment_method') && !empty($request->input('payment_method')) && $request->input('payment_method') == "other" && empty($request->input('payment_method_name'))) {
            $response = [
                'error' => true,
                'message' =>
                labels('admin_labels.enter_payment_method_name_prompt', 'Please enter payment method name'),
                'csrfHash' => csrf_token(),
                'data' => []
            ];
            return response()->json($response);
        }

        $post_data = json_decode($request->data, true);


        if (isset($post_data) && !empty($post_data)) {
            foreach ($post_data as $key => $data) {
                if (!isset($data['variant_id']) || empty($data['variant_id'])) {
                    return response()->json([
                        'error' => true,
                        'message' =>
                        labels('admin_labels.variant_id_required', 'The variant ID field is required'),

                    ]);
                }

                if (!isset($data['quantity']) || empty($data['quantity'])) {
                    return response()->json([
                        'error' => true,
                        'message' => 'Please enter valid quantity for ' . $data['title'],

                    ]);
                }
            }
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Cart is empty!!',

            ]);
        }
        $product_variant_id = array_column($post_data, "variant_id");
        $product_type = array_column($post_data, "product_type");
        $quantity = array_column($post_data, "quantity");
        $user_id = $request->user_id;

        $currency = Currency::where('is_default', 1)->value('code');
        $place_order_data = array();
        $product_type = $product_type;
        $place_order_data['product_variant_id'] = implode(",", $product_variant_id);
        $place_order_data['quantity'] = implode(",", $quantity);
        $place_order_data['user_id'] = $user_id;
        $user_mobile = fetchDetails("users", ['id' => $user_id], "mobile");
        $place_order_data['mobile'] = isset($user_mobile) && !empty($user_mobile) ? $user_mobile[0]->mobile : '';
        $place_order_data['address_id'] = $request->input('address_id');
        $place_order_data['is_wallet_used'] = 0;
        $place_order_data['delivery_charge'] = $request->input('delivery_charges');
        $place_order_data['discount'] = $request->input('discount');
        $place_order_data['is_delivery_charge_returnable'] = 0;
        $place_order_data['wallet_balance_used'] = 0;
        $place_order_data['active_status'] = "delivered";
        $place_order_data['is_pos_order'] = 1;
        $place_order_data['store_id'] = $store_id;
        $place_order_data['order_payment_currency_code'] = $currency;
        $payment_method_name = (isset($request->payment_method_name) && !empty($request->payment_method_name)) ? $request->payment_method_name : NULL;
        $place_order_data['payment_method'] = (isset($request->payment_method) && !empty($request->payment_method) && $request->payment_method != "other") ? $request->payment_method : $payment_method_name;
        $txn_id = (isset($request->txn_id) && !empty($request->txn_id)) ? $request->txn_id : NULL;


        $check_current_stock_status = validateStock($product_variant_id, $quantity, $product_type);

        if ($check_current_stock_status['error'] == true) {
            $response = [
                'error' => true,
                'message' => $check_current_stock_status['message'],
                'data' => []
            ];
            return response()->json($response);
        }


        $data = array(
            'product_variant_id' => implode(",", $product_variant_id),
            'qty' => implode(",", $quantity),
            'user_id' => $user_id,
            'store_id' => $store_id,
            'product_type' => implode(",", $product_type)
        );
        // dd($data);
        if (addToCart($data) != true) {
            $response = [
                'error' => true,
                'message' =>
                labels('admin_labels.items_not_added', 'Items are Not Added'),
                'data' => []
            ];
            return response()->json($response);
        }

        $cart = getCartTotal($user_id, false, 0, "", $store_id);

        if (empty($cart)) {
            $response = [
                'error' => true,
                'message' =>
                labels('admin_labels.cart_is_empty', 'Your Cart is empty.'),
                'data' => []
            ];
            return response()->json($response);
        }

        $final_total = $cart['overall_amount'];

        $place_order_data['final_total'] = floatval($final_total) - floatval($cart['delivery_charge']);
        $place_order_data['cart_product_type'] = implode(",", $product_type);

        // $res = placeOrder($place_order_data);
        // dd($place_order_data);
        $store_id = isset($place_order_data['store_id']) && !empty($place_order_data['store_id']) ? $place_order_data['store_id'] : '';


        $product_variant_id = explode(',', $place_order_data['product_variant_id']);

        $cart_product_type = explode(',', $place_order_data['cart_product_type']);

        $quantity = explode(',', $place_order_data['quantity']);
        // dd($quantity);
        $check_current_stock_status = validateStock($product_variant_id, $quantity, $cart_product_type);

        if (isset($check_current_stock_status['error']) && $check_current_stock_status['error'] == true) {
            return json_encode($check_current_stock_status);
        }
        $total = 0;
        $promo_code_discount = 0;
        $language_code = get_language_code();


        //fetch details from product_variants table for regular product

        $product_variant = Product_variants::select(
            'product_variants.*',
            'c.product_type as cart_product_type',
            DB::raw('(SELECT GROUP_CONCAT(tax.percentage) FROM taxes as tax WHERE FIND_IN_SET(tax.id, p.tax)) as tax_percentage'),
            DB::raw('(SELECT GROUP_CONCAT(tax.percentage) FROM taxes as tax WHERE FIND_IN_SET(tax.id, p.tax)) as tax_ids'),
            DB::raw('(SELECT GROUP_CONCAT(tax_name.title) FROM taxes as tax_name WHERE FIND_IN_SET(tax_name.id, p.tax)) as tax_name'),
            'p.seller_id',
            'p.name as product_name',
            'p.type as product_type',
            'p.is_prices_inclusive_tax',
            'p.download_link'
        )
            ->join('products as p', 'product_variants.product_id', '=', 'p.id')
            ->leftJoin('taxes as tax_id', function ($join) {
                $join->on(DB::raw('FIND_IN_SET(tax_id.id, p.tax)'), '>', DB::raw('0'));
            })
            ->leftJoin('cart as c', 'c.product_variant_id', '=', 'product_variants.id')
            ->whereIn('product_variants.id', $product_variant_id)
            ->where('c.product_type', 'regular')
            ->orderByRaw('FIELD(product_variants.id, ' . $place_order_data['product_variant_id'] . ')')
            ->get();



        //fetch details from combo_products table for combo product

        $combo_product_variant = ComboProduct::select(
            'combo_products.*',
            'c.product_type as cart_product_type',
            DB::raw('(SELECT GROUP_CONCAT(c_tax.percentage) FROM taxes as c_tax WHERE FIND_IN_SET(c_tax.id, combo_products.tax)) as tax_percentage'),
            DB::raw('(SELECT GROUP_CONCAT(c_tax.percentage) FROM taxes as c_tax WHERE FIND_IN_SET(c_tax.id, combo_products.tax)) as tax_ids'),
            DB::raw('(SELECT GROUP_CONCAT(c_tax_title.title) FROM taxes as c_tax_title WHERE FIND_IN_SET(c_tax_title.id, combo_products.tax)) as tax_name'),
            'combo_products.seller_id',
            'combo_products.title as product_name',
            'combo_products.product_type',
            'combo_products.is_prices_inclusive_tax',
            'combo_products.download_link'
        )
            ->leftJoin('taxes as c_tax', function ($join) {
                $join->on(DB::raw('FIND_IN_SET(c_tax.id, combo_products.tax)'), '>', DB::raw('0'));
            })
            ->leftJoin('cart as c', 'c.product_variant_id', '=', 'combo_products.id')
            ->whereIn('combo_products.id', $product_variant_id)
            ->where('c.product_type', 'combo')
            ->orderByRaw('FIELD(combo_products.id, ' . $place_order_data['product_variant_id'] . ')')
            ->get();



        //merge both collection
        $product_variant = $product_variant->merge($combo_product_variant);
        // dd($product_variant);
        if (!empty($product_variant)) {

            $system_settings = getSettings('system_settings', true, true);
            $system_settings = json_decode($system_settings, true);

            $seller_ids = $product_variant->pluck('seller_id')->unique()->values()->all();

            if ($system_settings['single_seller_order_system'] == '1') {
                if (isset($seller_ids) && count($seller_ids) > 1) {
                    $response['error'] = true;
                    $response['message'] = 'Only one seller products are allow in one order.';
                    return $response;
                }
            }

            $delivery_charge = isset($place_order_data['delivery_charge']) && !empty($place_order_data['delivery_charge']) ? $place_order_data['delivery_charge'] : 0;
            $discount = isset($place_order_data['discount']) && !empty($place_order_data['discount']) ? $place_order_data['discount'] : 0;
            $gross_total = 0;
            $cart_data = [];


            for ($i = 0; $i < count($product_variant); $i++) {

                $pv_price[$i] = ($product_variant[$i]['special_price'] > 0 && $product_variant[$i]['special_price'] != null) ? $product_variant[$i]['special_price'] : $product_variant[$i]['price'];
                $tax_ids[$i] = (isset($product_variant[$i]['tax_ids']) && $product_variant[$i]['tax_ids'] != null) ? $product_variant[$i]['tax_ids'] : '0';
                $tax_percentage[$i] = (isset($product_variant[$i]['tax_percentage']) && $product_variant[$i]['tax_percentage'] != null) ? $product_variant[$i]['tax_percentage'] : '0';
                $tax_percntg[$i] = explode(',', $tax_percentage[$i]);
                $total_tax = array_sum($tax_percntg[$i]);
                if ((isset($product_variant[$i]['is_prices_inclusive_tax']) && $product_variant[$i]['is_prices_inclusive_tax'] == 0)) {
                    $tax_amount[$i] = $pv_price[$i] * ($total_tax / 100);
                    $pv_price[$i] = $pv_price[$i] + $tax_amount[$i];
                }

                $subtotal[$i] = ($pv_price[$i]) * $quantity[$i];
                $pro_name[$i] = $product_variant[$i]['product_name'];

                if ($product_variant[$i]['cart_product_type'] == 'regular') {
                    $variant_info = getVariantsValuesById($product_variant[$i]['id']);
                } else {
                    $variant_info = [];
                }

                $product_variant[$i]['variant_name'] = (isset($variant_info[0]['variant_values']) && !empty($variant_info[0]['variant_values'])) ? $variant_info[0]['variant_values'] : "";


                if ($tax_percentage[$i] != NUll && $tax_percentage[$i] > 0) {
                    $tax_amount[$i] = round($subtotal[$i] * $total_tax / 100, 2);
                } else {
                    $tax_amount[$i] = 0;
                    $tax_percentage[$i] = 0;
                }
                $gross_total += $subtotal[$i];
                $total += $subtotal[$i];
                $total = round($total, 2);
                $gross_total = round($gross_total, 2);
                if ($product_variant[$i]->cart_product_type == 'regular') {
                    $product_name = getDynamicTranslation(
                        'products',
                        'name',
                        $product_variant[$i]->product_id,
                        $language_code
                    );
                } else {
                    $product_name = getDynamicTranslation(
                        'combo_products',
                        'title',
                        $product_variant[$i]->product_id,
                        $language_code
                    );
                }
                array_push(
                    $cart_data,
                    array(
                        'name' => $product_name,
                        'tax_amount' => $tax_amount[$i],
                        'qty' => $quantity[$i],
                        'sub_total' => $subtotal[$i],
                    )
                );
            }


            $settings = getSettings('system_settings', true, true);
            $settings = json_decode($settings, true);
            $app_name = isset($settings['app_name']) && !empty($settings['app_name']) ? $settings['app_name'] : '';

            $currency = isset($settings['currency']) && !empty($settings['currency']) ? $settings['currency'] : '';
            if (isset($settings['minimum_cart_amount']) && !empty($settings['minimum_cart_amount'])) {
                $carttotal = $total + $delivery_charge;
                if ($carttotal < $settings['minimum_cart_amount']) {
                    $response = [
                        'error' => true,
                        'message' => 'Total amount should be greater or equal to ' . $currency . $settings['minimum_cart_amount'] . ' total is ' . $currency . $carttotal,
                        'code' => 102,
                    ];
                    return $response;
                }
            }


            // add promocode calculation here
            if (isset($place_order_data['promo_code_id']) && !empty($place_order_data['promo_code_id'])) {
                $place_order_data['promo_code'] = fetchDetails('promo_codes', ['id' => $place_order_data['promo_code_id']], 'promo_code')[0]->promo_code;
                // dd($total);
                $promo_code = validatePromoCode($place_order_data['promo_code_id'], $place_order_data['user_id'], $total, 1);
                $promo_code = $promo_code->original;
                if ($promo_code['error'] == false) {

                    if ($promo_code['data'][0]->discount_type == 'percentage') {
                        $promo_code_discount = (isset($promo_code['data'][0]->is_cashback) && $promo_code['data'][0]->is_cashback == 0) ? floatval($total * $promo_code['data'][0]->discount / 100) : 0;
                    } else {
                        $promo_code_discount = (isset($promo_code['data'][0]->is_cashback) && $promo_code['data'][0]->is_cashback == 0) ? $promo_code['data'][0]->discount : 0;
                    }
                    if ($promo_code_discount <= $promo_code['data'][0]->max_discount_amount) {
                        $total = (isset($promo_code['data'][0]->is_cashback) && $promo_code['data'][0]->is_cashback == 0) ? floatval($total) - $promo_code_discount : floatval($total);
                    } else {
                        $total = (isset($promo_code['data'][0]->is_cashback) && $promo_code['data'][0]->is_cashback == 0) ? floatval($total) - $promo_code['data'][0]->max_discount_amount : floatval($total);
                        $promo_code_discount = $promo_code['data'][0]->max_discount_amount;
                    }
                } else {
                    return $promo_code;
                }
            }
            $final_total = $total + intval($delivery_charge);
            $final_total = round($final_total, 2);

            $total_payable = $final_total;

            $status = 'delivered';
            if ($place_order_data['is_wallet_used'] == '1') {
                $place_order_data['payment_method'] = 'wallet';
            }
            // dd($status);
            $order_payment_currency_data = fetchDetails('currencies', ['code' => $place_order_data['order_payment_currency_code']], ['id', 'exchange_rate']);
            $base_currency = getDefaultCurrency()->code;
            $order_data = [
                'user_id' => $place_order_data['user_id'],
                'mobile' => (isset($place_order_data['mobile']) && !empty($place_order_data['mobile']) && $place_order_data['mobile'] != '' && $place_order_data['mobile'] != 'NULL') ? $place_order_data['mobile'] : '',
                'total' => $gross_total,
                'promo_discount' => (isset($promo_code_discount) && $promo_code_discount != NULL) ? $promo_code_discount : '0',
                'total_payable' => $total_payable,
                'delivery_charge' => intval($delivery_charge),
                'is_delivery_charge_returnable' => isset($place_order_data['is_delivery_charge_returnable']) ? $place_order_data['is_delivery_charge_returnable'] : 0,
                'wallet_balance' => (isset($Wallet_used) && $Wallet_used == true) ? $place_order_data['wallet_balance_used'] : '0',
                'final_total' => $final_total,
                'discount' => $discount,
                'payment_method' => $place_order_data['payment_method'] ?? '',
                'promo_code_id' => (isset($place_order_data['promo_code_id'])) ? $place_order_data['promo_code_id'] : ' ',
                'email' => isset($place_order_data['email']) ? $place_order_data['email'] : ' ',
                'is_pos_order' => isset($place_order_data['is_pos_order']) ? $place_order_data['is_pos_order'] : 0,
                'is_shiprocket_order' => isset($place_order_data['delivery_type']) && !empty($place_order_data['delivery_type']) && $place_order_data['delivery_type'] == 'standard_shipping' ? 1 : 0,
                'order_payment_currency_id' => $order_payment_currency_data[0]->id ?? '',
                'order_payment_currency_code' => $place_order_data['order_payment_currency_code'] ?? "",
                'order_payment_currency_conversion_rate' => $order_payment_currency_data[0]->exchange_rate ?? '',
                'base_currency_code' => $base_currency,
            ];

            if (isset($place_order_data['address_id']) && !empty($place_order_data['address_id'])) {
                $order_data['address_id'] = (isset($place_order_data['address_id']) ? $place_order_data['address_id'] : '');
            }

            if (isset($place_order_data['delivery_date']) && !empty($place_order_data['delivery_date']) && !empty($place_order_data['delivery_time']) && isset($place_order_data['delivery_time'])) {
                $order_data['delivery_date'] = date('Y-m-d', strtotime($place_order_data['delivery_date']));
                $order_data['delivery_time'] = $place_order_data['delivery_time'];
            }
            $addressController = app(AddressController::class);
            if (isset($place_order_data['address_id']) && !empty($place_order_data['address_id'])) {

                $address_data = $addressController->getAddress(null, $place_order_data['address_id'], true);

                if (!empty($address_data)) {
                    $order_data['latitude'] = $address_data[0]->latitude;
                    $order_data['longitude'] = $address_data[0]->longitude;
                    $order_data['address'] = (!empty($address_data[0]->address) && $address_data[0]->address != 'NULL') ? $address_data[0]->address . ', ' : '';
                    $order_data['address'] .= (!empty($address_data[0]->landmark) && $address_data[0]->landmark != 'NULL') ? $address_data[0]->landmark . ', ' : '';
                    $order_data['address'] .= (!empty($address_data[0]->area) && $address_data[0]->area != 'NULL') ? $address_data[0]->area . ', ' : '';
                    $order_data['address'] .= (!empty($address_data[0]->city) && $address_data[0]->city != 'NULL') ? $address_data[0]->city . ', ' : '';
                    $order_data['address'] .= (!empty($address_data[0]->state) && $address_data[0]->state != 'NULL') ? $address_data[0]->state . ', ' : '';
                    $order_data['address'] .= (!empty($address_data[0]->country) && $address_data[0]->country != 'NULL') ? $address_data[0]->country . ', ' : '';
                    $order_data['address'] .= (!empty($address_data[0]->pincode) && $address_data[0]->pincode != 'NULL') ? $address_data[0]->pincode : '';
                }
            } else {
                $order_data['address'] = "";
            }

            if (!empty($place_order_data['latitude']) && !empty($place_order_data['longitude'])) {
                $order_data['latitude'] = $place_order_data['latitude'];
                $order_data['longitude'] = $place_order_data['longitude'];
            }
            $order_data['notes'] = isset($place_order_data['order_note']) ? $place_order_data['order_note'] : '';
            $order_data['store_id'] = $store_id;

            $order = Order::forceCreate($order_data);

            $order_id = $order->id;
            for ($i = 0; $i < count($product_variant); $i++) {
                // dd($product_variant[$i]);
                if ($product_variant[$i]->cart_product_type == 'regular') {
                    $product_name = getDynamicTranslation(
                        'products',
                        'name',
                        $product_variant[$i]->product_id,
                        $language_code
                    );
                } else {
                    $product_name = getDynamicTranslation(
                        'combo_products',
                        'title',
                        $product_variant[$i]->product_id,
                        $language_code
                    );
                }
                $product_variant_data[$i] = [
                    'user_id' => $place_order_data['user_id'],
                    'order_id' => $order_id,
                    'seller_id' => $product_variant[$i]['seller_id'],
                    'product_name' =>  $product_name,
                    // 'product_name' =>  $product_variant[$i]['product_name'],
                    'variant_name' => $product_variant[$i]['variant_name'],
                    'product_variant_id' => $product_variant[$i]['id'],
                    'quantity' => $quantity[$i],
                    'price' => $pv_price[$i],
                    'tax_percent' => $total_tax,
                    'tax_ids' => $tax_ids[$i],
                    'tax_amount' => $tax_amount[$i],
                    'sub_total' => $subtotal[$i],
                    'status' => json_encode(array(array($status, date("d-m-Y h:i:sa")))),
                    'active_status' => $status,
                    'otp' => 0,
                    'store_id' => $store_id,
                    'order_type' => $product_variant[$i]['cart_product_type'] . "_order",
                    'attachment' => $place_order_data['attachment_path'][$product_variant[$i]['id']] ?? "",
                ];
                // dd($product_variant_data[$i]);
                $order_items = OrderItems::forceCreate($product_variant_data[$i]);

                $order_item_id = $order_items->id;
                removeFromCart($place_order_data);
                $user_balance = fetchDetails('users', ['id' => $place_order_data['user_id']], 'balance');
                $response = [
                    'error' => false,
                    'message' => 'Order Delivered Successfully',
                    'order_id' => $order_id,
                    'final_total' => ($place_order_data['is_wallet_used'] == '1') ? $final_total -= $place_order_data['wallet_balance_used'] : $final_total,
                    'total_payable' => $total_payable,
                    'order_item_data' => $product_variant_data,
                    'balance' => $user_balance,
                ];
                return response()->json($response);
            }
        } else {
            $response = [
                'error' => true,
                'message' => "Product(s) Not Found!",
            ];

            return response()->json($response);
        }
        if (isset($res) && !empty($res)) {
            // creating transaction record for card payments
            $trans_data = [
                'transaction_type' => 'transaction',
                'user_id' => $user_id,
                'order_id' => $res->original['order_id'],
                'type' => strtolower($place_order_data['payment_method']),
                'txn_id' => $txn_id,
                'amount' => $final_total,
                'status' => "success",
                'message' =>
                labels('admin_labels.order_delivered_successfully', 'Order Delivered Successfully'),
            ];
            Transaction::forceCreate($trans_data);
        }
        // $data['order_id'] = $res->original['order_id'];
        // $response = [
        //     'error' => false,
        //     'message' =>
        //     labels('admin_labels.order_delivered_successfully', 'Order Delivered Successfully'),
        //     'data' => $res,
        // ];
        // return response()->json($response);
    }

    public function combo_place_order(Request $request)
    {

        $store_id = getStoreId();
        if (!$request->has('data') || empty($request->input('data'))) {
            $response = [
                'error' => true,
                'message' => 'Pass the data',
                'csrfHash' => csrf_token(),
                'data' => []
            ];
            return response()->json($response);
        }

        if (empty($request->input('payment_method'))) {
            $response = [
                'error' => true,
                'message' => labels('admin_labels.select_at_least_one_payment_method', 'Please select at least one payment method'),
                'csrfHash' => csrf_token(),
                'data' => []
            ];
            return response()->json($response);
        }

        if (!$request->has('user_id') || empty($request->input('user_id'))) {
            $response = [
                'error' => true,
                'message' => labels('admin_labels.select_customer_prompt', 'Please select the customer!'),
                'csrfHash' => csrf_token(),
                'data' => []
            ];
            return response()->json($response);
        }


        if ($request->has('payment_method') && !empty($request->input('payment_method')) && $request->input('payment_method') == "other" && empty($request->input('payment_method_name'))) {
            $response = [
                'error' => true,
                'message' => labels('admin_labels.enter_payment_method_name_prompt', 'Please enter payment method name'),
                'csrfHash' => csrf_token(),
                'data' => []
            ];
            return response()->json($response);
        }

        $post_data = json_decode($request->data, true);


        if (isset($post_data) && !empty($post_data)) {
            foreach ($post_data as $key => $data) {
                if (!isset($data['id']) || empty($data['id'])) {
                    return response()->json([
                        'error' => true,
                        'message' =>
                        labels('admin_labels.product_id_required', 'The product ID field is required'),
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_token(),
                        'data' => []
                    ]);
                }

                if (
                    !isset($data['quantity']) || empty($data['quantity'])
                ) {
                    return response()->json([
                        'error' => true,
                        'message' => 'Please enter valid quantity for ' . $data['title'],
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_token(),
                        'data' => []
                    ]);
                }
            }
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Pass the data',
                'data' => []
            ]);
        }

        $product_id = array_column($post_data, "id");
        $quantity = array_column($post_data, "quantity");
        $user_id = $request->user_id;

        $payment_method_name = (isset($request->payment_method_name) && !empty($request->payment_method_name)) ? $request->payment_method_name : NULL;
        $txn_id = (isset($request->txn_id) && !empty($request->txn_id)) ? $request->txn_id : NULL;

        $check_current_stock_status = validateComboStock($product_id, $quantity);

        if ($check_current_stock_status['error'] == true) {
            $response = [
                'error' => true,
                'message' => $check_current_stock_status['message'],
                'data' => []
            ];
            return response()->json($response);
        }


        $final_total = !empty($request->input('final_total')) ? $request->input('final_total') : '';
        $sub_total = !empty($request->input('sub_total')) ? $request->input('sub_total') : '';
        $delivery_charges = !empty($request->input('delivery_charges')) ? $request->input('delivery_charges') : '';
        $discount = !empty($request->input('discount')) ? $request->input('discount') : '';
        $payment_method = $request->input('payment_method');


        $currency = Currency::where('is_default', 1)->value('code');


        $order_payment_currency_data = fetchDetails('currencies', ['code' => $currency], ['id', 'exchange_rate']);
        $user_mobile = fetchDetails("users", ['id' => $user_id], "mobile");

        $order_data = [
            'user_id' => $user_id,
            'mobile' => $user_mobile[0]->mobile,
            'address_id' => $request->input('address_id'),
            'address' => $request->input('address'),
            'total' => $sub_total,
            'final_total' => $final_total,
            'total_payable' => $final_total,
            'discount' => $discount,
            'delivery_charge' => $delivery_charges,
            'is_delivery_charge_returnable' => 0,
            'wallet_balance' => 0,
            'type' => 'combo_place_order',
            'store_id' => $store_id,
            'payment_method' => $payment_method != '' ? $payment_method : $payment_method_name,
            'is_pos_order' => 1,
            'order_payment_currency_code' => $currency,
            'order_payment_currency_id' => $order_payment_currency_data[0]->id ?? '',
            'order_payment_currency_conversion_rate' => $order_payment_currency_data[0]->exchange_rate,
            'base_currency_code' => $currency,
        ];

        $order = Order::forceCreate($order_data);

        $order->save();
        $combo_data = $request->data;
        $combo_data = json_decode($combo_data, true);
        $userId = Auth::user()->id;
        $seller_id = Seller::where('user_id', $userId)->value('id');

        foreach ($combo_data as $data) {

            $order_id = $order->id;
            $combo_data = [
                'user_id' => $user_id,
                'store_id' => $store_id,
                'order_id' => $order_id,
                'product_name' => $data['title'],
                'quantity' => $data['quantity'],
                'price' => $data['price'],
                'sub_total' => $data['price'] * $data['quantity'],
                'seller_id' => $seller_id,
                'status' => json_encode(array(array('delivered', date("d-m-Y h:i:sa")))),
                'active_status' => 'delivered',
            ];

            $order_item = OrderItems::forceCreate($combo_data);
        }

        if (isset($order_item) && !empty($order_item)) {
            // creating transaction record for card payments
            $trans_data = [
                'transaction_type' => 'transaction',
                'user_id' => $user_id,
                'order_id' => $order_id,
                'type' => $order_data['payment_method'],
                'txn_id' => $txn_id,
                'amount' => $final_total,
                'status' => "success",
                'message' =>
                labels('admin_labels.order_delivered_successfully', 'Order Delivered Successfully'),
            ];
            Transaction::forceCreate($trans_data);
        }
        $data['order_id'] = $order_id;
        $response = [
            'error' => false,
            'message' =>
            labels('admin_labels.order_delivered_successfully', 'Order Delivered Successfully'),
            'data' => $order,
        ];
        return response()->json($response);
    }

    public function get_poduct_variants(Request $request)
    {


        $res = fetchProduct('', '', $request['product_id']);

        if (!empty($res)) {

            $response = [
                'error' => false,
                'data' => $res['product'][0]->variants,
            ];
        } else {
            $response = [
                'error' => true,
                'message' =>
                labels('admin_labels.product_variants_not_found', 'Product variants not found.'),
                'data' => [],
            ];
        }
        return response()->json($response);
    }

    public function getCustomerAddress(Request $request)
    {
        $address_data = Address::leftJoin('users', 'addresses.user_id', '=', 'users.id')
            ->where('users.id', '=', $request->pos_user_id)
            ->get(['addresses.*', 'users.image']);

        if (!$address_data->isEmpty()) {
            $data['address_id'] = (!empty($address_data[0]->id) && $address_data[0]->id != 'NULL') ? $address_data[0]->id : '';
            $data['city'] = (!empty($address_data[0]->city) && $address_data[0]->city != 'NULL') ? $address_data[0]->city : '';
            $data['state'] = (!empty($address_data[0]->state) && $address_data[0]->state != 'NULL') ? $address_data[0]->state : '';
            $data['country'] = (!empty($address_data[0]->country) && $address_data[0]->country != 'NULL') ? $address_data[0]->country : '';
            $data['user_address'] = (!empty($address_data[0]->address) && $address_data[0]->address != 'NULL') ? $address_data[0]->address : '';
            $data['user_name'] = (!empty($address_data[0]->name) && $address_data[0]->name != 'NULL') ? $address_data[0]->name : '';
            $data['user_image'] = getMediaImageUrl($address_data[0]->image, 'USER_IMG_PATH');
            $data['mobile'] = (!empty($address_data[0]->mobile) && $address_data[0]->mobile != 'NULL') ? $address_data[0]->mobile : $address_data[0]->alternate_mobile;
            $data['address'] = (!empty($address_data[0]->address) && $address_data[0]->address != 'NULL') ? $address_data[0]->address . ', ' : '';
            $data['address'] .= (!empty($address_data[0]->landmark) && $address_data[0]->landmark != 'NULL') ? $address_data[0]->landmark . ', ' : '';
            $data['address'] .= (!empty($address_data[0]->area) && $address_data[0]->area != 'NULL') ? $address_data[0]->area . ', ' : '';
            $data['address'] .= (!empty($address_data[0]->city) && $address_data[0]->city != 'NULL') ? $address_data[0]->city . ', ' : '';
            $data['address'] .= (!empty($address_data[0]->state) && $address_data[0]->state != 'NULL') ? $address_data[0]->state . ', ' : '';
            $data['address'] .= (!empty($address_data[0]->country) && $address_data[0]->country != 'NULL') ? $address_data[0]->country . ', ' : '';
            $data['address'] .= (!empty($address_data[0]->pincode) && $address_data[0]->pincode != 'NULL') ? $address_data[0]->pincode : '';
        }

        if (!$address_data->isEmpty()) {
            $response = [
                'error' => false,
                'data' => $data,
            ];
        } else {
            $response = [
                'error' => true,
                'message' =>
                labels('admin_labels.address_not_found', 'Address not found.'),
                'data' => [],
            ];
        }
        return response()->json($response);
    }

    public function update_user_address(Request $request)
    {
        $required_fields = [
            'address_id' => 'Address Id is required',
            'name' => 'Name is required',
            'mobile' => 'Mobile is required',
            'country' => 'Country is required',
            'state' => 'State is required',
            'city' => 'City is required',
            'address' => 'Address is required'
        ];

        // Initialize an empty array for missing fields
        $missing_fields = [];

        // Check each required field
        foreach ($required_fields as $field => $errorMessage) {
            if (empty($request->input($field))) {
                $missing_fields[] = [
                    'error' => true,
                    'error_message' => labels('admin_labels.' . $field . '_is_required', $errorMessage),
                    'csrfHash' => csrf_token(),
                    'data' => []
                ];
            }
        }

        // If any fields are missing, return the response with the first missing field error
        if (!empty($missing_fields)) {
            return response()->json($missing_fields[0]);
        }
        $address_id = !empty($request->input('address_id')) ? $request->input('address_id') : '';
        $address = Address::findOrFail($address_id);
        $address->name = !empty($request->input('name')) ? $request->name : '';
        $address->mobile = !empty($request->input('mobile')) ? $request->mobile : '';
        $address->address = !empty($request->input('address')) ? $request->address : '';
        $address->city = !empty($request->input('city')) ? $request->city : '';
        $address->state = !empty($request->input('state')) ? $request->state : '';
        $address->country = !empty($request->input('country')) ? $request->country : '';
        $address->save();
        if ($request->ajax()) {
            return response()->json(['message' => labels('admin_labels.address_updated_successfully', 'Address updated successfully')]);
        }
    }
}