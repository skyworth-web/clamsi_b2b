<?php

namespace App\Http\Controllers\Seller\v1;

use App\Http\Controllers\Admin\AddressController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PickupLocationController;
use App\Http\Controllers\Admin\SellerController;
use App\Http\Controllers\Seller\ReportController;
use App\Http\Controllers\Admin\StoreController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Seller\AreaController;
use App\Http\Controllers\Seller\AttributeController;
use App\Http\Controllers\Seller\CategoryController;
use App\Http\Controllers\Admin\CategoryController as AdmincategoryController;
use App\Http\Controllers\Seller\ComboProductController;
use App\Http\Controllers\Seller\MediaController;
use App\Http\Controllers\Seller\OrderController as SellerOrderController;
use App\Http\Controllers\Seller\PaymentRequestController;
use App\Http\Controllers\Seller\ComboProductRatingController;
use App\Http\Controllers\Seller\ProductController;
use App\Http\Controllers\Seller\ProductFaqController;
use App\Http\Controllers\Seller\ComboProductFaqController;
use App\Models\Brand;
use App\Models\Category;
use App\Models\City;
use App\Models\ComboProduct;
use App\Models\ComboProductFaq;
use App\Models\OrderItems;
use App\Models\ReturnRequest;
use App\Models\Seller;
use App\Models\Language;
use App\Models\Media;
use App\Models\Product;
use App\Models\ProductFaq;
use App\Models\SellerCommission;
use App\Models\StorageType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Zipcode;
use App\Models\Zone;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Exception;
use LaravelDaily\Invoices\Invoice;
use LaravelDaily\Invoices\Classes\Buyer;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use LaravelDaily\Invoices\Classes\Party;

/*
---------------------------------------------------------------------------
Defined Methods:-
---------------------------------------------------------------------------
1. login
2  register
3. update_user
4. verify_user
5. get_orders
6. get_order_items
7. update_order_item_status
8. get_categories
9. get_products
10. get_transactions
11. get_statistics
12. update_fcm
13. get_cities
14. get_zipcodes
15. get_taxes
16. send_withdrawal_request
17. get_withdrawal_request
18. get_attributes
19. get_attribute_values
20. get_media
21. add_products
22. get_seller_details
23. delete_product
24. update_products
25. get_delivery_boys
26. upload_media
27. get_product_rating
28. get_order_tracking
29. edit_order_tracking
30. get_sales_list
31. update_product_status
32. get_countries_data
33. get_brand_list
34. add_product_faqs
35. get_product_faqs
36. delete_product_faq
37. edit_product_faq
38. manage_stock
39. add_pickup_location
40. get_pickup_locations
41. create_shiprocket_order
42. generate_awb
43. send_pickup_request
44. generate_label
45. generate_invoice
46. cancel_shiprocket_order
47. download_label
48. download_invoice
49. shiprocket_order_tracking
50. get_shiprocket_order
51. delete_order
52. get_settings
53. delete_seller
54. get_stores
55. get_combo_products
56. add_combo_product
57. delete_combo_product
58. update_combo_product
59. get_languages
60. get_language_labels

<---- Newly Added for parcel ---->
61. get_all_parcels
62. create_order_parcel
63. delete_order_parcel
64. update_parcel_order_status
65. update_shiprocket_order_status
66. download_parcel_invoice
<---- Newly Added for parcel ---->

*/

class ApiController extends Controller
{
    public function login(Request $request)
    {
        /*
            mobile: 9874565478
            password: 12345678
            fcm_id: FCM_ID //{ optional }
        */
        $rules = [
            'mobile' => 'required|numeric',
            'password' => 'required',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            $credentials = $request->only('mobile', 'password');
            $language_code = $request->attributes->get('language_code');
            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                $fcm_ids = fetchdetails('user_fcm', ['user_id' => $user->id], 'fcm_id');

                $fcm_ids_array = array_map(function ($item) {
                    return $item->fcm_id;
                }, $fcm_ids);
                $token = $user->createToken('authToken')->plainTextToken;

                $userData = formatUserData($user, $fcm_ids_array);

                $seller_data = fetchDetails('seller_data', ['user_id' => $user->id], '*');
                $store_data = fetchDetails('seller_store', ['user_id' => $user->id], '*');
                $seller_id = !empty($seller_data) ? $seller_data[0]->id : "";
                $data = (array_merge($userData, (array) $seller_data));
                $output = $userData;
                unset($seller_data[0]->id);
                $isPublicDisk = !empty($store_data) && $store_data[0]->disk == 'public' ? 1 : 0;


                $output['store_data'] = formatStoreData($store_data, $isPublicDisk, $language_code);
                $output['seller_data'] = array_map(fn($seller) => (array) $seller, formatSellerData($seller_data, $isPublicDisk));


                foreach ($data as $key => $value) {
                    if (property_exists(!empty($seller_data) ? $seller_data[0] : '', $key)) {
                        $output[$key] = $value;
                    }
                }

                if ($user->role_id == 4) {
                    if (isset($request->fcm_id) && $request->fcm_id != '') {
                        $fcm_data = [
                            'fcm_id' => $request->fcm_id,
                            'user_id' => $user->id,
                        ];
                        $existing_fcm = DB::table('user_fcm')
                            ->where('user_id', $user->id)
                            ->where('fcm_id', $request->fcm_id)
                            ->first();

                        if (!$existing_fcm) {
                            // If it doesn't exist, create a new entry
                            DB::table('user_fcm')->insert($fcm_data);
                        }
                    }
                    unset($data[0]->password);

                    $messages = array("0" => "Your account is deactivated", "1" => "User Logged in successfully", "2" => "Your account is not yet approved.", "7" => "Your account has been removed by the admin. Contact admin for more information.");
                    $language_message_key = array("0" => "account_deactivated", "1" => "user_logged_in_successfully", "2" => "account_not_yet_approved", "7" => "account_removed_by_admin_contact_admin");
                    //if the login is successful

                    return response()->json([
                        'error' => (isset($seller_data[0]->status) && $seller_data[0]->status != "" && ($seller_data[0]->status == 1)) ? false : true,
                        'message' => $messages[$seller_data[0]->status],
                        'language_message_key' => $language_message_key[$seller_data[0]->status],
                        'token' => $token,
                        'data' => (isset($seller_data[0]->status) && $seller_data[0]->status != "" && ($seller_data[0]->status == 1)) ? $output : [],

                    ]);
                } else {
                    return response()->json([
                        'error' => true,
                        'message' => 'Incorrect Login.',
                        'language_message_key' => 'incorrect_login.',
                    ]);
                }
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Invalid credentials',
                    'language_message_key' => 'invalid_credentials',
                ], 401);
            }
        }
    }

    public function register(SellerController $sellerController, Request $request)
    {

        /*

            name:test
            mobile:9874565478
            email:test@gmail.com
            password:12345
            confirm_password:12345
            address:237,TimeSquare
            address_proof:FILE
            national_identity_card:FILE
            store_ids : 1,3
            store_name:eshop store
            store_logo:FILE
            authorized_signature:FILE
            store_url:url
            store_description:test
            tax_name:GST
            tax_number:GSTIN6786
            pan_number:GNU876
            account_number:123esdf
            account_name:name
            bank_code:INBsha23
            bank_name:bank name
        */


        $rules = [
            'name' => 'required',
            'mobile' => 'required|unique:users,mobile',
            'email' => 'required|unique:users,email',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
            'address' => 'required',
            'store_name' => 'required',
            'account_number' => 'required',
            'account_name' => 'required',
            'bank_name' => 'required',
            'bank_code' => 'required',
            'city' => 'required',
            'zipcode' => 'required',
            'deliverable_type' => 'required',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        }

        $seller_data = $sellerController->store($request, true);


        if (isset($seller_data->original['id']) && !empty($seller_data->original['id'])) {
            return response()->json([
                'error' => false,
                'message' => 'Seller registered Successfully. Wait for approval of admin.',
                'language_message_key' => 'seller_registered_successfully_wait_for_approval',
            ]);
        } else {
            return response()->json([
                'error' => isset($seller_data->original['error']) ? $seller_data->original['error'] : 'true',
                'message' => isset($seller_data->original['message']) ? $seller_data->original['message'] : $seller_data->original['error_message'],
                'language_message_key' => isset($seller_data->original['language_message_key']) ? $seller_data->original['language_message_key'] : 'something_went_wrong',
            ]);
        }
    }

    public function update_user(Request $request, SellerController $sellerController)
    {
        /*
            id:34  {seller's user_id}
            name:hiten
            mobile:7852347890
            email:amangoswami@gmail.com
            old:12345                       //{if want to change password}
            new:345234                      //{if want to change password}
            address:test
            store_ids:1,2
            store_name:storename
            store_url:url
            store_description:test
            account_number:123esdf
            account_name:name
            bank_code:INBsha23
            bank_name:bank name
            latitude:+37648
            longitude:-478237
            tax_name:GST
            tax_number:GSTIN6786
            pan_number:GNU876
            status:1 | 0                  //{1: active | 0:deactive}
            store_logo: file              // {pass if want to change}
            national_identity_card: file              // {pass if want to change}
            address_proof: file              // {pass if want to change}
            authorized_signature:FILE // {pass if want to change}
        */


        if (!empty($request->input('old')) || !empty($request->input('new'))) {
            $rules = [
                'old' => 'required',
                'new' => 'required',
            ];
            if ($validationResponse = validateRequest($request, $rules)) {
                return $validationResponse;
            }
        }

        $user_id = auth()->user()->id;

        if ($request->has('is_notification_on')) {
            User::where('id', $user_id)->update([
                'is_notification_on' => $request->input('is_notification_on')
            ]);
        }

        $store_id = $request->store_id;
        $request['store_id'] = $store_id;

        // dd($request->hasFile('profile_image'));
        $seller_data = $sellerController->update($request, $user_id, true);

        $user = fetchDetails('users', ['id' => $user_id], '*')[0];
        $language_code = $request->attributes->get('language_code');
        $fcm_ids = fetchdetails('user_fcm', ['user_id' => $user->id], 'fcm_id');

        $fcm_ids_array = array_map(function ($item) {
            return $item->fcm_id;
        }, $fcm_ids);

        $userData = formatUserData($user, $fcm_ids_array);

        $seller_data = fetchDetails('seller_data', ['user_id' => $user_id], '*');
        $store_data = fetchDetails('seller_store', ['user_id' => $user->id], '*');
        // dd($store_data);
        $seller_data[0]->seller_id = $seller_data[0]->id;
        $data = (array_merge($userData, (array) $seller_data));
        $output = $userData;
        unset($seller_data[0]->id);
        $isPublicDisk = $store_data[0]->disk == 'public' ? 1 : 0;
        $output['store_data'] = formatStoreData($store_data, $isPublicDisk, $language_code);
        $output['seller_data'] = array_map(fn($seller) => (array) $seller, formatSellerData($seller_data, $isPublicDisk));
        foreach ($data as $key => $value) {
            if (property_exists(!empty($seller_data) ? $seller_data[0] : '', $key)) {
                $output[$key] = $value;
            }
        }

        foreach ($data as $key => $value) {
            if (property_exists($seller_data[0], $key)) {
                $output[$key] = $value;
            }
        }
        unset($output[0]->password);

        if (!empty($seller_data)) {
            return response()->json([
                'error' => false,
                'message' => 'Seller Update Successfully.',
                'language_message_key' => 'seller_update_successfully',
                'data' => $output,
            ]);
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Seller data not updated',
                'language_message_key' => 'seller_data_not_updated',
                'data' => $seller_data,
            ]);
        }
    }


    public function verify_user(Request $request)
    {
        /* Parameters to be passed
            mobile: 9874565478
            email: test@gmail.com
        */
        $rules = [
            'mobile' => 'required|numeric',
            'email' => 'sometimes|nullable|email',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            $language_code = $request->attributes->get('language_code');
            $mobile = $request->input('mobile');
            $email = $request->input('email');
            $user = null;
            if (isset($mobile) && isExist(['mobile' => $mobile], 'users')) {
                $user = User::where('mobile', $mobile)->first();
            } elseif (isset($email) && isExist(['email' => $email], 'users')) {
                $user = User::where('email', $email)->first();
            }

            if ($user) {
                $test = Auth::login($user);
                $token = $user->createToken('authToken')->plainTextToken;
                $fcm_ids = fetchdetails('user_fcm', ['user_id' => $user->id], 'fcm_id');

                $fcm_ids_array = array_map(function ($item) {
                    return $item->fcm_id;
                }, $fcm_ids);

                $userData = formatUserData($user, $fcm_ids_array);

                $seller_data = fetchDetails('seller_data', ['user_id' => $user->id], '*');
                $store_data = fetchDetails('seller_store', ['user_id' => $user->id, 'status' => 1], '*');

                $seller_id = !empty($seller_data) ? $seller_data[0]->id : "";
                $data = (array_merge($userData, (array) $seller_data));
                $output = $userData;
                unset($seller_data[0]->id);

                $isPublicDisk = $store_data[0]->disk == 'public' ? 1 : 0;
                $output['seller_data'] = array_map(fn($seller) => (array) $seller, formatSellerData($seller_data, $isPublicDisk));

                $output['store_data'] = formatStoreData($store_data, $isPublicDisk, $language_code);


                foreach ($data as $key => $value) {
                    if (property_exists(!empty($seller_data) ? $seller_data[0] : '', $key)) {
                        $output[$key] = $value;
                    }
                }

                if ($user->role_id == 4) {
                    if (isset($request->fcm_id) && $request->fcm_id != '') {

                        $fcm_data = [
                            'fcm_id' => $request->fcm_id,
                            'user_id' => $user->id,
                        ];
                        $existing_fcm = DB::table('user_fcm')
                            ->where('user_id', $user->id)
                            ->where('fcm_id', $request->fcm_id)
                            ->first();

                        if (!$existing_fcm) {
                            // If it doesn't exist, create a new entry
                            DB::table('user_fcm')->insert($fcm_data);
                        }
                    }
                    unset($data[0]->password);

                    $messages = [
                        "0" => "Your account is deactivated",
                        "1" => "User Logged in successfully",
                        "2" => "Your account is not yet approved.",
                        "7" => "Your account has been removed by the admin. Contact admin for more information."
                    ];

                    $language_message_key = [
                        "0" => "account_deactivated",
                        "1" => "user_logged_in_successfully",
                        "2" => "account_not_yet_approved",
                        "7" => "account_removed_by_admin_contact_admin"
                    ];

                    $status = $seller_data[0]->status ?? null;

                    // Determine response code based on status
                    $responseCode = in_array($status, [0, 2, 7]) ? 401 : 200;

                    return response()->json([
                        'error' => $status != 1,
                        'message' => $messages[$status] ?? "Unknown status",
                        'language_message_key' => $language_message_key[$status] ?? "unknown_status",
                        'token' => $status == 1 ? $token : null,
                        'data' => $status == 1 ? $output : [],
                    ], $responseCode);
                } else {
                    return response()->json([
                        'error' => true,
                        'message' => 'Incorrect Login.',
                        'language_message_key' => 'incorrect_login.',
                    ]);
                }
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Invalid credentials',
                    'language_message_key' => 'invalid_credentials',
                ], 401);
            }
        }
    }

    public function get_orders(Request $request)
    {
        /*
            store_id : 1
            id:101 { optional }
            city_id:1 { optional }
            area_id:1 { optional }
            user_id:101 { optional }
            start_date : 2020-09-07 or 2020/09/07 { optional }
            end_date : 2021-03-15 or 2021/03/15 { optional }
            search:keyword      // optional
            limit:25            // { default - 25 } optional
            offset:0            // { default - 0 } optional
            sort: id / created_at // { default - id } optional
            order:DESC/ASC      // { default - DESC } optional
            order_type : digital/simple // if type is simple simple and variable product orders are showen AND if type is digital only digital product orders are showen
            active_status: received  {received,delivered,cancelled,processed,returned}     // optional
        */

        $rules = [
            'user_id' => 'numeric|exists:users,id',
            'store_id' => 'required|numeric|exists:stores,id',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            if (auth()->check()) {
                $user_id = auth()->user()->id;
                $seller_id = Seller::where('user_id', $user_id)->value('id');
            }
            $store_id = $request->input('store_id') ? (int) $request->input('store_id') : '';
            $limit = $request->input('limit', 25);
            $offset = $request->input('offset', 0);
            $sort = $request->input('sort', 'o.id');
            $order = $request->input('order', 'DESC');
            $search = $request->input('search', '');
            $id = $request->input('id', false);
            $user_id = $request->input('user_id', false);
            $start_date = $request->input('start_date', false);
            $end_date = $request->input('end_date', false);
            $multiple_status = $request->input('active_status') ? explode(',', $request->input('active_status')) : false;
            $download_invoice = $request->input('download_invoice', 1);
            $city_id = $request->input('city_id', null);
            $area_id = $request->input('area_id', null);
            $order_type = strtolower($request->input('order_type', ''));
            $language_code = $request->attributes->get('language_code');
            $order_details = fetchOrders(
                $id,
                $user_id,
                $multiple_status,
                false,
                $limit,
                $offset,
                $sort,
                $order,
                $download_invoice,
                $start_date,
                $end_date,
                $search,
                $city_id,
                $area_id,
                $seller_id,
                $order_type,
                '',
                $store_id,
                $language_code
            );
            $items = array();
            if (!$order_details['order_data']->isEmpty()) {
                $response['error'] = false;
                $response['message'] = 'Data retrieved successfully';
                $response['language_message_key'] = 'data_retrieved_successfully';
                $response['total'] = $order_details['total'];
                $response['awaiting'] = strval(ordersCount("awaiting", $seller_id, $order_type, $store_id));
                $response['received'] = strval(ordersCount("received", $seller_id, $order_type, $store_id));
                $response['processed'] = strval(ordersCount("processed", $seller_id, $order_type, $store_id));
                $response['shipped'] = strval(ordersCount("shipped", $seller_id, $order_type, $store_id));
                $response['delivered'] = strval(ordersCount("delivered", $seller_id, $order_type, $store_id));
                $response['cancelled'] = strval(ordersCount("cancelled", $seller_id, $order_type, $store_id));
                $response['returned'] = strval(ordersCount("returned", $seller_id, $order_type, $store_id));
                $response['data'] = $order_details['order_data'];
            } else {
                $response['error'] = true;
                $response['message'] = 'Data Does Not Exists';
                $response['language_message_key'] = 'data_does_not_exists';
                $response['total'] = "0";
                $response['awaiting'] = "0";
                $response['received'] = "0";
                $response['processed'] = "0";
                $response['shipped'] = "0";
                $response['delivered'] = "0";
                $response['cancelled'] = "0";
                $response['returned'] = "0";
                $response['data'] = array();
            }
            return response()->json($response);
        }
    }

    public function get_order_items(Request $request)
    {
        /*
            store_id:1
            id:101 { optional }
            user_id:101 { optional }
            order_id:101 { optional }
            active_status: received  {received,delivered,cancelled,processed,returned}     // optional
            start_date : 2020-09-07 or 2020/09/07 { optional }
            end_date : 2021-03-15 or 2021/03/15 { optional }
            search:keyword      // optional
            limit:25            // { default - 25 } optional
            offset:0            // { default - 0 } optional
            sort: oi.id / oi.created_at // { default - id } optional
            order:DESC/ASC      // { default - DESC } optional
        */
        $rules = [
            'user_id' => 'numeric|exists:users,id',
            'store_id' => 'required|numeric|exists:stores,id',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            if (auth()->check()) {
                $user_id = auth()->user()->id;
                $seller_id = Seller::where('user_id', $user_id)->value('id');
            }
            $store_id = $request->input('store_id') ? (int) $request->input('store_id') : '';
            $language_code = $request->attributes->get('language_code');
            $limit = $request->input('limit', 25);
            $offset = $request->input('offset', 0);
            $sort = $request->input('sort', 'oi.id');
            $order = $request->input('order', 'DESC');
            $search = $request->input('search', '');
            $id = $request->input('id', false);
            $userId = $request->input('user_id', false);
            $order_id = $request->input('order_id', false);
            $start_date = $request->input('start_date', false);
            $end_date = $request->input('end_date', false);
            $activeStatus = $request->input('active_status');

            // Check if active_status is present and not empty, then split it
            $multipleStatus = (!empty($activeStatus)) ? explode(',', $activeStatus) : false;

            $order_details = fetchOrderItems($id, $userId, $multipleStatus, false, $limit, $offset, $sort, $order, $start_date, $end_date, $search, $seller_id, $order_id, $store_id, $language_code);

            if (!empty($order_details['order_data'])) {
                $response['error'] = false;
                $response['message'] = 'Data retrieved successfully';
                $response['language_message_key'] = 'data_retrieved_successfully';
                $response['total'] = $order_details['total'];
                $response['awaiting'] = strval(ordersCount("awaiting", $seller_id));
                $response['received'] = strval(ordersCount("received", $seller_id));
                $response['processed'] = strval(ordersCount("processed", $seller_id));
                $response['shipped'] = strval(ordersCount("shipped", $seller_id));
                $response['delivered'] = strval(ordersCount("delivered", $seller_id));
                $response['cancelled'] = strval(ordersCount("cancelled", $seller_id));
                $response['returned'] = strval(ordersCount("returned", $seller_id));
                $response['data'] = $order_details['order_data'];
            } else {
                $response['error'] = true;
                $response['message'] = 'Data Does Not Exists';
                $response['language_message_key'] = 'data_does_not_exists';
                $response['total'] = "0";
                $response['awaiting'] = "0";
                $response['received'] = "0";
                $response['processed'] = "0";
                $response['shipped'] = "0";
                $response['delivered'] = "0";
                $response['cancelled'] = "0";
                $response['returned'] = "0";
                $response['data'] = array();
            }
            return response()->json($response);
        }
    }

    public function update_order_item_status(Request $request, SellerOrderController $SellerOrderController)
    {
        /*
            order_item_id[]:1 // only when status is cancelled / returned
            order_id:991
            seller_id : 8
            status : received / processed / shipped / delivered / cancelled / returned
            deliver_by: 15 {optional} //pass delivery_boy id
        */

        $validator = Validator::make($request->all(), [
            'deliver_by' => 'numeric',
            'order_id' => 'numeric|required|exists:orders,id',
        ]);

        if ($request->input('status') === 'cancelled' || $request->input('status') === 'returned') {
            $validator = Validator::make($request->all(), [
                'order_item_id' => 'required',
            ]);
        }

        if ($validator->fails()) {
            $response = [
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            ];
            return response()->json($response);
        } else {
            if (auth()->check()) {
                $user_id = auth()->user()->id;
                $seller_id = Seller::where('user_id', $user_id)->value('id');
            }
            $request['seller_id'] = $seller_id;
            $request['order_item_id'] = explode(',', $request['order_item_id']);
            $orderData = $SellerOrderController->update_order_status($request);
            return response()->json($orderData->original);
        }
    }

    public function get_categories(Request $request, CategoryController $categoryController)
    {
        /*
            store_id : 1;
        */
        $rules = [
            'store_id' => 'required|numeric|exists:stores,id',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            if (auth()->check()) {
                $user_id = auth()->user()->id;
                $seller_id = Seller::where('user_id', $user_id)->value('id');
            }
            $request['seller_id'] = $seller_id;
            $language_code = $request->attributes->get('language_code');
            $cat_res = $categoryController->get_seller_categories($request, $language_code);
            $total = $cat_res->original['total'] ?? 0;
            $response['error'] = (empty($cat_res->original['categories'])) ? true : false;
            $response['message'] = (empty($cat_res->original['categories'])) ? 'Category does not exist' : 'Category retrieved successfully';
            $response['total'] = $total;
            $response['language_message_key'] = (empty($cat_res->original['categories'])) ? 'categories_does_not_exist' : 'categories_retrived_successfully';
            $response['data'] = $cat_res->original['categories'];
            return response()->json($response);
        }
    }
    public function get_all_categories(AdminCategoryController $AdmincategoryController, Request $request)
    {
        /*
            store_id:3
            id:15               // optional
            limit:25            // { default - 25 } optional
            offset:0            // { default - 0 } optional
            sort:               id / name // { default -row_id } optional
            order:DESC/ASC      // { default - ASC } optional
            has_child_or_item:false { default - true}  optional
                                */
        $rules = [
            'id' => 'numeric|exists:categories,id',
            'limit' => 'numeric',
            'offset' => 'numeric',
            'store_id' => 'required|exists:stores,id',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {

            $store_id = $request->input('store_id') ? (int) $request->input('store_id') : '';

            $id = $request->filled('id') ? (int) $request->input('id') : '';
            $ids = $request->filled('ids') ? $request->input('ids') : '';
            $search = $request->filled('search') ? trim($request->input('search')) : '';
            $limit = $request->filled('limit') ? (int) $request->input('limit') : 25;
            $offset = $request->filled('offset') ? (int) $request->input('offset') : 0;
            $sort = $request->filled('sort') ? $request->input('sort') : 'row_order';
            $order = $request->filled('order') ? $request->input('order') : 'ASC';
            $has_child_or_item = $request->filled('has_child_or_item') ? $request->input('has_child_or_item') : 'true';

            $response = ['message' => 'Category(s) retrieved successfully'];
            $cat_res = $AdmincategoryController->get_categories($id, $limit, $offset, $sort, $order, $has_child_or_item, '', '', '', $store_id, $search, $ids);
            $popular_categories = $AdmincategoryController->get_categories(NULL, "", "", 'c1.clicks', 'DESC', 'false', "", "", "", $store_id);

            return response()->json([
                'error' => $cat_res->original['categories']->isEmpty() ? true : false,
                'total' => $cat_res->original['total'],
                'message' => $cat_res->original['categories']->isEmpty() ? 'Category does not exist' : 'Category retrieved successfully',
                'language_message_key' => $cat_res->original['categories']->isEmpty() ? 'categories_does_not_exist' : 'categories_retrived_successfully',
                'data' => $cat_res->original['categories'],
                'popular_categories' => $popular_categories->original['categories'],
            ]);
        }
    }
    public function get_products(Request $request)
    {
        /*
            store_id : 1;
            id:101              // optional
            category_id:29      // optional
            user_id:15          // optional
            search:keyword      // optional
            tags:multiword tag1, tag2, another tag      // optional
            flag:low/sold      // optional
            attribute_value_ids : 34,23,12 // { Use only for filteration } optional
            limit:25            // { default - 25 } optional
            offset:0            // { default - 0 } optional
            sort:p.id / p.created_at / pv.price
            order:DESC/ASC      // { default - DESC } optional
            is_similar_products:1 // { default - 0 } optional
            top_rated_product: 1 // { default - 0 } optional
            show_only_active_products:0 { default - 1 } optional
            show_only_stock_product:0 { default - 1 } optional
        */

        $rules = [
            'store_id' => 'required|numeric|exists:stores,id',
            'id' => 'numeric|exists:products,id',
            'category_id' => 'numeric|exists:categories,id',
            'sort' => 'string',
            'limit' => 'numeric',
            'offset' => 'numeric',
            'is_similar_products' => 'numeric',
            'top_rated_product' => 'numeric',
        ];

        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            if (auth()->check()) {
                $user_id = auth()->user()->id;
                $seller_id = Seller::where('user_id', $user_id)->value('id');
            }
            $limit = $request->has('limit') ? $request->input('limit') : 25;
            $id = $request->has('id') ? $request->input('id') : '';
            $offset = $request->has('offset') ? $request->input('offset') : 0;
            $order = $request->has('order') && trim($request->input('order')) !== '' ? $request->input('order') : 'ASC';
            $store_id = $request->input('store_id');
            $sort = $request->has('sort') && trim($request->input('sort')) !== '' ? $request->input('sort') : 'p.row_order';
            $is_detailed_data = $request->has('is_detailed_data') ? $request->input('is_detailed_data') : 0;
            $type = $request->has('type') ? $request->input('type') : '';

            $filters = [
                'search' => $request->input('search', null),
                'tags' => $request->input('tags', ''),
                'flag' => $request->has('flag') && $request->input('flag') !== '' ? $request->input('flag') : '',
                'attribute_value_ids' => $request->input('attribute_value_ids', null),
                'is_similar_products' => $request->input('is_similar_products', null),
                'product_type' => $request->input('top_rated_product') == 1 ? 'top_rated_product_including_all_products' : null,
                'show_only_active_products' => $request->input('show_only_active_products', true),
                'show_only_stock_product' => $request->input('show_only_stock_product', false),
            ];

            $category_id = $request->input('category_id', null);
            $product_id = $request->input('id', null);
            $user_id = $request->input('user_id', null);
            $language_code = $request->attributes->get('language_code');
            $products = fetchProduct($user_id, (isset($filters)) ? $filters : $id, $product_id, $category_id, $limit, $offset, $sort, $order, null, null, $seller_id, '', $store_id, $is_detailed_data, $type, 1, $language_code);
            if (!empty($products['product'])) {
                $filtered_brand_ids = array_filter($products['brand_ids'], function ($value) {
                    return !empty($value);
                });
                $brand_ids = implode(',', $filtered_brand_ids);
                $response['error'] = false;
                $response['message'] = "Products retrieved successfully !";
                $response['language_message_key'] = "products_retrived_successfully";
                $response['category_ids'] = isset($products['category_ids']) && !empty($products['category_ids']) ? implode(',', $products['category_ids']) : '';
                $response['brand_ids'] = isset($products['brand_ids']) && !empty($products['brand_ids']) ? $brand_ids : '';
                $response['filters'] = (isset($products['filters']) && !empty($products['filters'])) ? $products['filters'] : [];
                $response['total'] = (isset($products['total'])) ? strval($products['total']) : '';
                $response['offset'] = $offset;
                $response['data'] = $products['product'];
            } else {
                $response['error'] = true;
                $response['message'] = "Products Not Found !";
                $response['language_message_key'] = "products_not_found";
                $response['data'] = array();
            }
            return response()->json($response);
        }
    }

    public function get_transactions(Request $request)
    {
        /*
            id: 1001                // { optional}
            type : credit / debit - for wallet // { optional }
            search : Search keyword // { optional }
            limit:25                // { default - 25 } optional
            offset:0                // { default - 0 } optional
            sort: id / date_created // { default - id } optional
            order:DESC/ASC          // { default - DESC } optional
        */
        $rules = [
            'transaction_type' => 'string',
            'type' => 'string',
            'sort' => 'string',
            'limit' => 'numeric',
            'offset' => 'numeric',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            if (auth()->check()) {
                $user_id = auth()->user()->id;
            }
            $id = $request->filled('id') && is_numeric($request->input('id')) ? $request->input('id') : '';
            $type = $request->filled('type') ? $request->input('type') : '';
            $search = $request->filled('search') ? trim($request->input('search')) : '';
            $limit = $request->filled('limit') && is_numeric($request->input('limit')) ? $request->input('limit') : 25;
            $offset = $request->filled('offset') && is_numeric($request->input('offset')) ? $request->input('offset') : 0;
            $order = $request->filled('order') ? $request->input('order') : 'DESC';
            $sort = $request->filled('sort') ? $request->input('sort') : 'id';

            $res = getTransactions($id, $user_id, 'wallet', $type, $search, $offset, $limit, $sort, $order);
            $response['error'] = !$res['data']->isEmpty() ? false : true;
            $response['message'] = !$res['data']->isEmpty() ? 'Transactions Retrieved Successfully' : 'Transactions does not exists';
            $response['language_message_key'] = !$res['data']->isEmpty() ? 'transactions_retrieved_successfully' : 'transaction_not_exist';
            $response['total'] = !$res['data']->isEmpty() ? $res['total'] : 0;
            $response['data'] = !$res['data']->isEmpty() ? $res['data'] : [];
            return response()->json($response);
        }
    }

    public function get_statistics(Request $request)
    {
        /*
            store_id : 1;
        */
        $rules = [
            'store_id' => 'required|numeric|exists:stores,id',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            if (auth()->check()) {
                $user_id = auth()->user()->id;
                $seller_id = Seller::where('user_id', $user_id)->value('id');
            }
            $store_id = $request->input('store_id');
            $currencyDetails = fetchDetails('currencies', ['is_default' => 1], 'symbol');
            $currency = !empty($currencyDetails) ? $currencyDetails[0]->symbol : '';
            $bulkData = $tempRow = $tempRow1 = $tempRow2 = array();
            $bulkData['error'] = false;
            $bulkData['message'] = 'Data retrieved successfully';
            $bulkData['language_message_key'] = 'data_retrieved_successfully';
            $bulkData['currency_symbol'] = !empty($currency) ? $currency : '';

            $result = DB::table('categories as c')
                ->selectRaw('c.id as category_id, c.name as name, count(c.id) as counter')
                ->join('products as p', 'p.category_id', '=', 'c.id')
                ->where('p.status', 1)
                ->where('c.status', 1)
                ->where('c.store_id', $store_id)
                ->where('p.seller_id', $seller_id)
                ->where('p.store_id', $store_id)
                ->groupBy('c.id', 'c.name')
                ->get();

            $language_code = $request->attributes->get('language_code');

            $tempRow = [
                'cat_name' => $result->map(function ($item) use ($language_code) {
                    return getDynamicTranslation('categories', 'name', $item->category_id, $language_code);
                })->toArray(),
                'counter' => $result->pluck('counter')->toArray(),
            ];


            $bulkData['category_wise_product_count'] = $tempRow;

            // overall sale
            $overallSale = DB::table('order_items')
                ->selectRaw('SUM(sub_total) as overall_sale')
                ->where('seller_id', $seller_id)
                ->where('store_id', $store_id)
                ->where('active_status', 'delivered')
                ->first();

            $tempRow1['overall_sale'] = $overallSale->overall_sale ?? 0;

            // daily earnings

            $endDate = Carbon::now();
            $startDate = $endDate->copy()->subDays(29);

            $dayRes = DB::table('order_items')
                ->selectRaw("DAY(created_at) as date, SUM(sub_total) as total_sale")
                ->where('seller_id', $seller_id)
                ->where('store_id', $store_id)
                ->where('created_at', '>=', $startDate)
                ->groupBy(DB::raw('DAY(created_at)'))
                ->get();


            $dayWiseSales['total_sale'] = $dayRes->pluck('total_sale')->map(function ($value) {
                return intval($value);
            })->toArray();
            $dayWiseSales['day'] = $dayRes->pluck('date')->toArray();

            $tempRow1['daily_earnings'] = $dayWiseSales;

            // weekly earnings

            $startOfWeek = Carbon::now()->startOfWeek(); // Start of the current week (Sunday)
            $endOfWeek = Carbon::now()->endOfWeek(); // End of the current week (Saturday)

            $weekRes = DB::table('order_items')
                ->selectRaw("DATE_FORMAT(created_at, '%d-%b') as date, SUM(sub_total) as total_sale")
                ->where('seller_id', $seller_id)
                ->where('store_id', $store_id)
                ->whereBetween(DB::raw('date(created_at)'), [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
                ->groupBy(DB::raw('DAY(created_at)'))
                ->get();

            $weekWiseSales['total_sale'] = $weekRes->pluck('total_sale')->map(function ($value) {
                return intval($value);
            })->toArray();
            $weekWiseSales['week'] = $weekRes->pluck('date')->toArray();

            $tempRow1['weekly_earnings'] = $weekWiseSales;

            // monthly earnings

            $monthRes = DB::table('order_items')
                ->selectRaw('SUM(sub_total) AS total_sale, DATE_FORMAT(created_at, "%b") AS month_name')
                ->where('seller_id', $seller_id)
                ->where('store_id', $store_id)
                ->groupBy(DB::raw('YEAR(CURDATE()), MONTH(created_at)'))
                ->orderBy(DB::raw('YEAR(CURDATE()), MONTH(created_at)'))
                ->get();

            $monthWiseSales['total_sale'] = $monthRes->pluck('total_sale')->map(function ($value) {
                return intval($value);
            })->toArray();
            $monthWiseSales['month_name'] = $monthRes->pluck('month_name')->toArray();

            $tempRow1['monthly_earnings'] = $monthWiseSales;
            $rows1[] = $tempRow1;
            $bulkData['earnings'] = $rows1;

            $tempRow2 = [];

            // Count products with low stock status
            $countProductsLowStatus = countProductsStockLowStatus($seller_id, $store_id);

            // Count products with sold-out status
            $countProductsSoldOutStatus = countProductsAvailabilityStatus($seller_id, $store_id);

            $tempRow2['order_counter'] = strval(ordersCount("", $seller_id, '', $store_id));
            $tempRow2['delivered_orders_counter'] = strval(ordersCount("delivered", $seller_id, '', $store_id));
            $tempRow2['cancelled_orders_counter'] = strval(ordersCount("cancelled", $seller_id, '', $store_id));
            $tempRow2['returned_orders_counter'] = strval(ordersCount("returned", $seller_id, '', $store_id));
            $tempRow2['received_orders_counter'] = strval(ordersCount("received", $seller_id, '', $store_id));
            $tempRow2['product_counter'] = countProducts($seller_id, $store_id);
            $tempRow2['user_counter'] = (getSellerPermission($seller_id, $store_id, 'customer_privacy')) ? count_new_user() : "0";
            $tempRow2['permissions'] = getSellerPermission($seller_id, $store_id);
            $tempRow2['count_products_low_status'] = strval($countProductsLowStatus);
            $tempRow2['count_products_sold_out_status'] = (isset($countProductsSoldOutStatus) && ($countProductsSoldOutStatus != "")) ? strval($countProductsSoldOutStatus) : "0";

            $bulkData['counts'] = [$tempRow2];
            return response()->json($bulkData);
        }
    }

    public function update_fcm(Request $request)
    {
        // Validation rules

        $rules = [
            'user_id' => 'sometimes|numeric|exists:users,id',
            'fcm_id' => 'required',
            'is_delete' => 'sometimes|boolean',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        }

        // Check if the user is authenticated
        if (auth()->check()) {
            $user_id = auth()->user()->id;
        }

        // Get fcm_id from request
        $fcm_id = $request->input('fcm_id') ? $request->input('fcm_id') : '';
        $is_delete = $request->input('is_delete'); // New delete parameter

        // If the delete parameter is set to 1, handle deletion
        if ($is_delete == 1) {
            if (isset($user_id) && !empty($user_id) && !empty($fcm_id)) {
                // Delete the entry from user_fcm table
                $deleted = DB::table('user_fcm')
                    ->where('user_id', $user_id)
                    ->where('fcm_id', $fcm_id)
                    ->delete();

                if ($deleted) {
                    $response = [
                        'error' => false,
                        'message' => 'FCM ID deleted successfully',
                        'language_message_key' => 'deleted_successfully',
                        'data' => [],
                    ];
                } else {
                    $response = [
                        'error' => true,
                        'message' => 'No entry found to delete!',
                        'language_message_key' => 'no_entry_found',
                        'data' => [],
                    ];
                }
            } else {
                // Handle case where user_id or fcm_id is not set
                $response = [
                    'error' => true,
                    'message' => 'User ID and FCM ID are required for deletion!',
                    'language_message_key' => 'user_id_fcm_id_required',
                    'data' => [],
                ];
            }
        } else {
            // Handle insertion logic
            if (!empty($fcm_id)) {
                if (isset($user_id) && !empty($user_id)) {
                    // Prepare the data for insertion
                    $fcm_data = [
                        'fcm_id' => $fcm_id,
                        'user_id' => $user_id,
                    ];

                    // Check if the FCM ID already exists for the user
                    $existing_fcm = DB::table('user_fcm')
                        ->where('user_id', $user_id)
                        ->where('fcm_id', $fcm_id)
                        ->first();

                    if (!$existing_fcm) {
                        // If it doesn't exist, create a new entry
                        $user_res = DB::table('user_fcm')->insert($fcm_data);

                        // Prepare the response
                        if ($user_res) {
                            $response = [
                                'error' => false,
                                'message' => 'FCM ID stored successfully',
                                'language_message_key' => 'stored_successfully',
                                'data' => [],
                            ];
                        } else {
                            $response = [
                                'error' => true,
                                'message' => 'Insertion Failed!',
                                'language_message_key' => 'insertion_failed',
                                'data' => [],
                            ];
                        }
                    } else {
                        // If the FCM ID already exists, prepare a response indicating this
                        $response = [
                            'error' => true,
                            'message' => 'FCM ID already exists for this user.',
                            'language_message_key' => 'fcm_id_exists',
                            'data' => [],
                        ];
                    }
                } else {
                    // Handle case where user_id is not set
                    $response = [
                        'error' => true,
                        'message' => 'User ID is required!',
                        'language_message_key' => 'user_id_required',
                        'data' => [],
                    ];
                }
            }
        }

        return response()->json($response);
    }
    public function get_cities(Request $request, AreaController $areaController)
    {
        /*
           sort:               // { c.name / c.id } optional
           order:DESC/ASC      // { default - ASC } optional
           search:value        // {optional}
           offset: 0 {optional}
           limit: 10 {optional}
       */

        $rules = [
            'sort' => 'string',
            'limit' => 'numeric',
            'offset' => 'numeric',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            $language_code = $request->attributes->get('language_code');
            $city_data = $areaController->city_list($request, $language_code);
            if (empty($city_data->original['rows']) || $city_data->original['total'] == 0) {
                $response['error'] = true;
                $response['message'] = 'Data Does Not Exists  !';
                $response['language_message_key'] = 'data_does_not_exists';
                $response['data'] = array();
            } else {
                $response['error'] = false;
                $response['message'] = 'Cities retrieved successfully!';
                $response['language_message_key'] = 'cities_retrived_successfully';
                $response['total'] = $city_data->original['total'];
                $response['data'] = $city_data->original['rows'];
            }
            return response()->json($response);
        }
    }

    public function get_zipcodes(Request $request, AreaController $areaController)
    {
        /*
           sort:               // { c.name / c.id } optional
           order:DESC/ASC      // { default - ASC } optional
           search:value        // {optional}
           offset: 0 {optional}
           limit: 10 {optional}
       */

        $rules = [
            'sort' => 'string',
            'limit' => 'numeric',
            'offset' => 'numeric',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            $language_code = $request->attributes->get('language_code');
            $zipcode_data = $areaController->zipcode_list($request, $language_code);


            if ($zipcode_data) {
                $response['error'] = false;
                $response['message'] = 'Zipcode retrieved successfully!';
                $response['language_message_key'] = 'zipcodes_retrieved_successfully!';
                $response['total'] = $zipcode_data->original['total'];
                $response['data'] = $zipcode_data->original['rows'];
            } else {
                $response['error'] = true;
                $response['message'] = 'Zipcode(s) does not exist!';
                $response['language_message_key'] = 'zipcodes_not_exist';
                $response['data'] = array();
            }
            return response()->json($response);
        }
    }

    public function get_taxes(Request $request)
    {
        $language_code = $request->attributes->get('language_code');

        $taxes = DB::table('taxes')
            ->select('id', 'title', 'percentage', 'status')
            ->where('status', 1)
            ->get();
        $taxes = $taxes->map(function ($tax) use ($language_code) {
            $tax->title = getDynamicTranslation('taxes', 'title', $tax->id, $language_code);
            return $tax;
        });

        if ($taxes->isNotEmpty()) {
            $response['error'] = false;
            $response['message'] = 'Taxes retrieved successfully!';
            $response['language_message_key'] = 'taxes_retrieved_successfully';
            $response['data'] = $taxes;
        } else {
            $response['error'] = true;
            $response['message'] = 'Taxes do not exist!';
            $response['language_message_key'] = 'taxes_not_exist';
            $response['data'] = [];
        }

        return response()->json($response);
    }


    public function send_withdrawal_request(Request $request, PaymentRequestController $paymentRequest)
    {
        /*
            payment_address: 12343535
            amount: 56
        */

        $rules = [
            'payment_address' => 'required',
            'amount' => 'required|numeric|min:0',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            if (auth()->check()) {
                $user_id = auth()->user()->id;
            }

            $request['user_id'] = $user_id;
            $data = $paymentRequest->add_withdrawal_request($request);
            $response['error'] = $data->original['error'];
            $response['message'] = isset($data->original['message']) ? $data->original['message'] : $data->original['error_message'];
            $response['data'] = $data->original['data'];
            return response()->json($response);
        }
    }

    public function get_withdrawal_request(Request $request, PaymentRequestController $paymentRequest)
    {
        /*
           sort:               // { c.name / c.id } optional
           order:DESC/ASC      // { default - ASC } optional
           search:value        // {optional}
           offset: 0 {optional}
           limit: 10 {optional}
       */

        $rules = [
            'sort' => 'string',
            'limit' => 'numeric',
            'offset' => 'numeric',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            if (auth()->check()) {
                $user_id = auth()->user()->id;
            }

            $data = $paymentRequest->get_payment_request_list($request, $user_id);

            $response['error'] = empty($data->original['rows']) ? true : false;
            $response['message'] = empty($data->original['rows']) ? 'No data found' : 'Withdrawal Request Retrieved Successfully';
            $response['language_message_key'] = empty($data->original['rows']) ? 'no_data_found' : 'withdrawal_request_retrieved_successfully';
            $response['total'] = $data->original['total'];
            $response['data'] = $data->original['rows'];

            return response()->json($response);
        }
    }

    public function get_attributes(Request $request, AttributeController $attrubuteController)
    {
        /*
            sort: a.name              // { a.name / a.id } optional
            order:DESC/ASC      // { default - ASC } optional
            search:value        // {optional}
            limit:10  {optional}
            offset:10  {optional}
            attribute_value_ids:160,161  {optional}
        */

        $rules = [
            'store_id' => 'required|exists:stores,id',
            'sort' => 'string',
            'limit' => 'numeric',
            'offset' => 'numeric',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            $request['attribute_ids'] = $request->input('attribute_ids') ? $request->input('attribute_ids') : '';
            $request['attribute_value_ids'] = $request->input('attribute_value_ids') ? $request->input('attribute_value_ids') : '';
            $data = $attrubuteController->list($request);
            foreach ($data->original['rows'] as $row) {

                $tempRow['id'] = $row['id'];
                $tempRow['name'] = $row['name'];
                $tempRow['attribute_value_id'] = $row['attribute_value_id'];
                $tempRow['value'] = $row['value'];
                $status = [
                    '0' => 'Inactive',
                    '1' => 'Active',
                ];
                $tempRow['status_code'] = $row['status_code'];
                $tempRow['status'] = $status[$row['status_code']];

                $rows[] = $tempRow;
            }
            $response['error'] = false;
            $response['message'] = 'Attribute Retrieved Successfully';
            $response['language_message_key'] = 'attribute_retrieved_successfully';
            $response['total'] = $data->original['total'];
            $response['data'] = $rows;
            return response()->json($response);
        }
    }

    public function get_attribute_values(Request $request, AttributeController $attrubuteController)
    {
        /*
            store_id :1
            attribute_id : 5 // optional
            sort: a.name              // { a.name / a.id } optional
            order:DESC/ASC      // { default - ASC } optional
            search:value        // {optional}
            limit:10  {optional}
            offset:10  {optional}
        */

        $rules = [
            'store_id' => 'required|exists:stores,id',
            'attribute_id' => 'numeric|exists:attributes,id',
            'sort' => 'string',
            'limit' => 'numeric',
            'offset' => 'numeric',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            $data = $attrubuteController->getAttributeValue($request);
            return response()->json($data);
        }
    }

    public function get_media(Request $request, MediaController $mediaController)
    {
        /*
            store_id : 1
            limit:25            // { default - 25 } optional
            offset:0            // { default - 0 } optional
            sort:               // { id } optional
            order:DESC/ASC      // { default - DESC } optional
            search:value        // {optional}
            type:image          // {documents,spreadsheet,archive,video,audio,image}
        */
        $rules = [
            'store_id' => 'required|exists:stores,id',

        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            if (auth()->check()) {
                $user_id = auth()->user()->id;
                $seller_id = Seller::where('user_id', $user_id)->value('id');
            }
            $request['seller_id'] = $seller_id;

            $media = $mediaController->list($request);

            $rows = [];
            foreach ($media->original['rows'] as $row) {
                $tempRow['id'] = $row['id'];
                $tempRow['name'] = $row['name'];
                $tempRow['image'] = $row['media_image'];
                $tempRow['size'] = $row['size'];
                $tempRow['extension'] = $row['extension'];
                $tempRow['type'] = $row['type'];
                $tempRow['sub_directory'] = $row['sub_directory'];
                $rows[] = $tempRow;
            }

            if (!empty($rows)) {
                $response['error'] = false;
                $response['message'] = 'Media Retrieved Successfully';
                $response['language_message_key'] = 'media_retrieved_successfully';
                $response['total'] = $media->original['total'];
                $response['data'] = $rows;
            } else {
                $response['error'] = true;
                $response['message'] = 'Media not found !';
                $response['language_message_key'] = 'media_not_found';
                $response['total'] = 0;
                $response['data'] = $rows;
            }
            return response()->json($response);
        }
    }

    public function add_products(Request $request, ProductController $productController)
    {
        /*
            store_id:1
            pro_input_name: product name
            short_description: description
            tags:tag1,tag2,tag3     //{comma saprated}
            pro_input_tax[]:tax_id // you can add multiple tax ids like 1,2,3
            indicator:1             //{ 0 - none | 1 - veg | 2 - non-veg }
            made_in: india          //{optional}
            hsn_code: 456789        //{optional}
            brand: 1          //note : pass brand ID {optional}
            total_allowed_quantity:100
            minimum_order_quantity:12
            quantity_step_size:1
            warranty_period:1 month     {optional}
            guarantee_period:1 month   {optional}
            deliverable_type:1        //{0:none, 1:all, 2:include, 3:exclude}
            deliverable_zones[]:1,2,3  //{NULL: if deliverable_type = 0 or 1}
            is_prices_inclusive_tax:0   //{1: inclusive | 0: exclusive}
            cod_allowed:1               //{ 1:allowed | 0:not-allowed }
            download_allowed:1               //{ 1:allowed | 0:not-allowed }
            download_link_type:self_hosted             //{ values : self_hosted | add_link }
            pro_input_zip:file              //when download type is self_hosted add file for download
            download_link : url             //{URL of download file}
            is_returnable:1             // { 1:returnable | 0:not-returnable }
            is_cancelable:1             //{1:cancelable | 0:not-cancelable}
            is_attachment_required:1             //{1:yes | 0:no}
            cancelable_till:            //{received,processed,shipped}
            pro_input_image:file
            other_images: files
            video_type:                 // {values: vimeo | youtube}
            video:                      //{URL of video}
            pro_input_video: file
            pro_input_description:product's description
            extra_input_description:product's extra description
            category_id:99
            attribute_values:1,2,3,4,5
            minimum_free_delivery_order_qty:5 // used when product wise delivery charge is ON
            delivery_charges:10 // used when product wise delivery charge is ON

            pickup_location : jay nagar {optional}
            status:1/0 {optional}
            --------------------------------------------------------------------------------
            till above same params
            --------------------------------------------------------------------------------
            --------------------------------------------------------------------------------
            common param for simple and variable product
            --------------------------------------------------------------------------------
            product_type:simple_product | variable_product  |  digital_product
            variant_stock_level_type:product_level | variable_level
            variant_stock_status: 0             {optional}//{0 =>'Simple_Product_Stock_Active'}

            if(product_type == variable_product):
                variants_ids:3 5,4 5,1 2
                variant_price:100,200
                variant_special_price:90,190
                variant_images:files              //{optional}
                weight : 1,2,3  {optional}
                height :  1,2,3 {optional}
                breadth :  1,2,3 {optional}
                length :  1,2,3 {optional}

                sku_variant_type:test            //{if (variant_stock_level_type == product_level)}
                total_stock_variant_type:100     //{if (variant_stock_level_type == product_level)}
                variant_status:1                 //{if (variant_stock_level_type == product_level)}

                variant_sku:test,test             //{if(variant_stock_level_type == variable_level)}
                variant_total_stock:120,300       //{if(variant_stock_level_type == variable_level)}
                variant_level_stock_status:1,1    //{if(variant_stock_level_type == variable_level)}

            if(product_type == simple_product):
                simple_product_stock_status:null|0|1   {1=in stock | 0=out stock}
                simple_price:100
                simple_special_price:90
                weight : 1  {optional}
                height : 1 {optional}
                breadth : 1 {optional}
                length : 1 {optional}
                product_sku:test                    {optional}
                product_total_stock:100             {optional}


           if(product_type == digital_product):
                simple_price:100
                simple_special_price:90

                for multi language

                translated_product_name: {"hn": "हिंदी उत्पाद नाम","fr": "Nom du produit français"},
                translated_product_short_description": {hn": "हिंदी विवरण","fr": "Description française"}


       */
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|numeric|exists:stores,id',
        ]);
        if ($validator->fails()) {
            $response = [
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            ];
            return response()->json($response);
        } else {
            if (auth()->check()) {
                $user_id = auth()->user()->id;
                $seller_id = Seller::where('user_id', $user_id)->value('id');
            }
            $language_code = $request->attributes->get('language_code');
            $request['seller_id'] = $seller_id;
            $request['variant_price'] = (isset($request['variant_price']) && !empty($request['variant_price'])) ? explode(",", $request['variant_price']) : NULL;
            $request['variant_special_price'] = (isset($request['variant_special_price']) && !empty($request['variant_special_price'])) ? explode(",", $request['variant_special_price']) : NULL;
            $request['variants_ids'] = (isset($request['variants_ids']) && !empty($request['variants_ids'])) ? explode(",", $request['variants_ids']) : NULL;
            $request['variant_sku'] = (isset($request['variant_sku']) && !empty($request['variant_sku'])) ? explode(",", $request['variant_sku']) : NULL;
            $request['variant_total_stock'] = (isset($request['variant_total_stock']) && !empty($request['variant_total_stock'])) ? explode(",", $request['variant_total_stock']) : NULL;
            $request['variant_level_stock_status'] = (isset($request['variant_level_stock_status']) && !empty($request['variant_level_stock_status'])) ? explode(",", $request['variant_level_stock_status']) : NULL;
            $request['other_images'] = (isset($request['other_images']) && !empty($request['other_images'])) ? explode(",", $request['other_images']) : NULL;
            $request['variant_images'] = (isset($request['variant_images']) && !empty($request['variant_images'])) ? json_decode($request['variant_images'], true) : NULL;

            $request['status'] = (isset($request['status']) && ($request['status'] != '')) ? $request['status'] : 1;


            if (isset($request['product_type']) && strtolower($request['product_type']) == 'simple_product') {
                $request['weight'] = (isset($request['weight']) && !empty($request['weight'])) ? $request['weight'] : 0.0;
                $request['height'] = (isset($request['height']) && !empty($request['height'])) ? $request['height'] : 0.0;
                $request['breadth'] = (isset($request['breadth']) && !empty($request['breadth'])) ? $request['breadth'] : 0.0;
                $request['length'] = (isset($request['length']) && !empty($request['length'])) ? $request['length'] : 0.0;
            } else {
                $request['weight'] = (isset($request['weight']) && !empty($request['weight'])) ? explode(",", $request['weight']) : 0.0;
                $request['height'] = (isset($request['height']) && !empty($request['height'])) ? explode(",", $request['height']) : 0.0;
                $request['breadth'] = (isset($request['breadth']) && !empty($request['breadth'])) ? explode(",", $request['breadth']) : 0.0;
                $request['length'] = (isset($request['length']) && !empty($request['length'])) ? explode(",", $request['length']) : 0.0;
            }
            // process image and other images

            $request['zipcodes'] = (!empty($request['deliverable_zones'])) ? $request['deliverable_zones'] : NULL;
            $request['extra_input_description'] = (isset($request['extra_input_description']) && $request['extra_input_description'] != 'NULL' && !empty($request['extra_input_description']) ? $request['extra_input_description'] : '');
            $request['pickup_location'] = (isset($request['pickup_location']) && $request['pickup_location'] != 'NULL' && !empty($request['pickup_location']) ? $request['pickup_location'] : '');
            //    dd($request->deliverable_type);
            $product = $productController->store($request, true, $language_code);


            $response['error'] = $product->original['error'];
            $response['message'] = $product->original['message'];
            $response['data'] = isset($product->original['data']) ? $product->original['data'] : [];
            return response()->json($response);
        }
    }

    public function get_seller_details(Request $request)
    {

        if (auth()->check()) {
            $user = Auth::user();
            $fcm_ids = fetchdetails('user_fcm', ['user_id' => $user->id], 'fcm_id');

            $fcm_ids_array = array_map(function ($item) {
                return $item->fcm_id;
            }, $fcm_ids);

            $userData = formatUserData($user, $fcm_ids_array);
            $language_code = $request->attributes->get('language_code');
            $seller_data = fetchDetails('seller_data', ['user_id' => $user->id], '*');
            $store_data = fetchDetails('seller_store', ['user_id' => $user->id], '*');
            $seller_data[0]->seller_id = $seller_data[0]->id;
            $data = (array_merge($userData, (array) $seller_data));
            $output = $userData;
            unset($seller_data[0]->id);

            $isPublicDisk = $store_data[0]->disk == 'public' ? 1 : 0;

            $output['store_data'] = formatStoreData($store_data, $isPublicDisk, $language_code);
            $output['seller_data'] = array_map(fn($seller) => (array) $seller, formatSellerData($seller_data, $isPublicDisk));
            foreach ($data as $key => $value) {
                if (property_exists(!empty($seller_data) ? $seller_data[0] : '', $key)) {
                    $output[$key] = $value;
                }
            }
            foreach ($data as $key => $value) {
                if (property_exists($seller_data[0], $key)) {
                    $output[$key] = $value;
                }
            }

            if ($user->role_id == 4) {

                unset($data[0]->password);

                return response()->json([
                    'error' => false,
                    'message' => 'Data retrived successfully',
                    'language_message_key' => 'data_retrieved_successfully',
                    'data' => isset($output) ? $output : [],

                ]);
            }
        }
    }

    public function delete_product(Request $request)
    {
        /* Parameters to be passed
            product_id:28
        */

        $rules = [
            'product_id' => 'numeric|required|exists:products,id',

        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            $product_id = $request->input('product_id', 25);
            if (deleteDetails(['product_id' => $product_id], 'product_variants')) {

                deleteDetails(['id' => $product_id], 'products');
                deleteDetails(['product_id' => $product_id], 'product_attributes');
                $response['error'] = false;
                $response['message'] = 'Deleted Successfully';
                $response['language_message_key'] = 'deleted_successfully';
            } else {
                $response['error'] = true;
                $response['message'] = 'Something Went Wrong';
                $response['language_message_key'] = 'something_went_wrong';
            }
            return response()->json($response);
        }
    }

    public function update_products(Request $request, ProductController $productController)
    {
        /*
            edit_product_id:74
            edit_variant_id:104,105
            variants_ids: new created with new attributes added
            seller_id:1255
            pro_input_name: product name
            short_description: description
            tags:tag1,tag2,tag3     //{comma saprated}
            pro_input_tax[]:tax_id // you can add multiple tax ids like 1,2,3
            indicator:1             //{ 0 - none | 1 - veg | 2 - non-veg }
            made_in: india          //{optional}
            hsn_code: 123456         //{optional}
            brand: adidas          //{optional}
            total_allowed_quantity:100
            minimum_order_quantity:12
            quantity_step_size:1
            warranty_period:1 month
            guarantee_period:1 month
            deliverable_type:1        //{0:none, 1:all, 2:include, 3:exclude}
            deliverable_zones[]:1,2,3  //{NULL: if deliverable_type = 0 or 1}
            is_prices_inclusive_tax:0   //{1: inclusive | 0: exclusive}
            cod_allowed:1               //{ 1:allowed | 0:not-allowed }
            download_allowed:1               //{ 1:allowed | 0:not-allowed }
            download_link_type:self_hosted             //{ values : self_hosted | add_link }
            pro_input_zip:file              //when download type is self_hosted add file for download
            download_link : url             //{URL of download file}
            is_returnable:1             // { 1:returnable | 0:not-returnable }
            is_cancelable:1             //{1:cancelable | 0:not-cancelable}
            is_attachment_required:1             //{1:yes | 0:no}
            cancelable_till:            //{received,processed,shipped}
            pro_input_image:file
            other_images: files
            video_type:                 // {values: vimeo | youtube}
            video:                      //{URL of video}
            pro_input_video: file
            pro_input_description:product's description
            extra_input_description:product's extra description
            category_id:99

            pickup_location : jay nagar {optional}
            attribute_values:1,2,3,4,5
            status :1/0 {optional}
            --------------------------------------------------------------------------------
            till above same params
            --------------------------------------------------------------------------------
            --------------------------------------------------------------------------------
            common param for simple and variable product
            --------------------------------------------------------------------------------
            product_type:simple_product | variable_product
            variant_stock_level_type:product_level | variable_level

            if(product_type == variable_product):
                variants_ids:3 5,4 5,1 2
                variant_price:100,200
                variant_special_price:90,190
                variant_images:files              //{optional}
                weight : 1,2,3  {optional}
                height :  1,2,3 {optional}
                breadth :  1,2,3 {optional}
                length :  1,2,3 {optional}

                sku_variant_type:test            //{if (variant_stock_level_type == product_level)}
                total_stock_variant_type:100     //{if (variant_stock_level_type == product_level)}
                variant_status:1                 //{if (variant_stock_level_type == product_level)}

                variant_sku:test,test             //{if(variant_stock_level_type == variable_level)}
                variant_total_stock:120,300       //{if(variant_stock_level_type == variable_level)}
                variant_level_stock_status:1,1    //{if(variant_stock_level_type == variable_level)}

            if(product_type == simple_product):
                simple_product_stock_status:null|0|1   {1=in stock | 0=out stock}
                simple_price:100
                simple_special_price:90
                product_sku:test
                product_total_stock:100
                variant_stock_status: 0            //{0 =>'Simple_Product_Stock_Active' 1 => "Product_Level" 2 => "Variable_Level"	}
                weight : 1  {optional}
                height : 1 {optional}
                breadth : 1 {optional}
                length : 1 {optional}
            if(product_type == digital_product):
                simple_price:100
                simple_special_price:90

                for multi language

                translated_product_name: {"hn": "हिंदी उत्पाद नाम","fr": "Nom du produit français"},
                translated_product_short_description": {hn": "हिंदी विवरण","fr": "Description française"}
       */
        $validator = Validator::make($request->all(), [
            'edit_product_id' => 'numeric|required|exists:products,id',
            'store_id' => 'required|numeric|exists:stores,id',
        ]);
        if ($validator->fails()) {
            $response = [
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            ];
            return response()->json($response);
        } else {
            if (auth()->check()) {
                $user_id = auth()->user()->id;
                $seller_id = Seller::where('user_id', $user_id)->value('id');
            }
            $language_code = $request->attributes->get('language_code');
            $request['seller_id'] = $seller_id;
            $request['variant_price'] = (isset($request['variant_price']) && !empty($request['variant_price'])) ? explode(",", $request['variant_price']) : NULL;
            $request['variant_special_price'] = (isset($request['variant_special_price']) && !empty($request['variant_special_price'])) ? explode(",", $request['variant_special_price']) : NULL;
            $request['variants_ids'] = (isset($request['variants_ids']) && !empty($request['variants_ids'])) ? explode(",", $request['variants_ids']) : NULL;
            $request['edit_variant_id'] = (isset($request['edit_variant_id']) && !empty($request['edit_variant_id'])) ? explode(",", $request['edit_variant_id']) : NULL;
            $request['variant_sku'] = (isset($request['variant_sku']) && !empty($request['variant_sku'])) ? explode(",", $request['variant_sku']) : NULL;
            $request['variant_total_stock'] = (isset($request['variant_total_stock']) && !empty($request['variant_total_stock'])) ? explode(",", $request['variant_total_stock']) : NULL;
            $request['variant_level_stock_status'] = (isset($request['variant_level_stock_status']) && !empty($request['variant_level_stock_status'])) ? explode(",", $request['variant_level_stock_status']) : NULL;
            $request['other_images'] = (isset($request['other_images']) && !empty($request['other_images'])) ? explode(",", $request['other_images']) : [];
            $request['variant_images'] = (isset($request['variant_images']) && !empty($request['variant_images'])) ? json_decode($request['variant_images'], true) : NULL;


            if (isset($request['product_type']) && strtolower($request['product_type']) == 'simple_product') {
                $request['weight'] = (isset($request['weight']) && !empty($request['weight'])) ? $request['weight'] : 0.0;
                $request['height'] = (isset($request['height']) && !empty($request['height'])) ? $request['height'] : 0.0;
                $request['breadth'] = (isset($request['breadth']) && !empty($request['breadth'])) ? $request['breadth'] : 0.0;
                $request['length'] = (isset($request['length']) && !empty($request['length'])) ? $request['length'] : 0.0;
            } else {
                $request['weight'] = (isset($request['weight']) && !empty($request['weight'])) ? explode(",", $request['weight']) : 0.0;
                $request['height'] = (isset($request['height']) && !empty($request['height'])) ? explode(",", $request['height']) : 0.0;
                $request['breadth'] = (isset($request['breadth']) && !empty($request['breadth'])) ? explode(",", $request['breadth']) : 0.0;
                $request['length'] = (isset($request['length']) && !empty($request['length'])) ? explode(",", $request['length']) : 0.0;
            }
            // process image and other images

            $request['zipcodes'] = (!empty($request['deliverable_zones'])) ? $request['deliverable_zones'] : NULL;
            $request['extra_input_description'] = (isset($request['extra_input_description']) && $request['extra_input_description'] != 'NULL' && !empty($request['extra_input_description']) ? $request['extra_input_description'] : '');
            $request['pickup_location'] = (isset($request['pickup_location']) && $request['pickup_location'] != 'NULL' && !empty($request['pickup_location']) ? $request['pickup_location'] : '');
            $product = $productController->update($request, $request['edit_product_id'], true, $language_code);

            $response['error'] = $product->original['error'];
            $response['message'] = $product->original['message'];
            $response['data'] = isset($product->original['data']) ? $product->original['data'] : [];
            return response()->json($response);
        }
    }

    public function get_delivery_boys(Request $request)
    {
        $rules = [
            'id' => 'numeric',
            'sort' => 'string',
            'limit' => 'numeric',
            'offset' => 'numeric',
            'store_id' => 'required|numeric|exists:stores,id',

        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            if (auth()->check()) {
                $user_id = auth()->user()->id;
                $seller_id = Seller::where('user_id', $user_id)->value('id');
            }
            $store_id = $request->input('store_id');
            $store_deliverability_type = fetchDetails('stores', ['id' => $store_id], 'product_deliverability_type');
            $store_deliverability_type = isset($store_deliverability_type) && !empty($store_deliverability_type) ? $store_deliverability_type[0]->product_deliverability_type : "";


            // Get seller's city and pincode
            $seller_store = DB::table('seller_store')->where('user_id', $user_id)->where('store_id', $store_id)->select('city', 'zipcode', 'deliverable_zones', 'deliverable_type')->get();


            $seller_zone_ids = isset($seller_store) ? explode(',', $seller_store[0]->deliverable_zones) : [];
            $deliverable_type = isset($seller_store) ? $seller_store[0]->deliverable_type : 1;
            $seller_city = isset($seller_store) ? $seller_store[0]->city : "";
            $seller_zipcode = isset($seller_store) ? $seller_store[0]->zipcode : "";

            $limit = $request->input('limit', 25);
            $offset = $request->input('offset', 0);
            $sort = $request->input('sort', 'u.id');
            $order = $request->input('order', 'DESC');
            $search = $request->input('search', '');
            $id = $request->input('id', false);

            $data = getDeliveryBoys($id, $search, $offset, $limit, $sort, $order, $seller_city, $seller_zipcode, $store_deliverability_type, $seller_zone_ids, $deliverable_type);
            return response()->json($data);
        }
    }

    public function upload_media(Request $request, MediaController $mediaController)
    {
        /*
            store_id = 1
            documents:file
        */

        $rules = [
            'store_id' => 'required|numeric|exists:stores,id',
            'documents' => 'required',
        ];

        $messages = [
            'documents.required' => 'Upload at least one media file!',
        ];

        // Pass $messages only if it's not empty
        if ($validationResponse = validateRequest($request, $rules, !empty($messages) ? $messages : [])) {
            return $validationResponse;
        } else {
            if (auth()->check()) {
                $user_id = auth()->user()->id;
                $seller_id = Seller::where('user_id', $user_id)->value('id');
            }
            $request['seller_id'] = $seller_id;

            $media = $mediaController->upload($request);
            $response = [
                'error' => $media->original['error'],
                'message' => $media->original['message'],
                'data' => $media->original['media_paths'],
                'type' => $media->original['type'],
                'file_mime' => $media->original['file_mime'],
            ];
            return response()->json($response);
        }
    }

    public function get_product_rating(Request $request)
    {
        /*
            product_id: 1001
            user_id: 10 // { optional }
            limit:25                // { default - 25 } optional
            offset:0                // { default - 0 } optional
            sort: id // { default - id} optional
            order:DESC/ASC          // { default - DESC } optional
        */
        $rules = [
            'product_id' => 'required|numeric|exists:products,id',
            'user_id' => 'numeric|exists:users,id',
            'sort' => 'string',
            'limit' => 'numeric',
            'offset' => 'numeric',

        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            $product_id = $request->input('product_id');
            $user_id = $request->filled('user_id') ? $request->input('user_id') : '';
            $limit = $request->filled('limit') ? $request->input('limit') : 25;
            $offset = $request->filled('offset') ? $request->input('offset') : 0;
            $sort = $request->filled('sort') ? $request->input('sort') : 'id';
            $order = $request->filled('order') ? $request->input('order') : 'DESC';
            $has_images = $request->filled('has_images') ? 1 : 0;

            // update category clicks
            $category_id = fetchDetails('products', ['id' => $product_id], 'category_id')[0]->category_id;
            if ($category_id !== null) {
                $category = Category::find($category_id);
                if ($category) {
                    $category->increment('clicks');
                }
            }
            $rating = $request->input('rating') != null ? $request->input('rating') : '';
            $pr_rating = fetchDetails('products', ['id' => $product_id], 'rating');
            $rating = fetchRating($product_id, $user_id, $limit, $offset, $sort, $order, '', $has_images, 'true', $rating);
            if (!empty($rating['product_rating'])) {
                $response['error'] = false;
                $response['message'] = 'Rating retrieved successfully';
                $response['language_message_key'] = 'rating_retrieved_successfully';
                $response['no_of_rating'] = (!empty($rating['no_of_rating'])) ? $rating['no_of_rating'] : 0;
                $response['no_of_reviews'] = (!empty($rating['no_of_reviews'])) ? $rating['no_of_reviews'] : 0;
                $response['total'] = $rating['total_reviews'];
                $response['star_1'] = $rating['star_1'];
                $response['star_2'] = $rating['star_2'];
                $response['star_3'] = $rating['star_3'];
                $response['star_4'] = $rating['star_4'];
                $response['star_5'] = $rating['star_5'];
                $response['total_images'] = $rating['total_images'];
                $response['product_rating'] = (!empty($pr_rating)) ? $pr_rating[0]->rating : "0";
                $response['data'] = $rating['product_rating'];
            } else {
                $response['error'] = true;
                $response['message'] = 'No ratings found !';
                $response['no_of_rating'] = (!empty($rating['no_of_rating'])) ? $rating['no_of_rating'] : 0;
                $response['no_of_reviews'] = (!empty($rating['no_of_reviews'])) ? $rating['no_of_reviews'] : 0;

                $response['star_1'] = $rating['star_1'];
                $response['star_2'] = $rating['star_2'];
                $response['star_3'] = $rating['star_3'];
                $response['star_4'] = $rating['star_4'];
                $response['star_5'] = $rating['star_5'];
                $response['total_images'] = $rating['total_images'];
                $response['product_rating'] = (!empty($pr_rating)) ? $pr_rating[0]->rating : "0";
                $response['data'] = array();
            }
            return response()->json($response);
        }
    }
    public function get_combo_product_rating(Request $request, ComboProductRatingController $ProductRatingController)
    {
        $rules = [
            'product_id' => 'required|numeric|exists:combo_products,id',
            'user_id' => 'numeric|exists:users,id',
            'sort' => 'string',
            'limit' => 'numeric',
            'offset' => 'numeric',
            'order' => 'string',
            'has_images' => 'boolean',

        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        }

        $user_id = $request->input('user_id');
        $product_id = $request->input('product_id');

        $limit = $request->input('limit', 25);
        $offset = $request->input('offset', 0);
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'DESC');
        $has_images = $request->input('has_images', false);
        // update category click

        $category_id = fetchDetails('products', ['id' => $product_id], 'category_id');

        DB::table('categories')->where('id', $category_id[0]->category_id)->increment('clicks');


        $pr_rating = fetchDetails('products', ['id' => $product_id], 'rating');

        $rating = $request->input('rating') != null ? $request->input('rating') : '';
        $rating = $ProductRatingController->fetch_rating(($request->input('product_id') != null) ? $request->input('product_id') : '', $user_id, $limit, $offset, $sort, $order, '', $has_images, $rating);

        if (!empty($rating['product_rating'])) {
            $response['error'] = false;
            $response['message'] = 'Rating retrieved successfully';
            $response['language_message_key'] = 'ratings_retrived_successfully';
            $response['no_of_rating'] = (!empty($rating['rating'][0]['no_of_rating'])) ? $rating['rating'][0]['no_of_rating'] : 0;
            $response['total'] = $rating['total_reviews'];
            $response['star_1'] = $rating['star_1'];
            $response['star_2'] = $rating['star_2'];
            $response['star_3'] = $rating['star_3'];
            $response['star_4'] = $rating['star_4'];
            $response['star_5'] = $rating['star_5'];
            $response['total_images'] = $rating['total_images'];
            $response['product_rating'] = (!empty($pr_rating)) ? $pr_rating[0]->rating : "0";
            $response['data'] = $rating['product_rating'];
        } else {
            $response['error'] = true;
            $response['message'] = 'No ratings found !';
            $response['language_message_key'] = 'no_ratings_found';
            $response['no_of_rating'] = array();
            $response['data'] = array();
        }
        return $response;
    }
    public function get_order_tracking(Request $request, SellerOrderController $orderController)
    {
        /*
            order_id:10
            limit:25            // { default - 25 } optional
            offset:0            // { default - 0 } optional
            sort:               // { id } optional
            order:DESC/ASC      // { default - DESC } optional
            search:value        // {optional}
        */
        $rules = [
            'order_id' => 'required|numeric|exists:orders,id',
            'sort' => 'string',
            'limit' => 'numeric',
            'offset' => 'numeric',

        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            $order_tracking_data = $orderController->getSellerOrderTrackingList($request);

            $response['error'] = false;
            $response['message'] = 'Data retrived successfully !';
            $response['language_message_key'] = 'data_retrieved_successfully';
            $response['total'] = $order_tracking_data['total'];

            $response['data'] = isset($order_tracking_data['rows']) ? $order_tracking_data['rows'] : [];
        }
        return response()->json($response);
    }

    public function edit_order_tracking(Request $request, SellerOrderController $orderController)
    {
        /*
            order_id:57
            parcel_id:1
            courier_agency:asd agency
            tracking_id:t_id123
            url:http://test.com
        */

        $data = $orderController->update_order_tracking($request);
        $response['error'] = $data->original['error'];
        $response['message'] = $data->original['message'];

        return response()->json($response);
    }

    public function get_sales_list(Request $request, SellerOrderController $orderController, ReportController $reportController)
    {
        /*
          start_date : 2020-09-07 or 2020/09/07 { optional }
          end_date : 2021-03-15 or 2021/03/15 { optional }
          limit:25            // { default - 25 } optional
          offset:0            // { default - 0 } optional
          sort:               // { id } optional
          order:DESC/ASC      // { default - DESC } optional
          search:value        // {optional}
        */

        $rules = [
            'sort' => 'string',
            'limit' => 'numeric',
            'offset' => 'numeric',
            'store_id' => 'required|numeric|exists:stores,id',

        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            if (auth()->check()) {
                $user_id = auth()->user()->id;
                $seller_id = Seller::where('user_id', $user_id)->value('id');
            }
            $request['seller_id'] = $seller_id;

            $data = $reportController->get_sales_list($request);

            return response()->json($data);
        }
    }

    public function update_product_status(Request $request)
    {
        /*
            product_id:10
            status:1     {1: active | 0: de-active}
        */

        $rules = [
            'product_id' => 'required|numeric|exists:products,id',
            'status' => 'required|numeric|in:0,1',
            'store_id' => 'required|numeric',

        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            $status = $request->input('status');
            $product_id = $request->input('product_id');
            $store_id = $request->input('store_id');
            if (auth()->check()) {
                $user_id = auth()->user()->id;
                $seller_id = Seller::where('user_id', $user_id)->value('id');
            }
            $seller_data = fetchdetails('seller_store', ['seller_id' => $seller_id, 'store_id' => $store_id], ['category_ids', 'permissions']);
            $permissions = isset($seller_data) && !empty($seller_data) ? json_decode($seller_data[0]->permissions, true) : [];
            // dd($permissions);
            if ($permissions['require_products_approval'] == 1) {
                $response['error'] = true;
                $response['message'] = "Seller does not have permission to update status.";
                $response['language_message_key'] = 'seller_does_not_have_permission_to_update_status';
                return response()->json($response);
            } else {
                if (updateDetails(['status' => $status], ['id' => $product_id], "products")) {
                    $response['error'] = false;
                    $response['message'] = "Status Updated Successfully";
                    $response['language_message_key'] = 'status_updated_successfully';
                } else {
                    $response['error'] = true;
                    $response['message'] = "Status not Updated.";
                    $response['language_message_key'] = 'status_not_updated';
                }
                return response()->json($response);
            }
        }
    }

    public function get_countries_data(Request $request, ProductController $productController)
    {
        /*

          limit:25            // { default - 25 } optional
          offset:0            // { default - 0 } optional
          search:value        // {optional}
        */

        $rules = [
            'limit' => 'numeric',
            'offset' => 'numeric',

        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {

            $query = DB::table('countries')->get();
            $total = $query->count();
            $contries = $productController->get_countries($request, true);

            if (!$contries->isEmpty()) {
                $response['error'] = false;
                $response['message'] = "Countries Retrived Successfully";
                $response['language_message_key'] = 'countries_retrieved_successfully';
                $response['total'] = $total;
                $response['data'] = $contries;
            } else {
                $response['error'] = true;
                $response['message'] = "Countries Not Found";
                $response['language_message_key'] = "countries_not_found";
                $response['total'] = "";
                $response['data'] = [];
            }

            return response()->json($response);
        }
    }

    function get_brand_list(Request $request, ProductController $productController)
    {
        /*
          store_id :1
          limit:25            // { default - 25 } optional
          offset:0            // { default - 0 } optional
          search:value        // {optional}
        */
        $rules = [
            'store_id' => 'required|numeric|exists:stores,id',
            'limit' => 'numeric',
            'offset' => 'numeric',

        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            $store_id = $request->store_id ?? '';
            $language_code = $request->attributes->get('language_code');
            $query = Brand::where('store_id', $store_id)->where('status', '1');
            $total = $query->count();
            $brands = $productController->get_brands($request, $request->search ?? '', true);
            foreach ($brands as $row) {
                $row->image = getMediaImageUrl($row->image);
                $row->name = getDynamicTranslation('brands', 'name', $row->id, $language_code);
            }

            if (!$brands->isEmpty()) {
                $response['error'] = false;
                $response['message'] = "Brands Retrived Successfully";
                $response['language_message_key'] = 'brands_retrieved_successfully';
                $response['total'] = $total;
                $response['data'] = $brands;
            } else {
                $response['error'] = true;
                $response['message'] = "Brands Not Found";
                $response['language_message_key'] = 'brands_not_found';
                $response['data'] = [];
            }

            return response()->json($response);
        }
    }

    public function add_product_faqs(Request $request)
    {
        /*
            product_id:25
            question:this is test question?
            answer: this is test answer.
            product_type:regular // {regular / combo}
        */
        $rules = [
            'product_id' => 'required|numeric',
            'question' => 'required|string',
            'answer' => 'required|string',
            'product_type' => 'required'

        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            if (auth()->check()) {
                $user_id = auth()->user()->id;
                $seller_id = Seller::where('user_id', $user_id)->value('id');
            }
            $request['seller_id'] = $seller_id;
            $product_id = $request->input('product_id');
            $product_type = request('product_type') != null ? Str::lower(request('product_type')) : "";
            $answer = $request->input('answer');
            $question = $request->input('question');
            $faq_data = [];
            if ($product_type == 'regular') {
                $product = Product::find($product_id);
                if (!$product) {
                    $response = [
                        'error' => true,
                        'message' => 'Product not available.',
                        'language_message_key' => 'product_not_available',
                        'data' => [],
                    ];
                    return response()->json($response);
                }
                $product_name = $product->name;
                $product_type = 'regular';
                $product_faqs = new ProductFaq([
                    'product_id' => $product_id,
                    'seller_id' => $seller_id,
                    'user_id' => $user_id,
                    'question' => $question,
                    'answer' => $answer,
                    'answered_by' => $seller_id,
                ]);

                $product_faqs->save();

                $result = ProductFaq::where('id', $product_faqs->id)
                    ->where('product_id', $product_id)
                    ->where('user_id', $user_id)
                    ->get();
            }
            if ($product_type == 'combo') {
                $combo_product = ComboProduct::find($product_id);
                if (!$combo_product) {
                    $response = [
                        'error' => true,
                        'message' => 'Product not available.',
                        'language_message_key' => 'product_not_available',
                        'data' => [],
                    ];
                    return response()->json($response);
                }
                $product_name = $combo_product->title;
                $product_type = 'combo';
                $product_faqs = new ComboProductFaq([
                    'seller_id' => $seller_id,
                    'product_id' => $product_id,
                    'user_id' => $user_id,
                    'question' => $question,
                    'answer' => $answer,
                    'answered_by' => $seller_id,
                ]);

                $product_faqs->save();

                $result = ComboProductFaq::where('id', $product_faqs->id)
                    ->where('product_id', $product_id)
                    ->where('user_id', $user_id)
                    ->get();
            }

            foreach ($result as $value) {
                $fields = [
                    'id',
                    'user_id',
                    'seller_id',
                    'product_id',
                    'votes',
                    'question',
                    'answer',
                    'answered_by',
                    'created_at',
                    'updated_at',
                ];

                foreach ($fields as $field) {
                    $faq_data[$field] = ($value->$field == null) ? "" : $value->$field;
                }
                $seller_user_id = Seller::where('id', $value->answered_by)->value('user_id');
                $answered_by_user = User::find($seller_user_id);
                $faq_data['answered_by'] = $answered_by_user ? $answered_by_user->username : '';
                $faq_data['product_name'] = $product_name;
                $faq_data['type'] = $product_type;
            }

            return response()->json([
                'error' => false,
                'message' => 'FAQs added successfully',
                'language_message_key' => 'faqs_added_successfully',
                'data' => $faq_data ? $faq_data : []
            ]);
        }
    }


    public function get_product_faqs(Request $request)
    {
        $rules = [
            'id' => 'nullable|numeric',
            'product_id' => 'nullable|numeric',
            'seller_id' => 'nullable|numeric',
            'limit' => 'nullable|numeric',
            'offset' => 'nullable|numeric',
            'type' => 'nullable|string|in:regular,combo',
            'search' => 'nullable|string',
            'sort' => 'nullable|string',
            'order' => 'nullable|string|in:ASC,DESC',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        }

        $limit = $request->input('limit', 25);
        $offset = $request->input('offset', 0);
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'DESC');
        $search = $request->input('search', '');
        $id = $request->input('id');
        $product_id = $request->input('product_id');
        $type = $request->input('type');
        $auth_seller_id = auth()->user()->id;
        $language_code = $request->attributes->get('language_code');
        $seller_id = Seller::where('user_id', $auth_seller_id)->value('id');
        $queries = [];


        $faq_type = null;


        if ($type === 'regular' || !$type) {
            $queries[] = DB::table('product_faqs')
                ->select(
                    'product_faqs.id',
                    'product_faqs.product_id',
                    'product_faqs.seller_id',
                    'product_faqs.question',
                    'product_faqs.answer',
                    'product_faqs.user_id',
                    'product_faqs.created_at',
                    'product_faqs.updated_at',
                    'seller_data.user_id as seller_user_id',
                    'seller_user.username as answered_by',
                    'p.name as product_name'
                )
                ->leftJoin('users', 'product_faqs.answered_by', '=', 'users.id')
                ->leftJoin('seller_data', 'product_faqs.seller_id', '=', 'seller_data.id')
                ->leftJoin('users as seller_user', 'seller_data.user_id', '=', 'seller_user.id')
                ->leftJoin('products as p', 'p.id', '=', 'product_faqs.product_id')
                ->where('product_faqs.seller_id', $seller_id)
                ->when($id, function ($query) use ($id) {
                    $query->where('product_faqs.id', $id);
                })
                ->when($product_id, function ($query) use ($product_id) {
                    $query->where('product_faqs.product_id', $product_id);
                })
                ->when($search, function ($query) use ($search) {
                    $query->where('product_faqs.question', 'like', '%' . $search . '%');
                });

            $faq_type = 'regular';
        }


        if ($type === 'combo') {
            $queries[] = DB::table('combo_product_faqs')
                ->select(
                    'combo_product_faqs.id',
                    'combo_product_faqs.product_id',
                    'combo_product_faqs.seller_id',
                    'combo_product_faqs.question',
                    'combo_product_faqs.answer',
                    'combo_product_faqs.user_id',
                    'combo_product_faqs.created_at',
                    'combo_product_faqs.updated_at',
                    'seller_data.user_id as seller_user_id',
                    'seller_user.username as answered_by',
                    'cp.title as product_name'
                )
                ->leftJoin('users', 'combo_product_faqs.answered_by', '=', 'users.id')
                ->leftJoin('seller_data', 'combo_product_faqs.seller_id', '=', 'seller_data.id')
                ->leftJoin('users as seller_user', 'seller_data.user_id', '=', 'seller_user.id')
                ->leftJoin('combo_products as cp', 'cp.id', '=', 'combo_product_faqs.product_id')
                ->where('combo_product_faqs.seller_id', $seller_id)
                ->when($id, function ($query) use ($id) {
                    $query->where('combo_product_faqs.id', $id);
                })
                ->when($product_id, function ($query) use ($product_id) {
                    $query->where('combo_product_faqs.product_id', $product_id);
                })
                ->when($search, function ($query) use ($search) {
                    $query->where('combo_product_faqs.question', 'like', '%' . $search . '%');
                });

            $faq_type = 'combo';
        }


        if (count($queries) > 0) {
            $query = $queries[0];


            foreach (array_slice($queries, 1) as $subQuery) {
                $query->unionAll($subQuery);
            }


            $total = DB::table(DB::raw("({$query->toSql()}) as combined"))
                ->mergeBindings($query)
                ->count();


            $faqs = $query->orderBy($sort, $order)
                ->offset($offset)
                ->limit($limit)
                ->get();

            $faqs = $faqs->map(function ($faq) use ($faq_type, $language_code) {
                $faq->type = $faq_type;
                if ($faq_type === 'regular') {
                    $faq->product_name = getDynamicTranslation('products', 'name', $faq->product_id, $language_code);
                } elseif ($faq_type === 'combo') {
                    $faq->product_name = getDynamicTranslation('combo_products', 'title', $faq->product_id, $language_code);
                }
                return $faq;
            });

            return response()->json([
                'error' => $total > 0 ? false : true,
                'message' => $total > 0 ? 'FAQs retrieved successfully' : 'No FAQs found',
                'total' => $total,
                'data' => $faqs,
            ]);
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Invalid request parameters',
                'total' => 0,
                'data' => [],
            ]);
        }
    }


    public function delete_product_faq(Request $request, ProductFaqController $productFaqContrller, ComboProductFaqController $ComboProductFaqController)
    {
        /*
            id:2    // {optional} Product FAQ Id

        */
        $rules = [
            'id' => 'required|numeric',
            'type' => 'required|string|in:regular,combo'
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            $id = $request->input('id');
            $type = $request->input('type') ?? "";
            if (isset($type) && $type = 'regular') {

                $data = $productFaqContrller->destroy($id);
            } else {
                $data = $ComboProductFaqController->destroy($id);
            }

            return response()->json($data->original);
        }
    }

    public function edit_product_faq(Request $request, ProductFaqController $productFaqController)
    {
        /*
          edit_id:1 // product FAQ id
          answer: this is test answer.
          type: regular | combo // Product type
        */

        $rules = [
            'edit_id' => 'required|numeric',
            'answer' => 'required',
            'type' => 'required',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            if (auth()->check()) {
                $user_id = auth()->user()->id;
                $seller_id = Seller::where('user_id', $user_id)->value('id');
            }
            $request['seller_id'] = $seller_id;

            // Update the product FAQ using the provided controller method
            $data = $productFaqController->update($request, $request['edit_id'], true);

            // If update fails
            if (empty($data)) {
                $response = [
                    'error' => true,
                    'message' => "Not Updated. Try again later.",
                    'language_message_key' => "update_failed_try_again_later",
                    'data' => [],
                ];
                return response()->json($response);
            }

            // Fetch the product name and type after updating
            $product_id = $data->product_id;
            $product_type = $request->input('type'); // Fetch type from request

            // Fetch the product details based on type
            if ($product_type == 'regular') {
                $product = Product::find($product_id);
                $product_name = $product ? $product->name : '';
            } else if ($product_type == 'combo') {
                $combo_product = ComboProduct::find($product_id);
                $product_name = $combo_product ? $combo_product->title : '';
            } else {
                $product_name = ''; // In case the type is invalid
            }

            // Prepare the response with product details
            $response = [
                'error' => false,
                'message' => "Product FAQ Updated Successfully.",
                'language_message_key' => "product_faq_updated_successfully",
                'data' => [
                    'id' => $data->id,
                    'question' => $data->question,
                    'answer' => $data->answer,
                    'product_name' => $product_name,
                    'type' => $product_type,
                    'answered_by' => $data->answered_by,
                    'created_at' => $data->created_at,
                    'updated_at' => $data->updated_at,
                ],
            ];

            return response()->json($response);
        }
    }


    public function manage_stock(Request $request)
    {
        /*
            product_variant_id:156
            quantity:5
            type:add/subtract
        */

        $rules = [
            'product_variant_id' => 'required|numeric|exists:product_variants,id',
            'quantity' => 'required|numeric',
            'type' => 'required',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            if ((isset($request['type']) && $request['type'] == 'add')) {
                updateStock([$request['product_variant_id']], [$request['quantity']], 'plus');
                $product_id = fetchDetails('product_variants', ['id' => $request['product_variant_id']], 'product_id');
                $product_id = isset($product_id) && !empty($product_id) ? $product_id[0]->product_id : "";
                $product_details = fetchProduct('', '', $product_id);
                $product_details = !empty($product_details['product']) ? $product_details['product'] : '';
                $response['error'] = false;
                $response['message'] = 'Stock Updated Successfully';
                $response['language_message_key'] = 'stock_updated_successfully';
                $response['data'] = $product_details;
                return response()->json($response);
            } else if (isset($request['type']) && $request['type'] == 'subtract') {
                if ($request['quantity'] > $request['current_stock']) {
                    $response['error'] = true;
                    $response['message'] = "Subtracted stock cannot be greater than current stock";
                    $response['language_message_key'] = 'subtract_stock_greater_than_current_stock';
                    $response['data'] = array();
                    return response()->json($response);
                }
                updateStock([$request['product_variant_id']], [$request['quantity']]);
                $product_id = fetchDetails('product_variants', ['id' => $request['product_variant_id']], 'product_id');
                $product_id = isset($product_id) && !empty($product_id) ? $product_id[0]->product_id : "";
                $product_details = fetchProduct('', '', $product_id);
                $product_details = !empty($product_details['product']) ? $product_details['product'] : '';
                $response['error'] = false;
                $response['message'] = 'Stock Updated Successfully';
                $response['language_message_key'] = 'stock_updated_successfully';
                $response['data'] = $product_details;
                return response()->json($response);
            } else {
                $response['error'] = true;
                $response['message'] = 'Stock Not Updated';
                $response['language_message_key'] = 'stock_not_updated';
                $response['data'] = array();
                return response()->json($response);
            }
        }
    }

    public function manage_combo_stock(Request $request)
    {

        $rules = [
            'product_id' => 'required|numeric|exists:combo_products,id',
            'quantity' => 'required|numeric|min:1',
            'type' => 'required|in:add,subtract',
            'current_stock' => 'required_if:type,subtract|numeric|min:0',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        }

        $product_id = $request->input('product_id');
        $quantity = $request->input('quantity');
        $type = $request->input('type');
        $current_stock = $request->input('current_stock');



        // Handle stock operations
        if ($type === 'add') {
            updateComboStock($product_id, $quantity, 'add');
            // Fetch product details
            $product_details = fetchComboProduct('', '', $product_id);
            $product_details = $product_details['combo_product'] ?? '';
            return response()->json([
                'error' => false,
                'message' => 'Stock Updated Successfully',
                'language_message_key' => 'stock_updated_successfully',
                'data' => $product_details,
            ]);
        }

        if ($type === 'subtract') {
            // Check if subtraction is possible
            if ($quantity > $current_stock) {
                return response()->json([
                    'error' => true,
                    'message' => 'Subtracted stock cannot be greater than current stock',
                    'language_message_key' => 'subtract_stock_greater_than_current_stock',
                    'data' => [],
                ]);
            }

            updateComboStock($product_id, $quantity, 'subtract');
            // Fetch product details
            $product_details = fetchComboProduct('', '', $product_id);
            $product_details = $product_details['combo_product'] ?? '';
            return response()->json([
                'error' => false,
                'message' => 'Stock Updated Successfully',
                'language_message_key' => 'stock_updated_successfully',
                'data' => $product_details,
            ]);
        }

        return response()->json([
            'error' => true,
            'message' => 'Stock Not Updated',
            'language_message_key' => 'stock_not_updated',
            'data' => [],
        ]);
    }


    public function add_pickup_location(Request $request, PickupLocationController $pickupLocationController)
    {
        /*
         seller_id : 8
         pickup_location : Croma Digital
         name:admin // shipper's name
         email : admin123@gmail.com
         phone : 1234567890
         address : 201,time square,mirjapar hignway // note : must add specific address like plot_no/street_no/office_no etc.
         address2 : near prince lawns
         city : bhuj
         state : gujarat
         country : india
         pincode : 370001
         latitude : 23.5643445644
         longitude : 69.312531534
         status : 0/1 {default :0}
        */

        $rules = [
            'pickup_location' => 'required',
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required|numeric|digits_between:4,15',
            'address' => 'required',
            'address2' => 'required',
            'city' => 'required',
            'state' => 'required',
            'country' => 'required',
            'pincode' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            if (auth()->check()) {
                $user_id = auth()->user()->id;
                $seller_id = Seller::where('user_id', $user_id)->value('id');
                $request['seller_id'] = $seller_id;
                $data = $pickupLocationController->store($request);
                if (isset($data['success']) && $data['success'] == true) {
                    $response['error'] = false;
                    $response['message'] = 'Pickup Location added successfully';
                    $response['language_message_key'] = 'pickup_location_added_successfully';
                    $response['data'] = $data;
                } else {
                    $response['error'] = true;
                    $response['message'] = 'Something went wrong.Pleae try later !';
                    $response['language_message_key'] = 'something_went_wrong';
                    $response['data'] = isset($data['errors']) && !empty($data['errors']) ? $data['errors'] : $data;
                }
                return response()->json($response);
            }
        }
    }

    public function get_pickup_locations(Request $request, PickupLocationController $pickupLocationController)
    {
        /*
            seller_id:1
            search : Search keyword // { optional }
            limit:25                // { default - 10 } optional
            offset:0                // { default - 0 } optional
            sort: id                // { default - id } optional
            order:DESC/ASC          // { default - DESC } optional
            status:1           optional
        */

        $rules = [
            'limit' => 'numeric',
            'offset' => 'numeric',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {

            if (auth()->check()) {
                $user_id = auth()->user()->id;
                $seller_id = Seller::where('user_id', $user_id)->value('id');
            }
            $request['seller_id'] = $seller_id;
            $data = $pickupLocationController->list($request, true);
            $response['error'] = empty($data['rows']) ? true : false;
            $response['message'] = !empty($data['rows']) ? 'Pickup Location retrived successfully' : 'No data found !';
            $response['language_message_key'] = !empty($data['rows']) ? 'pickup_location_retrieved_successfully' : 'no_data_found';
            $response['total'] = $data['total'];
            $response['data'] = !empty($data) ? $data['rows'] : [];

            return response()->json($response);
        }
    }

    public function create_shiprocket_order(Request $request, SellerOrderController $ordercController)
    {
        /*
            order_id:120
            user_id:1
            pickup_location:croma digital
            parcel_weight:1 (in kg)
            parcel_height:1 (in cms)
            parcel_breadth:1 (in cms)
            parcel_length:1 (in cms)
        */

        $rules = [
            'order_id' => 'required|numeric',
            'user_id' => 'required|numeric|exists:users,id',
            'pickup_location' => 'required',
            'parcel_weight' => 'required',
            'parcel_height' => 'required',
            'parcel_breadth' => 'required',
            'parcel_length' => 'required',
            'parcel_id' => 'required',
            'store_id' => 'required',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {

            if (auth()->check()) {
                $user_id = auth()->user()->id;
                $seller_id = Seller::where('user_id', $user_id)->value('id');
            }
            $request['shiprocket_seller_id'] = $seller_id;
            $res = getOrderDetails(['o.id' => $request['order_id']]);
            $request['order_items'] = $res;
            $data = $ordercController->create_shiprocket_order($request, true);
            $response['error'] = $data->original['error'];
            $response['message'] = $data->original['message'];
            $response['data'] = Arr::except($data->original['data'], ['error', 'message']); //use for remove error and message key from response array
            return response()->json($response);
        }
    }

    public function generate_awb(Request $request)
    {
        /*
            shipment_id:120
        */

        $rules = [
            'shipment_id' => 'required|numeric',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            $res = generate_awb($request['shipment_id']);
            if (!empty($res) && $res['awb_assign_status'] == 1) {
                $response['error'] = false;
                $response['message'] = 'AWB generated successfully';
                $response['language_message_key'] = 'awb_generated_successfully';
                $response['data'] = $res;
            } else {
                $response['error'] = true;
                $response['message'] = 'AWB not generated';
                $response['language_message_key'] = 'awb_not_generated';
                $response['data'] = array();
            }
            return response()->json($response);
        }
    }

    public function send_pickup_request(Request $request)
    {
        /*
            shipment_id:120
        */

        $rules = [
            'shipment_id' => 'required|numeric',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            $res = send_pickup_request($request['shipment_id']);

            if (!empty($res)) {
                $response['error'] = false;
                $response['message'] = 'Request send successfully';
                $response['language_message_key'] = 'request_sent_successfully';
                $response['data'] = $res;
            } else {
                $response['error'] = true;
                $response['message'] = 'Request not sent';
                $response['language_message_key'] = 'request_not_sent';
                $response['data'] = array();
            }
            return response()->json($response);
        }
    }

    public function generate_label(Request $request)
    {
        /*
            shipment_id:120
        */
        $rules = [
            'shipment_id' => 'required|numeric',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            $res = generate_label($request['shipment_id']);
            if (!empty($res)) {
                $response['error'] = false;
                $response['message'] = 'Label generated successfully';
                $response['language_message_key'] = 'label_generated_successfully';
                $response['data'] = $res;
            } else {
                $response['error'] = true;
                $response['message'] = 'Label not generated';
                $response['language_message_key'] = 'label_not_generated';
                $response['data'] = array();
            }
            return response()->json($response);
        }
    }

    public function generate_invoice(Request $request)
    {
        /*
            shiprocket_order_id:120
        */

        $rules = [
            'shiprocket_order_id' => 'required',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {

            $res = generate_invoice($request['shiprocket_order_id']);
            if (!empty($res) && isset($res['is_invoice_created']) && $res['is_invoice_created'] == 1) {
                $response['error'] = false;
                $response['message'] = 'Invoice generated successfully';
                $response['language_message_key'] = 'invoice_generated_successfully';
                $response['data'] = $res;
            } else {
                $response['error'] = true;
                $response['message'] = 'Invoice not generated';
                $response['language_message_key'] = 'invoice_not_generated';
                $response['data'] = array();
            }
            return response()->json($response);
        }
    }

    public function cancel_shiprocket_order(Request $request)
    {
        /*
            shiprocket_order_id:120
        */
        $rules = [
            'shiprocket_order_id' => 'required',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            $res = cancel_shiprocket_order($request['shiprocket_order_id']);
            if (!empty($res) && (isset($res['status']) && $res['status'] == 200 || $res['status_code'] == 200)) {
                $response['error'] = false;
                $response['message'] = 'Order cancelled successfully';
                $response['language_message_key'] = 'order_cancelled_successfully';
                $response['data'] = $res['data'];
            } else {
                $response['error'] = true;
                $response['message'] = 'Order not cancelled';
                $response['language_message_key'] = 'order_not_cancelled';
                $response['data'] = array();
            }
            return response()->json($response);
        }
    }

    public function download_label(Request $request)
    {
        /*
            shipment_id:120
        */

        $rules = [
            'shipment_id' => 'required',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            $res = fetchDetails('order_trackings', ['shipment_id' => $request['shipment_id']], 'label_url')[0]->label_url;
            if (isset($res) && !empty($res)) {
                $response['error'] = false;
                $response['message'] = 'Data retrived successfully';
                $response['language_message_key'] = 'data_retrieved_successfully';
                $response['data'] = $res;
            } else {
                $response['error'] = true;
                $response['message'] = 'Data not retrived';
                $response['language_message_key'] = 'data_not_retrieved';
                $response['data'] = array();
            }
            return response()->json($response);
        }
    }

    public function download_invoice(Request $request)
    {
        /*
            shipment_id:120
        */
        $rules = [
            'shipment_id' => 'required',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            $res = fetchDetails('order_trackings', ['shipment_id' => $request['shipment_id']], 'invoice_url')[0]->invoice_url;
            if (isset($res) && !empty($res)) {
                $response['error'] = false;
                $response['message'] = 'Data retrived successfully';
                $response['language_message_key'] = 'data_retrieved_successfully';
                $response['data'] = $res;
            } else {
                $response['error'] = true;
                $response['language_message_key'] = 'data_not_retrieved';
                $response['data'] = array();
            }
            return response()->json($response);
        }
    }

    public function shiprocket_order_tracking(Request $request)
    {
        /*
            awb_code:120
        */

        $rules = [
            'awb_code' => 'required',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            $res = "https://shiprocket.co/tracking/" . $request['awb_code'];
            if (isset($res) && !empty($res)) {
                $response['error'] = false;
                $response['message'] = 'Data retrived successfully';
                $response['language_message_key'] = 'data_retrieved_successfully';
                $response['data'] = $res;
            } else {
                $response['error'] = true;
                $response['message'] = 'Data not retrived';
                $response['language_message_key'] = 'data_not_retrieved';
                $response['data'] = array();
            }
            return response()->json($response);
        }
    }
    public function get_shiprocket_order(Request $request)
    {
        /*
            shiprocket_order_id:120
        */

        $rules = [
            'shiprocket_order_id' => 'required',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            $shiprocket_order = get_shiprocket_order($request['shiprocket_order_id']);
            if (isset($shiprocket_order) && !empty($shiprocket_order)) {
                $response['error'] = false;
                $response['message'] = 'Data retrived successfully';
                $response['language_message_key'] = 'data_retrieved_successfully';
                $response['data']['status'] = $shiprocket_order['data']['status'];
            } else {
                $response['error'] = true;
                $response['message'] = 'Data not retrived';
                $response['language_message_key'] = 'data_not_retrieved';
                $response['data'] = array();
            }
            return response()->json($response);
        }
    }

    public function delete_order(Request $request)
    {
        /*
            order_id:120
        */
        $rules = [
            'order_id' => 'required|numeric|exists:orders,id',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            $order_id = $request['order_id'];
            deleteDetails(['id' => $order_id], 'orders');
            deleteDetails(['order_id' => $order_id], 'order_items');

            $response['error'] = false;
            $response['message'] = 'Order deleted successfully';
            $response['language_message_key'] = 'order_deleted_successfully';
            $response['data'] = array();
            return response()->json($response);
        }
    }

    public function get_settings(AddressController $addressController, Request $request)
    {
        /*
            type : payment_method // { default : all  } optional
            user_id:  15 { optional }
            limit:25            // { default - 25 } optional
            offset:0            // { default - 0 } optional
        */
        $rules = [
            'type' => 'sometimes|in:payment_method,store_setting',
            'user_id' => 'sometimes|numeric|exists:users,id',
            'store_id' => 'sometimes|numeric|exists:stores,id',
            'limit' => 'sometimes|numeric',
            'offset' => 'sometimes|numeric',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {

            $type = $request->input('type', 'all');
            $limit = $request->input('limit', 25);
            $offset = $request->input('offset', 0);
            $tags = $general_settings = array();
            $user_id = $request->input('user_id', '');
            $store_id = $request->input('store_id', '');
            if ($type == 'store_setting') {
                $rules = [
                    'store_id' => 'sometimes|numeric|required',
                ];
                if ($validationResponse = validateRequest($request, $rules)) {
                    return $validationResponse;
                }
            }

            if ($type == 'all' || $type == 'payment_method' || $type == 'store_setting') {


                $filter['tags'] = $request->input('tags', '');

                $products = fetchProduct(null, $filter, null, null, $limit, $offset, 'p.id', 'DESC', null);

                for ($i = 0; $i < count($products); $i++) {
                    if (!empty($products['product'][$i]->tags)) {
                        $tags = array_merge($tags, $products['product'][$i]->tags);
                    }
                }
                $settings = [
                    'logo' => 0,
                    'seller_privacy_policy' => 1,
                    'seller_terms_and_conditions' => 1,
                    'fcm_server_key' => 1,
                    'contact_us' => 1,
                    'payment_method' => 1,
                    'about_us' => 1,
                    'currency' => 0,
                    'time_slot_config' => 1,
                    'user_data' => 0,
                    'system_settings' => 1,
                    'shipping_policy' => 1,
                    'return_policy' => 1,
                    'pusher_settings' => 1,
                ];
                if ($type == 'payment_method') {
                    $settings_res['payment_method'] = getSettings($type, $settings[$type]);
                    $settings_res['payment_method'] = json_decode($settings_res['payment_method'], true);

                    if (isset($user_id) && !empty($user_id)) {
                        $cart_total_response = getCartTotal($user_id, false, 0, '', $store_id);

                        $cod_allowed = isset($cart_total_response[0]->is_cod_allowed) ? $cart_total_response[0]->is_cod_allowed : 1;
                        $settings_res['is_cod_allowed'] = $cod_allowed;
                    } else {
                        $settings_res['is_cod_allowed'] = 1;
                    }

                    $general_settings = $settings_res;
                } elseif ($type == 'store_setting') {
                } else {

                    foreach ($settings as $type => $isjson) {
                        if ($type == 'payment_method') {
                            continue;
                        }

                        $general_settings[$type] = [];

                        $settings_res = getSettings($type, $isjson);
                        $settings_res = json_decode($settings_res, true);

                        if ($type == 'logo') {
                            $logo_setting = getSettings('system_settings', true);
                            $logo_setting = json_decode($logo_setting, true);
                            $settings_res = getMediaImageUrl($logo_setting['logo']);
                        }
                        if ($type == 'user_data' && isset($user_id) && !empty($user_id)) {
                            $cart_total_response = getCartTotal($user_id, false, 0, '', $store_id);
                            $res = $addressController->getAddress($user_id, null, false, true);

                            if (!empty($res)) {
                                $zipcode_id = fetchDetails('zipcodes', ['zipcode' => $res[0]->pincode], 'id')[0]->id;
                                if (!empty($zipcode_id)) {
                                    $zipcode = fetchDetails('zipcodes', ['id' => $zipcode_id], 'zipcode')[0]->zipcode;
                                }
                            }
                            $settings_res = fetchUsers($user_id);
                            $settings_res = [
                                'cities' => $settings_res->cities,
                                'street' => $settings_res->street,
                                'area' => $settings_res->area,
                                'cart_total_items' => 0, // Initialize to 0, you can update it later
                                'pincode' => isset($zipcode) ? $zipcode : '',
                            ];
                        } elseif ($type == 'user_data' && !isset($user_id)) {
                            $settings_res = '';
                        }
                        // //Strip tags in case of terms_conditions and privacy_policy

                        if ($isjson && isset($settings_res[$type])) {
                            array_push($general_settings[$type], $settings_res[$type]);
                        } else {
                            array_push($general_settings[$type], $settings_res);
                        }
                    }

                    $general_settings['system_settings'][0]['store_currency'] = isset($general_settings['system_settings'][0]['store_currency']) && $general_settings['system_settings'][0]['store_currency'] !== null ? $general_settings['system_settings'][0]['store_currency'] : '';
                    $general_settings['system_settings'][0]['sidebar_color'] = isset($general_settings['system_settings'][0]['sidebar_color']) && $general_settings['system_settings'][0]['sidebar_color'] !== null ? $general_settings['system_settings'][0]['sidebar_color'] : '';
                    $general_settings['system_settings'][0]['sidebar_type'] = isset($general_settings['system_settings'][0]['sidebar_type']) && $general_settings['system_settings'][0]['sidebar_type'] !== null ? $general_settings['system_settings'][0]['sidebar_type'] : '';
                    $general_settings['user_data'] = (isset($general_settings['user_data'][0]) && !empty($general_settings['user_data'][0])) ? $general_settings['user_data'][0] : [];

                    $currencyDetails = fetchDetails('currencies', ['is_default' => 1], 'symbol');
                    $currency = !empty($currencyDetails) ? $currencyDetails[0]->symbol : '';
                    $general_settings['currency'] = $currency;

                    if (isset($general_settings['system_settings'][0]['on_boarding_image']) && !empty($general_settings['system_settings'][0]['on_boarding_image'])) {
                        $onboarding_images = $general_settings['system_settings'][0]['on_boarding_image'];
                        if (isset($onboarding_images) && !empty($onboarding_images)) {
                            foreach ($onboarding_images as &$image) {
                                $image = getImageUrl($image, "", "", 'image', 'MEDIA_PATH');
                            }
                        }
                    } else {
                        $onboarding_images = [];
                    }
                    $general_settings['system_settings'][0]['on_boarding_image'] = $onboarding_images;

                    $onboarding_videos = [];
                    if (isset($general_settings['system_settings'][0]['on_boarding_video']) && !empty($general_settings['system_settings'][0]['on_boarding_video'])) {
                        $onboarding_videos = $general_settings['system_settings'][0]['on_boarding_video'];

                        if (isset($onboarding_videos) && !empty($onboarding_videos)) {
                            foreach ($onboarding_videos as &$video) {
                                $video = getImageUrl($video, "", "", 'image', 'MEDIA_PATH');
                            }
                        }
                    }

                    $general_settings['system_settings'][0]['on_boarding_video'] = $onboarding_videos;
                }
                $response = [
                    'error' => false,
                    'message' => 'Settings retrieved successfully',
                    'language_message_key' => 'settings_retrieved_successfully',
                    'data' => $general_settings,
                ];
            } else {
                $response = [
                    'error' => true,
                    'message' => 'Settings Not Found',
                    'language_message_key' => 'settings_not_found',
                    'data' => [],
                ];
            }
            return response()->json($response);
        }
    }

    public function delete_seller(Request $request)
    {
        /*
            mobile:9874563214
            password:12345695
        */
        $rules = [
            'mobile' => 'required|numeric',
            'password' => 'required',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            if (auth()->check()) {
                $user_id = auth()->user()->id;
                $seller_id = Seller::where('user_id', $user_id)->value('id');
            }
            $user_data = fetchDetails('users', ['id' => $user_id, 'mobile' => $request['mobile']], ['id', 'username', 'password', 'active', 'mobile']);
            if ($user_data) {

                if (auth()->check()) {
                    $user = Auth::user();

                    if ($user['role_id'] == '4') {
                        $delete = array(
                            "media" => 0,
                            "payment_requests" => 0,
                            "products" => 0,
                            "product_attributes" => 0,
                            "product_variants" => 0,
                            "order_items" => 0,
                            "orders" => 0,
                            "order_bank_transfer" => 0,
                            "seller_commission" => 0,
                            "seller_data" => 0,
                        );
                        $seller_media = fetchDetails('seller_data', ['user_id' => $user_id], ['id', 'national_identity_card', 'authorized_signature']);
                        if (!empty($seller_media)) {

                            $authorizedSignaturePath = str_replace('\\', '/', public_path(config('constants.' . 'MEDIA_PATH') . $seller_media[0]->authorized_signature));

                            $nationalIdentityCardPath = str_replace('\\', '/', public_path(config('constants.' . 'MEDIA_PATH') . $seller_media[0]->national_identity_card));

                            if (File::exists($authorizedSignaturePath)) {
                                unlink($authorizedSignaturePath);
                            }
                            if (File::exists($nationalIdentityCardPath)) {
                                unlink($nationalIdentityCardPath);
                            }
                        }
                        if (updateDetails(['seller_id' => 0], ['seller_id' => $seller_id], 'media')) {
                            $delete['media'] = 1;
                        }

                        /* check for retur requesst if seller's product have */
                        $return_req = ReturnRequest::join('products as p', 'p.id', '=', 'return_requests.product_id')
                            ->where('p.seller_id', '=', $seller_id)
                            ->get();

                        if (!$return_req->isEmpty()) {
                            $response['error'] = true;
                            $response['message'] = 'Seller could not be deleted.Either found some order items which has return request.Finalize those before deleting it';
                            $response['language_message_key'] = 'seller_not_deleted_return_requests_are_pending_finalize_those_before_deleting_it';
                            return response()->json($response);
                            exit();
                        }

                        $pr_ids = fetchDetails("products", ['seller_id' => $seller_id], "id");
                        if (deleteDetails(['seller_id' => $seller_id], 'products')) {
                            $delete['products'] = 1;
                        }
                        foreach ($pr_ids as $row) {
                            if (deleteDetails(['product_id' => $row->id], 'product_attributes')) {
                                $delete['product_attributes'] = 1;
                            }
                            if (deleteDetails(['product_id' => $row->id], 'product_variants')) {
                                $delete['product_variants'] = 1;
                            }
                        }

                        /* check order items */
                        $order_items = fetchDetails('order_items', ['seller_id' => $seller_id], ['id', 'order_id']);

                        if (deleteDetails(['seller_id' => $seller_id], 'order_items')) {
                            $delete['order_items'] = 1;
                        }
                        if (!empty($order_items)) {
                            $res_order_id = collect($order_items)->pluck('order_id')->unique()->values()->all();
                            for ($i = 0; $i < count($res_order_id); $i++) {

                                $orders = OrderItems::where('order_items.seller_id', '!=', $seller_id)
                                    ->where('order_items.order_id', $res_order_id[$i])
                                    ->rightJoin('orders as o', 'o.id', '=', 'order_items.order_id')
                                    ->get();

                                if ($orders->isEmpty()) {

                                    // delete orders
                                    if (deleteDetails(['seller_id' => $seller_id], 'order_items')) {
                                        $delete['order_items'] = 1;
                                    }
                                    if (deleteDetails(['id' => $res_order_id[$i]], 'orders')) {
                                        $delete['orders'] = 1;
                                    }
                                    if (deleteDetails(['order_id' => $res_order_id[$i]], 'order_bank_transfer')) {
                                        $delete['order_bank_transfer'] = 1;
                                    }
                                }
                            }
                        } else {
                            $delete['order_items'] = 1;
                            $delete['orders'] = 1;
                            $delete['order_bank_transfer'] = 1;
                        }
                        if (!empty($res_order_id)) {
                            if (deleteDetails(['id' => $res_order_id[$i]], 'orders')) {
                                $delete['orders'] = 1;
                            }
                        } else {
                            $delete['orders'] = 1;
                        }
                        if (deleteDetails(['seller_id' => $seller_id], 'seller_commission')) {
                            $delete['seller_commission'] = 1;
                        }
                        if (deleteDetails(['user_id' => $user_id], 'seller_data')) {
                            $delete['seller_data'] = 1;
                        }
                        if (isset($delete['seller_data']) && !empty($delete['seller_data']) && isset($delete['seller_commission']) && !empty($delete['seller_commission'])) {
                            $deleted = TRUE;
                        }
                        deleteDetails(['id' => $user_id], 'users');
                        $response['error'] = false;
                        $response['message'] = 'Seller Deleted Successfully';
                        $response['language_message_key'] = 'seller_deleted_successfully';
                    } else {
                        $response['error'] = true;
                        $response['message'] = 'Details Does\'s Match';
                        $response['language_message_key'] = 'details_does_not_match';
                    }
                } else {
                    $response['error'] = true;
                    $response['message'] = 'Details Does\'s Match';
                    $response['language_message_key'] = 'details_does_not_match';
                }
            } else {
                $response['error'] = true;
                $response['message'] = 'User Not Found';
                $response['language_message_key'] = 'user_not_found';
            }
            return response()->json($response);
        }
    }

    public function get_stores(Request $request, StoreController $AdminStoreController)
    {
        $search = $request->input('search', null);
        $limit = $request->input('limit', 25);
        $offset = $request->input('offset', 0);
        $order = $request->input('order', 'DESC');
        $sort = $request->input('sort', 'id');

        $data = $AdminStoreController->getStores($limit, $offset, $sort, $order, $search);

        return response()->json($data);
    }


    public function get_seller_stores(Request $request)
    {
        $user = Auth::user();
        $user_id = auth()->user()->id;
        $seller_id = Seller::where('user_id', $user_id)->value('id');
        $store_details = DB::table('seller_store')
            ->join('stores', 'seller_store.store_id', '=', 'stores.id')
            ->select('stores.*', 'seller_store.status as store_status')
            ->where('seller_store.user_id', $user->id)
            ->where('stores.status', 1)
            ->where('seller_store.status', 1)
            ->get();
        $rows = [];
        $language_code = $request->attributes->get('language_code');
        if (!empty($store_details)) {
            foreach ($store_details as $store) {

                $temp = [
                    'id' => $store->id,
                    'name' => getDynamicTranslation('stores', 'name', $store->id, $language_code),
                    'description' => getDynamicTranslation('stores', 'description', $store->id, $language_code),
                    'image' => getMediaImageUrl($store->image, 'STORE_IMG_PATH'),
                    'banner_image' => getMediaImageUrl($store->banner_image, 'STORE_IMG_PATH'),
                    'banner_image_for_most_selling_product' => getMediaImageUrl($store->banner_image_for_most_selling_product, 'STORE_IMG_PATH'),
                    'stack_image' => getMediaImageUrl($store->stack_image, 'STORE_IMG_PATH'),
                    'login_image' => getMediaImageUrl($store->login_image, 'STORE_IMG_PATH'),
                    'is_single_seller_order_system' => $store->is_single_seller_order_system,
                    'is_default_store' => $store->is_default_store,
                    'disk' => $store->disk ?? '',
                    'note_for_necessary_documents' => $store->note_for_necessary_documents ?? '',
                    'primary_color' => $store->primary_color ?? '',
                    'secondary_color' => $store->secondary_color ?? '',
                    'hover_color' => $store->hover_color ?? '',
                    'active_color' => $store->active_color ?? '',
                    'background_color' => $store->background_color ?? '',
                    'delivery_charge_type' => $store->delivery_charge_type ?? '',
                    'delivery_charge_amount' => $store->delivery_charge_amount ?? '0',
                    'minimum_free_delivery_amount' => $store->minimum_free_delivery_amount ?? '0',
                    'product_deliverability_type' => $store->product_deliverability_type ?? '',
                    'rating' => $store->rating ?? '0',
                    'no_of_ratings' => $store->no_of_ratings ?? '0',
                    'status' => $store->status,
                    'store_status' => $store->store_status,
                    'store_settings' => json_decode($store->store_settings) ?? '',
                    'permissions' => getSellerPermission($seller_id, $store->id),
                ];
                $rows[] = $temp;
            }
        }

        $response['error'] = empty($store_details);
        $response['message'] = empty($store_details) ? 'No store found for this seller' : 'Store detail retrived successfully !';
        $response['language_message_key'] = empty($store_details) ? 'no_store_found_for_seller' : 'store_detail_retrieved_successfully';
        $response['data'] = $rows;
        return response()->json($response);
    }

    public function get_combo_products(Request $request)
    {

        $rules = [
            'store_id' => 'required|exists:stores,id',
            'id' => 'sometimes|numeric|exists:combo_products,id',
            'search' => 'sometimes|string',
            'attribute_value_ids' => 'sometimes',
            'sort' => 'sometimes|string',
            'limit' => 'sometimes|numeric',
            'offset' => 'sometimes|numeric',
            'order' => 'sometimes|string|alpha',
            'top_rated_product' => 'sometimes|numeric',
            'discount' => 'sometimes|numeric',
            'is_similar_products' => 'numeric',
            'top_rated_product' => 'numeric',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            $user_id = auth()->user()->id;
            // dd($user_id);
            $seller_id = Seller::where('user_id', $user_id)->value('id');
            $limit = $request->input('limit', 25);
            $offset = $request->input('offset', 0);
            $order = $request->filled('order') ? $request->input('order') : 'DESC';
            $sort = $request->filled('sort') ? $request->input('sort') : 'p.id';
            $id = $request->filled('id') ? $request->input('id') : '';
            $category_id = $request->filled('category_id') ? $request->input('category_id') : '';
            $type = $request->has('type') ? $request->input('type') : '';
            $brand_id = $request->filled('brand_id') ? $request->input('brand_id') : '';
            $filters['minimum_price'] = $request->filled('minimum_price') ? $request->input('minimum_price') : '';
            $filters['maximum_price'] = $request->filled('maximum_price') ? $request->input('maximum_price') : '';
            $filters['discount'] = $request->filled('discount') ? $request->input('discount', 0) : 0;
            $filters['most_popular_products'] = $request->filled('most_popular_products') ? $request->input('most_popular_products') : '';
            $filters = [
                'search' => $request->input('search', null),
                'tags' => $request->input('tags', ''),
                'flag' => $request->has('flag') && $request->input('flag') !== '' ? $request->input('flag') : '',
                'attribute_value_ids' => $request->input('attribute_value_ids', null),
                'is_similar_products' => $request->input('is_similar_products', null),
                'product_type' => $request->input('top_rated_product') == 1 ? 'top_rated_product_including_all_products' : $request->input('product_type'),
                'show_only_active_products' => $request->input('show_only_active_products', true),
                'show_only_stock_product' => $request->input('show_only_stock_product', false),
                'minimum_price' => $request->input('minimum_price', ''),
                'maximum_price' => $request->input('maximum_price', ''),
                'discount' => $request->input('discount', 0),
                'most_popular_products' => $request->input('most_popular_products', ''),
            ];
            $language_code = $request->attributes->get('language_code');
            $store_id = $request->input('store_id') ? (int) $request->input('store_id') : '';
            //    dd($order);
            $products = fetchComboProduct('', $filters, $id, $limit, $offset, $sort, $order, '', '', $seller_id, $store_id, $category_id, $brand_id, $type, 1, $language_code);

            $filtered_brand_ids = array_filter($products['brand_ids'], function ($value) {
                return !empty($value);
            });
            $brand_ids = implode(',', $filtered_brand_ids);
            $response = [
                'error' => !empty($products['combo_product']) ? false : true,
                'message' => !empty($products['combo_product']) ? 'Products retrived successfully!' : 'No data found',
                'language_message_key' => !empty($products['combo_product']) ? 'products_retrieved_successfully' : 'no_data_found',
                'total' => (isset($products['total'])) ? strval($products['total']) : 0,
                'category_ids' => isset($products['category_ids']) && !empty($products['category_ids']) ? implode(',', $products['category_ids']) : '',
                'brand_ids' => isset($products['brand_ids']) && !empty($products['brand_ids']) ? $brand_ids : '',
                'data' => $products['combo_product'],
            ];
            return response()->json($response);
        }
    }
    public function add_combo_product(Request $request, ComboProductController $ComboProductController)
    {

        $validator = Validator::make($request->all(), [

            'title' => 'required',
            'short_description' => 'required',
            'description' => 'required',
            'image' => 'required',
            'product_type_in_combo' => 'required',
            'simple_price' => 'required',
            'simple_special_price' => 'required',
            'store_id' => 'required|exists:stores,id',
        ]);
        if ($request->simple_stock_management_status == 'on') {
            $validator = Validator::make($request->all(), [
                'product_sku' => 'required',
                'product_total_stock' => 'required',
            ]);
        }
        if ($validator->fails()) {
            $response = [
                'error' => true,
                'message' => $validator->errors()->all(),
                'code' => 102,
            ];
            return response()->json($response);
        } else {
            $user_id = auth()->user()->id;
            $request['user_id'] = $user_id;
            $language_code = $request->attributes->get('language_code');
            $store_id = $request->input('store_id') ? (int) $request->input('store_id') : '';
            $request['store_id'] = $store_id;
            $request['selected_product'] = $request->input('selected_product');
            $request['physical_product_variant_id'] = explode(",", $request['physical_product_variant_id']);
            $request['similar_product_id'] = isset($request['similar_product_ids']) ? explode(",", $request['similar_product_ids']) : "";
            $request['other_images'] = (isset($request['other_images']) && !empty($request['other_images'])) ? explode(",", $request['other_images']) : NULL;

            $product_data = $ComboProductController->store($request, true, $language_code);

            if (!empty($product_data)) {
                return response()->json([
                    'error' => false,
                    'message' => 'Product Added Successfully',
                    'language_message_key' => 'product_added_successfully',
                    'data' => isset($product_data) ? $product_data->original['data'] : "",
                ]);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Something went wrong',
                    'language_message_key' => 'something_went_wrong',

                ]);
            }
        }
    }
    public function delete_combo_product(Request $request, ComboProductController $ComboProductController)
    {
        $rules = [
            'product_id' => 'required|exists:combo_products,id',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            $product_data = deleteDetails(['id' => $request->input('product_id')], 'combo_products');

            if (!empty($product_data)) {
                return response()->json([
                    'error' => false,
                    'message' => 'Product Deleted Successfully',
                    'language_message_key' => 'product_deleted_successfully',
                ]);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Something went wrong',
                    'language_message_key' => 'something_went_wrong',
                    'data' => $product_data,
                ]);
            }
        }
    }
    public function update_combo_product(Request $request, ComboProductController $ComboProductController)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:combo_products,id',
            'title' => 'required',
            'short_description' => 'required',
            'description' => 'required',
            'image' => 'required',
            'product_type_in_combo' => 'required',
            'product_id' => 'required|exists:combo_products,id',
            'simple_price' => 'required',
            'simple_special_price' => 'required',
            'store_id' => 'required|exists:stores,id',
        ]);
        if ($request->simple_stock_management_status == 'on') {
            $validator = Validator::make($request->all(), [
                'product_sku' => 'required',
                'product_total_stock' => 'required',
            ]);
        }
        if ($validator->fails()) {
            $response = [
                'error' => true,
                'message' => $validator->errors()->all(),
                'code' => 102,
            ];
            return response()->json($response);
        } else {
            $id = $request->input('id') ? (int) $request->input('id') : '';
            $store_id = $request->input('store_id') ? (int) $request->input('store_id') : '';
            $language_code = $request->attributes->get('language_code');
            // dd($language_code);
            $request['store_id'] = $store_id;
            $request['store_id'] = $store_id;
            $request['selected_product'] = $request->input('selected_product');
            $request['physical_product_variant_id'] = explode(",", $request['physical_product_variant_id']);
            $request['similar_product_id'] = isset($request['similar_product_ids']) ? explode(",", $request['similar_product_ids']) : "";
            $request['other_images'] = (isset($request['other_images']) && !empty($request['other_images'])) ? explode(",", $request['other_images']) : NULL;
            $product_data = $ComboProductController->update($request, $id, true, $language_code);
            if (!empty($product_data)) {
                return response()->json([
                    'error' => false,
                    'message' => 'Product Updated Successfully',
                    'language_message_key' => 'product_updated_successfully',
                    'data' => $product_data->original['data'],
                ]);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Something went wrong',
                    'language_message_key' => 'something_went_wrong',
                    'data' => $product_data,
                ]);
            }
        }
    }
    public function get_languages(Request $request)
    {
        // Fetch languages from the database
        $languages = Language::select('id', 'language', 'code', 'native_language', 'is_rtl')->get(); // You can adjust this as per your requirement, e.g., Language::select('id', 'name')->get();

        // Return the fetched languages
        return response()->json([
            'error' => false,
            'message' => 'Languages retrieved successfully',
            'language_message_key' => 'languages_retrived_successfully',
            'data' => $languages
        ], 200);
    }
    public function get_language_labels(Request $request)
    {
        $rules = [
            'language_code' => 'required',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        }

        $language_code = $request->input('language_code');
        $labels_file_path = resource_path('lang/' . $language_code . '/admin_labels.php');

        if (!file_exists($labels_file_path)) {
            return response()->json([
                'error' => true,
                'message' => 'Language file not found',
                'language_message_key' => 'language_file_not_found',
                'data' => [],
            ]);
        }

        $labels = include $labels_file_path;
        unset($labels['langcode']);
        return response()->json([
            'error' => false,
            'message' => 'Language labels retrieved successfully',
            'language_message_key' => 'language_labels_retrived_successfully',
            'data' => $labels,
        ]);
    }
    public function reset_password(Request $request)
    {
        /* Parameters to be passed
            mobile_no:7894561235
            new: pass@123
        */
        $validator = Validator::make($request->all(), [
            'mobile_no' => 'required|numeric|digits_between:1,16',
        ], [
            'mobile_no.required' => 'Mobile Number is required.',
            'mobile_no.numeric' => 'Mobile Number must be numeric.',
            'mobile_no.digits_between' => 'Mobile Number must be between 1 and 16 digits.',
        ]);

        if ($validator->fails()) {
            $response = [
                'error' => true,

                'message' => $validator->errors()->all(),
                'code' => 102,
            ];
            return response()->json($response);
        } else {
            $mobile_no = $request->input('mobile_no');
            $identityColumn = config('auth.defaults.passwords') === 'users.email' ? 'email' : 'mobile';

            $user = User::where($identityColumn, $mobile_no)->first();

            if (!$user) {
                $response = [
                    'error' => true,
                    'message' => 'Seller does not exist!',
                    'language_message_key' => 'user_does_not_exist',
                    'data' => [],
                ];
                return response()->json($response);
            }

            $status = Password::broker()->sendResetLink(
                ['email' => $user->email]
            );

            if ($status === Password::RESET_LINK_SENT) {

                $response = [
                    'error' => false,
                    'message' => 'Password reset link sent successfully!',
                    'language_message_key' => 'password_reset_link_sent_successfully!',
                    'data' => [],
                ];
            } else {
                $response = [
                    'error' => true,
                    'message' => 'Unable to send password reset link.',
                    'language_message_key' => 'unable_to_send_password_reset_link',
                    'data' => [],
                ];
            }
            return response()->json($response);
        }
    }
    public function add_seller_store(Request $request, SellerController $SellerController)
    {

        $rules = [
            'store_id' => 'required|exists:stores,id',
            'mobile' => 'required',
            'store_name' => 'required',
            'account_number' => 'required',
            'account_name' => 'required',
            'bank_name' => 'required',
            'bank_code' => 'required',
            'city' => 'required',
            'zipcode' => 'required',
            'deliverable_type' => 'required',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        }

        $language_code = $request->attributes->get('language_code');
        $user = User::where('mobile', $request->mobile)->where('role_id', 4)->first();
        $store_id = $request->input('store_id') ?? "";
        $seller_store_details = DB::table('seller_store')->select('store_id')->where('user_id', $user->id)->get();
        $seller_store_details = isset($seller_store_details) && !empty($seller_store_details) ? $seller_store_details[0]->store_id : "";
        $seller = Seller::where('user_id', $user->id)->first();
        if ($seller_store_details == $store_id) {
            return response()->json([
                'error' => true,
                'message' => labels('admin_labels.seller_already_registered', 'Seller already registered in this store.'),
                'language_message_key' => 'seller_already_registered'
            ]);
        } else {
            $seller_store_data = [];
            $media_storage_settings = fetchDetails('storage_types', ['is_default' => 1], '*');
            $mediaStorageType = isset($media_storage_settings) && !empty($media_storage_settings) ? $media_storage_settings[0]->id : 1;
            $disk = isset($media_storage_settings) && !empty($media_storage_settings) ? $media_storage_settings[0]->name : 'public';

            $media = StorageType::find($mediaStorageType);

            try {
                if ($request->hasFile('other_documents')) {
                    foreach ($request->file('other_documents') as $file) {
                        $other_documents = $media->addMedia($file)
                            ->sanitizingFileName(function ($fileName) use ($media) {
                                $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                                $uniqueId = time() . '_' . mt_rand(1000, 9999);
                                $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                                $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                                return "{$baseName}-{$uniqueId}.{$extension}";
                            })
                            ->toMediaCollection('sellers', $disk);
                        $other_document_file_names[] = $other_documents->file_name;
                        $mediaIds[] = $other_documents->id;
                    }
                }
                if ($request->hasFile('address_proof')) {

                    $addressProofFile = $request->file('address_proof');

                    $address_proof = $media->addMedia($addressProofFile)
                        ->sanitizingFileName(function ($fileName) use ($media) {
                            // Replace special characters and spaces with hyphens
                            $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                            // Generate a unique identifier based on timestamp and random component
                            $uniqueId = time() . '_' . mt_rand(1000, 9999);
                            $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                            $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                            return "{$baseName}-{$uniqueId}.{$extension}";
                        })
                        ->toMediaCollection('sellers', $disk);

                    $mediaIds[] = $address_proof->id;
                }
                if ($request->hasFile('store_logo')) {

                    $storeLogoFile = $request->file('store_logo');

                    $store_logo = $media->addMedia($storeLogoFile)
                        ->sanitizingFileName(function ($fileName) use ($media) {
                            // Replace special characters and spaces with hyphens
                            $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                            // Generate a unique identifier based on timestamp and random component
                            $uniqueId = time() . '_' . mt_rand(1000, 9999);
                            $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                            $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                            return "{$baseName}-{$uniqueId}.{$extension}";
                        })
                        ->toMediaCollection('sellers', $disk);

                    $mediaIds[] = $store_logo->id;
                }

                if ($request->hasFile('store_thumbnail')) {

                    $storeThumbnailFile = $request->file('store_thumbnail');

                    $store_thumbnail = $media->addMedia($storeThumbnailFile)
                        ->sanitizingFileName(function ($fileName) use ($media) {
                            // Replace special characters and spaces with hyphens
                            $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                            // Generate a unique identifier based on timestamp and random component
                            $uniqueId = time() . '_' . mt_rand(1000, 9999);
                            $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                            $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                            return "{$baseName}-{$uniqueId}.{$extension}";
                        })
                        ->toMediaCollection('sellers', $disk);

                    $mediaIds[] = $store_thumbnail->id;
                }


                if ($request->hasFile('authorized_signature')) {

                    $authorizedSignatureFile = $request->file('authorized_signature');

                    $authorized_signature = $media->addMedia($authorizedSignatureFile)
                        ->sanitizingFileName(function ($fileName) use ($media) {
                            // Replace special characters and spaces with hyphens
                            $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                            // Generate a unique identifier based on timestamp and random component
                            $uniqueId = time() . '_' . mt_rand(1000, 9999);
                            $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                            $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                            return "{$baseName}-{$uniqueId}.{$extension}";
                        })
                        ->toMediaCollection('sellers', $disk);

                    $mediaIds[] = $authorized_signature->id;
                }

                if ($request->hasFile('national_identity_card')) {

                    $nationalIdentityCardFile = $request->file('national_identity_card');

                    $national_identity_card = $media->addMedia($nationalIdentityCardFile)
                        ->sanitizingFileName(function ($fileName) use ($media) {
                            // Replace special characters and spaces with hyphens
                            $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                            // Generate a unique identifier based on timestamp and random component
                            $uniqueId = time() . '_' . mt_rand(1000, 9999);
                            $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                            $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                            return "{$baseName}-{$uniqueId}.{$extension}";
                        })
                        ->toMediaCollection('sellers', $disk);

                    $mediaIds[] = $national_identity_card->id;
                }

                //code for storing s3 object url for media

                if ($disk == 's3') {
                    $media_list = $media->getMedia('sellers');
                    for ($i = 0; $i < count($mediaIds); $i++) {
                        $media_url = $media_list[($media_list->count()) - (count($mediaIds) - $i)]->getUrl();

                        switch ($i) {
                            case 0:
                                $address_proof_url = $media_url;
                                break;
                            case 1:
                                $logo_url = $media_url;
                                break;
                            case 2:
                                $store_thumbnail_url = $media_url;
                                break;
                        }
                        Media::destroy($mediaIds[$i]);
                    }
                }
            } catch (Exception $e) {
                return response()->json([
                    'error' => true,
                    'message' => $e->getMessage(),
                ]);
            }

            $seller_store_data['address_proof'] = $disk == 's3' ? (isset($address_proof_url) ? $address_proof_url : '') : (isset($address_proof->file_name) ? '/' . $address_proof->file_name : '');

            $seller_store_data['logo'] = $disk == 's3' ? (isset($logo_url) ? $logo_url : '') : (isset($store_logo->file_name) ? '/' . $store_logo->file_name : '');

            $seller_store_data['store_thumbnail'] = $disk == 's3' ? (isset($store_thumbnail_url) ? $store_thumbnail_url : '') : (isset($store_thumbnail->file_name) ? '/' . $store_thumbnail->file_name : '');

            $seller_store_data['other_documents'] = $disk == 's3' ? (isset($other_documents_url) ? ($other_documents_url) : '') : (isset($other_documents->file_name) ? json_encode($other_document_file_names) : '');
            $zones = implode(',', (array) $request->deliverable_zones);
            $requested_categories = $request->requested_categories;
            $seller_store_data = array_merge($seller_store_data, [
                'user_id' => $user->id,
                'seller_id' => $seller->id,
                'store_name' => $request->store_name ?? "",
                'store_url' => $request->store_url ?? "",
                'store_description' => $request->description ?? "",
                'commission' => $request->global_commission ?? 0,
                'account_number' => $request->account_number ?? "",
                'account_name' => $request->account_name ?? "",
                'bank_name' => $request->bank_name ?? "",
                'bank_code' => $request->bank_code ?? "",
                'status' => 0,
                'tax_name' => $request->tax_name ?? "",
                'tax_number' => $request->tax_number ?? "",
                'category_ids' => $requested_categories ?? '',
                'permissions' => (isset($permmissions) && $permmissions != "") ? json_encode($permmissions) : null,
                'slug' => generateSlug($request->input('store_name'), 'seller_store'),
                'store_id' => $store_id,
                'latitude' => $request->latitude ?? "",
                'longitude' => $request->longitude ?? "",
                'city' => $request->city ?? "",
                'zipcode' => $request->zipcode ?? "",
                'disk' => isset($address_proof->disk) && !empty($address_proof->disk) ? $address_proof->disk : 'public',
                'deliverable_type' => isset($request->deliverable_type) && !empty($request->deliverable_type) ? $request->deliverable_type : '',
                'deliverable_zones' => ($request->deliverable_type == '1' || $request->deliverable_type == '0') ? '' : $zones,
            ]);

            $seller_store = DB::table('seller_store')->insert($seller_store_data);

            if (isset($request->requested_categories) && !empty($request->requested_categories)) {
                $requested_commission_category_ids = explode(',', $request->requested_categories);
                foreach ($requested_commission_category_ids as $category_id) {
                    SellerCommission::create([
                        'seller_id' => $seller->id,
                        'store_id' => $store_id,
                        'category_id' => $category_id,
                        'commission' => 0,
                    ]);
                }
            }

            $user_id = auth()->user()->id;
            $user = fetchDetails('users', ['id' => $user_id], '*')[0];
            $fcm_ids = fetchdetails('user_fcm', ['user_id' => $user->id], 'fcm_id');

            $fcm_ids_array = array_map(function ($item) {
                return $item->fcm_id;
            }, $fcm_ids);

            $userData = formatUserData($user, $fcm_ids_array);

            $seller_data = fetchDetails('seller_data', ['user_id' => $user_id], '*');
            $store_data = fetchDetails('seller_store', ['user_id' => $user->id], '*');
            $seller_data[0]->seller_id = $seller_data[0]->id;
            $data = (array_merge($userData, (array) $seller_data));
            $output = $userData;
            unset($seller_data[0]->id);
            $isPublicDisk = $store_data[0]->disk == 'public' ? 1 : 0;
            $output['store_data'] = formatStoreData($store_data, $isPublicDisk, $language_code);
            $output['seller_data'] = array_map(fn($seller) => (array) $seller, formatSellerData($seller_data, $isPublicDisk));
            foreach ($data as $key => $value) {
                if (property_exists(!empty($seller_data) ? $seller_data[0] : '', $key)) {
                    $output[$key] = $value;
                }
            }
            foreach ($data as $key => $value) {
                if (property_exists($seller_data[0], $key)) {
                    $output[$key] = $value;
                }
            }
            unset($output[0]->password);
            if ($seller_store) {
                $response = [
                    'error' => false,
                    'message' => 'Store registered successfully wait for admin approvel!',
                    'language_message_key' => 'store_registered_successfully',
                    'data' => $output,
                ];
            } else {
                $response = [
                    'error' => true,
                    'message' => 'Something went wrong.',
                    'language_message_key' => 'something_went_wrong',
                    'data' => [],
                ];
            }
            return response()->json($response);
        }
    }
    public function get_total_data(Request $request)
    {
        $rules = [
            'store_id' => 'required|exists:stores,id',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        }

        $user_id = Auth::id();
        $seller_id = Seller::where('user_id', $user_id)->value('id');
        $store_id = $request->input('store_id') ?? '';

        $total_balance = fetchDetails('users', ['id' => $user_id], 'balance')[0]->balance;
        $totalSale = DB::table('order_items')
            ->selectRaw('SUM(sub_total) as overall_sale')
            ->where('seller_id', $seller_id)
            ->where('store_id', $store_id)
            ->where('active_status', 'delivered')
            ->first();
        $totalCommission = DB::table('order_items')
            ->selectRaw('SUM(seller_commission_amount) as seller_commission_amount')
            ->where('seller_id', $seller_id)
            ->where('store_id', $store_id)
            ->first();
        // dd($totalCommission);
        $overallSale = $totalSale->overall_sale ?? 0;

        $total_commission_amount = $totalCommission->seller_commission_amount ?? 0;

        $total_orders = ordersCount('', $seller_id, '', $store_id);

        $total_products = countProducts($seller_id, $store_id);

        $low_stock_products = countProductsStockLowStatus($seller_id, $store_id);

        $response = [
            'error' => false,
            'message' => 'Data retrived successfully',
            'language_message_key' => 'data_retrived_successfully',
            'data' => [
                'total_balance' => $total_balance,
                'total_sales' => $overallSale,
                'total_orders' => $total_orders,
                'total_products' => $total_products,
                'total_commission_amount' => $total_commission_amount,
                'low_stock_products' => $low_stock_products,
            ]
        ];
        return response()->json($response);
    }

    public function get_overview_statistic(Request $request)
    {
        $rules = [
            'store_id' => 'required|exists:stores,id',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        }

        $user_id = Auth::id();
        $seller_id = Seller::where('user_id', $user_id)->value('id');
        $store_id = $request->input('store_id');

        $sales = [];

        // Monthly Earnings
        $monthRes = DB::table('order_items')
            ->selectRaw('SUM(quantity) AS total_sale, SUM(sub_total) AS total_revenue, COUNT(*) AS total_orders, DATE_FORMAT(created_at, "%b") AS month_name')
            ->where('seller_id', $seller_id)
            ->where('store_id', $store_id)
            ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at)'))
            ->orderBy(DB::raw('YEAR(created_at), MONTH(created_at)'))
            ->get()->toArray();

        $allMonths = array_fill_keys(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'], [
            'total_sale' => 0,
            'total_orders' => 0,
            'total_revenue' => 0,
        ]);

        foreach ($monthRes as $month) {
            $monthName = $month->month_name;
            $allMonths[$monthName] = [
                'total_sale' => intval($month->total_sale),
                'total_orders' => intval($month->total_orders),
                'total_revenue' => intval($month->total_revenue),
            ];
        }

        $monthWiseSales = [
            'total_sale' => array_column($allMonths, 'total_sale'),
            'total_orders' => array_column($allMonths, 'total_orders'),
            'total_revenue' => array_column($allMonths, 'total_revenue'),
            'month_name' => array_keys($allMonths),
        ];

        $sales['monthly'] = $monthWiseSales;

        // Weekly Earnings
        $startDate = Carbon::now()->startOfWeek();
        $endDate = Carbon::now()->endOfWeek();

        $weekWiseSales = [
            'total_sale' => [],
            'total_revenue' => [],
            'total_orders' => [],
            'day' => [],
        ];

        for ($i = 0; $i < 7; $i++) {
            $currentDate = $startDate->copy()->addDays($i);
            $dayName = $currentDate->englishDayOfWeek;

            $dayRes = DB::table('order_items')
                ->selectRaw("SUM(quantity) as total_sale, SUM(sub_total) as total_revenue, COUNT(*) as total_orders")
                ->where('seller_id', $seller_id)
                ->where('store_id', $store_id)
                ->whereDate('created_at', $currentDate)
                ->first();

            $weekWiseSales['total_sale'][] = $dayRes ? intval($dayRes->total_sale) : 0;
            $weekWiseSales['total_revenue'][] = $dayRes ? intval($dayRes->total_revenue) : 0;
            $weekWiseSales['total_orders'][] = $dayRes ? intval($dayRes->total_orders) : 0;
            $weekWiseSales['day'][] = $dayName;
        }

        $sales['weekly'] = $weekWiseSales;

        // today Earnings - Modified to return today's data
        $today = Carbon::today();

        $dayWiseSales = [
            'total_sale' => 0,
            'total_revenue' => 0,
            'total_orders' => 0,
            'day' => $today->format('j-n-y'),
        ];

        $todayRes = DB::table('order_items')
            ->selectRaw("SUM(quantity) as total_sale, SUM(sub_total) as total_revenue, COUNT(*) as total_orders")
            ->where('seller_id', $seller_id)
            ->where('store_id', $store_id)
            ->whereDate('created_at', $today)
            ->first();

        $dayWiseSales['total_sale'] = $todayRes ? intval($todayRes->total_sale) : 0;
        $dayWiseSales['total_revenue'] = $todayRes ? intval($todayRes->total_revenue) : 0;
        $dayWiseSales['total_orders'] = $todayRes ? intval($todayRes->total_orders) : 0;

        // Add today sales to the sales array
        $sales['today'] = $dayWiseSales;

        return response()->json([
            'error' => false,
            'message' => 'Data retrieved successfully',
            'language_message_key' => 'data_retrieved_successfully',
            'data' => $sales
        ]);
    }


    public function getOverviewStatistic(Request $request)
    {
        $rules = [
            'store_id' => 'required|exists:stores,id',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        }
        $user_id = Auth::id();
        $seller_id = Seller::where('user_id', $user_id)->value('id');
        $store_id = $request->input('store_id');

        $sales = [];
        $monthRes = DB::table('order_items')
            ->selectRaw('SUM(quantity) AS total_sale, SUM(sub_total) AS total_revenue, COUNT(*) AS total_orders, DATE_FORMAT(created_at, "%M") AS month_name')
            ->where('seller_id', $seller_id)
            ->where('store_id', $store_id)
            ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at)'))
            ->orderBy(DB::raw('YEAR(created_at), MONTH(created_at)'))
            ->get()->toArray();

        // Initialize all months with zero values
        $allMonths = [
            'january' => ['total_sale' => 0, 'total_revenue' => 0, 'total_orders' => 0],
            'february' => ['total_sale' => 0, 'total_revenue' => 0, 'total_orders' => 0],
            'march' => ['total_sale' => 0, 'total_revenue' => 0, 'total_orders' => 0],
            'april' => ['total_sale' => 0, 'total_revenue' => 0, 'total_orders' => 0],
            'may' => ['total_sale' => 0, 'total_revenue' => 0, 'total_orders' => 0],
            'june' => ['total_sale' => 0, 'total_revenue' => 0, 'total_orders' => 0],
            'july' => ['total_sale' => 0, 'total_revenue' => 0, 'total_orders' => 0],
            'august' => ['total_sale' => 0, 'total_revenue' => 0, 'total_orders' => 0],
            'september' => ['total_sale' => 0, 'total_revenue' => 0, 'total_orders' => 0],
            'october' => ['total_sale' => 0, 'total_revenue' => 0, 'total_orders' => 0],
            'november' => ['total_sale' => 0, 'total_revenue' => 0, 'total_orders' => 0],
            'december' => ['total_sale' => 0, 'total_revenue' => 0, 'total_orders' => 0],
        ];

        // Merge database results with allMonths array
        foreach ($monthRes as $month) {
            $monthName = strtolower($month->month_name);
            $allMonths[$monthName] = [
                'total_sale' => intval($month->total_sale),
                'total_revenue' => intval($month->total_revenue),
                'total_orders' => intval($month->total_orders),
            ];
        }

        $sales['monthly'] = $allMonths;

        // Weekly Earnings
        $startDate = Carbon::now()->startOfWeek();
        $endDate = Carbon::now()->endOfWeek();

        $weekWiseSales = [];
        for ($i = 0; $i < 7; $i++) {
            $currentDate = $startDate->copy()->addDays($i);
            $dayName = strtolower($currentDate->englishDayOfWeek);

            $dayRes = DB::table('order_items')
                ->selectRaw("SUM(quantity) as total_sale, SUM(sub_total) as total_revenue, COUNT(*) as total_orders")
                ->where('seller_id', $seller_id)
                ->where('store_id', $store_id)
                ->whereDate('created_at', $currentDate)
                ->first();

            $weekWiseSales[$dayName] = [
                'total_sale' => $dayRes ? intval($dayRes->total_sale) : 0,
                'total_revenue' => $dayRes ? intval($dayRes->total_revenue) : 0,
                'total_orders' => $dayRes ? intval($dayRes->total_orders) : 0,
            ];
        }

        $sales['weekly'] = $weekWiseSales;

        // Daily Earnings for the last 30 days
        $startDate = Carbon::now()->subDays(29);
        $endDate = Carbon::now();

        // Create an array to hold daily sales data
        $dayWiseSales = [];
        $dayRes = DB::table('order_items')
            ->selectRaw("DAY(created_at) as date, MONTH(created_at) as month, YEAR(created_at) as year, SUM(quantity) as total_sale, SUM(sub_total) as total_revenue, COUNT(*) as total_orders")
            ->where('seller_id', $seller_id)
            ->where('store_id', $store_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at), DAY(created_at)'))
            ->get();

        // Loop through each day in the last 30 days
        for ($i = 0; $i < 30; $i++) {
            $currentDate = $startDate->copy()->addDays($i);
            $day = $currentDate->day;
            $month = strtolower($currentDate->format('M')); // Short month name
            $year = $currentDate->year;

            // Find the result for the current day
            $dayData = $dayRes->firstWhere('date', $day);

            $dayWiseSales["{$day}-{$month}-{$year}"] = [
                'total_sale' => $dayData ? intval($dayData->total_sale) : 0,
                'total_revenue' => $dayData ? intval($dayData->total_revenue) : 0,
                'total_orders' => $dayData ? intval($dayData->total_orders) : 0,
            ];
        }

        $sales['daily'] = $dayWiseSales;


        return response()->json(['error' => false, 'message' => 'Data retrived successfully', 'language_message_key' => 'data_retrived_successfully', 'data' => $sales]);
    }

    public function most_selling_categories(Request $request)
    {
        $rules = [
            'store_id' => 'required|exists:stores,id',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        }
        $user_id = Auth::id();
        $seller_id = Seller::where('user_id', $user_id)->value('id');
        $store_id = $request->input('store_id');

        $language_code = $request->attributes->get('language_code');
        $most_selling_categories = [];

        // Monthly data for category chart
        $first_day_of_month = Carbon::now()->startOfMonth();
        $last_day_of_month = Carbon::now()->endOfMonth();

        $monthly_most_selling_categories = DB::table('order_items')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name as category_name', 'categories.id as category_id', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->where('order_items.seller_id', '=', $seller_id)
            ->where('order_items.store_id', '=', $store_id)
            ->whereBetween('order_items.created_at', [$first_day_of_month, $last_day_of_month])
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        $monthly_most_selling_categories_data['total_sold'] = $monthly_most_selling_categories->pluck('total_sold');
        $monthly_most_selling_categories_data['category_names'] = $monthly_most_selling_categories->map(function ($item) use ($language_code) {
            return getDynamicTranslation('categories', 'name', $item->category_id, $language_code);
        });
        // $monthly_most_selling_categories_data['category_names'] = $monthly_most_selling_categories->pluck('category_name');
        $most_selling_categories['monthly'] = $monthly_most_selling_categories_data;

        // Yearly data for category chart
        $current_year = Carbon::now()->year;
        $first_day_of_year = Carbon::create($current_year, 1, 1)->startOfDay();
        $last_day_of_year = Carbon::create($current_year, 12, 31)->endOfDay();

        $yearly_most_selling_categories = DB::table('order_items')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name as category_name', 'categories.id as category_id', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->where('order_items.seller_id', '=', $seller_id)
            ->where('order_items.store_id', '=', $store_id)
            ->whereBetween('order_items.created_at', [$first_day_of_year, $last_day_of_year])
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        $yearly_most_selling_categories_data['total_sold'] = $yearly_most_selling_categories->pluck('total_sold');
        // $yearly_most_selling_categories_data['category_names'] = $yearly_most_selling_categories->pluck('category_name');
        $yearly_most_selling_categories_data['category_names'] = $yearly_most_selling_categories->map(function ($item) use ($language_code) {
            return getDynamicTranslation('categories', 'name', $item->category_id, $language_code);
        });
        $most_selling_categories['yearly'] = $yearly_most_selling_categories_data;

        // Weekly data for category chart
        $start_date = Carbon::now()->startOfWeek();
        $end_date = Carbon::now()->endOfWeek();

        $weekly_most_selling_categories = DB::table('order_items')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name as category_name', 'categories.id as category_id', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->where('order_items.seller_id', '=', $seller_id)
            ->where('order_items.store_id', '=', $store_id)
            ->whereBetween('order_items.created_at', [$start_date, $end_date])
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        $weekly_most_selling_categories_data['total_sold'] = $weekly_most_selling_categories->pluck('total_sold');
        $weekly_most_selling_categories_data['category_names'] = $weekly_most_selling_categories->map(function ($item) use ($language_code) {
            return getDynamicTranslation('categories', 'name', $item->category_id, $language_code);
        });
        // $weekly_most_selling_categories_data['category_names'] = $weekly_most_selling_categories->pluck('category_name');
        $most_selling_categories['weekly'] = $weekly_most_selling_categories_data;

        // Prepare final response
        return response()->json([
            'error' => false,
            'message' => 'Data retrived successfully',
            'language_message_key' => 'data_retrived_successfully',
            'most_selling_categories' => $most_selling_categories,
        ]);
    }

    public function top_selling_products(Request $request)
    {
        $rules = [
            'store_id' => 'required|exists:stores,id',
            'category_id' => 'required|nullable|numeric|exists:categories,id',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        }


        $user_id = Auth::id();
        $seller_id = Seller::where('user_id', $user_id)->value('id');
        $store_id = $request->input('store_id');
        $category_id = $request->input('category_id');
        $language_code = $request->attributes->get('language_code');
        // Build the query for top-selling products based on the category
        $query = DB::table('order_items')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->select('products.image as image', 'products.category_id', 'products.id as product_id', 'products.brand as brand_id', 'products.name', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->where('order_items.seller_id', '=', $seller_id)
            ->where('order_items.store_id', '=', $store_id);

        // Apply the category filter if provided
        if (!empty($category_id)) {
            $query->where('products.category_id', '=', $category_id);
        }

        // Fetch the top 5 selling products, grouped by product
        $top_selling_products = $query->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get()
            ->map(function ($product) use ($language_code) {
                $product->image = getMediaImageUrl($product->image);
                $product->name = getDynamicTranslation('products', 'name', $product->product_id, $language_code);
                return $product;
            });
        return response()->json([
            'error' => !$top_selling_products->isEmpty() ? false : true,
            'message' => !$top_selling_products->isEmpty() ? 'Data retrieved successfully' : 'No data found',
            'language_message_key' => !$top_selling_products->isEmpty() ? 'data_retrived_successfully' : 'no_data_found',
            'category_ids' => implode(',', collect($top_selling_products)->pluck('category_id')->unique()->values()->all()),
            'brand_ids' => implode(',', collect($top_selling_products)->pluck('brand_id')->filter()->unique()->values()->all()),
            'data' => $top_selling_products,
        ]);
    }
    public function get_user_details(Request $request)
    {
        $rules = [
            'id' => 'required|exists:users,id',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        }
        $id = $request->input('id') ?? '';

        $user = User::where('id', $id)->first();
        $fcm_ids = fetchdetails('user_fcm', ['user_id' => $user->id], 'fcm_id');

        $fcm_ids_array = array_map(function ($item) {
            return $item->fcm_id;
        }, $fcm_ids);

        $userData = formatUserData($user, $fcm_ids_array);

        return response()->json([
            'error' => false,
            'message' => 'Dats retrived successfully',
            'language_message_key' => 'data_retrivrd_successfully',
            'data' => $userData,
        ]);
    }

    public function download_order_invoice(Request $request)
    {
        $rules = [
            'order_id' => 'required|numeric|exists:orders,id',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {

            if (auth()->check()) {
                $user_id = auth()->user()->id;
                $seller_id = Seller::where('user_id', $user_id)->value('id');
            }
            $order_id = $request->input('order_id');

            if (!isExist(['id' => $order_id], 'orders')) {
                $response = [
                    'error' => true,
                    'message' => 'No data found!',
                    'language_message_key' => 'no_data_found',
                    'data' => [],
                ];
                return response()->json($response);
            }

            // Generating the URL to download the invoice
            $invoice_url = route('seller.orders.generatInvoicePDF', ['id' => $order_id, 'seller_id' => $seller_id]);

            $response = [
                'error' => false,
                'message' => 'Invoice URL generated successfully',
                'invoice_url' => $invoice_url,  // Return the generated URL
            ];

            return response()->json($response);
        }
    }


    public function download_parcel_invoice(Request $request, SellerOrderController $SellerOrderController)
    {
        /*
            id:154
        */
        $rules = [
            'id' => 'required|numeric|exists:parcels,id',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        } else {
            $id = $request->input('id');

            if (!isExist(['id' => $id], 'parcels')) {
                $response = [
                    'error' => true,
                    'message' => 'No data found!',
                    'language_message_key' => 'no_data_found',
                    'data' => [],
                ];
                return response()->json($response);
            }

            // Generating the URL to download the invoice
            $invoice_url = route('seller.orders.generatParcelInvoicePDF', ['id' => $id]);

            $response = [
                'error' => false,
                'message' => 'Invoice URL generated successfully',
                'invoice_url' => $invoice_url,  // Return the generated URL
            ];

            return response()->json($response);
        }
    }
    public function get_zones(Request $request)
    {
        $language_code = $request->attributes->get('language_code');
        return getZones($request, $language_code);
    }

    public function get_all_parcels(Request $request)
    {
        // order_id:10 // optional
        // parcel_id:107 // optional
        // in_detail:0 // by default 0, if product detail needed than pass 1
        // limit:10 // optional
        // offset:0 // optional
        // order:desc // optional
        // parcel_type:combo_order/regular_order
        // store_id:required
        $rules = [
            'store_id' => 'required|numeric|exists:stores,id',
            'order_id' => 'numeric|exists:orders,id',
            'parcel_id' => 'numeric|exists:parcels,id',
            'parcel_type' => 'string|in:combo_order,regular_order',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        }

        $user_id = Auth::id();
        $seller_id = Seller::where('user_id', $user_id)->value('id');
        $order_id = $request->input('order_id') ?? "";
        $store_id = $request->input('store_id') ?? "";
        $in_detail = $request->input('in_detail') ?? 1;
        $parcel_id = $request->input('parcel_id') ?? "";
        $offset = $request->input('offset') ?? 0;
        $limit = $request->input('limit') ?? 10;
        $order = $request->input('order') ?? "desc";
        $parcel_type = $request->input('parcel_type');
        $res = viewAllParcels($order_id, $parcel_id, $seller_id, $offset, $limit, $order, $in_detail, '', '', $store_id, $parcel_type);
        return response()->json([
            'error' => $res->original['error'],
            'message' => $res->original['message'],
            'language_message_key' => 'data_retrivrd_successfully',
            'total' => $res->original['total'],
            'data' => $res->original['data'],
        ]);
    }
    public function create_order_parcel(Request $request)
    {
        /*
            order_id:154
            selected_items:123,565
            parcel_title:parcel 1
            parcel_order_type:regular_order/combo_order
        */

        $rules = [
            'selected_items' => 'required',
            'selected_items.*' => 'required|distinct',
            'parcel_title' => 'required|string|max:255',
            'order_id' => 'required|string|max:255',
            'parcel_order_type' => 'required|string|max:255',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        }

        $user_id = Auth::id();
        $request['seller_id'] = Seller::where('user_id', $user_id)->value('id');
        $request['selected_items'] = explode(',', $request->selected_items);
        $res = createParcel($request);
        if ($res['error'] == true) {
            $response['error'] = $res['error'];
            $response['message'] = $res['message'];
            $response['data'] = [];
            return response()->json($response);
        }
        $parcel_type = $request->parcel_order_type;


        $parcel_res = viewAllParcels('', $res['data'][0]['parcel_id'], offset: 0, limit: 10, parcel_type: $parcel_type);

        if ($res['error'] == false) {
            $response['error'] = $res['error'];
            $response['message'] = $res['message'];
            $response['data'] = $parcel_res->original['data'];
            return response()->json($response);
        }
        $response['error'] = $res['error'];
        $response['message'] = $res['message'];
        return response()->json($response);
    }
    public function delete_order_parcel(Request $request)
    {
        /*
            id:154
        */

        $rules = [
            'id' => 'required|numeric',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        }
        $user_id = Auth::id();
        $seller_id = Seller::where('user_id', $user_id)->value('id');

        $parcel_id = $request->id ?? "";
        $res = deleteParcel($parcel_id);
        return response()->json([
            'error' => $res['error'],
            'message' => $res['message'],
        ]);
    }
    public function update_parcel_order_status(Request $request, SellerOrderController $SellerOrderController)
    {
        // if type is digital order
        /*
            status : received/delivered
            order_id : 1
            order_item_ids : 1,2
            type : digital
        */
        /*
            status : received,processed,shipped,delivered,cancelled,returned
            deliver_by : 1
            parcel_id : 1
        */
        if (auth()->check()) {
            $user_id = auth()->user()->id;
            $seller_id = Seller::where('user_id', $user_id)->value('id');
        }
        $request['seller_id'] = $seller_id;
        $request['order_item_ids'] = explode(',', $request['order_item_ids']);
        $orderData = $SellerOrderController->update_order_status($request);
        return response()->json($orderData->original);
    }
    public function update_shiprocket_order_status(Request $request, SellerOrderController $SellerOrderController)
    {
        /*
            tracking_id : abcd1234
        */
        $rules = [
            'tracking_id' => 'required',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        }
        $user_id = auth()->user()->id;
        $seller_id = Seller::where('user_id', $user_id)->value('id');
        $in_detail = $request->input('in_detail') ?? "";
        $offset = $request->input('offset') ?? 0;
        $limit = $request->input('limit') ?? 10;
        $order = $request->input('order') ?? "desc";
        $tracking_id = $request->tracking_id ?? "";
        $res = update_shiprocket_order_status($tracking_id);
        $result = fetchDetails('order_trackings', ['tracking_id' => $tracking_id], 'parcel_id');
        $details = "";
        if (isset($result[0]->parcel_id) && !empty($result[0]->parcel_id)) {
            $details = viewAllParcels('', $result[0]->parcel_id, $seller_id, $offset, $limit, $order, $in_detail, '', '');
        }
        return response()->json([
            'error' => ($res['error'] == false) ? false : true,
            'message' => $res['message'],
            'data' => !empty($details) ? $details->original['data'][0] : "",
        ]);
    }

    private function formatMediaUrls(&$settings)
    {
        foreach ($settings as $key => $value) {
            if ($value === null) {
                $settings[$key] = "";
            } elseif (in_array($key, ['logo', 'favicon']) && !empty($value)) {
                $settings[$key] = getMediaImageUrl($value);
            }
        }

        // Handle onboarding media separately
        if (isset($settings['on_boarding_image']) && !empty($settings['on_boarding_image'])) {
            foreach ($settings['on_boarding_image'] as &$image) {
                $image = getMediaImageUrl($image);
            }
        } else {
            $settings['on_boarding_image'] = [];
        }

        if (isset($settings['on_boarding_video']) && !empty($settings['on_boarding_video'])) {
            foreach ($settings['on_boarding_video'] as &$video) {
                $video = getMediaImageUrl($video);
            }
        } else {
            $settings['on_boarding_video'] = [];
        }
    }
    public function get_notifications(Request $request, NotificationController $NotificationController)
    {

        $rules = [
            'sort' => 'nullable|sometimes|string',
            'limit' => 'nullable|sometimes|numeric',
            'offset' => 'nullable|sometimes|numeric',
            'order' => 'nullable|sometimes|string',
            'store_id' => 'required|numeric',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        }
        $limit = $request->input('limit', 25);
        $offset = $request->input('offset', 0);
        $order = $request->input('order', 'DESC');
        $sort = $request->input('sort', 'id');
        $user_id = $request->input('user_id') ?? "";


        $res = $NotificationController->get_seller_notifications($offset, $limit, $sort, $order, $user_id);
        return response()->json([
            'error' => empty($res['data']) ? true : false,
            'message' => empty($res['data']) ? 'Notification not found' : 'Notification Retrieved Successfully',
            'language_message_key' => empty($res['data']) ? 'no_data_found' : 'notification_retrieved_successfully',
            'total' => $res['total'],
            'data' => $res['data'],
        ]);
    }

    public function update_product_deliverability(Request $request)
    {
        $rules = [
            'product_id' => 'required|string',
            'deliverable_type' => 'required',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        }

        $product_ids = explode(',', $request->product_id);

        $valid_products = Product::whereIn('id', $product_ids)->pluck('id')->toArray();
        if (count($valid_products) !== count($product_ids)) {
            return response()->json([
                'error' => true,
                'message' => 'Some product IDs are invalid.',
            ], 422);
        }

        $zones = is_array($request->deliverable_zones) ? implode(',', $request->deliverable_zones) : '';
        $deliverable_zones = ($request->deliverable_type == '1' || $request->deliverable_type == '0') ? '' : $zones;

        // Bulk update
        Product::whereIn('id', $product_ids)->update([
            'deliverable_type' => $request->deliverable_type,
            'deliverable_zones' => $deliverable_zones,
        ]);

        return response()->json([
            'error' => false,
            'message' => 'Deliverability updated successfully!',
        ], 200);
    }
    public function update_combo_product_deliverability(Request $request)
    {
        $rules = [
            'product_id' => 'required|string',
            'deliverable_type' => 'required',
        ];
        if ($validationResponse = validateRequest($request, $rules)) {
            return $validationResponse;
        }
        $product_ids = explode(',', $request->product_id);

        $valid_products = ComboProduct::whereIn('id', $product_ids)->pluck('id')->toArray();
        if (count($valid_products) !== count($product_ids)) {
            return response()->json([
                'error' => true,
                'message' => 'Some product IDs are invalid.',
            ], 422);
        }

        $zones = is_array($request->deliverable_zones) ? implode(',', $request->deliverable_zones) : '';
        $deliverable_zones = ($request->deliverable_type == '1' || $request->deliverable_type == '0') ? '' : $zones;

        // Bulk update
        ComboProduct::whereIn('id', $product_ids)->update([
            'deliverable_type' => $request->deliverable_type,
            'deliverable_zones' => $deliverable_zones,
        ]);

        return response()->json([
            'error' => false,
            'message' => 'Deliverability updated successfully!',
        ], 200);
    }
}
