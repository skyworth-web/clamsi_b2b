<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    public function store(Request $request)
    {

        $transaction_type = empty($request['transaction_type']) ? 'transaction' : $request['transaction_type'];

        $trans_data = [
            'transaction_type' => $transaction_type,
            'user_id' => $request['user_id'] ?? null,
            'order_id' => $request['order_id'] ?? '',
            'order_item_id' => $request['order_item_id'] ?? null,
            'type' => strtolower($request['type'] ?? ''),
            'txn_id' => $request['txn_id'] ??  '',
            'amount' => $request['amount'] ?? 0,
            'status' => $request['status'] ?? '',
            'message' => $request['message'] ?? '',
        ];

        Transaction::create($trans_data);
    }

    public function get_transactions($id = '', $userId = '', $transaction_type = '', $search = '', $offset = 0, $limit = 25, $sort = 'id', $order = 'DESC', $type = '')
    {

        $count_result = DB::table('transactions')->select(DB::raw('COUNT(id) as total'));

        if (!empty($userId)) {
            $count_result->where('user_id', $userId);
        }

        if ($transaction_type !== '') {
            $count_result->where('transaction_type', $transaction_type);
        }

        if ($type !== '') {
            $count_result->where('type', $type);
        }

        if ($id !== '') {

            $count_result->where('id', $id);
        }

        if (!empty($search)) {
            $count_result->where(function ($query) use ($search) {
                $query->orWhere('id', 'LIKE', "%{$search}%")
                    ->orWhere('transaction_type', 'LIKE', "%{$search}%")
                    ->orWhere('type', 'LIKE', "%{$search}%")
                    ->orWhere('order_id', 'LIKE', "%{$search}%")
                    ->orWhere('txn_id', 'LIKE', "%{$search}%")
                    ->orWhere('amount', 'LIKE', "%{$search}%")
                    ->orWhere('status', 'LIKE', "%{$search}%")
                    ->orWhere('message', 'LIKE', "%{$search}%")
                    ->orWhere('transaction_date', 'LIKE', "%{$search}%")
                    ->orWhere('created_at', 'LIKE', "%{$search}%");
            });
        }


        $total_count = $count_result->get()->first()->total;

        $transaction_result = DB::table('transactions');

        if (!empty($userId)) {
            $transaction_result->where('user_id', $userId);
        }

        if ($transaction_type !== '') {
            $transaction_result->where('transaction_type', $transaction_type);
        }

        if ($type !== '') {
            $transaction_result->where('type', $type);
        }

        if ($id !== '') {
            $transaction_result->where('id', $id);
        }

        if (!empty($search)) {
            $transaction_result->where(function ($query) use ($search) {
                $query->orWhere('id', 'LIKE', "%{$search}%")
                    ->orWhere('transaction_type', 'LIKE', "%{$search}%")
                    ->orWhere('type', 'LIKE', "%{$search}%")
                    ->orWhere('order_id', 'LIKE', "%{$search}%")
                    ->orWhere('txn_id', 'LIKE', "%{$search}%")
                    ->orWhere('amount', 'LIKE', "%{$search}%")
                    ->orWhere('status', 'LIKE', "%{$search}%")
                    ->orWhere('message', 'LIKE', "%{$search}%")
                    ->orWhere('transaction_date', 'LIKE', "%{$search}%")
                    ->orWhere('date_created', 'LIKE', "%{$search}%");
            });
        }


        $transactions = $transaction_result->orderBy($sort, $order)->offset($offset)->limit($limit)->get();

        $transactions = json_decode(json_encode($transactions), true);

        foreach ($transactions as &$transaction) {
            $transaction['order_id'] = $transaction['order_id'] ?? '';
            $transaction['order_item_id'] = $transaction['order_item_id'] ?? '';
            $transaction['txn_id'] = $transaction['txn_id'] ?? '';
            $transaction['status'] = $transaction['status'] ?? '';
            $transaction['payu_txn_id'] = $transaction['payu_txn_id'] ?? '';
            $transaction['currency_code'] = $transaction['currency_code'] ?? '';
            $transaction['payer_email'] = $transaction['payer_email'] ?? '';
        }

        return [
            'data' => $transactions,
            'total' => $total_count,
        ];
    }

    public function edit_transactions(Request $request)
    {

        $rules = [
            'status' => 'required',
            'txn_id' => 'required',
            'id' => 'required|exists:transactions,id',
        ];

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }
        // Retrieve the current status of the transaction
        $currentStatus = DB::table('transactions')->where('id', $request->id)->value('status');

        // Check if the new status is greater than or equal to the current status
        $statuses = ['awaiting', 'success', 'failed'];
        if (array_search($currentStatus, $statuses) <= array_search($request->status, $statuses)) {
            $t_data = [
                'id' => $request->id,
                'status' => $request->status,
                'txn_id' => $request->txn_id,
                'message' => $request->message,
            ];

            if (updateDetails($t_data, ['id' => $request->id], 'transactions')) {
                return response()->json([
                    'error' => false,
                    'message' => labels('admin_labels.transaction_updated_successfully', 'Transaction Updated Successfully')
                ]);
            } else {
                return response()->json([
                    'errors' => true,
                    'message' => labels('admin_labels.something_went_wrong', 'Something went wrong')
                ]);
            }
        } else {
            return response()->json([
                'errors' => true,
                'message' => labels('admin_labels.can_not_update_to_lower_status', 'Cannot update to a lower status.')
            ]); // HTTP status code 422 for Unprocessable Entity
        }
    }
}
