<?php

namespace App\Http\Controllers\Delivery_boy;

use App\Http\Controllers\Admin\SellerController;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Media;
use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Zipcode;
use App\Models\Zone;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserController extends Controller
{
    public function edit(User $user)
    {
        return view('delivery_boy.pages.forms.account', ['user' => $user]);
    }

    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'mobile' => 'required',

        ]);
        if (!empty($request->input('old_password')) || !empty($request->input('new_password'))) {
            $validator = Validator::make($request->all(), [
                'old_password' => 'required',
                'new_password' => ['required', 'same:new_password'],
            ]);
        }
        if (isset($request->front_licence_image)) {
            $validator = Validator::make($request->all(), [
                'front_licence_image' => 'required',
            ]);
        }

        if (isset($request->back_licence_image)) {
            $validator = Validator::make($request->all(), [
                'back_licence_image' => 'required',
            ]);
        }
        if ($validator->fails()) {
            if ($request->ajax()) {
                throw ValidationException::withMessages($validator->errors()->all());
            }

            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = User::find($id);

        // Check if the old password matches the one in the database
        if (!empty($request->input('old_password'))) {

            if (!Hash::check($request->old_password, $user->password)) {
                if ($request->ajax()) {
                    return response()->json(['message' => labels('admin_labels.incorrect_old_password', 'The old password is incorrect.')], 422);
                }
                return redirect()->back()->withErrors(['old_password' => labels('admin_labels.incorrect_old_password', 'The old password is incorrect.')])->withInput();
            }
        }

        $user_data['username'] = $request->name ?? $user->username;
        $user_data['mobile'] = $request->mobile ?? $user->mobile;
        $user_data['email'] = $request->email ?? $user->email;
        if (isset($request->new_password) && !empty($request->new_password)) {
            $user_data['password'] = bcrypt($request->new_password);
        }
        $user_data['address'] = $request->address ?? $user->address;

        $user_data['role_id'] = 3;
        $user_data['active'] = 1;
        if (isset($request->serviceable_zones) && !empty($request->serviceable_zones)) {
            $user_data['serviceable_zones'] = implode(',', $request->serviceable_zones);
        }

        $disk = $user->disk;

        $media_storage_settings = fetchDetails('storage_types', ['is_default' => 1], '*');
        $current_disk = isset($media_storage_settings) && !empty($media_storage_settings) ? $media_storage_settings[0]->name : 'public';

        try {
            if ($request->hasFile('front_licence_image')) {

                // Specify the path and disk from which you want to delete the file
                if ($disk == 's3') {
                    $path = $user->front_licence_image;
                } else {
                    $path = 'delivery_boys/' . $user->front_licence_image; // Example path to the file you want to delete
                }

                // Call the removeFile method to delete the file
                removeMediaFile($path, $disk);


                $front_licence_image_file = $request->file('front_licence_image');

                $front_licence_image_file_path = $user->addMedia($front_licence_image_file)
                    ->sanitizingFileName(function ($fileName) use ($user) {
                        // Replace special characters and spaces with hyphens
                        $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                        // Generate a unique identifier based on timestamp and random component
                        $uniqueId = time() . '_' . mt_rand(1000, 9999);
                        $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                        $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                        return "{$baseName}-{$uniqueId}.{$extension}";
                    })
                    ->toMediaCollection('delivery_boys', $current_disk);

                $mediaIds[] = $front_licence_image_file_path->id;
            }
            if ($request->hasFile('back_licence_image')) {

                // Specify the path and disk from which you want to delete the file
                if ($disk == 's3') {
                    $path = $user->back_licence_image;
                } else {
                    $path = 'delivery_boys/' . $user->back_licence_image; // Example path to the file you want to delete
                }

                // Call the removeFile method to delete the file
                removeMediaFile($path, $disk);

                $back_licence_image_file = $request->file('back_licence_image');

                $back_licence_image_file_path = $user->addMedia($back_licence_image_file)
                    ->sanitizingFileName(function ($fileName) use ($user) {
                        // Replace special characters and spaces with hyphens
                        $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                        // Generate a unique identifier based on timestamp and random component
                        $uniqueId = time() . '_' . mt_rand(1000, 9999);
                        $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                        $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                        return "{$baseName}-{$uniqueId}.{$extension}";
                    })
                    ->toMediaCollection('delivery_boys', $current_disk);


                $mediaIds[] = $back_licence_image_file_path->id;
            }

            //code for storing s3 object url for media

            if ($current_disk == 's3') {
                $media_list = $user->getMedia('delivery_boys');
                for ($i = 0; $i < count($mediaIds); $i++) {
                    $media_url = $media_list[($media_list->count()) - (count($mediaIds) - $i)]->getUrl();

                    $fileName = implode('/', array_slice(explode('/', $media_url), -1));

                    if (isset($front_licence_image_file_path->file_name) && $fileName == $front_licence_image_file_path->file_name) {
                        $front_licence_image_file_path_url = $media_url;
                    }
                    if (isset($back_licence_image_file_path->file_name) && $fileName == $back_licence_image_file_path->file_name) {
                        $back_licence_image_file_path_url = $media_url;
                    }

                    Media::destroy($mediaIds[$i]);
                }
            }
        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ]);
        }

        if (isset($front_licence_image_file_path->file_name)) {
            $user_data['front_licence_image'] = $current_disk == 's3' ? (isset($front_licence_image_file_path_url) ? $front_licence_image_file_path_url : '') : (isset($front_licence_image_file_path->file_name) ? '/' . $front_licence_image_file_path->file_name : '');
        } else {
            $user_data['front_licence_image'] = $user->front_licence_image;
        }

        if (isset($back_licence_image_file_path->file_name)) {
            $user_data['back_licence_image'] = $current_disk == 's3' ? (isset($back_licence_image_file_path_url) ? $back_licence_image_file_path_url : '') : (isset($back_licence_image_file_path->file_name) ? '/' . $back_licence_image_file_path->file_name : '');
        } else {
            $user_data['back_licence_image'] = $user->back_licence_image;
        }


        $user->update($user_data);

        if ($request->ajax()) {
            return response()->json(['message' => labels('admin_labels.profile_details_updated_successfully', 'Profile details updated successfully!')]);
        }
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

        $data = array();
        foreach ($cities as $city) {
            $data[] = array("id" => $city->id, "text" => $city->name);
        }
        return response()->json($data);
    }
    public function walletTransaction()
    {
        $user_id = Auth::user()->id;
        $wallet_balance = Auth::user()->balance;
        return view('delivery_boy.pages.tables.manage_customer_wallet', ['user_id' => $user_id, 'wallet_balance' => $wallet_balance]);
    }

    public function getTransactionList()
    {
        $user_id = Auth::user()->id;
        $offset = request()->input('offset', 0);
        $limit = request()->input('limit', 10);
        $sort = request()->input('sort', 'id');
        $order = request()->input('order', 'ASC');

        $transactionsQuery = DB::table('transactions')->where('transactions.user_id', $user_id)->whereIn('transactions.type', ['credit', 'debit']);

        if (request()->has('search') && trim(request()->input('search')) !== '') {
            $search = trim(request()->input('search'));
            $transactionsQuery->where(function ($query) use ($search) {
                $query->where('transactions.id', 'LIKE', '%' . $search . '%')
                    ->orWhere('transactions.amount', 'LIKE', '%' . $search . '%')
                    ->orWhere('transactions.created_at', 'LIKE', '%' . $search . '%')
                    ->orWhere('transactions.type', 'LIKE', '%' . $search . '%')
                    ->orWhere('transactions.status', 'LIKE', '%' . $search . '%')
                    ->orWhere('transactions.txn_id', 'LIKE', '%' . $search . '%');
            });
        }

        $totalQuery = clone $transactionsQuery;

        $total = $totalQuery->count();

        $txn_search_res = $transactionsQuery->select('transactions.*')
            ->orderBy($sort, $order)
            ->skip($offset)
            ->take($limit)
            ->get();

        $formattedTransactions = $txn_search_res->map(function ($row) {
            return [
                'id' => $row->id,
                'type' => $row->type == 'bank_transfer' ? 'Bank Transfer' : $row->type,
                'payu_txn_id' => $row->payu_txn_id,
                'amount' => $row->amount,
                'status' => $row->status,
                'message' => $row->message,
                'created_at' => Carbon::parse($row->created_at)->format('d-m-Y'),
            ];
        });

        return response()->json(['total' => $total, 'rows' => $formattedTransactions]);
    }
    public function zone_data(Request $request)
    {
        $search = trim($request->input('term'));
        $limit = (int) $request->input('limit', 50);

        $query = Zone::where('status', 1)
            ->where(function ($query) use ($search) {
                $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%'])
                    ->orWhereRaw('CAST(id AS CHAR) LIKE ?', ['%' . $search . '%']);
            });


        $zones = $query->limit($limit)->get(['id', 'name', 'serviceable_city_ids', 'serviceable_zipcode_ids']);
        $total = $query->count();

        $cities = [];
        $zipcodes = [];

        foreach ($zones as $zone) {
            $city_ids = explode(',', $zone->serviceable_city_ids);
            $zipcode_ids = explode(',', $zone->serviceable_zipcode_ids);

            $cities = array_unique(array_merge($cities, $city_ids));
            $zipcodes = array_unique(array_merge($zipcodes, $zipcode_ids));
        }

        $city_names = City::whereIn('id', $cities)->pluck('name', 'id')->toArray();

        $zipcode_names = Zipcode::whereIn('id', $zipcodes)->pluck('zipcode', 'id')->toArray();
        $language_code = get_language_code();
        $response = [
            'total' => $total,
            'results' => $zones->map(function ($zone) use ($city_names, $zipcode_names, $language_code) {
                $city_ids = explode(',', $zone->serviceable_city_ids);
                $zipcode_ids = explode(',', $zone->serviceable_zipcode_ids);

                return [
                    'id' => $zone->id,
                    'text' => getDynamicTranslation('zones', 'name', $zone->id, $language_code), // Translate zone name
                    'serviceable_cities' => implode(', ', array_map(function ($city_id) use ($city_names, $language_code) {
                        return getDynamicTranslation('cities', 'name', $city_id, $language_code) ?? ($city_names[$city_id] ?? null);
                    }, $city_ids)), // Translate city names
                    'serviceable_zipcodes' => implode(', ', array_map(function ($zipcode_id) use ($zipcode_names) {
                        return $zipcode_names[$zipcode_id] ?? null;
                    }, $zipcode_ids)), // Zipcode remains unchanged
                ];
            }),
        ];

        return response()->json($response);
    }
}