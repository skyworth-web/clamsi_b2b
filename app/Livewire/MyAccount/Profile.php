<?php

namespace App\Livewire\MyAccount;

use App\Models\City;
use App\Models\User;
use Livewire\Component;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;

class Profile extends Component
{
    protected $listeners = ['refreshComponent'];

    public function mount()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
    }

    public function render()
    {

        $user = Auth::user();
        // if (!$user || !$user->id) {
        //     abort(404);
        // }

        $countryName = '';

        if (!empty($user->country_code)) {
            $country = DB::table('countries')
                ->select('*')
                ->where('phonecode', $user->country_code)
                ->first();

            if ($country) {
                $countryName = $country->name;
            }
        }

        $user['country_name'] = $countryName;
        $cities = City::get();
        $countries = DB::table('countries')->select('*')->get();
        return view('livewire.' . config('constants.theme') . '.my-account.profile', [
            'user_info' => $user,
            'cities' => $cities,
            'countries' => $countries
        ])->title("Profile |");
    }

    public function Update_profile(Request $request)
    {
        // Validate the request data
        $validator = Validator::make(
            $request->all(),
            [
                'username' => 'string',
                'city' => 'required',
                'country' => 'required',
                'address' => 'string',
                'zipcode' => 'nullable',
                'profile_upload' => 'nullable|image|mimes:jpeg,png,jpg'
            ]
        );
        if ($validator->fails()) {
            $errors = $validator->errors();
            $response['error'] = true;
            $response['message'] = $errors;
            return $response;
        }

        $user = auth()->user();
        $user_data = User::find($user->id);
        $store_image = $user_data['image'];
        if (isset($request['profile_upload']) && !empty($request['profile_upload'])) {
            $store_image = $request['profile_upload']->store('user_image', 'public');
            if (Storage::exists("user_image/" . $user_data['image'])) {
                Storage::delete("user_image/" . $user_data['image']);
            }
        }
        $countryName = $request['country'];
        $country = DB::table('countries')
            ->select('*')
            ->where('name', $countryName)
            ->first();

        $country_code = $country ? $country->phonecode : null;
        $request['country_code'] = $country_code;

        $user_data->update([
            'username' => $request->input('username'),
            'city' => $request->input('city'),
            'street' => $request->input('address'),
            'country_code' => $request->input('country_code'),
            'pincode' => $request->input('zipcode'),
            'image' => $store_image
        ]);
        if ($user_data) {
            $response = [
                'error' => false,
                'message' => 'Profile Updated successfully!'
            ];
            return $response;
        }
        $response = [
            'error' => true,
            'message' => 'Something Went Wrong Please Try Again Later!!'
        ];
        return $response;
    }

    public function update_password(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required',
            'verify_password' => 'required_with:new_password|same:new_password',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $response['error'] = true;
            $response['message'] = $errors;
            return $response;
        }
        $user = auth()->user();
        $user_data = User::find($user->id);

        $verify_currrent_password =  password_verify($request['current_password'], $user_data['password']);
        if (!$verify_currrent_password) {
            $response['error'] = true;
            $response['message'] = 'The current password is incorrect.';
            return $response;
        }

        if ($request['current_password'] == $request['verify_password']) {
            $response = [
                'error' => true,
                'message' => 'Current Password And New Password Can\'t be Some'
            ];
            return $response;
        }

        $password = bcrypt($request['verify_password']);
        $user_data->update([
            'password' => $password,
        ]);
        if ($user_data) {
            $response = [
                'error' => false,
                'message' => 'Password Updated successfully!'
            ];
            return $response;
        }
        $response = [
            'error' => true,
            'message' => 'Something Went Wrong Please Try Again Later!!'
        ];
        return $response;
    }

    public function get_Countries(Request $request)
    {
        $search_term = $request->search;

        $countries = DB::table('countries')
            ->select('name')
            ->where('name', 'like', '%' . $search_term . '%')
            ->orWhere('name', 'like', '%' . $search_term . '% collate utf8_general_ci')
            ->get();

        $data = array();
        foreach ($countries as $country) {
            $data[] = array("id" => $country->name, "text" => $country->name);
        }

        return $data;
    }

    public function get_Cities(Request $request)
    {
        $search_term = $request->search;
        $language_code = get_language_code();
        $cities = DB::table('cities')
            ->select('name', 'id')
            ->where('name', 'like', '%' . $search_term . '%')
            ->get();

        $data = array();
        foreach ($cities as $city) {
            $data[] = array("id" => $city->name, "text" => getDynamicTranslation('cities', 'name', $city->id, $language_code));
        }

        return $data;
    }

    public function refreshComponent()
    {
        $this->dispatch('$refresh');
    }
}
