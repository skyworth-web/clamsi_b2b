<?php

namespace App\Livewire\MyAccount;

use App\Http\Controllers\AddressController;
use App\Models\Address;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class Addresses extends Component
{
    protected $listeners = ['refreshComponent', 'deleteAddress'];

    public function render(AddressController $addressController)
    {
        $user = Auth::user();
        $res = $this->get_Address($addressController);
        return view('livewire.' . config('constants.theme') . '.my-account.addresses', [
            'user_info' => $user,
            'addresses' => $res
        ])->title("Addresses |");
    }

    public function get_address($addressController)
    {
        $user = Auth::user();
        $res = $addressController->getAddress($user->id);
        return $res;
    }

    // public function add_address(Request $request)
    // {
    //     $user_id = Auth::user()->id ?? "";
    //     $validator = Validator::make(
    //         $request->all(),
    //         [
    //             'name' => 'required|string',
    //             'type' => 'required',
    //             'mobile' => 'required|digits_between:1,16|numeric',
    //             'alternate_mobile' => 'nullable|digits_between:1,16|numeric',
    //             'address' => 'required',
    //             'landmark' => 'required',
    //             'city' => 'required',
    //             'pincode' => 'required',
    //             'state' => 'required',
    //             'country' => 'required',
    //             'latitude' => 'nullable|numeric',
    //             'longitude' => 'nullable|numeric',
    //         ]
    //     );
    //     if ($validator->fails()) {
    //         $errors = $validator->errors();
    //         $response['error'] = true;
    //         $response['message'] = $errors;
    //         return $response;
    //     }

    //     // Fetch city_id based on the selected city name
    //     $request['user_id'] = $user_id;
    //     $cityName = $request['city'];
    //     // dd($cityName);
    //     $city = City::where('name', $cityName)->first();
    //     $city_id = $city ? $city->id : null;
    //     // dd($city);
    //     $city_name = json_decode($cityName)->en;
    //     // Fetch country_code based on the selected country name
    //     $countryName = $request['country'];
    //     $country = DB::table('countries')
    //         ->select('*')
    //         ->where('name', $countryName)
    //         ->first();

    //     $country_code = $country ? $country->phonecode : null;
    //     // Add city_id and country_code to address data
    //     $request['city_id'] = $city_id;
    //     $request['city'] = $city_name;
    //     $request['country_code'] = $country_code;
    //     $address_data = $request->only([
    //         'user_id',
    //         'name',
    //         'type',
    //         'mobile',
    //         'alternate_mobile',
    //         'address',
    //         'landmark',
    //         'city',
    //         'city_id',
    //         'pincode',
    //         'country',
    //         'state',
    //         'latitude',
    //         'longitude',
    //         'country_code'
    //     ]);
    //     // dd($address_data);
    //     // if (isset($request->address_id)) {
    //     //     $address_id = $request->address_id;
    //     //     $res = updateDetails($address_data, ['id' => $address_id], 'addresses');
    //     //     if (!$res) {
    //     //         $response = [
    //     //             'error' => true,
    //     //             'message' => 'Failed to add address. Please try again.'
    //     //         ];
    //     //         return $response;
    //     //     }
    //     //     $response = [
    //     //         'error' => false,
    //     //         'message' => 'Address Updated successfully!'
    //     //     ];
    //     //     return $response;
    //     // }
    //     if (isset($request->address_id)) {
    //         $address_id = $request->address_id;

    //         // Get the old address details
    //         $existingAddress = Address::find($address_id);
    //         if (!$existingAddress) {
    //             return [
    //                 'error' => true,
    //                 'message' => 'Address not found.'
    //             ];
    //         }

    //         // Retain old city and country if not selected properly (i.e., value is 'false')
    //         if ($request->city == 'false') {
    //             $address_data['city'] = $existingAddress->city;
    //             $address_data['city_id'] = $existingAddress->city_id;
    //         }

    //         if ($request->country == 'false') {
    //             $address_data['country'] = $existingAddress->country;
    //             $address_data['country_code'] = $existingAddress->country_code;
    //         }

    //         $res = updateDetails($address_data, ['id' => $address_id], 'addresses');
    //         if (!$res) {
    //             return [
    //                 'error' => true,
    //                 'message' => 'Failed to update address. Please try again.'
    //             ];
    //         }

    //         return [
    //             'error' => false,
    //             'message' => 'Address updated successfully!'
    //         ];
    //     } else {
    //         // dd($address_data);
    //         $address_id = Address::insertGetId($address_data);
    //         if (!$address_id) {
    //             $response = [
    //                 'error' => true,
    //                 'message' => 'Failed to add address. Please try again.'
    //             ];
    //             return $response;
    //         }
    //         $response = [
    //             'error' => false,
    //             'message' => 'Address added successfully!'
    //         ];
    //         return $response;
    //     }
    // }
    public function add_address(Request $request)
    {
        $user_id = Auth::user()->id ?? "";
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string',
                'type' => 'required',
                'mobile' => 'required|digits_between:1,16|numeric',
                'alternate_mobile' => 'nullable|digits_between:1,16|numeric',
                'address' => 'required',
                'landmark' => 'required',
                'city' => 'required',
                'pincode' => 'required',
                'state' => 'required',
                'country' => 'required',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
            ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors();
            $response['error'] = true;
            $response['message'] = $errors;
            return $response;
        }

        // Fetch city_id based on the selected city name
        $request['user_id'] = $user_id;
        $cityName = $request['city'];

        // Check if city is provided and valid, otherwise keep the old value
        if ($cityName === 'false' || empty($cityName)) {
            $city_id = $existingAddress->city_id ?? null;
            $city_name = $existingAddress->city ?? null;
        } else {
            // Decode the JSON and get the 'en' field
            $decodedCityName = json_decode($cityName, true);
            $city_name = $decodedCityName['en'] ?? null; // Get 'en' field from JSON

            // Fetch city_id based on the 'en' name
            $city = City::where('name->en', $city_name)->first();

            // dd($city);
            $city_id = $city ? $city->id : null;
        }

        // Fetch country_code based on the selected country name
        $countryName = $request['country'];

        // If country is not provided or is set to 'false', keep the old value
        if ($countryName === 'false' || empty($countryName)) {
            $country_code = $existingAddress->country_code ?? null;
            $country = $existingAddress->country ?? null;
        } else {
            $country = DB::table('countries')
                ->select('*')
                ->where('name', $countryName)
                ->first();
            $country_code = $country ? $country->phonecode : null;
        }

        // Add city_id and country_code to address data
        $request['city_id'] = $city_id;
        $request['city'] = $city_name;
        $request['country_code'] = $country_code;

        // Prepare address data
        $address_data = $request->only([
            'user_id',
            'name',
            'type',
            'mobile',
            'alternate_mobile',
            'address',
            'landmark',
            'city',
            'city_id',
            'pincode',
            'country',
            'state',
            'latitude',
            'longitude',
            'country_code'
        ]);
        // dd($address_data);
        // If an address_id is provided, update the existing address
        if (isset($request->address_id)) {
            $address_id = $request->address_id;

            // Get the old address details
            $existingAddress = Address::find($address_id);
            if (!$existingAddress) {
                return [
                    'error' => true,
                    'message' => 'Address not found.'
                ];
            }

            // Retain old city and country if not selected properly (i.e., value is 'false')
            if ($request->city == 'false' || empty($request->city)) {
                $address_data['city'] = $existingAddress->city;
                $address_data['city_id'] = $existingAddress->city_id;
            }

            if ($request->country == 'false' || empty($request->country)) {
                $address_data['country'] = $existingAddress->country;
                $address_data['country_code'] = $existingAddress->country_code;
            }

            $res = updateDetails($address_data, ['id' => $address_id], 'addresses');
            if (!$res) {
                return [
                    'error' => true,
                    'message' => 'Failed to update address. Please try again.'
                ];
            }

            return [
                'error' => false,
                'message' => 'Address updated successfully!'
            ];
        } else {
            // Insert new address if no address_id is provided
            $address_id = Address::insertGetId($address_data);
            if (!$address_id) {
                $response = [
                    'error' => true,
                    'message' => 'Failed to add address. Please try again.'
                ];
                return $response;
            }
            $response = [
                'error' => false,
                'message' => 'Address added successfully!'
            ];
            return $response;
        }
    }

    public function edit_address(Request $request)
    {
        $addressId = $request->input('address_id');
        $address_data = Address::find($addressId);
        return $address_data;
    }

    public function deleteAddress($address_id)
    {
        $user = Auth::user();

        $data = [
            'user_id' => $user->id,
            'id' => $address_id,
        ];
        deleteDetails($data, 'addresses');
    }

    public function setDefault($address_id)
    {
        $user = Auth::user();
        $address = Address::where('id', $address_id)->where('user_id', $user->id)->first();
        if ($address) {
            // Update the is_default status for all addresses of the user
            Address::where('user_id', $user->id)->update(['is_default' => 0]);
            updateDetails(['is_default' => '1'], ['id' => $address_id], 'addresses');
        }
    }
    public function refreshComponent()
    {
        $this->dispatch('$refresh');
    }
}
