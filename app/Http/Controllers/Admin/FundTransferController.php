<?php

namespace App\Http\Controllers\admin;

use App\Models\FundTransfer;
use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

class FundTransferController extends Controller
{

    public function index()
    {
        return view('admin.pages.tables.fund_transfer');
    }
    public function store(Request $request)
    {
        $rules = [
            'delivery_boy_id' => 'required|numeric',
            'transfer_amt' => 'required|numeric',
            'message' => 'nullable',
        ];

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        } else {
            $delivery_boy_id = $request->input('delivery_boy_id');
            $deliveryBoy = User::find($request->input('delivery_boy_id'));

            if ($deliveryBoy->balance > 0 && $deliveryBoy->balance !== null) {

                updateWalletBalance('debit', $delivery_boy_id, $request->input('transfer_amt'));
                $amount = $request->input('transfer_amt');
                $opening_balance = $deliveryBoy->balance;
                $closing_balance = $opening_balance - $amount;
                FundTransfer::forceCreate([
                    'delivery_boy_id' => $request->input('delivery_boy_id'),
                    'amount' => $request->input('transfer_amt'),
                    'opening_balance' => $opening_balance,
                    'closing_balance' => $closing_balance,
                    'status' => 'success',
                    'message' => $request->input('message'),
                ]);

                return response()->json([
                    'error' => false,
                    'message' =>
                    labels('admin_labels.amount_successfully_transferred', 'Amount Successfully Transferred'),
                ]);
            } else {
                return response()->json([
                    'error' => true,
                    'error_message' =>
                    labels('admin_labels.balance_should_be_greater_than_zero', 'Balance should be greater than 0'),
                ]);
            }
        }
    }

    public function list(Request $request)
    {
        $search = trim($request->input('search'));
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'ASC');

        $multipleWhere = $where = [];

        if ($request->filled('search')) {

            $multipleWhere = [
                'fund_transfers.id' => $search,
                'users.username' => $search,
                'mobile' => $search,
                'message' => $search,
                'fund_transfers.opening_balance' => $search,
                'fund_transfers.closing_balance' => $search,
                'fund_transfers.status' => $search,
                'fund_transfers.amount' => $search,
            ];
        }

        $query = FundTransfer::select('fund_transfers.*', 'users.username as name', 'users.mobile')
            ->join('users', 'fund_transfers.delivery_boy_id', '=', 'users.id');

        if (!empty($multipleWhere)) {
            $query->orWhere(function ($query) use ($multipleWhere) {
                foreach ($multipleWhere as $column => $value) {
                    $query->orWhere($column, 'like', '%' . $value . '%');
                }
            });
        }

        $transfersCount = $query->count();
        $total = $transfersCount;

        $transfersRes = $query->orderBy($sort, $order)
            ->skip($offset)
            ->take($limit)
            ->get();

        $bulkData = [
            'total' => $total,
            'rows' => $transfersRes->map(function ($row) {
                return [
                    'id' => $row->id,
                    'name' => $row->name,
                    'mobile' => $row->mobile,
                    'opening_balance' => formateCurrency(formatePriceDecimal($row->opening_balance ?? '')),
                    'closing_balance' => formateCurrency(formatePriceDecimal($row->closing_balance ?? '')),
                    'amount' => formateCurrency(formatePriceDecimal($row->amount ?? '')),
                    'status' => $row->status ?? '',
                    'message' => $row->message ?? '',
                    'created_at' => $row->created_at ? Carbon::parse($row->created_at)->format('d-m-Y') : '',

                ];
            })->toArray(),
        ];

        return response()->json($bulkData);
    }
}
