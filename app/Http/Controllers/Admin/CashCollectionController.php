<?php

namespace App\Http\Controllers\Admin;

use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashCollectionController extends Controller
{
    public function index()
    {
        $store_id = getStoreId();
        $delivery_boys = User::where('active', 1)->where('role_id', 3)->get();

        return view('admin.pages.tables.manage_cash_collection', ['delivery_boys' => $delivery_boys]);
    }

    public function list(Request $request, $user_id = '')
    {
        $offset = request()->input('search') || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = $request->input('limit', 25);
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'ASC');


        $transactions = Transaction::select('transactions.*', 'users.username as name', 'users.mobile', 'users.cash_received')
            ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
            ->where('transactions.status', 1);

        // Date range filter
        if ($request->has('start_date') && $request->has('end_date')) {
            $transactions->whereDate('transactions.transaction_date', '>=', $request->input('start_date'))
                ->whereDate('transactions.transaction_date', '<=', $request->input('end_date'));
        }


        if ($request->has('search') && trim($request->input('search')) !== '') {
            $search = trim($request->input('search'));
            $transactions->where(function ($query) use ($search) {
                $query->where('transactions.id', 'LIKE', '%' . $search . '%')
                    ->orWhere('transactions.amount', 'LIKE', '%' . $search . '%')
                    ->orWhere('transactions.created_at', 'LIKE', '%' . $search . '%')
                    ->orWhere('users.username', 'LIKE', '%' . $search . '%')
                    ->orWhere('users.mobile', 'LIKE', '%' . $search . '%')
                    ->orWhere('users.email', 'LIKE', '%' . $search . '%')
                    ->orWhere('transactions.type', 'LIKE', '%' . $search . '%')
                    ->orWhere('transactions.status', 'LIKE', '%' . $search . '%');
            });
        }

        // Filter by delivery boy
        if ($request->has('filter_d_boy') && $request->input('filter_d_boy') !== null && $request->input('filter_d_boy') !== '') {
            $transactions->where('users.id', $request->input('filter_d_boy'));
        }

        // Filter by status
        if ($request->has('filter_status') && $request->input('filter_status') !== '') {
            $transactions->where('transactions.type', $request->input('filter_status'));
        }

        // Filter by user ID
        if (!empty($user_id)) {
            $transactions->where('users.id', $user_id);
        }

        // Clone the transactions query to count total records
        $total = $transactions->count();

        // Paginate and order the results
        $txn_search_res = $transactions->orderBy($sort, $order)
            ->offset($offset)
            ->limit($limit)
            ->get();

        // Prepare the response data
        $bulkData = [
            'total' => $total,
            'rows' => [],
        ];

        foreach ($txn_search_res as $row) {
            $tempRow = [
                'id' => $row['id'],
                'name' => $row['name'],
                'mobile' => $row['mobile'],
                'order_id' => $row['order_id'],
                'cash_received' => formateCurrency(formatePriceDecimal($row['cash_received'])),
                'type' => ($row['type'] == 'delivery_boy_cash') ? '<span class="badge bg-primary">Received</span>' : '<span class="badge bg-success">Collected</span>',
                'amount' => $row['amount'],
                'message' => $row['message'],
                'transaction_date' => Carbon::parse($row['transaction_date'])->format('d-m-Y'),
                'date' => Carbon::parse($row['created_at'])->format('d-m-Y'),
            ];
            $bulkData['rows'][] = $tempRow;
        }

        // Return the response as JSON
        return response()->json($bulkData);
    }


    public function getDeliveryBoys()
    {
        $users = User::where('users.role_id', 3)
            ->where('users.active', 1)
            ->get(['users.*'])
            ->toArray();

        if (isset($users) && !empty($users)) {
            return response()->json([
                'error' => false,
                'data' => $users,
            ]);
        } else {
            return response()->json([
                'error' => true,
                'data' => [],
            ]);
        }
    }
}
