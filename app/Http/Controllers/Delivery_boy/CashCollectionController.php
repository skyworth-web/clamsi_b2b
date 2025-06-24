<?php

namespace App\Http\Controllers\Delivery_boy;

use App\Models\FundTransfer;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
class CashCollectionController extends Controller
{
    public function index()
    {
        $delivery_boy_id = Auth::id();
        $cash_in_hand = User::where('id', $delivery_boy_id)->value('cash_received');
        $cash_collected = Transaction::where([
            'type' => 'delivery_boy_cash_collection',
            'user_id' => $delivery_boy_id,
        ])->sum('amount');


        return view('delivery_boy.pages.tables.cash_collection', ['cash_collected' => $cash_collected, 'cash_in_hand' => $cash_in_hand]);
    }

    public function list(Request $request)
    {
        $search = trim($request->input('search'));
        $offset = request()->input('search','') || (request('pagination_offset')) ? (request('pagination_offset')) : 0;

        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'ASC');
        $delivery_boy_id = Auth::id();
        $amount = $request->input('amount'); // Retrieve amount from request

        // Base query to retrieve total count and search results
        $baseQuery = Transaction::leftJoin('users', 'transactions.user_id', '=', 'users.id')
            ->select(
                'transactions.*',
                'users.username as name',
                'users.mobile',
                'users.cash_received'
            )
            ->where('transactions.status', 1)
            ->where('transactions.user_id', $delivery_boy_id);

        // Apply filters based on request parameters
        if ($request->has('filter_status') && $request->input('filter_status') !== null) {
            $baseQuery->where('transactions.type', $request->input('filter_status'));
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $baseQuery->whereDate('transactions.transaction_date', '>=', $request->input('start_date'))
                ->whereDate('transactions.transaction_date', '<=', $request->input('end_date'));
        }

        // Additional search filters for username and amount
        if ($search) {
            $baseQuery->where('users.username', 'like', '%' . $search . '%');
        }



        // Retrieve total count
        $totalCount = $baseQuery->count();

        // Fetch paginated search results
        $searchResults = $baseQuery
            ->orderBy($sort, $order)
            ->offset($offset)
            ->limit($limit)
            ->get();

        // Format and prepare response data
        $rows = [];
        foreach ($searchResults as $row) {
            $formattedRow = [
                'id' => $row->id,
                'name' => $row->name,
                'mobile' => $row->mobile,
                'order_id' => $row->order_id,
                'cash_received' => formateCurrency(formatePriceDecimal($row->cash_received)),
                'type' => ($row->type == 'delivery_boy_cash') ? '<span class="badge bg-primary">Received</span>' : '<span class="badge bg-success">Collected</span>',
                'amount' => formateCurrency(formatePriceDecimal($row->amount)),
                'message' => $row->message,
                'created_at' => Carbon::parse($row->transaction_date)->format('d-m-Y'),
                'date' => Carbon::parse($row->created_at)->format('d-m-Y'),
            ];
            $rows[] = $formattedRow;
        }

        // Prepare response data
        $bulkData = [
            'total' => $totalCount,
            'rows' => $rows,
        ];

        return response()->json($bulkData);
    }


    public function fund_transfer()
    {
        return view('delivery_boy.pages.tables.fund_transfer');
    }

    function fund_transfers_list()
    {
        $offset = request()->input('search','') || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = request()->input('limit', 10);
        $sort = request()->input('sort', 'id');
        $order = request()->input('order', 'ASC');
        $search = request()->has('search') ? trim(request()->input('search')) : '';

        $deliveryBoyId = Auth::id();

        $query = FundTransfer::select('fund_transfers.*', 'users.username as name', 'users.mobile as mobile')
            ->where('fund_transfers.delivery_boy_id', $deliveryBoyId);

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->orWhere('fund_transfers.id', 'like', '%' . $search . '%')
                    ->orWhere('users.username', 'like', '%' . $search . '%')
                    ->orWhere('mobile', 'like', '%' . $search . '%')
                    ->orWhere('message', 'like', '%' . $search . '%')
                    ->orWhere('fund_transfers.opening_balance', 'like', '%' . $search . '%')
                    ->orWhere('fund_transfers.closing_balance', 'like', '%' . $search . '%')
                    ->orWhere('fund_transfers.status', 'like', '%' . $search . '%')
                    ->orWhere('fund_transfers.amount', 'like', '%' . $search . '%');
            });
        }

        $transfersRes = $query->join('users', 'fund_transfers.delivery_boy_id', '=', 'users.id')
            ->orderBy($sort, $order)
            ->skip($offset)
            ->take($limit)
            ->get();

        $transfersRes->transform(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'mobile' => $item->mobile,
                'opening_balance' => formateCurrency(formatePriceDecimal($item->opening_balance)),
                'closing_balance' => formateCurrency(formatePriceDecimal($item->closing_balance)),
                'amount' => formateCurrency(formatePriceDecimal($item->amount)),
                'status' => $item->status,
                'message' => $item->message,
                'date' => $item->created_at->format('Y-m-d'),
            ];
        });

        $total = FundTransfer::where('delivery_boy_id', $deliveryBoyId)->count();

        $bulkData = [
            'total' => $total,
            'rows' => $transfersRes->toArray(),
        ];

        return response()->json($bulkData);
    }

    public function get_delivery_boy_cash_collection($limit = "", $offset = '', $sort = 'transactions.id', $order = 'DESC', $search = NULL, $filter = "")
    {

        $multipleWhere = [];
        $query = Transaction::select('transactions.*', 'users.username as name', 'users.mobile', 'users.cash_received')
            ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
            ->where('transactions.status', 1);



        if (!empty($search)) {
            $multipleWhere = [
                'transactions.id' => $search,
                'transactions.amount' => $search,
                'transactions.created_at' => $search,
                'users.username' => $search,
                'users.mobile' => $search,
                'users.email' => $search,
                'transactions.transaction_type' => $search,
                'transactions.status' => $search,
            ];
        }

        if (isset($search) && !empty($search)) {
            $query->where(function ($query) use ($multipleWhere) {
                foreach ($multipleWhere as $column => $value) {
                    $query->orWhere($column, 'like', '%' . $value . '%');
                }
            });
        }


        if (isset($filter['status']) && !empty($filter['status'])) {
            $query->where('transactions.type', $filter['status']);
        }

        if (isset($filter['delivery_boy_id']) && !empty($filter['delivery_boy_id'])) {
            $query->where('users.id', $filter['delivery_boy_id']);
        }


        $totalCount = $query->count();

        $transactions = $query->orderBy($sort, $order)->skip($offset)->take($limit)->get();

        $bulkData = array();
        $bulkData = array();
        $bulkData['error'] = ($transactions->isEmpty()) ? true : false;
        $bulkData['message'] = ($transactions->isEmpty()) ? labels('admin_labels.cash_collection_not_exist', 'Cash collection does not exist')
            :
            labels('admin_labels.cash_collection_retrieve_successfully', 'Cash collection are retrieve successfully');
        $bulkData['total'] = ($transactions->isEmpty()) ? 0 : $totalCount;
        $rows = array();
        $tempRow = array();
        foreach ($transactions as $row) {

            $tempRow['id'] = $row['id'];
            $tempRow['name'] = $row['name'];
            $tempRow['mobile'] = $row['mobile'];
            $tempRow['order_id'] = $row['order_id'];
            $tempRow['cash_received'] = formateCurrency(formatePriceDecimal($row['cash_received']));
            $tempRow['type'] = (isset($row['type']) && $row['type'] == "delivery_boy_cash") ? 'Received' : 'Collected';
            $tempRow['amount'] = formateCurrency(formatePriceDecimal($row['amount']));
            $tempRow['message'] = $row['message'];
            $tempRow['transaction_date'] = $row['transaction_date'];
            $tempRow['date'] = Carbon::parse($row['created_at'])->format('d-m-Y');

            $rows[] = $tempRow;
        }
        $bulkData['data'] = $rows;
        return $bulkData;
    }
}
