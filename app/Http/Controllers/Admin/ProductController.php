<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tax;
use App\Models\User;
use App\Models\Brand;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use Illuminate\Http\Request;
use App\Models\Attribute_values;
use App\Models\Language;
use App\Models\Product_variants;
use App\Models\Product_attributes;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\OrderItems;
use App\Models\UserFcm;
use PhpParser\Node\Expr\FuncCall;

class ProductController extends Controller
{
    public function index()
    {
        $store_id = getStoreId();
        $languages = Language::all();
        $product_deliverability_type = fetchDetails('stores', ['id' => $store_id], 'product_deliverability_type');
        $product_deliverability_type = isset($product_deliverability_type) && !empty($product_deliverability_type) ? $product_deliverability_type[0]->product_deliverability_type : '';

        $attributes = Attribute::where('store_id', $store_id)->where('status', 1)->with('attribute_values')->get();

        $sellers = DB::table('seller_store')
            ->leftJoin('seller_data', 'seller_data.id', '=', 'seller_store.seller_id')
            ->leftJoin('users', 'users.id', '=', 'seller_store.user_id')
            ->select('seller_store.*', 'seller_data.*', 'users.username') // Separate table and column names
            ->where('seller_store.store_id', $store_id)
            ->where('seller_data.status', 1)
            ->where('seller_data.status', 1)
            ->get();

        $brands = Brand::where('status', 1)->where('store_id', $store_id)->orderBy('id', 'desc')->get();

        return view('admin.pages.forms.products', compact('attributes', 'sellers', 'brands', 'product_deliverability_type', 'languages'));
        // return view('admin.pages.forms.products-old', compact('attributes', 'sellers', 'brands', 'product_deliverability_type', 'languages'));
    }
    public function getAttributes(Request $request)
    {
        $category_id = $request->category_id[0] ?? "";
        $store_id = getStoreId();
        $attributes = Attribute::where('store_id', $store_id)
            ->where('status', 1)
            ->where('category_id', $category_id)
            ->with('attribute_values')
            ->get();
        // dd($attributes);
        return response()->json($attributes);
    }
    public function fetchAttributeValuesById()
    {

        if (isset($id) && !empty($id)) {
            $aid = $id;
        } else {
            $aid = $_GET['id'];
        }
        $variant_ids = getAttributeValuesById($aid);
        print_r(json_encode($variant_ids));
    }

    public function fetchVariantsValuesByPid()
    {
        $edit_id = (isset($_GET['edit_id']) && !empty($_GET['edit_id'])) ? $_GET['edit_id'] : '';
        $res = getVariantsValuesByPid($edit_id);
        $response['result'] = $res;
        print_r(json_encode($response));
    }

    public function getVariantsById()
    {
        $variant_ids = json_decode($_GET['variant_ids']);
        $attributes_values = json_decode($_GET['attributes_values']);

        $attr_values = [];
        foreach ($attributes_values as $values) {
            $attr_values = array_merge($attr_values, $values);
        }

        $res = Attribute_values::whereIn('id', $attr_values)->where('status', 1)
            ->select('id', 'value')
            ->get()
            ->toArray();

        $final_variant_ids = [];
        foreach ($variant_ids as $ids) {
            $variant_values = [];
            foreach ($ids as $id) {
                $key = array_search($id, array_column($res, 'id'));
                if ($key !== false) {
                    $variant_values[] = $res[$key];
                }
            }
            $final_variant_ids[] = $variant_values;
        }

        $response['result'] = $final_variant_ids;
        print_r(json_encode($response));
    }

    public function store(Request $request)
    {

        // dd($request);
        $store_id = getStoreId();
        $validator = Validator::make($request->all(), [
            'pro_input_name' => 'required',
            'short_description' => 'required',
            'category_id' => 'required|exists:categories,id',
            'pro_input_image' => 'required',
            'product_type' => 'required',
            'seller_id' => 'required',
            'deliverable_type' => 'required',
            'slug' => generateSlug($request->input('pro_input_name'), 'products'),
        ], [
            'pro_input_name.required' => 'Please add product name.',
            'pro_input_image.required' => 'Please select product image',

        ]);

        // if (request()->has('product_type') && request()->input('product_type') == 'simple_product') {
        //     $validator->sometimes('simple_price', 'required|numeric|gte:' . request()->input('simple_special_price') . '|string', function ($input) {
        //         return true;
        //     },[
        //         'simple_price.gte' => 'The price field must be greater than or equal to'. request()->input('simple_special_price'),

        //     ]);

        //     $validator->sometimes('product_total_stock', 'nullable|required_if:simple_product_stock_status,0|numeric|string', function ($input) {
        //         return true;
        //     });
        // }
        if (request()->has('product_type') && request()->input('product_type') == 'simple_product') {
            $validator->sometimes(
                'simple_price',
                'required|numeric|gte:' . request()->input('simple_special_price') . '|string',
                function ($input) {
                    return true;
                }
            );

            $validator->sometimes(
                'product_total_stock',
                'nullable|required_if:simple_product_stock_status,0|numeric|string',
                function ($input) {
                    return true;
                }
            );
            $validator->setCustomMessages([
                'simple_price.gte' => 'The simple price must be greater than or equal to the special price.',
                'simple_price.required' => 'Please enter a simple price.',
                'simple_price.numeric' => 'Simple price must be a number.',
                'product_total_stock.required_if' => 'Total stock is required when stock status is 0.',
                'product_total_stock.numeric' => 'Total stock must be a number.',
            ]);
        } elseif (request()->has('product_type') && request()->input('product_type') == 'variable_product') {

            if (request()->has('variant_stock_level_type') && request()->input('variant_stock_level_type') == "product_level") {

                $validator->sometimes('total_stock_variant_type', 'required|string', function ($input) {
                    return true;
                });
                $validator->sometimes('variant_stock_status', 'required|string', function ($input) {
                    return true;
                });
                $validator->sometimes('variant_price.*', 'required|numeric', function ($input) {
                    return true;
                });
            }
        }

        if ($validator->fails()) {
            return $request->ajax()
                ? response()->json(['errors' => $validator->errors()->all()], 422)
                : redirect()->back()->withErrors($validator)->withInput();
        } else {
            $stock_type = '';
            if ($request->product_type == 'simple_product') {
                $stock_type = 0;
            }
            if ($request->product_type == 'variable_product') {
                if ($request->variant_stock_level_type == 'product_level') {
                    $stock_type = 1;
                }
            }

            $product_deliverability_type = fetchDetails('stores', ['id' => $store_id], 'product_deliverability_type');
            $product_deliverability_type = isset($product_deliverability_type) && !empty($product_deliverability_type) ? $product_deliverability_type[0]->product_deliverability_type : '';

            $tag_data = isset($request->tags) ? json_decode($request->tags, true) : [];
            $tag_values = array_column($tag_data, 'value');
            $tags = implode(',', $tag_values);
            $zones = implode(',', (array) $request->deliverable_zones);

            $translations = [
                'en' => $request->pro_input_name
            ];
            if (!empty($request['translated_product_name'])) {
                $translations = array_merge($translations, $request['translated_product_name']);
            }
            $translation_descriptions = [
                'en' => $request->short_description
            ];
            if (!empty($request['translated_product_short_description'])) {
                $translation_descriptions = array_merge($translation_descriptions, $request['translated_product_short_description']);
            }

            $data = [
                'name' => json_encode($translations, JSON_UNESCAPED_UNICODE),
                'short_description' => json_encode($translation_descriptions, JSON_UNESCAPED_UNICODE),
                'slug' => generateSlug($translations['en'], 'products', 'slug'),
                'type' => isset($request->product_type) ? $request->product_type : "",
                'tax' => (isset($request->pro_input_tax) && !empty($request->pro_input_tax)) ? implode(',', (array) $request->pro_input_tax) : '',
                'category_id' => isset($request->category_id) ? $request->category_id : '',
                'seller_id' => isset($request->seller_id) ? $request->seller_id : '',
                'made_in' => isset($request->made_in) ? $request->made_in : '',
                'brand' => isset($request->brand) ? $request->brand : '',
                'indicator' => isset($request->indicator) ? $request->indicator : '',
                'image' => isset($request->pro_input_image) ? $request->pro_input_image : '',
                'total_allowed_quantity' => isset($request->total_allowed_quantity) ? $request->total_allowed_quantity : '',
                'minimum_order_quantity' => isset($request->minimum_order_quantity) ? $request->minimum_order_quantity : '',
                'quantity_step_size' => isset($request->quantity_step_size) ? $request->quantity_step_size : '',
                'warranty_period' => isset($request->warranty_period) ? $request->warranty_period : '',
                'guarantee_period' => isset($request->guarantee_period) ? $request->guarantee_period : '',
                'other_images' => isset($request->other_images) ? $request->other_images : '',
                'video_type' => isset($request->video_type) ? $request->video_type : '',
                'video' => (!empty($request->video_type)) ? (($request->video_type == 'youtube' || $request->video_type == 'vimeo') ? $request->video : $request->pro_input_video) : "",
                'tags' => $tags,
                'status' => 1,
                'description' => isset($request->pro_input_description) ? $request->pro_input_description : '',
                'extra_description' => isset($request->extra_input_description) ? $request->extra_input_description : '',
                // 'deliverable_type' => isset($product_deliverability_type) && !empty($product_deliverability_type) && $product_deliverability_type == 'zipcode_wise_deliverability' ? (isset($request->deliverable_type) ? $request->deliverable_type : '') : '',
                'deliverable_type' =>  isset($request->deliverable_type) ? $request->deliverable_type : 0,
                'deliverable_zones' => ($request->deliverable_type == '1' || $request->deliverable_type == '0') ? '' : $zones,
                'hsn_code' => isset($request->hsn_code) ? $request->hsn_code : '',
                'pickup_location' => isset($request->pickup_location) ? $request->pickup_location : '',
                'minimum_free_delivery_order_qty' => isset($request->minimum_free_delivery_order_qty) ? $request->minimum_free_delivery_order_qty : '',
                'delivery_charges' => isset($request->delivery_charges) ? $request->delivery_charges : '',

            ];
            $download_link = (isset($request->download_link_type) && !empty($request->download_link_type)) ? (($request->download_link_type == 'add_link') ? $request->download_link : $request->pro_input_zip) : "";

            $download_type = (isset($request->download_link_type) && !empty($request->download_link_type)) ? $request->download_link_type : "";

            if ($request->product_type == 'simple_product') {
                if (isset($request->simple_product_stock_status) && empty($request->simple_product_stock_status)) {
                    $data['stock_type'] = NULL;
                }

                if (isset($request->simple_product_stock_status) && in_array($request->simple_product_stock_status, array('0', '1'))) {
                    $data['stock_type'] = '0';
                }

                if (isset($request->simple_product_stock_status) && in_array($request->simple_product_stock_status, array('0', '1'))) {
                    if (!empty($request->product_sku)) {
                        $data['sku'] = $request->product_sku;
                    }
                    $data['stock'] = $request->product_total_stock;
                    $data['availability'] = $request->simple_product_stock_status;
                }
            }

            if ((isset($request->variant_stock_status) || $request->variant_stock_status == '' || empty($request->variant_stock_status) || $request->variant_stock_status == ' ') && $request->product_type == 'variable_product') {
                $data['stock_type'] = NULL;
            }
            if (isset($request->variant_stock_level_type) && !empty($request->variant_stock_level_type) && $request->product_type != 'digital_product') {
                $data['stock_type'] = ($request->variant_stock_level_type == 'product_level') ? 1 : 2;
            }

            if ($request->product_type != 'digital_product' && isset($request->is_returnable) && $request->is_returnable != "" && ($request->is_returnable == "on" || $request->is_returnable == '1')) {
                $data['is_returnable'] = '1';
            } else {
                $data['is_returnable'] = '0';
            }

            if ($request->product_type != 'digital_product' && isset($request->is_cancelable) && $request->is_cancelable != "" && ($request->is_cancelable == "on" || $request->is_cancelable == '1')) {
                $data['is_cancelable'] = '1';
                $data['cancelable_till'] = $request->cancelable_till;
            } else {
                $data['is_cancelable'] = '0';
                $data['cancelable_till'] = '';
            }
            if (isset($request->is_attachment_required) && $request->is_attachment_required != "" && ($request->is_attachment_required == "on" || $request->is_attachment_required == '1')) {
                $data['is_attachment_required'] = '1';
            } else {
                $data['is_attachment_required'] = '0';
            }

            if (isset($request->download_allowed) && $request->download_allowed != "" && ($request->download_allowed == "on" || $request->download_allowed == '1')) {
                $data['download_allowed'] = '1';
                $data['download_type'] = $download_type;
                $data['download_link'] = $download_link;
            } else {
                $data['download_allowed'] = '0';
                $data['download_type'] = '';
                $data['download_link'] = '';
            }

            if ($request->product_type != 'digital_product' && isset($request->cod_allowed) && $request->cod_allowed != "" && ($request->cod_allowed == "on" || $request->cod_allowed == '1')) {
                $data['cod_allowed'] = '1';
            } else {
                $data['cod_allowed'] = '0';
            }

            if (isset($request->is_prices_inclusive_tax) && $request->is_prices_inclusive_tax != "" && ($request->is_prices_inclusive_tax == "on" || $request->is_prices_inclusive_tax == '1')) {
                $data['is_prices_inclusive_tax'] = '1';
            } else {
                $data['is_prices_inclusive_tax'] = '0';
            }
            $data['store_id'] = $store_id;
            $variant_images = (!empty($request->variant_images) && isset($request->variant_images)) ? $request->variant_images : [];
            $data['other_images'] = json_encode($request->other_images, 1);
            $product = Product::create($data);
            $product_attribute_data = [
                'product_id' => $product->id,
                'attribute_value_ids' => (isset($request->attribute_values) && !empty($request->attribute_values)) ? strval($request->attribute_values) : '',
            ];
            Product_attributes::create($product_attribute_data);

            if ($request->product_type == 'simple_product') {
                $product_variant_data = [
                    'product_id' => $product->id,
                    'price' => isset($request->simple_price) ? $request->simple_price : '',
                    'special_price' => (isset($request->simple_special_price) && !empty($request->simple_special_price)) ? $request->simple_special_price : '0',
                    'weight' => (isset($request->weight)) ? floatval($request->weight) : 0,
                    'height' => (isset($request->height)) ? $request->height : 0,
                    'breadth' => (isset($request->breadth)) ? $request->breadth : 0,
                    'length' => (isset($request->length)) ? $request->length : 0,
                ];
                Product_variants::create($product_variant_data);
            } elseif ($request->product_type == 'digital_product') {
                $product_variant_data = [
                    'product_id' => $product->id,
                    'price' => isset($request->simple_price) ? $request->simple_price : '',
                    'special_price' => (isset($request->simple_special_price) && !empty($request->simple_special_price)) ? $request->simple_special_price : '0',
                ];
                Product_variants::create($product_variant_data);
            } else {
                $flag = " ";
                if (isset($request->variant_stock_status) && $request->variant_stock_status == '0') {
                    if ($request->variant_stock_level_type == "product_level") {
                        $flag = "product_level";
                        $product_variant_data['product_id'] = $product->id;
                        $product_variant_data['sku'] = isset($request->sku_variant_type) ? $request->sku_variant_type : '';
                        $product_variant_data['stock'] = isset($request->total_stock_variant_type) ? $request->total_stock_variant_type : '';
                        $product_variant_data['availability'] = isset($request->variant_status) ? $request->variant_status : '';
                        $variant_price = $request->variant_price;
                        $variant_special_price = (isset($request->variant_special_price) && !empty($request->variant_special_price)) ? $request->variant_special_price : '0';
                        $variant_weight = $request->weight;
                        $variant_height = (isset($request->height)) ? $request->height : 0.0;
                        $variant_breadth = (isset($request->breadth)) ? $request->breadth : 0.0;
                        $variant_length = (isset($request->length)) ? $request->length : 0.0;
                    } else {
                        $flag = "variant_level";
                        $product_variant_data['product_id'] = $product->id;
                        $variant_price = $request->variant_price;
                        $variant_special_price = (isset($request->variant_special_price) && !empty($request->variant_special_price)) ? $request->variant_special_price : '0';
                        $variant_sku = $request->variant_sku;
                        $variant_total_stock = $request->variant_total_stock;
                        $variant_stock_status = $request->variant_level_stock_status;
                        $variant_weight = $request->weight;
                        $variant_height = (isset($request->height)) ? $request->height : 0.0;
                        $variant_breadth = (isset($request->breadth)) ? $request->breadth : 0.0;
                        $variant_length = (isset($request->length)) ? $request->length : 0.0;
                    }
                } else {
                    $variant_price = $request->variant_price;
                    $variant_special_price = (isset($request->variant_special_price) && !empty($request->variant_special_price)) ? $request->variant_special_price : '0';
                    $variant_weight = $request->weight;
                    $variant_height = (isset($request->height)) ? $request->height : 0.0;
                    $variant_breadth = (isset($request->breadth)) ? $request->breadth : 0.0;
                    $variant_length = (isset($request->length)) ? $request->length : 0.0;
                }

                if (!empty($request->variants_ids)) {

                    $variants_ids = $request->variants_ids;
                    for ($i = 0; $i < count($variants_ids); $i++) {
                        $value = str_replace(' ', ',', trim($variants_ids[$i]));
                        if ($flag == "variant_level") {
                            $product_variant_data['product_id'] = $product->id;
                            $product_variant_data['price'] = $variant_price[$i];
                            $product_variant_data['special_price'] = (isset($variant_special_price[$i]) && !empty($variant_special_price[$i])) ? $variant_special_price[$i] : '0';
                            $product_variant_data['weight'] = $variant_weight[$i];
                            $product_variant_data['height'] = $variant_height[$i];
                            $product_variant_data['breadth'] = $variant_breadth[$i];
                            $product_variant_data['length'] = $variant_length[$i];
                            $product_variant_data['sku'] = $variant_sku[$i];
                            $product_variant_data['stock'] = $variant_total_stock[$i];
                            $product_variant_data['availability'] = $variant_stock_status[$i];
                        } else {
                            $product_variant_data['product_id'] = $product->id;
                            $product_variant_data['price'] = $variant_price[$i];
                            $product_variant_data['special_price'] = (isset($variant_special_price[$i]) && !empty($variant_special_price[$i])) ? $variant_special_price[$i] : '0';
                            $product_variant_data['weight'] = $variant_weight[$i];
                            $product_variant_data['height'] = $variant_height[$i];
                            $product_variant_data['breadth'] = $variant_breadth[$i];
                            $product_variant_data['length'] = $variant_length[$i];
                        }
                        if (isset($variant_images[$i]) && !empty($variant_images[$i])) {
                            $product_variant_data['images'] = json_encode($variant_images[$i]);
                        } else {
                            $product_variant_data['images'] = '[]';
                        }
                        $product_variant_data['attribute_value_ids'] = $value;

                        Product_variants::create($product_variant_data);
                    }
                }
            }
            return response()->json([
                'error' => false,
                'message' => labels('admin_labels.product_added_successfully', 'Product added successfully.'),
                'location' => route('admin.products.manage_product')
            ]);
        }
    }

    public function getBrands(Request $request)
    {
        $search = trim($request->search) ?? "";
        $language_code = get_language_code();
        $store_id = getStoreId();
        $brands = Brand::where('name', 'LIKE', '%' . $search . '%')
            ->where('store_id', $store_id)
            ->where('status', 1)
            ->where('status', 1)
            ->orderBy('id', 'desc')
            ->get();
        $data = array();
        foreach ($brands as $brand) {
            $data[] = array("id" => $brand->id, "text" => getDynamicTranslation('brands', 'name', $brand->id, $language_code));
        }

        return $data;
    }


    function getCountries(Request $request)
    {
        $search_term = trim($request->search);

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

    public function getProductdetails(Request $request)
    {
        $store_id = getStoreId();
        $search = trim($request->input('search'));
        $limit = (int) $request->input('limit', 30);
        $seller_id = $request->input('seller_id') ?? '';
        $page = (int) $request->input('page', 1);
        $offset = ($page - 1) * $limit;

        $query = Product::where('name', 'like', '%' . $search . '%')
            ->where('store_id', $store_id)
            ->where('status', 1);

        // Conditionally add the seller_id where condition
        if ($seller_id) {
            $query->where('seller_id', $seller_id);
        }
        $query->orderBy('id', 'desc');
        $totalCount = $query->count();
        $language_code = get_language_code();
        // Apply pagination
        $products = $query->skip($offset)
            ->limit($limit)
            ->get(['id', 'name']);

        $response = [
            'total' => $totalCount,
            'results' => $products->map(function ($product) use ($language_code) {
                return [
                    'id' => $product->id,
                    'text' => getDynamicTranslation('products', 'name', $product->id, $language_code),
                ];
            }),
        ];

        return response()->json($response);
    }

    public function getProductdetailsForCombo(Request $request)
    {
        $store_id = getStoreId();
        $search = trim($request->input('search'));
        $limit = (int) $request->input('limit', 10);
        $seller_id = isset($request->seller_id) && $request->seller_id != '' ? $request->seller_id : '';

        // Base query to fetch products
        $query = DB::table('products')
            ->where('products.name', 'like', '%' . $search . '%')
            ->where('products.store_id', $store_id)
            ->where('products.status', 1)
            ->whereIn('products.type', ['simple_product', 'variable_product']);


        // Conditionally add the seller_id where condition
        if ($seller_id) {
            $query->where('products.seller_id', $seller_id);
        }

        // Get products with a limit
        $products = $query->limit($limit)
            ->get(['products.id', 'products.name', 'products.type']);

        // Get the total count of matching products
        $totalCount = DB::table('products')
            ->where('products.name', 'like', '%' . $search . '%')
            ->where('products.store_id', $store_id)
            ->where('products.status', 1)
            ->whereIn('products.type', ['simple_product', 'variable_product'])
            ->count();
        $language_code = get_language_code();
        $results = [];

        // Iterate through the products and check if they have variants
        foreach ($products as $product) {
            // For variable products, fetch variants
            if ($product->type == 'variable_product') {
                $variants = DB::table('product_variants')
                    ->where('product_variants.product_id', $product->id)
                    ->get(['product_variants.id', 'product_variants.attribute_value_ids']);

                foreach ($variants as $variant) {
                    // Get attribute values for this variant
                    $attributeValueIds = explode(',', $variant->attribute_value_ids);
                    $attributeNames = DB::table('attribute_values')
                        ->whereIn('id', $attributeValueIds)
                        ->pluck('value')
                        ->toArray();

                    // Concatenate the variant name (e.g., "Red, Large")
                    $variantName = implode(', ', $attributeNames);

                    $results[] = [
                        'id' => $variant->id, // Variant ID
                        'text' => getDynamicTranslation('products', 'name', $product->id, $language_code) . ' - ' . $variantName,
                    ];
                }
            } else {
                // If the product is simple, fetch the associated single variant
                $variant = DB::table('product_variants')
                    ->where('product_variants.product_id', $product->id)
                    ->first(['product_variants.id']);

                if ($variant) {
                    $results[] = [
                        'id' => $variant->id, // Always return the variant ID
                        'text' => getDynamicTranslation('products', 'name', $product->id, $language_code), // Simple product name
                    ];
                }
            }
        }

        // Prepare the response
        $response = [
            'total' => $totalCount,
            'results' => $results,
        ];

        return response()->json($response);
    }


    public function getDigitalProductData(Request $request)
    {
        $store_id = getStoreId();

        $search = request('search', null);
        $offset = request('offset', 0);

        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'ASC');


        if (!empty($search)) {
            $multipleWhere = [
                'products.name' => $search,
                'products.id' => $search,
                'products.description' => $search,
                'products.short_description' => $search,
            ];
        }

        $query = Product::query()
            ->select('product_variants.id AS id', 'seller_store.store_name', 'products.id as pid', 'products.rating', 'products.no_of_ratings', 'products.name', 'products.type', 'products.image', 'products.status', 'products.brand', 'product_variants.price', 'product_variants.special_price', 'product_variants.stock')
            ->join('seller_store', 'seller_store.seller_id', '=', 'products.seller_id')
            ->join('product_variants', 'product_variants.product_id', '=', 'products.id')
            ->where('products.type', 'digital_product')
            ->where('products.store_id', $store_id)
            ->where('products.status', 1);


        if (!empty($search)) {
            $query->where(function ($query) use ($multipleWhere) {
                foreach ($multipleWhere as $column => $value) {
                    $query->orWhere($column, 'like', '%' . $value . '%');
                }
            });
        }


        $query->groupBy('products.id');
        $total = $query->count();

        $query->orderBy('products.' . $sort, $order);

        $products = $query->limit($limit)
            ->offset($offset)
            ->get();

        $rows = [];
        $language_code = get_language_code();
        foreach ($products as $product) {
            $attr_values = getVariantsValuesByPid($product->pid);
            // dd($product->pid);
            $row = [
                'id' => $product->pid,
                'varaint_id' => $product->id,
                // 'text' => $product->name,
                'text' => getDynamicTranslation('products', 'name', $product->pid, $language_code),

            ];

            $rows[] = $row;
        }

        $bulkData = [
            'total' => $total,
            'results' => $rows,
        ];

        return response()->json($bulkData);
    }

    public function list()
    {
        $store_id = getStoreId();

        $search = trim(request('search'));
        // dd($search);
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $limit = request("limit");
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $settings = getSettings('system_settings', true);
        $settings = json_decode($settings, true);
        $low_stock_limit = isset($settings['low_stock_limit']) ? $settings['low_stock_limit'] : 5;

        $query = Product::query();
        $query->select('products.id AS id', 'categories.id as category_id', 'brands.id as brand_id', 'products.brand', 'categories.name as category_name', 'brands.name as brand_name', 'seller_store.store_name', 'products.id as pid', 'products.rating', 'products.no_of_ratings', 'products.category_id', 'products.name', 'products.type', 'products.image', 'products.status', 'products.brand', 'product_variants.price', 'product_variants.special_price', 'product_variants.stock')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('brands', 'products.brand', '=', 'brands.id')
            ->leftJoin('seller_data', 'seller_data.id', '=', 'products.seller_id')
            ->join('seller_store', 'seller_store.seller_id', '=', 'products.seller_id')
            ->join('product_variants', 'product_variants.product_id', '=', 'products.id')
            ->where('products.store_id', $store_id);

        // if (request()->filled('search')) {
        //     $search = trim(request('search'));
        //     $query->where(function ($q) use ($search) {
        //         $q->where('products.id', (string) $search)
        //             ->orWhere('products.name', 'LIKE', "%$search%")
        //             ->orWhere('products.description', 'LIKE', "%$search%")
        //             ->orWhere('products.short_description', 'LIKE', "%$search%")
        //             ->orWhere('categories.name', 'LIKE', "%$search%");
        //     });
        // }
        $language_code = get_language_code();
        if (request()->filled('search')) {
            $search = trim(request('search'));
            $query->where(function ($q) use ($search, $language_code) {
                $q->where('products.id', (string) $search)
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(products.name, '$.\"$language_code\"')) LIKE ?", ["%$search%"])
                    ->orWhere('products.description', 'LIKE', "%$search%")
                    ->orWhere('products.short_description', 'LIKE', "%$search%")
                    ->orWhere('categories.name', 'LIKE', "%$search%");
            });
        }

        if (request()->filled('category_id')) {
            $query->Where(function ($q) {
                $q->Where('products.category_id', request('category_id'))
                    ->orWhere('categories.parent_id', request('category_id'));
            });
        }

        if (request()->has('flag') && request('flag') === 'low') {
            $query->where(function ($q) use ($low_stock_limit) {
                $q->whereNotNull('products.stock_type')
                    ->where('products.stock', '<=', $low_stock_limit)
                    ->where('products.availability', '=', 1)
                    ->orWhere('product_variants.stock', '<=', $low_stock_limit)
                    ->where('product_variants.availability', '=', 1);
            });
        }

        if (request()->filled('seller_id')) {
            $query->where('products.seller_id', request('seller_id'));
        }

        if (request()->filled('product_type')) {
            $query->where('products.type', request('product_type'));
        }
        if (request()->filled('brand_id')) {
            $query->where('products.brand', request('brand_id'));
        }


        if (request()->filled('status')) {
            $query->where('products.status', request('status'));
        }

        if (request()->has('flag') && request('flag') === 'sold') {
            $query->where(function ($q) {
                $q->whereNotNull('products.stock_type')
                    ->where('products.stock', '=', 0)
                    ->where('products.availability', '=', 0)
                    ->orWhere('product_variants.stock', '=', 0)
                    ->where('product_variants.availability', '=', 0);
            });
        }


        $total = $query->groupBy('products.id')->get()->count();

        $products = $query->groupBy('pid')
            ->orderBy($sort, $order)
            ->skip($offset)
            ->take($limit)
            ->get();

        $products = $products->map(function ($p) use ($language_code) {
            // dd($p);
            $store_id = getStoreId();
            $edit_url = route('admin.products.edit', $p->pid); // Use 'pid' as you've aliased 'products.id as pid' in the select statement.
            $show_url = route('admin.product.show', $p->id);
            $delete_url = route('admin.products.destroy', $p->pid); // Use 'pid' here as well.
            $attr_values = getVariantsValuesByPid($p->pid);

            $action = '<div class="dropdown bootstrap-table-dropdown">
                    <a href="#" class="text-dark" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="bx bx-dots-horizontal-rounded"></i>
                        </a>
                        <div class="dropdown-menu table_dropdown product_action_dropdown" aria-labelledby="dropdownMenuButton">
                            <a class="dropdown-item dropdown_menu_items" href="' . $edit_url . '"><i class="bx bx-pencil mx-2"></i> Edit</a>
                            <a class="dropdown-item delete-data dropdown_menu_items" data-url="' . $delete_url . '"><i class="bx bx-trash mx-2"></i> Delete</a>
                            <a class="dropdown-item dropdown_menu_items" href="' . $show_url . '"><i class="bx bxs-show mx-2"></i>View</a>
                        </div>
                    </div>';
            $variations = '';

            foreach ($attr_values as $variants) {
                if (isset($attr_values[0]->attr_name)) {


                    if (!empty($variations)) {
                        $variations .= '---------------------<br>';
                    }
                    $attr_name = explode(',', $variants->attr_name);
                    $variant_values = explode(',', $variants->variant_values);
                    for ($i = 0; $i < count($attr_name); $i++) {

                        $variations .= '<b>' . $attr_name[$i] . '</b> : ' . $variant_values[$i] . '&nbsp;&nbsp;<b> Variant id : </b>' . $variants->id . '<br>';
                    }
                }
            }
            $image = route('admin.dynamic_image', [
                'url' => getMediaImageUrl($p->image),
                'width' => 60,
                'quality' => 90
            ]);

            return [
                'id' => $p->id,
                'name' => getDynamicTranslation('products', 'name', $p->id, $language_code) . '<br><small>' . ucwords(str_replace('_', ' ', $p->type)) . '</small><br><small> By </small><b>' . $p->store_name . '</b>',
                'brand' => getDynamicTranslation('brands', 'name', $p->brand_id, $language_code),
                'category_name' => getDynamicTranslation('categories', 'name', $p->category_id, $language_code),
                'rating' => $p->rating,
                'variations' => $variations,
                'image' => '<div class="d-flex align-items-center"><a href="' . getMediaImageUrl($p->image) . '" data-lightbox="image-' . $p->id . '"><img src=' . $image . ' class="rounded mx-2"></a></div>',
                'status' => '<select class="form-select status_dropdown change_toggle_status ' .
                    ($p->status == 1 ? 'active_status' : ($p->status == 2 ? 'not_approved_status bg-gray-500' : 'inactive_status')) .
                    '" data-id="' . $p->id . '" data-url="/admin/products/update_status/' . $p->id . '" aria-label="" data-toggle-status="' . $p->status . '">
                <option value="1" ' . ($p->status == 1 ? 'selected' : '') . '>Active</option>
                <option value="0" ' . ($p->status == 0 ? 'selected' : '') . '>Deactive</option>' .
                    ($p->status == 2 ? '<option value="2" ' . ($p->status == 2 ? 'selected' : '') . '>Not Approved</option>' : '') .
                    '</select>',
                'operate' => $action,

            ];
        });

        return response()->json([
            "rows" => $products,
            "total" => $total,
        ]);
    }

    public function fetchAttributesById(request $request)
    {
        $id = $request->edit_id;

        $variants = getVariantsValuesByPid($id);

        $res['attr_values'] = getAttributeValuesByPid($id);
        $res['pre_selected_variants_names'] = (!empty($variants)) ? $variants[0]->attr_name : null;

        $res['pre_selected_variants_ids'] = $variants;

        $response['result'] = $res;
        return $response;
    }
    public function destroy($id)
    {
        $product = Product::find($id);
        if ($product) {
            // Delete associated variants
            Product_variants::where('product_id', $id)->delete();

            // Delete the product
            Product::where('id', $id)->delete();

            return response()->json([
                'error' => false,
                'message' => labels('admin_labels.product_deleted_successfully', 'Product deleted successfully!')
            ]);
        } else {
            return response()->json([
                'error' => true,
                'message' => labels('admin_labels.data_not_found', 'Data Not Found')
            ]);
        }
    }

    public function manageProduct()
    {
        return view('admin.pages.tables.manage_products');
    }
    public function edit($data)
    {
        $store_id = getStoreId();

        $language_code = get_language_code();
        $data = Product::where('store_id', $store_id)
            ->find($data);
        if ($data === null || empty($data)) {
            return view('admin.pages.views.no_data_found');
        } else {
            $product_variants = getVariantsValuesByPid($data->id);


            $product_deliverability_type = fetchDetails('stores', ['id' => $store_id], 'product_deliverability_type');
            $product_deliverability_type = isset($product_deliverability_type) && !empty($product_deliverability_type) ? $product_deliverability_type[0]->product_deliverability_type : '';


            $attributes = Attribute::with('attribute_values')->where('store_id', $store_id)->get();

            $languages = Language::all();
            $sellers = DB::table('seller_store')
                ->leftJoin('seller_data', 'seller_data.id', '=', 'seller_store.seller_id')
                ->leftJoin('users', 'users.id', '=', 'seller_store.user_id')
                ->select('seller_store.*', 'seller_data.*', 'users.username') // Separate table and column names
                ->where('seller_store.store_id', $store_id)
                ->where('seller_data.status', 1)
                ->get();

            $brands = Brand::where('status', 1)->where('store_id', $store_id)->orderBy('id', 'desc')->get();
            $brand_name = fetchDetails('brands', ['id' => $data->brand], '*');
            $brand_name = $brand_name[0]->name ?? '';

            $country = fetchDetails('countries', ['name' => $data->made_in], '*');

            $seller_id = fetchDetails('products', ['id' => $data->id], 'seller_id')[0]->seller_id;

            $shipping_data = fetchDetails('pickup_locations', ['status' => 1, 'seller_id' => $seller_id], ['id', 'pickup_location']);
            // return view('admin.pages.forms.update_product-old', compact('data', 'attributes', 'sellers', 'brands', 'product_variants', 'country', 'shipping_data', 'brand_name', 'product_deliverability_type'));
            return view('admin.pages.forms.update_product', compact('data', 'attributes', 'sellers', 'brands', 'product_variants', 'country', 'shipping_data', 'brand_name', 'product_deliverability_type', 'languages', 'language_code'));
        }
    }
    public function update(Request $request, $data)
    {

        $product_details = fetchdetails('products', ['id' => $data], ['name', 'slug', 'short_description']);

        $store_id = getStoreId();
        $validator = Validator::make($request->all(), [
            'pro_input_name' => 'required',
            'short_description' => 'required',
            'category_id' => 'required|exists:categories,id',
            'pro_input_image' => 'required',
            'product_type' => 'required',
            'seller_id' => 'required',
            'deliverable_type' => 'required',
        ]);


        // if (request()->has('product_type') && request()->input('product_type') == 'simple_product') {
        //     $validator->sometimes('simple_price', 'required|numeric|gte:' . request()->input('simple_special_price') . '|string', function ($input) {
        //         return true;
        //     });

        //     $validator->sometimes('product_total_stock', 'nullable|required_if:simple_product_stock_status,0|numeric|string', function ($input) {
        //         return true;
        //     });
        // }
        if (request()->has('product_type') && request()->input('product_type') == 'simple_product') {
            $validator->sometimes(
                'simple_price',
                'required|numeric|gte:' . request()->input('simple_special_price') . '|string',
                function ($input) {
                    return true;
                }
            );

            $validator->sometimes(
                'product_total_stock',
                'nullable|required_if:simple_product_stock_status,0|numeric|string',
                function ($input) {
                    return true;
                }
            );
            $validator->setCustomMessages([
                'simple_price.gte' => 'The price must be greater than or equal to the special price.',
                'simple_price.required' => 'Please enter a simple price.',
                'simple_price.numeric' => 'Simple price must be a number.',
                'product_total_stock.required_if' => 'Total stock is required when stock status is 0.',
                'product_total_stock.numeric' => 'Total stock must be a number.',
            ]);
        }

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->all(),
                'data' => [],
            ]);
        } else {
            $stock_type = '';
            if ($request->product_type == 'simple_product') {
                $stock_type = 0;
            }
            if ($request->product_type == 'variable_product') {
                if ($request->variant_stock_level_type == 'product_level') {
                    $stock_type = 1;
                }
            }

            $product_deliverability_type = fetchDetails('stores', ['id' => $store_id], 'product_deliverability_type');
            $product_deliverability_type = isset($product_deliverability_type) && !empty($product_deliverability_type) ? $product_deliverability_type[0]->product_deliverability_type : '';

            $tag_data = isset($request->tags) ? json_decode($request->tags, true) : [];
            $tag_values = array_column($tag_data, 'value');
            $tags = implode(',', $tag_values);
            $zones = implode(',', (array) $request->deliverable_zones);


            $new_name = $request->pro_input_name;
            $current_name = $product_details[0]->name;
            $current_slug = $product_details[0]->slug;
            // dd($product_details[0]->name);
            $translations = json_decode($product_details[0]->name, true) ?? [];
            $translation_descriptions = json_decode($product_details[0]->short_description, true) ?? [];

            $translations['en'] = $request->pro_input_name;
            $translation_descriptions['en'] = $request->short_description;

            if (!empty($request->translated_product_name)) {
                $translations = array_merge($translations, $request->translated_product_name);
            }
            if (!empty($request->translated_product_short_description)) {
                $translation_descriptions = array_merge($translation_descriptions, $request->translated_product_short_description);
            }

            $product_data = [
                'name' => json_encode($translations, JSON_UNESCAPED_UNICODE),
                'short_description' => json_encode($translation_descriptions, JSON_UNESCAPED_UNICODE),
                'slug' => generateSlug($new_name, 'products', 'slug', $current_slug, $current_name),
                'type' => isset($request->product_type) ? $request->product_type : "",
                'tax' => (isset($request->pro_input_tax) && !empty($request->pro_input_tax)) ? implode(',', (array) $request->pro_input_tax) : '',
                'category_id' => isset($request->category_id) ? $request->category_id : '',
                'seller_id' => isset($request->seller_id) ? $request->seller_id : '',
                'made_in' => isset($request->made_in) ? $request->made_in : '',
                'brand' => isset($request->brand) ? $request->brand : '',
                'indicator' => isset($request->indicator) ? $request->indicator : '',
                'image' => isset($request->pro_input_image) ? $request->pro_input_image : '',
                'total_allowed_quantity' => isset($request->total_allowed_quantity) ? $request->total_allowed_quantity : '',
                'minimum_order_quantity' => isset($request->minimum_order_quantity) ? $request->minimum_order_quantity : '',
                'quantity_step_size' => isset($request->quantity_step_size) ? $request->quantity_step_size : '',
                'warranty_period' => isset($request->warranty_period) ? $request->warranty_period : '',
                'guarantee_period' => isset($request->guarantee_period) ? $request->guarantee_period : '',
                'other_images' => isset($request->other_images) ? $request->other_images : '',
                'video_type' => isset($request->video_type) ? $request->video_type : '',
                'video' => (!empty($request->video_type)) ? (($request->video_type == 'youtube' || $request->video_type == 'vimeo') ? $request->video : $request->pro_input_video) : "",
                'tags' => $tags,
                'status' => 1,
                'description' => isset($request->pro_input_description) ? $request->pro_input_description : '',
                'extra_description' => isset($request->extra_input_description) ? $request->extra_input_description : '',
                'deliverable_type' => isset($request->deliverable_type) ? $request->deliverable_type : '',
                'deliverable_zones' => ($request->deliverable_type == '1' || $request->deliverable_type == '0') ? '' : $zones,
                'hsn_code' => isset($request->hsn_code) ? $request->hsn_code : '',
                'pickup_location' => isset($request->pickup_location) ? $request->pickup_location : '',
                'minimum_free_delivery_order_qty' => isset($request->minimum_free_delivery_order_qty) ? $request->minimum_free_delivery_order_qty : '',
                'delivery_charges' => isset($request->delivery_charges) ? $request->delivery_charges : '',
            ];
            $download_link = (isset($request->download_link_type) && !empty($request->download_link_type)) ? (($request->download_link_type == 'add_link') ? $request->download_link : $request->pro_input_zip) : "";

            $download_type = (isset($request->download_link_type) && !empty($request->download_link_type)) ? $request->download_link_type : "";

            if ($request->product_type == 'simple_product') {

                if ((empty($request->simple_product_stock_status) || $request->simple_product_stock_status == null)) {
                    $product_data['stock_type'] = NULL;
                }

                if (isset($request->simple_product_stock_status) && in_array($request->simple_product_stock_status, array('0', '1'))) {
                    $product_data['stock_type'] = '0';
                }

                if (isset($request->simple_product_stock_status) && in_array($request->simple_product_stock_status, array('0', '1'))) {
                    if (!empty($request->product_sku)) {
                        $product_data['sku'] = $request->product_sku;
                    }
                    $product_data['stock'] = $request->product_total_stock;
                    $product_data['availability'] = $request->simple_product_stock_status;
                }
            }

            if (($request->variant_stock_status !== '0' || $request->variant_stock_status == '') && $request->variant_stock_status !== null) {

                $product_data['stock_type'] = NULL;
            }
            if (isset($request->variant_stock_level_type) && !empty($request->variant_stock_level_type) && $request->product_type != 'digital_product') {

                $product_data['stock_type'] = ($request->variant_stock_level_type == 'product_level') ? 1 : 2;
            }



            if ($request->product_type != 'digital_product' && isset($request->is_returnable) && $request->is_returnable != "" && ($request->is_returnable == "on" || $request->is_returnable == '1')) {
                $product_data['is_returnable'] = '1';
            } else {
                $product_data['is_returnable'] = '0';
            }

            if ($request->product_type != 'digital_product' && isset($request->is_cancelable) && $request->is_cancelable != "" && ($request->is_cancelable == "on" || $request->is_cancelable == '1')) {
                $product_data['is_cancelable'] = '1';
                $product_data['cancelable_till'] = $request->cancelable_till;
            } else {
                $product_data['is_cancelable'] = '0';
                $product_data['cancelable_till'] = '';
            }
            if (isset($request->is_attachment_required) && $request->is_attachment_required != "" && ($request->is_attachment_required == "on" || $request->is_attachment_required == '1')) {
                $product_data['is_attachment_required'] = '1';
            } else {
                $product_data['is_attachment_required'] = '0';
            }

            if (isset($request->download_allowed) && $request->download_allowed != "" && ($request->download_allowed == "on" || $request->download_allowed == '1')) {
                $product_data['download_allowed'] = '1';
                $product_data['download_type'] = $download_type;
                $product_data['download_link'] = $download_link;
            } else {
                $product_data['download_allowed'] = '0';
                $product_data['download_type'] = '';
                $product_data['download_link'] = '';
            }

            if ($request->product_type != 'digital_product' && isset($request->cod_allowed) && $request->cod_allowed != "" && ($request->cod_allowed == "on" || $request->cod_allowed == '1')) {
                $product_data['cod_allowed'] = '1';
            } else {
                $product_data['cod_allowed'] = '0';
            }

            if (isset($request->is_prices_inclusive_tax) && $request->is_prices_inclusive_tax != "" && ($request->is_prices_inclusive_tax == "on" || $request->is_prices_inclusive_tax == '1')) {
                $product_data['is_prices_inclusive_tax'] = '1';
            } else {
                $product_data['is_prices_inclusive_tax'] = '0';
            }
            $product_data['store_id'] = $store_id;

            $variant_images = (!empty($request->variant_images) && isset($request->variant_images)) ? $request->variant_images : [];

            $product_data['other_images'] = json_encode($request->other_images, 1);

            $product = Product::where('id', $data)->update($product_data);


            $product_attribute_data = [
                'product_id' => $data,
                'attribute_value_ids' => (isset($request->attribute_values) && !empty($request->attribute_values)) ? strval($request->attribute_values) : '',
            ];
            Product_attributes::where('product_id', $data)->update($product_attribute_data);

            if ($request->product_type == 'simple_product') {
                $product_variant_data = [
                    'product_id' => $data,
                    'price' => isset($request->simple_price) ? $request->simple_price : '',
                    'special_price' => (isset($request->simple_special_price) && !empty($request->simple_special_price)) ? $request->simple_special_price : '0',
                    'weight' => (isset($request->weight)) ? floatval($request->weight) : 0,
                    'height' => (isset($request->height)) ? $request->height : 0,
                    'breadth' => (isset($request->breadth)) ? $request->breadth : 0,
                    'length' => (isset($request->length)) ? $request->length : 0,
                ];

                Product_variants::where('product_id', $data)->update($product_variant_data);
            } elseif ($request->product_type == 'digital_product') {
                $product_variant_data = [
                    'product_id' => $data,
                    'price' => isset($request->simple_price) ? $request->simple_price : '',
                    'special_price' => (isset($request->simple_special_price) && !empty($request->simple_special_price)) ? $request->simple_special_price : '0',
                ];
                Product_variants::where('product_id', $data)->update($product_variant_data);
            } else {

                $flag = " ";
                if (($request->variant_stock_status == '0') || $request->variant_stock_status == '') {

                    if ($request->variant_stock_level_type == "product_level") {
                        $flag = "product_level";
                        $product_variant_data['product_id'] = $data;
                        $product_variant_data['sku'] = isset($request->sku_variant_type) ? $request->sku_variant_type : '';
                        $product_variant_data['stock'] = isset($request->total_stock_variant_type) ? $request->total_stock_variant_type : '';
                        $product_variant_data['availability'] = isset($request->variant_status) ? $request->variant_status : '';
                        $variant_price = $request->variant_price;
                        $variant_special_price = (isset($request->variant_special_price) && !empty($request->variant_special_price)) ? $request->variant_special_price : '0';
                        $variant_weight = $request->weight;
                        $variant_height = (isset($request->height)) ? $request->height : 0.0;
                        $variant_breadth = (isset($request->breadth)) ? $request->breadth : 0.0;
                        $variant_length = (isset($request->length)) ? $request->length : 0.0;
                    } else {

                        $flag = "variant_level";
                        $product_variant_data['product_id'] = $data;
                        $variant_price = $request->variant_price;
                        $variant_special_price = (isset($request->variant_special_price) && !empty($request->variant_special_price)) ? $request->variant_special_price : '0';
                        $variant_sku = $request->variant_sku;
                        $variant_total_stock = $request->variant_total_stock;
                        $variant_stock_status = $request->variant_level_stock_status;
                        $variant_weight = $request->weight;
                        $variant_height = (isset($request->height)) ? $request->height : 0.0;
                        $variant_breadth = (isset($request->breadth)) ? $request->breadth : 0.0;
                        $variant_length = (isset($request->length)) ? $request->length : 0.0;
                    }
                } else {

                    $variant_price = $request->variant_price;
                    $variant_special_price = (isset($request->variant_special_price) && !empty($request->variant_special_price)) ? $request->variant_special_price : '0';
                    $variant_weight = $request->weight;
                    $variant_height = (isset($request->height)) ? $request->height : 0.0;
                    $variant_breadth = (isset($request->breadth)) ? $request->breadth : 0.0;
                    $variant_length = (isset($request->length)) ? $request->length : 0.0;
                }

                if (!empty($request->variants_ids)) {
                    $variants_ids = $request->variants_ids;

                    //first all the created variants and then add new variants
                    Product_variants::where('product_id', $data)->delete();
                    for ($i = 0; $i < count($variants_ids); $i++) {
                        $value = str_replace(' ', ',', trim($variants_ids[$i]));
                        if ($flag == "variant_level") {

                            $product_variant_data['price'] = $variant_price[$i];
                            $product_variant_data['special_price'] = (isset($variant_special_price[$i]) && !empty($variant_special_price[$i])) ? $variant_special_price[$i] : '0';
                            $product_variant_data['weight'] = $variant_weight[$i];
                            $product_variant_data['height'] = $variant_height[$i];
                            $product_variant_data['breadth'] = $variant_breadth[$i];
                            $product_variant_data['length'] = $variant_length[$i];
                            $product_variant_data['sku'] = $variant_sku[$i] ?? "";
                            $product_variant_data['stock'] = $variant_total_stock[$i] ?? "";
                            $product_variant_data['availability'] = $variant_stock_status[$i] ?? "";
                        } else {

                            $product_variant_data['sku'] = isset($request->sku_variant_type) ? $request->sku_variant_type : '';
                            $product_variant_data['stock'] = isset($request->total_stock_variant_type) ? $request->total_stock_variant_type : '';
                            $product_variant_data['availability'] = isset($request->variant_status) ? $request->variant_status : '';
                            $product_variant_data['price'] = $variant_price[$i];
                            $product_variant_data['special_price'] = (isset($variant_special_price[$i]) && !empty($variant_special_price[$i])) ? $variant_special_price[$i] : '0';
                            $product_variant_data['weight'] = isset($variant_weight[$i]) && !empty($variant_weight[$i]) ? $variant_weight[$i] : "";
                            $product_variant_data['height'] = isset($variant_height[$i]) && !empty($variant_height[$i]) ? $variant_height[$i] : "";
                            $product_variant_data['breadth'] = isset($variant_breadth[$i]) && !empty($variant_breadth[$i]) ? $variant_breadth[$i] : "";
                            $product_variant_data['length'] = isset($variant_length[$i]) && !empty($variant_length[$i]) ? $variant_length[$i] : "";
                        }
                        if (isset($variant_images[$i]) && !empty($variant_images[$i])) {
                            $product_variant_data['images'] = json_encode($variant_images[$i]);
                        } else {
                            $product_variant_data['images'] = '[]';
                        }
                        $product_variant_data['attribute_value_ids'] = $value;


                        Product_variants::create($product_variant_data);
                    }
                }
            }
            return response()->json([
                'error' => false,
                'message' =>
                labels('admin_labels.product_updated_successfully', 'Product updated successfully.'),
                'location' => route('admin.products.manage_product')
            ]);
        }
    }

    public function update_status($id, Request $request)
    {
        $status = $request->status ?? "0";
        $product = Product::findOrFail($id);
        $product_id = $product->id;
        $seller_id = Seller::where('id', $product->seller_id)->value('user_id');
        $user = User::where('id', $seller_id)->select('fcm_id', 'username')->first();
        $store_id = $product->store_id;
        // Check if the new status is different from the current status
        $results = UserFcm::join('users', 'user_fcm.user_id', '=', 'users.id')
            ->where('user_fcm.user_id', $seller_id)
            ->where('users.is_notification_on', 1)
            ->select('user_fcm.fcm_id')
            ->get();
        if ($product->status !== $status) {
            $product->status = $status;
            $product->save();
            $fcm_ids = array();
            // Send notification (you need to implement this method)
            $title = "Product status changed";
            $message = "Hello dear " . $user->username . " your product status has been changed";
            $fcmMsg = array(
                'title' => "$title",
                'body' => "$message",
                'type' => "regular_product",
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

    public function deleteImage(Request $request)
    {

        $response['is_deleted'] = deleteImage($request['id'], $request['path'], $request['field'], $request['img_name'], $request['table_name'], $request['isjson']);
        return response()->json([$response]);
    }

    public function bulk_upload()
    {
        return view('admin.pages.forms.product_bulk_upload');
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
        // dd($uploaded_file);
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
                // dd($row);


                if ($temp != 0) {
                    if (empty($row[0])) {
                        return response()->json([
                            'error' => 'true',
                            'message' => labels('admin_labels.category_id_empty', 'Category id is empty at row ')
                                . $row[0]
                        ]);
                    }
                    if ($row[2] != 'simple_product' && $row[2] != 'variable_product') {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.product_type_invalid_at_row', 'Product type is invalid at row') . $temp]);
                    }

                    if (empty($row[4])) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.name_is_empty_at_row', 'Name is empty at row') . $temp]);
                    }

                    if (!empty($row[7]) && $row[7] != 1) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.cod_allowed_invalid_at_row', 'COD allowed is invalid at row') . $temp]);
                    }

                    if (!empty($row[11]) && $row[11] != 1) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.prices_inclusive_tax_invalid_at_row', 'Is prices inclusive tax is invalid at row') . $temp]);
                    }

                    if (!empty($row[12]) && $row[12] != 1) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.returnable_invalid_at_row', 'Is Returnable is invalid at row') . $temp]);
                    }

                    if (!empty($row[13]) && $row[13] != 1) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.cancelable_invalid_at_row', 'Is Cancelable is invalid at row') . $temp]);
                    }

                    if (!empty($row[13]) && $row[13] == 1 && (empty($row[14]) || !in_array($row[14], $allowed_status))) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.cancelable_till_invalid_at_row', 'Cancelable till is invalid at row') . $temp]);
                    }

                    if (empty($row[13]) && !(empty($row[14]))) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.cancelable_till_invalid_at_row', 'Cancelable till is invalid at row') . $temp]);
                    }

                    if (empty($row[15])) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.image_is_empty_at_row', 'Image is empty at row') . $temp]);
                    }

                    if (!empty($row[17]) && !in_array($row[17], $video_types)) {
                        return response()->json([
                            'error' => 'true',
                            'message' => labels('admin_labels.video_type_invalid', 'Video type is invalid at row ')
                                . $temp
                        ]);
                    }

                    if ($row[27] != 0 && $row[27] != 1 && $row[27] != 2 && $row[27] != 3 && $row[27] == "") {
                        return response()->json([
                            'error' => 'true',
                            'message' => labels('admin_labels.invalid_deliverable_type', 'Not valid value for deliverable_type at row ')
                                . $temp
                        ]);
                    }

                    if ($row[27] == '2' || $row[27] == '3') {
                        if (empty($row[28])) {
                            return response()->json(['error' => 'true', 'message' => labels('admin_labels.deliverable_zones_empty_at_row', 'Deliverable Zipcodes is empty at row') . $temp]);
                        }
                    }

                    if (empty($row[29])) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.seller_id_empty_at_row', 'Seller id is empty at row') . $temp]);
                    }
                    if (empty($row[34])) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.store_id_empty_at_row', 'Store id is empty at row') . $temp]);
                    }
                    $seller_id = $row[29];
                    $seller_data = fetchdetails('seller_store', ['seller_id' => $seller_id, 'store_id' => $row[34]], ['category_ids', 'permissions']);
                    $permissions = isset($seller_data) && !empty($seller_data) ? json_decode($seller_data[0]->permissions, true) : [];
                    if (!isset($seller_data[0]->category_ids) || !in_array($row[0], explode(',', $seller_data[0]->category_ids))) {
                        return response()->json(['error' => 'true', 'message' => 'This Category ID : ' . $row[0] . ' is not assign to seller id:' . $seller_id . ' at row ' . $temp]);
                    }

                    $index1 = 35;
                    $total_variants = 0;
                    for ($j = 0; $j < 70; $j++) {

                        if (!empty($row[$index1])) {
                            $total_variants++;
                        }
                        $index1 = $index1 + 11;
                    }
                    $variant_index = 35;
                    for ($k = 0; $k < $total_variants; $k++) {
                        if ($row[2] == 'variable_product') {
                            if (empty($row[$variant_index])) {
                                return response()->json([
                                    'error' => 'true',
                                    'message' => labels('admin_labels.attribute_value_ids_empty', 'Attribute value ids is empty at row ')
                                        . $temp
                                ]);
                            }
                            $variant_index = $variant_index + 11;
                        }
                    }
                    if ($total_variants == 0) {
                        return response()->json([
                            'error' => 'true',
                            'message' => labels('admin_labels.variants_not_found', 'Variants not found at row ')
                                . $temp
                        ]);
                    } elseif ($row[2] == 'simple_product' && $total_variants > 1) {
                        return response()->json([
                            'error' => 'true',
                            'message' => labels('admin_labels.too_many_variants_for_simple_product', 'You cannot add variants more than one for simple product at row ')
                                . $temp
                        ]);
                    }
                }
                $temp++;
            }

            fclose($handle);
            $handle = fopen($csv, "r");
            while (($row = fgetcsv($handle, 10000, ",")) != FALSE) //get row vales
            {
                if ($temp1 != 0) {

                    $data['category_id'] = $row[0];
                    if (!empty($row[1])) {
                        $data['tax'] = $row[1];
                    }
                    $data['type'] = $row[2];
                    if ($row[3] != '') {
                        $data['stock_type'] = $row[3];
                    }
                    $product_name = trim($row[4]);
                    $product_name = stripslashes($product_name);

                    $decoded_product_name = json_decode($product_name, true);

                    $product_short_description = trim($row[5]);
                    $product_short_description = stripslashes($product_short_description);

                    $decoded_product_short_description = json_decode($product_short_description, true);

                    $data['name'] = json_encode($decoded_product_name, JSON_UNESCAPED_UNICODE);
                    $data['short_description'] = json_encode($decoded_product_short_description, JSON_UNESCAPED_UNICODE);
                    $data['slug'] = generateSlug($decoded_product_name['en'] ?? '', 'products');
                    if ($row[6] != '') {
                        $data['indicator'] = $row[6];
                    }
                    if ($row[7] != '') {
                        $data['cod_allowed'] = $row[7];
                    }

                    if ($row[8] != '') {
                        $data['minimum_order_quantity'] = $row[8];
                    }
                    if ($row[9] != '') {
                        $data['quantity_step_size'] = $row[9];
                    }
                    if ($row[10] != '') {
                        $data['total_allowed_quantity'] = $row[10];
                    }
                    if ($row[11] != '') {
                        $data['is_prices_inclusive_tax'] = $row[11];
                    }
                    if ($row[12] != '') {
                        $data['is_returnable'] = $row[12];
                    }
                    if ($row[13] != '') {
                        $data['is_cancelable'] = $row[13];
                    }
                    $data['cancelable_till'] = $row[14];
                    $data['image'] = $row[15];
                    if (isset($row[16]) && $row[16] != '') {
                        $other_images = explode(',', $row[16]);
                        $data['other_images'] = json_encode($other_images, 1);
                    } else {
                        $data['other_images'] = '[]';
                    }
                    $data['video_type'] = $row[17];
                    $data['video'] = $row[18];
                    $data['tags'] = $row[19];
                    $data['warranty_period'] = $row[20];
                    $data['guarantee_period'] = $row[21];
                    $data['made_in'] = $row[22];

                    if (!empty($row[23])) {
                        $data['sku'] = $row[23];
                    }
                    if (!empty($row[24])) {
                        $data['stock'] = $row[24];
                    }
                    if ($row[25] != '') {
                        $data['availability'] = $row[25];
                    }

                    $data['description'] = $row[26];
                    $data['deliverable_type'] = $row[27];
                    $data['deliverable_zones'] = $row[28];
                    $data['seller_id'] = $row[29];
                    $data['brand'] = isset($row[30]) ? $row[30] : '';
                    $data['hsn_code'] = isset($row[31]) ? $row[31] : '';
                    $data['pickup_location'] = isset($row[32]) ? $row[32] : '';
                    $data['extra_description'] = isset($row[33]) ? $row[33] : '';
                    $data['store_id'] = isset($row[34]) ? $row[34] : '';
                    if ($permissions['require_products_approval'] == 1) {
                        $data['status'] = 2;
                    }


                    $product = Product::create($data);

                    $index1 = 36;
                    $total_variants = 0;
                    for ($j = 0; $j < 70; $j++) {
                        if (!empty($row[$index1])) {
                            $total_variants++;
                        }
                        $index1 = $index1 + 11;
                    }

                    $index1 = 35;
                    $attribute_value_ids = '';
                    for ($j = 0; $j < $total_variants; $j++) {
                        if (!empty($row[$index1])) {
                            if (!empty($attribute_value_ids)) {
                                $attribute_value_ids .= ',' . strval($row[$index1]);
                            } else {
                                $attribute_value_ids = strval($row[$index1]);
                            }
                        }
                        $index1 = $index1 + 11;
                    }
                    $attribute_value_ids = !empty($attribute_value_ids) ? $attribute_value_ids : '';
                    $product_attribute_data = [
                        'product_id' => $product->id,
                        'attribute_value_ids' => $attribute_value_ids,

                    ];
                    $product_attributes = Product_attributes::create($product_attribute_data);

                    $index = 35;
                    for ($i = 0; $i < $total_variants; $i++) {
                        $variant_data[$i]['images'] = '[]';
                        $variant_data[$i]['product_id'] = $product->id;

                        if (strval($data['type']) == 'variable_product') {
                            $variant_data[$i]['attribute_value_ids'] = $row[$index];
                        } else {
                            $variant_data[$i]['attribute_value_ids'] = null;
                        }
                        $index++;
                        $variant_data[$i]['price'] = $row[$index];
                        $index++;
                        if (isset($row[$index]) && !empty($row[$index])) {
                            $variant_data[$i]['special_price'] = $row[$index];
                        } else {
                            $variant_data[$i]['special_price'] = 0;
                        }

                        $index++;
                        if (isset($row[$index]) && !empty($row[$index])) {
                            $variant_data[$i]['sku'] = $row[$index];
                        }
                        $index++;
                        if (isset($row[$index]) && !empty($row[$index])) {
                            $variant_data[$i]['stock'] = $row[$index];
                        }

                        $index++;
                        if (isset($row[$index]) && $row[$index] != '' && !empty($row[$index])) {
                            $images = explode(',', $row[$index]);
                            $variant_data[$i]['images'] = json_encode($images, 1);
                        }

                        $index++;
                        if (isset($row[$index]) && $row[$index] != '') {
                            $variant_data[$i]['availability'] = $row[$index];
                        }

                        $index++;
                        if (isset($row[$index]) && $row[$index] != '') {
                            $variant_data[$i]['weight'] = $row[$index];
                        }

                        $index++;
                        if (isset($row[$index]) && $row[$index] != '') {
                            $variant_data[$i]['height'] = $row[$index];
                        }

                        $index++;
                        if (isset($row[$index]) && $row[$index] != '') {
                            $variant_data[$i]['breadth'] = $row[$index];
                        }

                        $index++;
                        if (isset($row[$index]) && $row[$index] != '') {
                            $variant_data[$i]['length'] = $row[$index];
                        }

                        $index++;
                        $product_attributes = Product_variants::create($variant_data[$i]);
                    }
                }
                $temp1++;
            }
            fclose($handle);
            return response()->json(['error' => 'false', 'message' => labels('admin_labels.products_uploaded_successfully', 'Products uploaded successfully!')]);
        } else { // bulk_update
            while (($row = fgetcsv($handle, 10000, ",")) != FALSE) //get row vales
            {

                if ($temp != 0) {
                    if (empty($row[0])) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.product_id_empty_at_row', 'Product id is empty at row') . $temp]);
                    }

                    if (!empty($row[3]) && $row[3] != 'simple_product' && $row[3] != 'variable_product') {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.product_type_invalid_at_row', 'Product type is invalid at row') . $temp]);
                    }


                    if (!empty($row[8]) && $row[8] != 1) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.cod_allowed_invalid_at_row', 'COD allowed is invalid at row') . $temp]);
                    }

                    if (!empty($row[12]) && $row[12] != 1) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.prices_inclusive_tax_invalid_at_row', 'Is prices inclusive tax is invalid at row') . $temp]);
                    }

                    if (!empty($row[13]) && $row[13] != 1) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.returnable_invalid_at_row', 'Is Returnable is invalid at row') . $temp]);
                    }

                    if (!empty($row[14]) && $row[14] != 1) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.cancelable_invalid_at_row', 'Is Cancelable is invalid at row') . $temp]);
                    }

                    if (!empty($row[14]) && $row[14] == 1 && (empty($row[15]) || !in_array($row[15], $allowed_status))) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.cancelable_till_invalid_at_row', 'Cancelable till is invalid at row') . $temp]);
                    }

                    if (empty($row[14]) && !(empty($row[15]))) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.cancelable_till_invalid_at_row', 'Cancelable till is invalid at row') . $temp]);
                    }

                    if (!empty($row[18]) && !in_array($row[17], $video_types)) {
                        return response()->json([
                            'error' => 'true',
                            'message' => labels('admin_labels.video_type_invalid', 'Video type is invalid at row ')
                                . $temp
                        ]);
                    }
                    if ($row[27] != "") {
                        if ($row[27] != 0 && $row[27] != 1 && $row[27] != 2 && $row[27] != 3) {
                            return response()->json([
                                'error' => 'true',
                                'message' => labels('admin_labels.invalid_deliverable_type', 'Not valid value for deliverable_type at row ')
                                    . $temp
                            ]);
                        }
                    }

                    if ($row[27] != "" && ($row[27] == '2' || $row[27] == '3')) {
                        if (empty($row[28])) {
                            return response()->json(['error' => 'true', 'message' => labels('admin_labels.deliverable_zones_empty_at_row', 'Deliverable Zipcodes is empty at row') . $temp]);
                        }
                    }

                    if (!empty($row[1])) {
                        if (empty($row[29])) {
                            return response()->json(['error' => 'true', 'message' => labels('admin_labels.seller_id_empty_at_row', 'Seller id is empty at row') . $temp]);
                        }
                        $seller_id = $row[29];
                        $seller_data = fetchdetails('seller_store', ['seller_id' => $seller_id], 'category_ids');

                        if (!isset($seller_data[0]->category_ids) || !in_array($row[1], explode(',', $seller_data[0]->category_ids))) {
                            return response()->json(['error' => 'true', 'message' => 'This Category ID : ' . $row[1] . ' is not assign to seller id:' . $seller_id . ' at row ' . $temp]);
                        }

                        if (empty($row[30])) {
                            return response()->json([
                                'error' => 'true',
                                'message' => labels('admin_labels.variant_id_empty', 'Variant ID is empty at row')
                                    . $temp
                            ]);
                        }
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
                    $product = fetchDetails('products', ['id' => $product_id], '*');

                    if (isset($product[0]) && !empty($product[0])) {
                        if (!empty($row[1])) {
                            $data['category_id'] = $row[1];
                        } else {
                            $data['category_id'] = $product[0]->category_id;
                        }
                        if (!empty($row[2])) {
                            $data['tax'] = $row[2];
                        } else {
                            $data['tax'] = $product[0]->tax;
                        }
                        if (!empty($row[3])) {
                            $data['type'] = $row[3];
                        } else {
                            $data['type'] = $product[0]->type;
                        }
                        if ($row[4] != '') {
                            $data['stock_type'] = $row[4];
                        } else {
                            $data['stock_type'] = $product[0]->stock_type;
                        }

                        if (!empty($row[5])) {

                            $product_name = trim($row[5]);
                            $product_name = stripslashes($product_name);

                            $decoded_product_name = json_decode($product_name, true);

                            $data['name'] = json_encode($decoded_product_name, JSON_UNESCAPED_UNICODE);
                            $data['slug'] = generateSlug($decoded_product_name['en'], 'products');
                        } else {
                            $data['name'] = $product[0]->name;
                        }
                        if (!empty($row[6])) {
                            $product_short_description = trim($row[6]);
                            $product_short_description = stripslashes($product_short_description);

                            $decoded_product_short_description = json_decode($product_short_description, true);

                            $data['short_description'] = json_encode($decoded_product_short_description, JSON_UNESCAPED_UNICODE);
                        } else {
                            $data['short_description'] = $product[0]->short_description;
                        }
                        if ($row[7] != '') {
                            $data['indicator'] = $row[7];
                        } else {
                            $data['indicator'] = $product[0]->indicator;
                        }
                        if (!empty($row[8])) {
                            $data['cod_allowed'] = $row[8];
                        } else {
                            $data['cod_allowed'] = $product[0]->cod_allowed;
                        }

                        if (!empty($row[9])) {
                            $data['minimum_order_quantity'] = $row[9];
                        } else {
                            $data['minimum_order_quantity'] = $product[0]->minimum_order_quantity;
                        }
                        if (!empty($row[10])) {
                            $data['quantity_step_size'] = $row[10];
                        } else {
                            $data['quantity_step_size'] = $product[0]->quantity_step_size;
                        }
                        if ($row[11] != '') {
                            $data['total_allowed_quantity'] = $row[11];
                        } else {
                            $data['total_allowed_quantity'] = $product[0]->total_allowed_quantity;
                        }
                        if ($row[12] != '') {
                            $data['is_prices_inclusive_tax'] = $row[12];
                        } else {
                            $data['is_prices_inclusive_tax'] = $product[0]->is_prices_inclusive_tax;
                        }
                        if ($row[13] != '') {
                            $data['is_returnable'] = $row[13];
                        } else {
                            $data['is_returnable'] = $product[0]->is_returnable;
                        }
                        if ($row[14] != '') {
                            $data['is_cancelable'] = $row[14];
                        } else {
                            $data['is_cancelable'] = $product[0]->is_cancelable;
                        }
                        if (!empty($row[15])) {
                            $data['cancelable_till'] = $row[15];
                        } else {
                            $data['cancelable_till'] = $product[0]->cancelable_till;
                        }
                        if (!empty($row[16])) {
                            $data['image'] = $row[16];
                        } else {
                            $data['image'] = $product[0]->image;
                        }
                        if (!empty($row[17])) {
                            $data['video_type'] = $row[17];
                        } else {
                            $data['video_type'] = $product[0]->video_type;
                        }
                        if (!empty($row[18])) {
                            $data['video'] = $row[18];
                        } else {
                            $data['video'] = $product[0]->video;
                        }
                        if (!empty($row[19])) {
                            $data['tags'] = $row[19];
                        } else {
                            $data['tags'] = $product[0]->tags;
                        }
                        if (!empty($row[20])) {
                            $data['warranty_period'] = $row[20];
                        } else {
                            $data['warranty_period'] = $product[0]->warranty_period;
                        }
                        if (!empty($row[21])) {
                            $data['guarantee_period'] = $row[21];
                        } else {
                            $data['guarantee_period'] = $product[0]->guarantee_period;
                        }
                        if (!empty($row[22])) {
                            $data['made_in'] = $row[22];
                        } else {
                            $data['made_in'] = $product[0]->made_in;
                        }
                        if (!empty($row[23])) {
                            $data['sku'] = $row[23];
                        } else {
                            $data['sku'] = $product[0]->sku;
                        }
                        if ($row[24] != '') {
                            $data['stock'] = $row[24];
                        } else {
                            $data['stock'] = $product[0]->stock;
                        }
                        if ($row[25] != '') {
                            $data['availability'] = $row[25];
                        } else {
                            $data['availability'] = $product[0]->availability;
                        }
                        if ($row[26] != '') {
                            $data['description'] = $row[26];
                        } else {
                            $data['description'] = $product[0]->description;
                        }
                        if ($row[27] != '') {
                            $data['deliverable_type'] = $row[27];
                        } else {
                            $data['deliverable_type'] = $product[0]->deliverable_type;
                        }
                        if ($row[27] != '' && ($row[27] == '2')) {
                            $data['deliverable_zones'] = $row[28];
                        } else {
                            $data['deliverable_zones'] = $product[0]->deliverable_zones;
                        }

                        if ($row[29] != '') {
                            $data['seller_id'] = $row[29];
                        } else {
                            $data['seller_id'] = $product[0]->seller_id;
                        }
                        if ($row[30] != '') {
                            $data['brand'] = $row[30];
                        } else {
                            $data['brand'] = $product[0]->brand;
                        }
                        if ($row[31] != '') {
                            $data['hsn_code'] = $row[31];
                        } else {
                            $data['hsn_code'] = $product[0]->hsn_code;
                        }
                        if ($row[32] != '') {
                            $data['pickup_location'] = $row[32];
                        } else {
                            $data['pickup_location'] = $product[0]->pickup_location;
                        }
                        if ($row[33] != '') {
                            $data['extra_description'] = $row[33];
                        } else {
                            $data['extra_description'] = $product[0]->extra_description;
                        }
                        Product::where('id', $row[0])->update($data);
                    }
                    $index1 = 35;
                    $total_variants = 0;
                    for ($j = 0; $j < 70; $j++) {
                        if (!empty($row[$index1])) {
                            $total_variants++;
                        }
                        $index1 = $index1 + 10;
                    }
                    $index = 34;
                    for ($i = 0; $i < $total_variants; $i++) {
                        $variant_id = $row[$index];
                        $variant = fetchDetails('product_variants', ['id' => $row[$index]], '*');
                        if (isset($variant[0]) && !empty($variant[0])) {
                            $variant_data[$i]['product_id'] = $variant[0]->product_id;
                            $index++;
                            if (isset($row[$index]) && !empty($row[$index])) {
                                $variant_data[$i]['price'] = $row[$index];
                            } else {
                                $variant_data[$i]['price'] = $variant[0]->price;
                            }
                            $index++;
                            if (isset($row[$index]) && $row[$index] != '') {
                                $variant_data[$i]['special_price'] = $row[$index];
                            } else {
                                $variant_data[$i]['special_price'] = $variant[0]->special_price;
                            }
                            $index++;
                            if (isset($row[$index]) && !empty($row[$index])) {
                                $variant_data[$i]['sku'] = $row[$index];
                            } else {
                                $variant_data[$i]['sku'] = $variant[0]->sku;
                            }
                            $index++;
                            if (isset($row[$index]) && $row[$index] != '') {
                                $variant_data[$i]['stock'] = $row[$index];
                            } else {
                                $variant_data[$i]['stock'] = $variant[0]->stock;
                            }

                            $index++;
                            if (isset($row[$index]) && $row[$index] != '') {
                                $variant_data[$i]['availability'] = $row[$index];
                            } else {
                                $variant_data[$i]['availability'] = $variant[0]->availability;
                            }

                            $index++;
                            if (isset($row[$index]) && $row[$index] != '') {
                                $variant_data[$i]['weight'] = $row[$index];
                            }

                            $index++;
                            if (isset($row[$index]) && $row[$index] != '') {
                                $variant_data[$i]['height'] = $row[$index];
                            }

                            $index++;
                            if (isset($row[$index]) && $row[$index] != '') {
                                $variant_data[$i]['breadth'] = $row[$index];
                            }

                            $index++;
                            if (isset($row[$index]) && $row[$index] != '') {
                                $variant_data[$i]['length'] = $row[$index];
                            }
                            $index++;
                            Product_variants::where('id', $variant_id)->update($variant_data[$i]);
                        }
                    }
                }
                $temp1++;
            }
            fclose($handle);
            return response()->json(['error' => 'false', 'message' => labels('admin_labels.products_updated_successfully', 'Products updated successfully!')]);
        }
    }

    public function show($id)
    {

        $store_id = getStoreId();
        $language_code = get_language_code();

        $data = Product::where('store_id', $store_id)
            ->find($id);
        if ($data === null || empty($data)) {
            return view('admin.pages.views.no_data_found');
        } else {
            $attributes = Product_attributes::leftJoin('attribute_values', function ($join) {
                $join->on('attribute_values.id', '=', DB::raw("FIND_IN_SET(product_attributes.attribute_value_ids, attribute_values.id)"));
            })
                ->where('product_attributes.product_id', $id)
                ->get();
            $attribute_value_ids = isset($attributes[0]->attribute_value_ids) && !empty($attributes[0]->attribute_value_ids) ? explode(',', $attributes[0]->attribute_value_ids) : [];

            $attribute_values = getAttributeValuesById($attribute_value_ids);

            $product_variants = getVariantsValuesByPid($data->id, [0, 1, 7]);


            $sellers = DB::table('seller_store')
                ->leftJoin('seller_data', 'seller_data.id', '=', 'seller_store.seller_id')
                ->leftJoin('users', 'users.id', '=', 'seller_store.user_id')
                ->select('seller_store.*', 'seller_data.*', 'users.username') // Separate table and column names
                ->where('seller_store.store_id', $store_id)
                ->get();

            $taxes = Tax::where('status', 1)->get();

            $brands = Brand::where('status', 1)->where('store_id', $store_id)->orderBy('id', 'desc')->get();
            $brand_name = fetchDetails('brands', ['id' => $data->brand], '*');
            $brand_name = getDynamicTranslation('brands', 'name', $data->brand, $language_code) ?? '';

            $country = fetchDetails('countries', ['name' => $data->made_in], '*');

            $seller_id = fetchDetails('products', ['id' => $data->id], 'seller_id')[0]->seller_id;

            $shipping_data = fetchDetails('pickup_locations', ['status' => 1, 'seller_id' => $seller_id], ['id', 'pickup_location']);

            $categories = Category::where('status', 1)->where('store_id', $store_id)->orderBy('id', 'desc')->get();

            $ProductRatingController = new ProductRatingController;

            $rating = $ProductRatingController->fetch_rating(($id) ? $id : '', '', 10, 0, 'id', 'desc');

            $product_faqs = getProductFaqs('', $data->id, '', '', 50);

            $sales_count = OrderItems::leftJoin('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')->leftJoin('products', 'products.id', '=', 'product_variants.product_id')
                ->where('product_variants.product_id', $data->id)
                ->sum('order_items.quantity');

            return view('admin.pages.views.product', compact('data', 'attributes', 'sellers', 'taxes', 'brands', 'product_variants', 'country', 'shipping_data', 'brand_name', 'categories', 'rating', 'product_faqs', 'sales_count', 'attribute_values', 'language_code'));
        }
    }

    public function change_variant_status(Request $request)
    {
        $status = $request->status;
        $id = $request->id;

        if (empty($id) || !in_array($status, [0, 1])) {
            return response()->json([
                'error' => true,
                'error_message' => labels('admin_labels.invalid_status_or_id_value', 'Invalid Status or ID value supplied')
            ]);
        }

        // Toggle status value
        $newStatus = $status == 1 ? 0 : 1;

        // Update the product variant status
        Product_variants::where('id', $id)->update(['status' => $newStatus]);

        return response()->json(['error' => false, 'message' => labels('admin_labels.status_updated_successfully', 'Status updated successfully.')]);
    }

    public function delete_variant(Request $request)
    {
        $status = $request->status;
        $id = $request->id;

        if (empty($id) || !in_array($status, [0, 1, 7])) {
            return response()->json(['error' => true, 'error_message' => labels('admin_labels.invalid_status_or_id_value', 'Invalid Status or ID value supplied')]);
        }

        // Toggle status value
        $newStatus = $status == 7 ? 1 : ($status == 1 ? 7 : $status);

        // Update the product variant status
        Product_variants::where('id', $id)->update(['status' => $newStatus]);

        return response()->json(['error' => false, 'message' => labels('admin_labels.status_updated_successfully', 'Status updated successfully.')]);
    }
    public function delete_selected_data(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:products,id'
        ]);

        foreach ($request->ids as $id) {
            $product = Product::find($id);

            if ($product) {
                Product_variants::where('product_id', $id)->delete();
                Product::where('id', $id)->delete();
            }
        }


        return response()->json([
            'error' => false,
            'message' => labels('admin_labels.products_deleted_successfully', 'Selected products deleted successfully!'),
        ]);
    }
    
    public function uploadProduct() {
        $masterCategories = \App\Models\Category::where('status', 1)->where('parent_id', 0)->orderBy('name')->get();
        foreach ($masterCategories as $master) {
            $decoded = json_decode($master->name, true);
            $master->name = $decoded['en'] ?? $master->name;
            $subcategories = \App\Models\Category::where('status', 1)->where('parent_id', $master->id)->orderBy('name')->get();
            foreach ($subcategories as $sub) {
                $subDecoded = json_decode($sub->name, true);
                $sub->name = $subDecoded['en'] ?? $sub->name;
            }
            // Always set subcategories, even if empty
            $master->subcategories = $subcategories;
        }
        return view('product.productUpload', ['masterCategories' => $masterCategories]);
    }
}