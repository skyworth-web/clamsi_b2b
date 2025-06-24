<?php

namespace App\Http\Controllers;

use App\Libraries\Paystack;
use Illuminate\Http\Request;
use App\Models\PaymentRequest;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    function refill(Request $request, TransactionController $transactionController)
    {
        if ($request->has('res')) {
            $res = $request->input('res');
            $request = new Request($res);
            $request['add_amount'] = $request['amount'];
        }
        $user_id = Auth::user()->id ?? "";
        if ($user_id == "") {
            $response = [
                'error' => true,
                'message' => 'Please Login first.',
                'code' => 102,
            ];
            return response()->json($response);
        }
        $validated = Validator::make($request->all(), [
            'add_amount' => 'required|numeric',
            'payment_method' => 'required',
        ]);

        if ($validated->fails()) {
            $response = [
                'error' => true,
                'message' => $validated->errors()->all(),
                'code' => 102,
            ];
            return response()->json($response);
        }

        if ($request['payment_method'] == 'phonepe') {
            $transaction_id = $request['transaction_id'];
        } elseif ($request['payment_method'] == 'paypal') {
            $transaction_id = $request['paypal_transaction_id'];
        } elseif ($request['payment_method'] == 'paystack') {
            $validator = Validator::make($request->all(), [
                'paystack_reference' => 'required',
            ]);
            if ($validator->fails()) {
                $response = [
                    'error' => true,
                    'message' => $validator->errors()->all(),
                    'code' => 102,
                ];
                return response()->json($response);
            }
            $paystack = new Paystack();
            $payment = $paystack->verify_transaction($request['paystack_reference']);
            if (!empty($payment)) {
                $payment = json_decode($payment, true);
                if (isset($payment['data']['status']) && $payment['data']['status'] == 'success') {
                    $response['error'] = false;
                    $response['message'] = "Payment is successful";
                    $response['amount'] = (isset($payment['data']['amount'])) ? $payment['data']['amount'] / 100 : 0;
                    $response['data'] = $payment;
                } elseif (isset($payment['data']['status']) && $payment['data']['status'] != 'success') {
                    $response['error'] = true;
                    $response['message'] = "Payment is " . ucwords($payment['data']['status']) . "! ";
                    $response['amount'] = (isset($payment['data']['amount'])) ? $payment['data']['amount'] / 100 : 0;
                    $response['data'] = $payment;
                    return $response;
                } else {
                    $response['error'] = true;
                    $response['message'] = "Payment is unsuccessful! ";
                    $response['amount'] = (isset($payment['data']['amount'])) ? $payment['data']['amount'] / 100 : 0;
                    $response['data'] = $payment;
                    return $response;
                }
            } else {
                $response['error'] = true;
                $response['message'] = "Payment not found by the transaction ID!";
                $response['amount'] = 0;
                $response['data'] = [];
                return $response;
            }
            $transaction_id = $request['paystack_reference'];
            $status = 'success';
            $message = 'Payment Successfully';
        } elseif ($request['payment_method'] == 'stripe') {
            $find_tnx = Transaction::where('txn_id', $request['stripe_payment_id'])->first();
            if ($find_tnx) {
                $response = [
                    'error' => true,
                    'message' => 'Transaction Already Completed',
                ];
                return response()->json($response);
            }
            $transaction_id = $request['stripe_payment_id'];
            $status = 'success';
            $message = 'Payment Successfully';
        } elseif ($request['payment_method'] == 'razorpay') {
            $validated = Validator::make($request->all(), [
                'razorpay_payment_id' => 'required',
            ]);

            if ($validated->fails()) {
                $response = [
                    'error' => true,
                    'message' => $validated->errors()->all(),
                    'code' => 102,
                ];
                return response()->json($response);
            }
            $res = verifyPaymentTransaction($request['razorpay_payment_id'], $request['payment_method']);
            if ($res['error'] == true) {
                $response = [
                    'error' => true,
                    'message' => $res['message'],
                    'data' => $res['data']
                ];
                return response()->json($response);
            }
            $status = 'success';
            $transaction_id = $request['razorpay_payment_id'];
            $message = $res['message'];
        }
        $data = new Request([
            'status' => $status ?? "awaiting",
            'txn_id' => $transaction_id ?? null,
            'message' => $message ?? 'Payment Is Pending',
            'user_id' => $user_id,
            'transaction_type' => 'wallet',
            // 'type' => $request['payment_method'],
            'type' => 'credit',
            'amount' => $request['add_amount'],
        ]);
        if ($request['payment_method'] == 'paystack' || $request['payment_method'] == 'stripe' || $request['payment_method'] == 'razorpay') {
            if (!updateBalance($request['add_amount'], $user_id, 'add')) {
                $response = [
                    'error' => true,
                    'message' => 'Wallet couldn\'t Update',
                ];
                return response()->json($response);
            }
        }
        $transactionController->store($data);
        $response = [
            'error' => false,
            'message' => 'Wallet Refill Successfully',
        ];
        return response()->json($response);
    }

    function withdrawal(Request $request)
    {
        $user = Auth::user() ?? "";
        $balance = $user['balance'];
        if ($user->id == "") {
            $response = [
                'error' => true,
                'message' => 'Please Login first.',
                'code' => 102,
            ];
            return response()->json($response);
        }
        $validated = Validator::make($request->all(), [
            'amount_requested' => 'required|numeric',
            'payment_address' => 'required',
        ]);

        if ($validated->fails()) {
            $response = [
                'error' => true,
                'message' => $validated->errors()->all(),
                'code' => 102,
            ];
            return response()->json($response);
        }

        $system_settings = getSettings('system_settings', true);
        $system_settings = json_decode($system_settings, true);

        if ($balance < $request['amount_requested']) {
            $response = [
                'error' => true,
                'message' => 'unfortunately you don\'t have enough funds to Withdraw',
            ];
            return response()->json($response);
        }
        if ($request['amount_requested'] <= 0) {
            $response = [
                'error' => true,
                'message' => 'Please Enter Correct Amount',
            ];
            return response()->json($response);
        }
        $data = [
            'payment_type' => 'customer',
            'payment_address' => $request['payment_address'],
            'amount_requested' =>  $request['amount_requested'],
            'user_id' => $user->id,
        ];

        if (PaymentRequest::create($data)) {
            updateBalance($request['amount_requested'], $user->id, 'deduct');
            $balance = $user['balance'] - $request['amount_requested'];
            $response = [
                'error' => false,
                'message' => 'Withdrawal Request Sent Successfully.',
                'balance' => $balance,
            ];
            return response()->json($response);
        }
        $response = [
            'error' => true,
            'message' => 'Something Went Wrong Please Try Again later.',
        ];
        return response()->json($response);
    }
}