<?php

namespace App\Http\Controllers\seller;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        return view('seller.pages.tables.sales_report');
    }

    public function list(Request $request)
    {
        $store_id = getStoreId();
        $search = trim($request->input('search'));
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'o.id');
        $order = $request->input('order', 'ASC');
        $language_code = get_language_code();
        if ($request->has('search') && trim($request->input('search')) != '') {
            $filters = [
                'u.username' => $search,
                'u.email' => $search,
                'u.mobile' => $search,
                'o.final_total' => $search,
                'o.created_at' => $search,
                'o.id' => $search,
                'oi.product_name' => $search,
                'o.payment_method' => $search,
            ];
        }
        $user = Auth::user();
        $query = DB::table('orders as o')
            ->selectRaw('COUNT(o.id) as total')
            ->join('users as u', 'u.id', '=', 'o.user_id');


        $seller_id = Seller::where('user_id', $user->id)->value('id');
        $query->join('order_items as oi', 'oi.order_id', '=', 'o.id')
            ->where('oi.seller_id', $seller_id)
            ->where('o.is_pos_order', 0);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereDate('o.created_at', '>=', $request->input('start_date'))
                ->whereDate('o.created_at', '<=', $request->input('end_date'));
        }

        if (isset($filters) && !empty($filters)) {
            $query->where(function ($query) use ($filters) {
                foreach ($filters as $field => $value) {
                    $query->orWhere($field, 'like', '%' . $value . '%');
                }
            });
        }
        $query->where('o.store_id', $store_id);
        $salesCount = $query->first()->total;

        $searchQuery = DB::table('orders as o')
            ->select(
                'o.*',
                'oi.*',
                'u.username',
                'u.email',
                'u.mobile',
                'ss.store_name',
                'u.username as seller_name',
                'pv.product_id'
            )
            ->leftJoin('users as u', 'u.id', '=', 'o.user_id')
            ->leftJoin('order_items as oi', 'oi.order_id', '=', 'o.id')
            ->leftJoin('seller_store as ss', 'ss.seller_id', '=', 'oi.seller_id')
            ->leftJoin('product_variants as pv', 'pv.id', '=', 'oi.product_variant_id')
            ->where('oi.seller_id', $seller_id)
            ->where('o.is_pos_order', 0);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $searchQuery->whereDate('o.created_at', '>=', $request->input('start_date'))
                ->whereDate('o.created_at', '<=', $request->input('end_date'));
        }

        if (isset($filters) && !empty($filters)) {
            $searchQuery->where(function ($query) use ($filters) {
                foreach ($filters as $field => $value) {
                    $query->orWhere($field, 'like', '%' . $value . '%');
                }
            });
        }
        $searchQuery->where('o.store_id', $store_id);

        $userDetails = $searchQuery->orderBy($sort, $order)->limit($limit)->offset($offset)->get();

        $totalAmount = 0;
        $finalTotalAmount = 0;
        $totalDeliveryCharge = 0;
        $rows = [];


        foreach ($userDetails as $row) {
            // dd($row);
            $tempRow = [
                'id' => $row->id,
                'product_name' => $row->product_name . (!empty($row->variant_name) ? '(' . $row->variant_name . ')' : ''),
                'product_name' => getDynamicTranslation('products', 'name', $row->product_id, $language_code)
                    . (!empty($row->variant_name) ? '(' . $row->variant_name . ')' : ''),

                'mobile' =>  $row->mobile,
                'date_added' => Carbon::parse($row->created_at)->format('d-m-Y'),
                'final_total' => $row->final_total,
            ];

            $totalAmount += intval($row->total);
            $finalTotalAmount += intval($row->final_total);
            $totalDeliveryCharge += intval($row->delivery_charge);
            $tempRow['payment_method'] = $row->payment_method;
            $tempRow['store_name'] = $row->store_name;
            $tempRow['seller_name'] = $row->seller_name;

            $rows[] = $tempRow;
        }

        $bulkData = [
            'total' => $salesCount,
            'rows' => $rows,
        ];


        return response()->json($bulkData);
    }

    public function get_sales_list(Request $request)
    {
        $store_id = $request->input('store_id', 0);
        $search = trim($request->input('search'));
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'o.id');
        $order = $request->input('order', 'ASC');
        if ($request->has('search') && trim($request->input('search')) != '') {
            $filters = [
                'oi.product_name' => $search,
                'o.payment_method' => $search,
            ];
        }
        $user = Auth::user();
        $query = DB::table('orders as o')
            ->selectRaw('COUNT(o.id) as total')
            ->join('users as u', 'u.id', '=', 'o.user_id');


        $seller_id = Seller::where('user_id', $user->id)->value('id');
        $query->join('order_items as oi', 'oi.order_id', '=', 'o.id')
            ->where('oi.seller_id', $seller_id);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereDate('o.created_at', '>=', $request->input('start_date'))
                ->whereDate('o.created_at', '<=', $request->input('end_date'));
        }

        if (isset($filters) && !empty($filters)) {
            $query->where(function ($query) use ($filters) {
                foreach ($filters as $field => $value) {
                    $query->orWhere($field, 'like', '%' . $value . '%');
                }
            });
        }
        $query->where('o.store_id', $store_id);
        $total = $query->first()->total;

        $searchQuery = DB::table('orders as o')
            ->select('o.*', 'oi.*', 'u.username', 'u.email', 'u.mobile', 'ss.store_name', 'u.username as seller_name')
            ->leftJoin('users as u', 'u.id', '=', 'o.user_id')
            ->leftJoin('order_items as oi', 'oi.order_id', '=', 'o.id')
            ->leftJoin('seller_store as ss', 'ss.seller_id', '=', 'oi.seller_id')
            ->where('oi.seller_id', $seller_id);
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $searchQuery->whereDate('o.created_at', '>=', $request->input('start_date'))
                ->whereDate('o.created_at', '<=', $request->input('end_date'));
        }

        if (isset($filters) && !empty($filters)) {
            $searchQuery->where(function ($query) use ($filters) {
                foreach ($filters as $field => $value) {
                    $query->orWhere($field, 'like', '%' . $value . '%');
                }
            });
        }
        $searchQuery->where('o.store_id', $store_id);

        $user_details = $searchQuery->orderBy($sort, $order)->limit($limit)->offset($offset)->get();
        $total_amount = 0;
        $final_total_amount = 0;
        $total_delivery_charge = 0;
        if (!$user_details->isEmpty()) {
            foreach ($user_details as $row) {
                $total_amount += intval($row->total ?? 0);
                $final_total_amount += intval($row->final_total ?? 0);
                $total_delivery_charge += intval($row->delivery_charge ?? 0);

                $tempRow['id'] = $row->id ?? '';
                $tempRow['name'] = $row->username ?? '';
                $tempRow['total'] = $row->total ?? '';
                $tempRow['tax_amount'] = $row->tax_amount ?? '';
                $tempRow['discounted_price'] = isset($row->discounted_price) && $row->discounted_price !== '' ? $row->discounted_price : '0';
                $tempRow['delivery_charge'] = $row->delivery_charge ?? '';
                $tempRow['final_total'] = $row->final_total ?? '';
                $tempRow['payment_method'] = $row->payment_method ?? '';
                $tempRow['store_name'] = $row->store_name ?? '';
                $tempRow['seller_name'] = $row->seller_name ?? '';
                $tempRow['date_added'] = Carbon::parse($row->created_at)->format('d-m-Y') ?? '';

                $rows[] = $tempRow;
            }
            $bulkData['error'] = false;
            $bulkData['message'] = "Data Retrived Successfully";
            $bulkData['total'] = $total;
            $bulkData['grand_total'] =  "$total_amount";
            $bulkData['total_delivery_charge'] = "$total_delivery_charge";
            $bulkData['grand_final_total'] =   "$final_total_amount";
            $bulkData['rows'] = $rows;
            return $bulkData;
        } else {
            $bulkData['error'] = true;
            $bulkData['message'] = "No data found";
            $bulkData['total'] = '';
            $bulkData['grand_total'] =  "";
            $bulkData['total_delivery_charge'] = "";
            $bulkData['grand_final_total'] =   "";
            $bulkData['rows'] = '';
            return $bulkData;
        }
    }
}
