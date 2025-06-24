<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Store;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.pages.tables.sales_reports');
    }
    // public function list(Request $request)
    // {
    //     $store_id = getStoreId();
    //     $search = trim($request->input('search'));
    //     $offset = $request->input('pagination', 0);
    //     $limit = $request->input('limit', 10);

    //     // Query Builder
    //     $query = DB::table('orders')
    //         ->join('order_items', 'orders.id', '=', 'order_items.order_id')
    //         ->join('stores', 'orders.store_id', '=', 'stores.id')
    //         ->select(
    //             'orders.id AS order_id',
    //             'order_items.product_name AS product_name',
    //             'orders.total',
    //             'orders.promo_discount',
    //             'orders.delivery_charge',
    //             DB::raw('COALESCE(SUM(order_items.admin_commission_amount), 0) AS admin_commission'),
    //             DB::raw('COALESCE(SUM(order_items.seller_commission_amount), 0) AS seller_commission'),
    //             DB::raw('(orders.total - orders.promo_discount + orders.delivery_charge) AS net_revenue'),
    //             DB::raw('COALESCE(SUM(order_items.admin_commission_amount + order_items.seller_commission_amount), 0) AS total_commissions'),
    //             DB::raw('COALESCE(SUM(order_items.price * order_items.quantity) + SUM(order_items.admin_commission_amount + order_items.seller_commission_amount), 0) AS total_costs'),
    //             DB::raw('
    //         GREATEST(
    //             (orders.total - orders.promo_discount + orders.delivery_charge) -
    //             COALESCE(SUM(order_items.price * order_items.quantity) + SUM(order_items.admin_commission_amount + order_items.seller_commission_amount), 0),
    //             0
    //         ) AS profit
    //     '),
    //             DB::raw('
    //         GREATEST(
    //             ABS(
    //                 LEAST(
    //                     (orders.total - orders.promo_discount + orders.delivery_charge) -
    //                     COALESCE(SUM(order_items.price * order_items.quantity) + SUM(order_items.admin_commission_amount + order_items.seller_commission_amount), 0),
    //                     0
    //                 )
    //             ),
    //             0
    //         ) AS loss
    //     ')
    //         )
    //         ->where('orders.store_id', $store_id)
    //         ->where('orders.is_pos_order', 0)
    //         ->groupBy('orders.id');

    //     // Search Filter
    //     if (!empty($search)) {
    //         $query->where(function ($q) use ($search) {
    //             $q->where('order_items.product_name', 'like', "%$search%");
    //         });
    //     }

    //     // Get Total Count Before Applying Limit & Offset
    //     $totalCount = $query->first()->total;

    //     // Apply Pagination
    //     $report_data = $query->limit($limit)->offset($offset)->get();

    //     // Process Data
    //     $rows = [];
    //     foreach ($report_data as $row) {
    //         $rows[] = [
    //             'id' => $row->order_id,
    //             'product_name' => $row->product_name,
    //             'total' => (float) $row->total,
    //             'promo_discount' => (float) $row->promo_discount,
    //             'delivery_charge' => (float) $row->delivery_charge,
    //             'admin_commission' => (float) $row->admin_commission,
    //             'seller_commission' => (float) $row->seller_commission,
    //             'net_revenue' => (float) $row->net_revenue,
    //             'total_commissions' => (float) $row->total_commissions,
    //             'profit' => (float) $row->profit,
    //             'loss' => (float) $row->loss,
    //         ];
    //     }

    //     // Return Response with Pagination Data
    //     return response()->json([
    //         'total' => $totalCount,
    //         'rows' => $rows,
    //     ]);
    // }


    public function list(Request $request)
    {
        $store_id = getStoreId();
        $search = trim($request->input('search'));
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'orders.id');
        $order = $request->input('order', 'ASC');

        if ($request->has('search') && trim($request->input('search')) != '') {
            $filters = [
                'o.id' => $search,
                'order_items.product_name' => $search,
            ];
        }
        $query = DB::table('orders as o')
            ->join('order_items', 'o.id', '=', 'order_items.order_id')
            ->join('stores', 'o.store_id', '=', 'stores.id')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->select(
                'o.id AS order_id',
                'order_items.product_name AS product_name',
                'product_variants.product_id',
                'o.total',
                'o.promo_discount',
                'o.delivery_charge',
                DB::raw('COALESCE(SUM(order_items.admin_commission_amount), 0) AS admin_commission'),
                DB::raw('COALESCE(SUM(order_items.seller_commission_amount), 0) AS seller_commission'),
                DB::raw('(o.total - o.promo_discount + o.delivery_charge) AS net_revenue'),
                DB::raw('COALESCE(SUM(order_items.admin_commission_amount + order_items.seller_commission_amount), 0) AS total_commissions'),
                DB::raw('COALESCE(SUM(order_items.price * order_items.quantity) + SUM(order_items.admin_commission_amount + order_items.seller_commission_amount), 0) AS total_costs'),
                DB::raw('
                    GREATEST(
                        (o.total - o.promo_discount + o.delivery_charge) -
                        COALESCE(SUM(order_items.price * order_items.quantity) + SUM(order_items.admin_commission_amount + order_items.seller_commission_amount), 0),
                        0
                    ) AS profit
                '),
                DB::raw('
                    GREATEST(
                        ABS(
                            LEAST(
                                (o.total - o.promo_discount + o.delivery_charge) -
                                COALESCE(SUM(order_items.price * order_items.quantity) + SUM(order_items.admin_commission_amount + order_items.seller_commission_amount), 0),
                                0
                            )
                        ),
                        0
                    ) AS loss
                ')
            )
            ->where('o.store_id', $store_id)
            ->where('o.is_pos_order', 0)
            ->groupBy('o.id');

        if (isset($filters) && !empty($filters)) {
            $query->where(function ($query) use ($filters) {
                foreach ($filters as $field => $value) {
                    $query->orWhere($field, 'like', '%' . $value . '%');
                }
            });
        }
        $salesCount = $query->first()->total;

        $report_data = $query->orderBy($sort, $order)->limit($limit)->offset($offset)->get();

        $language_code = get_language_code();
        foreach ($report_data as $row) {
            // dd($row);
            $rows[] = [
                'id' => $row->order_id,
                'product_name' => getDynamicTranslation('products', 'name', $row->product_id, $language_code),
                'total' => (float) $row->total,
                'promo_discount' => (float) $row->promo_discount,
                'delivery_charge' => (float) $row->delivery_charge,
                'admin_commission' => (float) $row->admin_commission,
                'seller_commission' => (float) $row->seller_commission,
                'net_revenue' => (float) $row->net_revenue,
                'total_commissions' => (float) $row->total_commissions,
                'profit' => (float) $row->profit,
                'loss' => (float) $row->loss,
            ];
        }

        // Return Response with Pagination Data
        return response()->json([
            'total' => $salesCount,
            'rows' => $rows,
        ]);


        return response()->json($bulkData);
    }
}
