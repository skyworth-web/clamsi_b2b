<?php

namespace App\Http\Controllers\Admin;

use App\Models\Role;
use App\Models\User;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Category;
use App\Models\City;
use App\Models\ComboProduct;
use Illuminate\Http\Request;
use App\Models\SellerCommission;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Media;
use App\Models\StorageType;
use App\Models\Store;
use App\Models\UserFcm;
use App\Models\Zipcode;
use Illuminate\Support\Facades\File;
use Exception;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class SellerController extends Controller
{
    public function index()
    {
        $users = User::whereHas('role', function ($query) {
            $query->where('id', 2);
        })->get();
        return view('admin.pages.tables.manage_sellers', ['users' => $users]);
    }

    public function update_status($id)
    {
        $user = User::findOrFail($id);
        $user->active = $user->active == '1' ? '0' : '1';
        $user->save();
        return response()->json(['success' => labels('admin_labels.status_updated_successfully', 'Status updated successfully.')]);
    }

    public function create()
    {
        $store_id = getStoreId();
        $categories = $this->getCategories();

        $categories = Category::where('status', 1)->where('store_id', $store_id)->orderBy('id', 'desc')->get();

        $note_for_necessary_documents = fetchDetails('stores', ['id' => $store_id], 'note_for_necessary_documents');
        $note_for_necessary_documents = isset($note_for_necessary_documents) && $note_for_necessary_documents != null ? $note_for_necessary_documents[0]->note_for_necessary_documents : "Other Documents";
        // dD($note_for_necessary_documents);

        $stores = Store::where('status', 1)->get();
        return view('admin.pages.forms.add_sellers', compact('categories', 'stores', 'note_for_necessary_documents'));
    }

    public function store(Request $request, $fromApp = false)
    {
        $rules = [
            'name' => 'required',
            'mobile' => 'required',
            'email' => 'required',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
            'address' => 'required',
            'store_name' => 'required',
            'account_number' => 'required',
            'account_name' => 'required',
            'bank_name' => 'required',
            'bank_code' => 'required',
            'city' => 'required',
            'zipcode' => 'required',
            'description' => 'required',
            'deliverable_type' => 'required',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180'
        ];

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }

        $storeImgPath = public_path(config('constants.SELLER_IMG_PATH'));

        if (!File::exists($storeImgPath)) {
            File::makeDirectory($storeImgPath, 0755, true);
        }

        $seller_data = [];
        $seller_store_data = [];
        $store_id = isset($request->store_id) && !empty($request->store_id) ? $request->store_id : getStoreId();
        $user = User::where('mobile', $request->mobile)->where('role_id', 4)->first();

        $media_storage_settings = fetchDetails('storage_types', ['is_default' => 1], '*');
        $mediaStorageType = isset($media_storage_settings) && !empty($media_storage_settings) ? $media_storage_settings[0]->id : 1;
        $disk = isset($media_storage_settings) && !empty($media_storage_settings) ? $media_storage_settings[0]->name : 'public';

        $media = StorageType::find($mediaStorageType);
        try {
            if ($request->hasFile('other_documents')) {
                foreach ($request->file('other_documents') as $file) {
                    $other_documents = $media->addMedia($file)
                        ->sanitizingFileName(function ($fileName) use ($media) {
                            $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                            $uniqueId = time() . '_' . mt_rand(1000, 9999);
                            $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                            $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                            return "{$baseName}-{$uniqueId}.{$extension}";
                        })
                        ->toMediaCollection('sellers', $disk);
                    $other_document_file_names[] = $other_documents->file_name;
                    $mediaIds[] = $other_documents->id;
                }
            }
            if ($request->hasFile('profile_image')) {

                $profile_image = $request->file('profile_image');
                $profile_image = $media->addMedia($profile_image)
                    ->sanitizingFileName(function ($fileName) use ($media) {
                        // Replace special characters and spaces with hyphens
                        $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                        // Generate a unique identifier based on timestamp and random component
                        $uniqueId = time() . '_' . mt_rand(1000, 9999);
                        $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                        $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                        return "{$baseName}-{$uniqueId}.{$extension}";
                    })
                    ->toMediaCollection('sellers', $disk);
                $mediaIds[] = $profile_image->id;
            }
            if ($request->hasFile('address_proof')) {

                $addressProofFile = $request->file('address_proof');

                $address_proof = $media->addMedia($addressProofFile)
                    ->sanitizingFileName(function ($fileName) use ($media) {
                        // Replace special characters and spaces with hyphens
                        $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                        // Generate a unique identifier based on timestamp and random component
                        $uniqueId = time() . '_' . mt_rand(1000, 9999);
                        $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                        $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                        return "{$baseName}-{$uniqueId}.{$extension}";
                    })
                    ->toMediaCollection('sellers', $disk);

                $mediaIds[] = $address_proof->id;
            }
            if ($request->hasFile('store_logo')) {

                $storeLogoFile = $request->file('store_logo');

                $store_logo = $media->addMedia($storeLogoFile)
                    ->sanitizingFileName(function ($fileName) use ($media) {
                        // Replace special characters and spaces with hyphens
                        $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                        // Generate a unique identifier based on timestamp and random component
                        $uniqueId = time() . '_' . mt_rand(1000, 9999);
                        $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                        $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                        return "{$baseName}-{$uniqueId}.{$extension}";
                    })
                    ->toMediaCollection('sellers', $disk);

                $mediaIds[] = $store_logo->id;
            }

            if ($request->hasFile('store_thumbnail')) {

                $storeThumbnailFile = $request->file('store_thumbnail');

                $store_thumbnail = $media->addMedia($storeThumbnailFile)
                    ->sanitizingFileName(function ($fileName) use ($media) {
                        // Replace special characters and spaces with hyphens
                        $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                        // Generate a unique identifier based on timestamp and random component
                        $uniqueId = time() . '_' . mt_rand(1000, 9999);
                        $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                        $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                        return "{$baseName}-{$uniqueId}.{$extension}";
                    })
                    ->toMediaCollection('sellers', $disk);

                $mediaIds[] = $store_thumbnail->id;
            }


            if ($request->hasFile('authorized_signature')) {

                $authorizedSignatureFile = $request->file('authorized_signature');

                $authorized_signature = $media->addMedia($authorizedSignatureFile)
                    ->sanitizingFileName(function ($fileName) use ($media) {
                        // Replace special characters and spaces with hyphens
                        $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                        // Generate a unique identifier based on timestamp and random component
                        $uniqueId = time() . '_' . mt_rand(1000, 9999);
                        $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                        $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                        return "{$baseName}-{$uniqueId}.{$extension}";
                    })
                    ->toMediaCollection('sellers', $disk);

                $mediaIds[] = $authorized_signature->id;
            }

            if ($request->hasFile('national_identity_card')) {

                $nationalIdentityCardFile = $request->file('national_identity_card');

                $national_identity_card = $media->addMedia($nationalIdentityCardFile)
                    ->sanitizingFileName(function ($fileName) use ($media) {
                        // Replace special characters and spaces with hyphens
                        $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                        // Generate a unique identifier based on timestamp and random component
                        $uniqueId = time() . '_' . mt_rand(1000, 9999);
                        $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                        $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                        return "{$baseName}-{$uniqueId}.{$extension}";
                    })
                    ->toMediaCollection('sellers', $disk);

                $mediaIds[] = $national_identity_card->id;
            }

            //code for storing s3 object url for media

            if ($disk == 's3') {
                $media_list = $media->getMedia('sellers');
                for ($i = 0; $i < count($mediaIds); $i++) {
                    $media_url = $media_list[($media_list->count()) - (count($mediaIds) - $i)]->getUrl();

                    switch ($i) {
                        case 0:
                            $address_proof_url = $media_url;
                            break;
                        case 1:
                            $logo_url = $media_url;
                            break;
                        case 2:
                            $store_thumbnail_url = $media_url;
                            break;
                        case 3:
                            $authorized_signature_url = $media_url;
                            break;
                        case 4:
                            $national_identity_card_url = $media_url;
                            break;
                        case 5:
                            $profile_image_url = $media_url;
                            break;
                        case 6:
                            $other_documents_url = $media_url;
                            break;
                            // Add more cases as needed
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
        $user_data = [
            'role_id' => 4,
            'active' => $request->status,
            'password' => bcrypt($request->password),
            'address' => $request->address,
            'username' => $request->name,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'image' => $disk == 's3' ? (isset($profile_image_url) ? $profile_image_url : '') : (isset($profile_image->file_name) ? '/' . $profile_image->file_name : ''),
        ];
        // dd(json_encode($other_document_file_names));
        $seller_store_data['address_proof'] = $disk == 's3' ? (isset($address_proof_url) ? $address_proof_url : '') : (isset($address_proof->file_name) ? '/' . $address_proof->file_name : '');

        $seller_store_data['logo'] = $disk == 's3' ? (isset($logo_url) ? $logo_url : '') : (isset($store_logo->file_name) ? '/' . $store_logo->file_name : '');

        $seller_store_data['other_documents'] = $disk == 's3' ? (isset($other_documents_url) ? ($other_documents_url) : '') : (isset($other_documents->file_name) ? json_encode($other_document_file_names) : '');

        $seller_store_data['store_thumbnail'] = $disk == 's3' ? (isset($store_thumbnail_url) ? $store_thumbnail_url : '') : (isset($store_thumbnail->file_name) ? '/' . $store_thumbnail->file_name : '');

        $seller_data['authorized_signature'] = $disk == 's3' ? (isset($authorized_signature_url) ? $authorized_signature_url : '') : (isset($authorized_signature->file_name) ? '/' . $authorized_signature->file_name : '');

        $seller_data['national_identity_card'] = $disk == 's3' ? (isset($national_identity_card_url) ? $national_identity_card_url : '') : (isset($national_identity_card->file_name) ? '/' . $national_identity_card->file_name : '');
        // dd($seller_store_data);
        $permmissions = array();
        $permmissions['require_products_approval'] = ($request->require_products_approval == "on") ? 1 : 0;
        $permmissions['customer_privacy'] = ($request->customer_privacy == "on") ? 1 : 0;
        $permmissions['view_order_otp'] = ($request->view_order_otp == "on") ? 1 : 0;
        // dd($request);
        if ($fromApp == true) {
            $requested_categories = $request->requested_categories;
        } else {
            $requested_categories = implode(',', (array) $request->requested_categories);
        }
        if (isset($request->commission_data) && !empty($request->commission_data)) {
            $commission_data = json_decode($request->commission_data, true);
            $category_ids = implode(',', (array) $commission_data['category_id']);
        }



        if (!$user) {
            $user = User::create($user_data);
            if (!empty($request->fcm_id)) {
                $fcm_data = [
                    'fcm_id' => $request->fcm_id ?? '',
                    'user_id' => $user->id,
                ];
                $existing_fcm = DB::table('user_fcm')
                    ->where('user_id', $user->id)
                    ->where('fcm_id', $request->fcm_id)
                    ->first();

                if (!$existing_fcm) {
                    DB::table('user_fcm')->insert($fcm_data);
                }
            }

            $seller_data = array_merge($seller_data, [
                'user_id' => $user->id,
                'status' => $request->status ?? 2,
                'pan_number' => $request->pan_number,
                'disk' => isset($authorized_signature->disk) && !empty($authorized_signature->disk) ? $authorized_signature->disk : 'public',
            ]);


            $seller = Seller::create($seller_data);
        } else {

            $seller_store_details = DB::table('seller_store')->select('store_id')->where('user_id', $user->id)->get()[0]->store_id;
            $seller = Seller::where('user_id', $user->id)->first();

            if ($seller_store_details == $store_id) {
                return response()->json([
                    'error_message' => labels('admin_labels.seller_already_registered', 'Seller already registered in this store.'),
                    'language_message_key' => 'seller_already_registered'
                ]);
            }
        }
        if ($fromApp == true) {
            $zones = $request->deliverable_zones;
        } else {
            $zones = implode(',', (array) $request->deliverable_zones);
        }
        // dd($fromApp);
        if (isset($request->requested_categories) && !empty($request->requested_categories)) {
            if ($fromApp == true) {
                // $requested_commission_category_ids = implode(',', (array) $request->requested_categories);
                $requested_commission_category_ids = explode(',', $request->requested_categories);
            } else {
                $requested_commission_category_ids =  $request->requested_categories;
            }
            // dd($requested_commission_category_ids);
            foreach ($requested_commission_category_ids as $category_id) {
                SellerCommission::create([
                    'seller_id' => $seller->id,
                    'store_id' => $store_id,
                    'category_id' => $category_id,
                    'commission' => 0,
                ]);
            }
        }
        $seller_store_data = array_merge($seller_store_data, [
            'user_id' => $user->id,
            'seller_id' => $seller->id,
            'store_name' => $request->store_name ?? "",
            'store_url' => $request->store_url ?? "",
            'store_description' => $request->description ?? "",
            'commission' => $request->global_commission ?? 0,
            'account_number' => $request->account_number ?? "",
            'account_name' => $request->account_name ?? "",
            'bank_name' => $request->bank_name ?? "",
            'bank_code' => $request->bank_code ?? "",
            'status' => $request->store_status ? $request->store_status : 0,
            'tax_name' => $request->tax_name ?? "",
            'tax_number' => $request->tax_number ?? "",
            'category_ids' => isset($category_ids) ? $category_ids : ($requested_categories ?? ""),
            'permissions' => (isset($permmissions) && $permmissions != "") ? json_encode($permmissions) : null,
            'slug' => generateSlug($request->input('store_name'), 'seller_store'),
            'store_id' => $store_id,
            'latitude' => $request->latitude ?? "",
            'longitude' => $request->longitude ?? "",
            'city' => $request->city ?? "",
            'zipcode' => $request->zipcode ?? "",
            'disk' => isset($address_proof->disk) && !empty($address_proof->disk) ? $address_proof->disk : 'public',
            'deliverable_type' => isset($request->deliverable_type) && !empty($request->deliverable_type) ? $request->deliverable_type : '',
            'deliverable_zones' => ($request->deliverable_type == '1' || $request->deliverable_type == '0') ? '' : $zones,
        ]);

        $seller_store = DB::table('seller_store')->insert($seller_store_data);



        if (isset($request->commission_data) && !empty($request->commission_data)) {
            $commission_data = json_decode($request->commission_data, true);
            if (is_array($commission_data['category_id'])) {
                if (count($commission_data['category_id']) >= 2) {
                    $cat_array = array_unique($commission_data['category_id']);
                    foreach ($commission_data['commission'] as $key => $val) {
                        if (!array_key_exists($key, $cat_array))
                            unset($commission_data['commission'][$key]);
                    }
                    $cat_array = array_values($cat_array);
                    $com_array = array_values($commission_data['commission']);

                    for ($i = 0; $i < count($cat_array); $i++) {
                        $tmp['seller_id'] = $seller->id;
                        $tmp['category_id'] = $cat_array[$i];
                        $tmp['commission'] = $com_array[$i];
                        $com_data[] = $tmp;
                    }
                } else {
                    $com_data[0] = array(
                        "seller_id" => $seller->id,
                        "category_id" => $commission_data['category_id'],
                        "commission" => $commission_data['commission'],
                    );
                }
            } else {
                $com_data[0] = array(
                    "seller_id" => $seller->id,
                    "category_id" => $commission_data['category_id'],
                    "commission" => $commission_data['commission'],
                );
            }
        }

        if (isset($com_data) && !empty($com_data)) {
            foreach ($com_data as $commission) {
                // dd($commission);
                SellerCommission::create([
                    'seller_id' => $commission['seller_id'],
                    'store_id' => $store_id,
                    'category_id' => $commission['category_id'],
                    'commission' => $commission['commission'],
                ]);
            }
        }
        if ($request->ajax()) {
            return response()->json([
                'message' => labels('admin_labels.seller_registered_successfully', 'Seller registered successfully'),
                'location' => route('sellers.index')
            ]);
        } else {
            return response()->json($seller);
        }
    }

    public function edit($id)
    {
        $seller_data = User::find($id);
        $store_id = getStoreId();
        $all_categories = Category::where('status', 1)->where('store_id', $store_id)->orderBy('id', 'desc')->get();

        $zipcodes = Zipcode::orderBy('id', 'desc')->get();

        $cities = City::orderBy('id', 'desc')->get();
        $language_code = get_language_code();
        $note_for_necessary_documents = fetchDetails('stores', ['id' => $store_id], 'note_for_necessary_documents');
        $note_for_necessary_documents = isset($note_for_necessary_documents) && $note_for_necessary_documents[0]->note_for_necessary_documents != null ? $note_for_necessary_documents[0]->note_for_necessary_documents : "Other Documents";

        $store_data = DB::table('seller_store')
            ->leftJoin('seller_data', 'seller_data.id', '=', 'seller_store.seller_id')
            ->leftJoin('zipcodes', 'seller_store.zipcode', '=', 'zipcodes.id')
            ->leftJoin('cities', 'seller_store.city', '=', 'cities.id')
            ->select(
                'seller_store.*',
                'seller_data.authorized_signature',
                'seller_data.status as seller_status',
                'seller_data.national_identity_card',
                'seller_store.address_proof',
                'seller_data.pan_number',
                'zipcodes.zipcode as selected_zipcode',
                'cities.name as selected_city'
            )
            ->where('seller_store.store_id', $store_id)
            ->where('seller_store.user_id', $id)
            ->get();
        if ($store_data->isEmpty()) {
            return view('admin.pages.views.no_data_found');
        } else {
            $category_ids_string = $store_data[0]->category_ids;
            $existing_category_ids = explode(',', $category_ids_string);
            // dd($existing_category_ids);
            $categories = [];
            foreach ($all_categories as $category) {
                if (!in_array($category->id, $existing_category_ids)) {
                    $categories[] = $category;
                }
            }



            return view('admin.pages.forms.update_seller', compact('seller_data', 'categories', 'store_data', 'store_id', 'zipcodes', 'cities', 'note_for_necessary_documents', 'existing_category_ids', 'language_code'));
        }
    }

    public function update(Request $request, $id, $fromApp = false)
    {
        // dd($request->store_status);

        $seller_data = User::find($id);
        $user = User::find($id);
        $seller_id = Seller::where('user_id', $id)->value('id');

        if (!$seller_data) {
            return response()->json(['error' => true, 'message' => labels('admin_labels.data_not_found', 'Data Not Found')], 404);
        } else {

            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'mobile' => 'required',
                'email' => 'required',
                'address' => 'required',
                'store_name' => 'required',
                'account_number' => 'required',
                'account_name' => 'required',
                'bank_name' => 'required',
                'bank_code' => 'required',
                'city' => 'required',
                'zipcode' => 'required',
                'deliverable_type' => 'required',
                'latitude' => 'numeric|between:-90,90',
                'longitude' => 'numeric|between:-180,180'

            ]);
            if ($fromApp == false) {
                $validator = Validator::make($request->all(), [
                    'global_commission' => 'required',
                ]);
            }
            if ($fromApp == false) {
                $validator = Validator::make($request->all(), [
                    'status' => 'required',
                ]);
            }

            if (!empty($request->input('old')) || !empty($request->input('new'))) {
                $validator = Validator::make($request->all(), [
                    'old' => 'required',
                ]);
            }

            if (!empty($request->input('old'))) {
                if (!Hash::check(($request->input('old')), $user->password)) {
                    if ($request->ajax()) {
                        return response()->json(['message' => labels('admin_labels.incorrect_old_password', 'The old password is incorrect.')], 422);
                    }
                }
            }
            if ($request->filled('new')) {
                $request['password'] = bcrypt($request->input('new'));
            }

            if ($validator->fails()) {
                $errors = $validator->errors();

                if ($request->ajax()) {

                    return response()->json(['errors' => $errors->all()], 422);
                } else {

                    $response = [
                        'error' => true,
                        'message' => $validator->errors(),
                        'code' => 102,
                    ];
                    return response()->json($response);
                }
            }

            if ($fromApp == true) {
                $store_id = $request->store_id;
            } else {
                $store_id = getStoreId();
            }

            $seller = Seller::find($seller_id);
            $seller_details = Seller::where('user_id', $id)->get();
            $seller_store_detail = DB::table('seller_store')
                ->where('seller_id', $seller_id)
                ->where('store_id', $store_id)->get();
            $disk = $seller_details[0]->disk;
            $current_disk = isset($media_storage_settings) && !empty($media_storage_settings) ? $media_storage_settings[0]->name : 'public';

            if ($request->hasFile('profile_image')) {

                // Specify the path and disk from which you want to delete the file
                if ($disk == 's3') {
                    $path = $request->edit_profile_image;
                } else {
                    $path = 'sellers/' . $user['image']; // Example path to the file you want to delete
                }

                // Call the removeFile method to delete the file
                removeMediaFile($path, $disk);

                $profile_image = $request->file('profile_image');

                // Add and sanitize the new image
                $profile_image = $seller->addMedia($profile_image)
                    ->sanitizingFileName(function ($fileName) use ($seller) {
                        // Replace special characters and spaces with hyphens
                        $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                        // Generate a unique identifier based on timestamp and random component
                        $uniqueId = time() . '_' . mt_rand(1000, 9999);
                        $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                        $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                        return "{$baseName}-{$uniqueId}.{$extension}";
                    })
                    ->toMediaCollection('sellers', $current_disk);

                $mediaIds[] = $profile_image->id;

                // If a new image is uploaded, set the image URL for storage
                $imagePath = $disk == 's3'
                    ? (isset($profile_image_url) ? $profile_image_url : '')
                    : (isset($profile_image->file_name) ? '/' . $profile_image->file_name : '');
            } else {
                // If no image is uploaded, keep the existing image path
                $imagePath = $user['image'];
            }
            $user_details = fetchDetails('users', ['id' => $id], '*');
            $user_data = [
                'role_id' => 4,
                'active' => $request->status ?? 1,
                'address' => $request->address ?? $user_details[0]->address,
                'username' => $request->name ?? $user_details[0]->username,
                'mobile' => $request->mobile ?? $user_details[0]->mobile,
                'email' => $request->email ?? $user_details[0]->email,
                'image' => $imagePath,
                'city' => $request->city ?? $user_details[0]->city,
                'pincode' => $request->zipcode ?? $user_details[0]->pincode,
            ];
            if ($request->filled('new')) {
                $user_data['password'] = $request->input('password');
            }

            $seller_data->update($user_data);

            // Example disk (filesystem) from which you want to delete the file

            $seller_data = [];
            $seller_store_data = [];

            $mediaIds = [];

            $media_storage_settings = fetchDetails('storage_types', ['is_default' => 1], '*');

            try {
                // dd($request->file());
                if ($request->hasFile('other_documents')) {
                    // Retrieve existing files from the database
                    $existing_documents = json_decode($seller_store_detail[0]->other_documents, true) ?? [];

                    $other_documents = $request->file('other_documents');
                    $other_document_file_names = [];

                    foreach ($other_documents as $file) {
                        $uploadedFile = $seller->addMedia($file)
                            ->sanitizingFileName(function ($fileName) {
                                $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                                $uniqueId = time() . '_' . mt_rand(1000, 9999);
                                $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                                $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                                return "{$baseName}-{$uniqueId}.{$extension}";
                            })
                            ->toMediaCollection('sellers', $current_disk);

                        $other_document_file_names[] = $uploadedFile->file_name;
                        $mediaIds[] = $uploadedFile->id;
                    }

                    // Merge new files with existing ones
                    $all_other_documents = array_merge($existing_documents, $other_document_file_names);
                } else {
                    // If no new files are uploaded, keep old ones
                    $all_other_documents = json_decode($seller_store_detail[0]->other_documents, true) ?? [];
                }

                // Store updated list in the database
                $seller_store_data['other_documents'] = json_encode($all_other_documents);

                if ($request->hasFile('address_proof')) {

                    // Specify the path and disk from which you want to delete the file
                    if ($disk == 's3') {
                        $path = $request->edit_address_proof;
                    } else {
                        $path = 'sellers/' . $seller_store_detail[0]->address_proof; // Example path to the file you want to delete
                    }

                    // Call the removeFile method to delete the file
                    removeMediaFile($path, $disk);


                    $addressProofFile = $request->file('address_proof');

                    $address_proof = $seller->addMedia($addressProofFile)
                        ->sanitizingFileName(function ($fileName) use ($seller) {
                            // Replace special characters and spaces with hyphens
                            $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                            // Generate a unique identifier based on timestamp and random component
                            $uniqueId = time() . '_' . mt_rand(1000, 9999);
                            $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                            $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                            return "{$baseName}-{$uniqueId}.{$extension}";
                        })
                        ->toMediaCollection('sellers', $current_disk);

                    $mediaIds[] = $address_proof->id;
                }
                if ($request->hasFile('store_logo')) {

                    if ($disk == 's3') {
                        $path = $request->edit_store_logo;
                    } else {
                        $path = 'sellers/' . $seller_store_detail[0]->logo; // Example path to the file you want to delete
                    }

                    // Call the removeFile method to delete the file
                    removeMediaFile($path, $disk);


                    $storeLogoFile = $request->file('store_logo');

                    $store_logo = $seller->addMedia($storeLogoFile)
                        ->sanitizingFileName(function ($fileName) use ($seller) {
                            // Replace special characters and spaces with hyphens
                            $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                            // Generate a unique identifier based on timestamp and random component
                            $uniqueId = time() . '_' . mt_rand(1000, 9999);
                            $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                            $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                            return "{$baseName}-{$uniqueId}.{$extension}";
                        })
                        ->toMediaCollection('sellers', $current_disk);

                    $mediaIds[] = $store_logo->id;
                }
                if ($request->hasFile('store_thumbnail')) {

                    // Check if the old thumbnail exists before attempting to remove it
                    if (!empty($seller_store_detail[0]->store_thumbnail)) {
                        if ($disk == 's3') {
                            $path = $request->edit_store_thumbnail;
                        } else {
                            $path = 'sellers/' . $seller_store_detail[0]->store_thumbnail; // Example path to the file you want to delete
                        }

                        // Call the removeFile method to delete the file
                        removeMediaFile($path, $disk);
                    }

                    // Proceed with uploading the new store thumbnail
                    $storeThumbnailFile = $request->file('store_thumbnail');
                    // dd($storeThumbnailFile);
                    $store_thumbnail = $seller->addMedia($storeThumbnailFile)
                        ->sanitizingFileName(function ($fileName) use ($seller) {
                            // Replace special characters and spaces with hyphens
                            $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                            // Generate a unique identifier based on timestamp and random component
                            $uniqueId = time() . '_' . mt_rand(1000, 9999);
                            $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                            $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                            return "{$baseName}-{$uniqueId}.{$extension}";
                        })
                        ->toMediaCollection('sellers', $current_disk);

                    // Store the media ID for further reference
                    $mediaIds[] = $store_thumbnail->id;
                }



                if ($request->hasFile('authorized_signature')) {

                    if ($disk == 's3') {
                        $path = $request->edit_authorized_signature;
                    } else {
                        $path = 'sellers/' . $seller->authorized_signature; // Example path to the file you want to delete
                    }

                    // Call the removeFile method to delete the file
                    removeMediaFile($path, $disk);

                    $authorizedSignatureFile = $request->file('authorized_signature');

                    $authorized_signature = $seller->addMedia($authorizedSignatureFile)
                        ->sanitizingFileName(function ($fileName) use ($seller) {
                            // Replace special characters and spaces with hyphens
                            $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                            // Generate a unique identifier based on timestamp and random component
                            $uniqueId = time() . '_' . mt_rand(1000, 9999);
                            $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                            $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                            return "{$baseName}-{$uniqueId}.{$extension}";
                        })
                        ->toMediaCollection('sellers', $current_disk);

                    $mediaIds[] = $authorized_signature->id;
                }

                if ($request->hasFile('national_identity_card')) {
                    if ($disk == 's3') {
                        $path = $request->edit_national_identity_card;
                    } else {
                        $path = 'sellers/' . $seller->national_identity_card; // Example path to the file you want to delete
                    }

                    // Call the removeFile method to delete the file
                    removeMediaFile($path, $disk);

                    $nationalIdentityCardFile = $request->file('national_identity_card');

                    $national_identity_card = $seller->addMedia($nationalIdentityCardFile)
                        ->sanitizingFileName(function ($fileName) use ($seller) {
                            // Replace special characters and spaces with hyphens
                            $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                            // Generate a unique identifier based on timestamp and random component
                            $uniqueId = time() . '_' . mt_rand(1000, 9999);
                            $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                            $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                            return "{$baseName}-{$uniqueId}.{$extension}";
                        })
                        ->toMediaCollection('sellers', $current_disk);

                    $mediaIds[] = $national_identity_card->id;
                }


                //code for storing s3 object url for media

                if ($current_disk == 's3') {
                    $media_list = $seller->getMedia('sellers');
                    for ($i = 0; $i < count($mediaIds); $i++) {
                        $media_url = $media_list[($media_list->count()) - (count($mediaIds) - $i)]->getUrl();

                        $fileName = implode('/', array_slice(explode('/', $media_url), -1));

                        if (isset($profile_image->file_name) && $fileName == $profile_image->file_name) {
                            $profile_image_url = $media_url;
                        }
                        if (isset($address_proof->file_name) && $fileName == $address_proof->file_name) {
                            $address_proof_url = $media_url;
                        }
                        if (isset($store_logo->file_name) && $fileName == $store_logo->file_name) {
                            $logo_url = $media_url;
                        }
                        if (isset($store_thumbnail->file_name) && $fileName == $store_thumbnail->file_name) {
                            $store_thumbnail_url = $media_url;
                        }
                        if (isset($authorized_signature->file_name) && $fileName == $authorized_signature->file_name) {
                            $authorized_signature_url = $media_url;
                        }
                        if (isset($national_identity_card->file_name) && $fileName == $national_identity_card->file_name) {
                            $national_identity_card_url = $media_url;
                        }
                        if (isset($other_documents->file_name)) {
                            $other_documents_url = $media_url;
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
            if (isset($address_proof->file_name)) {
                $seller_store_data['address_proof'] = $current_disk == 's3' ? (isset($address_proof_url) ? $address_proof_url : '') : (isset($address_proof->file_name) ? '/' . $address_proof->file_name : '');
            } else {
                $seller_store_data['address_proof'] = $request->edit_address_proof;
                $seller_store_data['address_proof'] = $seller_store_detail[0]->address_proof;
            }

            if (isset($store_logo->file_name)) {
                $seller_store_data['logo'] = $current_disk == 's3' ? (isset($logo_url) ? $logo_url : '') : (isset($store_logo->file_name) ? '/' . $store_logo->file_name : '');
            } else {
                $seller_store_data['logo'] = $request->edit_store_logo;
                $seller_store_data['logo'] = $seller_store_detail[0]->logo;
            }

            $seller_store_data['other_documents'] = json_encode($all_other_documents);

            if (isset($store_thumbnail->file_name)) {
                $seller_store_data['store_thumbnail'] = $current_disk == 's3' ? (isset($store_thumbnail_url) ? $store_thumbnail_url : '') : (isset($store_thumbnail->file_name) ? '/' . $store_thumbnail->file_name : '');
            } else {
                $seller_store_data['store_thumbnail'] = $request->edit_store_thumbnail;
                $seller_store_data['store_thumbnail'] = $seller_store_detail[0]->store_thumbnail;
            }
            if (isset($authorized_signature->file_name)) {
                $seller_data['authorized_signature'] = $current_disk == 's3' ? (isset($authorized_signature_url) ? $authorized_signature_url : '') : (isset($authorized_signature->file_name) ? '/' . $authorized_signature->file_name : '');
            } else {
                $seller_data['authorized_signature'] = $request->edit_authorized_signature;
                $seller_data['authorized_signature'] = $seller->authorized_signature;
            }

            if (isset($national_identity_card->file_name)) {
                $seller_data['national_identity_card'] = $current_disk == 's3' ? (isset($national_identity_card_url) ? $national_identity_card_url : '') : (isset($national_identity_card->file_name) ? '/' . $national_identity_card->file_name : '');
            } else {
                $seller_data['national_identity_card'] = $request->edit_national_identity_card;
                $seller_data['national_identity_card'] = $seller->national_identity_card;
            }

            $permmissions = array();
            $permmissions['require_products_approval'] = ($request->require_products_approval == "on") ? 1 : 0;
            $permmissions['customer_privacy'] = ($request->customer_privacy == "on") ? 1 : 0;
            $permmissions['view_order_otp'] = ($request->view_order_otp == "on") ? 1 : 0;

            $commission_data = json_decode($request->commission_data, true);

            if (isset($commission_data['category_id']) && !empty($commission_data['category_id'])) {
                if (isset($commission_data['category_id']) && !empty($commission_data['category_id'])) {
                    if (!is_array($commission_data['category_id'])) {
                        $category_ids = $commission_data['category_id'];
                    } else {
                        $category_ids = implode(',', $commission_data['category_id']);
                    }
                }
            } else {
                $categoryids = fetchDetails("seller_store", ['seller_id' => $seller_id, 'store_id' => $store_id], "*");

                $categories = $categoryids[0]->category_ids;
            }

            $seller_data = array_merge($seller_data, [
                'status' => $request->status ?? $seller->status,
                'pan_number' => $request->pan_number,
                // 'disk' => isset($authorized_signature->disk) && !empty($authorized_signature->disk) ? $authorized_signature->disk : $disk,

            ]);



            $seller->update($seller_data);
            $new_name = $request->store_name;
            $current_name = $seller_store_detail[0]->store_name;
            $current_slug = $seller_store_detail[0]->slug;
            $updated_seller = Seller::where('user_id', $id)->first();

            // send notification to seller when seller's store status change
            if ($fromApp == true) {
                $zones = $request->deliverable_zones;
            } else {
                $zones = implode(',', (array) $request->deliverable_zones);
            }

            // dd($zones);
            $seller_store_data = array_merge($seller_store_data, [
                'store_name' => $request->store_name,
                'store_url' => $request->store_url,
                'store_description' => $request->description,
                'commission' => $request->global_commission ?? 0,
                'account_number' => $request->account_number,
                'account_name' => $request->account_name,
                'bank_name' => $request->bank_name,
                'bank_code' => $request->bank_code,
                'status' => $request->store_status ? $request->store_status : 1,
                'tax_name' => $request->tax_name,
                'tax_number' => $request->tax_number,
                'category_ids' => isset($category_ids) && !empty($category_ids) ? $category_ids : $categories,
                'permissions' => (isset($permmissions) && $permmissions != "") ? json_encode($permmissions) : null,
                'slug' => generateSlug($new_name, 'seller_store', 'slug', $current_slug, $current_name),
                'store_id' => $store_id,
                'latitude' => $request->latitude ?? "",
                'longitude' => $request->longitude ?? "",
                'disk' => isset($address_proof->disk) && !empty($address_proof->disk) ? $address_proof->disk : $disk,
                'city' => $request->city ?? "",
                'zipcode' => $request->zipcode ?? "",
                'deliverable_type' => isset($request->deliverable_type) ? $request->deliverable_type : '',
                'deliverable_zones' => ($request->deliverable_type == '1' || $request->deliverable_type == '0') ? '' : $zones,
            ]);
            if ($request->store_status !== null) {
                if ($seller_store_detail[0] != $request->store_status) {
                    $fcm_ids = array();
                    $results = UserFcm::join('users', 'user_fcm.user_id', '=', 'users.id')
                        ->where('user_fcm.user_id', $user->id)
                        ->where('users.is_notification_on', 1)
                        ->select('user_fcm.fcm_id')
                        ->get();
                    foreach ($results as $result) {
                        if (is_object($result)) {
                            $fcm_ids[] = $result->fcm_id;
                        }
                    }
                    $store_name = fetchDetails('stores', ['id' => $store_id], 'name');
                    $store_name = $store_name[0]->name;
                    $store_name = json_decode($store_name, true);
                    $store_name = $store_name['en'] ?? '';
                    // dd($store_name);
                    $status = $request->store_status == 1 ? 'Approved' : 'Not approved';

                    $title = "Store status changed";
                    $message = "Hello dear " . $request->name . " Your " . $store_name . " store status is changed to " . $status;
                    $fcmMsg = array(
                        'title' => "$title",
                        'body' => "$message",
                        'type' => "status_change",
                        'seller_id' => "$seller_id",
                        'store_id' => "$store_id",
                        'status' => "$request->store_status"
                    );
                    $registrationIDs_chunks = array_chunk($fcm_ids, 1000);
                    sendNotification('', $registrationIDs_chunks, $fcmMsg);
                }
            }
            $seller_store = DB::table('seller_store')
                ->where('seller_id', $seller_id)
                ->where('store_id', $store_id)
                ->update($seller_store_data);

            $commission_data = json_decode($request->commission_data, true);
            // dd($commission_data);
            if (isset($commission_data['category_id']) && !empty($commission_data['category_id'])) {

                if (is_array($commission_data['category_id'])) {
                    if (count($commission_data['category_id']) >= 2) {
                        $cat_array = array_unique($commission_data['category_id']);
                        foreach ($commission_data['commission'] as $key => $val) {
                            if (!array_key_exists($key, $cat_array))
                                unset($commission_data['commission'][$key]);
                        }
                        $cat_array = array_values($cat_array);
                        $com_array = array_values($commission_data['commission']);

                        for ($i = 0; $i < count($cat_array); $i++) {
                            $tmp['seller_id'] = $updated_seller->id;
                            $tmp['category_id'] = $cat_array[$i];
                            $tmp['commission'] = $com_array[$i];
                            $com_data[] = $tmp;
                        }
                    } else {
                        $com_data[0] = array(
                            "seller_id" => $updated_seller->id,
                            "category_id" => $commission_data['category_id'],
                            "commission" => $commission_data['commission'],
                        );
                    }
                } else {
                    $com_data[0] = array(
                        "seller_id" => $updated_seller->id,
                        "category_id" => $commission_data['category_id'],
                        "commission" => $commission_data['commission'],
                    );
                }
            }


            if (isset($com_data) && !empty($com_data)) {

                deleteDetails(['seller_id' => $updated_seller->id], 'seller_commissions');
                foreach ($com_data as $commission) {
                    // dd($commission);
                    SellerCommission::create([
                        'seller_id' => $commission['seller_id'],
                        'store_id' => $store_id,
                        'category_id' => $commission['category_id'],
                        'commission' => $commission['commission'],
                    ]);
                }
            }
            if ($request->ajax()) {
                return response()->json([
                    'message' => labels('admin_labels.seller_updated_successfully', 'Seller updated successfully'),
                    'location' => route('sellers.index')
                ]);
            }
        }
    }

    public function destroy($id)
    {
        $user = User::find($id);
        // dd($user->id);
        if ($user) {
            $seller_data = Seller::where('user_id', $user->id)->select('id')->get();
            $seller_id = isset($seller_data) ? $seller_data[0]->id : "";
            $products = Product::where('seller_id', $seller_id)->count();
            if ($products > 0) {
                return response()->json([
                    'error' => labels('admin_labels.cannot_delete_seller_associated_data', 'Cannot delete seller. There are associated seller data records.')
                ]);
            }
            Seller::where('user_id', $user->id)->delete();

            if ($user->delete()) {
                return response()->json([
                    'error' => false,
                    'message' => labels('admin_labels.seller_deleted_successfully', 'Seller deleted successfully!')
                ]);
            }
        }
        return response()->json(['error' => labels('admin_labels.data_not_found', 'Data Not Found')]);
    }

    public function list()
    {
        $store_id = getStoreId();
        $search = trim(request('search'));
        $sort = 'users.id';
        $order = request('order') ?: 'DESC';
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;

        $limit = (request('limit')) ? request('limit') : "10";

        $sellers = User::with('seller_data')
            ->select('users.*', 'seller_store.*', 'seller_data.*', 'seller_data.status as seller_status')
            ->where('role_id', 4)
            ->where(function ($query) use ($search) {
                $query->where('users.username', 'like', '%' . $search . '%')
                    ->orWhere('users.id', 'like', '%' . $search . '%')
                    ->orWhere('users.email', 'like', '%' . $search . '%')
                    ->orWhere('users.mobile', 'like', '%' . $search . '%');
            })
            ->join('seller_data', 'users.id', '=', 'seller_data.user_id')
            ->join('seller_store', 'users.id', '=', 'seller_store.user_id')
            ->where('seller_store.store_id', $store_id);

        if (request()->filled('productStatus')) {
            $sellers->where('seller_data.status', request('productStatus'));
        }

        $total = $sellers->count();
        $sellers = $sellers->orderBy($sort, $order)
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(function ($seller) {

                $isPublicDisk = $seller->disk == 'public' ? 1 : 0;
                $logo = $isPublicDisk
                    ? asset(config('constants.SELLER_IMG_PATH') . $seller->logo)
                    : $seller->logo;

                $store_thumbnail = $isPublicDisk
                    ? asset(config('constants.SELLER_IMG_PATH') . $seller->store_thumbnail)
                    : $seller->store_thumbnail;

                $active_status = "";
                $delete_url = route('admin.sellers.destroy', $seller->user_id);
                $edit_url = route('admin.sellers.edit', $seller->user_id);
                $action = '<div class="dropdown bootstrap-table-dropdown">
                    <a href="#" class="text-dark" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="bx bx-dots-horizontal-rounded"></i>
                    </a>
                    <div class="dropdown-menu table_dropdown seller_action_dropdown" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item dropdown_menu_items" href="' . $edit_url . '"><i class="bx bx-pencil mx-2"></i> Edit</a>
                        <a class="dropdown-item delete-data dropdown_menu_items" data-url="' . $delete_url . '"><i class="bx bx-trash mx-2"></i> Delete</a>
                    </div>
                </div>';
                if ($seller->seller_status == '1') {
                    $active_status = '<label class="badge bg-primary">Approved</label>';
                }
                if ($seller->seller_status == '2') {
                    $active_status = '<label class="badge bg-secondary">Not Approved</label>';
                }
                if ($seller->seller_status == '0') {
                    $active_status = '<label class="badge bg-warning">Deactive</label>';
                }
                if ($seller->seller_status == '7') {
                    $active_status = '<label class="badge bg-secondary">Removed</label>';
                }



                $logo = route('admin.dynamic_image', [
                    'url' => getMediaImageUrl($seller->logo, 'SELLER_IMG_PATH'),
                    'width' => 60,
                    'quality' => 90
                ]);
                $store_thumbnail = route('admin.dynamic_image', [
                    'url' => getMediaImageUrl($seller->store_thumbnail, 'SELLER_IMG_PATH'),
                    'width' => 60,
                    'quality' => 90
                ]);

                return [
                    'id' => $seller->id,
                    'name' => $seller->username,
                    'mobile' => $seller->mobile,
                    'email' => $seller->email,
                    'balance' => formateCurrency(formatePriceDecimal($seller->balance)),
                    'store_name' => $seller->store_name ?? '',
                    'address' => $seller->address ?? '',
                    'store_url' => $seller->store_url,
                    'store_description' => $seller->store_description,
                    'account_name' => $seller->account_name,
                    'account_number' => $seller->account_number,
                    'bank_name' => $seller->bank_name,
                    'bank_code' => $seller->bank_code,
                    'tax_name' => $seller->tax_name,
                    'tax_number' => $seller->tax_number,
                    'pan_number' => $seller->pan_number,
                    'logo' => '<div class="mx-auto"><a href="' . getMediaImageUrl($seller->logo, 'SELLER_IMG_PATH') . '" data-lightbox="image-' . $seller->id . '" data-gallery="gallery"><img src="' . $logo . '" class="rounded"></a></div>',
                    'store_thumbnail' => '<div class="mx-auto"><a href="' . getMediaImageUrl($seller->store_thumbnail, 'SELLER_IMG_PATH') . '" data-lightbox="image-' . $seller->id . '" data-gallery="gallery"><img src="' . $store_thumbnail . '" class="rounded"></a></div>',
                    'status' => $active_status,
                    'operate' => $action
                ];
            });

        return response()->json([
            "rows" => $sellers,
            "total" => $total,
        ]);
    }


    public function getsellerCommissionData(Request $request)
    {
        // dd($request);
        $result = array();
        if (isset($request->id) && !empty($request->id)) {
            $id = $request->id;
            $result = $this->getSellerCommissionDetails($id);
            // dd($result);
            if (empty($result)) {
                if (empty($result)) {
                    $result = $this->getCategories();
                }
            }
        } else {
            $result = fetchDetails('categories', ['status' => 1], 'id,name');
        }
        if (empty($result)) {
            if ($request->ajax()) {
                return response()->json([
                    'error' => 'true',
                    'message' => labels('admin_labels.no_category_commission_data_found', 'No category & commission data found for seller.')
                ]);
            }
        } else {
            if ($request->ajax()) {
                return response()->json(['error' => 'false', 'data' => $result]);
            }
        }
    }

    public function getSellerCommissionDetails($id)
    {
        $store_id = getStoreId();
        $language_code = get_language_code();
        $data = DB::table('seller_commissions as sc')
            ->select('sc.*', 'c.*')
            ->join('categories as c', 'c.id', '=', 'sc.category_id')
            ->where('sc.seller_id', $id)
            ->where('c.store_id', $store_id)
            ->orderBy('sc.category_id', 'ASC')
            ->get()
            ->toArray();

        if (!empty($data)) {
            foreach ($data as &$item) {
                $item->name = getDynamicTranslation('categories', 'name', $item->category_id, $language_code);
            }
            return $data;
        } else {
            return false;
        }
    }

    public function getCategories($id = null, $limit = '', $offset = '', $sort = 'row_order', $order = 'ASC', $has_child_or_item = 'true', $slug = '', $ignore_status = '', $seller_id = '')
    {
        $level = 0;
        $store_id = getStoreId();
        $language_code = get_language_code();
        if ($ignore_status == 1) {
            $where = (isset($id) && !empty($id)) ? ['id' => $id, 'store_id' => $store_id] : ['parent_id' => 0, 'store_id' => $store_id];
        } else {
            $where = (isset($id) && !empty($id)) ? ['id' => $id, 'status' => 1, 'store_id' => $store_id] : ['parent_id' => 0, 'status' => 1, 'store_id' => $store_id];
        }

        $query = Category::orderBy($sort, $order)
            ->where($where);

        if ($has_child_or_item == 'false') {
            $query->leftJoin('categories as c2', 'c2.parent_id', '=', 'id')
                ->leftJoin('products as p', 'p.category_id', '=', 'id')

                ->where(function ($query) {
                    $query->where('id', '=', DB::raw('p.category_id'))
                        ->orWhere('c2.parent_id', '=', 'id');
                })
                ->groupBy('id');
        } else {

            if (!empty($limit)) {
                $query->take($limit);
            }

            if (!empty($offset)) {
                $query->skip($offset);
            }
        }


        $categories = $query->get();

        $i = 0;
        foreach ($categories as $p_cat) {
            $categories[$i]->children = $this->subCategories($p_cat->id, $level);
            $categories[$i]->text = getDynamicTranslation('categories', 'name', $p_cat->id, $language_code);
            $categories[$i]->name = getDynamicTranslation('categories', 'name', $p_cat->id, $language_code);
            $categories[$i]->state = ['opened' => true];
            $categories[$i]->icon = "jstree-folder";
            $categories[$i]->level = $level;
            $p_cat['image'] = getMediaImageUrl($p_cat['image']);
            $p_cat['banner'] = getMediaImageUrl($p_cat['banner']);
            $i++;
        }
        return $categories;
    }

    public function subCategories($id, $level)
    {
        $level = $level + 1;
        $category = Category::find($id);
        $categories = $category->children;
        $language_code = get_language_code();
        $i = 0;
        foreach ($categories as $p_cat) {
            $categories[$i]->children = $this->subCategories($p_cat->id, $level);
            $categories[$i]->text = getDynamicTranslation('categories', 'name', $p_cat->id, $language_code);
            $categories[$i]->state = ['opened' => true];
            $categories[$i]->level = $level;
            $p_cat['image'] = getMediaImageUrl($p_cat['image']);
            $p_cat['banner'] = getMediaImageUrl($p_cat['banner']);
            $i++;
        }

        return $categories;
    }

    public function getSellers($zipcode_id = "", $limit = null, $offset = '', $sort = 'u.id', $order = 'DESC', $search = null, $filter = [], $store_id = '', $seller_ids = '', $user_id = '')
    {
        $query = User::query()
            ->select('users.*', 'seller_store.*', DB::raw("IF(favorites.seller_id IS NOT NULL, 1, 0) as is_favorite"))
            ->leftJoin('seller_store', 'seller_store.user_id', '=', 'users.id')
            ->leftJoin('favorites', function ($join) use ($user_id) {
                $join->on('favorites.seller_id', '=', 'seller_store.seller_id')
                    ->where('favorites.user_id', '=', $user_id);
            })
            ->where('users.active', 1)
            ->where('seller_store.status', 1)
            ->where("seller_store.store_id", $store_id);

        if (isset($filter) && !empty($filter['slug']) && $filter['slug'] != "") {
            $query->where('seller_store.slug', $filter['slug']);
        }

        if (request()->has('seller_id') && request()->input('seller_id') != "" && request()->input('seller_id') != null) {
            $query->where('seller_store.seller_id', request()->input('seller_id'));
        }

        if (isset($search) && $search != '') {
            $query->where(function ($q) use ($search) {
                $q->where('users.id', 'LIKE', "%{$search}%")
                    ->orWhere('users.username', 'LIKE', "%{$search}%")
                    ->orWhere('users.email', 'LIKE', "%{$search}%")
                    ->orWhere('users.mobile', 'LIKE', "%{$search}%")
                    ->orWhere('users.address', 'LIKE', "%{$search}%")
                    ->orWhere('users.balance', 'LIKE', "%{$search}%")
                    ->orWhere('seller_store.store_name', 'LIKE', "%{$search}%");
            });
        }

        $query->where('users.role_id', '4');

        if (!empty($zipcode_id) && $zipcode_id != "") {
            $query->where(function ($q) use ($zipcode_id) {
                $q->where(function ($q) use ($zipcode_id) {
                    $q->where('deliverable_type', '2')
                        ->whereIn(DB::raw("FIND_IN_SET('$zipcode_id', deliverable_zipcodes)"), [1, '1']);
                })
                    ->orWhere('deliverable_type', '1')
                    ->orWhere(function ($q) use ($zipcode_id) {
                        $q->where('deliverable_type', '3')
                            ->whereNotIn(DB::raw("FIND_IN_SET('$zipcode_id', deliverable_zipcodes)"), [1, '1']);
                    });
            });
        }
        if (isset($seller_ids) && !empty($seller_ids) && $seller_ids != null) {
            if (is_array($seller_ids) && !empty($seller_ids)) {
                $query->whereIn('seller_store.seller_id', $seller_ids);
            }
        }
        $total = $query->count();

        $query->groupBy('users.id')
            ->orderBy($sort, $order);

        if ($limit !== null) {
            $query->limit($limit);
        }

        if ($offset !== null) {
            $query->offset($offset);
        }

        $results = $query->get();

        $bulkData = [
            'error' => $results->isEmpty(),
            'message' => $results->isEmpty() ? labels('admin_labels.sellers_not_exist', 'Seller(s) does not exist')
                :
                labels('admin_labels.seller_retrieved_successfully', 'Seller retrieved successfully'),
            'language_message_key' => $results->isEmpty() ? 'sellers_not_exist' : 'seller_retrived_successfully',
            'total' => $total,
            'data' => [],
        ];

        $rows = [];
        foreach ($results as $user) {

            $regularProductsCount = fetchDetails('products', ['seller_id' => $user->seller_id, 'store_id' => $store_id, 'status' => '1'], 'id');

            $comboProductsCount = fetchDetails('combo_products', ['seller_id' => $user->seller_id, 'store_id' => $store_id, 'status' => '1'], 'id');

            $totalProductsCount = count($regularProductsCount) + count($comboProductsCount);

            $tempRow = [
                'seller_id' => $user->seller_id,
                'user_id' => $user->user_id,
                'seller_name' => stripslashes($user->username),
                'email' => $user->email,
                'mobile' => $user->mobile,
                'slug' => $user->slug ?? '',
                'rating' => $user->rating ?? '',
                'no_of_ratings' => $user->no_of_ratings ?? '',
                'store_name' => stripslashes($user->store_name),
                'store_url' => stripslashes($user->store_url),
                'store_description' => stripslashes($user->store_description),
                'store_logo' => getMediaImageUrl($user->logo, 'SELLER_IMG_PATH'),
                'balance' => empty($user->balance) ? "0" : number_format($user->balance, 2),
                'total_products' => $totalProductsCount,  // Combined product count
                'is_favorite' => $user->is_favorite,
            ];

            $rows[] = $tempRow;
        }

        $bulkData['data'] = $rows;

        return $bulkData;
    }



    public function sellerWallet()
    {
        return view('admin.pages.tables.seller_wallet');
    }

    public function wallet_transactions_list($user_id = '', $role_id = 2)
    {
        $search = trim(request()->input('search')) ?? '';
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = request()->input('limit', 10);
        $sort = request()->input('sort', 'id');
        $order = request()->input('order', 'ASC');
        $user_id = isset($user_id) && !empty($user_id) ? $user_id : (request()->has('user_id') && !empty(request()->input('user_id')) ? request()->input('user_id') : '');

        $transactionsQuery = DB::table('transactions')
            ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
            ->leftJoin('order_items', 'transactions.order_item_id', '=', 'order_items.id');

        if (request()->has('transaction_type')) {
            $transactionsQuery->where('transactions.transaction_type', request()->input('transaction_type'));
        }

        if (request()->has('search') && trim(request()->input('search')) !== '') {
            $transactionsQuery->where(function ($query) use ($search) {
                $query->where('transactions.id', 'LIKE', "%{$search}%")
                    ->orWhere('transactions.amount', 'LIKE', "%{$search}%")
                    ->orWhere('transactions.created_at', 'LIKE', "%{$search}%")
                    ->orWhere('users.username', 'LIKE', "%{$search}%")
                    ->orWhere('users.mobile', 'LIKE', "%{$search}%")
                    ->orWhere('users.email', 'LIKE', "%{$search}%")
                    ->orWhere('transactions.type', 'LIKE', "%{$search}%")
                    ->orWhere('transactions.status', 'LIKE', "%{$search}%")
                    ->orWhere('transactions.txn_id', 'LIKE', "%{$search}%");
            });
        }

        if (isset($user_id) && !empty($user_id)) {
            $transactionsQuery->where('users.id', $user_id);
        }

        if (request()->filled('user_type')) {
            $role_id = DB::table('roles')->where('name', request()->input('user_type'))->value('id');
        }


        if (request()->filled('start_date') && request()->filled('end_date')) {
            $transactionsQuery->whereDate('transactions.created_at', '>=', request()->input('start_date'))
                ->whereDate('transactions.created_at', '<=', request()->input('end_date'));
        }

        $totalQuery = clone $transactionsQuery;
        $total = $totalQuery->count();
        $txn_search_res = $transactionsQuery->select('transactions.*', 'users.username as name')
            ->orderBy($sort, $order)
            ->skip($offset)
            ->take($limit)
            ->get();

        $formattedTransactions = $txn_search_res->map(function ($row) {
            $operate = '';
            if ($row->type == 'bank_transfer') {
                $operate = ' <div class="d-flex align-items-center">
                    <a class="single_action_button edit_transaction" data-id="' . $row->id . '" data-txn_id="' . $row->txn_id . '" data-status="' . $row->status . '" data-message="' . $row->message . '" data-bs-target="#transaction_modal" data-bs-toggle="modal"><i class="bx bx-pencil mx-2"></i></a>
                </div>';
            }
            return [
                'id' => $row->id,
                'name' => $row->name,
                'type' => $row->type == 'bank_transfer' ? 'Bank Transfer' : $row->type,
                'order_id' => $row->order_id,
                'txn_id' => $row->txn_id,
                'payu_txn_id' => $row->payu_txn_id,
                'amount' => $row->amount,
                'status' => $row->status,
                'message' => $row->message,
                'created_at' => Carbon::parse($row->created_at)->format('d-m-Y'),
                'operate' => $operate,
            ];
        });

        return response()->json(['total' => $total, 'rows' => $formattedTransactions]);
    }

    public function seller_wallet_transactions_list($user_id = '')
    {
        $offset = request()->input('search') || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = request()->input('limit', 10);
        $sort = request()->input('sort', 'id');
        $order = request()->input('order', 'ASC');
        $user_id = isset($user_id) && !empty($user_id) ? $user_id : (request()->has('user_id') && !empty(request()->input('user_id')) ? request()->input('user_id') : '');

        // Get the store_id from session
        $store_id = getStoreId(); // Assuming store_id is stored in the session

        // Ensure store_id exists in the session, if not, return an empty response or error
        if (empty($store_id)) {
            return response()->json(['error' => 'Store ID is not found in the session'], 400);
        }

        $transactionsQuery = DB::table('transactions')
            ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
            ->leftJoin('order_items', 'transactions.order_item_id', '=', 'order_items.id')
            ->where('users.role_id', 4)
            ->where('order_items.store_id', $store_id); // Filter by store_id from session

        // Check for transaction type filter
        if (request()->has('transaction_type')) {
            $transactionsQuery->where('transactions.transaction_type', request()->input('transaction_type'));
        }

        // Check for search input
        if (request()->filled('search')) {
            $search = trim(request()->input('search'));
            $transactionsQuery->where(function ($query) use ($search) {
                $query->where('transactions.id', 'LIKE', '%' . $search . '%')
                    ->orWhere('transactions.amount', 'LIKE', '%' . $search . '%')
                    ->orWhere('transactions.txn_id', 'LIKE', '%' . $search . '%')
                    ->orWhere('transactions.type', 'LIKE', '%' . $search . '%')
                    ->orWhere('transactions.status', 'LIKE', '%' . $search . '%')
                    ->orWhere('transactions.message', 'LIKE', '%' . $search . '%')
                    ->orWhere('users.username', 'LIKE', '%' . $search . '%')
                    ->orWhere('users.mobile', 'LIKE', '%' . $search . '%')
                    ->orWhere('users.email', 'LIKE', '%' . $search . '%')
                    ->orWhereDate('transactions.created_at', '=', $search);
            });
        }

        // Filter by user ID if provided
        if (!empty($user_id)) {
            $transactionsQuery->where('users.id', $user_id);
        }

        // Clone query for total count
        $totalQuery = clone $transactionsQuery;
        $total = $totalQuery->count();

        // Date range filter
        if (request()->filled('start_date') && request()->filled('end_date')) {
            $transactionsQuery->whereDate('transactions.created_at', '>=', request()->input('start_date'))
                ->whereDate('transactions.created_at', '<=', request()->input('end_date'));
        }

        // Get transactions with pagination
        $txn_search_res = $transactionsQuery->select('transactions.*', 'users.username as name')
            ->orderBy($sort, $order)
            ->skip($offset)
            ->take($limit)
            ->get();

        // Format the transactions
        $formattedTransactions = $txn_search_res->map(function ($row) {
            $operate = '';
            if ($row->type == 'bank_transfer') {
                $operate = '<div class="dropdown bootstrap-table-dropdown">
                    <a href="#" class="text-dark" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="bx bx-dots-horizontal-rounded"></i>
                    </a>
                    <div class="dropdown-menu table_dropdown" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item dropdown_menu_items edit_transaction" data-id="' . $row->id . '" data-txn_id="' . $row->txn_id . '" data-status="' . $row->status . '" data-message="' . $row->message . '" data-bs-target="#transaction_modal" data-bs-toggle="modal"><i class="bx bx-pencil mx-2"></i>Edit</a>
                    </div>
                    </div>';
            }

            return [
                'id' => $row->id,
                'name' => $row->name,
                'type' => $row->type == 'bank_transfer' ? 'Bank Transfer' : $row->type,
                'order_id' => $row->order_id,
                'txn_id' => $row->txn_id,
                'payu_txn_id' => $row->payu_txn_id,
                'amount' => $row->amount,
                'status' => $row->status,
                'message' => $row->message,
                'created_at' => Carbon::parse($row->created_at)->format('d-m-Y'),
                'operate' => $operate,
            ];
        });

        return response()->json(['total' => $total, 'rows' => $formattedTransactions]);
    }

    public function delete_selected_data(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:seller_data,id' // Validate seller IDs
        ]);

        // Initialize arrays to track deletable and non-deletable seller IDs
        $nonDeletableIds = [];
        $deletedSellers = [];

        // Loop through each seller ID
        foreach ($request->ids as $sellerId) {
            // Get the seller based on the provided seller ID
            $seller = Seller::find($sellerId);

            if ($seller) {
                // Get the associated user for the seller
                $user = User::find($seller->user_id);

                if ($user) {
                    // Check if there are any associated products with the seller ID
                    $productsCount = Product::where('seller_id', $seller->id)->count();

                    if ($productsCount > 0) {
                        // If there are associated products, collect the seller ID
                        $nonDeletableIds[] = $seller->id;
                    } else {
                        // Delete the seller
                        if ($seller->delete()) {
                            $deletedSellers[] = $seller->id;
                        }
                    }
                }
            }
        }

        // Check if there are any non-deletable sellers
        if (!empty($nonDeletableIds)) {
            return response()->json([
                'error' => labels(
                    'admin_labels.cannot_delete_seller_associated_data',
                    'Cannot delete the following sellers: ' . implode(', ', $nonDeletableIds) . ' because they have associated products.'
                ),
                'non_deletable_ids' => $nonDeletableIds
            ], 401);
        }

        // If all sellers were deleted successfully, return success message
        return response()->json([
            'error' => false,
            'message' => labels('admin_labels.seller_deleted_successfully', 'Selected sellers deleted successfully!'),
            'deleted_ids' => $deletedSellers
        ]);
    }
    public function get_seller_deliverable_type(Request $request)
    {
        $store_id = getStoreId();
        $seller_id = isset($request->seller_id) ? $request->seller_id : "";
        // dd($seller_id);
        $deliverable_type = fetchDetails('seller_store', ['seller_id' => $seller_id, 'store_id' => $store_id], ['deliverable_type', 'deliverable_zones']);
        $deliverable_type = isset($deliverable_type) && !empty($deliverable_type) ? $deliverable_type[0] : [];
        // dd($deliverable_type);
        return response()->json(['deliverable_type' => $deliverable_type->deliverable_type]);
    }
}
