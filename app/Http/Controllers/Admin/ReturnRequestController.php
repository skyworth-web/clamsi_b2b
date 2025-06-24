<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use App\Models\User;
use App\Models\UserFcm;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReturnRequestController extends Controller
{
    public function index()
    {
        $deliveryRes = DB::table('users as u')
            ->select('u.*')
            ->where('u.role_id', '3')
            ->where('u.active', 1)
            ->get();
        $deliveryRes = $deliveryRes->toArray();

        return view('admin.pages.tables.return_request', compact('deliveryRes'));
    }

    public function list()
    {
        $limit = 10;
        $sort = 'id';
        $order = 'ASC';
        $multipleWhere = [];

        if (request()->has('offset')) {
            $offset = request()->input('search', '') || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        }

        if (request()->has('limit')) {
            $limit = request()->input('limit');
        }

        if (request()->has('sort')) {
            $sort = request()->input('sort');
        }

        if (request()->has('order')) {
            $order = request()->input('order');
        }

        $search = trim(request()->input('search', ''));

        // Base query for counting records
        $count_res = DB::table('return_requests as rr')
            ->join('users as u', 'u.id', '=', 'rr.user_id')
            ->join('products as p', 'p.id', '=', 'rr.product_id')
            ->join('order_items as oi', 'oi.id', '=', 'rr.order_item_id')
            ->join('stores as s', 's.id', '=', 'oi.store_id');

        // Apply search filter
        if (!empty($search)) {
            $count_res->where(function ($query) use ($search) {
                $query->where('u.username', 'like', '%' . $search . '%')
                    ->orWhere('p.name', 'like', '%' . $search . '%')
                    ->orWhere('s.name', 'like', '%' . $search . '%')
                    ->orWhere('rr.id', 'like', '%' . $search . '%')
                    ->orWhere('oi.order_id', 'like', '%' . $search . '%');
            });
        }

        if (!empty($multipleWhere)) {
            $count_res->orWhere(function ($query) use ($multipleWhere) {
                foreach ($multipleWhere as $column => $value) {
                    $query->orWhere($column, $value);
                }
            });
        }

        // Get total count
        $request_count = $count_res->select(DB::raw('COUNT(rr.id) as total'))->get();
        $total = $request_count->isEmpty() ? 0 : $request_count[0]->total;

        // Base query for fetching records
        $search_res = DB::table('return_requests as rr')
            ->join('users as u', 'u.id', '=', 'rr.user_id')
            ->join('products as p', 'p.id', '=', 'rr.product_id')
            ->join('order_items as oi', 'oi.id', '=', 'rr.order_item_id')
            ->join('stores as s', 's.id', '=', 'oi.store_id');

        // Apply search filter
        if (!empty($search)) {
            $search_res->where(function ($query) use ($search) {
                $query->where('u.username', 'like', '%' . $search . '%')
                    ->orWhere('p.name', 'like', '%' . $search . '%')
                    ->orWhere('s.name', 'like', '%' . $search . '%')
                    ->orWhere('rr.id', 'like', '%' . $search . '%')
                    ->orWhere('oi.order_id', 'like', '%' . $search . '%');
            });
        }

        if (!empty($multipleWhere)) {
            $search_res->orWhere(function ($query) use ($multipleWhere) {
                foreach ($multipleWhere as $column => $value) {
                    $query->orWhere($column, 'like', '%' . $value . '%');
                }
            });
        }

        // Order, limit, and offset for pagination
        $search_res->orderBy($sort, $order)
            ->select('rr.id', 'rr.remarks', 'oi.order_id', 'u.id as user_id', 'u.username as username', 's.id as store_id', 'p.id as product_id', 'p.name as product_name', 'oi.price', 'oi.delivery_boy_id', 'oi.discounted_price', 'oi.id as order_item_id', 'oi.quantity', 'oi.sub_total', 'rr.status', 's.name as store_name')
            ->skip($offset)
            ->take($limit);

        // Get the results
        $results = $search_res->get();

        $rows = [];
        $tempRow = [];
        $language_code = get_language_code();
        foreach ($results as $row) {
            $action = '<div class="d-flex align-items-center">
                        <a class="dropdown-item single_action_button dropdown_menu_items edit_request edit_return_request data-id="' . $row->order_item_id . '"  data-bs-target="#request_request_modal" data-bs-toggle="modal"><i class="bx bx-pencil mx-2"></i></a>
                        </div>';
            $delivery_boy_name = $this->getUserName($row->delivery_boy_id);

            $tempRow['id'] = $row->id;
            $tempRow['user_id'] = $row->user_id;
            $tempRow['user_name'] = $row->username;
            $tempRow['order_id'] = $row->order_id;
            $tempRow['order_item_id'] = $row->order_item_id;
            $tempRow['delivery_boy_id'] = $row->delivery_boy_id . '|' . $delivery_boy_name;
            $tempRow['product_name'] = getDynamicTranslation('products', 'name', $row->product_id, $language_code);
            $tempRow['store_name'] = getDynamicTranslation('stores', 'name', $row->store_id, $language_code);
            $tempRow['price'] = formateCurrency(formatePriceDecimal($row->price));
            $tempRow['discounted_price'] = formateCurrency(formatePriceDecimal($row->discounted_price));
            $tempRow['quantity'] = $row->quantity;
            $tempRow['sub_total'] = formateCurrency(formatePriceDecimal($row->sub_total));
            $tempRow['status_digit'] = $row->status;

            $status = [
                '0' => '<span class="badge bg-success">Pending</span>',
                '1' => '<span class="badge bg-primary">Approved</span>',
                '2' => '<span class="badge bg-danger">Rejected</span>',
                '8' => '<span class="badge bg-secondary">Return Pickedup</span>',
                '3' => '<span class="badge bg-success">Returned</span>',
            ];

            $tempRow['status'] = $status[$row->status];
            $tempRow['remarks'] = $row->remarks;
            $tempRow['operate'] = $action;

            $rows[] = $tempRow;
        }

        return response()->json([
            "rows" => $rows,
            "total" => $total,
        ]);
    }

    private function getUserName($userId)
    {
        $user = User::find($userId);
        return $user ? $user->username : null;
    }
    public function update(Request $request)
    {

        $rules = [
            'return_request_id' => 'required|numeric',
            'status' => 'required|numeric',
            'order_item_id' => 'required|numeric',
        ];
        if ($request->filled('status') && $request->input('status') == '1') {
            $rules['deliver_by'] = 'required';
        }

        $messages = [
            'deliver_by.required' => 'Please select delivery boy.',
        ];

        if ($response = validatePanelRequest($request, $rules, $messages)) {
            return $response;
        } else {
            $status = $request['status'];

            $remarks = isset($request['update_remarks']) && !empty($request['update_remarks']) ? $request['update_remarks'] : null;
            $returnRequestId = $request['return_request_id'];
            $item_id = $request['order_item_id'];

            // Find the record by its ID
            $returnRequest = ReturnRequest::find($returnRequestId);

            if ($returnRequest) {

                if ($returnRequest->status == 3 && $request['status'] == 3) {
                    return response()->json([
                        'error' => true,
                        'error_message' => 'This Item Is Already Returned!'
                    ]);
                }
                $returnRequest->status = $status;
                $returnRequest->remarks = $remarks;
                $returnRequest->save();
                $data = fetchDetails('order_items', ['id' => $request['order_item_id']], ['product_variant_id', 'quantity', 'user_id']);
                $order_item_res = fetchDetails('order_items', ['id' => $item_id], ['order_id', 'store_id']);
                $customer_id = $data[0]->user_id;
                $settings = getSettings('system_settings', true);
                $settings = json_decode($settings, true);
                $app_name = isset($settings['app_name']) && !empty($settings['app_name']) ? $settings['app_name'] : '';
                $customer_res = fetchDetails('users', ['id' => $customer_id], ['username', 'fcm_id']);
                $fcm_ids = array();

                if ($request['status'] == '3') {
                    process_refund($item_id, 'returned');
                    updateStock($data[0]->product_variant_id, $data[0]->quantity, 'plus');
                    update_order_item($item_id, 'returned', 1);

                    $custom_notification = fetchDetails('custom_messages', ['type' => "customer_order_returned"], '*');
                    $customer_res[0]->username = isset($customer_res[0]->username) ? $customer_res[0]->username : '';
                    $hashtag_customer_name = '< customer_name >';
                    $hashtag_order_id = '< order_item_id >';
                    $hashtag_application_name = '< application_name >';
                    $string = isset($custom_notification) && !empty($custom_notification) ? json_encode($custom_notification[0]->message, JSON_UNESCAPED_UNICODE) : '';
                    $hashtag = html_entity_decode($string);
                    $data1 = str_replace(array($hashtag_customer_name, $hashtag_order_id, $hashtag_application_name), array($customer_res[0]->username, $order_item_res[0]->order_id, $app_name), $hashtag);
                    $message = outputEscaping(trim($data1, '"'));
                    $customer_msg = (!empty($custom_notification)) ? $message : 'Hello Dear ' . $customer_res[0]->username . ',your return request of order item id' . $item_id . ' has been declined';

                    $customer_result = UserFcm::join('users', 'user_fcm.user_id', '=', 'users.id')
                        ->where('user_fcm.user_id', $customer_id)
                        ->where('users.is_notification_on', 1)
                        ->select('user_fcm.fcm_id')
                        ->get();

                    foreach ($customer_result as $result) {
                        if (is_object($result)) {
                            $fcm_ids[] = $result->fcm_id;
                        }
                    }
                    $store_id = $order_item_res[0]->store_id;
                    $order_status_title = (!empty($custom_notification)) ? $custom_notification[0]->title : "Order status updated";

                    $fcmMsg = array(
                        'title' => "$order_status_title",
                        'body' => "$customer_msg",
                        'type' => "order",
                        'store_id' => "$store_id",
                    );

                    $registrationIDs_chunks = array_chunk($fcm_ids, 1000);
                    sendNotification('', $registrationIDs_chunks, $fcmMsg);
                } elseif ($request['status'] == '1') {
                    $store_id = fetchDetails('order_items', ['id' => $item_id], 'store_id');
                    $store_id = isset($store_id) && !empty($store_id) ? $store_id[0]->store_id : "";
                    updateDetails(['delivery_boy_id' => $request['deliver_by']], ['id' => $item_id], 'order_items');
                    update_order_item($item_id, 'return_request_approved', 1);

                    //for delivery boy notification
                    $user_id = $request['deliver_by'];

                    $user_res = fetchDetails('users', ['id' => $user_id], ['username', 'fcm_id']);

                    //custom message

                    $custom_notification = fetchDetails('custom_messages', ['type' => "customer_order_returned_request_approved"], '*');
                    $customer_res[0]->username = isset($customer_res[0]->username) ? $customer_res[0]->username : '';
                    $hashtag_customer_name = '< customer_name >';
                    $hashtag_order_id = '< order_item_id >';
                    $hashtag_application_name = '< application_name >';
                    $string = isset($custom_notification) && !empty($custom_notification) ? json_encode($custom_notification[0]->message, JSON_UNESCAPED_UNICODE) : '';
                    $hashtag = html_entity_decode($string);
                    $data1 = str_replace(array($hashtag_customer_name, $hashtag_order_id, $hashtag_application_name), array($customer_res[0]->username, $order_item_res[0]->order_id, $app_name), $hashtag);
                    $message = outputEscaping(trim($data1, '"'));
                    $delivery_boy_msg = 'Hello Dear ' . $user_res[0]->username . ' ' . 'you have new order to be pickup order ID #' . $order_item_res[0]->order_id . ' please take note of it! Thank you. Regards ' . $app_name . '';
                    $customer_msg = (!empty($custom_notification)) ? $message : 'Hello Dear ' . $customer_res[0]->username . ',your return request of order item id' . $item_id . ' is approved';
                    $title = (!empty($custom_notification)) ? $custom_notification[0]->title : "You have new order to deliver";

                    $results = UserFcm::join('users', 'user_fcm.user_id', '=', 'users.id')
                        ->where('user_fcm.user_id', $user_id)
                        ->where('users.is_notification_on', 1)
                        ->select('user_fcm.fcm_id')
                        ->get();
                    foreach ($results as $result) {
                        if (is_object($result)) {
                            $fcm_ids[] = $result->fcm_id;
                        }
                    }
                    $fcmMsg = array(
                        'title' => "$title",
                        'body' => "$delivery_boy_msg",
                        'type' => "order",
                        'store_id' => "$store_id",


                    );
                    $registrationIDs_chunks = array_chunk($fcm_ids, 1000);
                    sendNotification('', $registrationIDs_chunks, $fcmMsg);

                    $order_status_title = (!empty($custom_notification)) ? $custom_notification[0]->title : "Order status updated";

                    $customer_result = UserFcm::join('users', 'user_fcm.user_id', '=', 'users.id')
                        ->where('user_fcm.user_id', $customer_id)
                        ->where('users.is_notification_on', 1)
                        ->select('user_fcm.fcm_id')
                        ->get();

                    foreach ($customer_result as $result) {
                        if (is_object($result)) {
                            $fcm_ids[] = $result->fcm_id;
                        }
                    }

                    $fcmMsg = array(
                        'title' => "$order_status_title",
                        'body' => "$customer_msg",
                        'type' => "order",
                        'store_id' => "$store_id",
                    );

                    $registrationIDs_chunks = array_chunk($fcm_ids, 1000);
                    sendNotification('', $registrationIDs_chunks, $fcmMsg);
                } elseif ($request['status'] == '2') {
                    $store_id = fetchDetails('order_items', ['id' => $item_id], 'store_id');
                    $store_id = isset($store_id) && !empty($store_id) ? $store_id[0]->store_id : "";
                    update_order_item($item_id, 'return_request_decline', 1);
                    //custom message
                    $custom_notification = fetchDetails('custom_messages', ['type' => "customer_order_returned_request_decline"], '*');
                    $customer_res[0]->username = isset($customer_res[0]->username) ? $customer_res[0]->username : '';
                    $hashtag_customer_name = '< customer_name >';
                    $hashtag_order_id = '< order_item_id >';
                    $hashtag_application_name = '< application_name >';
                    $string = isset($custom_notification) && !empty($custom_notification) ? json_encode($custom_notification[0]->message, JSON_UNESCAPED_UNICODE) : '';
                    $hashtag = html_entity_decode($string);
                    $data1 = str_replace(array($hashtag_customer_name, $hashtag_order_id, $hashtag_application_name), array($customer_res[0]->username, $order_item_res[0]->order_id, $app_name), $hashtag);
                    $message = outputEscaping(trim($data1, '"'));
                    $customer_msg = (!empty($custom_notification)) ? $message : 'Hello Dear ' . $customer_res[0]->username . ',your return request of order item id' . $item_id . ' has been declined';

                    $customer_result = UserFcm::join('users', 'user_fcm.user_id', '=', 'users.id')
                        ->where('user_fcm.user_id', $customer_id)
                        ->where('users.is_notification_on', 1)
                        ->select('user_fcm.fcm_id')
                        ->get();

                    foreach ($customer_result as $result) {
                        if (is_object($result)) {
                            $fcm_ids[] = $result->fcm_id;
                        }
                    }

                    $order_status_title = (!empty($custom_notification)) ? $custom_notification[0]->title : "Order status updated";

                    $fcmMsg = array(
                        'title' => "$order_status_title",
                        'body' => "$customer_msg",
                        'type' => "order",
                        'store_id' => "$store_id",
                    );

                    $registrationIDs_chunks = array_chunk($fcm_ids, 1000);
                    sendNotification('', $registrationIDs_chunks, $fcmMsg);
                }
            }
            $response['error'] = false;
            $response['message'] =
                labels('admin_labels.return_request_updated_successfully', 'Return request updated successfully');
            return response()->json($response);
        }
    }
}
