<?php

namespace App\Http\Controllers\Admin;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {


        $rules = [
            'mobile' => 'numeric',
            'alternate_mobile' => 'numeric',
            'pincode_name' => 'numeric',
            'pincode' => 'numeric',
        ];

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }
        $address_data = [];

        if (request()->filled('user_id')) {
            $address_data['user_id'] = request('user_id');
        }
        if (request()->filled('id')) {
            $address_data['id'] = request('id');
        }
        if (request()->filled('type')) {
            $address_data['type'] = request('type');
        }
        if (request()->filled('name')) {
            $address_data['name'] = request('name');
        }
        if (request()->filled('mobile')) {
            $address_data['mobile'] = request('mobile');
        }
        $address_data['country_code'] = (request()->filled('country_code') && is_numeric(request('country_code'))) ? request('country_code') : 0;
        if (request()->filled('alternate_mobile')) {
            $address_data['alternate_mobile'] = request('alternate_mobile');
        }
        if (request()->filled('address')) {
            $address_data['address'] = request('address');
        }
        if (request()->filled('landmark')) {
            $address_data['landmark'] = request('landmark');
        }
        $city = fetchDetails('cities', ['id' => request('city_id')], 'name');
        $area = fetchDetails('areas', ['id' => request('area_id')], 'name');

        if (request()->filled('general_area_name')) {
            $address_data['area'] = $request->input('general_area_name', '');
        }
        if (request()->filled('edit_general_area_name')) {
            $address_data['area'] = $request->input('edit_general_area_name', '');
        }
        if (request()->filled('city_id')) {
            $address_data['city_id'] = $request->input('city_id', 0);
            // $address_data['city'] = isset($city) && !empty($city) ?  $city[0]->name : '';
            $address_data['city'] = isset($city) && !empty($city) ?  json_decode($city[0]->name)->en : '';
        }

        if (request()->filled('city_name')) {

            $address_data['city'] = $request->input('city_name');
        }
        if (request()->filled('area_name')) {
            $address_data['area'] = $request->input('area_name', !empty($area[0]->name) ?? '');
        }
        if (request()->filled('other_city')) {
            $address_data['city'] = $request->input('other_city', $city[0]->name);
        }
        if (request()->filled('other_areas')) {
            $address_data['area'] = $request->input('other_areas', !empty($area[0]->name) ?? '');
        }
        if (request()->filled('pincode_name') || request()->filled('pincode')) {
            $address_data['system_pincode'] = $request->input('pincode_name') ? 0 : 1;
            $address_data['pincode'] =  $request->input('pincode_name', $request->input('pincode'));
        }
        if (request()->filled('state')) {
            $address_data['state'] = $request->input('state');
        }
        if (request()->filled('country')) {
            $address_data['country'] = $request->input('country');
        }
        if (request()->filled('latitude')) {
            $address_data['latitude'] = $request->input('latitude');
        }
        if (request()->filled('longitude')) {
            $address_data['longitude'] = $request->input('longitude');
        }
        if (request()->filled('id')) {

            if (request()->filled('is_default') && ($request->input('is_default') == true || $request->input('is_default') == 1)) {
                $address = fetchDetails('addresses', ['id' => $request->input('id')], '*');
                updateDetails(['is_default' => '0'], ['user_id' => $address[0]->user_id], "addresses");
                updateDetails(['is_default' => '1'], ['id' => $request->input('id')], "addresses");
            }
            updateDetails($address_data, ['id' => $request->input('id')], "addresses");
        } else {

            $lastInsertId = DB::table('addresses')->insertGetId($address_data);
            if (request()->filled('is_default') && ($request->input('is_default') == true || $request->input('is_default') == 1)) {
                updateDetails(['is_default' => '0'], ['user_id' => request('user_id')], "addresses");
                updateDetails(['is_default' => '1'], ['id' => $lastInsertId], "addresses");
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $address = Address::find($id);

        if ($address->delete()) {
            return response()->json(['error' => false, 'message' => labels('admin_labels.address_deleted_successfully', 'Address Deleted Successfully')]);
        } else {
            return response()->json(['error' => labels('admin_labels.something_went_wrong', 'Something went wrong')]);
        }
    }

    public function getAddress($user_id, $id = null, $fetch_latest = false, $is_default = false)
    {

        $query = DB::table('addresses as addr')
            ->select('addr.*')
            ->where(function ($query) use ($user_id, $id) {
                if ($user_id !== null) {
                    $query->where('user_id', $user_id);
                }
                if ($id !== null) {
                    $query->where('addr.id', $id);
                }
            })
            ->groupBy('addr.id')
            ->orderByDesc('addr.id');

        if ($fetch_latest) {
            $query->limit(1);
        }

        if ($is_default) {
            $query->where('is_default', true);
        }

        $addresses = $query->get();

        $addresses = $addresses->map(function ($address) {

            $zipcode = $address->pincode ?? null;
            $zipcode_id = fetchDetails('zipcodes', ['zipcode' => $zipcode], 'id');
            $zipcode_id = isset($zipcode_id) && !empty($zipcode_id) ? $zipcode_id[0]->id : '';
            $zipcode_id = $zipcode_id ?? '';

            $minimumFreeDeliveryOrderAmount = fetchDetails('zipcodes', ['id' => $zipcode_id], ['minimum_free_delivery_order_amount', 'delivery_charges']);

            $address->minimum_free_delivery_order_amount = optional($minimumFreeDeliveryOrderAmount)->minimum_free_delivery_order_amount ?? 0;
            $address->delivery_charges = optional($minimumFreeDeliveryOrderAmount)->delivery_charges ?? 0;
            $address->area_id = $address->area_id ?? "";
            $address->city_id = $address->city_id ?? "";
            $address->latitude = $address->latitude ?? "";
            $address->longitude = $address->longitude ?? "";
            $address->landmark = $address->landmark ?? "";
            return $address;
        });

        return $addresses->toArray();
    }
}
