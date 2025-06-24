<?php

namespace App\Http\Controllers\Admin;

use App\Models\ComboProduct;
use App\Models\OrderItems;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{

    public function index()
    {
        $id = 0;

        $store_id = getStoreId();

        $currency = fetchDetails('currencies', ['is_default' => 1], 'symbol')[0]->symbol ?? "";

        // total statictis

        $order_counter = countNewOrders();
        $product_counter = Product::where('store_id', $store_id)
            ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
            ->select(DB::raw('count(DISTINCT products.id) as counter'))
            ->get()[0]->counter;
        $combo_product_counter = ComboProduct::where('store_id', $store_id)
            ->select(DB::raw('count(id) as counter'))
            ->get()[0]->counter;

        $total_products = $product_counter + $combo_product_counter;
        $total_store = Store::select(DB::raw('count(id) as counter'))
            ->get()[0]->counter;
        $total_seller = DB::table('seller_store')
            ->where('store_id', $store_id)
            ->select(DB::raw('count(id) as counter'))
            ->get()[0]->counter;


        $delivery_boy_counter = countDeliveryBoys();

        $total_earnings = AdmintotalEarnings();

        // chatify

        $role_id = Auth::user() ? Auth::user()->role_id : "";
        $store_details = fetchDetails('stores', ['id' => $store_id], ['primary_color', 'secondary_color', 'hover_color', 'active_color']);
        $primary_colour = (isset($store_details[0]->primary_color) && !empty($store_details[0]->primary_color)) ? $store_details[0]->primary_color : '#B52046';
        $messengerColor = $primary_colour;
        $dark_mode = Auth::user() && Auth::user()->dark_mode < 1 ? 'light' : 'dark';

        // user counter

        $user_counter = countNewUsers();


        //-------------------------------- get admin overview statistics ------------------------------------

        $sales = [];

        // monthly earnings

        $allMonths = [
            'Jan' => 0,
            'Feb' => 0,
            'Mar' => 0,
            'Apr' => 0,
            'May' => 0,
            'Jun' => 0,
            'Jul' => 0,
            'Aug' => 0,
            'Sep' => 0,
            'Oct' => 0,
            'Nov' => 0,
            'Dec' => 0
        ];

        // Fetch data for each type

        $monthRes = $this->getMonthlyData('sub_total', $store_id);
        $monthCommissionRes = $this->getMonthlyData('admin_commission_amount', $store_id);
        $monthSalesRes = $this->getMonthlyData('quantity', $store_id);

        // Merge the database results with the allMonths array, replacing existing values
        $monthWiseRevenueDetail = array_merge($allMonths, array_combine(array_column($monthRes, 'month_name'), array_map('intval', array_column($monthRes, 'total'))));
        $monthCommissionDetail = array_merge($allMonths, array_combine(array_column($monthCommissionRes, 'month_name'), array_map('intval', array_column($monthCommissionRes, 'total'))));
        $monthSalesDetail = array_merge($allMonths, array_combine(array_column($monthSalesRes, 'month_name'), array_map('intval', array_column($monthSalesRes, 'total'))));

        // Create the result array
        $monthWiseSales['total_revenue'] = array_values($monthWiseRevenueDetail);
        $monthWiseSales['total_commission'] = array_values($monthCommissionDetail);
        $monthWiseSales['total_sales'] = array_values($monthSalesDetail);
        $monthWiseSales['month_name'] = array_keys($monthWiseRevenueDetail);

        $sales[0] = $monthWiseSales;
        $now = now();

        // weekly earnings

        $startDate = Carbon::now()->startOfWeek(); // Start of the current week (Sunday)
        $endDate = Carbon::now()->endOfWeek(); // End of the current week (Saturday)

        $weekWiseSales = [
            'total_revenue' => [],
            'total_commission' => [],
            'total_sales' => [],
            'day' => []
        ];
        $currentDate = Carbon::now();
        // Loop to retrieve data for each day of the week
        for ($i = 0; $i < 7; $i++) {
            // Get the day name for the current iteration
            $dayName = $currentDate->copy()->startOfWeek()->addDays($i)->format('D, d M');

            // Get sales data for the current day
            $dayRes = $this->getWeeklySalesData('order_items', 'created_at', 'sub_total', 'admin_commission_amount', 'quantity', $store_id);

            // If data exists for the current day
            if (isset($dayRes['total_revenue'][$i])) {
                $weekWiseSales['total_revenue'][] = intval($dayRes['total_revenue'][$i]);
                $weekWiseSales['total_commission'][] = intval($dayRes['total_commission'][$i]);
                $weekWiseSales['total_sales'][] = intval($dayRes['total_sales'][$i]);
            } else {
                // If no data exists for the current day, set totals to 0
                $weekWiseSales['total_revenue'][] = 0;
                $weekWiseSales['total_commission'][] = 0;
                $weekWiseSales['total_sales'][] = 0;
            }

            // Add the day name to the week-wise sales array
            $weekWiseSales['day'][] = $dayName;
        }


        $sales[1] = $weekWiseSales;
        // daily earnings

        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays(29);

        // Create an array with all dates of the month
        $allDatesOfMonth = [];
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $allDatesOfMonth[] = [
                'date' => $currentDate->format('j'),
                'month' => $currentDate->format('M'),
                'year' => $currentDate->format('Y')
            ];
            $currentDate->addDay();
        }

        $dayRes = $this->getDailySalesData('order_items', 'created_at', 'sub_total', 'admin_commission_amount', 'quantity', $store_id, 29);

        // Create an associative array with date as key for easier merging
        $dayData = [];
        foreach ($dayRes as $day) {
            $dayData[$day->date] = [
                'total_revenue' => intval($day->total_revenue),
                'total_commission' => intval($day->total_commission),
                'total_sales' => intval($day->total_sales)
            ];
        }

        // Merge fetched data with all dates of the month, filling missing dates with zeros
        $dayWiseSales = [];
        foreach ($allDatesOfMonth as $dateInfo) {
            $date = $dateInfo['date'];
            if (isset($dayData[$date])) {
                $dayWiseSales['total_revenue'][] = $dayData[$date]['total_revenue'];
                $dayWiseSales['total_commission'][] = $dayData[$date]['total_commission'];
                $dayWiseSales['total_sales'][] = $dayData[$date]['total_sales'];
            } else {
                $dayWiseSales['total_revenue'][] = 0;
                $dayWiseSales['total_commission'][] = 0;
                $dayWiseSales['total_sales'][] = 0;
            }
            $dayWiseSales['day'][] = $date . '-' . $dateInfo['month'] . '-' . $dateInfo['year'];
        }

        $sales[2] = $dayWiseSales;


        // top categories

        $top_categories = OrderItems::join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'categories.id',
                'categories.name',
                'categories.image',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('COUNT(DISTINCT products.id) as product_count')
            )
            ->groupBy(
                'categories.id',
                'categories.name',
                'categories.image'
            )
            ->where('products.store_id', $store_id)
            ->orderByDesc('total_quantity')
            ->limit(6)
            ->get();

        $top_sellers = DB::table('order_items as oi')
            ->select([
                'oi.seller_id',
                DB::raw('SUM(oi.seller_commission_amount) as total_commission'),
                's.logo',
                's.store_name',
                DB::raw('(SELECT username FROM users as u WHERE u.id = s.user_id) as seller_name'),
                DB::raw('(SELECT SUM(i.sub_total) FROM order_items i WHERE i.seller_id = oi.seller_id AND i.active_status = "delivered") as total_sales'),
            ])
            ->leftJoin('seller_store as s', 's.seller_id', '=', 'oi.seller_id')
            ->where('s.store_id', $store_id)
            ->groupBy('oi.seller_id', 's.logo', 's.store_name')
            ->orderByDesc('total_sales')
            ->limit(6)
            ->get();

        return view('admin.pages.forms.home', compact('order_counter', 'id', 'store_id', 'user_counter', 'delivery_boy_counter', 'currency', 'top_sellers', 'total_products', 'top_categories', 'total_store', 'total_seller', 'total_earnings', 'role_id', 'store_details', 'primary_colour', 'messengerColor', 'dark_mode', 'sales'));
    }

    private function getMonthlyData($type, $store_id)
    {
        return DB::table('order_items')
            ->selectRaw("SUM($type) as total, DATE_FORMAT(created_at, '%b') AS month_name")
            ->where('store_id', $store_id)
            ->groupBy(DB::raw('YEAR(CURDATE()), MONTH(created_at)'))
            ->orderBy(DB::raw('YEAR(CURDATE()), MONTH(created_at)'))
            ->get()
            ->toArray();
    }


    private function getWeeklySalesData($tableName, $dateColumn, $revenueColumn, $commissionColumn, $salesColumn, $store_id)
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        // Initialize the week-wise sales data structure
        $weekWiseSales = [
            'total_revenue' => array_fill(0, 7, 0), // 0 for each day of the week
            'total_commission' => array_fill(0, 7, 0),
            'total_sales' => array_fill(0, 7, 0),
            'week' => [],
        ];

        // Fetch sales data from the database
        $res = DB::table($tableName)
            ->selectRaw("DATE_FORMAT($dateColumn, '%d-%b') as date,
            SUM($revenueColumn) as total_revenue,
            SUM($commissionColumn) as total_commission,
            SUM($salesColumn) as total_sales")
            ->where('store_id', $store_id)
            ->whereBetween(DB::raw("date($dateColumn)"), [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
            ->groupBy(DB::raw("DATE($dateColumn)"))
            ->orderBy(DB::raw("DATE($dateColumn)")) // Order results by date for consistency
            ->get()
            ->toArray();
        // Populate the week-wise sales data
        foreach ($res as $dayData) {
            // Get the day index based on the difference from the start of the week
            $dayIndex = Carbon::createFromFormat('d-M', $dayData->date)->diffInDays($startOfWeek);

            // Set the corresponding revenue, commission, and sales for that day
            $weekWiseSales['total_revenue'][$dayIndex] = intval($dayData->total_revenue);
            $weekWiseSales['total_commission'][$dayIndex] = intval($dayData->total_commission);
            $weekWiseSales['total_sales'][$dayIndex] = intval($dayData->total_sales);
            $weekWiseSales['week'][$dayIndex] = $dayData->date;
        }

        // Fill in the week with day names for all 7 days
        foreach (range(0, 6) as $i) {
            if (!isset($weekWiseSales['week'][$i])) {
                $weekWiseSales['week'][$i] = $startOfWeek->copy()->addDays($i)->format('d-M');
            }
        }

        return $weekWiseSales;
    }

    private function getDailySalesData($tableName, $dateColumn, $revenueColumn, $commissionColumn, $salesColumn, $store_id, $daysBack)
    {
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays($daysBack);

        $res = DB::table($tableName)
            ->selectRaw("DAY(created_at) as date, SUM($revenueColumn) as total_revenue, SUM($commissionColumn) as total_commission, SUM($salesColumn) as total_sales")
            ->where('store_id', $store_id)
            ->where($dateColumn, '>=', $startDate)
            ->groupBy(DB::raw("DAY($dateColumn)"))
            ->get()
            ->toArray();

        return $res;
    }
}
