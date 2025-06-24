<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function store(Request $request)
    {
        $transaction_type = empty($request['transaction_type']) ? 'transaction' : $request['transaction_type'];

        $trans_data = [
            'transaction_type' => $transaction_type,
            'user_id' => $request['user_id'],
            'order_id' => $request['order_id'],
            'order_item_id' => $request['order_item_id'],
            'type' => strtolower($request['type']),
            'txn_id' => $request['txn_id'],
            'amount' => $request['amount'],
            'status' => $request['status'],
            'message' => $request['message'],
        ];
        Transaction::create($trans_data);
    }

    public function update_transaction($txn_id, $data)
    {
        $transaction = Transaction::where('txn_id', $txn_id)->first();
        return $transaction->update($data);
    }
}
