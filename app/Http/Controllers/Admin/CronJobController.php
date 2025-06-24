<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItems;
use App\Models\Seller;
use App\Models\User;
use App\Models\UserFcm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CronJobController extends Controller
{
    public function settleSellerCommission(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'is_date' => 'required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            if ($request->ajax()) {
                return response()->json(['errors' => true, 'message' => $errors->all()]);
            }
        }
        $store_id = getStoreId();
        $is_date = (isset($request['is_date']) && is_numeric($request['is_date']) && !empty(trim($request['is_date']))) ? $request['is_date'] : false;

        $date = now()->toDateString();


        $settings = getSettings('system_settings', true);
        $settings = json_decode($settings, true);

        if ($is_date) {
            $where = "oi.active_status='delivered' AND is_credited=0 and  DATE_ADD(DATE_FORMAT(oi.created_at, '%Y-%m-%d'), INTERVAL " . $settings['max_days_to_return_item'] . " DAY) = '" . $date . "'";
        } else {
            $where = "oi.active_status='delivered' AND is_credited=0 ";
        }

        $data = DB::table('order_items as oi')
            ->select("c.id as category_id", "oi.id", DB::raw("DATE(oi.created_at) as order_date"), "oi.order_id", "oi.product_variant_id", "oi.seller_id", "oi.sub_total")
            ->leftJoin('product_variants as pv', 'pv.id', '=', 'oi.product_variant_id')
            ->leftJoin('products as p', 'p.id', '=', 'pv.product_id')
            ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
            ->whereRaw($where)
            ->get()
            ->toArray();

        $walletUpdated = false;


        if (!empty($data)) {
            foreach ($data as $row) {

                $user_id = Seller::where('id', $row->seller_id)->value('user_id');

                $catCom = fetchDetails('seller_commissions', ['seller_id' => $row->seller_id, 'category_id' => $row->category_id], 'commission');

                if (!empty($catCom) && ($catCom[0]->commission != 0)) {
                    $commissionPr = $catCom[0]->commission;
                } else {

                    $globalComm = DB::table('seller_store')->select('commission')
                        ->where('seller_store.seller_id', $row->seller_id)
                        ->where('seller_store.store_id', $store_id)
                        ->get();

                    // Using ternary operator to handle the array access and empty check
                    $commissionPr = (isset($globalComm) && !empty($globalComm) && isset($globalComm[0]->commission)) ? $globalComm[0]->commission : 0;
                }

                $commissionAmt = $row->sub_total / 100 * $commissionPr;
                $transferAmt = $row->sub_total - $commissionAmt;

                $response = updateWalletBalance('credit',  $user_id, $transferAmt, 'Commission Amount Credited for Order Item ID  : ' . $row->id, $row->id);

                if ($response['error'] == false) {
                    updateDetails(['is_credited' => 1, 'admin_commission_amount' => $commissionAmt, "seller_commission_amount" => $transferAmt], ['id' => $row->id], 'order_items');
                    $walletUpdated = true;
                    $responseData['error'] = false;
                    $responseData['message'] =
                        labels('admin_labels.commission_settled_successfully', 'Commission settled Successfully');
                } else {
                    $walletUpdated = false;
                    $responseData['error'] = true;
                    $responseData['message'] =
                        labels('admin_labels.commission_not_settled', 'Commission not settled');
                }
            }

            if ($walletUpdated == true) {
                $sellerIds = array_values(array_unique(array_column($data, "seller_id")));

                foreach ($sellerIds as $seller) {

                    $settings = getSettings('system_settings', true);
                    $settings = json_decode($settings, true);
                    $appName = isset($settings['app_name']) && !empty($settings['app_name']) ? $settings['app_name'] : '';

                    $userRes = fetchDetails('users', ['id' => $seller], ['username', 'fcm_id', 'email', 'mobile']);
                }
            } else {
                $responseData['error'] = true;
                $responseData['message'] =
                    labels('admin_labels.commission_not_settled', 'Commission not settled');
            }
        } else {
            $responseData['error'] = true;
            $responseData['message'] =
                labels('admin_labels.no_order_found_for_settlement', 'No order found for settlement');
        }

        return response()->json($responseData);
    }


    public function settleCashbackDiscount(Request $request)
    {
        $return = false;
        $date = now()->format('Y-m-d');
        $settings = getSettings('system_settings', true);
        $settings = json_decode($settings, true);

        $returnableData = Order::select('orders.id', 'orders.created_at', 'orders.total', 'orders.final_total', 'orders.promo_code_id', 'orders.user_id', DB::raw('(DATE_FORMAT(orders.created_at, "%Y-%m-%d")) AS date'))
            ->leftJoin('order_items AS oi', 'oi.order_id', '=', 'orders.id')
            ->leftJoin('product_variants AS pv', 'oi.product_variant_id', '=', 'pv.id')
            ->leftJoin('products AS p', 'p.id', '=', 'pv.product_id')
            ->where('oi.active_status', 'delivered')
            ->where('orders.promo_code_id', '!=', '')
            ->where('orders.promo_discount', '<=', 0)
            ->groupBy('orders.id')

            ->get();

        foreach ($returnableData as $result) {

            $res = OrderItems::select('order_items.id AS item_id', 'order_items.order_id', 'p.is_returnable')
                ->leftJoin('product_variants AS pv', 'order_items.product_variant_id', '=', 'pv.id')
                ->leftJoin('products AS p', 'p.id', '=', 'pv.product_id')
                ->where('order_items.order_id', $result->id)
                ->whereIn('p.is_returnable', [0, 1])
                ->get();

            $returnableStatus = $res->pluck('is_returnable')->toArray();

            if (in_array(1, $returnableStatus)) {
                $return = true;
            } else {
                $return = false;
            }
        }

        if ($return == true) {
            $select = DB::raw("DATE_ADD(DATE_FORMAT(orders.created_at, '%Y-%m-%d'), INTERVAL {$settings['max_days_to_return_item']} DAY) AS date");
        } else {
            $select = DB::raw('(DATE_FORMAT(orders.created_at, "%Y-%m-%d")) AS date');
        }

        $data = Order::select('orders.id', 'orders.store_id', 'orders.created_at', 'orders.total', 'orders.final_total', 'orders.promo_code_id', 'orders.user_id', $select)
            ->leftJoin('order_items AS oi', 'oi.order_id', '=', 'orders.id')
            ->where('oi.active_status', 'delivered')
            ->where('orders.promo_code_id', '!=', '')
            ->where('orders.promo_discount', '<=', 0)
            ->groupBy('orders.id')

            ->get();

        $walletUpdated = false;
        if ($data->isNotEmpty()) {
            foreach ($data as $row) {

                $promoCodeId = $row->promo_code_id;
                $userId = $row->user_id;
                $finalTotal = $row->final_total;
                $store_id = $row->store_id;

                $res = validatePromoCode($promoCodeId, $userId, $finalTotal);
                if (!empty($res->original['data']) && isset($res->original['data'][0])) {
                    $response = updateWalletBalance('credit', $userId, $res->original['data'][0]->final_discount, 'Discounted Amount Credited for Order Item ID: ' . $row->id);
                    if ($response['error'] == false && $response['error'] == '') {
                        updateDetails(['total_payable' => $res->original['data'][0]->final_total, 'final_total' => $res->original['data'][0]->final_total, 'promo_discount' => $res->original['data'][0]->final_discount], ['id' => $row->id], 'orders');
                        $walletUpdated = true;
                        $response_data['error'] = false;
                        $response_data['message'] =
                            labels('admin_labels.discount_added_successfully', 'Discount Added Successfully...');
                    } else {
                        $walletUpdated = false;
                        $response_data['error'] =  true;
                        $response_data['message'] =
                            labels('admin_labels.discount_not_added', 'Discount not Added');
                    }
                }
            }

            if ($walletUpdated == true) {
                $userIds = array_values(array_unique($data->pluck('user_id')->toArray()));
                foreach ($userIds as $user) {

                    //custom message
                    $app_name = isset($settings['app_name']) && !empty($settings['app_name']) ? $settings['app_name'] : '';
                    $user_res = fetchDetails('users', ['id' => $user], ['username', 'fcm_id', 'email', 'mobile']);
                    $custom_notification =  fetchDetails('custom_messages', ['type' => "settle_cashback_discount"], '*');
                    $hashtag_customer_name = '< customer_name >';
                    $hashtag_application_name = '< application_name >';
                    $string = isset($custom_notification) && !empty($custom_notification) ? json_encode($custom_notification[0]->message, JSON_UNESCAPED_UNICODE) : '';
                    $hashtag = html_entity_decode($string);
                    $data = str_replace(array($hashtag_customer_name, $hashtag_application_name), array($user_res[0]->username, $app_name), $hashtag);
                    $message = outputEscaping(trim($data, '"'));
                    $customer_title = (!empty($custom_notification)) ? $custom_notification[0]->title : "Discounted Amount Credited";
                    $customer_msg = (!empty($custom_notification)) ? $message :  'Hello Dear ' . $user_res[0]->username . 'Discounted Amount Credited, which orders are delivered. Please take note of it! Regards' . $app_name . '';

                    $fcm_ids = array();
                    $results =  UserFcm::join('users', 'user_fcm.user_id', '=', 'users.id')
                        ->where('user_fcm.user_id', $user)
                        ->where('users.is_notification_on', 1)
                        ->select('user_fcm.fcm_id')
                        ->get();
                    foreach ($results as $result) {
                        if (is_object($result)) {
                            $fcm_ids[] = $result->fcm_id;
                        }
                    }

                    $fcmMsg = array(
                        'title' => "$customer_title",
                        'body' => "$customer_msg",
                        'type' => "Discounted",
                        'store_id' => "$store_id",
                    );
                    sendNotification('', $fcm_ids, $fcmMsg,);

                }
            } else {
                $response_data['error'] =  true;
                $response_data['message'] =
                    labels('admin_labels.discount_not_added', 'Discount not Added');
            }
        } else {
            $response_data['error'] =  true;
            $response_data['message'] =
                labels('admin_labels.orders_not_found', 'Orders Not Found');
        }

        return response()->json($response_data);
    }
}
