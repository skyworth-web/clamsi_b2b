<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attribute;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Models\ComboProduct;
use App\Models\ComboProductAttribute;
use App\Models\Language;
use App\Models\Product;
use App\Models\Seller;
use App\Models\Tax;
use App\Models\User;
use App\Models\UserFcm;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ComboProductController extends Controller
{
    public function index()
    {
        $store_id = getStoreId();
        $attributes = ComboProductAttribute::where('store_id', $store_id)->with('attribute_values')->get();
        $languages = Language::all();
        $product_deliverability_type = fetchDetails('stores', ['id' => $store_id], 'product_deliverability_type');
        $product_deliverability_type = isset($product_deliverability_type) && !empty($product_deliverability_type) ? $product_deliverability_type[0]->product_deliverability_type : '';

        $sellers = DB::table('seller_store')
            ->leftJoin('seller_data', 'seller_data.id', '=', 'seller_store.seller_id')
            ->leftJoin('users', 'users.id', '=', 'seller_store.user_id')
            ->select('seller_store.*', 'seller_data.*', 'users.username') // Separate table and column names
            ->where('seller_store.store_id', $store_id)
            ->where('seller_data.status', 1)
            ->get();

        return view('admin.pages.forms.combo_products', compact('attributes', 'sellers', 'product_deliverability_type', 'languages'));
    }

    public function store(Request $request)
    {
        $store_id = getStoreId();
        $rules = [
            'title' => 'required',
            'short_description' => 'required',
            'image' => 'required',
            'seller_id' => 'required',
            'product_type_in_combo' => 'required',
        ];

        if ($request->simple_stock_management_status == 'on') {
            $rules = [
                'product_sku' => 'required',
                'product_total_stock' => 'required',
            ];
        }
        if (!isset($request->physical_product_variant_id) || empty($request->physical_product_variant_id)) {
            return response()->json([
                'error' => true,
                'message' => 'Please select at least one product.'
            ], 422);
        }
        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }


        if (isset($request->digital_product_id) && !empty($request->digital_product_id)) {
            $product_ids = isset($request->digital_product_id) && !empty($request->digital_product_id) ? implode(',', (array)$request->digital_product_id) : '';
            $selected_products = isset($request->digital_product_id) && !empty($request->digital_product_id) ? count($request->digital_product_id) : 0;
        } else {
            $product_variant_ids = isset($request->physical_product_variant_id) && !empty($request->physical_product_variant_id) ? $request->physical_product_variant_id : '';

            $product_ids = DB::table('product_variants')
                ->whereIn('id', $product_variant_ids)
                ->pluck('product_id')
                ->toArray();
            $selected_products = isset($request->physical_product_id) && !empty($request->physical_product_id) ? count($request->physical_product_id) : 0;
            $product_ids = isset($product_ids) && !empty($product_ids) ? implode(',', (array)$product_ids) : '';
            $product_variant_ids = isset($request->physical_product_variant_id) && !empty($request->physical_product_variant_id) ? implode(',', (array)$request->physical_product_variant_id) : '';
        }
        $product_deliverability_type = fetchDetails('stores', ['id' => $store_id], 'product_deliverability_type');
        $product_deliverability_type = isset($product_deliverability_type) && !empty($product_deliverability_type) ? $product_deliverability_type[0]->product_deliverability_type : '';

        $zones = implode(',', (array)$request->deliverable_zones);

        $attribute = implode(',', (array)$request->attribute_id);
        $attribute_value_ids = implode(',', (array)$request->attribute_value_ids);

        $tag_data = isset($request->tags) ? json_decode($request->tags, true) : [];
        $tag_values = array_column($tag_data, 'value');
        $tags = implode(',', $tag_values);

        $translations = [
            'en' => $request->title
        ];
        if (!empty($request['translated_product_title'])) {
            $translations = array_merge($translations, $request['translated_product_title']);
        }
        $translation_descriptions = [
            'en' => $request->short_description
        ];
        if (!empty($request['translated_product_short_description'])) {
            $translation_descriptions = array_merge($translation_descriptions, $request['translated_product_short_description']);
        }

        $product_data = [
            'title' => json_encode($translations, JSON_UNESCAPED_UNICODE),
            'short_description' => json_encode($translation_descriptions, JSON_UNESCAPED_UNICODE),
            'slug' => generateSlug($request->input('title'), 'combo_products', 'slug'),
            'seller_id' => $request->seller_id ?? '',
            'image' => $request->image ?? '',
            'description' => $request->description ?? '',
            'deliverable_type' => isset($product_deliverability_type) && !empty($product_deliverability_type) && $product_deliverability_type == 'zipcode_wise_deliverability' ? (isset($request->deliverable_type) ? $request->deliverable_type : '') : '',
            'deliverable_zones' => ($request->deliverable_type == '1' || $request->deliverable_type == '0') ? '' : $zones,
            'pickup_location' => $request->pickup_location ?? '',
            'tax' => (isset($request->pro_input_tax) && !empty($request->pro_input_tax)) ? implode(',', (array)$request->pro_input_tax) : '',
            'weight' => $request->weight ?? 0,
            'height' => $request->height ?? 0,
            'length' => $request->length ?? 0,
            'breadth' => $request->breadth ?? 0,
            'tags' => $tags,
            'selected_products' => $selected_products,
            'price' => $request->simple_price ?? '',
            'special_price' => $request->simple_special_price ?? '',
            'product_type' => $request->product_type_in_combo ?? '',
            'quantity_step_size' => $request->quantity_step_size ?? '',
            'minimum_order_quantity' => $request->minimum_order_quantity ?? '',
            'total_allowed_quantity' => $request->total_allowed_quantity ?? '',
            'product_ids' => $product_ids ?? '',
            'product_variant_ids' => $product_variant_ids ?? '',
            'status' => 1,
            'store_id' => $store_id ?? '',
            'attribute' => $attribute ?? '',
            'attribute_value_ids' => $attribute_value_ids ?? '',
            'minimum_free_delivery_order_qty' => isset($request->minimum_free_delivery_order_qty) ? $request->minimum_free_delivery_order_qty : '',
            'delivery_charges' => isset($request->delivery_charges) ? $request->delivery_charges : '',
        ];

        $download_link = (isset($request->download_link_type) && !empty($request->download_link_type)) ? (($request->download_link_type == 'add_link') ? $request->download_link : $request->pro_input_zip) : "";

        $download_type = (isset($request->download_link_type) && !empty($request->download_link_type)) ? $request->download_link_type : "";

        if (isset($request->download_allowed)  && $request->download_allowed != "" && ($request->download_allowed == "on" || $request->download_allowed == '1')) {
            $product_data['download_allowed'] = '1';
            $product_data['download_type'] = $download_type;
            $product_data['download_link'] = $download_link;
        } else {
            $product_data['download_allowed'] = '0';
            $product_data['download_type'] = '';
            $product_data['download_link'] = '';
        }


        if (isset($request->simple_product_stock_status)  && in_array($request->simple_product_stock_status, array('0', '1'))) {
            if (!empty($request->product_sku)) {
                $product_data['sku'] = $request->product_sku;
            }
            $product_data['stock'] = $request->product_total_stock;
            $product_data['availability'] = $request->simple_product_stock_status;
        }

        if ($request->product_type_in_combo != 'digital_product' && isset($request->cod_allowed)  && $request->cod_allowed != "" && ($request->cod_allowed == "on" || $request->cod_allowed == '1')) {
            $product_data['cod_allowed'] = '1';
        } else {
            $product_data['cod_allowed'] = '0';
        }

        if (isset($request->is_prices_inclusive_tax) && $request->is_prices_inclusive_tax != "" && ($request->is_prices_inclusive_tax == "on" || $request->is_prices_inclusive_tax == '1')) {
            $product_data['is_prices_inclusive_tax'] = '1';
        } else {
            $product_data['is_prices_inclusive_tax'] = '0';
        }
        if ($request->product_type_in_combo != 'digital_product' && isset($request->is_returnable)  && $request->is_returnable != "" && ($request->is_returnable == "on" || $request->is_returnable == '1')) {
            $product_data['is_returnable'] = '1';
        } else {
            $product_data['is_returnable'] = '0';
        }

        if ($request->product_type_in_combo != 'digital_product' && isset($request->is_cancelable)  && $request->is_cancelable != "" && ($request->is_cancelable == "on" || $request->is_cancelable == '1')) {
            $product_data['is_cancelable'] = '1';
            $product_data['cancelable_till'] = $request->cancelable_till;
        } else {
            $product_data['is_cancelable'] = '0';
            $product_data['cancelable_till'] = '';
        }
        if (isset($request->is_attachment_required)  && $request->is_attachment_required != "" && ($request->is_attachment_required == "on" || $request->is_attachment_required == '1')) {
            $product_data['is_attachment_required'] = '1';
        } else {
            $product_data['is_attachment_required'] = '0';
        }
        if ($request->product_type_in_combo != 'digital_product' && isset($request->has_similar_product)  && $request->has_similar_product != "" && ($request->has_similar_product == "on" || $request->has_similar_product == '1')) {
            $product_data['has_similar_product'] = '1';

            $product_data['similar_product_ids'] = implode(',', (array)$request->similar_product_id);
        }
        $product_data['other_images'] = isset($request->other_images) ? json_encode($request->other_images, 1) : '';

        $product =  ComboProduct::create($product_data);

        if ($product) {
            return response()->json([
                'error' => false,
                'message' =>
                labels('admin_labels.product_added_successfully', 'Product added successfully.'),
                'location' => route('admin.combo_products.manage_product')
            ]);
        }
    }

    public function update(Request $request, $data)
    {
        $product_data = fetchDetails('combo_products', ['id' => $data], '*');
        $product_type = $product_data[0]->product_type;
        $store_id = getStoreId();
        $rules = [
            'title' => 'required',
            'short_description' => 'required',
            'image' => 'required',
            'seller_id' => 'required'
        ];

        if ($request->simple_stock_management_status == 'on') {
            $rules = [
                'product_sku' => 'required',
                'product_total_stock' => 'required',
            ];
        }
        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }
        $product_deliverability_type = fetchDetails('stores', ['id' => $store_id], 'product_deliverability_type');
        $product_deliverability_type = isset($product_deliverability_type) && !empty($product_deliverability_type) ? $product_deliverability_type[0]->product_deliverability_type : '';

        $zones = implode(',', (array)$request->deliverable_zones);


        if (isset($request->digital_product_id) && !empty($request->digital_product_id) && $request->digital_product_id[0] != null) {
            $product_ids = implode(',', (array)$request->digital_product_id);
            $selected_products = count($request->digital_product_id);
        } else {
            $product_variant_ids = isset($request->physical_product_variant_id) && !empty($request->physical_product_variant_id) ? $request->physical_product_variant_id : '';

            $product_ids = DB::table('product_variants')
                ->whereIn('id', $product_variant_ids)
                ->pluck('product_id')
                ->toArray();

            $selected_products = isset($request->physical_product_id) && !empty($request->physical_product_id) ? count($request->physical_product_id) : 0;
            $product_ids = isset($product_ids) && !empty($product_ids) ? implode(',', (array)$product_ids) : '';
            $product_variant_ids = isset($request->physical_product_variant_id) && !empty($request->physical_product_variant_id) ? implode(',', (array)$request->physical_product_variant_id) : '';
        }

        $attribute = implode(',', (array)$request->attribute_id);
        $attribute_value_ids = implode(',', (array)$request->attribute_value_ids);

        $tag_data = isset($request->tags) ? json_decode($request->tags, true) : [];
        $tag_values = array_column($tag_data, 'value');
        $tags = implode(',', $tag_values);

        $new_name = $request->title;
        $current_name = $product_data[0]->title;
        $current_slug = $product_data[0]->slug;

        $translations = json_decode($product_data[0]->title, true) ?? [];
        $translation_descriptions = json_decode($product_data[0]->short_description, true) ?? [];

        $translations['en'] = $request->title;
        $translation_descriptions['en'] = $request->short_description;

        if (!empty($request->translated_product_title)) {
            $translations = array_merge($translations, $request->translated_product_title);
        }
        if (!empty($request->translated_product_short_description)) {
            $translation_descriptions = array_merge($translation_descriptions, $request->translated_product_short_description);
        }

        $product_data = [
            'title' => json_encode($translations, JSON_UNESCAPED_UNICODE),
            'short_description' => json_encode($translation_descriptions, JSON_UNESCAPED_UNICODE),
            'slug' => generateSlug($new_name, 'combo_products', 'slug', $current_slug, $current_name),
            'seller_id' => $request->seller_id ?? '',
            'image' => $request->image ?? '',
            'description' => $request->description ?? '',
            'deliverable_type' => isset($request->deliverable_type) ? $request->deliverable_type : '',
            'deliverable_zones' => ($request->deliverable_type == '1' || $request->deliverable_type == '0') ? '' : $zones,
            'pickup_location' => $request->pickup_location ?? '',
            'tax' => (isset($request->pro_input_tax) && !empty($request->pro_input_tax)) ? implode(',', (array)$request->pro_input_tax) : '',
            'weight' => $request->weight ?? 0,
            'height' => $request->height ?? 0,
            'length' => $request->length ?? 0,
            'breadth' => $request->breadth ?? 0,
            'tags' => $tags,
            'selected_products' => $selected_products,
            'price' => $request->simple_price ?? '',
            'special_price' => $request->simple_special_price ?? '',
            'product_type' => $request->product_type_in_combo ?? $product_type,
            'quantity_step_size' => $request->quantity_step_size ?? '',
            'minimum_order_quantity' => $request->minimum_order_quantity ?? '',
            'total_allowed_quantity' => $request->total_allowed_quantity ?? '',
            'minimum_free_delivery_order_qty' => $request->minimum_free_delivery_order_qty ?? 0,
            'delivery_charges' => $request->delivery_charges ?? 0,
            'product_ids' => $product_ids ?? '',
            'product_variant_ids' => $product_variant_ids ?? '',
            'status' => 1,
            'store_id' => $store_id ?? '',
            'attribute' => $attribute ?? '',
            'attribute_value_ids' => $attribute_value_ids ?? '',
        ];


        if (isset($request->simple_product_stock_status)  && in_array($request->simple_product_stock_status, array('0', '1'))) {
            if (!empty($request->product_sku)) {
                $product_data['sku'] = $request->product_sku;
            }
            $product_data['stock'] = $request->product_total_stock;
            $product_data['availability'] = $request->simple_product_stock_status;
        }

        if (
            $request->product_type_in_combo != 'digital_product' && isset($request->cod_allowed)  && $request->cod_allowed != "" && ($request->cod_allowed == "on" || $request->cod_allowed == '1')
        ) {
            $product_data['cod_allowed'] = '1';
        } else {
            $product_data['cod_allowed'] = '0';
        }

        if (isset($request->is_prices_inclusive_tax) && $request->is_prices_inclusive_tax != "" && ($request->is_prices_inclusive_tax == "on" || $request->is_prices_inclusive_tax == '1')) {
            $product_data['is_prices_inclusive_tax'] = '1';
        } else {
            $product_data['is_prices_inclusive_tax'] = '0';
        }
        if ($request->product_type_in_combo != 'digital_product' && isset($request->is_returnable)  && $request->is_returnable != "" && ($request->is_returnable == "on" || $request->is_returnable == '1')) {
            $product_data['is_returnable'] = '1';
        } else {
            $product_data['is_returnable'] = '0';
        }

        if (
            $request->product_type_in_combo != 'digital_product' && isset($request->is_cancelable)  && $request->is_cancelable != "" && ($request->is_cancelable == "on" || $request->is_cancelable == '1')
        ) {
            $product_data['is_cancelable'] = '1';
            $product_data['cancelable_till'] = $request->cancelable_till;
        } else {
            $product_data['is_cancelable'] = '0';
            $product_data['cancelable_till'] = '';
        }
        if (
            isset($request->is_attachment_required)  && $request->is_attachment_required != "" && ($request->is_attachment_required == "on" || $request->is_attachment_required == '1')
        ) {
            $product_data['is_attachment_required'] = '1';
        } else {
            $product_data['is_attachment_required'] = '0';
        }

        if (isset($request->has_similar_product)  && $request->has_similar_product != "" && ($request->has_similar_product == "on" || $request->has_similar_product == '1')) {
            $product_data['has_similar_product'] = '1';
            $product_data['similar_product_ids'] = implode(',', (array)$request->similar_product_id);
        } else {
            $product_data['has_similar_product'] = '0';
            $product_data['similar_product_ids'] = '';
        }
        // dd($product_data);
        $product_data['other_images'] = isset($request->other_images) ? json_encode($request->other_images, 1) : '';

        $product =
            $product =  ComboProduct::where('id', $data)->update($product_data);

        if ($product) {
            return response()->json([
                'error' => false,
                'message' =>
                labels('admin_labels.product_updated_successfully', 'Product updated successfully.'),
                'location' => route('admin.combo_products.manage_product')
            ]);
        }
    }


    public function manageProduct()
    {
        return view('admin.pages.tables.manage_combo_products');
    }

    public function list()
    {
        $store_id = getStoreId();
        $search = trim(request('search'));
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $limit = request("limit");
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $settings = getSettings('system_settings', true);
        $settings = json_decode($settings, true);
        $low_stock_limit = isset($settings['low_stock_limit']) ? $settings['low_stock_limit'] : 5;

        $query = ComboProduct::query();
        $query->select('combo_products.*', 'seller_store.store_name', 'combo_products.id as pid', 'combo_products.title', 'combo_products.product_type', 'combo_products.image', 'combo_products.status', 'combo_products.price', 'combo_products.special_price', 'combo_products.stock')
            ->join('seller_store', 'seller_store.seller_id', '=', 'combo_products.seller_id')
            ->where('combo_products.store_id', $store_id);

        // if (request()->filled('search')) {
        //     $search = trim(request('search'));
        //     $query->where(function ($q) use ($search) {
        //         $q->where('combo_products.id', 'LIKE', '%' . $search . '%')
        //             ->orWhere('combo_products.title', 'LIKE', "%$search%")
        //             ->orWhere('combo_products.description', 'LIKE', "%$search%")
        //             ->orWhere('combo_products.short_description', 'LIKE', "%$search%");
        //     });
        // }

        $language_code = get_language_code();
        if (request()->filled('search')) {
            $search = trim(request('search'));
            $query->where(function ($q) use ($search, $language_code) {
                $q->where('combo_products.id', (string) $search)
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(combo_products.title, '$.\"$language_code\"')) LIKE ?", ["%$search%"])
                    ->orWhere('combo_products.description', 'LIKE', "%$search%")
                    ->orWhere('combo_products.short_description', 'LIKE', "%$search%");
            });
        }

        if (request()->has('flag') && request('flag') === 'low') {
            $query->where(function ($q) use ($low_stock_limit) {
                $q->whereNotNull('combo_products.stock_type')
                    ->where('combo_products.stock', '<=', $low_stock_limit)
                    ->where('combo_products.availability', '=', 1)
                    ->orWhere('combo_products.stock', '<=', $low_stock_limit)
                    ->where('combo_products.availability', '=', 1);
            });
        }

        if (request()->filled('seller_id')) {
            $query->where('combo_products.seller_id', request('seller_id'));
        }

        if (request()->filled('status')) {
            $query->where('combo_products.status', request('status'));
        }

        if (request()->has('flag') && request('flag') === 'sold') {
            $query->where(function ($q) {
                $q->whereNotNull('combo_products.stock_type')
                    ->where('combo_products.stock', '=', 0)
                    ->where('combo_products.availability', '=', 0)
                    ->orWhere('combo_products.stock', '=', 0)
                    ->where('combo_products.availability', '=', 0);
            });
        }



        $total = $query->distinct('combo_products.id')->count('pid');

        $combo_products = $query->groupBy('pid')
            ->orderBy($sort, $order)
            ->skip($offset)
            ->take($limit)
            ->get();

        $combo_products = $combo_products->map(function ($p) use ($language_code) {

            $store_id = getStoreId();
            $edit_url = route('admin.combo_products.edit', $p->pid);
            $delete_url = route('admin.combo_products.destroy', $p->pid);


            $show_url = route('admin.combo_products.show', $p->id);

            $action = '<div class="dropdown bootstrap-table-dropdown">
                    <a href="#" class="text-dark" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="bx bx-dots-horizontal-rounded"></i>
                        </a>
                        <div class="dropdown-menu table_dropdown combo_product_action_dropdown" aria-labelledby="dropdownMenuButton">
                            <a class="dropdown-item dropdown_menu_items" href="' . $edit_url . '"><i class="bx bx-pencil mx-2"></i> Edit</a>
                            <a class="dropdown-item delete-data dropdown_menu_items" data-url="' . $delete_url . '"><i class="bx bx-trash mx-2"></i> Delete</a>
                            <a class="dropdown-item dropdown_menu_items" href="' . $show_url . '"><i class="bx bxs-show mx-2"></i>View</a>
                        </div>
                    </div>';

            $product_ids = explode(',', $p->product_ids);

            $product_names = array_map(function ($product_id) use ($language_code) {
                return getDynamicTranslation('products', 'name', $product_id, $language_code);
            }, $product_ids);
            $image = route('admin.dynamic_image', [
                'url' => getMediaImageUrl($p->image),
                'width' => 60,
                'quality' => 90
            ]);
            return [
                'id' => $p->id,
                'title' => getDynamicTranslation('combo_products', 'title', $p->id, $language_code) . '<br><small>' . ucwords(str_replace('_', ' ', $p->product_type)) . '</small><br><small> By </small><b>' . ($p->store_name) ?? '' . '</b>',
                'products' => $product_names,
                'image' => '<div class="d-flex align-items-center"><a href="' . getMediaImageUrl($p->image) . '" data-lightbox="image-' . $p->id . '"><img src=' . $image . ' class="rounded mx-2"></a></div>',
                'status' => '<select class="form-select status_dropdown change_toggle_status ' .
                    ($p->status == 1 ? 'active_status' : ($p->status == 2 ? 'not_approved_status bg-gray-500' : 'inactive_status')) .
                    '" data-id="' . $p->id . '" data-url="/admin/combo_products/update_status/' . $p->id . '" aria-label="">
                <option value="1" ' . ($p->status == 1 ? 'selected' : '') . '>Active</option>
                <option value="0" ' . ($p->status == 0 ? 'selected' : '') . '>Deactive</option>' .
                    ($p->status == 2 ? '<option value="2" ' . ($p->status == 2 ? 'selected' : '') . '>Not Approved</option>' : '') .
                    '</select>',
                'operate' => $action,

            ];
        });

        return response()->json([
            "rows" => $combo_products,
            "total" => $total,
        ]);
    }

    public function destroy($id)
    {
        $product = ComboProduct::find($id);
        if ($product) {
            $product->delete();
            return response()->json([
                'error' => false,
                'message' => labels('admin_labels.product_deleted_successfully', 'Product deleted successfully!')
            ]);
        } else {
            return response()->json(['error' => labels('admin_labels.data_not_found', 'Data Not Found')]);
        }
    }

    public function update_status($id, Request $request)
    {
        $status = $request->status ?? "0";
        $combo_product = ComboProduct::findOrFail($id);
        $product_id = $combo_product->id;

        $seller_id = Seller::where('id', $combo_product->seller_id)->value('user_id');
        $user = User::where('id', $seller_id)->select('fcm_id', 'username')->first();
        $results =  UserFcm::join('users', 'user_fcm.user_id', '=', 'users.id')
            ->where('user_fcm.user_id', $seller_id)
            ->where('users.is_notification_on', 1)
            ->select('user_fcm.fcm_id')
            ->get();
        $store_id = $combo_product->store_id;

        if ($combo_product->status !== $status) {
            $combo_product->status = $status;
            $combo_product->save();
            $fcm_ids = array();
            // Send notification (you need to implement this method)
            $title = "Product status changed";
            $message = "Hello dear " . $user->username . " your product status has been changed";
            $fcmMsg = array(
                'title' => "$title",
                'body' => "$message",
                'type' => "combo_product",
                'product_id' => "$product_id",
                'store_id' => "$store_id",
                'status' => "$status"
            );
            foreach ($results as $result) {
                if (is_object($result)) {
                    $fcm_ids[] = $result->fcm_id;
                }
            }
            $registrationIDs_chunks = array_chunk($fcm_ids, 1000);
            sendNotification('', $registrationIDs_chunks, $fcmMsg);
        }


        return response()->json(['success' => labels('admin_labels.status_updated_successfully', 'Status updated successfully.')]);
    }

    public function edit($data)
    {
        $store_id = getStoreId();
        $languages = Language::all();
        $language_code = get_language_code();
        $data = ComboProduct::where('store_id', $store_id)
            ->find($data);

        if ($data === null || empty($data)) {
            return view('admin.pages.views.no_data_found');
        } else {
            $attributes = ComboProductAttribute::with('attribute_values')->where('store_id', $store_id)->get();

            $sellers = DB::table('seller_store')
                ->leftJoin('seller_data', 'seller_data.id', '=', 'seller_store.seller_id')
                ->leftJoin('users', 'users.id', '=', 'seller_store.user_id')
                ->select('seller_store.*', 'users.username', 'seller_store.store_name')
                ->where('seller_store.store_id', $store_id)
                ->get();

            $seller_id = fetchDetails('combo_products', ['id' => $data->id], 'seller_id')[0]->seller_id;


            // Extract product IDs and variant IDs
            $product_ids = explode(',', $data->product_ids);
            $variant_ids = explode(',', $data->product_variant_ids);

            // Fetch products
            $products = Product::whereIn('id', $product_ids)
                ->where('store_id', $store_id)
                ->get();
            $results = [];

            // Iterate through each product
            foreach ($products as $product) {
                // dd($product);
                // If the product is a variable product
                if ($product->type == 'variable_product') {
                    // Fetch all variants for this product
                    $variants = DB::table('product_variants')
                        ->where('product_id', $product->id)
                        ->whereIn('id', $variant_ids)
                        ->get();

                    foreach ($variants as $variant) {
                        // Get attribute names for the variant
                        $attribute_value_ids = explode(',', $variant->attribute_value_ids);
                        $attribute_names = DB::table('attribute_values')
                            ->whereIn('id', $attribute_value_ids)
                            ->pluck('value')
                            ->toArray();

                        $variant_name = implode(', ', $attribute_names);

                        // Add the variant details to the results
                        $results[] = [
                            'id' => $variant->id,
                            'product_id' => $product->id,
                            'name' => $product->name,
                            'variant_name' => $variant_name,
                        ];
                    }
                } else {
                    // For simple products, fetch the single variant
                    $variant = DB::table('product_variants')
                        ->where('product_id', $product->id)
                        ->first();

                    if ($variant) {
                        // Add the simple product details to the results
                        $results[] = [
                            'id' => $variant->id,
                            'product_id' => $product->id,
                            'name' => $product->name,
                            'variant_name' => '', // No variant name for simple products
                        ];
                    }
                }
            }

            // Convert results to array
            $product_details = $results;

            // dd($product_details);
            $combo_product_details = ComboProduct::whereIn('id', explode(',', $data->similar_product_ids))->where('store_id', $store_id)
                ->get()
                ->toArray();

            $product_deliverability_type = fetchDetails('stores', ['id' => $store_id], 'product_deliverability_type');
            $product_deliverability_type = isset($product_deliverability_type) && !empty($product_deliverability_type) ? $product_deliverability_type[0]->product_deliverability_type : '';

            $shipping_data = fetchDetails('pickup_locations', ['status' => 1, 'seller_id' => $seller_id], ['id', 'pickup_location']);

            return view('admin.pages.forms.update_combo_product', compact('data', 'attributes', 'sellers', 'shipping_data', 'product_details', 'combo_product_details', 'product_deliverability_type', 'languages', 'language_code'));
        }
    }

    public function fetchAttributesById(request $request)
    {
        $id = $request->edit_id;


        $res['attr_values'] = getComboAttributeValuesByPid($id);


        $response['result'] = $res;
        return $response;
    }

    public function getProductdetails(Request $request)
    {
        $store_id = getStoreId();
        $search = trim($request->input('search'));
        $limit = (int) $request->input('limit', 2);
        $page = (int) $request->input('page', 1);
        $offset = ($page - 1) * $limit;
        $is_update = (int) $request->input('is_update', 0);
        $product_id = (int) $request->input('product_id', 0);

        // Create the query for fetching products
        $productsQuery = ComboProduct::where('title', 'like', '%' . $search . '%')
            ->where('store_id', $store_id)
            ->where('status', 1);

        // If 'is_update' is 1, exclude the product with the given 'product_id'
        if ($is_update === 1 && $product_id > 0) {
            $productsQuery->where('id', '!=', $product_id);
        }

        // Fetch paginated product results
        $products = $productsQuery->orderBy('id', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get(['id', 'title']);
        $language_code = get_language_code();
        // Count total products matching the query
        $totalCount = $productsQuery->count();

        // Prepare response data
        $response = [
            'total' => $totalCount,
            'results' => $products->map(function ($product) use ($language_code) {
                return [
                    'id' => $product->id,
                    'text' => getDynamicTranslation('combo_products', 'title', $product->id, $language_code),
                ];
            }),
            'pagination' => [
                'more' => ($page * $limit) < $totalCount,
            ],
        ];

        return response()->json($response);
    }


    public function show($id)
    {
        $store_id = getStoreId();


        $data = ComboProduct::where('store_id', $store_id)
            ->find($id);

        if ($data === null || empty($data)) {
            return view('admin.pages.views.no_data_found');
        } else {
            $sellers = DB::table('seller_store')
                ->leftJoin('seller_data', 'seller_data.id', '=', 'seller_store.seller_id')
                ->leftJoin('users', 'users.id', '=', 'seller_store.user_id')
                ->select('seller_store.*', 'users.username', 'seller_store.store_name')
                ->where('seller_store.store_id', $store_id)
                ->get();

            $taxes = Tax::where('status', 1)->get();
            $language_code = get_language_code();
            $seller_id = fetchDetails('combo_products', ['id' => $data->id], 'seller_id')[0]->seller_id;

            $product_details = Product::whereIn('id', explode(',', $data->product_ids))->where('store_id', $store_id)
                ->get()
                ->toArray();
            $combo_product_details = ComboProduct::whereIn('id', explode(',', $data->similar_product_ids))->where('store_id', $store_id)
                ->get()
                ->toArray();

            $shipping_data = fetchDetails('pickup_locations', ['status' => 1, 'seller_id' => $seller_id], ['id', 'pickup_location']);


            $product_faqs = getProductFaqs('', $data->id);

            $ProductRatingController = new ComboProductRatingController;

            $rating = $ProductRatingController->fetch_rating(($id) ? $id : '', '', 10, 0, 'id', 'desc');


            $attributes = DB::table('combo_products')
                ->select('combo_product_attributes.id as attribute_id', 'combo_product_attribute_values.id as attribute_value_id', 'combo_product_attributes.name', 'combo_product_attribute_values.value')
                ->join('combo_product_attribute_values', 'combo_products.attribute_value_ids', 'LIKE', DB::raw("CONCAT('%', combo_product_attribute_values.id, '%')"))
                ->join('combo_product_attributes', 'combo_product_attribute_values.combo_product_attribute_id', '=', 'combo_product_attributes.id')
                ->where('combo_products.id', $id)
                ->get();

            $product_faqs = getComboProductFaqs('', $data->id);


            return view('admin.pages.views.combo_product', compact('data', 'attributes', 'sellers', 'taxes',  'shipping_data', 'product_faqs', 'product_details', 'combo_product_details', 'rating', 'language_code'));
        }
    }

    public function bulk_upload()
    {
        return view('admin.pages.forms.combo_product_bulk_upload');
    }

    public function process_bulk_upload(Request $request)
    {

        if (!$request->hasFile('upload_file')) {
            return response()->json(['error' => 'true', 'message' => labels('admin_labels.please_choose_file', 'Please Choose File')]);
        }
        $allowed_mime_types = [
            'text/x-comma-separated-values',
            'text/comma-separated-values',
            'application/x-csv',
            'text/x-csv',
            'text/csv',
            'application/csv',
        ];

        $uploaded_file = $request->file('upload_file');
        $uploaded_mime_type = $uploaded_file->getClientMimeType();

        if (!in_array($uploaded_mime_type, $allowed_mime_types)) {
            return response()->json(['error' => 'true', 'message' => labels('admin_labels.invalid_file_format', 'Invalid File Format')]);
        }

        $csv = $_FILES['upload_file']['tmp_name'];
        $temp = 0;
        $temp1 = 0;
        $handle = fopen($csv, "r");
        $allowed_status = array("received", "processed", "shipped");
        $video_types = array("youtube", "vimeo");
        $type = $request->type;

        if ($type == 'upload') {
            while (($row = fgetcsv($handle, 10000, ",")) != FALSE) //get row values
            {


                if ($temp != 0) {
                    if (empty($row[0])) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.store_id_empty_at_row', 'Store id is empty at row')
                            . $row[0]]);
                    }
                    if (empty($row[1])) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.seller_id_empty_at_row', 'Seller id is empty at row')
                            . $row[1]]);
                    }
                    if (empty($row[2])) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.title_empty_at_row', 'Title is empty at row')
                            . $row[2]]);
                    }
                    if ($row[3] != 'physical_product' && $row[3] != 'digital_product') {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.product_type_invalid_at_row', 'Product type is invalid at row')
                            . $temp]);
                    }

                    if (empty($row[4])) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.short_description_empty_at_row', 'Short Description is empty at row')
                            . $temp]);
                    }
                    if (empty($row[5])) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.image_is_empty_at_row', 'Image is empty at row') . $temp]);
                    }
                    if (empty($row[8])) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.product_ids_empty_at_row', 'Product Ids are empty at row')
                            . $temp]);
                    }
                    if (empty($row[9])) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.selected_products_empty_at_row', 'Selected Products is empty at row')
                            . $temp]);
                    }

                    if ($row[10] == '1' || $row[11] == '') {
                        if (empty($row[11])) {
                            return response()->json(['error' => 'true', 'message' => labels('admin_labels.similar_product_ids_empty_at_row', 'Similar Product Ids are empty at row')
                                . $temp]);
                        }
                    }

                    if (empty($row[12])) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.price_empty_at_row', 'Price is empty at row')
                            . $temp]);
                    }
                    if (empty($row[13])) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.special_price_empty_at_row', 'Special Price is empty at row')
                            . $temp]);
                    }

                    if (
                        $row[14] == '2' || $row[14] == '3'
                    ) {
                        if (empty($row[15])) {
                            return response()->json(['error' => 'true', 'message' => labels('admin_labels.deliverable_zipcodes_empty_at_row', 'Deliverable Zipcodes is empty at row')
                                . $temp]);
                        }
                    }

                    if ($row[14] != 0 && $row[14] != 1 && $row[14] != 2 && $row[14] != 3 && $row[14] == "") {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.not_valid_value_for_deliverable_type_at_row', 'Not valid value for deliverable type at row')
                            . $temp]);
                    }

                    if (!empty($row[30]) && $row[30] != 1) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.cod_allowed_invalid_at_row', 'COD allowed is invalid at row')
                            . $temp]);
                    }

                    if (!empty($row[31]) && $row[31] != 1) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.prices_inclusive_tax_invalid_at_row', 'Is prices inclusive tax is invalid at row')
                            . $temp]);
                    }

                    if (!empty($row[32]) && $row[32] != 1) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.returnable_invalid_at_row', 'Is Returnable is invalid at row')
                            . $temp]);
                    }

                    if (!empty($row[33]) && $row[33] != 1) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.cancelable_invalid_at_row', 'Is Cancelable is invalid at row')
                            . $temp]);
                    }

                    if (!empty($row[33]) && $row[33] == 1 && (empty($row[34]) || !in_array($row[34], $allowed_status))) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.cancelable_till_invalid_at_row', 'Cancelable till is invalid at row')
                            . $temp]);
                    }

                    if (empty($row[33]) && !(empty($row[34]))) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.cancelable_till_invalid_at_row', 'Cancelable till is invalid at row')
                            . $temp]);
                    }

                    $seller_id = $row[1];
                }
                $temp++;
            }

            fclose($handle);
            $handle = fopen($csv, "r");
            while (($row = fgetcsv($handle, 10000, ",")) != FALSE) //get row vales
            {


                if ($temp1 != 0) {
                    $data = [
                        'store_id' => !empty($row[0]) ? $row[0] : "",
                        'seller_id' => !empty($row[1]) ? $row[1] : "",
                        'title' => !empty($row[2]) ? $row[2] : "",
                        'slug' => !empty($row[2]) ? generateSlug($row[2], 'combo_products') : "",
                        'product_type' => !empty($row[3]) ? $row[3] : "",
                        'short_description' => !empty($row[4]) ? $row[4] : "",
                        'image' => !empty($row[5]) ? $row[5] : "",
                        'description' => !empty($row[6]) ? $row[6] : "",
                        'tags' => !empty($row[7]) ? $row[7] : "",
                        'product_ids' => !empty($row[8]) ? $row[8] : "",
                        'selected_products' => !empty($row[9]) ? $row[9] : "",
                        'has_similar_product' => !empty($row[10]) ? $row[10] : "",
                        'similar_product_ids' => !empty($row[11]) ? $row[11] : "",
                        'price' => !empty($row[12]) ? $row[12] : "",
                        'special_price' => !empty($row[13]) ? $row[13] : "",
                        'deliverable_type' => !empty($row[14]) ? $row[14] : "",
                        'deliverable_zones' => !empty($row[15]) ? $row[15] : "",
                        'pickup_location' =>   $row[16],
                        'tax' =>  $row[17],
                        'weight' =>  $row[18],
                        'height' =>   $row[19],
                        'length' =>  $row[20],
                        'breadth' =>   $row[21],
                        'quantity_step_size' => !empty($row[22]) ? $row[22] : "",
                        'minimum_order_quantity' => !empty($row[23]) ? $row[23] : "",
                        'total_allowed_quantity' => !empty($row[24]) ? $row[24] : "",
                        'attribute' => !empty($row[25]) ? $row[25] : "",
                        'attribute_value_ids' => !empty($row[26]) ? $row[26] : "",
                        'sku' => !empty($row[27]) ? $row[27] : "",
                        'stock' => !empty($row[28]) ? $row[28] : "",
                        'availability' => !empty($row[29]) ? $row[29] : "",
                        'cod_allowed' => !empty($row[30]) ? $row[30] : "",
                        'is_prices_inclusive_tax' => !empty($row[31]) ? $row[31] : "",
                        'is_returnable' => !empty($row[32]) ? $row[32] : "",
                        'is_cancelable' => !empty($row[33]) ? $row[33] : "",
                        'cancelable_till' => !empty($row[34]) ? $row[34] : "",
                        'minimum_free_delivery_order_qty' => !empty($row[35]) ? $row[35] : "",
                        'delivery_charges' => !empty($row[36]) ? $row[36] : "",
                        'other_images' => isset($row[37]) && !empty($row[37]) ? json_encode(explode(',', $row[37]), 1) : '[]',
                    ];

                    $product =  ComboProduct::create($data);
                }
                $temp1++;
            }
            fclose($handle);
            return response()->json([
                'error' => 'false',
                'message' => labels('admin_labels.products_uploaded_successfully', 'Products uploaded successfully!')
            ]);
        } else { // bulk_update
            while (($row = fgetcsv($handle, 10000, ",")) != FALSE) //get row vales
            {

                if ($temp != 0) {
                    if (empty($row[0])) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.product_id_empty_at_row', 'Product id is empty at row')
                            . $temp]);
                    }
                    if (empty($row[1])) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.seller_id_empty_at_row', 'Seller id is empty at row')
                            . $row[0]]);
                    }
                    if (empty($row[2])) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.title_empty_at_row', 'Title is empty at row')
                            . $row[0]]);
                    }

                    if (empty($row[3])) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.short_description_empty_at_row', 'Short Description is empty at row')
                            . $temp]);
                    }
                    if (empty($row[4])) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.image_is_empty_at_row', 'Image is empty at row') . $temp]);
                    }
                    if (empty($row[7])) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.product_ids_empty_at_row', 'Product Ids are empty at row')
                            . $temp]);
                    }
                    if (empty($row[8])) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.selected_products_empty_at_row', 'Selected Products is empty at row')
                            . $temp]);
                    }

                    if ($row[9] == '1' || $row[10] == '') {
                        if (empty($row[10])) {
                            return response()->json(['error' => 'true', 'message' => labels('admin_labels.similar_product_ids_empty_at_row', 'Similar Product Ids are empty at row')
                                . $temp]);
                        }
                    }

                    if (empty($row[11])) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.price_empty_at_row', 'Price is empty at row')
                            . $temp]);
                    }
                    if (empty($row[12])) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.special_price_empty_at_row', 'Special Price is empty at row')
                            . $temp]);
                    }

                    if (
                        $row[13] == '2' || $row[13] == '3'
                    ) {
                        if (empty($row[14])) {
                            return response()->json(['error' => 'true', 'message' => labels('admin_labels.deliverable_zipcodes_empty_at_row', 'Deliverable Zipcodes is empty at row')
                                . $temp]);
                        }
                    }

                    if ($row[13] != 0 && $row[13] != 1 && $row[13] != 2 && $row[13] != 3 && $row[13] == "") {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.not_valid_value_for_deliverable_type_at_row', 'Not valid value for deliverable_type at row')
                            . $temp]);
                    }

                    if (!empty($row[29]) && $row[29] != 1) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.cod_allowed_invalid_at_row', 'COD allowed is invalid at row')
                            . $temp]);
                    }

                    if (!empty($row[30]) && $row[30] != 1) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.prices_inclusive_tax_invalid_at_row', 'Is prices inclusive tax is invalid at row')
                            . $temp]);
                    }

                    if (!empty($row[31]) && $row[31] != 1) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.returnable_invalid_at_row', 'Is Returnable is invalid at row')
                            . $temp]);
                    }

                    if (!empty($row[32]) && $row[32] != 1) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.cancelable_invalid_at_row', 'Is Cancelable is invalid at row')
                            . $temp]);
                    }

                    if (!empty($row[32]) && $row[32] == 1 && (empty($row[33]) || !in_array($row[33], $allowed_status))) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.cancelable_till_invalid_at_row', 'Cancelable till is invalid at row')
                            . $temp]);
                    }

                    if (empty($row[32]) && !(empty($row[33]))) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.cancelable_till_invalid_at_row', 'Cancelable till is invalid at row')
                            . $temp]);
                    }
                }
                $temp++;
            }

            fclose($handle);
            $handle = fopen($csv, "r");
            while (($row = fgetcsv($handle, 10000, ",")) != FALSE) //get row values
            {

                if ($temp1 != 0) {
                    $product_id = $row[0];
                    $product = fetchDetails(
                        'combo_products',
                        ['id' => $product_id],
                        '*'
                    );
                    if (isset($product[0]) && !empty($product[0])) {
                        $fields = [
                            'seller_id',
                            'title',
                            'short_description',
                            'image',
                            'description',
                            'tags',
                            'product_ids',
                            'selected_products',
                            'has_similar_product',
                            'similar_product_ids',
                            'price',
                            'special_price',
                            'deliverable_type',
                            'deliverable_zones',
                            'pickup_location',
                            'tax',
                            'weight',
                            'height',
                            'length',
                            'breadth',
                            'quantity_step_size',
                            'minimum_order_quantity',
                            'total_allowed_quantity',
                            'attribute',
                            'attribute_value_ids',
                            'sku',
                            'stock',
                            'availability',
                            'cod_allowed',
                            'is_prices_inclusive_tax',
                            'is_returnable',
                            'is_cancelable',
                            'cancelable_till',
                            'minimum_free_delivery_order_qty',
                            'delivery_charges',
                            'other_images'
                        ];

                        foreach ($fields as $index => $field) {
                            if (!empty($row[$index + 1])) {
                                $data[$field] = $row[$index + 1];
                            } else {
                                $data[$field] = $product[0]->{$field};
                            }
                        }

                        if (isset($row[36]) && $row[36] != '') {
                            $other_images = explode(',', $row[36]);
                            $data['other_images'] = json_encode($other_images, 1);
                        }

                        ComboProduct::where('id', $product_id)->update($data);
                    }
                }

                $temp1++;
            }
            fclose($handle);
            return response()->json([
                'error' => 'false',
                'message' => labels('admin_labels.products_updated_successfully', 'Products updated successfully!')
            ]);
        }
    }
    public function delete_selected_data(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:combo_products,id'
        ]);

        foreach ($request->ids as $id) {
            $combo_products = ComboProduct::find($id);

            if ($combo_products) {
                ComboProduct::where('id', $id)->delete();
            }
        }

        return response()->json([
            'error' => false,
            'message' => labels('admin_labels.products_deleted_successfully', 'Selected products deleted successfully!'),
        ]);
    }
}
