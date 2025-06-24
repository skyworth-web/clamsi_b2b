<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentRequest;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaymentRequestController extends Controller
{
    public function index()
    {
        return view('admin.pages.tables.payment_request');
    }

    public function list()
    {
        $search = trim(request()->input('search'));
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = request()->input('limit', 10);
        $sort = request()->input('sort', 'pr.id');
        $order = request()->input('order', 'ASC');
        $startDate = request()->input('start_date');
        $endDate = request()->input('end_date');
        $payment_request_status = request()->input('payment_request_status');
        $userFilter = request()->input('user_filter');
        if (!empty($search)) {
            $multipleWhere = [
                'payment_requests.id' => $search,
                'payment_requests.payment_type' => $search,
                'payment_requests.amount_requested' => $search,
                'u.username' => $search,
                'u.email' => $search,
                'u.mobile' => $search,
            ];
        }

        $query = PaymentRequest::join('users as u', 'u.id', '=', 'payment_requests.user_id');

        if (!empty($search)) {
            $query->where(function ($query) use ($multipleWhere) {
                foreach ($multipleWhere as $column => $value) {
                    $query->orWhere($column, 'like', '%' . $value . '%');
                }
            });
        }

        if ($startDate && $endDate) {
            $query->whereDate('payment_requests.created_at', '>=', $startDate)
                ->whereDate('payment_requests.created_at', '<=', $endDate);
        }

        if (isset($payment_request_status)) {
            $query->where('payment_requests.status', intval($payment_request_status));
        }

        if (!empty($userFilter)) {
            $query->where('payment_requests.payment_type', $userFilter);
        }

        if (!empty($user_id)) {
            $query->where('payment_requests.user_id', $user_id);
        }

        $total = $query->count();

        $results = $query->orderBy($sort, $order)
            ->offset($offset)
            ->limit($limit)
            ->select('u.username', 'payment_requests.*')
            ->get();
        $rows = array();
        $tempRow = array();
        foreach ($results as $row) {

            $action = '<div class="d-flex align-items-center">
        <a class="single_action_button edit_request edit_return_request" href="javascript:void(0)"  data-bs-target="#payment_request_modal" data-bs-toggle="modal"><i class="bx bx-pencil mx-2"></i></a>
            </div>';
            $tempRow['id'] = $row->id;
            $tempRow['user_name'] = $row->username;
            $tempRow['payment_type'] = $row->payment_type;
            $tempRow['payment_address'] = $row->payment_address;
            $tempRow['amount_requested'] = formateCurrency(formatePriceDecimal($row->amount_requested));
            $tempRow['remarks'] = $row->remarks;
            $tempRow['status_digit'] = $row->status;

            $status = [
                '0' => '<span class="badge bg-success">Pending</span>',
                '1' => '<span class="badge bg-primary">Approved</span>',
                '2' => '<span class="badge bg-danger">Rejected</span>',
            ];

            $tempRow['status'] = $status[$row->status];
            $date = Carbon::parse($row->created_at);
            $formattedDate = $date->format('Y-m-d');
            $tempRow['date_created'] = $formattedDate;
            $tempRow['operate'] = $action;
            $rows[] = $tempRow;
        }
        return response()->json([
            "rows" => $rows,
            "total" => $total,
        ]);
    }

    public function update(Request $request)
    {
        $rules = [
            'payment_request_id' => 'required|numeric',
            'status' => 'required|numeric',
            'update_remarks' => 'required',
        ];

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        } else {
            $status = fetchDetails('payment_requests', ['id' => $request['payment_request_id']], 'status')[0]->status;
            if ($status == 2) {
                $response['error'] = true;
                $response['message'] = labels('admin_labels.you_have_already_rejected_amount', 'You have already rejected the amount');
                return response()->json($response);
            } else {

                $data = $request->all();
                $update_status = $data['status'];
                $updateRemarks = $request->input('update_remarks', null);
                $paymentRequestId = $data['payment_request_id'];

                // Fetch the amount_requested and user_id from the "payment_requests" table
                $paymentRequest = PaymentRequest::select('amount_requested', 'user_id')
                    ->where('id', $paymentRequestId)
                    ->first();

                if ($update_status == 2) {
                    // Update the balance based on the condition
                    updateBalance($paymentRequest->amount_requested, $paymentRequest->user_id, "add");
                }

                // Update the "payment_requests" table
                PaymentRequest::where('id', $paymentRequestId)
                    ->update([
                        'status' => $update_status,
                        'remarks' => $updateRemarks
                    ]);
                $response['error'] = false;
                $response['message'] =
                    labels('admin_labels.payment_request_updated_successfully', 'Payment request updated successfully');
                return response()->json($response);
            }
        }
    }
}
