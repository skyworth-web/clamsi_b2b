<?php

namespace App\Livewire\Payments;

use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Status extends Component
{
    public function render(Request $request)
    {
        $breadcrumb = 'Payment Status';
        $payment_response = $this->payment_response($request);
        if (isset($request->query()['response']) && !empty($request->query()['response'])) {
            $res = $request->query()['response'];
            if ($res == "wallet_success") {
                $payment_response = 'wallet_success';
            } elseif ($res == "wallet_failed") {
                $payment_response = 'wallet_failed';
            } elseif ($res == "order_success") {
                $payment_response = 'order_success';
            } elseif ($res == "order_failed") {
                $payment_response = 'order_failed';
            }
        }
        return view('livewire.' . config('constants.theme') . '.payments.status', [
            'breadcrumb' => $breadcrumb,
            'payment_response' => $payment_response
        ])->title("Payment Status |");
    }

    public function payment_response($request)
    {
        $transaction = fetchDetails('transactions', ['txn_id' => $request['transactionId']]);
        if ($transaction != []) {

            if ($transaction[0]->type == "phonepe") {
                Auth::loginUsingId($transaction[0]->user_id);
            }
            $status = $request['code'];
            $payment_response = "";
            if ($status == 'PAYMENT_SUCCESS' || $status == "INTERNAL_SERVER_ERROR") {
                if ($transaction[0]->transaction_type == 'wallet') {
                    return $payment_response = 'wallet_success';
                }
                return $payment_response = 'order_success';
            } else {
                if ($transaction[0]->transaction_type == 'wallet') {
                    return $payment_response = 'wallet_failed';
                }
                return $payment_response = 'order_failed';
            }
        }
    }
}
