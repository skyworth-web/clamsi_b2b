<?php

namespace App\Http\Controllers\Admin;

use App\Models\Language;
use App\Models\Media;
use App\Models\StorageType;
use App\Models\Store;

use Exception;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class StoreController extends Controller
{
    public function index()
    {
        $languages = Language::all();
        return view('admin.pages.forms.stores', ['languages' => $languages]);
    }

    public function webProductCardStyle()
    {
        return view('webProductCardStyle');
    }
    public function webCategoriesStyle()
    {
        return view('webCategoriesStyle');
    }
    public function webBrandsStyle()
    {
        return view('webBrandsStyle');
    }
    public function webWishlistStyle()
    {
        return view('webWishlistStyle');
    }
    public function webHomePageTheme()
    {
        return view('webHomePageTheme');
    }

    public function manage_store()
    {
        return view('admin.pages.tables.manage_stores');
    }

    public function store(Request $request)
    {
        // dd($request);
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
            'image' => 'required',
            'banner_image' => 'required',
            'primary_color' => ['required', 'regex:/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'secondary_color' => ['required', 'regex:/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'hover_color' => ['required', 'regex:/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'active_color' => ['required', 'regex:/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'background_color' => ['required', 'regex:/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'banner_image_for_most_selling_product' => 'required',
            'stack_image' => 'required',
            'login_image' => 'required',
            'half_store_logo' => 'required',
            'store_style' => 'required',
            'product_style' => 'required',
            'category_section_title' => 'required',
            'category_style' => 'required',
            'category_card_style' => 'required',
            'brand_style' => 'required',
            'offer_slider_style' => 'required',
            'delivery_charge_type_value' => 'required',
            'product_deliverability_type_value' => 'required',
        ], [
            'delivery_charge_type_value.required' => 'select any one delivery charge type.',
            'product_deliverability_type_value.required' => 'select any one product deliverability type.',
            'primary_color.regex' => 'The primary theme color must be a valid hexadecimal code (e.g., #FFF or #FFFFFF).',
            'secondary_color.regex' => 'The secondary theme color must be a valid hexadecimal code (e.g., #FFF or #FFFFFF).',
            'hover_color.regex' => 'The link hover color must be a valid hexadecimal code (e.g., #FFF or #FFFFFF).',
            'active_color.regex' => 'The link active color must be a valid hexadecimal code (e.g., #FFF or #FFFFFF).',
            'background_color.regex' => 'The background color must be a valid hexadecimal code (e.g., #FFF or #FFFFFF).',

        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ($request->ajax()) ? response()->json(['errors' => $errors->all()], 422) : redirect()->back()->withErrors($errors)->withInput();
        }

        // --------------------------------------- Code For Upload Image ------------------------------------


        $store = new Store();
        $storeImgPath = base_path(config('constants.STORE_IMG_PATH'));

        if (!File::exists($storeImgPath)) {
            File::makeDirectory($storeImgPath, 0755, true);
        }

        $media_storage_settings = fetchDetails('storage_types', ['is_default' => 1], '*');
        $mediaStorageType = isset($media_storage_settings) && !empty($media_storage_settings) ? $media_storage_settings[0]->id : 1;
        $disk = isset($media_storage_settings) && !empty($media_storage_settings) ? $media_storage_settings[0]->name : 'public';

        $media = StorageType::find($mediaStorageType);

        $image_path = $banner_image = $banner_image_for_most_selling_product = $stack_image = $login_image = $half_store_logo = [];

        try {
            if ($request->hasFile('image')) {

                $imageFile = $request->file('image');

                $image_path = $media->addMedia($imageFile)
                    ->sanitizingFileName(function ($fileName) use ($media) {
                        // Replace special characters and spaces with hyphens
                        $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                        // Generate a unique identifier based on timestamp and random component
                        $uniqueId = time() . '_' . mt_rand(1000, 9999);
                        $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                        $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                        return "{$baseName}-{$uniqueId}.{$extension}";
                    })
                    ->toMediaCollection('store_images', $disk);

                $mediaIds[] = $image_path->id;
            }

            if ($request->hasFile('banner_image')) {

                $bannerFile = $request->file('banner_image');

                $banner_image = $media->addMedia($bannerFile)
                    ->sanitizingFileName(function ($fileName) use ($media) {
                        // Replace special characters and spaces with hyphens
                        $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                        // Generate a unique identifier based on timestamp and random component
                        $uniqueId = time() . '_' . mt_rand(1000, 9999);
                        $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                        $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                        return "{$baseName}-{$uniqueId}.{$extension}";
                    })
                    ->toMediaCollection('store_images', $disk);

                $mediaIds[] = $banner_image->id;
            }


            if ($request->hasFile('banner_image_for_most_selling_product')) {

                $banner_image_for_most_selling_product_file = $request->file('banner_image_for_most_selling_product');

                $banner_image_for_most_selling_product = $media->addMedia($banner_image_for_most_selling_product_file)
                    ->sanitizingFileName(function ($fileName) use ($media) {
                        // Replace special characters and spaces with hyphens
                        $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                        // Generate a unique identifier based on timestamp and random component
                        $uniqueId = time() . '_' . mt_rand(1000, 9999);
                        $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                        $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                        return "{$baseName}-{$uniqueId}.{$extension}";
                    })
                    ->toMediaCollection('store_images', $disk);

                $mediaIds[] = $banner_image_for_most_selling_product->id;
            }

            if ($request->hasFile('stack_image')) {

                $stack_image_file = $request->file('stack_image');

                $stack_image = $media->addMedia($stack_image_file)
                    ->sanitizingFileName(function ($fileName) use ($media) {
                        // Replace special characters and spaces with hyphens
                        $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                        // Generate a unique identifier based on timestamp and random component
                        $uniqueId = time() . '_' . mt_rand(1000, 9999);
                        $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                        $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                        return "{$baseName}-{$uniqueId}.{$extension}";
                    })
                    ->toMediaCollection('store_images', $disk);

                $mediaIds[] = $stack_image->id;
            }

            if ($request->hasFile('login_image')) {

                $login_image_file = $request->file('login_image');

                $login_image = $media->addMedia($login_image_file)
                    ->sanitizingFileName(function ($fileName) use ($media) {
                        // Replace special characters and spaces with hyphens
                        $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                        // Generate a unique identifier based on timestamp and random component
                        $uniqueId = time() . '_' . mt_rand(1000, 9999);
                        $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                        $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                        return "{$baseName}-{$uniqueId}.{$extension}";
                    })
                    ->toMediaCollection('store_images', $disk);

                $mediaIds[] = $login_image->id;
            }
            if ($request->hasFile('half_store_logo')) {

                $half_store_logo_file = $request->file('half_store_logo');

                $half_store_logo = $media->addMedia($half_store_logo_file)
                    ->sanitizingFileName(function ($fileName) use ($media) {
                        // Replace special characters and spaces with hyphens
                        $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                        // Generate a unique identifier based on timestamp and random component
                        $uniqueId = time() . '_' . mt_rand(1000, 9999);
                        $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                        $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                        return "{$baseName}-{$uniqueId}.{$extension}";
                    })
                    ->toMediaCollection('store_images', $disk);

                $mediaIds[] = $half_store_logo->id;
            }


            //code for storing s3 object url for media

            if ($disk == 's3') {
                $media_list = $media->getMedia('store_images'); /* ["key" => "value"] */
                for ($i = 0; $i < count($mediaIds); $i++) {
                    $media_url = $media_list[($media_list->count()) - (count($mediaIds) - $i)]->getUrl();
                    switch ($i) {
                        case 0:
                            $image_url = $media_url;
                            break;
                        case 1:
                            $banner_image_url = $media_url;
                            break;
                        case 2:
                            $banner_image_for_most_selling_product_url = $media_url;
                            break;
                        case 3:
                            $stack_image_url = $media_url;
                            break;
                        case 4:
                            $login_image_url = $media_url;
                            break;
                        case 5:
                            $half_store_logo_url = $media_url;
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

        $settings_data = $request->only([
            "store_style",
            "product_style",
            "category_section_title",
            "category_style",
            "category_card_style",
            "brand_style",
            "offer_slider_style",
            "web_home_page_theme",
            "products_display_style_for_web",
            "categories_display_style_for_web",
            "brands_display_style_for_web",
            "wishlist_display_style_for_web",
            "web_product_details_style"
        ]);

        $settings_data = json_encode($settings_data);
        $translations = [
            'en' => $request->name
        ];
        if (!empty($request['translated_store_name'])) {
            $translations = array_merge($translations, $request['translated_store_name']);
        }
        $translation_descriptions = [
            'en' => $request->description
        ];
        if (!empty($request['translated_store_description'])) {
            $translation_descriptions = array_merge($translations, $request['translated_store_description']);
        }

        $store->name = json_encode($translations, JSON_UNESCAPED_UNICODE);
        $store->description = json_encode($translation_descriptions, JSON_UNESCAPED_UNICODE);
        $store->slug = generateSlug($translations['en'], 'stores');

        $store->image = $disk == 's3' ? (isset($image_url) ? $image_url : '') : (isset($image_path->file_name) ? '/' . $image_path->file_name : '');

        $store->banner_image = $disk == 's3' ? (isset($banner_image_url) ? $banner_image_url : '') : (isset($banner_image->file_name) ? '/' . $banner_image->file_name : '');

        $store->banner_image_for_most_selling_product = $disk == 's3' ? (isset($banner_image_for_most_selling_product_url) ? $banner_image_for_most_selling_product_url : '') : (isset($banner_image_for_most_selling_product->file_name) ? '/' . $banner_image_for_most_selling_product->file_name : '');

        $store->stack_image = $disk == 's3' ? (isset($stack_image_url) ? $stack_image_url : '') : (isset($stack_image->file_name) ? '/' . $stack_image->file_name : '');

        $store->login_image = $disk == 's3' ? (isset($login_image_url) ? $login_image_url : '') : (isset($login_image->file_name) ? '/' . $login_image->file_name : '');

        $store->half_store_logo = $disk == 's3' ? (isset($half_store_logo_url) ? $half_store_logo_url : '') : (isset($half_store_logo->file_name) ? '/' . $half_store_logo->file_name : '');

        $store->status = 1;
        $store->is_single_seller_order_system = isset($request->is_single_seller_order_system) && $request->is_single_seller_order_system == "on" ? 1 : 0;
        if (isset($request->is_default_store) && $request->is_default_store == "on") {
            // Set all other store records' 'is_default_store' value to '0'
            Store::query()->update(['is_default_store' => 0]);

            // Set the current store's 'is_default_store' value to '1'
            $store->is_default_store = 1;
        } else {
            // If 'is_default_store' parameter is not set to '1', set the value based on the request
            $store->is_default_store = isset($request->is_default_store) && $request->is_default_store == "on" ? 1 : 0;
        }
        $store->primary_color = isset($request->primary_color) && !empty($request->primary_color) ? $request->primary_color : '';
        $store->note_for_necessary_documents = isset($request->note_for_necessary_documents) && !empty($request->note_for_necessary_documents) ? $request->note_for_necessary_documents : '';
        $store->secondary_color = isset($request->secondary_color) && !empty($request->secondary_color) ? $request->secondary_color : '';
        $store->active_color = isset($request->active_color) && !empty($request->active_color) ? $request->active_color : '';
        $store->background_color = isset($request->background_color) && !empty($request->background_color) ? $request->background_color : '';
        $store->hover_color = isset($request->hover_color) && !empty($request->hover_color) ? $request->hover_color : '';
        $store->store_settings = isset($settings_data) && !empty($settings_data) ? $settings_data : '';
        $store->disk = isset($image_path->disk) && !empty($image_path->disk) ? $image_path->disk : 'public';
        $store->delivery_charge_type = isset($request->delivery_charge_type_value) && !empty($request->delivery_charge_type_value) ? $request->delivery_charge_type_value : '';
        $store->delivery_charge_amount = isset($request->delivery_charge_amount) && !empty($request->delivery_charge_amount) ? $request->delivery_charge_amount : 0;
        $store->minimum_free_delivery_amount = isset($request->minimum_free_delivery_amount) && !empty($request->minimum_free_delivery_amount) ? $request->minimum_free_delivery_amount : 0;
        $store->product_deliverability_type = isset($request->product_deliverability_type_value) && !empty($request->product_deliverability_type_value) ? $request->product_deliverability_type_value : '';


        $store->save();
        if ($request->ajax()) {
            return response()->json([
                'message' => labels('admin_labels.store_added_successfully', 'Store added successfully'),
                'location' => route('admin.stores.manage_store')
            ]);
        }
    }

    public function list(Request $request)
    {
        $search = trim($request->input('search'));
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'DESC');
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = $request->input('limit', 10);
        $status = $request->input('status', '');
        // dd($status);
        $store_data = Store::query()
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->when($status !== '' && !is_null($status), function ($query) use ($status) {
                $query->where('status', $status);
            });

        $total = $store_data->count();

        $stores = $store_data->orderBy($sort, $order)
            ->skip($offset)
            ->take($limit)
            ->get();

        $data = $stores->map(function ($s) {
            $language_code = get_language_code();
            $edit_url = route('admin.store.update', $s->id);
            $action = '<div class="d-flex align-items-center ">
                <a href="' . $edit_url . '" class="btn text-dark single_action_button" aria-label="Edit">
                    <i class="bx bx-pencil mx-2"></i>
                </a>
            </div>';

            $image_src = route('admin.dynamic_image', [
                'url' => getMediaImageUrl($s->image, 'STORE_IMG_PATH'),
                'width' => 60,
                'quality' => 90
            ]);

            $banner_src = route('admin.dynamic_image', [
                'url' => getMediaImageUrl($s->banner_image, 'STORE_IMG_PATH'),
                'width' => 60,
                'quality' => 90
            ]);

            return [
                'id' => $s->id,
                'name' => getDynamicTranslation('stores', 'name', $s->id, $language_code),
                'is_default_store' => $s->is_default_store == 1
                    ? '<a class="form-switch set_default_store" data-id=' . $s->id . ' data-store-status=' . $s->is_default_store . ' data-url="/admin/store/set_default_store/' . $s->id . '">
                          <input class="form-check-input" type="checkbox" role="switch" checked></a>'
                    : '<a class="form-switch set_default_store" data-id=' . $s->id . ' data-store-status=' . $s->is_default_store . ' data-url="/admin/store/set_default_store/' . $s->id . '">
                          <input class="form-check-input" type="checkbox" role="switch"></a>',
                'image' => '<div>
                                <a href="' . getMediaImageUrl($s->image, 'STORE_IMG_PATH') . '" data-lightbox="image-' . $s->id . '">
                                    <img src="' . $image_src . '" alt="Avatar" class="rounded" />
                                </a>
                            </div>',
                'banner' => '<div>
                                <a href="' . getMediaImageUrl($s->banner_image, 'STORE_IMG_PATH') . '" data-lightbox="banner-' . $s->id . '">
                                    <img src="' . $banner_src . '" alt="Banner" class="rounded" />
                                </a>
                            </div>',
                'status' => '<select class="form-select status_dropdown change_toggle_status ' . ($s->status == 1 ? 'active_status' : 'inactive_status') . '" data-id="' . $s->id . '" data-url="/admin/store/update_status/' . $s->id . '" aria-label="">
                      <option value="1" ' . ($s->status == 1 ? 'selected' : '') . '>Active</option>
                      <option value="0" ' . ($s->status == 0 ? 'selected' : '') . '>Deactive</option>
                  </select>',
                'operate' => $action,
            ];
        });

        return response()->json([
            "rows" => $data,
            "total" => $total,
        ]);
    }


    public function update_status($id)
    {
        $store = Store::findOrFail($id);

        if ($store->is_default_store == 1) {
            return response()->json([
                'status_error' => labels('admin_labels.cannot_disable_default_store', 'You cannot disable the default store. Please set another store as default before disabling this.')
            ]);
        } else {
            try {
                $sellerStoreCount = DB::table('seller_store')->where('store_id', $id)->count();

                if ($store->status == '1' && $sellerStoreCount > 0) {
                    return response()->json([
                        'status_error' => labels('admin_labels.cannot_disable_store_connected_to_sellers', 'You cannot disable this store because it is connected to sellers.')
                    ]);
                }

                $store->status = $store->status == '1' ? '0' : '1';
                $store->save();

                return response()->json([
                    'success' => labels('admin_labels.status_updated_successfully', 'Status updated successfully.')
                ]);
            } catch (\Exception $e) {
                // Handle any database-related errors
                return response()->json(['error' => labels('admin_labels.something_went_wrong', 'Something went wrong')]);
            }
        }
    }

    public function edit($id)
    {

        $data = Store::find($id);
        $languages = Language::all();
        return view('admin.pages.forms.update_store', [
            'data' => $data,
            'languages' => $languages
        ]);
    }

    public function update(Request $request, $data)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
            'image' => 'required',
            'banner_image' => 'required',
            'delivery_charge_type_value' => 'required',
            'product_deliverability_type_value' => 'required',
            'primary_color' => ['regex:/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'secondary_color' => ['regex:/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'hover_color' => ['regex:/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'active_color' => ['regex:/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'background_color' => ['regex:/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
        ], [
            'delivery_charge_type_value.required' => 'select any one delivery charge type.',
            'product_deliverability_type_value.required' => 'select any one product deliverability type.',
            'primary_color.regex' => 'The primary theme color must be a valid hexadecimal code (e.g., #FFF or #FFFFFF).',
            'secondary_color.regex' => 'The secondary theme color must be a valid hexadecimal code (e.g., #FFF or #FFFFFF).',
            'hover_color.regex' => 'The link hover color must be a valid hexadecimal code (e.g., #FFF or #FFFFFF).',
            'active_color.regex' => 'The link active color must be a valid hexadecimal code (e.g., #FFF or #FFFFFF).',
            'background_color.regex' => 'The background color must be a valid hexadecimal code (e.g., #FFF or #FFFFFF).',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();

            if ($request->ajax()) {
                return response()->json(['errors' => $errors->all()], 422);
            }
            return redirect()->back()->withErrors($errors)->withInput();
        }

        $store = Store::find($data);

        $disk = $store->disk; // Example disk (filesystem) from which you want to delete the file

        $image_path = [];
        $banner_image = [];
        $banner_image_for_most_selling_product = [];
        $stack_image = [];
        $login_image = [];
        $half_store_logo = [];
        $mediaIds = [];

        $media_storage_settings = fetchDetails('storage_types', ['is_default' => 1], '*');
        $current_disk = isset($media_storage_settings) && !empty($media_storage_settings) ? $media_storage_settings[0]->name : 'public';

        try {
            if ($request->hasFile('update_image')) {

                // Specify the path and disk from which you want to delete the file
                if ($disk == 's3') {
                    $path = $request->input('image');
                } else {
                    $path = 'store_images/' . $request->input('image'); // Example path to the file you want to delete
                }

                // Call the removeFile method to delete the file
                removeMediaFile($path, $disk);

                $imageFile = $request->file('update_image');

                $image_path = $store->addMedia($imageFile)
                    ->sanitizingFileName(function ($fileName) use ($store) {
                        // Replace special characters and spaces with hyphens
                        $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                        // Generate a unique identifier based on timestamp and random component
                        $uniqueId = time() . '_' . mt_rand(1000, 9999);
                        $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                        $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                        return "{$baseName}-{$uniqueId}.{$extension}";
                    })
                    ->toMediaCollection('store_images', $current_disk);

                $mediaIds[] = $image_path->id;
            }

            if ($request->hasFile('update_banner_image')) {

                // Specify the path and disk from which you want to delete the file
                if ($disk == 's3') {
                    $path = $request->input('banner_image');
                } else {
                    $path = 'store_images/' . $request->input('banner_image'); // Example path to the file you want to delete
                }

                // Call the removeFile method to delete the file
                removeMediaFile($path, $disk);

                $bannerFile = $request->file('update_banner_image');

                $banner_image = $store->addMedia($bannerFile)
                    ->sanitizingFileName(function ($fileName) use ($store) {
                        // Replace special characters and spaces with hyphens
                        $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                        // Generate a unique identifier based on timestamp and random component
                        $uniqueId = time() . '_' . mt_rand(1000, 9999);
                        $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                        $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                        return "{$baseName}-{$uniqueId}.{$extension}";
                    })
                    ->toMediaCollection('store_images', $current_disk);

                $mediaIds[] = $banner_image->id;
            }


            if ($request->hasFile('update_banner_image_for_most_selling_product')) {

                // Specify the path and disk from which you want to delete the file
                if ($disk == 's3') {
                    $path = $request->input('banner_image_for_most_selling_product');
                } else {
                    $path = 'store_images/' . $request->input('banner_image_for_most_selling_product'); // Example path to the file you want to delete
                }

                // Call the removeFile method to delete the file
                removeMediaFile($path, $disk);

                $banner_image_for_most_selling_product_file = $request->file('update_banner_image_for_most_selling_product');

                $banner_image_for_most_selling_product = $store->addMedia($banner_image_for_most_selling_product_file)
                    ->sanitizingFileName(function ($fileName) use ($store) {
                        // Replace special characters and spaces with hyphens
                        $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                        // Generate a unique identifier based on timestamp and random component
                        $uniqueId = time() . '_' . mt_rand(1000, 9999);
                        $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                        $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                        return "{$baseName}-{$uniqueId}.{$extension}";
                    })
                    ->toMediaCollection('store_images', $current_disk);

                $mediaIds[] = $banner_image_for_most_selling_product->id;
            }

            if ($request->hasFile('update_stack_image')) {

                // Specify the path and disk from which you want to delete the file
                if ($disk == 's3') {
                    $path = $request->input('stack_image');
                } else {
                    $path = 'store_images/' . $request->input('stack_image'); // Example path to the file you want to delete
                }

                // Call the removeFile method to delete the file
                removeMediaFile($path, $disk);

                $stack_image_file = $request->file('update_stack_image');

                $stack_image = $store->addMedia($stack_image_file)
                    ->sanitizingFileName(function ($fileName) use ($store) {
                        // Replace special characters and spaces with hyphens
                        $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                        // Generate a unique identifier based on timestamp and random component
                        $uniqueId = time() . '_' . mt_rand(1000, 9999);
                        $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                        $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                        return "{$baseName}-{$uniqueId}.{$extension}";
                    })
                    ->toMediaCollection('store_images', $current_disk);

                $mediaIds[] = $stack_image->id;
            }

            if ($request->hasFile('update_login_image')) {

                // Specify the path and disk from which you want to delete the file
                if ($disk == 's3') {
                    $path = $request->input('login_image');
                } else {
                    $path = 'store_images/' . $request->input('login_image'); // Example path to the file you want to delete
                }

                // Call the removeFile method to delete the file
                removeMediaFile($path, $disk);

                $login_image_file = $request->file('update_login_image');

                $login_image = $store->addMedia($login_image_file)
                    ->sanitizingFileName(function ($fileName) use ($store) {
                        // Replace special characters and spaces with hyphens
                        $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                        // Generate a unique identifier based on timestamp and random component
                        $uniqueId = time() . '_' . mt_rand(1000, 9999);
                        $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                        $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                        return "{$baseName}-{$uniqueId}.{$extension}";
                    })
                    ->toMediaCollection('store_images', $current_disk);

                $mediaIds[] = $login_image->id;
            }

            if ($request->hasFile('update_half_store_logo')) {

                // Specify the path and disk from which you want to delete the file
                if ($disk == 's3') {
                    $path = $request->input('half_store_logo');
                } else {
                    $path = 'store_images/' . $request->input('half_store_logo'); // Example path to the file you want to delete
                }

                // Call the removeFile method to delete the file
                removeMediaFile($path, $disk);

                $half_store_logo_file = $request->file('update_half_store_logo');

                $half_store_logo = $store->addMedia($half_store_logo_file)
                    ->sanitizingFileName(function ($fileName) use ($store) {
                        // Replace special characters and spaces with hyphens
                        $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                        // Generate a unique identifier based on timestamp and random component
                        $uniqueId = time() . '_' . mt_rand(1000, 9999);
                        $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                        $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                        return "{$baseName}-{$uniqueId}.{$extension}";
                    })
                    ->toMediaCollection('store_images', $current_disk);

                $mediaIds[] = $half_store_logo->id;
            }

            //code for storing s3 object url for media

            if ($current_disk == 's3') {
                $media_list = $store->getMedia('store_images');
                for ($i = 0; $i < count($mediaIds); $i++) {
                    $media_url = $media_list[($media_list->count()) - (count($mediaIds) - $i)]->getUrl();

                    $fileName = implode('/', array_slice(explode('/', $media_url), -1));

                    if (isset($image_path->file_name) && $fileName == $image_path->file_name) {
                        $image_url = $media_url;
                    }
                    if (isset($banner_image->file_name) && $fileName == $banner_image->file_name) {
                        $banner_image_url = $media_url;
                    }
                    if (isset($banner_image_for_most_selling_product->file_name) && $fileName == $banner_image_for_most_selling_product->file_name) {
                        $banner_image_for_most_selling_product_url = $media_url;
                    }
                    if (isset($stack_image->file_name) && $fileName == $stack_image->file_name) {
                        $stack_image_url = $media_url;
                    }
                    if (isset($login_image->file_name) && $fileName == $login_image->file_name) {
                        $login_image_url = $media_url;
                    }
                    if (isset($half_store_logo->file_name) && $fileName == $half_store_logo->file_name) {
                        $half_store_logo_url = $media_url;
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



        if (isset($image_path->file_name)) {
            $storeData['image'] = $current_disk == 's3' ? (isset($image_url) ? $image_url : '') : (isset($image_path->file_name) ? '/' . $image_path->file_name : '');
        } else {
            $storeData['image'] = $request->input('image', '');
        }

        if (isset($banner_image->file_name)) {
            $storeData['banner_image'] = $current_disk == 's3' ? (isset($banner_image_url) ? $banner_image_url : '') : (isset($banner_image->file_name) ? '/' . $banner_image->file_name : '');
        } else {
            $storeData['banner_image'] = $request->input('banner_image', '');
        }

        if (isset($banner_image_for_most_selling_product->file_name)) {
            $storeData['banner_image_for_most_selling_product'] = $current_disk == 's3' ? (isset($banner_image_for_most_selling_product_url) ? $banner_image_for_most_selling_product_url : '') : (isset($banner_image_for_most_selling_product->file_name) ? '/' . $banner_image_for_most_selling_product->file_name : '');
        } else {
            $storeData['banner_image_for_most_selling_product'] = $request->input('banner_image_for_most_selling_product', '');
        }

        if (isset($stack_image->file_name)) {
            $storeData['stack_image'] = $current_disk == 's3' ? (isset($stack_image_url) ? $stack_image_url : '') : (isset($stack_image->file_name) ? '/' . $stack_image->file_name : '');
        } else {
            $storeData['stack_image'] = $request->input('stack_image', '');
        }

        if (isset($login_image->file_name)) {
            $storeData['login_image'] = $current_disk == 's3' ? (isset($login_image_url) ? $login_image_url : '') : (isset($login_image->file_name) ? '/' . $login_image->file_name : '');
        } else {
            $storeData['login_image'] = $request->input('login_image', '');
        }

        if (isset($half_store_logo->file_name)) {
            $storeData['half_store_logo'] = $current_disk == 's3' ? (isset($half_store_logo_url) ? $half_store_logo_url : '') : (isset($half_store_logo->file_name) ? '/' . $half_store_logo->file_name : '');
        } else {
            $storeData['half_store_logo'] = $request->input('half_store_logo', '');
        }

        $settings_data = $request->only([
            "store_style",
            "product_style",
            "category_section_title",
            "category_style",
            "category_card_style",
            "brand_style",
            "offer_slider_style",
            "web_home_page_theme",
            "products_display_style_for_web",
            "categories_display_style_for_web",
            "brands_display_style_for_web",
            "wishlist_display_style_for_web",
            "web_product_details_style"
        ]);

        $settings_data = json_encode($settings_data);
        $new_name = $request->name;
        $current_name = json_decode($store->name, true)['en'] ?? $store->name;
        $current_slug = $store->slug;

        $existingTranslations = json_decode($store->name, true) ?? [];
        $existingDescriptionTranslations = json_decode($store->description, true) ?? [];

        $existingTranslations['en'] = $request->name;
        $existingDescriptionTranslations['en'] = $request->description;

        if (!empty($request->translated_store_name)) {
            $existingTranslations = array_merge($existingTranslations, $request->translated_store_name);
        }
        if (!empty($request->translated_store_description)) {
            $existingDescriptionTranslations = array_merge($existingDescriptionTranslations, $request->translated_store_description);
        }

        $storeData['name'] = json_encode($existingTranslations, JSON_UNESCAPED_UNICODE);
        $storeData['description'] = json_encode($existingDescriptionTranslations, JSON_UNESCAPED_UNICODE);

        $storeData['slug'] = generateSlug($new_name, 'stores', 'slug', $current_slug, $current_name);

        $storeData['is_single_seller_order_system'] = isset($request->is_single_seller_order_system) && $request->is_single_seller_order_system == "on" ? 1 : 0;
        $storeData['store_settings'] = isset($settings_data) && !empty($settings_data) ? $settings_data : '';

        $storeData['primary_color'] = isset($request->primary_color) && !empty($request->primary_color) ? $request->primary_color : '';
        $storeData['note_for_necessary_documents'] = isset($request->note_for_necessary_documents) && !empty($request->note_for_necessary_documents) ? $request->note_for_necessary_documents : '';
        $storeData['secondary_color'] = isset($request->secondary_color) && !empty($request->secondary_color) ? $request->secondary_color : '';
        $storeData['active_color'] = isset($request->active_color) && !empty($request->active_color) ? $request->active_color : '';
        $storeData['background_color'] = isset($request->background_color) && !empty($request->background_color) ? $request->background_color : '';

        $storeData['hover_color'] = isset($request->hover_color) && !empty($request->hover_color) ? $request->hover_color : '';
        $storeData['disk'] = isset($image_path->disk) && !empty($image_path->disk) ? $image_path->disk : $disk;

        $storeData['delivery_charge_type'] = isset($request->delivery_charge_type_value) && !empty($request->delivery_charge_type_value) ? $request->delivery_charge_type_value : '';
        $storeData['delivery_charge_amount'] = isset($request->delivery_charge_amount) && !empty($request->delivery_charge_amount) ? $request->delivery_charge_amount : 0;
        $storeData['minimum_free_delivery_amount'] = isset($request->minimum_free_delivery_amount) && !empty($request->minimum_free_delivery_amount) ? $request->minimum_free_delivery_amount : 0;
        $storeData['product_deliverability_type'] = isset($request->product_deliverability_type_value) && !empty($request->product_deliverability_type_value) ? $request->product_deliverability_type_value : '';


        $store->update($storeData);

        if ($request->ajax()) {
            return response()->json([
                'message' => labels('admin_labels.store_updated_successfully', 'Store updated successfully'),
                'location' => route('admin.stores.manage_store')
            ]);
        }
    }


    public function get_stores_list(Request $request)
    {
        $store_id = getStoreId();

        $search = trim($request->input('search'));
        $limit = (int) $request->input('limit', 10);

        $stores = Store::where('name', 'like', '%' . $search . '%')
            ->where('id', '<>', $store_id)
            ->limit($limit)
            ->get(['id', 'name']);
        $totalCount = Store::where('name', 'like', '%' . $search . '%')->where('id', '<>', $store_id)->count();

        $response = [
            'total' => $totalCount,
            'results' => $stores->map(function ($store) {
                $language_code = get_language_code();
                return [
                    'id' => $store->id,
                    'text' => getDynamicTranslation('stores', 'name', $store->id, $language_code),
                ];
            }),
        ];

        return response()->json($response);
    }


    // public function set_default_store($id)
    // {
    //     $store = Store::find($id);
    //     if (!$store) {
    //         return response()->json([
    //             'error' => true,
    //             'message' => labels('admin_labels.store_not_found', 'Store not found'),
    //         ]);
    //     }
    //     if ($store->status == 0) {
    //         return response()->json([
    //             'error' => true,
    //             'error_message' => labels('admin_labels.deactivate_store_can_not_be_Set_as_default', 'Deactivated store cannot be set as default'),
    //         ]);
    //     }
    //     Store::query()->update(['is_default_store' => 0]);

    //     $store->is_default_store = 1;
    //     $store->save();

    //     return response()->json([
    //         'error' => false,
    //         'message' => labels('admin_labels.store_set_as_default', 'Store has been set as default'),
    //     ]);
    // }
    public function set_default_store($id)
    {
        $store = Store::find($id);

        // Check if store exists
        if (!$store) {
            return response()->json([
                'error' => true,
                'message' => labels('admin_labels.store_not_found', 'Store not found'),
            ]);
        }

        // Check if the store is deactivated
        if ($store->status == 0) {
            return response()->json([
                'error' => true,
                'error_message' => labels('admin_labels.deactivate_store_can_not_be_Set_as_default', 'Deactivated store cannot be set as default'),
            ]);
        }

        // Check if at least one store is set as default
        $defaultStore = Store::where('is_default_store', 1)->first();

        // If the store being set as default is already the default, return success
        if ($defaultStore && $defaultStore->id == $store->id) {
            return response()->json([
                'error' => false,
                'error_message' => labels('admin_labels.at_least_one_default_store', 'There must be at least one default store'),
            ]);
        }

        // Update previous default store to not default
        if ($defaultStore) {
            $defaultStore->is_default_store = 0;
            $defaultStore->save();
        }

        // Set the new store as default
        $store->is_default_store = 1;
        $store->save();

        return response()->json([
            'error' => false,
            'message' => labels('admin_labels.store_set_as_default', 'Store has been set as default'),
        ]);
    }


    public function getStores($limit = null, $offset = null, $sort = 'id', $order = 'DESC', $search = null, $from_app = false, $language_code = "")
    {
        $query = DB::table('stores as s')->select('*');
        if ($from_app != true) {
            $query->where('s.status', 1);
        }
        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->orWhere('s.id', 'LIKE', "%{$search}%")
                    ->orWhere('s.name', 'LIKE', "%{$search}%");
            });
        }

        $total = $query->count();

        $stores = $query
            ->orderBy($sort, $order)
            ->when($limit, function ($query, $limit) use ($offset) {
                return $query->skip($offset)->take($limit);
            })
            ->get();

        $bulkData = [
            'error' => $stores->isEmpty(),
            'message' => $stores->isEmpty() ? labels('admin_labels.store_not_exist', 'Store(s) does not exist')
                :
                labels('admin_labels.store_retrieved_successfully', 'Store(s) retrieved successfully'),
            'total' => $total,
            'data' => $stores->map(function ($row) use ($language_code) {
                // dd($language_code);
                return [
                    'id' => $row->id ?? "",
                    'name' => getDynamicTranslation('stores', 'name', $row->id, $language_code) ?? "",
                    'description' => getDynamicTranslation('stores', 'description', $row->id, $language_code) ?? "",
                    'is_single_seller_order_system' => $row->is_single_seller_order_system ?? "",
                    'is_default_store' => $row->is_default_store ?? "",
                    'note_for_necessary_documents' => $row->note_for_necessary_documents ?? "",
                    'primary_color' => $row->primary_color ?? "",
                    'secondary_color' => $row->secondary_color ?? "",
                    'active_color' => $row->active_color ?? "",
                    'hover_color' => $row->hover_color ?? "",
                    'background_color' => $row->background_color ?? "",
                    'store_settings' => json_decode($row->store_settings) ?? "",
                    'image' => getMediaImageUrl($row->image, 'STORE_IMG_PATH'),
                    'banner_image' => getMediaImageUrl($row->banner_image, 'STORE_IMG_PATH'),
                    'banner_image_for_most_selling_product' => getMediaImageUrl($row->banner_image_for_most_selling_product, 'STORE_IMG_PATH'),
                    'stack_image' => getMediaImageUrl($row->stack_image, 'STORE_IMG_PATH'),
                    'login_image' => getMediaImageUrl($row->login_image, 'STORE_IMG_PATH'),
                    'status' => $row->status ?? "",
                    'delivery_charge_type' => $row->delivery_charge_type ?? "",
                    'delivery_charge_amount' => $row->delivery_charge_amount ?? 0,
                    'minimum_free_delivery_amount' => $row->minimum_free_delivery_amount ?? 0,
                    'product_deliverability_type' => $row->product_deliverability_type ?? "",
                ];
            }),
        ];

        return $bulkData;
    }
}
