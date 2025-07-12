<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\PaymentRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    public function register_user(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'mobile' => 'required|numeric|unique:users,mobile',
            'country_code' => 'required|string|max:255',
            'fcm_id' => 'nullable|string|max:255',
            'referral_code' => 'nullable|string|unique:users,referral_code|max:255',
            'friends_code' => 'nullable|string|max:255',
            'password' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
                'data' => [],
            ]);
        } else {
            if ($request->filled('friends_code')) {
                $friends_code = $request->input('friends_code');
                $friend = User::where('referral_code', $friends_code)->first();

                if (!$friend) {
                    $response = [
                        'error' => true,
                        'error_message' => 'Invalid friends code! Please pass the valid referral code of the inviter',
                        'data' => [],
                    ];
                    return response()->json($response);
                }
            }

            $additional_data = [
                'username' => $request->username,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'country_code' => $request->country_code,
                'fcm_id' => $request->fcm_id,
                'referral_code' => $request->referral_code,
                'friends_code' => $request->friends_code,
                'type' => 'phone',
                'role_id' => 2,
            ];
            $identity_column = config('auth.defaults.passwords') === 'users.email' ? 'email' : 'mobile';
            $identity = ($identity_column == 'mobile') ? $request->mobile : $request->email;
            $lastInsertId = DB::table('users')->insertGetId($additional_data);

            if ($lastInsertId) {
                User::where($identity_column, $identity)->update(['active' => 1]);

                $data = User::select('users.id', 'users.username', 'users.email', 'users.mobile', 'c.name as city_name')
                    ->where($identity_column, $identity)
                    ->leftJoin('cities as c', 'c.id', '=', 'users.city')
                    ->groupBy('users.email')
                    ->get()
                    ->toArray();

                foreach ($data as $row) {
                    $row = outputEscaping($row);
                    $tempRow = [
                        'id' => isset($row['id']) && !empty($row['id']) ? $row['id'] : '',
                        'username' => isset($row['username']) && !empty($row['username']) ? $row['username'] : '',
                        'email' => isset($row['email']) && !empty($row['email']) ? $row['email'] : '',
                        'mobile' => isset($row['mobile']) && !empty($row['mobile']) ? $row['mobile'] : '',
                        'city_name' => isset($row['city_name']) && !empty($row['city_name']) ? $row['city_name'] : '',
                        'area_name' => isset($row['area_name']) && !empty($row['area_name']) ? $row['area_name'] : '',
                    ];

                    $rows[] = $tempRow;
                }
                $response = [
                    'error' => false,
                    'message' => 'Registered Successfully',
                    'data' => $rows,
                ];
                return response()->json($response);
            } else {
                $response = [
                    'error' => false,
                    'message' => 'Registration Fail',
                    'data' => [],
                ];
                return response()->json($response);
            }
        }
    }

    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'mobile' => 'required|numeric',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->all(),
                'data' => [],
            ]);
        } else {
            $credentials = $request->only('mobile', 'password');

            if (auth()->attempt($credentials)) {
                $user = User::with('role')
                    ->where('active', 1)
                    ->find(Auth::user()->id);
                if ($user) {

                    $fcm_ids = fetchdetails('user_fcm', ['user_id' => $user->id], 'fcm_id');

                    $fcm_ids_array = array_map(function ($item) {
                        return $item->fcm_id;
                    }, $fcm_ids);

                    $user_data = [
                        'id' => $user->id ?? '',
                        'ip_address' => $user->ip_address ?? '',
                        'username' => $user->username ?? '',
                        'email' => $user->email ?? '',
                        'mobile' =>  $user->mobile ?? '',
                        'image' => getMediaImageUrl($user->image, 'USER_IMG_PATH'),
                        'balance' => $user->balance ?? '0',
                        'activation_selector' => $user->activation_selector ?? '',
                        'activation_code' => $user->activation_code ?? '',
                        'forgotten_password_selector' => $user->forgotten_password_selector ?? '',
                        'forgotten_password_code' => $user->forgotten_password_code ?? '',
                        'forgotten_password_time' => $user->forgotten_password_time ?? '',
                        'remember_selector' => $user->remember_selector ?? '',
                        'remember_code' => $user->remember_code ?? '',
                        'created_on' => $user->created_on ?? '',
                        'last_login' => $user->last_login ?? '',
                        'active' => $user->active ?? '',
                        'company' => $user->company ?? '',
                        'address' => $user->address ?? '',
                        'bonus' => $user->bonus ?? '',
                        'cash_received' => $user->cash_received ?? '0.00',
                        'dob' => $user->dob ?? '',
                        'country_code' => $user->country_code ?? '',
                        'city' => $user->city ?? '',
                        'area' => $user->area ?? '',
                        'street' => $user->street ?? '',
                        'pincode' => $user->pincode ?? '',
                        'apikey' => $user->apikey ?? '',
                        'referral_code' => $user->referral_code ?? '',
                        'friends_code' => $user->friends_code ?? '',
                        'fcm_id' => array_values($fcm_ids_array) ?? '',
                        'latitude' => $user->latitude ?? '',
                        'longitude' => $user->longitude ?? '',
                        'created_at' => $user->created_at ?? '',
                        'type' => $user->type ?? '',
                    ];
                    $request->session()->put('user_data', $user_data);

                    return response()->json([
                        'error' => false,
                        'message' => 'Login successful',
                        'user' => $user_data,

                    ], 200);
                }
            } else {

                return response()->json([
                    'error' => true,
                    'message' => 'Invalid credentials',
                ], 401);
            }
        }
    }
    public function logout()
    {
        Auth::logout();
        return response()->json([
            'error' => false,
            'message' => "User Logged out Successfully.",
        ], 200);
    }

    public function web_logout()
    {
        Auth::logout();
        return redirect('/onboard')->withHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }


    public function transactions_list($user_id = '', $type = '', $transaction_type = '')
    {
        // dd($user_id);
        $offset = request()->input('offset', 0);
        $limit = request()->input('limit', 10);
        $sort = request()->input('sort', 'id');
        $order = request()->input('order', 'ASC');
        $startDate = request()->input('start_date');
        $endDate = request()->input('end_date');
        $multipleWhere = [];
        $where = [];
        $currency_details = getDefaultCurrency();
        $currency_symbol = "";
        if ($currency_details != null) {
            $currency_symbol = $currency_details->symbol;
        }
        if (!empty($transaction_type)) {
            $where = ['transactions.transaction_type' => $transaction_type];
        }

        if (!empty($type)) {
            $where = ['transactions.type' => $type];
        }

        if (request()->has('search') && request()->input('search') !== '') {
            $search = trim(request()->input('search'));
            $multipleWhere = [
                'transactions.id' => $search,
                'transactions.amount' => $search,
                'transactions.created_at' => $search,
                'users.username' => $search,
                'users.mobile' => $search,
                'users.email' => $search,
                'transactions.type' => $search,
                'transactions.status' => $search,
            ];
        }

        if (request()->has('user_id') && !empty(request()->input('user_id'))) {
            $where = ['users.id' => request()->input('user_id')];
        }

        if (request()->has('user_type') && !empty(request()->input('user_type'))) {
            $role_id = DB::table('roles')->where('name', request()->input('user_type'))->value('id');
        }

        $count_res = DB::table('transactions')
            ->select(DB::raw('COUNT(transactions.id) as total'))
            ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
            ->where('users.id', $user_id);

        if (!empty($multipleWhere)) {
            $count_res->where(function ($query) use ($multipleWhere) {
                foreach ($multipleWhere as $column => $value) {
                    $query->orWhere($column, 'like', '%' . $value . '%');
                }
            });
        }
        if ($startDate && $endDate) {
            $count_res->whereDate('transactions.created_at', '>=', $startDate)
                ->whereDate('transactions.created_at', '<=', $endDate);
        }

        if (!empty($where)) {
            $count_res->where($where);
        }

        if (!empty($user_where)) {
            $count_res->where($user_where);
        }

        $txn_count = $count_res->get()->first();

        $search_res = DB::table('transactions')
            ->select('transactions.*', 'users.username as name')
            ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
            ->where('users.id', $user_id);

        if (!empty($multipleWhere)) {
            $search_res->where(function ($query) use ($multipleWhere) {
                foreach ($multipleWhere as $column => $value) {
                    $query->orWhere($column, 'like', '%' . $value . '%');
                }
            });
        }

        if (!empty($where)) {
            $search_res->where($where);
        }

        if (!empty($user_where)) {
            $search_res->where($user_where);
        }

        if ($startDate && $endDate) {
            $search_res->whereDate('transactions.created_at', '>=', $startDate)
                ->whereDate('transactions.created_at', '<=', $endDate);
        }
        $txn_search_res = $search_res->orderBy($sort, $order)->skip($offset)->take($limit)->get();
        $bulkData = [
            'total' =>  $txn_count->total,
            'rows' => []
        ];

        $status = [
            'success' => '<span class="badge bg-success">Success</span>',
            'pending' => '<span class="badge bg-info">Pending</span>',
            'awaiting' => '<span class="badge bg-info">Awaiting</span>',
            'Failed' => '<span class="badge bg-danger">Failed</span>',
        ];
        foreach ($txn_search_res as $row) {
            // dd($row);
            $tempRow = [
                'id' => $row->id,
                'type' => str_replace('_', ' ', $row->type),
                'order_id' => $row->order_id,
                'txn_id' => $row->txn_id,
                'payu_txn_id' => $row->payu_txn_id,
                'amount' => $currency_symbol . $row->amount,
                'status' => $status[$row->status] ?? '<span class="badge bg-primary">' . $row->status . '</span>',
                'message' => $row->message,
                'created_at' => Carbon::parse($row->created_at)->format('d-m-Y'),
            ];

            $bulkData['rows'][] = $tempRow;
        }

        print_r(json_encode($bulkData));
    }

    public function wallet_withdrawal_request($user_id)
    {
        $offset = request()->input('offset', 0);
        $limit = request()->input('limit', 10);
        $sort = request()->input('sort', 'pr.id');
        $order = request()->input('order', 'ASC');
        $search = trim(request()->input('search'));
        $startDate = request()->input('start_date');
        $endDate = request()->input('end_date');
        $payment_request_status = request()->input('payment_request_status');
        if (!empty($search)) {
            $multipleWhere = [
                'payment_requests.id' => $search,
                'payment_requests.amount_requested' => $search,
                'payment_requests.payment_address' => $search,
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
            $tempRow['id'] = $row->id;
            $tempRow['payment_address'] = $row->payment_address;
            $tempRow['amount_requested'] = formateCurrency(formatePriceDecimal($row->amount_requested));
            $tempRow['remarks'] = $row->remarks;

            $status = [
                '0' => '<span class="badge bg-success">Pending</span>',
                '1' => '<span class="badge bg-primary">Approved</span>',
                '2' => '<span class="badge bg-danger">Rejected</span>',
            ];

            $tempRow['status'] = $status[$row->status];
            $date = Carbon::parse($row->created_at);
            $formattedDate = $date->format('Y-m-d');
            $tempRow['date_created'] = $formattedDate;
            $rows[] = $tempRow;
        }
        return response()->json([
            "rows" => $rows,
            "total" => $total,
        ]);
    }
}
