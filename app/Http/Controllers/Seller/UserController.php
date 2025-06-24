<?php

namespace App\Http\Controllers\Seller;

use App\Models\City;
use App\Models\Media;
use App\Models\Role;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\File;
use App\Models\Seller;
use App\Models\Zipcode;
use App\Models\Zone;
use Exception;

class UserController extends Controller
{

    public function edit($id)
    {
        $seller_data = User::find($id);
        $store_id = getStoreId();

        $store_data = DB::table('seller_store')
            ->leftJoin('seller_data', 'seller_data.id', '=', 'seller_store.seller_id')
            ->select(
                'seller_store.*',
                'seller_data.authorized_signature',
                'seller_data.national_identity_card',
                'seller_store.address_proof',
                'seller_data.pan_number',
                'zipcodes.zipcode as selected_zipcode',
                'cities.name as selected_city'
            )
            ->leftJoin('zipcodes', 'seller_store.zipcode', '=', 'zipcodes.id')
            ->leftJoin('cities', 'seller_store.city', '=', 'cities.id')
            ->where('seller_store.store_id', $store_id)
            ->where('seller_store.user_id', $id)
            ->get();


        $user = User::find($id);
        $zipcodes = Zipcode::orderBy('id', 'desc')->get();

        $cities = City::orderBy('id', 'desc')->get();
        $note_for_necessary_documents = fetchDetails('stores', ['id' => $store_id], 'note_for_necessary_documents');
        $note_for_necessary_documents = isset($note_for_necessary_documents) && $note_for_necessary_documents[0]->note_for_necessary_documents != null ? $note_for_necessary_documents[0]->note_for_necessary_documents : "Other Documents";
        return view('seller.pages.forms.account', compact('seller_data', 'store_data', 'store_id', 'zipcodes', 'cities', 'note_for_necessary_documents'));
    }

    public function update(Request $request, $id, $fromApp = false)
    {

        $seller_data = User::find($id);
        $seller_id = Seller::where('user_id', $id)->value('id');
        $user = User::find($id);

        if (!$seller_data) {
            return response()->json(['error' => true, 'message' => 'Seller not found'], 404);
        } else {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required',
                'address' => 'required',
                'store_name' => 'required',
                'account_number' => 'required',
                'account_name' => 'required',
                'bank_name' => 'required',
                'bank_code' => 'required',
            ]);
            if (!empty($request->input('old_password')) || !empty($request->input('new_password'))) {
                $validator = Validator::make($request->all(), [
                    'old_password' => 'required',
                    'password' => 'required',
                    'confirm_password' => 'required|same:password',
                ]);
            }

            if (!empty($request->input('old_password'))) {
                if (!Hash::check($request->old_password, $user->password)) {
                    if ($request->ajax()) {
                        return response()->json(['message' => labels('admin_labels.incorrect_old_password', 'The old password is incorrect.')], 422);
                    }
                }
            }
            if ($request->filled('new')) {
                $request['password'] = bcrypt($request->input('password'));
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

            $seller = Seller::find($seller_id);
            $store_id = getStoreId();
            $disk = $seller->disk; // Example disk (filesystem) from which you want to delete the file


            $media_storage_settings = fetchDetails('storage_types', ['is_default' => 1], '*');
            $current_disk = isset($media_storage_settings) && !empty($media_storage_settings) ? $media_storage_settings[0]->name : 'public';
            $imagePath = '';



            $user_data = [
                'role_id' => 4,
                'active' => 1,

                'address' => $request->address,
                'username' => $request->name,
                'mobile' => $request->mobile ?? $user->mobile,
                'email' => $request->email ?? $user->email,
                'image' => $imagePath,
            ];
            if ($request->filled('password')) {
                $user_data['password'] = $request->input('password');
            }
            $user = User::find($id);
            $user->update($user_data);

            $seller_data = [];
            $seller_store_data = [];


            $seller = Seller::find($seller_id);



            $seller_store_detail = DB::table('seller_store')
                ->where('seller_id', $seller_id)
                ->where('store_id', $store_id)->get();




            try {
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

                    if ($disk == 's3') {
                        $path = $request->edit_store_thumbnail;
                    } else {
                        $path = 'sellers/' . $seller_store_detail[0]->store_thumbnail; // Example path to the file you want to delete
                    }

                    // Call the removeFile method to delete the file
                    removeMediaFile($path, $disk);

                    $storeThumbnailFile = $request->file('store_thumbnail');

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

                    $mediaIds[] = $store_thumbnail->id;
                }


                if ($request->hasFile('authorized_signature')) {


                    if ($disk == 's3') {
                        $path = $request->edit_authorized_signature;
                    } else {
                        $path = 'sellers/' . $seller->authorized_signature; // Example path to the file you want to delete
                    }

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
            }

            if (isset($store_logo->file_name)) {
                $seller_store_data['logo'] = $current_disk == 's3' ? (isset($logo_url) ? $logo_url : '') : (isset($store_logo->file_name) ? '/' . $store_logo->file_name : '');
            } else {
                $seller_store_data['logo'] = $request->edit_store_logo;
            }
            $seller_store_data['other_documents'] = json_encode($all_other_documents);
            if (isset($store_thumbnail->file_name)) {
                $seller_store_data['store_thumbnail'] = $current_disk == 's3' ? (isset($store_thumbnail_url) ? $store_thumbnail_url : '') : (isset($store_thumbnail->file_name) ? '/' . $store_thumbnail->file_name : '');
            } else {
                $seller_store_data['store_thumbnail'] = $request->edit_store_thumbnail;
            }

            if (isset($authorized_signature->file_name)) {
                $seller_data['authorized_signature'] = $current_disk == 's3' ? (isset($authorized_signature_url) ? $authorized_signature_url : '') : (isset($authorized_signature->file_name) ? '/' . $authorized_signature->file_name : '');
            } else {
                $seller_data['authorized_signature'] = $request->edit_authorized_signature;
            }

            if (isset($national_identity_card->file_name)) {
                $seller_data['national_identity_card'] = $current_disk == 's3' ? (isset($national_identity_card_url) ? $national_identity_card_url : '') : (isset($national_identity_card->file_name) ? '/' . $national_identity_card->file_name : '');
            } else {
                $seller_data['national_identity_card'] = $request->edit_national_identity_card;
            }

            $seller_data = array_merge($seller_data, [
                'status' => 1,
                'pan_number' => $request->pan_number,
            ]);



            $seller->update($seller_data);

            $updated_seller = Seller::where('user_id', $id)->first();

            $new_name = $request->store_name;
            $current_name = $seller_store_detail[0]->store_name;
            $current_slug = $seller_store_detail[0]->slug;

            $seller_store_data = array_merge($seller_store_data, [
                'store_name' => $request->store_name,
                'store_url' => $request->store_url,
                'store_description' => $request->description,
                'commission' => $request->global_commission ?? 0,
                'account_number' => $request->account_number,
                'account_name' => $request->account_name,
                'bank_name' => $request->bank_name,
                'bank_code' => $request->bank_code,
                'status' => 1,
                'tax_name' => $request->tax_name,
                'tax_number' => $request->tax_number,
                'slug' => generateSlug($new_name, 'seller_store', 'slug', $current_slug, $current_name),
                'store_id' => $store_id,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'city' => $request->city ?? "",
                'zipcode' => $request->zipcode ?? "",
            ]);

            $seller_store = DB::table('seller_store')
                ->where('seller_id', $seller_id)
                ->where('store_id', $store_id)
                ->update($seller_store_data);


            if ($request->ajax()) {
                return response()->json(['message' => labels('admin_labels.profile_details_updated_successfully', 'Profile details updated successfully!')]);
            }
        }
    }
    public function seller_zones_data(Request $request)
    {
        $store_id = getStoreId();
        $search = trim($request->input('search'));
        $user = Auth::user();
        $seller_id = Seller::where('user_id', $user->id)->value('id');
        $limit = (int) $request->input('limit', 50);
        $seller_zones = fetchDetails('seller_store', ['seller_id' => $seller_id, 'store_id' => $store_id], ['deliverable_type', 'deliverable_zones']);
        $seller_zones = isset($seller_zones) && !empty($seller_zones) ? $seller_zones[0] : [];

        $query = Zone::where('status', 1)
            ->where(function ($query) use ($search) {
                $query->where('id', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%');
            });

        if ($seller_zones->deliverable_type == '2' || $seller_zones->deliverable_type == '3') {
            $zone_ids = explode(',', $seller_zones->deliverable_zones);
            // dd($zone_ids);
            $query->whereIn('id', $zone_ids);
        }

        $zones = $query->limit($limit)->get(['id', 'name', 'serviceable_city_ids', 'serviceable_zipcode_ids']);
        // dd($zones);
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
            // dd('here'),
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
