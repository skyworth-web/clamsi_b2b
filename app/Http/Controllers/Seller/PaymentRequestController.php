<?php

namespace App\Http\Controllers\seller;

use App\Http\Controllers\Controller;
use App\Models\PaymentRequest;
use App\Models\Seller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PaymentRequestController extends Controller
{
    public function withdrawal_requests()
    {
        $user_id = Auth::user()->id;
        return view('seller.pages.tables.withdrawal_request', compact('user_id'));
    }

    public function get_payment_request_list(Request $request, $user_id = null)
    {
        // If $user_id is null, use the authenticated user's ID
        $user_id = isset($user_id) && $user_id != null ? $user_id : Auth::user()->id;

        // Set default pagination and sorting parameters
        $search = trim($request->input('search', ''));
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'payment_requests.id');
        $order = $request->input('order', 'DESC');
        $userFilter = $request->input('user_filter');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $status = $request->input('payment_request_status');

        // Query for counting the total number of results
        $countQuery = PaymentRequest::join('users as u', 'u.id', '=', 'payment_requests.user_id');

        // Apply filters for counting the total
        if (!empty($search)) {
            $search = trim($search);
            $countQuery->where(function ($q) use ($search) {
                $q->where('payment_requests.id', $search)
                    ->orWhere('u.username', 'LIKE', "%$search%")
                    ->orWhere('u.email', 'LIKE', "%$search%");
            });
        }

        if ($startDate && $endDate) {
            $countQuery->whereDate('payment_requests.created_at', '>=', $startDate)
                ->whereDate('payment_requests.created_at', '<=', $endDate);
        }

        if (isset($status)) {
            $countQuery->where('payment_requests.status', intval($status));
        }

        if (!empty($userFilter)) {
            $countQuery->where('payment_requests.payment_type', $userFilter);
        }

        if (!empty($user_id)) {
            $countQuery->where('payment_requests.user_id', $user_id);
        }

        // Get the total number of matching records
        $total = $countQuery->count();

        // Query for fetching the actual payment request data
        $searchQuery = PaymentRequest::join('users as u', 'u.id', '=', 'payment_requests.user_id');

        // Apply the same filters to fetch the data
        if (!empty($search)) {
            $search = trim($search);
            $searchQuery->where(function ($q) use ($search) {
                $q->where('payment_requests.id', $search)
                    ->orWhere('u.username', 'LIKE', "%$search%")
                    ->orWhere('u.email', 'LIKE', "%$search%");
            });
        }

        if ($startDate && $endDate) {
            $searchQuery->whereDate('payment_requests.created_at', '>=', $startDate)
                ->whereDate('payment_requests.created_at', '<=', $endDate);
        }

        if (isset($status)) {
            $searchQuery->where('payment_requests.status', intval($status));
        }

        if (!empty($userFilter)) {
            $searchQuery->where('payment_requests.payment_type', $userFilter);
        }

        if (!empty($user_id)) {
            $searchQuery->where('payment_requests.user_id', $user_id);
        }

        // Fetch the paginated results
        $paymentRequests = $searchQuery->orderBy($sort, $order)
            ->limit($limit)
            ->offset($offset)
            ->select('u.username', 'payment_requests.*')
            ->get();

        // Prepare the response data
        $rows = [];
        foreach ($paymentRequests as $row) {
            $tempRow['id'] = $row->id;
            $tempRow['user_id'] = $row->user_id;
            $tempRow['user_name'] = $row->username;
            $tempRow['payment_type'] = $row->payment_type;
            $tempRow['amount_requested'] = $row->amount_requested;
            $tempRow['remarks'] = $row->remarks;
            $tempRow['payment_address'] = $row->payment_address;
            $tempRow['date_created'] = Carbon::parse($row->created_at)->format('d-m-Y');
            $tempRow['status_code'] = $row->status;
            $status = [
                '0' => '<span class="badge bg-success">Pending</span>',
                '1' => '<span class="badge bg-primary">Approved</span>',
                '2' => '<span class="badge bg-danger">Rejected</span>',
            ];
            $tempRow['status'] = $status[$row->status];
            $tempRow['remarks'] = $row->remarks;
            $rows[] = $tempRow;
        }

        // Return the total and the rows in the response
        return response()->json([
            'total' => $total,
            'rows' => $rows,
        ]);
    }


    public function add_withdrawal_request(Request $request, $fromDeliveryBoyApp = false)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|numeric|exists:users,id',
            'payment_address' => 'required',
            'amount' => 'required|numeric|gt:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user_id = $request->input('user_id');
        $payment_address = $request->input('payment_address');
        $amount = $request->input('amount');
        $userData = fetchDetails('users', ['id' => $user_id], 'balance');
        if (!empty($userData)) {
            if ($amount <= $userData[0]->balance) {
                $payment_type = $fromDeliveryBoyApp == true ? 'delivery_boy' : 'seller';
                $data = [
                    'user_id' => $user_id,
                    'payment_address' => $payment_address,
                    'payment_type' => $payment_type,
                    'amount_requested' => $amount,
                ];
                if (PaymentRequest::create($data)) {
                    $lastAddedRequest = PaymentRequest::latest()->first();
                    if ($lastAddedRequest) {
                        $data = $lastAddedRequest->toArray();

                        // Change the key from 'status' to 'status_code'
                        $data['status_code'] = $data['status'];
                        $data['date_created'] = Carbon::parse($data['created_at'])->format('d-m-Y');
                        $data['updated_at'] = Carbon::parse($data['updated_at'])->format('d-m-Y');
                        unset($data['status']);
                        unset($data['created_at']);
                    }
                    updateBalance($amount, $user_id, 'deduct');
                    $userData = fetchDetails('users', ['id' => $user_id], 'balance');
                    $response['error'] = false;
                    $response['message'] =
                        labels('admin_labels.withdrawal_request_sent_successfully', 'Withdrawal Request Sent Successfully');
                    $response['amount'] = $userData[0]->balance;
                    $response['data'] = $data;
                } else {
                    $response['error'] = true;
                    $response['message'] =
                        labels('admin_labels.cannot_send_withdrawal_request', "Cannot sent Withdrawal Request.Please Try again later.");
                    $response['data'] = array();
                    $response['amount'] = 0;
                }
            } else {
                $response['error'] = true;
                $response['error_message'] =
                    labels('admin_labels.insufficient_balance_for_withdrawal', "You don't have enough balance to sent the withdraw request.");
                $response['data'] = array();
                $response['amount'] = 0;
            }
            return response()->json($response);
        }
    }
}
