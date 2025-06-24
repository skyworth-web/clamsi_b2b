<?php

namespace App\Http\Controllers\Seller;

use App\Models\Area;
use App\Models\City;
use App\Models\Language;
use App\Models\Seller;
use App\Models\Zone;
use App\Models\Setting;
use App\Models\Zipcode;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AreaController extends Controller
{

    // zipcode

    public function zipcodes()
    {
        $languages = Language::all();
        return view('seller.pages.tables.zipcodes', ['languages' => $languages]);
    }


    public function zipcode_list(Request $request, $language_code = '')
    {
        $search = trim(request('search'));
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : "0";
        $limit = (request('limit')) ? request('limit') : "10";
        $multipleWhere = [
            'zipcodes.id' => $search,
            'zipcodes.zipcode' => $search,
            'zipcodes.minimum_free_delivery_order_amount' => $search,
            'zipcodes.delivery_charges' => $search,
            'cities.name' => $search,
            'cities.id' => $search,

        ];


        $query = Zipcode::query();

        $query->select('zipcodes.*', 'cities.name as city_name', 'cities.id as city_id')
            ->leftJoin('cities', 'zipcodes.city_id', '=', 'cities.id');

        $query->where(function ($query) use ($multipleWhere) {
            foreach ($multipleWhere as $column => $value) {
                $query->orWhere($column, 'like', '%' . $value . '%');
            }
        });

        if (isset($where) && !empty($where)) {
            $query->where($where);
        }
        $total = $query->count();
        $language_code = isset($language_code) && !empty($language_code) ? $language_code : get_language_code();

        $search_query = DB::table('zipcodes');

        if (!Schema::hasColumn('zipcodes', 'city_id')) {
            $search_query->select('*');
        } else {
            $search_query->select('zipcodes.*', 'cities.name as city_name', 'cities.id as city_id')
                ->leftJoin('cities', 'zipcodes.city_id', '=', 'cities.id');
        }

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $search_query->where(function ($search_query) use ($multipleWhere) {
                foreach ($multipleWhere as $column => $value) {
                    $search_query->orWhere($column, 'like', '%' . $value . '%');
                }
            });
        }

        if (isset($where) && !empty($where)) {
            $search_query->where($where);
        }

        $result = $search_query->orderBy($sort, 'asc')
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->toArray();

        if (!empty($result)) {

            foreach ($result as $row) {

                $tempRow['id'] = $row->id;
                $tempRow['zipcode'] = $row->zipcode;
                if (!Schema::hasColumn('zipcodes', 'city_id')) {
                    $tempRow['city_name'] = '';
                    $tempRow['city_id'] = '';
                    $tempRow['minimum_free_delivery_order_amount'] = 0;
                    $tempRow['delivery_charges'] = 0;
                } else {
                    $tempRow['city_name'] = getDynamicTranslation('cities', 'name', $row->city_id, $language_code) ?? '';
                    $tempRow['city_id'] = $row->city_id ?? '';
                    $tempRow['minimum_free_delivery_order_amount'] = $row->minimum_free_delivery_order_amount ?? 0;
                    $tempRow['delivery_charges'] = $row->delivery_charges ?? 0;
                }

                $rows[] = $tempRow;
            }
            return response()->json([
                "rows" => $rows,
                "total" => $total,
            ]);
        }
    }


    // city

    public function city()
    {
        $languages = Language::all();
        return view('seller.pages.tables.city', ['languages' => $languages]);
    }




    public function city_list(Request $request, $language_code = '')
    {
        $search = trim(request('search'));
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : "0";
        $limit = (request('limit')) ? request('limit') : "10";
        $language_code = isset($language_code) && !empty($language_code) ? $language_code : get_language_code();
        $city_data = City::when($search, function ($query) use ($search) {
            return $query->where('name', 'like', '%' . $search . '%');
        });

        $total = $city_data->count();

        // Use Paginator to handle the server-side pagination
        $cities = $city_data->orderBy($sort, $order)->offset($offset)
            ->limit($limit)
            ->get();

        // Prepare the data for the "Actions" field
        $data = $cities->map(function ($c) use ($language_code) {
            return [
                'id' => $c->id ?? '',
                'name' => getDynamicTranslation('cities', 'name', $c->id, $language_code) ?? '',
                'text' => getDynamicTranslation('cities', 'name', $c->id, $language_code) ?? '',
                'minimum_free_delivery_order_amount' => $c->minimum_free_delivery_order_amount ?? '',
                'delivery_charges' => $c->delivery_charges ?? '',
            ];
        });

        return response()->json([
            "rows" => $data, // Return the formatted data for the "Actions" field
            "total" => $total,
        ]);
    }


    public function get_cities(Request $request)
    {
        $search = trim($request->search) ?? "";
        $cities = City::where('name', 'like', '%' . $search . '%')->get();

        $data = array();
        foreach ($cities as $city) {
            $data[] = array("id" => $city->id, "text" => $city->name);
        }
        return response()->json($data);
    }

    public function get_zipcodes(Request $request)
    {
        $search = trim($request->search) ?? "";
        $zipcodes = Zipcode::where('zipcode', 'like', '%' . $search . '%')->get();

        $data = array();
        foreach ($zipcodes as $zipcode) {
            $data[] = array("id" => $zipcode->id, "text" => $zipcode->zipcode);
        }
        return response()->json($data);
    }

    public function getCities(Request $request)
    {
        $search = trim($request->search) ?? "";
        $cities = City::where('name', 'like', '%' . $search . '%')->get();
        $language_code = get_language_code();
        $data = array();
        foreach ($cities as $city) {
            $data[] = array("id" => $city->id, "text" => getDynamicTranslation('cities', 'name', $city->id, $language_code));
        }
        return response()->json($data);
    }

    public function zone_data(Request $request)
    {
        $store_id = getStoreId();
        $user_id = Auth::user()->id;
        $seller_id = Seller::where('user_id', $user_id)->value('id');
        $seller_zones = fetchDetails('seller_store', ['seller_id' => $seller_id, 'store_id' => $store_id], ['deliverable_type', 'deliverable_zones']);
        $seller_zones = isset($seller_zones) && !empty($seller_zones) ? $seller_zones[0] : [];
        $search = trim($request->input('search'));

        $limit = (int) $request->input('limit', 50);

        $query = Zone::where('status', 1)
            ->where(function ($query) use ($search) {
                $query->where('id', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%');
            });

        if ($seller_zones->deliverable_type == '2' || $seller_zones->deliverable_type == '3') {
            $zone_ids = explode(',', $seller_zones->deliverable_zones);
            $query->whereIn('id', $zone_ids);
        }
        $zones = $query->limit($limit)->get(['id', 'name', 'serviceable_city_ids', 'serviceable_zipcode_ids']);
        $total = $query->count();

        $cities = [];
        $zipcodes = [];
        $language_code = get_language_code();
        foreach ($zones as $zone) {
            $city_ids = explode(',', $zone->serviceable_city_ids);
            $zipcode_ids = explode(',', $zone->serviceable_zipcode_ids);

            $cities = array_unique(array_merge($cities, $city_ids));
            $zipcodes = array_unique(array_merge($zipcodes, $zipcode_ids));
        }

        $city_names = City::whereIn('id', $cities)->pluck('name', 'id')->toArray();

        $zipcode_names = Zipcode::whereIn('id', $zipcodes)->pluck('zipcode', 'id')->toArray();

        $response = [
            'total' => $total,
            'results' => $zones->map(function ($zone) use ($city_names, $zipcode_names, $language_code) {
                $city_ids = explode(',', $zone->serviceable_city_ids);
                $zipcode_ids = explode(',', $zone->serviceable_zipcode_ids);

                return [
                    'id' => $zone->id,
                    'text' => getDynamicTranslation('zones', 'name', $zone->id, $language_code),
                    'serviceable_cities' => implode(', ', array_map(function ($city_id) use ($language_code) {
                        return getDynamicTranslation('zones', 'name', $city_id, $language_code);
                    }, $city_ids)),
                    'serviceable_zipcodes' => implode(', ', array_map(function ($zipcode_id) use ($zipcode_names) {
                        return $zipcode_names[$zipcode_id] ?? null;
                    }, $zipcode_ids)),
                ];
            }),
        ];

        return response()->json($response);
    }

    public function zones()
    {
        return view('seller.pages.tables.zones');
    }

    public function get_zones(Request $request)
    {
        // dd($request);
        $search = trim($request->input('term', $request->input('q', '')));
        $limit = (int) $request->input('limit', 50);

        // Start the query with 'status = 1'
        $query = Zone::where('status', 1);

        // Only apply search if a valid string is provided
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%'); // Search only in name field
            });
        }

        $zones = $query->limit($limit)->get(['id', 'name', 'serviceable_city_ids', 'serviceable_zipcode_ids']);
        $total = $query->count();

        $cities = [];
        $zipcodes = [];
        $language_code = get_language_code();
        foreach ($zones as $zone) {
            $city_ids = array_filter(explode(',', $zone->serviceable_city_ids));
            $zipcode_ids = array_filter(explode(',', $zone->serviceable_zipcode_ids));

            $cities = array_unique(array_merge($cities, $city_ids));
            $zipcodes = array_unique(array_merge($zipcodes, $zipcode_ids));
        }

        $city_names = City::whereIn('id', $cities)->pluck('name', 'id')->toArray();
        $zipcode_names = Zipcode::whereIn('id', $zipcodes)->pluck('zipcode', 'id')->toArray();

        $response = [
            'total' => $total,
            'results' => $zones->map(function ($zone) use ($city_names, $zipcode_names, $language_code) {
                $city_ids = array_filter(explode(',', $zone->serviceable_city_ids));
                $zipcode_ids = array_filter(explode(',', $zone->serviceable_zipcode_ids));

                return [
                    'id' => $zone->id,
                    'text' => getDynamicTranslation('zones', 'name', $zone->id, $language_code),
                    'serviceable_cities' => implode(', ', array_map(function ($city_id) use ($language_code) {
                        return getDynamicTranslation('zones', 'name', $city_id, $language_code);
                    }, $city_ids)),
                    'serviceable_zipcodes' => implode(', ', array_map(fn($zipcode_id) => $zipcode_names[$zipcode_id] ?? null, $zipcode_ids)),
                ];
            }),
        ];

        return response()->json($response);
    }


    public function zone_list(Request $request)
    {
        $search = trim($request->input('search'));
        $limit = (int) $request->input('limit', 10);
        $offset = (int) $request->input('pagination_offset', 0);
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'DESC');
        $user_id = Auth::user()->id;

        // Fetch seller ID
        $seller_id = Seller::where('user_id', $user_id)->value('id');

        // Fetch seller's deliverable type and zones
        $seller = DB::table('seller_store as ss')
            ->where('ss.seller_id', $seller_id)
            ->select('ss.deliverable_type', 'ss.deliverable_zones')
            ->first();

        // Query to filter and fetch zones
        $query = Zone::where('status', 1)
            ->when($search, function ($query) use ($search) {
                return $query->where('id', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%');
            });

        // If deliverable_type is 2, filter zones by deliverable_zones
        if ($seller && $seller->deliverable_type == 2) {
            $deliverable_zone_ids = explode(',', $seller->deliverable_zones);
            $query->whereIn('id', $deliverable_zone_ids);
        }

        $total = $query->count();
        $language_code = get_language_code();
        // Fetch paginated results
        $zones = $query->orderBy($sort, $order)
            ->offset($offset)
            ->limit($limit)
            ->get(['id', 'name', 'serviceable_city_ids', 'serviceable_zipcode_ids']);

        // Extract unique city and zipcode IDs
        $city_ids = [];
        $zipcode_ids = [];

        foreach ($zones as $zone) {
            $city_ids = array_merge($city_ids, explode(',', $zone->serviceable_city_ids));
            $zipcode_ids = array_merge($zipcode_ids, explode(',', $zone->serviceable_zipcode_ids));
        }

        $city_ids = array_unique(array_filter($city_ids));
        $zipcode_ids = array_unique(array_filter($zipcode_ids));

        // Fetch city and zipcode names
        $city_names = City::whereIn('id', $city_ids)->pluck('name', 'id')->toArray();
        $zipcode_names = Zipcode::whereIn('id', $zipcode_ids)->pluck('zipcode', 'id')->toArray();

        // Format response data
        $data = $zones->map(function ($zone) use ($city_names, $zipcode_names, $language_code) {
            return [
                'id' => $zone->id,
                'name' => getDynamicTranslation('zones', 'name', $zone->id, $language_code),
                'serviceable_cities' => implode(', ', array_map(fn($id) => getDynamicTranslation('zones', 'name', $id, $language_code), explode(',', $zone->serviceable_city_ids))),
                'serviceable_zipcodes' => implode(', ', array_map(fn($id) => $zipcode_names[$id] ?? '', explode(',', $zone->serviceable_zipcode_ids))),
            ];
        });

        return response()->json([
            'total' => $total,
            'rows' => $data,
        ]);
    }
}
