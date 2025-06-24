<?php

namespace App\Http\Controllers\Delivery_boy;

use App\Http\Controllers\Controller;
use App\Models\OrderItems;
use App\Models\UserFcm;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index()
    {
        $currencyDetails = fetchDetails('currencies', ['is_default' => 1], 'symbol');
        $currency = !empty($currencyDetails) ? $currencyDetails[0]->symbol : '';
        return view('delivery_boy.pages.tables.manage_orders', compact('currency'));
    }

    public function order_item_list()
    {
        $offset = request()->input('search', '') || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = request('limit', 10);
        $sort = 'oi.id';
        $order = request('order', 'DESC');
        $search = trim(request()->input('search'));
        $startDate = request()->input('start_date');
        $endDate = request()->input('end_date');
        $delivery_boy_id = Auth::id(); // Assuming this is the authenticated delivery boy's ID

        // Base query to fetch order items with necessary joins
        $query = DB::table('order_items as oi')
            ->leftJoin('users as u', 'u.id', '=', 'oi.delivery_boy_id')
            ->leftJoin('seller_data as sd', 'sd.id', '=', 'oi.seller_id')
            ->leftJoin('users as us', 'us.id', '=', 'sd.user_id')
            ->leftJoin('orders as o', 'o.id', '=', 'oi.order_id')
            ->leftJoin('product_variants as v', 'oi.product_variant_id', '=', 'v.id')
            ->leftJoin('products as p', 'v.product_id', '=', 'p.id')
            ->leftJoin('users as un', 'o.user_id', '=', 'un.id')
            ->select(
                'o.id as order_id',
                'oi.product_name as product_name',
                'oi.variant_name as variant_name',
                'oi.id as order_item_id',
                'o.*',
                'oi.*',
                'u.username as delivery_boy',
                'un.username as username',
                'us.username as seller_name',
                'p.type',
                'p.download_allowed'
            );

        // Apply filtering conditions
        if ($startDate && $endDate) {
            $query->whereDate('oi.created_at', '>=', $startDate)
                ->whereDate('oi.created_at', '<=', $endDate);
        }

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('un.username', 'like', '%' . $search . '%')
                    ->orWhere('u.username', 'like', '%' . $search . '%')
                    ->orWhere('us.username', 'like', '%' . $search . '%')
                    ->orWhere('un.email', 'like', '%' . $search . '%')
                    ->orWhere('oi.id', $search)
                    ->orWhere('o.mobile', 'like', '%' . $search . '%')
                    ->orWhere('o.address', 'like', '%' . $search . '%')
                    ->orWhere('o.payment_method', 'like', '%' . $search . '%')
                    ->orWhere('oi.sub_total', $search)
                    ->orWhere('o.delivery_time', 'like', '%' . $search . '%')
                    ->orWhere('oi.active_status', 'like', '%' . $search . '%')
                    ->orWhereDate('oi.created_at', $search);
            });
        }

        if ($delivery_boy_id) {
            $query->where('oi.delivery_boy_id', $delivery_boy_id)
                ->where('oi.active_status', '!=', 'awaiting');
        }

        // Count total matching records
        $total = $query->count();

        // Fetch paginated and sorted results
        $rows = $query->orderBy($sort, $order)
            ->offset($offset)
            ->limit($limit)
            ->get();

        // Format rows for response
        $formattedRows = [];

        foreach ($rows as $index => $row) {
            // Customize active_status label based on the status value
            $active_status = '<label class="badge ';
            switch ($row->active_status) {
                case 'awaiting':
                    $active_status .= 'bg-secondary';
                    break;
                case 'received':
                    $active_status .= 'bg-primary';
                    break;
                case 'processed':
                    $active_status .= 'bg-info';
                    break;
                case 'shipped':
                    $active_status .= 'bg-warning';
                    break;
                case 'delivered':
                    $active_status .= 'bg-success';
                    break;
                case 'returned':
                case 'cancelled':
                    $active_status .= 'bg-danger';
                    break;
                case 'return_request_decline':
                    $active_status .= 'bg-danger';
                    $row->active_status = 'Return Declined';
                    break;
                case 'return_request_approved':
                    $active_status .= 'bg-success';
                    $row->active_status = 'Return Approved';
                    break;
                case 'return_request_pending':
                    $active_status .= 'bg-secondary';
                    $row->active_status = 'Return Requested';
                    break;
                default:
                    $active_status .= 'bg-light text-dark'; // Default label for unknown status
            }
            $active_status .= '">' . $row->active_status . '</label>';

            // Prepare the formatted row for response
            $formattedRow = [
                'id' => $offset + $index + 1, // Sequential count starting from 1
                'order_item_id' => $row->order_item_id, // Actual order_item_id
                'order_id' => $row->order_id,
                'user_id' => $row->user_id,
                'seller_id' => $row->seller_id,
                'notes' => isset($row->notes) ? $row->notes : '',
                'username' => $row->username,
                'seller_name' => $row->seller_name,
                'is_credited' => $row->is_credited ? '<label class="badge bg-success">Credited</label>' : '<label class="badge bg-danger">Not Credited</label>',
                'product_name' => $row->product_name . (isset($row->variant_name) && !empty($row->variant_name) ? ' (' . $row->variant_name . ')' : ''),
                'mobile' => $row->mobile,
                'sub_total' => formateCurrency(formatePriceDecimal($row->sub_total)), // Format currency
                'quantity' => $row->quantity,
                'delivery_boy' => $row->delivery_boy,
                'payment_method' => $row->payment_method,
                'delivery_boy_id' => $row->delivery_boy_id,
                'product_variant_id' => $row->product_variant_id,
                'delivery_date' => $row->delivery_date,
                'delivery_time' => $row->delivery_time,
                'courier_agency' => isset($row->courier_agency) ? $row->courier_agency : '',
                'tracking_id' => isset($row->tracking_id) ? $row->tracking_id : '',
                'url' => isset($row->url) ? $row->url : '',
                'active_status' => $active_status,
                'date_added' => Carbon::parse($row->created_at)->format('d-m-Y'),
                'operate' => '<div class="dropdown bootstrap-table-dropdown">
                    <a href="#" class="text-dark" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-ellipsis-v"></i>
                    </a>
                    <div class="dropdown-menu table_dropdown order_action_dropdown" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item" href="' . route('delivery_boy.orders.edit', $row->order_id) . '"><i class="bx bx-pencil"></i> Edit</a>
                    </div>
                </div>'
            ];

            // Add the formatted row to the array
            $formattedRows[] = $formattedRow;
        }

        // Return response with formatted rows and total count
        return response()->json([
            'rows' => $formattedRows,
            'total' => $total,
        ]);
    }
    public function view_parcels(Request $request, $orderId = null, $sellerId = null, $deliveryBoyId = null)
    {
        $language_code = get_language_code();
        $delivery_boy_id = auth::id();
        $parcelData = ViewParcel($request, '', '', $delivery_boy_id, $language_code);
        return response()->json($parcelData);
    }
    public function edit(Request $request, $id)
    {

        $parcel_id = $request->parcel_id ?? "";

        $parcel_details = fetchDetails('parcels', ['id' => $parcel_id], ['store_id']);

        $store_id = isset($parcel_details) && !empty($parcel_details) ? $parcel_details[0]->store_id : "";

        $limit = 25;
        $offset =  0;
        $order = 'DESC';
        $delivery_boy_id = auth::id();

        $parcel_details = viewAllParcels('', $parcel_id, '', $offset, $limit, $order, 1, '', '', $store_id);


        if (isset($parcel_details->original) && empty($parcel_details->original['data'])) {
            $response['error'] = true;
            $response['message'] = "Parcel Not Found.";
            $response['data'] = [];
            return response()->json($response);
        }


        if (!empty($parcel_details->original)) {

            $parcel_items = $parcel_details->original['data'];

            $order_items_id = [];

            foreach ($parcel_items as $item) {
                $order_items_id = [...$order_items_id, ...array_map(function ($items) {
                    return ($items["order_item_id"]);
                }, $item["items"])];
            }


            $order_items = fetchOrderItems($order_items_id, null, null, null, null, null, 'oi.id', $order, null, null, null, null, $id, $store_id);


            if (isset($order_items['order_data']) && empty($order_items['order_data'])) {
                $response['error'] = true;
                $response['message'] = "Order items Not Found.";
                $response['data'] = [];
                return response()->json($response);
            }
            $order_items = $order_items['order_data'];

            if ($delivery_boy_id == $order_items[0]->delivery_boy_id && isset($id) && !empty($id) && !empty($parcel_items) && is_numeric($id)) {
                $items = [];
                $total = 0;
                foreach ($order_items as $row) {

                    $multipleWhere = ['seller_id' => $row->seller_id, 'order_id' => $row->id];
                    $orderChargeData = DB::table('order_charges')->where($multipleWhere)->get();
                    $updated_username = isset($row->updated_by) && !empty($row->updated_by) && $row->updated_by != 0 ? fetchDetails('users', ['id' => $row->updated_by], 'username')[0]->username : '';
                    $address_number = isset($row->address_id) && !empty($row->address_id) && $row->address_id != 0
                        ? (fetchDetails('addresses', ['id' => $row->address_id], 'mobile') && isset(fetchDetails('addresses', ['id' => $row->address_id], 'mobile')[0]->mobile)
                            ? fetchDetails('addresses', ['id' => $row->address_id], 'mobile')[0]->mobile
                            : '')
                        : '';
                    $address = isset($row->address_id) && !empty($row->address_id) && $row->address_id != 0
                        ? (fetchDetails('addresses', ['id' => $row->address_id], '*') && isset(fetchDetails('addresses', ['id' => $row->address_id], 'address')[0]->address)
                            ? fetchDetails('addresses', ['id' => $row->address_id], '*')
                            : '')
                        : '';
                    if ($address) {
                        $addressDetails = $address[0];
                        $fullAddress = trim(
                            (isset($addressDetails->name) ? $addressDetails->name : '') . ', ' .
                                (isset($addressDetails->mobile) ? $addressDetails->mobile : '') . ', ' .
                                (isset($addressDetails->address) ? $addressDetails->address : '') . ', ' .
                                (isset($addressDetails->city) ? $addressDetails->city : '') . ', ' .
                                (isset($addressDetails->state) ? $addressDetails->state : '') . ' - ' .
                                (isset($addressDetails->pincode) ? $addressDetails->pincode : '')
                        );
                    }
                    $deliver_by = isset($row->delivery_boy_id) && !empty($row->delivery_boy_id) && $row->delivery_boy_id != 0 ? fetchDetails('users', ['id' => $row->delivery_boy_id], 'username')[0]->username : '';
                    $temp = [
                        'id' => $row->id,
                        'item_otp' => $row->otp,
                        'product_id' => $row->product_id,
                        'product_variant_id' => $row->product_variant_id,
                        'product_type' => $row->product_type,
                        'wallet_balance' => $row->wallet_balance,
                        'pname' => isset($row->pname) && ($row->pname != null) ? $row->pname : $row->product_name,
                        'quantity' => $row->quantity,
                        'is_cancelable' => $row->is_cancelable,
                        'is_attachment_required' => $row->is_attachment_required,
                        'is_returnable' => $row->is_returnable,
                        'tax_amount' => $row->tax_amount,
                        'discounted_price' => $row->discounted_price,
                        'price' => $row->price,
                        'item_subtotal' => $row->sub_total,
                        'updated_by' => $updated_username,
                        'deliver_by' => $deliver_by,
                        'seller_delivery_charge' => $orderChargeData->isEmpty() ? 0 : $orderChargeData->first()->delivery_charge,
                        'seller_promo_discount' => $orderChargeData->isEmpty() ? 0 : $orderChargeData->first()->promo_discount,
                        'active_status' => $row->active_status,
                        'product_image' => $row->image,
                        'product_variants' => getVariantsValuesById($row->product_variant_id),
                        'product_id' => $row->product_id,
                        'pickup_location' => $row->pickup_location,
                        'seller_otp' => $orderChargeData->isEmpty() ? 0 : $orderChargeData[0]->otp,
                        'is_sent' => $row->is_sent,
                        'seller_id' => $row->seller_id,
                        'download_allowed' => $row->download_allowed,
                        'product_slug' => $row->product_slug,
                        'sku' => isset($row->product_sku) && !empty($row->product_sku) ? $row->product_sku : $row->sku,
                        'address_number' => $address_number,
                    ];

                    array_push($items, $temp);
                    $total += $row->sub_total;
                    if ($total > 0 && $order_items[0]->subtotal_of_order_items > 0) {
                        $total_discount_percentage = calculatePercentage($total, $order_items[0]->subtotal_of_order_items);
                    }
                    $total_order_items = OrderItems::where('order_id', $order_items[0]->order_id)
                        ->distinct()
                        ->count('id');


                    $res['data']['id'] = $order_items[0]->id;
                    $res['data']['order_id'] = $order_items[0]->order_id;
                    $res['data']['parcel_id'] = $parcel_id;
                    $res['data']['delivery_charge'] =  $orderChargeData->isEmpty() ? 0 : $orderChargeData->first()->delivery_charge;
                    $res['data']['seller_promo_discount'] = $orderChargeData->isEmpty() ? 0 : $orderChargeData->first()->promo_discount;
                    $res['data']['delivery_boy_name'] = $order_items[0]->username;
                    $res['data']['delivery_boy_mobile'] = $order_items[0]->mobile;
                    $res['data']['delivery_boy_email'] = $order_items[0]->email;
                    $res['data']['notes'] = $order_items[0]->notes;
                    $res['data']['payment_method'] = $order_items[0]->payment_method;
                    $res['data']['address'] = $fullAddress;
                    $res['data']['total_promo_discount'] = $order_items[0]->promo_discount;
                    $res['data']['username'] = $order_items[0]->username;
                    $res['data']['wallet_balance'] = $order_items[0]->wallet_balance;
                    $res['data']['total_payable'] = $order_items[0]->total_payable;
                    $res['data']['order_total'] = $order_items[0]->total;
                    $res['data']['final_total'] = $order_items[0]->final_total;
                    $res['data']['delivery_boy_id'] = $order_items[0]->delivery_boy_id;
                    $res['data']['created_at'] = $order_items[0]->created_at;
                    $res['data']['delivery_date'] = $order_items[0]->delivery_date;
                    $res['data']['delivery_time'] = $order_items[0]->delivery_time;
                    $res['data']['is_cod_collected'] = $order_items[0]->is_cod_collected;
                    $res['data']['seller_id'] = $order_items[0]->seller_id;
                    $res['data']['promo_discount'] = $order_items[0]->promo_discount;
                    $order_detls = $res['data'];
                }
            }

            $seller = [];
            $sellers_id = collect($res)->pluck('seller_id')->unique()->values()->all();

            foreach ($sellers_id as $id) {
                $query =  DB::table('seller_store as ss')
                    ->select(
                        'ss.store_name',
                        'ss.logo as shop_logo',
                        'ss.user_id as user_id',
                        'u.mobile as seller_mobile',
                        'u.city as seller_city',
                        'u.pincode as seller_pincode',
                        'u.email as seller_email',
                        'u.username as seller_name',
                    )
                    ->leftJoin('users as u', 'u.id', '=', 'ss.user_id')
                    ->where('ss.seller_id', $id)->get()->toArray();

                $value = [
                    'id' => $id,
                    'user_id' => !empty($query) ? $query[0]->user_id : null,
                    'store_name' => !empty($query) ? $query[0]->store_name : null,
                    'shop_logo' => !empty($query) ? getMediaImageUrl($query[0]->shop_logo, 'STORE_IMG_PATH') : null,
                    'seller_mobile' => !empty($query) ? $query[0]->seller_mobile : null,
                    'seller_pincode' => !empty($query) ? $query[0]->seller_pincode : null,
                    'seller_city' => !empty($query) ? $query[0]->seller_city : null,
                    'seller_email' => !empty($query) ? $query[0]->seller_email : null,
                    'seller_name' => !empty($query) ? $query[0]->seller_name : null,

                ];
                array_push($seller, $value);
            }


            $sellers = $seller;

            $settings = getSettings('system_settings', true);
            $settings = json_decode($settings, true);
            $currencyDetails = fetchDetails('currencies', ['is_default' => 1], 'symbol');
            $currency = !empty($currencyDetails) ? $currencyDetails[0]->symbol : '';
            $mobile_data = fetchDetails('addresses', ['id' => $order_items[0]->address_id], 'mobile');
            return view('delivery_boy.pages.forms.edit_orders', compact('order_detls', 'items', 'settings', 'sellers', 'currency', 'mobile_data'));
        }
    }
    public function update_order_item_status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
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
            ];
            return response()->json($response);
        }
        $parcel_id =  $request->input('id') ?? "";
        $parcel = fetchDetails('parcels', ['id' => $parcel_id], '*');
        $res = validateOrderStatus($parcel_id, $request['status'], 'parcels', '', '', $parcel[0]->type);

        if ($res['error']) {
            $response['error'] = true;
            $response['message'] = $res['message'];
            $response['data'] = array();
            return response()->json($response);
        }
        $parcel_items = fetchDetails('parcel_items', ['parcel_id' => $parcel[0]->id], '*');
        if (empty($parcel) && empty($parcel_items)) {
            $response = [
                'error' => true,
                'message' => 'Parcel Not Found',
            ];
            return response()->json($response);
        }
        $order_item_ids = array_column($parcel_items, 'order_item_id');
        $order_id = $parcel[0]->order_id;


        $orderItemRes = DB::table('order_items as oi')
            ->selectRaw('*, oi.id as order_item_id, (SELECT count(id) from order_items where order_id = oi.order_id) as order_counter,
            (SELECT count(active_status) from order_items where active_status ="cancelled" and order_id = oi.order_id) as order_cancel_counter,
            (SELECT count(active_status) from order_items where active_status ="returned" and order_id = oi.order_id) as order_return_counter,
            (SELECT count(active_status) from order_items where active_status ="delivered" and order_id = oi.order_id) as order_delivered_counter,
            (SELECT count(active_status) from order_items where active_status ="processed" and order_id = oi.order_id) as order_processed_counter,
            (SELECT count(active_status) from order_items where active_status ="shipped" and order_id = oi.order_id) as order_shipped_counter,
            (SELECT status from orders where id = oi.order_id) as order_status')
            ->whereIn('id', $order_item_ids)
            ->get()
            ->toArray();

        if (request('status') == 'delivered') {
            $settings = getSettings('system_settings', true);
            $settings = json_decode($settings, true);

            if ($settings['order_delivery_otp_system'] == 1) {
                $validator = Validator::make(request()->all(), [
                    'otp' => 'required|numeric',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'error' => true,
                        'message' => $validator->errors()->first(),
                        'data' => [],
                    ]);
                }

                if (!validateOtp(request('otp'), $orderItemRes[0]->order_item_id, $order_id, $orderItemRes[0]->seller_id, $parcel_id)) {
                    return response()->json([
                        'error' => true,
                        'message' =>
                        labels('admin_labels.invalid_otp_supplied', 'Invalid OTP supplied!'),
                        'data' => [],
                    ]);
                }
            }
        }

        $order_method = fetchDetails('orders', ['id' => $order_id], ['store_id', 'payment_method']);
        $store_id = $order_method[0]->store_id;
        if ($order_method[0]->payment_method == 'bank_transfer') {
            $bank_receipt = fetchDetails('order_bank_transfers', ['order_id' => $order_id]);
            $transaction_status = fetchDetails('transactions', ['order_id' => $order_id], 'status');
            if (empty($bank_receipt) || strtolower($transaction_status[0]->status) != 'success') {
                $response['error'] = true;
                $response['message'] =
                    labels('admin_labels.order_status_cannot_update_bank_verification_remains', "Order Status can not update, Bank verification is remain from transactions.");
                $response['data'] = array();
                return response()->json($response);
            }
        }
        if (updateOrder(['status' => $request->input('status')], ['id' => $parcel_id], true, 'parcels')) {
            updateOrder(['active_status' => $request->input('status')], ['id' => $parcel_id], false, 'parcels');
            foreach ($parcel_items as $item) {
                updateOrder(['status' => $request->input('status')], ['id' => $item->order_item_id], true, 'order_items');
                updateOrder(['active_status' => $request->input('status')], ['id' => $item->order_item_id], false, 'order_items');
                updateDetails(['updated_by' => auth()->id()], ['id' => $item->order_item_id], 'order_items');
            }
            if (($orderItemRes[0]->order_counter == intval($orderItemRes[0]->order_cancel_counter) + 1 && $request['status'] == 'cancelled') ||  ($orderItemRes[0]->order_counter == intval($orderItemRes[0]->order_return_counter) + 1 && $request['status'] == 'returned') || ($orderItemRes[0]->order_counter == intval($orderItemRes[0]->order_delivered_counter) + 1 && $request['status'] == 'delivered') || ($orderItemRes[0]->order_counter == intval($orderItemRes[0]->order_processed_counter) + 1 && $request['status'] == 'processed') || ($orderItemRes[0]->order_counter == intval($orderItemRes[0]->order_shipped_counter) + 1 && $request['status'] == 'shipped')) {
                /* process the refer and earn */
                $user = fetchDetails('orders', ['id' => $order_id], 'user_id');
                $user_id = $user[0]->user_id;

                $settings = getSettings('system_settings', true);
                $settings = json_decode($settings, true);
                $app_name = isset($settings['app_name']) && !empty($settings['app_name']) ? $settings['app_name'] : '';
                $user_res = fetchDetails('users', ['id' => $user_id], ['username', 'fcm_id', 'mobile', 'email']);
                //custom message
                if ($request->input('status') == 'received') {
                    $type = ['type' => "customer_order_received"];
                } elseif ($request->input('status') == 'processed') {
                    $type = ['type' => "customer_order_processed"];
                } elseif ($request->input('status') == 'shipped') {
                    $type = ['type' => "customer_order_shipped"];
                } elseif ($request->input('status') == 'delivered') {
                    $type = ['type' => "customer_order_delivered"];
                } elseif ($request->input('status') == 'cancelled') {
                    $type = ['type' => "customer_order_cancelled"];
                } elseif ($request->input('status') == 'returned') {
                    $type = ['type' => "customer_order_returned"];
                }

                $custom_notification = fetchDetails('custom_messages', $type, '*');
                $hashtag_customer_name = '< customer_name >';
                $hashtag_order_id = '< order_item_id >';
                $hashtag_application_name = '< application_name >';
                $string = isset($custom_notification) && !empty($custom_notification) ? json_encode($custom_notification[0]->message, JSON_UNESCAPED_UNICODE) : '';
                $hashtag = html_entity_decode($string);
                $data = str_replace(array($hashtag_customer_name, $hashtag_order_id, $hashtag_application_name), array($user_res[0]->username, $order_id, $app_name), $hashtag);
                $message = outputEscaping(trim($data, '"'));
                $customer_msg = (!empty($custom_notification)) ? $message :  'Hello Dear ' . $user_res[0]->username . 'Order status updated to' . $request['status'] . ' for your order ID #' . $order_id . ' please take note of it! Thank you for shopping with us. Regards ' . $app_name . '';
                $results = UserFcm::join('users', 'user_fcm.user_id', '=', 'users.id')
                    ->where('user_fcm.user_id', $user_id)
                    ->where('users.is_notification_on', 1)
                    ->select('user_fcm.fcm_id')
                    ->get();
                $fcm_ids = array();
                foreach ($results as $result) {
                    if (is_object($result)) {
                        $fcm_ids[] = $result->fcm_id;
                    }
                }

                $title = (!empty($custom_notification)) ? $custom_notification[0]->title : "Order status updated";
                $fcmMsg = array(
                    'title' => "$title",
                    'body' => "$customer_msg",
                    'type' => "order",
                    'store_id' => "$store_id",
                );
                $registrationIDs_chunks = array_chunk($fcm_ids, 1000);
                sendNotification('', $registrationIDs_chunks, $fcmMsg);
            }
            $response['error'] = false;
            $response['message'] = labels('admin_labels.status_updated_successfully', 'Status updated successfully.');
            $response['data'] = array();
            return response()->json($response);
        }
    }
    public function returned_orders()
    {
        return view('delivery_boy.pages.tables.returned_orders');
    }
    public function returned_orders_list(Request $request)
    {
        $delivery_boy_id = Auth::id();
        $response = getReturnOrderItemsList(
            $delivery_boy_id,
            $request->input('search', ''),
            $request->input('offset', 0),
            $request->input('limit', 10),
            $request->input('sort', 'oi.id'),
            $request->input('order', 'DESC'),
            $request->input('seller_id'),
            $request->input('fromApp', '0'),
            $request->input('order_item_id', ''),
            $request->input('isPrint', '0'),
            $request->input('order_status', ''),
            $request->input('payment_method', '')
        );
        return $response;
    }
    public function edit_returned_orders($order_id, $order_item_id)
    {
        $store_id = fetchDetails('order_items', ['id' => $order_item_id], 'store_id');
        $store_id = isset($store_id) && !empty($store_id) ? $store_id[0]->store_id : "";
        $delivery_boy_id = Auth::id();

        $res = fetchOrderItems($order_item_id, '', '', $delivery_boy_id, '', '', 'oi.id', 'DESC', '', '', '', '', '', $store_id);
        if (!empty($res['order_data'])) {
            $items = [];
            foreach ($res['order_data'] as $row) {
                if ($delivery_boy_id == $row->delivery_boy_id) {
                    $multipleWhere = ['seller_id' => $row->seller_id, 'order_id' => $row->id];
                    $orderChargeData = DB::table('order_charges')->where($multipleWhere)->get();
                    $updated_username = isset($row->updated_by) && !empty($row->updated_by) && $row->updated_by != 0 ? fetchDetails('users', ['id' => $row->updated_by], 'username')[0]->username : '';
                    $temp = [
                        'id' => $row->id,
                        'item_otp' => $row->otp,
                        'product_id' => $row->product_id,
                        'product_variant_id' => $row->product_variant_id,
                        'product_type' => $row->product_type,
                        'wallet_balance' => $row->wallet_balance,
                        'pname' => isset($row->pname) && ($row->pname != null) ? $row->pname : $row->product_name,
                        'quantity' => $row->quantity,
                        'is_cancelable' => $row->is_cancelable,
                        'is_attachment_required' => $row->is_attachment_required,
                        'is_returnable' => $row->is_returnable,
                        'tax_amount' => $row->tax_amount,
                        'discounted_price' => $row->discounted_price,
                        'price' => $row->price,
                        'item_subtotal' => $row->sub_total,
                        'updated_by' => $updated_username,
                        'seller_delivery_charge' => $orderChargeData->isEmpty() ? 0 : $orderChargeData->first()->delivery_charge,
                        'seller_promo_discount' => $orderChargeData->isEmpty() ? 0 : $orderChargeData->first()->promo_discount,
                        'active_status' => $row->active_status,
                        'product_image' => $row->image,
                        'product_variants' => getVariantsValuesById($row->product_variant_id),
                        'product_id' => $row->product_id,
                        'pickup_location' => $row->pickup_location,
                        'seller_otp' => $orderChargeData->isEmpty() ? 0 : $orderChargeData[0]->otp,
                        'is_sent' => $row->is_sent,
                        'seller_id' => $row->seller_id,
                        'download_allowed' => $row->download_allowed,
                        'product_slug' => $row->product_slug,
                        'sku' => isset($row->product_sku) && !empty($row->product_sku) ? $row->product_sku : $row->sku,
                    ];
                    array_push($items, $temp);
                }
            }

            $seller = [];
            $sellers_id = collect($res['order_data'])->pluck('seller_id')->unique()->values()->all();
            foreach ($sellers_id as $id) {
                $query =  DB::table('seller_store as ss')
                    ->select(
                        'ss.store_name',
                        'ss.logo as shop_logo',
                        'ss.user_id as user_id',
                        'u.mobile as seller_mobile',
                        'u.city as seller_city',
                        'u.pincode as seller_pincode',
                        'u.email as seller_email',
                        'u.username as seller_name',
                    )
                    ->leftJoin('users as u', 'u.id', '=', 'ss.user_id')
                    ->where('ss.seller_id', $id)->get()->toArray();
                $value = [
                    'id' => $id,
                    'user_id' => !empty($query) ? $query[0]->user_id : null,
                    'store_name' => !empty($query) ? $query[0]->store_name : null,
                    'shop_logo' => !empty($query) ? getMediaImageUrl($query[0]->shop_logo, 'STORE_IMG_PATH') : null,
                    'seller_mobile' => !empty($query) ? $query[0]->seller_mobile : null,
                    'seller_pincode' => !empty($query) ? $query[0]->seller_pincode : null,
                    'seller_city' => !empty($query) ? $query[0]->seller_city : null,
                    'seller_email' => !empty($query) ? $query[0]->seller_email : null,
                    'seller_name' => !empty($query) ? $query[0]->seller_name : null,

                ];
                array_push($seller, $value);
            }
            $sellers = $seller;
            $order_details = $res['order_data'][0];

            $settings = getSettings('system_settings', true);
            $settings = json_decode($settings, true);
            $currencyDetails = fetchDetails('currencies', ['is_default' => 1], 'symbol');
            $currency = !empty($currencyDetails) ? $currencyDetails[0]->symbol : '';
            $mobile_data = fetchDetails('addresses', ['id' => $res['order_data'][0]->address_id], 'mobile');
            $address = isset($row->address_id) && !empty($row->address_id) && $row->address_id != 0
                ? (fetchDetails('addresses', ['id' => $row->address_id], 'address') && isset(fetchDetails('addresses', ['id' => $row->address_id], 'address')[0]->address)
                    ? fetchDetails('addresses', ['id' => $row->address_id], 'address')[0]->address
                    : '')
                : '';
        }
        return view('delivery_boy.pages.forms.edit_returned_orders', compact('order_details', 'items', 'settings', 'currency', 'address', 'mobile_data', 'sellers'));
    }
    public function update_return_order_item_status(Request $request)
    {
        $order_item_id = $request->order_item_id ?? "";
        $status = $request->status ?? "";
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
            return response()->json([
                'error' => false,
                'message' =>
                labels('admin_labels.status_updated_successfully', 'Status Updated Successfully'),
                'data' => $result,
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
}