<?php

namespace App\Http\Controllers\Seller;

use App\Models\User;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Http\Request;
use App\Models\PickupLocation;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PickupLocationController extends Controller
{
    public function index()
    {
        return view('seller.pages.forms.pickup_locations');
    }

    public function store(Request $request)
    {

        $user_id = Auth::user()->id;
        $seller_id = Seller::where('user_id', $user_id)->value('id');
        $rules = [
            'pickup_location' => 'required',
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'city' => 'required',
            'state' => 'required',
            'country' => 'required',
            'pincode' => 'required',
            'address' => 'required',
            'address2' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ];

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }
        $location_data['seller_id'] = $seller_id ?? "";
        $location_data['pickup_location'] = $request->pickup_location ?? "";
        $location_data['name'] = $request->name ?? "";
        $location_data['email'] = $request->email ?? "";
        $location_data['phone'] = $request->phone ?? "";
        $location_data['city'] = $request->city ?? "";
        $location_data['country'] = $request->country ?? "";
        $location_data['state'] = $request->state ?? "";
        $location_data['pincode'] = $request->pincode ?? "";
        $location_data['address'] = $request->address ?? "";
        $location_data['address2'] = $request->address2 ?? "";
        $location_data['longitude'] = $request->longitude ?? "";
        $location_data['latitude'] = $request->latitude ?? "";
        $location_data['status'] = 1;


        PickupLocation::create($location_data);

        if ($request->ajax()) {
            return response()->json(['message' => labels('admin_labels.pickup_location_created_successfully', 'Pickup Location created successfully')]);
        }
    }

    public function list(Request $request)
    {
        $user_id = Auth::user()->id;
        $seller_id = Seller::where('user_id', $user_id)->value('id');

        $search = trim($request->input('search'));
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'ASC');

        $multiple_where = [];
        $where = [];

        if ($request->has('search') && trim($request->input('search')) !== '') {
            $multiple_where = [
                'pickup_locations.id' => $search,
                'pickup_locations.pickup_location' => $search,
                'pickup_locations.email' => $search,
                'pickup_locations.phone' => $search
            ];
        }

        if ($seller_id !== '') {
            $seller_id = $seller_id;
            $where = ['seller_id' => $seller_id];
        } elseif ($seller_id !== 0) {
            $where = ['seller_id' => $seller_id];
        }

        $count_res = DB::table('pickup_locations')->selectRaw('COUNT(id) as total');

        if (!empty($multiple_where)) {
            $count_res->orWhere(function ($query) use ($multiple_where) {
                foreach ($multiple_where as $column => $seacrh) {
                    $query->orWhere($column, 'LIKE', '%' . $seacrh . '%');
                }
            });
        }

        if (!empty($where)) {
            $count_res->where($where);
        }

        $total = $count_res->first()->total;

        $search_res = DB::table('pickup_locations')->select('*');

        if (!empty($multiple_where)) {
            $search_res->orWhere(function ($query) use ($multiple_where) {
                foreach ($multiple_where as $column => $seacrh) {
                    $query->orWhere($column, 'LIKE', '%' . $seacrh . '%');
                }
            });
        }

        if (!empty($where)) {
            $search_res->where($where);
        }

        $location_data = $search_res->orderBy($sort, $order)->limit($limit)->offset($offset)->get();

        $bulkData = [
            'total' => $total,
            'rows' => []
        ];

        foreach ($location_data as $row) {
            $tempRow = [];
            $tempRow['id'] = $row->id;
            $tempRow['pickup_location'] = $row->pickup_location;
            $tempRow['name'] = $row->name;
            $tempRow['email'] = $row->email;
            $tempRow['phone'] = $row->phone;
            $tempRow['address'] = $row->address;
            $tempRow['address2'] = $row->address2;
            $tempRow['city'] = $row->city;
            $tempRow['state'] = $row->state;
            $tempRow['country'] = $row->country;
            $tempRow['pincode'] = $row->pincode;
            $bulkData['rows'][] = $tempRow;
        }
        return response()->json($bulkData);
    }
}
