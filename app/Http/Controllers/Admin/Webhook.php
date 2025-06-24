<?php

namespace App\Http\Controllers\Admin;

use App\Libraries\Phonepe;
use App\Models\Transaction;
use Hamcrest\Arrays\IsArray;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Controllers\TransactionController;
use App\Libraries\Paystack;
use App\Libraries\Razorpay;
use App\Libraries\Stripe;
use App\Models\Setting;

class Webhook extends Controller
{

    public $transactionController = "";

    function __construct()
    {
        $this->transactionController = app(TransactionController::class);
    }

    public function phonepe_webhook(Request $request)
    {

        $phonepe = new Phonepe;
        $request = file_get_contents('php://input');
        $request = json_decode($request);
        $request = $request->response ?? "";
        Log::alert("phonepe webhook=>" . $request);
        if (!empty($request)) {

            $request = base64_decode($request);
            $request = json_decode($request, 1);
            $txn_id = $request['data']['merchantTransactionId'] ?? "";
            if (!empty($txn_id)) {
                $transaction = fetchDetails('transactions', ['txn_id' => $txn_id]);
                $amount = $request['data']['amount'] / 100;
            } else {
                $amount = 0;
            }
            if (!empty($transaction)) {
                $user_id = $transaction[0]->user_id;
                $transaction_type = (isset($transaction[0]->transaction_type)) ? $transaction[0]->transaction_type : "";
                $order_id = (isset($transaction[0]->order_id)) ? $transaction[0]->order_id : "";
            } else {
                Log::alert("Phonepe transaction id not found in local database ==>" . $request);
                return;
            }
            $status = $request['code'] ?? "";
            $check_status = $phonepe->check_status($txn_id);
            Log::alert("Phonepe check_status" . json_encode($check_status));
            if ($check_status['code'] = 'INTERNAL_SERVER_ERROR') {
                Log::alert("Phonepe INTERNAL SERVER ERROR!! retry to check status");
                $check_status = $phonepe->check_status($txn_id);
            }
            if ($check_status['success'] == true) {
                if ($status == 'PAYMENT_SUCCESS') {
                    $data['status'] = "success";
                    if ($transaction_type == 'transaction') {
                        $data['message'] = "Payment received successfully";
                        updateDetails(['active_status' => "received"], ['order_id' => $order_id], 'order_items');
                        $order_status = json_encode(array(array('received', date("d-m-Y h:i:sa"))));
                        updateDetails(['status' => $order_status], ['order_id' => $order_id], 'order_items');

                        // place order custome notification on payment success

                        sendCustomNotificationOnPaymentSuccess($order_id, $user_id);

                        $response['error'] = false;
                        $response['message'] = "Payment received successfully";

                        return response()->json($response);
                    } else {
                        $data['status'] = "success";
                        if (!updateBalance($amount, $user_id, 'add')) {
                            Log::alert("Phonepe Webhook | couldn't update in wallet balance  -->" . $request);
                        }
                        $data['message'] = "Wallet refill successful";
                    }
                    Transaction::where('txn_id', $txn_id)->update($data);
                    $response['error'] = false;
                    $response['message'] = "Wallet refill successful";
                    return response()->json($response);
                } else if ($status == "BAD_REQUEST" || $status == "AUTHORIZATION_FAILED" || $status == "PAYMENT_ERROR" || $status == "TRANSACTION_NOT_FOUND" || $status == "PAYMENT_DECLINED" || $status == "TIMED_OUT") {
                    $data['status'] = "failed";
                    if ($transaction_type == 'transaction') {
                        $data['message'] = "Payment couldn't be processed!";
                        updateDetails(['active_status' => "cancelled"], ['order_id' => $order_id], 'order_items');
                        $order_status = json_encode(array(array('cancelled', date("d-m-Y h:i:sa"))));
                        updateDetails(['status' => $order_status], ['order_id' => $order_id], 'order_items');
                        $order_items = fetchDetails('order_items', ['order_id' => $order_id]);
                        $product_variant_ids = [];
                        $qty = [];
                        foreach ($order_items as $items) {
                            array_push($product_variant_ids, $items->product_variant_id);
                            array_push($qty, $items->quantity);
                        }
                        $order_detail = fetchDetails('orders', ["id" => $order_id], 'wallet_balance');
                        $wallet_balance = $order_detail[0]->wallet_balance;
                        if ($wallet_balance > 0) {
                            updateBalance($wallet_balance, $user_id, "add");
                        }
                        updateStock($product_variant_ids, $qty, 'plus');
                    } else {
                        $data['message'] = "Wallet could not be recharged!";
                        $response['error'] = false;
                        $response['message'] = "Wallet could not be recharged!";

                        return response()->json($response);
                    }
                    Transaction::where('txn_id', $txn_id)->update($data);
                    return;
                }
            }
            return;
        }
        $response['error'] = false;
        $response['message'] = "phonepe No Request Found";

        return response()->json($response);
        Log::alert("phonepe No Request Found=>" . $request);
    }

    public function paypal_webhook(Request $request) //remaining
    {
        $res = $request->all();
        $request = json_encode($res);
        Log::alert("paypal webhook=>" . $request);
        if (!empty($res)) {
            $txn_id = $res['resource']['purchase_units'][0]['reference_id'] ?? "";
            if (!empty($txn_id)) {
                $transaction = fetchDetails('transactions', ['txn_id' => $txn_id]);
                $amount = $res['resource']['purchase_units'][0]['amount']['value'];
            } else {
                $amount = 0;
            }
            if (!empty($transaction)) {
                $user_id = $transaction[0]->user_id;
                $transaction_type = (isset($transaction[0]->transaction_type)) ? $transaction[0]->transaction_type : "";
                $order_id = (isset($transaction[0]->order_id)) ? $transaction[0]->order_id : "";
            } else {
                Log::alert("paypal transaction id not found in local database ==>" . $request);
                return;
            }
            if ($amount != number_format($transaction[0]->amount, 2, '.', '')) {
                Log::alert("paypal order amount doesn't match ==>" . $request);
                return;
            }
            $status = $res['resource']['status'] ?? "";
            $intent = $res['resource']['intent'];
            if ($status == 'COMPLETED' && $intent == "CAPTURE") {
                $data['status'] = "success";
                if ($transaction_type == 'transaction') {
                    $data['message'] = "Payment received successfully";
                    updateDetails(['active_status' => "received"], ['order_id' => $order_id], 'order_items');
                    $order_status = json_encode(array(array('received', date("d-m-Y h:i:sa"))));
                    updateDetails(['status' => $order_status], ['order_id' => $order_id], 'order_items');
                } else {
                    $data['message'] = "Wallet refill successful";
                    if (!updateBalance($amount, $user_id, 'add')) {
                        Log::alert("paypal Webhook | couldn\'t update in wallet balance  -->" . $request);
                    }
                }
                $this->transactionController->update_transaction($txn_id, $data);
            }
            return;
        }
        Log::alert("Paypal No Request Found=>" . $request);
    }

    public function paystack_webhook(Request $request)
    {
        $system_settings = getsettings('system_settings', true);
        $system_settings = json_decode($system_settings, true);
        $paystack = new Paystack;
        $credentials = getsettings('payment_method', true);
        $credentials = json_decode($credentials, true);
        $paystack_key_id = $credentials['paystack_key_id'];
        $secret_key = $credentials['paystack_secret_key'];

        $request_body = file_get_contents('php://input');
        $event = json_decode($request_body, true);
        Log::alert("paystack webhook=>" . $request);

        $order_id = $event['data']['metadata']['order_id'];
        if (is_numeric($order_id)) {
            if (!empty($event['data'])) {

                $txn_id = (isset($event['data']['reference'])) ? $event['data']['reference'] : "";
                if (isset($txn_id) && !empty($txn_id)) {
                    $transaction = fetchdetails('transactions', ['txn_id' => $txn_id], '*');
                    if (!empty($transaction)) {
                        $order_id = $transaction[0]->order_id;
                        $user_id = $transaction[0]->user_id;
                    } else {
                        $order_id = $event['data']['metadata']['order_id'];
                        $order_data = fetchorders($order_id);
                        $user_id = $order_data['order_data'][0]->user_id;
                    }
                }
                $amount = $event['data']['amount'] / 100;
                $currency = $event['data']['currency'];
            } else {
                $order_id = 0;
                $amount = 0;
                $currency = (isset($event['data']['currency'])) ? $event['data']['currency'] : "";
            }
        }

        /* Wallet refill has unique format for order ID - wallet-refill-user-{user_id}-{system_time}-{3 random_number}  */
        if (!is_numeric($order_id) && strpos($order_id, "wallet-refill-user") !== false) {

            $temp = explode("-", $order_id);
            if (isset($temp[3]) && is_numeric($temp[3]) && !empty($temp[3] && $temp[3] != '')) {
                $user_id = $temp[3];
            } else {
                $user_id = 0;
            }
        }


        if ($event['event'] == 'charge.success') {
            if (!empty($order_id)) { /* To do the wallet recharge if the order id is set in the pattern */

                if (strpos($order_id, "wallet-refill-user") !== false) {
                    $txn_id = (isset($event['data']['reference'])) ? $event['data']['reference'] : "";
                    $amount = $event['data']['amount'] / 100;
                    $data['transaction_type'] = "wallet";
                    $data['user_id'] = $user_id;
                    $data['order_id'] = $order_id;
                    $data['type'] = "credit";
                    $data['txn_id'] = $txn_id;
                    $data['amount'] = $amount;
                    $data['status'] = "success";
                    $data['message'] = "Wallet refill successful";
                    Transaction::create($data);

                    if (updatebalance($amount, $user_id, 'add')) {
                        $response['error'] = false;
                        $response['transaction_status'] = 'success';
                        $response['message'] = "Wallet recharged successfully!";
                    } else {
                        $response['error'] = true;
                        $response['transaction_status'] = 'failed';
                        $response['message'] = "Wallet could not be recharged!";
                        Log::alert('Paystack Webhook | wallet recharge failure --> ' . var_export($event, true));
                    }
                    return response()->json($response);

                } else {

                    /* process the order and mark it as received */
                    $order = fetchOrders($order_id, '', '', '', '', '', 'o.id', 'DESC');
                    Log::alert('Paystack Webhook | order --> ' . var_export($order, true));

                    if (isset($order['order_data'][0]->user_id)) {
                        $user = fetchdetails('users', ['id' => $order['order_data'][0]->user_id]);


                        $overall_total = array(
                            'total_amount' => $order['order_data'][0]->total,
                            'delivery_charge' => $order['order_data'][0]->delivery_charge,
                            'tax_amount' => $order['order_data'][0]->total_tax_amount,
                            'tax_percentage' => $order['order_data'][0]->total_tax_percent,
                            'discount' => $order['order_data'][0]->promo_discount,
                            'wallet' => $order['order_data'][0]->wallet_balance,
                            'final_total' => $order['order_data'][0]->final_total,
                            'otp' => $order['order_data'][0]->otp,
                            'address' => $order['order_data'][0]->address,
                            'payment_method' => $order['order_data'][0]->payment_method
                        );

                        /* No need to add because the transaction is already added just update the transaction status */
                        if (!empty($transaction)) {
                            $transaction_id = $transaction[0]->id;
                            updateDetails(['status' => 'success'], ['id' => $transaction_id], 'transactions');
                        } else {
                            /* add transaction of the payment */
                            $amount = ($event['data']['amount']);
                            $data = [
                                'transaction_type' => 'transaction',
                                'user_id' => $user_id,
                                'order_id' => $order_id,
                                'type' => 'paystack',
                                'txn_id' => $txn_id,
                                'amount' => $amount,
                                'status' => 'success',
                                'message' => 'order placed successfully',
                            ];
                            Transaction::create($data);
                        }


                        $status = json_encode(array(array('received', date("d-m-Y h:i:sa"))));
                        updateDetails(['status' => $status], ['order_id' => $order_id], 'order_items');
                        updateDetails(['active_status' => 'received'], ['order_id' => $order_id], 'order_items');


                        sendCustomNotificationOnPaymentSuccess($order_id, $user_id);

                        Log::alert('Paystack Webhook inner Success --> ' . var_export($event, true));
                    }
                    Log::alert('Paystack Webhook order Success --> ' . var_export($event, true));
                }
            } else {
                /* No order ID found / sending 304 error to payment gateway so it retries wenhook after sometime*/
                Log::alert('Paystack Webhook | Order id not found --> ' . var_export($event, true));
            }

            $response['error'] = false;
            $response['transaction_status'] = $event['event'];
            $response['message'] = "Transaction successfully done";
            Log::alert('Paystack Transaction Successfully --> ' . var_export($event, true));
            return response()->json($response);

        } else if ($event['event'] == 'charge.dispute.create') {
            if (!empty($order_id) && is_numeric($order_id)) {
                $order = fetchOrders($order_id, '', '', '', '', '', 'o.id', 'DESC');

                if ($order['order_data']['0']->active_status == 'received' || $order['order_data']['0']->active_status == 'processed') {
                    updateDetails(['active_status' => 'awaiting'], ['order_id' => $order_id], 'order_items');
                }

                if (!empty($transaction)) {
                    $transaction_id = $transaction[0]->id;
                    updateDetails(['status' => 'pending'], ['id' => $transaction_id], 'transactions');
                }

                Log::alert('Paystack Transaction is Pending --> ' . var_export($event, true));
            }
        } else {

            if (!empty($order_id) && is_numeric($order_id)) {
                updateDetails(['active_status' => 'cancelled'], ['order_id' => $order_id], 'order_items');
            }
            /* No need to add because the transaction is already added just update the transaction status */
            if (!empty($transaction)) {
                $transaction_id = $transaction[0]['id'];
                updateDetails(['status' => 'failed'], ['id' => $transaction_id], 'transactions');
            }

            $response['error'] = true;
            $response['transaction_status'] = $event['event'];
            $response['message'] = "Transaction could not be detected.";
            Log::alert('Paystack Webhook | Transaction could not be detected --> ' . var_export($event, true));
            return response()->json($response);
        }


    }

    public function razorpay_webhook(Request $request)
    {
        $system_settings = getsettings('system_settings', true);
        $system_settings = json_decode($system_settings, true);
        $razorpay = new Razorpay;
        $request = file_get_contents('php://input');
        if ($request === false || empty($request)) {
            $this->edie("Error in reading Post Data");
        }
        $request = json_decode($request, true);

        $payment_method_settings = getsettings('payment_method', true);
        $payment_method_settings = json_decode($payment_method_settings, true);

        $key_id = $payment_method_settings['razorpay_key_id'] ?? "";
        $secret_key = $payment_method_settings['razorpay_secret_key'] ?? "";
        $secret_hash = $payment_method_settings['razorpay_webhook_secret_key'] ?? "";
        define('RAZORPAY_SECRET_KEY', $secret_hash);
        Log::alert('Razorpay IPN POST --> ' . var_export($request, true));
        Log::alert('Razorpay IPN SERVER --> ' . var_export($_SERVER, true));
        $http_razorpay_signature = isset($_SERVER['HTTP_X_RAZORPAY_SIGNATURE']) ? $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] : "";
        $txn_id = (isset($request['payload']['payment']['entity']['id'])) ? $request['payload']['payment']['entity']['id'] : "";
        if (!empty($request['payload']['payment']['entity']['id'])) {
            if (!empty($txn_id)) {
                $transaction = fetchDetails('transactions', ['txn_id' => $txn_id], '*');
            }
            $amount = $request['payload']['payment']['entity']['amount'];
            $amount = ($amount / 100);
        } else {
            $amount = 0;
            $currency = (isset($request['payload']['payment']['entity']['currency'])) ? $request['payload']['payment']['entity']['currency'] : "";
        }
        if (!empty($transaction)) {
            $order_id = $transaction[0]->order_id;
            Log::alert('razorpay Webhook | transaction order id --> ' . var_export($order_id, true));
            $user_id = $transaction[0]->user_id;
        } else {
            $order_id = 0;
            $order_id = (isset($request['payload']['order']['entity']['notes']['order_id'])) ? $request['payload']['order']['entity']['notes']['order_id'] : $request['payload']['payment']['entity']['notes']['order_id'];
            Log::alert('razorpay Webhook | webhook order id --> ' . var_export($order_id, true));
        }
        if ($http_razorpay_signature) {
            if ($request['event'] == 'payment.authorized') {
                $currency = (isset($request['payload']['payment']['entity']['currency'])) ? $request['payload']['payment']['entity']['currency'] : "INR";
                $response = $razorpay->capture_payment($amount * 100, $txn_id, $currency);

            }
            if ($request['event'] == 'payment.captured' || $request['event'] == 'order.paid') {
                if ($request['event'] == 'order.paid') {
                    $order_id = $request['payload']['order']['entity']['receipt'];

                    $order_data = fetchOrders($order_id, '', '', '', '', '', 'o.id', 'DESC');
                    $user_id = (isset($order_data['order_data'][0]->user_id)) ? $order_data['order_data'][0]->user_id : "";
                }
                if (!empty($order_id)) {
                    /* To do the wallet recharge if the order id is set in the patter */
                    if (strpos($order_id, "wallet-refill-user") !== false) {
                        if (!is_numeric($order_id) && strpos($order_id, "wallet-refill-user") !== false) {
                            $temp = explode("-", $order_id);
                            if (isset($temp[3]) && is_numeric($temp[3]) && !empty($temp[3] && $temp[3] != '')) {
                                $user_id = $temp[3];
                            } else {
                                $user_id = 0;
                            }
                        }

                        $data['transaction_type'] = "wallet";
                        $data['user_id'] = $user_id;
                        $data['order_id'] = $order_id;
                        $data['type'] = "credit";
                        $data['txn_id'] = $txn_id;
                        $data['amount'] = $amount;
                        $data['status'] = "success";
                        $data['message'] = "Wallet refill successful";
                        Log::alert('Razorpay user ID -  transaction data--> ' . var_export($data, true));
                        Transaction::create($data);
                        Log::alert('Razorpay user ID -  transaction data--> ' . var_export($txn_id, true));

                        if (updateBalance($amount, $user_id, 'add')) {
                            $response['error'] = false;
                            $response['transaction_status'] = $request['event'];
                            $response['message'] = "Wallet recharged successfully!";
                            Log::alert('Razorpay user ID - Wallet recharged successfully --> ' . var_export($order_id, true));
                        } else {
                            $response['error'] = true;
                            $response['transaction_status'] = $request['event'];
                            $response['message'] = "Wallet could not be recharged!";
                            Log::alert('Razorpay user ID - Wallet recharged successfully --> ' . var_export($request['event'], true));
                        }
                        return response()->json($response);
                    } else {

                        /* process the order and mark it as received */
                        $order = fetchOrders($order_id, '', '', '', '', '', 'o.id', 'DESC');

                        Log::alert('Razorpay order -   data--> ' . var_export($order, true));
                        if (isset($order['order_data'][0]->user_id)) {
                            $user = fetchdetails('users', ['id' => $order['order_data'][0]->user_id]);
                            $overall_total = array(
                                'total_amount' => $order['order_data'][0]->total,
                                'delivery_charge' => $order['order_data'][0]->delivery_charge,
                                'tax_amount' => $order['order_data'][0]->total_tax_amount,
                                'tax_percentage' => $order['order_data'][0]->total_tax_percent,
                                'discount' => $order['order_data'][0]->promo_discount,
                                'wallet' => $order['order_data'][0]->wallet_balance,
                                'final_total' => $order['order_data'][0]->final_total,
                                'otp' => $order['order_data'][0]->otp,
                                'address' => $order['order_data'][0]->address,
                                'payment_method' => $order['order_data'][0]->payment_method
                            );

                            /* No need to add because the transaction is already added just update the transaction status */
                            if (!empty($transaction)) {
                                $transaction_id = $transaction[0]->id;
                                updateDetails(['status' => 'success'], ['id' => $transaction_id], 'transactions');
                            } else {
                                /* add transaction of the payment */
                                $amount = ($request['payload']['payment']['entity']['amount'] / 100);
                                $data = [
                                    'transaction_type' => 'transaction',
                                    'user_id' => $order['order_data'][0]->user_id,
                                    'order_id' => $order_id,
                                    'type' => 'razorpay',
                                    'txn_id' => $txn_id,
                                    'amount' => $amount,
                                    'status' => 'success',
                                    'message' => 'order placed successfully',
                                ];
                                Transaction::create($data);
                            }
                            updateDetails(['active_status' => 'received'], ['order_id' => $order_id], 'order_items');
                            $status = json_encode(array(array('received', date("d-m-Y h:i:sa"))));
                            updateDetails(['status' => $status], ['order_id' => $order_id], 'order_items');

                            // place order custome notification on payment success
                            $user_id = (isset($order_data['order_data'][0]->user_id)) ? $order_data['order_data'][0]->user_id : "";
                            sendCustomNotificationOnPaymentSuccess($order_id, $user_id);

                            $product_variant_ids = [];
                            $qty = [];
                            $order_items = fetchDetails('order_items', ['order_id' => $order_id]);
                            foreach ($order_items as $items) {
                                array_push($product_variant_ids, $items->product_variant_id);
                                array_push($qty, $items->quantity);
                            }
                            updatestock($product_variant_ids, $qty, 'plus');
                        }
                    }
                } else {
                    Log::alert('Razorpay Order id not found --> ' . var_export($request, true));
                    /* No order ID found */
                }

                $response['error'] = false;
                $response['transaction_status'] = $request['event'];
                $response['message'] = "Transaction successfully done";
                return response()->json($response);

            } elseif ($request['event'] == 'payment.failed') {

                if (!empty($order_id)) {
                    updateDetails(['active_status' => 'cancelled'], ['order_id' => $order_id], 'order_items');
                }
                /* No need to add because the transaction is already added just update the transaction status */
                if (!empty($transaction)) {
                    $transaction_id = $transaction[0]['id'];
                    updateDetails(['status' => 'failed'], ['id' => $transaction_id], 'transactions');
                }
                $response['error'] = true;
                $response['transaction_status'] = $request['event'];
                $response['message'] = "Transaction is failed. ";
                Log::alert('Razorpay Webhook | Transaction is failed --> ' . var_export($request['event'], true));
                return response()->json($response);

            } elseif ($request['event'] == 'payment.authorized') {
                if (!empty($order_id)) {
                    updateDetails(['active_status' => 'awaiting'], ['order_id' => $order_id], 'order_items');
                }
            } elseif ($request['event'] == "refund.processed") {
                //Refund Successfully
                $transaction = fetchdetails('transactions', ['txn_id' => $request['payload']['refund']['entity']['payment_id']]);
                if (empty($transaction)) {

                }
                process_refund($transaction[0]['id'], $transaction[0]['status']);
                $response['error'] = false;
                $response['transaction_status'] = $request['event'];
                $response['message'] = "Refund successfully done. ";
                Log::alert('Razorpay Webhook | Transaction is failed --> ' . var_export($request['event'], true));
                return response()->json($response);

            } elseif ($request['event'] == "refund.failed") {
                $response['error'] = true;
                $response['transaction_status'] = $request['event'];
                $response['message'] = "Refund is failed. ";
                Log::alert('Razorpay Webhook | Payment refund failed --> ' . var_export($request['event'], true));
                return response()->json($response);

            } else {
                $response['error'] = true;
                $response['transaction_status'] = $request['event'];
                $response['message'] = "Transaction could not be detected.";
                Log::alert('Razorpay Webhook | Transaction could not be detected --> ' . var_export($request['event'], true));
                return response()->json($response);

            }
        } else {
            Log::alert('razorpay Webhook | Invalid Server Signature  --> ' . var_export($request['event'], true));
            return false;
        }
    }
//    public function stripe_webhook(Request $request)
//    {
//        $system_settings = getsettings('system_settings', true);
//        $system_settings = json_decode($system_settings, true);
//        $stripe = new Stripe;
//        $credentials = getsettings('payment_method', true);
//        $credentials = json_decode($credentials, true);
//
//        $request_body = file_get_contents('php://input');
//
//        $event = json_decode($request_body, FALSE);
//
//        Log::alert("stripe webhook=>" . $request);
//
//        if (!empty($event->data->object)) {
//            $txn_id = (isset($event->data->object->payment_intent)) ? $event->data->object->payment_intent : "";
//            if (!empty($txn_id)) {
//                $transaction = fetchDetails('transactions', ['txn_id' => $txn_id], '*');
//                Log::alert('transaction --> ' . var_export($transaction, true));
//                if (isset($transaction) && !empty($transaction)) {
//                    $order_id = $transaction[0]->order_id;
//                    $user_id = $transaction[0]->user_id;
//                } else {
//                    $order_id = $event->data->object->metadata->order_id;
//                    if (is_numeric($order_id)) {
//                        $order_data = fetchOrders($order_id, '', '', '', '', '', 'o.id', 'DESC');
//                        $user_id = $order_data['order_data'][0]->user_id;
//                    }
//                }
//            }
//            $amount = $event->data->object->amount;
//            $currency = $event->data->object->currency;
//            $balance_transaction = $event->data->object->balance_transaction;
//        } else {
//            $order_id = 0;
//            $amount = 0;
//            $currency = (isset($event->data->object->currency)) ? $event->data->object->currency : "";
//            $balance_transaction = 0;
//        }
//        /* Wallet refill has unique format for order ID - wallet-refill-user-{user_id}-{system_time}-{3 random_number}  */
//        if (empty($order_id)) {
//            $order_id = (!empty($event->data->object->metadata->order_id) && isset($event->data->object->metadata->order_id)) ? $event->data->object->metadata->order_id : 0;
//        }
//
//        if (!is_numeric($order_id) && strpos($order_id, "wallet-refill-user") !== false) {
//            $temp = explode("-", $order_id);
//            if (isset($temp[3]) && is_numeric($temp[3]) && !empty($temp[3] && $temp[3] != '')) {
//                $user_id = $temp[3];
//            } else {
//                $user_id = 0;
//            }
//        }
//
//        $http_stripe_signature = isset($_SERVER['HTTP_STRIPE_SIGNATURE']) ? $_SERVER['HTTP_STRIPE_SIGNATURE'] : "";
//        $result = $stripe->construct_event($request_body, $http_stripe_signature, $credentials['stripe_webhook_secret_key']);
//        Log::alert('Stripe order id --> ' . var_export($result, true));
//        Log::alert('http_stripe_signature--> ' . var_export($http_stripe_signature, true));
//
//
//        if ($result == "Matched") {
//            if ($event->type == 'charge.succeeded') {
//                if (!empty($order_id)) {
//                    /* To do the wallet recharge if the order id is set in the above mentioned pattern */
//                    if (strpos($order_id, "wallet-refill-user") !== false) {
//                        $data['transaction_type'] = "wallet";
//                        $data['user_id'] = $user_id;
//                        $data['order_id'] = $order_id;
//                        $data['type'] = "credit";
//                        $data['txn_id'] = $txn_id;
//                        $data['amount'] = $amount / 100;
//                        $data['status'] = "success";
//                        $data['message'] = "Wallet refill successful";
//                        Log::alert('Stripe order id --> ' . var_export($data, true));
//
//                        Transaction::create($data);
//
//                        if (updateBalance($amount / 100, $user_id, 'add')) {
//                            $response['error'] = false;
//                            $response['transaction_status'] = $event->type;
//                            $response['message'] = "Wallet recharged successfully!";
//                        } else {
//                            $response['error'] = true;
//                            $response['transaction_status'] = $event->type;
//                            $response['message'] = "Wallet could not be recharged!";
//                            Log::alert('Stripe Webhook | wallet recharge failure --> ' . var_export($event, true));
//                        }
//                        return response()->json($response);
//                    } else {
//                        /* process the order and mark it as received */
//                        $order = fetchOrders($order_id, '', '', '', '', '', 'o.id', 'DESC');
//                        if (isset($order['order_data'][0]->user_id)) {
//                            $user = fetchDetails('users', ['id' => $order['order_data'][0]->user_id]);
//                            $overall_total = array(
//                                'total_amount' => $order['order_data'][0]->total,
//                                'delivery_charge' => $order['order_data'][0]->delivery_charge,
//                                'tax_amount' => $order['order_data'][0]->total_tax_amount,
//                                'tax_percentage' => $order['order_data'][0]->total_tax_percent,
//                                'discount' => $order['order_data'][0]->promo_discount,
//                                'wallet' => $order['order_data'][0]->wallet_balance,
//                                'final_total' => $order['order_data'][0]->final_total,
//                                'otp' => $order['order_data'][0]->otp,
//                                'address' => $order['order_data'][0]->address,
//                                'payment_method' => $order['order_data'][0]->payment_method
//                            );
//
//                            /* No need to add because the transaction is already added just update the transaction status */
//                            if (!empty($transaction)) {
//                                $transaction_id = $transaction[0]->id;
//                                updateDetails(['status' => 'success'], ['txn_id' => $txn_id], 'transactions');
//                            } else {
//                                /* add transaction of the payment */
//                                $amount = ($event->data->object->amount / 100);
//                                $data = [
//                                    'transaction_type' => 'transaction',
//                                    'user_id' => $user_id,
//                                    'order_id' => $order_id,
//                                    'type' => 'stripe',
//                                    'txn_id' => $txn_id,
//                                    'amount' => $amount,
//                                    'status' => 'success',
//                                    'message' => 'order placed successfully',
//                                ];
//                                Transaction::create($data);
//                                ;
//                            }
//                            updateDetails(['active_status' => 'received'], ['order_id' => $order_id], 'order_items');
//
//                            $status = json_encode(array(array('received', date("d-m-Y h:i:sa"))));
//                            updateDetails(['status' => $status], ['order_id' => $order_id], 'order_items');
//                            sendCustomNotificationOnPaymentSuccess($order_id, $user_id);
//                        }
//                    }
//                } else {
//                    /* No order ID found / sending 304 error to payment gateway so it retries wenhook after sometime*/
//                    Log::alert('Stripe Webhook | Order id not found --> ' . var_export($event, true));
//                }
//                $response['error'] = false;
//                $response['transaction_status'] = $event->type;
//                $response['message'] = "Transaction successfully done";
//                return response()->json($response);
//
//            } elseif ($event->type == 'charge.failed') {
//                $order = fetchOrders($order_id, '', '', '', '', '', 'o.id', 'DESC');
//                if (!empty($order_id)) {
//                    updateDetails(['active_status' => 'cancelled'], ['order_id' => $order_id], 'order_items');
//                }
//                /* No need to add because the transaction is already added just update the transaction status */
//                if (!empty($transaction)) {
//                    $transaction_id = $transaction[0]['id'];
//                    updateDetails(['status' => 'failed'], ['id' => $transaction_id], 'transactions');
//                }
//                $response['error'] = true;
//                $response['transaction_status'] = $event->type;
//                $response['message'] = "Transaction is failed. ";
//                Log::alert('Stripe Webhook | Transaction is failed --> ' . var_export($event, true));
//                return response()->json($response);
//
//            } elseif ($event->type == 'charge.pending') {
//                $response['error'] = false;
//                $response['transaction_status'] = $event->type;
//                $response['message'] = "Waiting for customer to finish transaction ";
//                Log::alert('Stripe Webhook | Waiting customer to finish transaction --> ' . var_export($event, true));
//                return response()->json($response);
//
//            } elseif ($event->type == 'charge.expired') {
//                if (!empty($order_id)) {
//                    updateDetails(['active_status' => 'cancelled'], ['order_id' => $order_id], 'order_items');
//                }
//                /* No need to add because the transaction is already added just update the transaction status */
//                if (!empty($transaction)) {
//                    $transaction_id = $transaction[0]['id'];
//                    updateDetails(['status' => 'expired'], ['id' => $transaction_id], 'transactions');
//                }
//                $response['error'] = true;
//                $response['transaction_status'] = $event->type;
//                $response['message'] = "Transaction is expired.";
//                Log::alert('Stripe Webhook | Transaction is expired --> ' . var_export($event, true));
//                return response()->json($response);
//
//            } elseif ($event->type == 'charge.refunded') {
//                if (!empty($order_id)) {
//                    updateDetails(['active_status' => 'cancelled'], ['order_id' => $order_id], 'order_items');
//                }
//                /* No need to add because the transaction is already added just update the transaction status */
//                if (!empty($transaction)) {
//                    $transaction_id = $transaction[0]['id'];
//                    updateDetails(['status' => 'refunded'], ['id' => $transaction_id], 'transactions');
//                }
//                $response['error'] = true;
//                $response['transaction_status'] = $event->type;
//                $response['message'] = "Transaction is refunded.";
//                Log::alert('Stripe Webhook | Transaction is refunded --> ' . var_export($event, true));
//                return response()->json($response);
//
//            } else {
//                $response['error'] = true;
//                $response['transaction_status'] = $event->type;
//                $response['message'] = "Transaction could not be detected.";
//                Log::alert('Stripe Webhook | Transaction could not be detected --> ' . var_export($event, true));
//                return response()->json($response);
//
//            }
//        } else {
//            Log::alert('Stripe Webhook | Invalid Server Signature  --> ' . var_export($result, true));
//            Log::alert('Stripe Webhook | Order id  --> ' . var_export($order_id, true));
//
//        }
//
//    }

    public function stripe_webhook(Request $request)
    {
        // Fetch system settings and Stripe credentials
        $credentials = json_decode(getsettings('payment_method', true), true);

        // Retrieve the request body and Stripe signature
        $request_body = @file_get_contents('php://input');
        $event = json_decode($request_body, FALSE);
        $http_stripe_signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? "";

        // Verify the Stripe Webhook signature
//        try {
//            $event = \Stripe\Webhook::constructEvent(
//                $request_body,
//                $http_stripe_signature,
//                $credentials['stripe_webhook_secret_key']
//            );
//        } catch (\UnexpectedValueException $e) {
//            Log::alert('Invalid Payload: ' . $e->getMessage());
//            return response()->json(['error' => true, 'message' => 'Invalid payload'], 400);
//        } catch (\Stripe\Exception\SignatureVerificationException $e) {
//            Log::alert('Invalid Signature: ' . $e->getMessage());
//            return response()->json(['error' => true, 'message' => 'Invalid signature'], 400);
//        }

        Log::alert('Stripe Webhook Event: ' . var_export($event, true));

        // Extract important data from the event
        $type = $event->data->object->metadata->type ?? "";
        $txn_id = $event->data->object->payment_intent ?? "";
        $amount = $event->data->object->metadata->amount ?? 0;

        // Validate type
        if (empty($type) || !in_array($type, ['wallet', 'order'])) {
            Log::alert('Invalid Type in Metadata: ' . $type);
            return response()->json(['error' => true, 'message' => 'Invalid type in metadata'], 400);
        }

        if ($type === 'wallet') {
            // Check for duplicate transactions (only for wallet)
            $existing_transaction = fetchDetails('transactions', ['txn_id' => $txn_id], '*');
            if (!empty($existing_transaction)) {
                Log::alert('Duplicate Transaction Detected: ' . $txn_id);
                return response()->json(['error' => false, 'message' => 'Duplicate transaction'], 200);
            }
        } elseif ($type === 'order') {
            // Add a delay of 30 seconds for order processing
            sleep(30);

            // Fetch the transaction to get the order ID
            $transaction = fetchDetails('transactions', ['txn_id' => $txn_id], '*');
            if (empty($transaction)) {
                Log::alert('Transaction Not Found for Order: ' . $txn_id);
                return response()->json(['error' => true, 'message' => 'Transaction not found'], 400);
            }

            $order_id = $transaction[0]->order_id;
        }

        // Handle specific payment events
        switch ($event->type) {
            case 'payment_intent.succeeded':
            case 'checkout.session.completed':
            case 'charge.succeeded':
                if ($type === 'wallet') {
                    // Wallet Refill Logic
                    $user_id = $event->data->object->metadata->user_id ?? 0;

                    if (empty($user_id)) {
                        Log::alert('User ID not found for Wallet Refill: ' . $txn_id);
                        return response()->json(['error' => true, 'message' => 'User ID not found'], 400);
                    }

                    $data = [
                        'transaction_type' => 'wallet',
                        'user_id' => $user_id,
                        'order_id' => "",
                        'type' => 'credit',
                        'txn_id' => $txn_id,
                        'amount' => $amount,
                        'status' => 'success',
                        'message' => 'Wallet refill successful',
                    ];

                    Transaction::create($data);

                    if (updateBalance($amount, $user_id, 'add')) {
                        Log::alert('Wallet Recharged Successfully: ' . $txn_id);
                        return response()->json(['error' => false, 'message' => 'Wallet recharged successfully'], 200);
                    } else {
                        Log::alert('Wallet Recharge Failed: ' . $txn_id);
                        return response()->json(['error' => true, 'message' => 'Wallet recharge failed'], 500);
                    }
                } elseif ($type === 'order') {
                    // Order Payment Success Logic
                    $order = fetchOrders($order_id, '', '', '', '', '', 'o.id', 'DESC');
                    if (!empty($order)) {
                        updateDetails(['active_status' => 'received'], ['order_id' => $order_id], 'order_items');
                        $status = json_encode([['received', date("d-m-Y h:i:sa")]]);
                        updateDetails(['status' => $status], ['order_id' => $order_id], 'order_items');

                        // Update transaction status
                        updateDetails(['status' => 'success'], ['txn_id' => $txn_id], 'transactions');
                        Log::alert('Order updated to Received: ' . $txn_id);
                        sendCustomNotificationOnPaymentSuccess($order_id, $order[0]->user_id);
                    }
                    Log::alert('order not found --> ' . var_export($order, true));
                }
                break;

            case 'payment_intent.payment_failed':
            case 'charge.failed':
                if ($type === 'wallet') {
                    // Wallet Payment Failed Logic
                    Log::alert('Wallet Payment Failed: ' . $txn_id);
                    Transaction::create([
                        'transaction_type' => 'wallet',
                        'user_id' => $event->data->object->metadata->user_id ?? 0,
                        'order_id' => "",
                        'type' => 'credit',
                        'txn_id' => $txn_id,
                        'amount' => $amount,
                        'status' => 'failed',
                        'message' => 'Wallet payment failed',
                    ]);
                } elseif ($type === 'order') {
                    // Order Payment Failed Logic
                    if (!empty($order_id)) {
                        updateDetails(['active_status' => 'cancelled'], ['order_id' => $order_id], 'order_items');
                    }

                    // Update transaction status
                    updateDetails(['status' => 'failed'], ['txn_id' => $txn_id], 'transactions');
                    Log::alert('Order updated to Cancelled: ' . $txn_id);
                }
                Log::alert('Payment Failed: ' . $txn_id);

            case 'charge.refunded':
                if ($type === 'wallet') {
                    // Wallet Payment Refunded Logic
                    Log::alert('Wallet Payment Refunded: ' . $txn_id);

                    Transaction::create([
                        'transaction_type' => 'wallet',
                        'user_id' => $event->data->object->metadata->user_id ?? 0,
                        'order_id' => "",
                        'type' => 'debit',
                        'txn_id' => $txn_id,
                        'amount' => $amount,
                        'status' => 'refunded',
                        'message' => 'Wallet payment refunded',
                    ]);

                    updateBalance($amount, $event->data->object->metadata->user_id ?? 0, 'subtract');
                } elseif ($type === 'order') {
                    // Order Payment Refunded Logic
                    if (!empty($order_id)) {
                        updateDetails(['active_status' => 'refunded'], ['order_id' => $order_id], 'order_items');
                    }

                    // Update transaction status
                    updateDetails(['status' => 'refunded'], ['txn_id' => $txn_id], 'transactions');
                    Log::alert('Order updated to Refunded: ' . $txn_id);
                }
                Log::alert('Payment refunded: ' . $txn_id);

            case 'charge.expired':
                if ($type === 'wallet') {
                    // Wallet Payment Expired Logic
                    Log::alert('Wallet Payment Expired: ' . $txn_id);

                    Transaction::create([
                        'transaction_type' => 'wallet',
                        'user_id' => $event->data->object->metadata->user_id ?? 0,
                        'order_id' => "",
                        'type' => 'credit',
                        'txn_id' => $txn_id,
                        'amount' => $amount,
                        'status' => 'expired',
                        'message' => 'Wallet payment expired',
                    ]);
                } elseif ($type === 'order') {
                    // Order Payment Expired Logic
                    if (!empty($order_id)) {
                        updateDetails(['active_status' => 'expired'], ['order_id' => $order_id], 'order_items');
                    }

                    // Update transaction status
                    updateDetails(['status' => 'expired'], ['txn_id' => $txn_id], 'transactions');
                    Log::alert('Order updated to Expired: ' . $txn_id);
                }
                Log::alert('Payment expired: ' . $txn_id);

            default:
                Log::alert('Unhandled Event Type: ' . $event->type);
                return response()->json(['error' => true, 'message' => 'Unhandled event type'], 400);
        }
    }

    public function spr_webhook(Request $request)
    {

    }
}
