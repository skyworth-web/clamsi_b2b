<?php

namespace App\Http\Controllers\Admin;

use App\Models\City;
use App\Models\User;
use App\Models\Deliveryboy;
use App\Models\Media;
use App\Models\StorageType;
use App\Models\Transaction;
use App\Models\Zipcode;
use App\Models\Zone;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class Delivery_boyController extends Controller
{
    public function index()
    {
        $userData = [];
        $zones_name = [];
        $cities_name = [];
        if (request()->has('edit_id')) {
            $editId = request('edit_id');
            $userData = User::select('id', 'username', 'mobile', 'email', 'address', 'bonus_type', 'bonus', 'serviceable_zones', 'front_licence_image', 'back_licence_image', 'disk')
                ->where('id', $editId)
                ->first();
            $language_code = get_language_code();
            if (isset($userData['serviceable_zones']) &&  $userData['serviceable_zones'] != NULL) {
                $zones = (isset($userData['serviceable_zones']) &&  $userData['serviceable_zones'] != NULL) ? explode(",", $userData['serviceable_zones']) : "";
                $zones_name = fetchDetails('zones', '', ['name', 'id', 'serviceable_city_ids', 'serviceable_zipcode_ids'], '', '', '', '', 'id', $zones);
                foreach ($zones_name as $zone) {
                    $zone->name = getDynamicTranslation('zones', 'name', $zone->id, $language_code);
                    $city_names = getCityNamesFromIds($zone->serviceable_city_ids, $language_code);
                    $zipcode_names = getZipcodesFromIds($zone->serviceable_zipcode_ids);
                    $zone->data = 'ID - ' . $zone->id . ' | Name - ' . $zone->name .
                        ' | Serviceable Cities: ' . implode(', ', $city_names) .
                        ' | Serviceable Zipcodes: ' . implode(', ', $zipcode_names);
                }
            }
        }
        if (request()->ajax()) {
            return response()->json(['userData' => $userData, 'zones_name' => $zones_name]);
        }
        return view('admin.pages.forms.delivery_boy', compact('userData', 'zones_name'));
    }

    public function store(Request $request, $fromApp = false)
    {
        $rules = [
            'name' => 'required',
            'mobile' => 'required|unique:users,mobile',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
            'address' => 'required',
            'front_licence_image' => 'required',
            'back_licence_image' => 'required',
            'serviceable_zones' => 'required|array',
        ];
        // dd($fromApp == false);
        if (!$fromApp) {
            $rules['bonus_type'] = 'required';
        }

        if ($request->bonus_type === 'fixed_amount_per_order_item') {
            $rules['bonus_amount'] = 'required';
        }

        if ($request->bonus_type === 'percentage_per_order_item') {
            $rules['bonus_percentage'] = 'required|numeric|between:1,100';
        }
        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }

        $storeImgPath = public_path(config('constants.DELIVERY_BOY_IMG_PATH'));

        if (!File::exists($storeImgPath)) {
            File::makeDirectory($storeImgPath, 0755, true);
        }
        $data['serviceable_zones'] = is_array($request->serviceable_zones) ? implode(',', $request->serviceable_zones) : $request->serviceable_zones;

        $media_storage_settings = fetchDetails('storage_types', ['is_default' => 1], '*');
        $mediaStorageType = isset($media_storage_settings) && !empty($media_storage_settings) ? $media_storage_settings[0]->id : 1;
        $disk = isset($media_storage_settings) && !empty($media_storage_settings) ? $media_storage_settings[0]->name : 'public';

        $media = StorageType::find($mediaStorageType);

        try {
            if ($request->hasFile('front_licence_image')) {

                $front_licence_image_file = $request->file('front_licence_image');

                $front_licence_image_file_path = $media->addMedia($front_licence_image_file)
                    ->sanitizingFileName(function ($fileName) use ($media) {
                        // Replace special characters and spaces with hyphens
                        $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                        // Generate a unique identifier based on timestamp and random component
                        $uniqueId = time() . '_' . mt_rand(1000, 9999);
                        $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                        $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                        return "{$baseName}-{$uniqueId}.{$extension}";
                    })
                    ->toMediaCollection('delivery_boys', $disk);

                $mediaIds[] = $front_licence_image_file_path->id;
            }
            if ($request->hasFile('back_licence_image')) {

                $back_licence_image_file = $request->file('back_licence_image');

                $back_licence_image_file_path = $media->addMedia($back_licence_image_file)
                    ->sanitizingFileName(function ($fileName) use ($media) {
                        // Replace special characters and spaces with hyphens
                        $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                        // Generate a unique identifier based on timestamp and random component
                        $uniqueId = time() . '_' . mt_rand(1000, 9999);
                        $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                        $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                        return "{$baseName}-{$uniqueId}.{$extension}";
                    })
                    ->toMediaCollection('delivery_boys', $disk);

                $mediaIds[] = $back_licence_image_file_path->id;
            }
            if ($request->hasFile('profile_image')) {

                $profile_image_file = $request->file('profile_image');

                $profile_image_file_path = $media->addMedia($profile_image_file)
                    ->sanitizingFileName(function ($fileName) use ($media) {
                        // Replace special characters and spaces with hyphens
                        $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                        // Generate a unique identifier based on timestamp and random component
                        $uniqueId = time() . '_' . mt_rand(1000, 9999);
                        $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                        $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                        return "{$baseName}-{$uniqueId}.{$extension}";
                    })
                    ->toMediaCollection('delivery_boys', $disk);

                $mediaIds[] = $profile_image_file_path->id;
            }
            if ($disk == 's3') {
                $media_list = $media->getMedia('delivery_boys');
                for ($i = 0; $i < count($mediaIds); $i++) {
                    $media_url = $media_list[($media_list->count()) - (count($mediaIds) - $i)]->getUrl();

                    switch ($i) {
                        case 0:
                            $front_licence_image_file_path_url = $media_url;
                            break;
                        case 1:
                            $back_licence_image_file_path_url = $media_url;
                            break;
                        case 2:
                            $profile_image_file_path_url = $media_url;
                            break;
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
        $data['front_licence_image'] = $disk == 's3' ?  (isset($front_licence_image_file_path_url) ? $front_licence_image_file_path_url : '') : (isset($front_licence_image_file_path->file_name) ? '/' . $front_licence_image_file_path->file_name : '');
        $data['back_licence_image'] = $disk == 's3' ?  (isset($back_licence_image_file_path_url) ? $back_licence_image_file_path_url : '') : (isset($back_licence_image_file_path->file_name) ? '/' . $back_licence_image_file_path->file_name : '');
        $data['image'] = $disk == 's3' ?  (isset($profile_image_file_path_url) ? $profile_image_file_path_url : '') : (isset($profile_image_file_path->file_name) ? '/' . $profile_image_file_path->file_name : '');

        $data['username'] = $request->name;
        $data['mobile'] = $request->mobile;
        $data['email'] = $request->email;
        $data['password'] = bcrypt($request->password);
        $data['address'] = $request->address;
        $data['bonus_type'] = $request->bonus_type;
        $data['bonus'] = ($request->bonus_amount != '' && $request->bonus_amount !== 'null') ? $request->bonus_amount : $request->bonus_percentage;
        $data['role_id'] = 3;
        $data['active'] = 1;
        if (!$fromApp) {
            $data['status'] = 1;
        } else {
            $data['status'] = 0;
        }
        $data['disk'] = $disk;
        // dd($data);
        // Create the Deliveryboy record with the updated data
        $delivery_boy = Deliveryboy::create($data);
        $fcm_ids = fetchdetails('user_fcm', ['user_id' => $delivery_boy->id], 'fcm_id');

        $fcm_ids_array = array_map(function ($item) {
            return $item->fcm_id;
        }, $fcm_ids);
        $delivery_boy['fcm_id'] = $fcm_ids_array;
        if (isset($request->fcm_id) && $request->fcm_id != '') {

            $fcm_data = [
                'fcm_id' => $request->fcm_id,
                'user_id' => $delivery_boy->id,
            ];

            $existing_fcm = DB::table('user_fcm')
                ->where('user_id', $delivery_boy->id)
                ->where('fcm_id', $request->fcm_id)
                ->first();
            if (!$existing_fcm) {
                DB::table('user_fcm')->insert($fcm_data);
            }
        }
        if ($request->ajax()) {
            return response()->json([
                'message' => labels('admin_labels.delivery_boy_created_successfully', 'Delivery Boy created successfully'),
                'error' => false
            ]);
        } else {
            return response()->json($delivery_boy);
        }
    }

    public function list(Request $request)
    {
        $search = trim($request->input('search'));
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'DESC');
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = $request->input('limit', 50);

        $delivery_boys_data = User::when($search, function ($query) use ($search) {
            return $query->where('username', 'like', '%' . $search . '%');
        })->where('role_id', 3);

        $total = $delivery_boys_data->count();
        $delivery_boys = $delivery_boys_data
            ->orderBy($sort, $order)
            ->offset($offset)
            ->limit($limit)
            ->get();
        $language_code = get_language_code();
        $delivery_boys = $delivery_boys->map(function ($b) use ($language_code) {

            $isPublicDisk = $b->disk == 'public' ? 1 : 0;
            $front_licence_image = route('admin.dynamic_image', [
                'url' => getMediaImageUrl($b->front_licence_image, 'DELIVERY_BOY_IMG_PATH'),
                'width' => 60,
                'quality' => 90
            ]);
            $back_licence_image = route('admin.dynamic_image', [
                'url' => getMediaImageUrl($b->back_licence_image, 'DELIVERY_BOY_IMG_PATH'),
                'width' => 60,
                'quality' => 90
            ]);
            $delete_url = route('delivery_boys.destroy', $b->id);
            $action = '<div class="dropdown bootstrap-table-dropdown">
                <a href="#" class="text-dark" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="bx bx-dots-horizontal-rounded"></i>
                </a>
                <div class="dropdown-menu table_dropdown order_action_dropdown" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item dropdown_menu_items edit_delivery_boy" data-id="' . $b->id  . '" data-bs-toggle="modal" data-bs-target="#edit_delivery_boy"><i class="bx bx-pencil mx-2"></i> Edit</a>
                    <a class="dropdown-item delete-data dropdown_menu_items" data-url="' . $delete_url . '"><i class="bx bx-trash mx-2"></i>Delete</a>
                    <a class="dropdown-item fund_transfer dropdown_menu_items"  data-bs-target="#fund_transfer_delivery_boy" data-bs-toggle="modal" data-id="' . $b->id . '" ><i class="bx bx-right-arrow-alt mx-2"></i>Fund Transfer</a>
                </div>
            </div>';
            if ($b->status == 0) {
                $b->status = '<select class="form-select status_dropdown change_toggle_status ' . ($b->status == 1 ? 'active_status' : 'inactive_status') . '" data-id="' . $b->id . '" data-url="/admin/delivery_boy/update_status/' . $b->id . '" aria-label="">
                      <option value="1" ' . ($b->status == 1 ? 'selected' : '') . '>Active</option>
                      <option value="0" ' . ($b->status == 0 ? 'selected' : '') . '>Deactive</option>
                  </select>';
            } else {
                $b->status = '<span class="badge bg-primary">Active</span>';
            }
            $bonusBadge = ($b->bonus_type == 'percentage_per_order_item') ? '<span class="badge bg-primary">Percentage Per Order Item</span>' : '<span class="badge bg-info">Fix Amount Per Order Item</span>';
            $b->action = $action;
            $b->bonus_type = $bonusBadge;
            $b->serviceable_zones = $this->get_zones_by_ids($b->serviceable_zones, $language_code);
            $b->front_licence_image = '<div><a href="' . getMediaImageUrl($b->front_licence_image, 'DELIVERY_BOY_IMG_PATH')  . '" data-lightbox="image-' . $b->pid . '"><img src="' . $front_licence_image . '" alt="Avatar" class="rounded"/></a></div>';
            $b->back_licence_image = '<div><a href="' .  getMediaImageUrl($b->back_licence_image, 'DELIVERY_BOY_IMG_PATH')  . '" data-lightbox="image-' . $b->pid . '"><img src="' . $back_licence_image . '" alt="Avatar" class="rounded"/></a></div>';

            return $b;
        });

        return response()->json([
            "rows" => $delivery_boys,
            "total" => $total,
        ]);
    }

    public function get_zones_by_ids($zone_ids, $language_code = '')
    {
        $ids_array = explode(',', $zone_ids);
        $zones = Zone::whereIn('id', $ids_array)->get();

        $translated_names = [];

        foreach ($zones as $zone) {
            $translated_name = getDynamicTranslation('zones', 'name', $zone->id, $language_code);
            $translated_names[] = $translated_name;
        }

        $comma_separated_zones = implode(',', $translated_names);

        return $comma_separated_zones;
    }


    public function update_status($id)
    {
        $delivery_boy = Deliveryboy::findOrFail($id);
        $delivery_boy->status = $delivery_boy->status == '1' ? '0' : '1';
        $delivery_boy->save();
        return response()->json(['success' => labels('admin_labels.status_updated_successfully', 'Status updated successfully.')]);
    }

    public function edit($data)
    {

        $data = User::find($data);
        return view('admin.pages.forms.update_category', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $delivery_boy_data = User::find($id);

        if (!$delivery_boy_data) {
            return response()->json(['error' => true, 'message' => labels('admin_labels.data_not_found', 'Data Not Found')], 404);
        } else {
            if (isset($request->old)) {
                if (!Hash::check($request->input('old'), $delivery_boy_data->password)) {
                    // If the old password does not match
                    return response()->json([
                        'error' => true,
                        'message' => 'Old password is incorrect',
                        'language_message_key' => 'old_password_incorrect'
                    ], 400);
                }
            }
            $validator = Validator::make($request->all(), [
                'mobile' => 'numeric',
            ]);

            if (isset($request->password)) {
                $validator = Validator::make($request->all(), [
                    'password' => 'required',
                    'confirm_password' => 'required|same:password',
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
            if ($request->bonus_type == 'percentage_per_order_item') {
                $validator = Validator::make($request->all(), [
                    'bonus_percentage' => 'required',
                ]);
            }
            if ($request->bonus_type == 'percentage_per_order_item' && ($request->bonus_percentage < 1 || $request->bonus_percentage > 100)) {
                $response = [
                    'error_message' =>
                    labels('admin_labels.you_can_set_percentage_between_one_to_hundred', 'You Can Set Percentage Between 1 to 100.')
                ];
                return response()->json($response);
            }

            if ($validator->fails()) {
                $errors = $validator->errors();

                if ($request->ajax()) {
                    return response()->json(['errors' => $errors->all()], 422);
                } else {
                    $response = [
                        'error' => true,
                        'message' => $validator->errors()->first(),
                        'code' => 102,
                    ];
                    return response()->json($response);
                }
            }


            $user_data['username'] = $request->name ?? $delivery_boy_data->username;
            $user_data['is_notification_on'] = $request->is_notification_on ?? $delivery_boy_data->is_notification_on;
            $user_data['mobile'] = $request->mobile ?? $delivery_boy_data->mobile;
            $user_data['email'] = $request->email ?? $delivery_boy_data->email;
            if (isset($request->new) && !empty($request->new)) {
                $user_data['password'] = bcrypt($request->new);
            }
            $user_data['address'] = $request->address ?? $delivery_boy_data->address;
            $user_data['bonus_type'] = $request->bonus_type ?? $delivery_boy_data->bonus_type;

            $user_data['bonus'] = ($request->bonus_amount != '' && $request->bonus_amount !== 'null') ? $request->bonus_amount : $request->bonus_percentage;
            $user_data['role_id'] = 3;
            $user_data['active'] = 1;
            if (isset($request->serviceable_zones) && !empty($request->serviceable_zones)) {
                $user_data['serviceable_zones'] = implode(',', $request->serviceable_zones);
                $user_data['serviceable_zones'] = is_array($request->serviceable_zones) ? implode(',', $request->serviceable_zones) : $request->serviceable_zones;
            }
            $disk = $delivery_boy_data->disk;

            $media_storage_settings = fetchDetails('storage_types', ['is_default' => 1], '*');
            $current_disk = isset($media_storage_settings) && !empty($media_storage_settings) ? $media_storage_settings[0]->name : 'public';

            try {
                if ($request->hasFile('front_licence_image')) {
                    // Specify the path and disk from which you want to delete the file
                    if ($disk == 's3') {
                        $path = $delivery_boy_data->front_licence_image;
                    } else {
                        $path = 'delivery_boys/' . $delivery_boy_data->front_licence_image; // Example path to the file you want to delete
                    }

                    // Call the removeFile method to delete the file
                    removeMediaFile($path, $disk);

                    $front_licence_image_file = $request->file('front_licence_image');

                    $front_licence_image_file_path = $delivery_boy_data->addMedia($front_licence_image_file)
                        ->sanitizingFileName(function ($fileName) use ($delivery_boy_data) {
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

                    if ($disk == 's3') {
                        $path = $delivery_boy_data->back_licence_image;
                    } else {
                        $path = 'delivery_boys/' . $delivery_boy_data->back_licence_image; // Example path to the file you want to delete
                    }


                    // Call the removeFile method to delete the file
                    removeMediaFile($path, $disk);

                    $back_licence_image_file = $request->file('back_licence_image');

                    $back_licence_image_file_path = $delivery_boy_data->addMedia($back_licence_image_file)
                        ->sanitizingFileName(function ($fileName) use ($delivery_boy_data) {
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
                if ($request->hasFile('profile_image')) {

                    if ($disk == 's3') {
                        $path = $delivery_boy_data->profile_image;
                    } else {
                        $path = 'delivery_boys/' . $delivery_boy_data->profile_image; // Example path to the file you want to delete
                    }


                    // Call the removeFile method to delete the file
                    removeMediaFile($path, $disk);

                    $profile_image_file = $request->file('profile_image');

                    $profile_image_file_path = $delivery_boy_data->addMedia($profile_image_file)
                        ->sanitizingFileName(function ($fileName) use ($delivery_boy_data) {
                            // Replace special characters and spaces with hyphens
                            $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                            // Generate a unique identifier based on timestamp and random component
                            $uniqueId = time() . '_' . mt_rand(1000, 9999);
                            $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                            $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                            return "{$baseName}-{$uniqueId}.{$extension}";
                        })
                        ->toMediaCollection('delivery_boys', $current_disk);

                    $mediaIds[] = $profile_image_file_path->id;
                }
                //code for storing s3 object url for media

                if ($current_disk == 's3') {
                    $media_list = $delivery_boy_data->getMedia('delivery_boys');
                    for ($i = 0; $i < count($mediaIds); $i++) {
                        $media_url = $media_list[($media_list->count()) - (count($mediaIds) - $i)]->getUrl();

                        $fileName = implode('/', array_slice(explode('/', $media_url), -1));

                        if (isset($front_licence_image_file_path->file_name) && $fileName == $front_licence_image_file_path->file_name) {
                            $front_licence_image_file_path_url = $media_url;
                        }
                        if (isset($back_licence_image_file_path->file_name) && $fileName == $back_licence_image_file_path->file_name) {
                            $back_licence_image_file_path_url = $media_url;
                        }
                        if (isset($profile_image_file_path->file_name) && $fileName == $profile_image_file_path->file_name) {
                            $profile_image_file_path_url = $media_url;
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
                $user_data['front_licence_image'] = $delivery_boy_data->front_licence_image;
            }

            if (isset($back_licence_image_file_path->file_name)) {
                $user_data['back_licence_image'] = $current_disk == 's3' ? (isset($back_licence_image_file_path_url) ? $back_licence_image_file_path_url : '') : (isset($back_licence_image_file_path->file_name) ? '/' . $back_licence_image_file_path->file_name : '');
            } else {
                $user_data['back_licence_image'] = $delivery_boy_data->back_licence_image;
            }
            if (isset($profile_image_file_path->file_name)) {
                $user_data['image'] = $current_disk == 's3' ? (isset($profile_image_file_path_url) ? $profile_image_file_path_url : '') : (isset($profile_image_file_path->file_name) ? '/' . $profile_image_file_path->file_name : '');
            } else {
                $user_data['image'] = $delivery_boy_data->image;
            }

            $user_data['disk'] = isset($back_licence_image_file_path->disk) && !empty($back_licence_image_file_path->disk) ? $back_licence_image_file_path->disk : $disk;

            $delivery_boy_data->update($user_data);

            if ($request->ajax()) {
                return response()->json([
                    'message' => labels('admin_labels.delivery_boy_updated_successfully', 'Delivery Boy updated successfully'),
                    'language_message_key' => 'delivery_boy_updated_successfully',
                ]);
            } else {
                return response()->json([
                    'error' => false,
                    'message' =>
                    labels('admin_labels.delivery_boy_updated_successfully', 'Delivery Boy updated successfully'),
                    'language_message_key' => 'delivery_boy_updated_successfully',
                    'data' => $delivery_boy_data,
                    'location' => route('delivery_boys.index')
                ]);
            }
        }
    }

    public function destroy($id)
    {
        $delivery_boy = Deliveryboy::find($id);

        if ($delivery_boy->delete()) {

            $disk = $delivery_boy->disk;

            // delete front licence image from media
            $path = 'delivery_boys/' . $delivery_boy->front_licence_image; // Example path to the file you want to delete
            removeMediaFile($path, $disk);

            // delete back licence image from media
            $path = 'delivery_boys/' . $delivery_boy->back_licence_image; // Example path to the file you want to delete
            removeMediaFile($path, $disk);

            return response()->json([
                'error' => false,
                'message' => labels('admin_labels.delivery_boy_deleted_successfully', 'Delivery Boy deleted successfully!')
            ]);
        } else {
            return response()->json(['error' => labels('admin_labels.something_went_wrong', 'Something went wrong')]);
        }
    }

    public function delete_selected_data(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id'
        ]);

        foreach ($request->ids as $id) {
            $users = User::find($id);
            $disk = $users->disk;

            // delete front licence image from media
            $path = 'delivery_boys/' . $users->front_licence_image; // Example path to the file you want to delete
            removeMediaFile($path, $disk);

            // delete back licence image from media
            $path = 'delivery_boys/' . $users->back_licence_image; // Example path to the file you want to delete
            removeMediaFile($path, $disk);
            if ($users) {
                User::where('id', $id)->delete();
            }
        }
        User::destroy($request->ids);

        return response()->json(['message' => 'Selected data deleted successfully.']);
    }

    public function manage_cash_collection(Request $request)
    {
        $rules = [
            'delivery_boy_id' => 'required|numeric',
            'amount' => 'required|numeric|gt:0',
            'date' => 'required',
            'message' => 'nullable|string',
        ];

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        } else {
            $deliveryBoyId = request('delivery_boy_id');
            $user = User::find($deliveryBoyId);

            if (!$user) {
                return response()->json([
                    'error' => true,
                    'error_message' =>
                    labels('admin_labels.delivery_boy_not_exist_in_database', 'Delivery Boy is not exist in your database'),
                ]);
            }

            $amount = request('amount');
            $date = request('date');
            $message = request(
                'message',
                labels('admin_labels.delivery_boy_cash_collection_by_admin', 'Delivery boy cash collection by admin')
            );

            if ($user->cash_received < $amount) {
                return response()->json([
                    'error' => true,
                    'error_message' =>
                    labels('admin_labels.amount_must_not_be_greater_than_cash', 'Amount must not be greater than cash'),
                ]);
            }

            if ($user->cash_received > 0) {
                $user->cash_received -= $amount;
                $user->save();

                $transaction = new Transaction();
                $transaction->transaction_type = "transaction";
                $transaction->user_id = $deliveryBoyId;
                $transaction->order_id = "";
                $transaction->type = "delivery_boy_cash_collection";
                $transaction->amount = $amount;
                $transaction->status = 1;
                $transaction->message = $message;
                $transaction->transaction_date = $date;
                $transaction->save();

                return response()->json([
                    'error' => false,
                    'message' =>
                    labels('admin_labels.amount_successfully_collected', 'Amount Successfully Collected'),
                ]);
            } else {
                return response()->json([
                    'error' => true,
                    'error_message' =>
                    labels('admin_labels.cash_should_be_greater_than_zero', 'Cash should be greater than 0'),
                ]);
            }
        }
    }
}
