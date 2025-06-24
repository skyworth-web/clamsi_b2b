<?php

namespace App\Http\Controllers\Delivery_boy\v1;

use App\Http\Controllers\Admin\Delivery_boyController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Delivery_boy\CashCollectionController;
use App\Http\Controllers\Delivery_boy\OrderController;
use App\Http\Controllers\Seller\AreaController;
use App\Http\Controllers\Seller\PaymentRequestController;
use App\Models\OrderItems;
use App\Models\Language;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;

class ApiController extends Controller
{
    /*
---------------------------------------------------------------------------
Defined Methods:-
---------------------------------------------------------------------------
1. login
2. register
3. get_zipcodes
4. get_delivery_boy_details
<---- Newly changes for parcels ---->
5. get_orders
<---- Newly changes for parcels ---->
6. get_fund_transfers
7. update_fcm
8. update_user
9. get_notifications
10.verify_user
11.get_settings
12.send_withdrawal_request
13.get_withdrawal_request
14.update_order_item_status
15.get_delivery_boy_cash_collection
16.delete_delivery_boy
17.get_wallet_transaction
<---- Newly changes for return order ---->
18. view_return_order_items
19. update_return_order_item_status
<---- Newly changes for return order ---->

*/

    public function login(Request $request)
    {
        /*
            mobile: 9874565478
            password: 12345678
            fcm_id: FCM_ID //{ optional }
        */

        $validator = Validator::make($request->all(), [
            'mobile' => 'required|numeric',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            ];
            return response()->json($response);
        } else {
            $credentials = $request->only('mobile', 'password');

            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                $token = $user->createToken('authToken')->plainTextToken;
                $fcm_ids = fetchdetails('user_fcm', ['user_id' => $user->id], 'fcm_id');

                $fcm_ids_array = array_map(function ($item) {
                    return $item->fcm_id;
                }, $fcm_ids);
                $language_code = $request->attributes->get('language_code');
                $zone_ids = explode(',', $user->serviceable_zones);
                $zones = Zone::whereIn('id', $zone_ids)->get();

                $translated_zones = $zones->map(function ($zone) use ($language_code) {
                    return getDynamicTranslation('zones', 'name', $zone->id, $language_code);
                })->toArray();

                $zones = implode(',', $translated_zones) ?? '';

                $userData = [
                    'user_id' => $user->id ?? '',
                    'ip_address' => $user->ip_address ?? '',
                    'username' => $user->username ?? '',
                    'email' => $user->email ?? '',
                    'mobile' => $user->mobile ?? '',
                    'image' => !empty($user->image) ? getMediaImageUrl($user->image, 'DELIVERY_BOY_IMG_PATH') : '',
                    'activation_selector' => $user->activation_selector ?? '',
                    'activation_code' => $user->activation_code ?? '',
                    'forgotten_password_selector' => $user->forgotten_password_selector ?? '',
                    'forgotten_password_code' => $user->forgotten_password_code ?? '',
                    'forgotten_password_time' => $user->forgotten_password_time ?? '',
                    'remember_selector' => $user->remember_selector ?? '',
                    'remember_code' => $user->remember_code ?? '',
                    'created_on' => $user->created_on ?? '',
                    'last_login' => $user->last_login ?? '',
                    'active' => $user->active ?? '',
                    'is_notification_on' => $user->is_notification_on ?? '',
                    'company' => $user->company ?? '',
                    'address' => $user->address ?? '',
                    'bonus' => $user->bonus ?? '',
                    'bonus_type' => $user->bonus_type ?? '',
                    'cash_received' => $user->cash_received ?? '0.00',
                    'dob' => $user->dob ?? '',
                    'country_code' => $user->country_code ?? '',
                    'city' => $user->city ?? '',
                    'area' => $user->area ?? '',
                    'street' => $user->street ?? '',
                    'pincode' => $user->pincode ?? '',
                    'apikey' => $user->apikey ?? '',
                    'referral_code' => $user->referral_code ?? '',
                    'friends_code' => $user->friends_code ?? '',
                    'fcm_id' => array_values($fcm_ids_array) ?? '',
                    'latitude' => $user->latitude ?? '',
                    'longitude' => $user->longitude ?? '',
                    'created_at' => $user->created_at ?? '',
                    'type' => $user->type ?? '',
                    'serviceable_zones' => $user->serviceable_zones ?? '',
                    'zones' => $zones ?? '',
                    'front_licence_image' => !empty($user->front_licence_image) ? getMediaImageUrl($user->front_licence_image, 'DELIVERY_BOY_IMG_PATH') : '',
                    'back_licence_image' => !empty($user->back_licence_image) ? getMediaImageUrl($user->back_licence_image, 'DELIVERY_BOY_IMG_PATH') : '',
                ];

                if ($user->role_id == 3) {
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
                            DB::table('user_fcm')->insert($fcm_data);
                        }
                    }
                    unset($user->password);

                    $messages = array(
                        "0" => "Your account is deactivated",
                        "1" => "Logged in successfully",
                        "2" => "Your account is not yet approved.",
                        "7" => "Your account has been removed by the admin. Contact admin for more information."
                    );

                    $language_message_key = array(
                        "0" => "account_deactivated",
                        "1" => "user_logged_in_successfully",
                        "2" => "account_not_yet_approved",
                        "7" => "account_removed_by_admin_contact_admin",
                    );

                    //if the login is successful


                    return response()->json([
                        'error' => (isset($user->status) && $user->status != "" && ($user->status == 1)) ? false : true,
                        'message' => $messages[$user->status],
                        'language_message_key' => $language_message_key[$user->status],
                        'token' => $token,
                        'data' => (isset($user->status) && $user->status != "" && ($user->status == 1)) ? $userData : [],
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
                ], 200);
            }
        }
    }


    public function register(Delivery_boyController $deliveryBoyController, Request $request)
    {
        /*
            name:hiten
            mobile:7852347890
            email:amangoswami@gmail.com
            password:12345678
            confirm_password:12345678
            address : test
            serviceable_zones[] : 1,2
            front_licence_image : FILE
            back_licence_image : FILE
            profile_image : FILE
            bonus_type : percentage_per_order/fixed_amount_per_order
            bonus_amount : 20 // required when type is fixed_amount_per_order
            bonus_percentage : 20 // required when type is percentage_per_order
        */

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'mobile' => 'required|unique:users,mobile',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
            'address' => 'required',
            'front_licence_image' => 'required',
            'profile_image' => 'required',
            'back_licence_image' => 'required',
            'serviceable_zones' => 'required|array',
        ]);
        if ($validator->fails()) {
            $response = [
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            ];
            return response()->json($response);
        }

        $delivery_boy_data = $deliveryBoyController->store($request, true);


        if ($delivery_boy_data->original['error'] != true) {
            return response()->json([
                'error' => false,
                'message' => 'Delivery Boy registered Successfully. Wait for approval of admin.',
                'language_message_key' => 'delivery_boy_registered_successfully_wait_for_approval',

            ]);
        } else {
            return response()->json([
                'error' => $delivery_boy_data->original['error'],
                'message' => $delivery_boy_data->original['message'],
                'data' => [],
            ]);
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
        $validator = Validator::make($request->all(), [
            'sort' => 'string',
            'limit' => 'numeric',
            'offset' => 'numeric',
        ]);

        if ($validator->fails()) {
            $response = [
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            ];
            return response()->json($response);
        } else {
            $zipcode_data = $areaController->zipcode_list($request);


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

    public function get_delivery_boy_details(Request $request)
    {
        if (auth()->check()) {
            $user = Auth::user();
            $fcm_ids = fetchdetails('user_fcm', ['user_id' => $user->id], 'fcm_id');

            $fcm_ids_array = array_map(function ($item) {
                return $item->fcm_id;
            }, $fcm_ids);
            $language_code = $request->attributes->get('language_code');
            $zone_ids = explode(',', $user->serviceable_zones);
            $zones = Zone::whereIn('id', $zone_ids)->get();

            $translated_zones = $zones->map(function ($zone) use ($language_code) {
                return getDynamicTranslation('zones', 'name', $zone->id, $language_code);
            })->toArray();

            $zones = implode(',', $translated_zones) ?? '';
            $userData = [
                'user_id' => $user->id ?? '',
                'ip_address' => $user->ip_address ?? '',
                'username' => $user->username ?? '',
                'email' => $user->email ?? '',
                'mobile' => $user->mobile ?? '',
                'image' => !empty($user->image) ? getMediaImageUrl($user->image, 'DELIVERY_BOY_IMG_PATH') : '',
                'activation_selector' => $user->activation_selector ?? '',
                'activation_code' => $user->activation_code ?? '',
                'forgotten_password_selector' => $user->forgotten_password_selector ?? '',
                'forgotten_password_code' => $user->forgotten_password_code ?? '',
                'forgotten_password_time' => $user->forgotten_password_time ?? '',
                'remember_selector' => $user->remember_selector ?? '',
                'remember_code' => $user->remember_code ?? '',
                'created_on' => $user->created_on ?? '',
                'last_login' => $user->last_login ?? '',
                'active' => $user->active ?? '',
                'is_notification_on' => $user->is_notification_on ?? '',
                'balance' => $user->balance ?? '',
                'company' => $user->company ?? '',
                'address' => $user->address ?? '',
                'bonus' => $user->bonus ?? '',
                'bonus_type' => $user->bonus_type ?? '',
                'serviceable_zones' => $user->serviceable_zones ?? '',
                'zones' => $zones ?? '',
                'cash_received' => $user->cash_received ?? '0.00',
                'dob' => $user->dob ?? '',
                'country_code' => $user->country_code ?? '',
                'city' => $user->city ?? '',
                'area' => $user->area ?? '',
                'street' => $user->street ?? '',
                'pincode' => $user->pincode ?? '',
                'apikey' => $user->apikey ?? '',
                'referral_code' => $user->referral_code ?? '',
                'friends_code' => $user->friends_code ?? '',
                'fcm_id' => array_values($fcm_ids_array) ?? '',
                'latitude' => $user->latitude ?? '',
                'longitude' => $user->longitude ?? '',
                'created_at' => $user->created_at ?? '',
                'type' => $user->type ?? '',
                'front_licence_image' => !empty($user->front_licence_image) ? getMediaImageUrl($user->front_licence_image, 'DELIVERY_BOY_IMG_PATH') : '',
                'back_licence_image' => !empty($user->back_licence_image) ? getMediaImageUrl($user->back_licence_image, 'DELIVERY_BOY_IMG_PATH') : '',
            ];

            if ($user->role_id == 3) {

                unset($user->password);

                return response()->json([
                    'error' => false,
                    'message' => 'Data retrived successfully',
                    'language_message_key' => 'data_retrieved_successfully',
                    'data' => isset($userData) ? $userData : [],
                ]);
            }
        }
    }

    public function get_orders(Request $request)
    {
        /*
            parcel_type : combo_order/regular_order
            active_status: received  {received,delivered,cancelled,processed,returned}     // optional
            limit:25            // { default - 25 } optional
            offset:0            // { default - 0 } optional
            sort: id / date_added // { default - id } optional
            order:DESC/ASC      // { default - DESC } optional
        */
        $validator = Validator::make($request->all(), [
            'limit' => 'numeric',
            'offset' => 'numeric',
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
            }
            $limit = $request->input('limit', 25);
            $offset = $request->input('offset', 0);
            $sort = $request->input('sort', 'o.id');
            $order = $request->input('order', 'DESC');
            $activeStatus = $request->input('active_status');
            $parcel_type = $request->input('parcel_type');

            $delivery_boy_id = auth::id();

            $res = viewAllParcels('', '', '', $offset, $limit, $order, '1', $delivery_boy_id, $activeStatus, '', $parcel_type, 1);

            if (isset($res->original) && empty($res->original['data'])) {
                $response['error'] = true;
                $response['message'] = "Parcel Not Found.";
                $response['data'] = [];
                return response()->json($response);
            }
            $res = !$res->isempty() ? $res->original : "";

            foreach ($res['data'] as $key => $parcel) {
                $subtotal = 0;
                foreach ($parcel['items'] as $items) {


                    $subtotal = $items['unit_price'] * $items['quantity'];
                }
                $res['data'][$key]['total'] = $subtotal;
                $delivery_charge = $res['data'][$key]['delivery_charge'];
                $promo_discount = $res['data'][$key]['promo_discount'];
                $tax_amount = $res['data'][$key]['tax_amount'];
                $final_total = $subtotal + $delivery_charge + $tax_amount - $promo_discount;
                $res['data'][$key]['sub_total'] = (string) intval($subtotal);
                $res['data'][$key]['final_total'] = (string) intval($final_total);
                $res['data'][$key]['total_payable'] = (string) intval($final_total);
            }

            if (!empty($res)) {
                $response['error'] = false;
                $response['message'] = 'Data retrieved successfully';
                $response['language_message_key'] = 'data_retrieved_successfully';
                $response['total'] = $res['total'];
                $response['awaiting'] = strval(deliveryBoyOrdersCount("awaiting", $user_id, 'parcels'));
                $response['received'] = strval(deliveryBoyOrdersCount("received", $user_id, 'parcels'));
                $response['processed'] = strval(deliveryBoyOrdersCount("processed", $user_id, 'parcels'));
                $response['shipped'] = strval(deliveryBoyOrdersCount("shipped", $user_id, 'parcels'));
                $response['delivered'] = strval(deliveryBoyOrdersCount("delivered", $user_id, 'parcels'));
                $response['cancelled'] = strval(deliveryBoyOrdersCount("cancelled", $user_id, 'parcels'));
                $response['returned'] = strval(deliveryBoyOrdersCount("returned", $user_id, 'parcels'));
                $response['data'] = $res['data'];
            } else {
                $response['error'] = true;
                $response['message'] = 'Order Does Not Exists';
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
    public function get_fund_transfers(Request $request, CashCollectionController $cashCollectionController)
    {
        /*
            active_status: received  {received,delivered,cancelled,processed,returned}     // optional
            limit:25            // { default - 25 } optional
            offset:0            // { default - 0 } optional
            sort: id / date_added // { default - id } optional
            order:DESC/ASC      // { default - DESC } optional
        */
        $validator = Validator::make($request->all(), [
            'limit' => 'numeric',
            'offset' => 'numeric',
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
            }
            $limit = $request->input('limit', 25);
            $offset = $request->input('offset', 0);
            $sort = $request->input('sort', 'o.id');
            $order = $request->input('order', 'DESC');

            $fundTransferData = $cashCollectionController->fund_transfers_list($request);
            if ($fundTransferData->original['total'] != 0) {
                $response['error'] = false;
                $response['message'] = 'Data retrieved successfully';
                $response['language_message_key'] = 'data_retrieved_successfully';
                $response['total'] = strval($fundTransferData->original['total']);
                $response['data'] = $fundTransferData->original['rows'];
            } else {
                $response['error'] = true;
                $response['message'] = 'No fund transfer has been made yet';
                $response['language_message_key'] = 'data_does_not_exists';
                $response['total'] = strval($fundTransferData->original['total']);
                $response['data'] = $fundTransferData->original['rows'];
            }
            return response()->json($response);
        }
    }
    public function update_fcm(Request $request)
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            'user_id' => 'sometimes|numeric|exists:users,id',
            'fcm_id' => 'required',
            'is_delete' => 'sometimes|boolean', // New parameter for deletion
        ]);

        if ($validator->fails()) {
            $response = [
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            ];
            return response()->json($response);
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

    public function update_user(Request $request, Delivery_boyController $deliveryBoyController)
    {
        /*
            user_id:34
            username:hiten
            mobile:7852347890 {optional}
            email:amangoswami@gmail.com	{optional}
            //optional parameters
            password:12345 {optional}
            confirm_password:345234 {optional}
            front_licence_image : FILE {optional}
            back_licence_image : FILE {optional}
            profile_image : FILE {optional}
        */

        if (auth()->check()) {
            $user_id = auth()->user()->id;
        }
        $data = $deliveryBoyController->update($request, $user_id);
        $language_code = $request->attributes->get('language_code');

        $zone_ids = explode(',', $data->original['data']->serviceable_zones);
        $zones = Zone::whereIn('id', $zone_ids)->pluck('name')->toArray();

        $translated_zones = array_map(function ($zoneJson) use ($language_code) {
            $decoded = json_decode($zoneJson, true);
            return $decoded[$language_code] ?? '';
        }, $zones);

        $zones = implode(', ', $translated_zones);

        $data->original['data']->zones = $zones ?? '';

        $data->original['data']['front_licence_image'] = !empty($data->original['data']['front_licence_image']) ? getMediaImageUrl($data->original['data']['front_licence_image'], 'DELIVERY_BOY_IMG_PATH') : '';
        $data->original['data']['back_licence_image'] = !empty($data->original['data']['back_licence_image']) ? getMediaImageUrl($data->original['data']['back_licence_image'], 'DELIVERY_BOY_IMG_PATH') : '';
        $data->original['data']['image'] = !empty($data->original['data']['image']) ? getMediaImageUrl($data->original['data']['image'], 'DELIVERY_BOY_IMG_PATH') : '';
        $response['error'] = $data->original['error'];
        $response['message'] = $data->original['message'];
        $response['data'] = $data->original['data'] ?? [];

        return response()->json($response);
    }

    public function get_notifications(Request $request, NotificationController $NotificationController)
    {
        /*
            limit:25            // { default - 25 } optional
            offset:0            // { default - 0 } optional
            sort: id / date_added // { default - id } optional
            order:DESC/ASC      // { default - DESC } optional
        */
        $validator = Validator::make($request->all(), [
            'sort' => 'nullable|sometimes|string',
            'limit' => 'nullable|sometimes|numeric',
            'offset' => 'nullable|sometimes|numeric',
            'order' => 'nullable|sometimes|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => true,

                'message' => $validator->errors()->all(),
                'data' => []
            ]);
        }
        $limit = $request->input('limit', 25);
        $offset = $request->input('offset', 0);
        $order = $request->input('order', 'DESC');
        $sort = $request->input('sort', 'id');

        $res = $NotificationController->get_notifications($offset, $limit, $sort, $order);

        return response()->json([
            'error' => false,
            'message' => 'Notification Retrieved Successfully',
            'language_message_key' => 'notification_retrieved_successfully',
            'total' => $res['total'],
            'data' => $res['data'],
        ]);
    }

    public function verify_user(Request $request)
    {
        /* Parameters to be passed
            mobile: 9874565478
            email: test@gmail.com // { optional }
        */
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|numeric',
            'email' => 'sometimes|nullable|email',
        ]);

        if ($validator->fails()) {
            $response = [
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            ];
            return response()->json($response);
        } else {
            $mobile = $request->input('mobile');
            $email = $request->input('email');

            if (isset($mobile) && isExist(['mobile' => $mobile], 'users')) {
                $user_id = fetchDetails('users', ['mobile' => $mobile], 'role_id')[0];

                //Check if this mobile no. is registered as a delivery boy or not.
                if ($user_id->role_id != 3) {
                    $response = [
                        'error' => true,
                        'message' => 'Mobile number / email could not be found!',
                        'language_message_key' => 'mobile_or_email_not_found',
                        'data' => [],
                    ];
                } else {
                    $response = [
                        'error' => true,
                        'message' => 'Mobile is already registered.Please login again !',
                        'language_message_key' => 'mobile_already_registered_login_again',
                        'data' => [],
                    ];
                }
                return response()->json($response);
            }

            if (isset($email) && isExist(['email' => $email], 'users')) {
                $response = [
                    'error' => true,
                    'message' => 'Email is already registered.Please login again !',
                    'language_message_key' => 'email_already_registered_login_again',

                    'data' => [],
                ];
                return response()->json($response);
            }
            $response = [
                'error' => true,
                'message' => 'Mobile number / email could not be found!',
                'language_message_key' => 'mobile_or_email_not_found',
                'data' => [],
            ];
            return response()->json($response);
        }
    }

    public function get_settings(Request $request)
    {
        /*
            type : delivery_boy_privacy_policy / delivery_boy_terms_conditions
        */

        // Validate the 'type' parameter
        $validator = Validator::make($request->all(), [
            'type' => 'sometimes|in:delivery_boy_privacy_policy,delivery_boy_terms_and_conditions',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            ]);
        }

        // Fetch system settings
        $settings = json_decode(getSettings('system_settings', true), true);
        $contact_us = json_decode(getSettings('contact_us', true), true);
        $about_us = json_decode(getSettings('about_us', true), true);
        // Default settings to unset
        $fields_to_unset = [
            'enable_cart_button_on_product_list_view',
            'expand_product_image',
            'tax_name',
            'tax_number',
            'google',
            'facebook',
            'apple',
            'refer_and_earn_status',
            'minimum_refer_and_earn_amount',
            'minimum_refer_and_earn_bonus',
            'refer_and_earn_method',
            'max_refer_and_earn_amount',
            'number_of_times_bonus_given_to_customer',
            'wallet_balance_status',
            'wallet_balance_amount',
            'authentication_method',
            'supported_locals',
            'store_currency',
            'decimal_point',
            'single_seller_order_system',
            'customer_app_maintenance_status',
            'seller_app_maintenance_status',
            'message_for_customer_app',
            'message_for_seller_app',
            'sidebar_color',
            'sidebar_type',
            'navbar_fixed',
            'theme_mode',
            'current_version_of_ios_app',
            'current_version_of_android_app',
            'current_version_of_android_app_for_seller',
            'current_version_of_ios_app_for_seller',
            'storage_type',
            'minimum_cart_amount',
            'maximum_item_allowed_in_cart',
            'low_stock_limit',
            'max_days_to_return_item',
        ];

        // Unset unnecessary fields
        foreach ($fields_to_unset as $field) {
            unset($settings[$field]);
        }

        // Get currency symbol
        $currencyDetails = fetchDetails('currencies', ['is_default' => 1], 'symbol');
        $currency = !empty($currencyDetails) ? $currencyDetails[0]->symbol : '';

        // Handle case when 'type' is not passed
        $type = $request->input('type', '');
        if (empty($type)) {
            $terms_and_conditions = json_decode(getSettings('delivery_boy_terms_and_conditions'), true);
            $privacy_policy = json_decode(getSettings('delivery_boy_privacy_policy'), true);

            $data = [
                'delivery_boy_terms_and_conditions' => $terms_and_conditions['delivery_boy_terms_and_conditions'] ?? '',
                'delivery_boy_privacy_policy' => $privacy_policy['delivery_boy_privacy_policy'] ?? '',
                'contact_us' => $contact_us['contact_us'] ?? '',
                'about_us' => $about_us['about_us'] ?? '',
            ];

            $this->formatMediaUrls($settings);

            return response()->json([
                'error' => false,
                'message' => 'Settings retrieved successfully',
                'language_message_key' => 'settings_retrieved_successfully',
                'data' => $data,
                'currency' => $currency,
                // 'delivery_boy_app_maintenance_status' => $settings['delivery_boy_app_maintenance_status'],
                // 'message_for_delivery_boy_app' => $settings['message_for_delivery_boy_app'],
                'system_settings' => $settings,

            ]);
        }

        // Handle specific setting based on 'type'
        $allowed_settings = ['delivery_boy_terms_and_conditions', 'delivery_boy_privacy_policy', 'currency'];
        if (!in_array($type, $allowed_settings)) {
            return response()->json([
                'error' => false,
                'message' => 'Currency',
                'data' => [],
            ]);
        }

        $settings_res = json_decode(getSettings($type), true);
        if (!empty($settings_res)) {
            $data = [$type => [$settings_res[$type] ?? '']];

            $this->formatMediaUrls($settings);

            return response()->json([
                'error' => false,
                'message' => 'Settings retrieved successfully',
                'language_message_key' => 'settings_retrieved_successfully',
                'data' => $data,
                'currency' => $currency,
                'delivery_boy_app_maintenance_status' => $settings['delivery_boy_app_maintenance_status'],
                'message_for_delivery_boy_app' => $settings['message_for_delivery_boy_app'],
                'system_settings' => $settings,

            ]);
        }

        return response()->json([
            'error' => true,
            'message' => 'Settings Not Found',
            'language_message_key' => 'settings_not_found',
            'data' => [],
        ]);
    }

    // Helper function to format media URLs and set null fields to empty strings
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


    public function send_withdrawal_request(Request $request, PaymentRequestController $paymentRequest)
    {
        /*
            payment_address: 12343535
            amount: 56
        */
        $validator = Validator::make($request->all(), [
            'payment_address' => 'required',
            'amount' => 'required|numeric|min:0',
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
            }

            $request['user_id'] = $user_id;
            $data = $paymentRequest->add_withdrawal_request($request, true);
            $response['error'] = $data->original['error'];
            $response['message'] = isset($data->original['message']) ? $data->original['message'] : $data->original['error_message'];
            $response['amount'] = $data->original['amount'];
            $response['data'] = $data->original['data'];
            return response()->json($response);
        }
    }

    public function get_withdrawal_request(Request $request, PaymentRequestController $paymentRequest)
    {
        /*
           sort:               // { payment_requests.id } optional
           order:DESC/ASC      // { default - ASC } optional
           search:value        // {optional}
           offset: 0 {optional}
           limit: 10 {optional}
       */
        $validator = Validator::make($request->all(), [
            'sort' => 'string',
            'limit' => 'numeric',
            'offset' => 'numeric',
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
            }

            $data = $paymentRequest->get_payment_request_list($request, $user_id);

            $response['error'] = empty($data->original['rows']) ? true : false;
            $response['message'] = empty($data->original['rows']) ? 'Withdrawal Request does not exist' : 'Withdrawal Request Retrieved Successfully';
            $response['language_message_key'] = empty($data->original['rows']) ? 'withdrawal_request_does_not_exist' : 'withdrawal_request_retrieved_successfully';
            $response['total'] = $data->original['total'];
            $response['data'] = $data->original['rows'];

            return response()->json($response);
        }
    }

    public function update_order_item_status(Request $request, OrderController $orderController)
    {
        /*
            id:1
            status : received / processed / shipped / delivered / cancelled / returned
            delivery_boy_id: 15
            otp:value      //{required when status is delivered}
         */
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'otp' => 'nullable|numeric',
            'status' => [
                'required',
                Rule::in(['received', 'processed', 'shipped', 'delivered', 'cancelled', 'returned']),
            ],
        ]);

        if ($validator->fails()) {
            $response = [
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            ];
            return response()->json($response);
        } else {

            $request['table'] = 'parcels';
            $res = $orderController->update_order_item_status($request);
            return response()->json($res->original);
        }
    }

    public function get_delivery_boy_cash_collection(Request $request, CashCollectionController $cashCollectionController)
    {
        /*
            status:             // {delivery_boy_cash (delivery boy collected) | delivery_boy_cash_collection (admin collected)}
            limit:25            // { default - 25 } optional
            offset:0            // { default - 0 } optional
            sort:               // { id } optional
            order:DESC/ASC      // { default - DESC } optional
            search:value        // {optional}
        */
        $validator = Validator::make($request->all(), [
            'sort' => 'string',
            'limit' => 'numeric',
            'offset' => 'numeric',
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
            }
            $limit = $request->input('limit', 25);
            $offset = $request->input('offset', 0);
            $sort = $request->input('sort', 'transactions.id');
            $order = $request->input('order', 'DESC');
            $search = $request->input('search', '');
            $filters['delivery_boy_id'] = $user_id;
            $filters['status'] = (isset($request['status']) && !empty(trim($request['status']))) ? $request['status'] : '';

            $data = $cashCollectionController->get_delivery_boy_cash_collection($limit, $offset, $sort, $order, $search, $filters);

            if (isset($data['data']) && !empty($data['data'])) {
                foreach ($data['data'] as $row) {



                    $tmpRow['id'] = $row['id'];
                    $tmpRow['name'] = $row['name'];
                    $tmpRow['mobile'] = $row['mobile'];
                    $tmpRow['order_id'] = $row['order_id'];
                    $tmpRow['cash_received'] = $row['cash_received'];
                    $tmpRow['type'] = $row['type'];
                    $tmpRow['amount'] = $row['amount'];
                    $tmpRow['message'] = $row['message'];
                    $tmpRow['transaction_date'] = $row['transaction_date'];
                    $tmpRow['date'] = $row['date'];

                    if (isset($row['order_id']) && !empty($row['order_id']) && $row['order_id'] != "") {

                        $order_data = fetchOrders($row['id']);


                        $tmpRow['order_details'] = isset($order_data['order_data'][0]) ? $order_data['order_data'][0] : "";
                    } else {
                        $tmpRow['order_details'] = "";
                    }
                    $rows[] = $tmpRow;
                }
                if ($data['error'] == false) {
                    $data['data'] = $rows;
                } else {
                    $data['data'] = array();
                }
            }

            return response()->json($data);
        }
    }

    public function delete_delivery_boy(Request $request)
    {
        /*
            mobile:9874563214
            password:12345695
        */

        $validator = Validator::make($request->all(), [
            'mobile' => 'required|numeric',
            'email' => 'sometimes|nullable|email',
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
            }


            $user_data = fetchDetails('users', ['id' => $user_id, 'mobile' => $request['mobile']], ['id', 'username', 'password', 'active', 'mobile']);
            if ($user_data) {
                if (auth()->check()) {
                    $user = Auth::user();

                    if ($user['role_id'] == '3') {
                        deleteDetails(['id' => $user_id], 'users');

                        //delete delivery boy's images
                        $frontLicenceImagePath = str_replace('\\', '/', public_path(config('constants.' . 'MEDIA_PATH') . $user['front_licence_image']));
                        $backLicenceImagePath = str_replace('\\', '/', public_path(config('constants.' . 'MEDIA_PATH') . $user['back_licence_image']));


                        if (File::exists($frontLicenceImagePath)) {
                            unlink($frontLicenceImagePath);
                        }

                        if (File::exists($backLicenceImagePath)) {
                            unlink($backLicenceImagePath);
                        }

                        $response['error'] = false;
                        $response['message'] = 'Delivery Boy Deleted Successfully';
                        $response['language_message_key'] = 'delivery_boy_deleted_successfully';
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
                $response['message'] = 'User Not Found';
            }
        }
        return response()->json($response);
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
        $validator = Validator::make($request->all(), [
            'language_code' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->all(),
                'data' => [],
            ]);
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
    public function get_wallet_transaction()
    {
        if (auth()->check()) {
            $user_id = auth()->user()->id;
        }
        $offset = request()->input('offset', 0);
        $limit = request()->input('limit', 10);
        $sort = request()->input('sort', 'id');
        $order = request()->input('order', 'DESC');

        $transactionsQuery = DB::table('transactions')
            ->where('transactions.user_id', $user_id)
            ->whereIn('transactions.type', ['credit', 'debit']);

        if (request()->has('search') && trim(request()->input('search')) !== '') {
            $search = trim(request()->input('search'));
            $transactionsQuery->where(function ($query) use ($search) {
                $query->where('transactions.id', $search)
                    ->orWhere('transactions.amount', $search)
                    ->orWhere('transactions.created_at', $search)
                    ->orWhere('transactions.type', $search)
                    ->orWhere('transactions.status', $search)
                    ->orWhere('transactions.txn_id', $search);
            });
        }

        $totalQuery = clone $transactionsQuery;
        $total = $totalQuery->count();

        $txn_search_res = $transactionsQuery->select('transactions.*')
            ->orderBy($sort, $order)
            ->skip($offset)
            ->take($limit)
            ->get();

        $formattedTransactions = $txn_search_res->map(function ($row) {
            return [
                'id' => $row->id,
                'type' => $row->type,
                'payu_txn_id' => $row->payu_txn_id,
                'amount' => $row->amount,
                'status' => $row->status,
                'message' => $row->message,
                'created_at' => date('Y-m-d', strtotime($row->created_at)),
            ];
        });

        $response['error'] = $total == 0 ? true : false;
        $response['message'] = $total == 0 ? 'No Details Found' : 'Wallet Transaction Retrived Successfully';
        $response['language_message_key'] = $total == 0 ? 'no_data_found' : 'data_retrived_successfully';
        $response['total'] = $total;
        $response['data'] = $formattedTransactions;

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
        $validator = Validator::make($request->all(), [
            'sort' => 'string',
            'limit' => 'numeric',
            'offset' => 'numeric',
        ]);

        if ($validator->fails()) {
            $response = [
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            ];
            return response()->json($response);
        } else {
            $city_data = $areaController->city_list($request);

            if ($city_data) {
                $response['error'] = false;
                $response['message'] = 'Cities retrieved successfully!';
                $response['language_message_key'] = 'cities_retrived_successfully';
                $response['total'] = $city_data->original['total'];
                $response['data'] = $city_data->original['rows'];
            } else {
                $response['error'] = true;
                $response['message'] = 'Data Does Not Exists  !';
                $response['language_message_key'] = 'data_does_not_exists';
                $response['data'] = array();
            }
            return response()->json($response);
        }
    }
    public function get_zones(Request $request)
    {
        $language_code = $request->attributes->get('language_code');
        // dd($language_code);
        return getZones($request, $language_code);
    }
    public function get_returned_order_items(Request $request)
    {

        /*
           sort:               // { oi.id } optional
           order:DESC/ASC      // { default - DESC } optional
           search:value        // {optional}
           offset: 0 {optional}
           limit: 10 {optional}
       */

        if (auth()->check()) {
            $delivery_boy_id = auth()->user()->id;
        }
        $delivery_boy_id = Auth::id();
        $language_code = $request->attributes->get('language_code');

        $res = fetchOrderItems($request->input('order_item_id', ''), '', ['return_pickedup', 'return_request_approved', 'returned'], $delivery_boy_id, $request->input('limit', 10), $request->input('offset', 0), $request->input('sort', 'oi.id'), $request->input('order', 'DESC'), '', '', $request->input('search', ''), $request->input('seller_id'), '', '');

        $data = $res;
        if (!empty($data)) {
            return response()->json([
                'error' => false,
                'message' => 'Orders retrieved successfully!',
                'language_message_key' => 'orders_retrived_successfully',
                'total' => $data['total'],
                'data' => $data['order_data'],
            ]);
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Data Does Not Exists!',
                'language_message_key' => 'data_does_not_exists',
                'total' => '',
                'data' => [],
            ]);
        }
    }
    public function update_returned_order_item_status(Request $request)
    {

        /*
           order_item_id: 1
           status:  return_pickedup
       */

        $validator = Validator::make($request->all(), [
            'order_item_id' => 'required|numeric',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'error' => true,
                'message' => $validator->errors()->all(),
                'code' => 102,
            ];
            return response()->json($response);
        }
        $status = $request->status ?? "";
        $order_item_id = $request->order_item_id ?? "";
        if ($status !== 'return_pickedup') {
            return response()->json([
                'error' => true,
                'message' =>
                labels('admin_labels.invalid_status_passed', 'Invalid Status Passed.'),
                'data' => [],
            ]);
        }
        $current_status = fetchDetails('order_items', ['id' => $order_item_id], 'status');
        $current_status = isset($current_status) && !empty($current_status) ? $current_status[0]->status : "";
        $current_status = json_decode($current_status, true);

        if (!is_array($current_status)) {
            $current_status = [];
        }
        $last_status = end($current_status);
        if ($last_status[0] == 'returned') {
            return response()->json([
                'error' => true,
                'message' =>
                labels('admin_labels.status_is_already_returned_you_can_not_set_it_as_pickedup', 'Status is already returned you can not set it as pickedup.'),
                'data' => [],
            ]);
        }
        if ($last_status[0] == 'return_pickedup') {
            return response()->json([
                'error' => true,
                'message' =>
                labels('admin_labels.status_already_updated', 'Status already updated.'),
                'data' => [],
            ]);
        }
        $current_time = date("Y-m-d H:i:s");
        $new_entry = [$status, $current_time];
        $current_status[] = $new_entry;
        $updated_status = json_encode($current_status);

        $update_data = [
            'active_status' => $status,
            'status' => $updated_status
        ];
        $result = updateOrderItemStatus($order_item_id, $update_data);
        if ($result) {
            $order_item_data = getReturnOrderItemsList(
                '',
                $request->input('search', ''),
                $request->input('offset', 0),
                $request->input('limit', 10),
                $request->input('sort', 'oi.id'),
                $request->input('order', 'DESC'),
                $request->input('seller_id'),
                $request->input('fromApp', '1'),
                $request->input('order_item_id', $order_item_id),
                $request->input('isPrint', '1')
            );

            return response()->json([
                'error' => false,
                'message' =>
                labels('admin_labels.status_updated_successfully', 'Status Updated Successfully'),
                'data' => !empty($order_item_data) ? $order_item_data[0] : [],
            ]);
        } else {
            return response()->json([
                'error' => true,
                'message' =>
                labels('admin_labels.something_went_wrong', 'Something went wrong'),
                'data' => [],
            ]);
        }
    }

    public function reset_password(Request $request)
    {
        /* Parameters to be passed
            mobile_no:7894561235
        */
        $validator = Validator::make($request->all(), [
            'mobile_no' => 'required|numeric|digits_between:1,16',
        ], [
            'mobile_no.required' => 'Mobile Number is required.',
            'mobile_no.numeric' => 'Mobile Number must be numeric.',
            'mobile_no.digits_between' => 'Mobile Number must be between 1 and 16 digits.',
            'new.required' => 'New Password is required.',
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
                    'message' => 'User does not exist!',
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
}
