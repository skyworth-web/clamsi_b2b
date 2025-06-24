<?php

namespace App\Http\Controllers\Seller;

use App\Models\Category;
use App\Models\Seller;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\ChMessage as Message;
use App\Models\ChFavorite as Favorite;
use App\Models\ProductRating;
use Carbon\Carbon;
use Chatify\Facades\ChatifyMessenger as Chatify;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $id =  0;
        $currencyDetails = fetchDetails('currencies', ['is_default' => 1], 'symbol');
        $currency = !empty($currencyDetails) ? $currencyDetails[0]->symbol : '';
        $dark_mode = Auth::user()->dark_mode < 1 ? 'light' : 'dark';
        $role_id = Auth::user()->role_id;
        $store_id = getStoreId();
        $language_code = get_language_code();
        $store_details = fetchDetails('stores', ['id' => $store_id], ['primary_color', 'secondary_color', 'hover_color', 'active_color']);
        $primary_colour = (isset($store_details[0]->primary_color) && !empty($store_details[0]->primary_color)) ?  $store_details[0]->primary_color : '#B52046';
        $messengerColor = $primary_colour;
        $user_id = Auth::id();
        $seller_id = Seller::where('user_id', $user_id)->value('id');
        $total_balance = fetchDetails('users', ['id' => $user_id], 'balance')[0]->balance;
        $totalSale = DB::table('order_items')
            ->selectRaw('SUM(sub_total) as overall_sale')
            ->where('seller_id', $seller_id)
            ->where('store_id', $store_id)
            ->where('active_status', 'delivered')
            ->first();
        $overallSale =  $totalSale->overall_sale ?? 0;

        // -------------------------- get latest product ratings -----------------------------------

        $latestRatings = ProductRating::select('product_ratings.*', 'products.name as product_name', 'product_variants.price', 'product_variants.special_price', 'products.image as product_image', 'users.username', 'users.image as user_image')
            ->leftJoin('products', 'product_ratings.product_id', '=', 'products.id')
            ->leftJoin('product_variants', 'product_ratings.product_id', '=', 'product_variants.product_id')
            ->leftJoin('users', 'product_ratings.user_id', '=', 'users.id')
            ->where('products.seller_id', $seller_id)
            ->where('products.store_id', $store_id)
            ->orderBy('product_ratings.created_at', 'desc')
            ->limit(5)
            ->groupBy('product_ratings.id')
            ->get();


        //-------------------------------- get seller order overview statistics ------------------------------------

        $sales = [];

        // monthly earnings

        $monthRes = DB::table('order_items')
            ->selectRaw('SUM(quantity) AS total_sale, SUM(sub_total) AS total_revenue, COUNT(*) AS total_orders, DATE_FORMAT(created_at, "%b") AS month_name')
            ->where('seller_id', $seller_id)
            ->where('store_id', $store_id)
            ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at)'))
            ->orderBy(DB::raw('YEAR(created_at), MONTH(created_at)'))
            ->get()->toArray();



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

        // Merge the database results with the allMonths array, replacing existing values

        $monthWiseSalesDetail = $allMonths;

        foreach ($monthRes as $month) {
            $monthName = $month->month_name;
            $totalSale = intval($month->total_sale);
            $totalOrders = intval($month->total_orders);
            $totalRevenue = intval($month->total_revenue);

            $monthWiseSalesDetail[$monthName] = [
                'total_sale' => $totalSale,
                'total_orders' => $totalOrders,
                'total_revenue' => $totalRevenue
            ];
        }

        // Extracting individual arrays
        $totalSales = [];
        $totalOrders = [];
        $totalRevenues = [];

        foreach ($monthWiseSalesDetail as $monthName => $monthData) {
            $totalSales[] = isset($monthData['total_sale']) ? $monthData['total_sale'] : 0;
            $totalOrders[] = isset($monthData['total_orders']) ? $monthData['total_orders'] : 0;
            $totalRevenues[] = isset($monthData['total_revenue']) ? $monthData['total_revenue'] : 0;
        }

        $monthWiseSales['total_sale'] = $totalSales;
        $monthWiseSales['total_orders'] = $totalOrders;
        $monthWiseSales['total_revenue'] = $totalRevenues;
        $monthWiseSales['month_name'] = array_keys($monthWiseSalesDetail);

        $sales[0] = $monthWiseSales;
        // weekly earnings

        //this is for current week data
        $startDate = Carbon::now()->startOfWeek(); // Start of the current week (Sunday)
        $endDate = Carbon::now()->endOfWeek(); // End of the current week (Saturday)





        // Initialize an array to hold the data for each day of the week
        $weekWiseSales = [
            'total_sale' => [],
            'day' => []
        ];

        $allDaysOfWeek = [
            'Sunday' => 0,
            'Monday' => 0,
            'Tuesday' => 0,
            'Wednesday' => 0,
            'Thursday' => 0,
            'Friday' => 0,
            'Saturday' => 0
        ];

        // Loop to retrieve data for each day of the week
        for ($i = 0; $i < 7; $i++) {
            $currentDate = $startDate->copy()->addDays($i);
            $dayName = $currentDate->englishDayOfWeek; // Get the day name (e.g., "Sunday", "Monday")

            $dayRes = DB::table('order_items')
                ->selectRaw("SUM(quantity) as total_sale, SUM(sub_total) as total_revenue, COUNT(*) as total_orders")
                ->where('seller_id', $seller_id)
                ->where('store_id', $store_id)
                ->whereDate('created_at', $currentDate->format('Y-m-d'))
                ->first();

            // If data exists for the current day, add it to the weekWiseSales array
            if ($dayRes) {
                $weekWiseSales['total_sale'][] = intval($dayRes->total_sale);
                $weekWiseSales['total_revenue'][] = intval($dayRes->total_revenue);
                $weekWiseSales['total_orders'][] = intval($dayRes->total_orders);
                $weekWiseSales['day'][] = $dayName;
            } else {
                // If no data exists for the current day, set total_sale, total_revenue, and total_orders to 0
                $weekWiseSales['total_sale'][] = 0;
                $weekWiseSales['total_revenue'][] = 0;
                $weekWiseSales['total_orders'][] = 0;
                $weekWiseSales['day'][] = $dayName;
            }
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

        // Fetch data from the database
        $dayRes = DB::table('order_items')
            ->selectRaw("DAY(created_at) as date, SUM(quantity) as total_sale, SUM(sub_total) as total_revenue, COUNT(*) as total_orders")
            ->where('seller_id', $seller_id)
            ->where('store_id', $store_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DAY(created_at)'))
            ->get()
            ->toArray();

        // Create an associative array with date as key for easier merging
        $dayData = [];
        foreach ($dayRes as $day) {
            $dayData[$day->date] = [
                'total_sale' => intval($day->total_sale),
                'total_revenue' => intval($day->total_revenue),
                'total_orders' => intval($day->total_orders)
            ];
        }

        // Merge fetched data with all dates of the month, filling missing dates with zeros
        $dayWiseSales = [];
        foreach ($allDatesOfMonth as $dateInfo) {
            $date = $dateInfo['date'];
            if (isset($dayData[$date])) {
                $dayWiseSales['total_sale'][] = $dayData[$date]['total_sale'];
                $dayWiseSales['total_revenue'][] = $dayData[$date]['total_revenue'];
                $dayWiseSales['total_orders'][] = $dayData[$date]['total_orders'];
            } else {
                $dayWiseSales['total_sale'][] = 0;
                $dayWiseSales['total_revenue'][] = 0;
                $dayWiseSales['total_orders'][] = 0;
            }
            $dayWiseSales['day'][] = $date . '-' . $dateInfo['month'] . '-' . $dateInfo['year'];
        }

        $sales[2] = $dayWiseSales;


        // ============================= Most popular category data for chart ================================
        $topSellingCategories = [];

        //monthly data for category chart

        $firstDayOfMonth = Carbon::now()->startOfMonth();
        $lastDayOfMonth = Carbon::now()->endOfMonth();

        $topSellingCategoriesDataMonthRes = DB::table('order_items')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.id as category_id', 'categories.name as category_name', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->where('order_items.seller_id', '=', $seller_id)
            ->where('order_items.store_id', '=', $store_id)
            ->whereBetween('order_items.created_at', [$firstDayOfMonth, $lastDayOfMonth])
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        // Apply dynamic translation to category names
        $monthlyTopSellingCategoriesData['totalSold'] = $topSellingCategoriesDataMonthRes->pluck('total_sold');

        $monthlyTopSellingCategoriesData['categoryNames'] = $topSellingCategoriesDataMonthRes->map(function ($item) use ($language_code) {
            return getDynamicTranslation('categories', 'name', $item->category_id, $language_code);
        });


        $topSellingCategories[0] = $monthlyTopSellingCategoriesData;



        //Yearly data for category chart

        $currentYear = Carbon::now()->year;

        $firstDayOfYear = Carbon::create($currentYear, 1, 1)->startOfDay();
        $lastDayOfYear = Carbon::create($currentYear, 12, 31)->endOfDay();

        $topSellingCategoriesDataYearRes = DB::table('order_items')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.id as category_id', 'categories.name as category_name', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->where('order_items.seller_id', '=', $seller_id)
            ->where('order_items.store_id', '=', $store_id)
            ->whereBetween('order_items.created_at', [$firstDayOfYear, $lastDayOfYear])
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        $yearlyTopSellingCategoriesData['totalSold'] = $topSellingCategoriesDataYearRes->pluck('total_sold');

        $yearlyTopSellingCategoriesData['categoryNames'] = $topSellingCategoriesDataYearRes->map(function ($item) use ($language_code) {
            return getDynamicTranslation('categories', 'name', $item->category_id, $language_code);
        });

        // $yearlyTopSellingCategoriesData['categoryNames'] = $topSellingCategoriesDataYearRes->pluck('category_name');

        $topSellingCategories[1] = $yearlyTopSellingCategoriesData;

        //weekly data for category chart

        //for current week
        $startDate = Carbon::now()->startOfWeek();
        $endDate = Carbon::now()->endOfWeek();

        $topSellingCategoriesDataWeekRes = DB::table('order_items')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.id as category_id', 'categories.name as category_name', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->where('order_items.seller_id', '=', $seller_id)
            ->where('order_items.store_id', '=', $store_id)
            ->whereBetween('order_items.created_at', [$startDate, $endDate])
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        $weeklyTopSellingCategoriesData['totalSold'] = $topSellingCategoriesDataWeekRes->pluck('total_sold');

        $weeklyTopSellingCategoriesData['categoryNames'] = $topSellingCategoriesDataWeekRes->map(function ($item) use ($language_code) {
            return getDynamicTranslation('categories', 'name', $item->category_id, $language_code);
        });


        $topSellingCategories[2] = $weeklyTopSellingCategoriesData;

        $seller_rating =
            $topSellingCategoriesDataWeekRes = DB::table('seller_store')->select('rating', 'no_of_ratings')
            ->where('seller_store.seller_id', '=', $seller_id)
            ->where('seller_store.store_id', '=', $store_id)->get();



        // ============================================ store average rating ===========================================


        $currentYear = Carbon::now()->year;

        // Initialize ratings array with 0 for all months of the current year
        $ratings = array_fill(1, 12, 0);

        // Get simple product ratings for the current year
        $simpleProductRatings = DB::table('product_ratings')
            ->select(DB::raw('MONTH(product_ratings.created_at) as month, COUNT(*) as total_ratings, AVG(product_ratings.rating) as avg_rating'))
            ->join('products', 'product_ratings.product_id', '=', 'products.id')
            ->where('products.store_id', $store_id)
            ->where('products.seller_id', $seller_id)
            ->whereYear('product_ratings.created_at', $currentYear)
            ->groupBy(DB::raw('MONTH(product_ratings.created_at)'))
            ->get();

        // Get combo product ratings for the current year
        $comboProductRatings = DB::table('combo_product_ratings')
            ->select(DB::raw('MONTH(combo_product_ratings.created_at) as month, COUNT(*) as total_ratings, AVG(combo_product_ratings.rating) as avg_rating'))
            ->join('combo_products', 'combo_product_ratings.product_id', '=', 'combo_products.id')
            ->where('combo_products.store_id', $store_id)
            ->where('combo_products.seller_id', $seller_id)
            ->whereYear('combo_product_ratings.created_at', $currentYear)
            ->groupBy(DB::raw('MONTH(combo_product_ratings.created_at)'))
            ->get();

        // Update ratings array with actual ratings
        foreach ($simpleProductRatings as $rating) {
            $month = $rating->month;
            $ratings[$month] += $rating->total_ratings;
        }

        foreach ($comboProductRatings as $rating) {
            $month = $rating->month;
            $ratings[$month] += $rating->total_ratings;
        }

        // Calculate the total ratings and average rating for the whole year
        $totalRatings = 0;
        $averageRating = 0;
        foreach ($ratings as $monthTotal) {
            $totalRatings += $monthTotal;
        }


        // Get month names
        $monthNames = [
            1 => "Jan",
            2 => "Feb",
            3 => "Mar",
            4 => "Apr",
            5 => "May",
            6 => "Jun",
            7 => "Jul",
            8 => "Aug",
            9 => "Sep",
            10 => "Oct",
            11 => "Nov",
            12 => "Dec"
        ];

        // Prepare the final output
        $store_rating = [
            'total_ratings' => array_values($ratings),
            'month_name' => array_map(function ($month) use ($monthNames) {
                return $monthNames[$month];
            }, array_keys($ratings)),

        ];

        $seller_categories = DB::table('seller_store')
            ->select('category_ids')
            ->where('store_id', $store_id)
            ->where('seller_id', $seller_id)
            ->get();

        $category_ids = $seller_categories->isNotEmpty()
            ? explode(",", $seller_categories[0]->category_ids)
            : [];

        $categories = Category::select('id', 'name')
            ->whereIn('id', $category_ids)
            ->where('status', 1)
            ->where('store_id', $store_id)
            ->get();
        return view('seller.pages.forms.home', compact('store_id', 'seller_id', 'id', 'messengerColor', 'dark_mode', 'role_id', 'currency', 'total_balance', 'overallSale', 'latestRatings', 'sales', 'language_code', 'topSellingCategories', 'seller_rating', 'store_rating', 'categories'));
    }

    public function topSellingProducts(Request $request)
    {
        $store_id = getStoreId();
        $user_id = Auth::id();
        $seller_id = Seller::where('user_id', $user_id)->value('id');
        $category_id = $request->input('category_id');
        $language_code = get_language_code();
        $query = DB::table('order_items')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->select(
                'products.id as product_id',
                'products.image as product_image',
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_sold')
            )
            ->where('order_items.seller_id', '=', $seller_id)
            ->where('order_items.store_id', '=', $store_id);

        if (!empty($category_id)) {
            $query->where('products.category_id', '=', $category_id);
        }

        $top_selling_products = $query->groupBy('products.id', 'products.name', 'products.image')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        $data = $top_selling_products->map(function ($item) use ($language_code) {
            return [
                'product_image' => $item->product_image,
                'name' => getDynamicTranslation('products', 'name', $item->product_id, $language_code),
                'total_sold' => $item->total_sold,
            ];
        });

        return response()->json([
            "data" => $data,
        ]);
    }


    public function mostPopularProduct(Request $request)
    {
        $store_id = getStoreId();
        $user_id = Auth::id();
        $seller_id = Seller::where('user_id', $user_id)->value('id');
        $category_id = $request->input('category_id');
        $language_code = $request->input('language_code', 'en'); // default to English

        $topRatedProducts = DB::table('product_ratings')
            ->join('products', 'product_ratings.product_id', '=', 'products.id')
            ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
            ->join('order_items', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->select(
                'products.id as product_id',
                'products.image as product_image',
                'products.name',
                DB::raw('ROUND(AVG(product_ratings.rating), 2) as average_rating'),
                DB::raw('COUNT(product_ratings.id) as total_reviews')
            )
            ->groupBy('products.id', 'products.name', 'products.image')
            ->where('order_items.seller_id', '=', $seller_id)
            ->where('order_items.store_id', '=', $store_id)
            ->orderByDesc('average_rating')
            ->limit(5);

        if (!empty($category_id)) {
            $topRatedProducts->where('products.category_id', '=', $category_id);
        }

        $result = $topRatedProducts->get();

        $translatedResult = $result->map(function ($item) use ($language_code) {
            return [
                'product_image' => $item->product_image,
                'name' => getDynamicTranslation('products', 'name', $item->product_id, $language_code),
                'average_rating' => $item->average_rating,
                'total_reviews' => $item->total_reviews,
            ];
        });

        return response()->json([
            "data" => $translatedResult,
        ]);
    }


    public function get_statistics()
    {
        $store_id = getStoreId();
        $user_id = Auth::id();
        $seller_id = Seller::where('user_id', $user_id)->value('id');
        $sales = [];

        // monthly earnings

        $monthRes = DB::table('order_items')
            ->selectRaw('SUM(sub_total) AS total_sale, DATE_FORMAT(created_at, "%b") AS month_name')
            ->where('seller_id', $seller_id)
            ->where('store_id', $store_id)
            ->groupBy(DB::raw('YEAR(CURDATE()), MONTH(created_at)'))
            ->orderBy(DB::raw('YEAR(CURDATE()), MONTH(created_at)'))
            ->get()->toArray();

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

        // Merge the database results with the allMonths array, replacing existing values
        $monthWiseSalesDetail = array_merge($allMonths, array_combine(array_column($monthRes, 'month_name'), array_map('intval', array_column($monthRes, 'total_sale'))));

        $monthWiseSales['total_sale'] = array_values($monthWiseSalesDetail);
        $monthWiseSales['month_name'] = array_keys($monthWiseSalesDetail);

        $sales[0] = $monthWiseSales;

        $now = now();


        // weekly earnings

        $startOfWeek = Carbon::now()->startOfWeek(); // Start of the current week (Sunday)
        $endOfWeek = Carbon::now()->endOfWeek(); // End of the current week (Saturday)

        $weekRes = DB::table('order_items')
            ->selectRaw("DATE_FORMAT(created_at, '%d-%b') as date, SUM(sub_total) as total_sale")
            ->where('seller_id', $seller_id)
            ->where('store_id', $store_id)
            ->whereBetween(DB::raw('date(created_at)'), [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
            ->groupBy(DB::raw('DAY(created_at)'))
            ->get()->toArray();

        $weekWiseSales['total_sale'] = array_map('intval', array_column($weekRes, 'total_sale'));
        $weekWiseSales['week'] = array_column($weekRes, 'date');

        $sales[1] = $weekWiseSales;


        // daily earnings

        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays(29);

        $dayRes = DB::table('order_items')
            ->selectRaw("DAY(created_at) as date, SUM(sub_total) as total_sale")
            ->where('seller_id', $seller_id)
            ->where('store_id', $store_id)
            ->where('created_at', '>=', $startDate)
            ->groupBy(DB::raw('DAY(created_at)'))
            ->get()
            ->toArray();

        $dayWiseSales['total_sale'] = array_map('intval', array_column($dayRes, 'total_sale'));
        $dayWiseSales['day'] = array_column($dayRes, 'date');

        $sales[2] = $dayWiseSales;

        return response()->json([
            "sales" => $sales,

        ]);
    }

    public function get_most_selling_category()
    {
        $store_id = getStoreId();
        $user_id = Auth::id();
        $seller_id = Seller::where('user_id', $user_id)->value('id');

        $topSellingCategoriesData = DB::table('order_items')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name as category_name', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->where('order_items.seller_id', '=', 2)

            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        // Now, $topSellingCategories will be a collection of objects

        $topSellingCategories['totalSold'] = $topSellingCategoriesData->pluck('total_sold')->toArray();
        $topSellingCategories['categoryNames'] = $topSellingCategoriesData->pluck('category_name')->toArray();

        return response()->json([
            "topSellingCategories" => $topSellingCategories,

        ]);
    }
}
