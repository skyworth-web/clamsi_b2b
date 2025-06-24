<?php

use Carbon\Carbon;
use App\Models\Faq;
use App\Models\Area;
use App\Models\Cart;
use App\Models\City;
use App\Models\User;
use App\Models\Order;
use App\Models\Store;
use App\Models\Seller;
use App\Models\Slider;
use Imagine\Image\Box;
use App\Models\Address;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Updates;
use App\Models\Zipcode;
use Imagine\Gd\Imagine;
use App\Models\Category;
use App\Models\Favorite;
use Imagine\Image\Point;
use App\Models\OrderItems;
use App\Libraries\Paystack;
use App\Libraries\Razorpay;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Models\ComboProduct;
use App\Models\OrderCharges;

use Illuminate\Mail\Message;
use App\Libraries\Shiprocket;
use App\Models\OrderTracking;
use App\Models\ProductRating;
use App\Models\ReturnRequest;
use App\Models\Attribute_values;
use App\Models\Product_variants;
use App\Models\OrderBankTransfers;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Services\CustomFileRemover;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Request;
// use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Admin\AddressController;
use App\Http\Controllers\Admin\TransactionController;
use App\Models\Parcel;
use App\Models\Parcelitem;
use App\Models\Tax;
use App\Models\UserFcm;
use App\Models\Zone;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Spatie\MediaLibrary\MediaCollections\Filesystem as MediaFilesystem;
use Google\Client;
use Illuminate\Support\Facades\Validator;
use LaravelDaily\Invoices\Invoice;
use LaravelDaily\Invoices\Classes\Buyer;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use LaravelDaily\Invoices\Classes\Party;
use Illuminate\Support\Facades\Log;

// generate unique slug

function generateSlug($newName, $tableName = 'categories', $slugField = 'slug', $currentSlug = '', $currentName = '')
{

    $slug = Str::slug($newName);

    // If the name hasn't changed, use the existing slug
    if ($currentName !== '' && $currentName == $newName && $currentSlug !== '') {
        return $currentSlug;
    }


    $count = 0;
    $slugBase = $slug;

    // Check if the generated slug already exists in the database
    while (DB::table($tableName)->where($slugField, $slug)->exists()) {
        $count++;
        $slug = $slugBase . '-' . $count;
    }

    return $slug;
}

// fetch allowed media type

function allowedMediaTypes()
{
    $config = config('eshop_pro.type');
    $general = [];

    foreach ($config as $mainType => $extensions) {
        $general = array_merge($general, $extensions['types']);
    }

    return $general;
}

function timezoneList()
{
    $zones_array = array();
    $timestamp = time();
    foreach (timezone_identifiers_list() as $key => $zone) {
        date_default_timezone_set($zone);
        $zones_array[$key]['zone'] = $zone;
        $zones_array[$key]['offset'] = (int) ((int) date('O', $timestamp)) / 100;
        $zones_array[$key]['diff_from_GMT'] = date('P', $timestamp);
        $zones_array[$key]['time'] = date('h:i:s A');
    }
    return $zones_array;
}

function getCurrentStoreData($store_id)
{
    $store_details = session('store_details');
    if ($store_details !== null && json_decode($store_details)[0]->id == $store_id) {
        $store_details = session('store_details');
    } else {
        $store_details = Store::where('id', $store_id)
            ->where('status', 1)
            ->get();
        session()->forget("store_details");
        session()->put("store_details", json_encode($store_details));
    }

    return $store_details;
}

// function getCurrentStoreData($store_id)
// {
//     $store_details = session('store_details');
//     if (!empty(json_decode($store_details))) {
//         if ($store_details !== null && json_decode($store_details)[0]->id == $store_id) {
//             $store_details = session('store_details');
//         }
//     } else {
//         $store_details = Store::where('id', $store_id)
//             ->where('status', 1)
//             ->get();
//         session()->forget("store_details");
//         session()->put("store_details", json_encode($store_details));
//     }

//     return $store_details;
// }

function getStoreSettings()
{
    $store_id = session('store_id');
    $store_settings = null;
    if (!empty($store_id)) {
        $store_settings = getCurrentStoreData($store_id);
        if ($store_settings !== null && $store_settings !== []) {
            $store_settings = json_decode($store_settings, true);
            $store_settings = json_decode($store_settings[0]['store_settings'], true);
        }
    }
    return $store_settings;
}

function getDefaultCurrency()
{

    $current_currency = session()->get("default-currency");
    if ($current_currency != null) {
        return json_decode($current_currency);
    }
    $currency = DB::table('currencies')->select("*")->where(['is_default' => 1])->get()->toArray();
    if ($currency != NULL) {
        $currency = $currency[0];
    }
    session()->forget("default-currency");
    session()->put("default-currency", json_encode($currency));

    // dd($currency);
    return $currency;
}
function getAllCurrency()
{

    $current_currency = session()->get("all-currency");
    if ($current_currency != null) {

        return json_decode($current_currency);
    }
    $currency = DB::table('currencies')->select("*")->get()->toArray();

    session()->forget("all-currency");
    session()->put("all-currency", json_encode($currency));

    return $currency;
}

function getCurrencyCodeSettings($code, $fetchWithSymbol = false)
{

    $current_currency = session()->get("currency-$code");
    if ($current_currency != null) {

        return json_decode($current_currency);
    }
    if ($fetchWithSymbol == true) {
        $currency = DB::table('currencies')->where('symbol', $code)->select("*")->get()->toArray();
    } else {
        $currency = DB::table('currencies')->where('code', $code)->select("*")->get()->toArray();
    }


    session()->forget("currency-$code");
    session()->put("currency-$code", json_encode($currency));

    return $currency;
}


function getSettings($type = 'system_settings', $is_json = false, $for_user_web = false)
{
    if ($for_user_web == true) {
        $current_settings = session()->get($type);
        if ($current_settings != null) {
            return $current_settings;
        }
    }


    if (session()->get('firebase_settings') == null) {
        $firebase_settings = Setting::where('variable', 'firebase_settings')->first();
        if ($firebase_settings != null) {
            session()->put("firebase_settings", $firebase_settings['value']);
        }
    }
    $setting = Setting::where('variable', $type)->first();

    $currency = getDefaultCurrency();
    // dd($currency);
    if ($setting) {

        $settingsArray = json_decode($setting->value, true);
        if ($type == 'system_settings') { //display only when type is system settings
            $settingsArray['currency_setting'] = $currency;
        }
        // dd($settingsArray['currency_setting']);
        if ($is_json) {
            // Encode the updated array back to JSON
            $updatedValue = json_encode($settingsArray);
            // dd($settingsArray);
            if ($for_user_web == true) {
                session()->put($type, $updatedValue);
            }
            return $updatedValue;
        } else {
            if ($for_user_web == true) {
                session()->put($type, htmlspecialchars_decode(json_encode($settingsArray)));
            }
            return htmlspecialchars_decode(json_encode($settingsArray));
        }
        // dd($settingsArray);
    }
}
if (!function_exists('getCategoriesOptionHtml')) {
    function getCategoriesOptionHtml($categories, $selected_vals = [], $level = 0, &$all_subcategory_ids = [])
    {
        // dd($selected_vals);
        $html = "";

        // Collect all subcategory IDs
        foreach ($categories as $category) {
            if (!empty($category['children'])) {
                foreach ($category['children'] as $child) {
                    $all_subcategory_ids[] = $child['id'];
                }
            }
        }

        foreach ($categories as $category) {
            // Skip already selected categories
            if (in_array($category['id'], $selected_vals)) {
                continue;
            }

            // Skip if it's a subcategory appearing elsewhere
            if ($level == 0 && in_array($category['id'], $all_subcategory_ids)) {
                continue;
            }
            $language_code = get_language_code();
            $category_name = getDynamicTranslation('categories', 'name', $category['id'], $language_code);
            $indentation = str_repeat('&nbsp;&nbsp;&nbsp;', $level);
            $style = ($level > 0) ? 'style="color: gray;"' : '';

            $html .= '<option value="' . $category['id'] . '" class="l' . $category['level'] . '" ' . $style . '>';
            $html .= $indentation . e($category_name) . '</option>';

            if (!empty($category['children'])) {
                $html .= getCategoriesOptionHtml($category['children'], $selected_vals, $level + 1, $all_subcategory_ids);
            }
        }
        return $html;
    }
}

function getAttributeValuesById($id)
{
    $attributeValues = Attribute_values::select('attributes.name as attribute_name')
        ->selectRaw('GROUP_CONCAT(attribute_values.value ORDER BY attribute_values.id ASC) AS attribute_values')
        ->selectRaw('GROUP_CONCAT(attribute_values.id ORDER BY attribute_values.id ASC) AS attribute_values_id')
        ->join('attributes', 'attribute_values.attribute_id', '=', 'attributes.id')
        ->whereIn('attribute_values.id', $id)
        ->groupBy('attributes.name')
        ->get()
        ->toArray();

    // Process the attribute values
    if (!empty($attributeValues)) {
        foreach ($attributeValues as &$value) {
            // Convert comma-separated string to array for each attribute_values and attribute_values_id
            $value['attribute_values'] = explode(',', $value['attribute_values']);
            $value['attribute_values_id'] = explode(',', $value['attribute_values_id']);
        }
    }
    return $attributeValues;
}

function getVariantsValuesByPid($id, $status = [1], $language_code = "")
{
    $varaint_values = [];
    $varaint_values = DB::table('product_variants as pv')
        ->selectRaw('pv.*, pv.product_id,p.name as product_name,p.image as product_image,
                    group_concat(av.id ORDER BY av.id ASC) as variant_ids,
                    group_concat(a.name ORDER BY av.id ASC SEPARATOR " ") as attr_name,
                    group_concat(av.value ORDER BY av.id ASC) as variant_values,
                    group_concat(av.swatche_type ORDER BY av.id ASC ) as swatche_type ,
                    group_concat(av.swatche_value ORDER BY av.id ASC ) as swatche_value,
                    pv.price as price')
        ->leftJoin('attribute_values as av', DB::raw('FIND_IN_SET(av.id, pv.attribute_value_ids)'), '>', DB::raw('0'))
        ->leftJoin('attributes as a', 'a.id', '=', 'av.attribute_id')
        ->leftJoin('products as p', 'p.id', '=', 'pv.product_id')
        ->where('pv.product_id', $id)
        ->whereIn('pv.status', $status)
        ->groupBy('pv.id')
        ->orderBy('pv.id')
        ->get()
        ->toArray();

    if (!empty($varaint_values)) {
        foreach ($varaint_values as $variant) {

            if ($variant->swatche_type != "") {
                $swatche_type = array();
                $swatche_values1 = array();
                $swatche_type = explode(",", $variant->swatche_type);
                $swatche_values = explode(",", $variant->swatche_value);

                for ($j = 0; $j < count($swatche_type); $j++) {

                    if ($swatche_type[$j] == "2") {

                        $swatche_values1[$j] = $swatche_values[$j];
                    } else if ($swatche_type[$j] == "0") {
                        $swatche_values1[$j] = '0';
                    } else if ($swatche_type[$j] == "1") {
                        $swatche_values1[$j] = $swatche_values[$j];
                    }
                    $row = implode(',', $swatche_values1);

                    $variant->swatche_value = $row;
                }
            }
            $variant = ((array) $variant);
            // dd($variant);
            $images = [];
            $variant_image = json_decode($variant['images']);
            if ($variant_image != null) {
                foreach ($variant_image as $img) {
                    $image = getImageUrl($img);
                    array_push($images, $image);
                }
            }
            $variant['images'] = $images;

            $variant['availability'] = isset($variant['availability']) && $variant['availability'] != "" ? $variant['availability'] : '';
        }
    }

    return $varaint_values;
}




function resizeImage($image_data, $source_path, $id = false)
{
    if ($image_data['type'] == "image") {
        $image_types = ['thumb', 'cropped'];
        $image_sizes = [
            'md' => ['width' => 800, 'height' => 800],
            'sm' => ['width' => 450, 'height' => 450]
        ];

        $image_name = $image_data['name'];
        $width = $image_data['width'];
        $height = $image_data['height'];

        foreach ($image_types as $image_type) {
            if (!array_key_exists($image_type, $image_sizes)) {
                continue; // Skip unknown image types
            }

            foreach ($image_sizes as $size_key => $size_value) {
                $new_width = $size_value['width'];
                $new_height = $size_value['height'];

                if (($width >= $new_width || $height >= $new_height) && $image_type == 'cropped') {
                    $x_axis = max(0, ($width - $new_width) / 2);
                    $y_axis = max(0, ($height - $new_height) / 2);

                    try {
                        $imagine = new Imagine();
                        $imagePath = storage_path('app/public' . $source_path . '/' . $image_name);
                        $image = $imagine->open($imagePath);
                        $cropped_image = $image->crop(new Point($x_axis, $y_axis), new Box($width, $height));
                        $cropped_image->save(storage_path('app/public' . $source_path . '/cropped-' . $size_key . '/' . $image_name));
                    } catch (Exception $e) {
                        // Handle exception (e.g., log the error)
                    }
                }

                if (($width >= $new_width || $height >= $new_height) && $image_type == 'thumb') {
                    try {
                        $imagine = new Imagine();
                        $imagePath = storage_path('app/public' . $source_path . '/' . $image_name);
                        $image = $imagine->open($imagePath);
                        $resized_image = $image->resize(new Box($new_width, $new_height));
                        $resized_image->save(storage_path('app/public' . $source_path . '/thumb-' . $size_key . '/' . $image_name));
                    } catch (Exception $e) {
                        // Handle exception (e.g., log the error)
                    }
                }
            }
        }
    }
}


function outputEscaping($array)
{
    $exclude_fields = ["images", "other_images"];
    $data = null;

    if (!empty($array)) {
        if (is_array($array)) {
            $data = [];
            foreach ($array as $key => $value) {
                if (!in_array($key, $exclude_fields)) {
                    $data[$key] = stripslashes((string) $value);
                } else {
                    $data[$key] = $value;
                }
            }
        } elseif (is_object($array)) {
            $data = new \stdClass();
            foreach ($array as $key => $value) {
                if (!in_array($key, $exclude_fields)) {
                    $data->$key = stripslashes($value);
                } else {
                    $data->$key = $value;
                }
            }
        } else {
            $data = stripslashes($array);
        }
    }

    return $data;
}

function escapeArray($array)
{
    $posts = [];

    if (!empty($array)) {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $posts[$key] = DB::connection()->getPdo()->quote($value ?? '');
            }
        } else {
            return DB::connection()->getPdo()->quote($array);
        }
    }

    return $posts;
}
function isExist($where, $table, $update_id = null)
{
    $query = DB::table($table);
    foreach ($where as $key => $val) {
        $query->where($key, $val);
    }

    if ($update_id !== null) {
        $query->whereNotIn('id', [$update_id]);
    }
    return $query->exists();
}

// use for fetch particular or whole details from table

function fetchDetails($table, $where = NULL, $fields = '*', $limit = '', $offset = '', $sort = '', $order = '', $where_in_key = '', $where_in_value = '')
{
    $query = DB::table($table)->select($fields);

    if (!empty($where)) {
        $query->where($where);
    }

    if (!empty($where_in_key) && !empty($where_in_value)) {
        $query->whereIn($where_in_key, $where_in_value);
    }

    if (!empty($limit)) {
        $query->limit($limit);
    }

    if (!empty($offset)) {
        $query->offset($offset);
    }

    if (!empty($order) && !empty($sort)) {
        $query->orderBy($sort, $order);
    }
    $res = $query->get()->toArray();
    return $res;
}

function sendNotification($fcmMsg, $registrationIDs_chunks, $customBodyFields = [], $title = "test title", $message = "test message", $type = "test type")
{
    $store_id = getStoreId();
    $store_id = isset($store_id) && !empty($store_id) ? $store_id : (isset($customBodyFields['store_id']) && !empty($customBodyFields['store_id']) ? $customBodyFields['store_id'] : "");
    // dd($store_id);
    $project_id = Setting::where('variable', 'firebase_project_id')
        ->value('value');
    // dd($project_id);

    $url = 'https://fcm.googleapis.com/v1/projects/' . $project_id . '/messages:send';

    $access_token = getAccessToken();


    $fcmFields = [];
    // dd($customBodyFields);

    foreach ($registrationIDs_chunks as $registrationIDs) {
        foreach ($registrationIDs as $registrationID) {

            if ($registrationID == "BLACKLISTED") {
                continue;
            }
            if ($registrationID == "") {
                continue;
            }
            $data = [
                "message" => [
                    "token" => $registrationID,
                    "notification" => [
                        "title" => $customBodyFields['title'],
                        "body" => $customBodyFields['body'],
                    ],
                    "data" => $customBodyFields,
                    "android" => [
                        "notification" => [
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        ],
                        "data" => [
                            "title" => $title,
                            "body" => $message,
                            "type" => $customBodyFields['type'],
                            "store_id" => strval($store_id),
                        ]
                    ],
                    "apns" => [
                        "headers" => [
                            "apns-priority" => "10"
                        ],
                        "payload" => [
                            "aps" => [
                                "alert" => [
                                    "title" => $customBodyFields['title'],
                                    "body" => $customBodyFields['body'],
                                ],
                                "user_id" => isset($customBodyFields['user_id']) ? $customBodyFields['user_id'] : '',
                                "store_id" => strval($store_id),
                                "data" => $customBodyFields,
                            ]
                        ]
                    ],
                ]
            ];
            // dd($data);
            $encodedData = json_encode($data);
            $headers = [
                'Authorization: Bearer ' . $access_token,
                'Content-Type: application/json',
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

            // Disabling SSL Certificate support temporarily
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
            // Execute post
            $result = curl_exec($ch);

            if ($result == FALSE) {
                die('Curl failed: ' . curl_error($ch));
            }
            // Close connection
            curl_close($ch);
        }
    }

    return $fcmFields;
}
function getAccessToken()
{
    // Fetch the file name from the settings table
    $file_name = DB::table('settings')
        ->where('variable', 'service_account_file')
        ->value('value');

    // Construct the file path in the storage/app/public directory
    $file_path = storage_path('app/public/' . $file_name);

    // Check if the file exists
    if (!file_exists($file_path)) {
        throw new \Exception('Service account file not found.');
    }

    // Initialize the Google Client
    $client = new Client();
    $client->setAuthConfig($file_path);
    $client->setScopes(['https://www.googleapis.com/auth/firebase.messaging']);

    // Fetch the access token
    $accessToken = $client->fetchAccessTokenWithAssertion()['access_token'];
    return $accessToken;
}

function deleteDetails($where, $table)
{
    try {
        DB::table($table)->where($where)->delete();
        return true;
    } catch (\Exception $e) {
        return false;
    }
}

function fetchProduct($user_id = NULL, $filter = NULL, $id = NULL, $category_id = NULL, $limit = 20, $offset = NULL, $sort = 'p.id', $order = 'DESC', $return_count = NULL, $is_deliverable = NULL, $seller_id = NULL, $brand_id = NULL, $store_id = NULL, $is_detailed_data = 0, $type = '', $from_seller = 0, $language_code = "")
{
    $sort = empty($sort) || $sort == "" ? 'p.id' : $sort;
    $order = empty($order) || $order == "" ? 'desc' : $order;
    $language_code = isset($language_code) && !empty($language_code) ? $language_code : get_language_code();
    // dd($language_code);
    $settings = getSettings('system_settings', true, true);
    $settings = json_decode($settings, true);
    $low_stock_limit = isset($settings['low_stock_limit']) ? $settings['low_stock_limit'] : 5;


    $discount_filter_data = (isset($filter['discount']) && !empty($filter['discount']))
        ? ', (CASE WHEN pv.special_price > 0 THEN ((pv.price - pv.special_price) / pv.price) * 100 ELSE 0 END) as cal_discount_percentage'
        : '';



    // $total_query = DB::table('products as p')
    //     ->select('p.id', 'p.category_id', 'p.no_of_ratings', 'p.brand', 'b.name as brand_name', DB::raw('GROUP_CONCAT(pa.attribute_value_ids) as attr_value_ids' . $discount_filter_data))
    //     ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
    //     ->leftJoin('brands as b', 'p.brand', '=', 'b.id')
    //     ->leftJoin('seller_data as sd', 'p.seller_id', '=', 'sd.id')
    //     ->leftJoin('seller_store as ss', 'p.seller_id', '=', 'ss.seller_id')
    //     ->leftJoin('product_variants as pv', 'p.id', '=', 'pv.product_id')
    //     ->leftJoin('product_attributes as pa', 'pa.product_id', '=', 'p.id')
    //     ->leftJoin('order_items as oi', 'oi.product_variant_id', '=', 'pv.id')
    //     ->leftJoin('taxes as tax_id', function ($join) {
    //         $join->on(DB::raw('FIND_IN_SET(tax_id.id, p.tax)'), '>', DB::raw('0'));
    //     })
    //     ->leftJoin('taxes as tax', function ($join) {
    //         $join->on(DB::raw('FIND_IN_SET(tax.id, p.tax)'), '>', DB::raw('0'));
    //     });
    $total_query = DB::table('products as p')
        ->select(
            'p.id',
            'p.category_id',
            'p.no_of_ratings',
            'p.brand',
            'b.name as brand_name',
            DB::raw('GROUP_CONCAT(pa.attribute_value_ids) as attr_value_ids' . $discount_filter_data),
            'ss.store_name'
        )
        ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
        ->leftJoin('brands as b', 'p.brand', '=', 'b.id')
        ->leftJoin('seller_data as sd', 'p.seller_id', '=', 'sd.id')
        ->leftJoin('seller_store as ss', function ($join) use ($store_id) {
            $join->on('p.seller_id', '=', 'ss.seller_id')
                ->where('ss.store_id', '=', $store_id);
        })
        ->leftJoin('product_variants as pv', 'p.id', '=', 'pv.product_id')
        ->leftJoin('product_attributes as pa', 'pa.product_id', '=', 'p.id')
        ->leftJoin('order_items as oi', 'oi.product_variant_id', '=', 'pv.id')
        ->leftJoin('taxes as tax_id', function ($join) {
            $join->on(DB::raw('FIND_IN_SET(tax_id.id, p.tax)'), '>', DB::raw('0'));
        })
        ->leftJoin('taxes as tax', function ($join) {
            $join->on(DB::raw('FIND_IN_SET(tax.id, p.tax)'), '>', DB::raw('0'));
        });
    if (isset($filter['show_only_stock_product']) && $filter['show_only_stock_product'] == 1) {
        $total_query->where(function ($query) {
            $query->whereNotNull('p.stock_type');
        });
    }
    if (isset($filter['rating'])) {
        $total_query->where(function ($query) use ($filter) {
            $query->where('p.rating', '>=', $filter['rating']);
        });
    }

    if (isset($sort) && $sort == 'most_popular_products') {
        $sort = 'p.rating';
        $order = 'desc';
    }
    if ((isset($filter['minimum_price']) && $filter['minimum_price'] !== '') || (isset($filter['maximum_price']) && $filter['maximum_price'] !== '')) {
        $minPrice = $filter['minimum_price'];
        $maxPrice = $filter['maximum_price'];



        $total_query->where(function ($query) use ($minPrice, $maxPrice) {
            $query->where(function ($query) use ($minPrice, $maxPrice) {
                $query->where('pv.special_price', '>', 0)
                    ->whereBetween('pv.special_price', [$minPrice, $maxPrice]);
            })
                ->orWhere(function ($query) use ($minPrice, $maxPrice) {
                    $query->where('pv.special_price', '=', 0)
                        ->whereBetween('pv.price', [$minPrice, $maxPrice]);
                });
        });
    }

    if ($sort == 'pv.price' && !empty($sort) && $sort != null) {
        $expression = "IF(pv.special_price > 0,
            IF(p.is_prices_inclusive_tax = 1,
                pv.special_price,
                pv.special_price + ((pv.special_price * p.tax) / 100)
            ),
            IF(p.is_prices_inclusive_tax = 1,
                pv.price,
                pv.price + ((pv.price * p.tax) / 100)
            )
        ) " . $order;
        $total_query->orderByRaw($expression);
    }

    // if (isset($filter['show_only_active_products']) && $filter['show_only_active_products'] == 0) {

    //     $where = [];
    // } else {
    //     $where = ['p.status' => '1', 'pv.status' => 1, 'sd.status' => 1];
    // }

    if (isset($filter['show_only_active_products']) && $filter['show_only_active_products'] == 0) {
        $where = [];
    } else {
        // dd(isset($from_seller) && $from_seller == 0);
        $where = ['pv.status' => 1, 'sd.status' => 1];

        if (isset($from_seller) && $from_seller == 0) {
            // dd('here');
            $where['p.status'] = '1';
        }
    }
    if (isset($type) && $type == 'simple_product') {
        $total_query->where('p.type', 'simple_product');
    }
    if (isset($type) && $type == 'variable_product') {
        $total_query->where('p.type', 'variable_product');
    }
    if (isset($type) && $type == 'physical_product') {
        $total_query->whereIn('p.type', ['simple_product', 'variable_product']);
    }

    if (isset($type) && $type == 'digital_product') {
        $total_query->where('p.type', 'digital_product');
    }

    if (isset($filter['show_only_stock_product']) && $filter['show_only_stock_product'] == 1) {
        $total_query->where(function ($query) {
            $query->whereNotNull('p.stock')
                ->orWhereNotNull('pv.stock');
        });
    }

    if (isset($filter['show_only_physical_product']) && $filter['show_only_physical_product'] == 1) {
        $total_query->whereNotIn('p.type', ['digital_product']);
    }

    if (isset($filter) && !empty($filter['product_type']) && strtolower($filter['product_type']) == 'most_selling_products') {

        $sort = 'total_sale';
        $order = 'desc';
    }

    if (isset($filter) && !empty($filter['search'])) {

        $tags = explode(" ", $filter['search']);
        $total_query->where(function ($total_query) use ($tags, $filter) {
            foreach ($tags as $i => $tag) {
                if ($i == 0) {
                    $total_query->where('p.tags', 'like', '%' . trim($tag) . '%');
                } else {
                    $total_query->orWhere('p.tags', 'like', '%' . trim($tag) . '%');
                }
            }
            $total_query->orWhere('p.name', 'like', '%' . trim($filter['search']) . '%');
        });
    }

    if (isset($filter) && !empty($filter['flag']) && $filter['flag'] != "null" && $filter['flag'] != "") {
        $flag = $filter['flag'];
        if ($flag == 'low') {
            $total_query->where(function ($total_query) use ($low_stock_limit) {
                $total_query->where(function ($total_query) {
                    $low_stock_limit = isset($settings['low_stock_limit']) ? $settings['low_stock_limit'] : 5;
                    $total_query->whereNotNull('p.stock_type')
                        ->where('p.stock', '<=', $low_stock_limit)
                        ->where('p.availability', '=', '1');
                })->orWhere(function ($total_query) use ($low_stock_limit) {
                    $total_query->where('pv.stock', '<=', $low_stock_limit)
                        ->where('pv.availability', '=', '1');
                });
            });
        } else {
            $total_query->where(function ($total_query) {
                $total_query->orWhere('p.availability', '=', '0')
                    ->orWhere('pv.availability', '=', '0')
                    ->where('p.stock', '=', '0')
                    ->orWhere('pv.stock', '=', '0');
            });
        }
    }
    if (isset($filter['max_price']) && $filter['max_price'] > 0 && isset($filter['min_price']) && $filter['min_price'] > 0) {
        $max_price = $filter['max_price'];
        $min_price = $filter['min_price'];
        $total_query->where(function ($total_query) use ($max_price, $min_price) {
            $total_query->where(function ($total_query) use ($max_price, $min_price) {
                $total_query->whereRaw("(
                    CASE
                        WHEN pv.special_price > 0 THEN
                            pv.special_price * (1 + (IFNULL((
                                SELECT MAX(tax.percentage)
                                FROM taxes as tax
                                WHERE FIND_IN_SET(tax.id, p.tax)
                            ), 0) / 100))
                        ELSE
                            pv.price * (1 + (IFNULL((
                                SELECT MAX(tax.percentage)
                                FROM taxes as tax
                                WHERE FIND_IN_SET(tax.id, p.tax)
                            ), 0) / 100))
                    END
                ) BETWEEN ? AND ?", [$min_price, $max_price]);
            });
        });
    }

    if (isset($filter) && !empty($filter['tags'])) {
        $tags = explode(",", $filter['tags']);
        $tags = explode("|", implode("|", $tags));
        $total_query->where(function ($total_query) use ($tags) {
            foreach ($tags as $i => $tag) {
                if ($i == 0) {
                    $total_query->where('p.tags', 'like', '%' . trim($tag) . '%');
                } else {
                    $total_query->orWhere('p.tags', 'like', '%' . trim($tag) . '%');
                }
            }
        });
    }

    if (isset($filter) && !empty($filter['brand'])) {
        $total_query->where('p.brand', trim($filter['brand']));
    }

    if (isset($filter) && !empty($filter['slug'])) {
        $total_query->where('p.slug', $filter['slug']);
    }

    if (isset($seller_id) && !empty($seller_id) && $seller_id != "") {
        $total_query->where('p.seller_id', $seller_id);
    }

    if (isset($filter) && !empty($filter['attribute_value_ids'])) {
        $attributeValueIds = $filter['attribute_value_ids'];
        foreach ($attributeValueIds as $attributeValueId) {
            $total_query->whereRaw('FIND_IN_SET(?, pa.attribute_value_ids) > 0', [$attributeValueId]);
        }
    }
    if (isset($category_id) && !empty($category_id)) {
        if (is_array($category_id) && !empty($category_id)) {
            $total_query->where(function ($total_query) use ($category_id) {
                $total_query->whereIn('p.category_id', $category_id)
                    ->orWhereIn('c.parent_id', $category_id);
            });
        } else {
            $where['p.category_id'] = $category_id;
        }
    }

    if (isset($brand_id) && !empty($brand_id)) {
        if (is_array($brand_id) && !empty($brand_id)) {
            $total_query->where(function ($total_query) use ($brand_id) {
                $total_query->whereIn('p.brand', $brand_id);
            });
        } else {
            $where['p.brand'] = $brand_id;
        }
    }

    if (isset($store_id) && !empty($store_id)) {
        $where['p.store_id'] = $store_id;
    }

    $total_query->where($where);

    if (isset($filter) && !empty($filter['product_type']) && strtolower($filter['product_type']) == 'products_on_sale') {
        $total_query->where('pv.special_price', '>', '0');
    }

    if (isset($filter) && !empty($filter['product_type']) && strtolower($filter['product_type']) == 'top_rated_products') {
        $total_query->where('p.no_of_ratings', '>', 0)
            ->orderBy('p.rating', 'desc')
            ->orderBy('p.no_of_ratings', 'desc');
    }

    if (isset($filter) && !empty($filter['product_type']) && strtolower($filter['product_type']) == 'top_rated_product_including_all_products') {
        $sort = 'p.rating';
        $order = 'desc';
        $total_query->orderBy('p.rating', 'desc')
            ->orderBy('p.no_of_ratings', 'desc');
    }

    if (isset($filter) && !empty($filter['product_type']) && $filter['product_type'] == 'new_added_products') {
        $sort = 'p.id';
        $order = 'desc';
    }

    if (isset($filter) && !empty($filter['product_variant_ids'])) {
        if (is_array($filter['product_variant_ids'])) {
            $total_query->whereIn('pv.id', $filter['product_variant_ids']);
        }
    }

    if (isset($id) && !empty($id) && $id != null) {

        if (is_array($id) && !empty($id)) {
            $total_query->whereIn('p.id', $id);
        } else {
            $total_query->where('p.id', $id);
        }
    }

    if (!isset($filter['flag']) && empty($filter['flag'])) {
        $total_query->where(function ($total_query) {
            $total_query->orWhere('c.status', '1')
                ->orWhere('c.status', '0');
        });
    }

    if (isset($filter['discount']) && !empty($filter['discount']) && $filter['discount'] != "") {
        $discount_pr = $filter['discount'];
        $total_query->groupBy('p.id')
            ->havingRaw("cal_discount_percentage >= $discount_pr")
            ->havingRaw("cal_discount_percentage > 0");
    } else {
        $total_query->groupBy('p.id');
    }

    if ($sort !== null || $order !== null && $sort !== 'pv.price') {
    } else {
        $total_query->orderBy('p.row_order', 'asc');
    }

    if ($sort !== null && $sort == 'discount') {
        $total_query->orderByRaw('pv.special_price > 0 DESC');
    }
    if (isset($from_seller) && $from_seller == 1) {
        $total_query->whereIn('p.status', [1, 2]);
    }
    if (isset($filter['discount']) && !empty($filter['discount']) && $filter['discount'] != "") {
        $total_query->orderBy('cal_discount_percentage', 'desc');
    } else {
        if ($sort !== null || $order !== null && $sort !== 'pv.price') {
        }
        $total_query->orderBy('p.row_order', 'asc');
    }
    $total_query->groupBy('p.id');
    $total_data = $total_query->get();

    $total = $total_data != '' ? count($total_data) : '';
    $category_ids = collect($total_data)->pluck('category_id')->unique()->values()->all();
    $brand_ids = collect($total_data)->pluck('brand')->unique()->values()->all();

    $discount_filter_data = (isset($filter['discount']) && !empty($filter['discount']))
        ? ', (CASE WHEN pv.special_price > 0 THEN ((pv.price - pv.special_price) / pv.price) * 100 ELSE 0 END) as cal_discount_percentage'
        : '';

    $product_query = DB::table('products as p')
        ->select(
            'p.*',
            'b.name as brand_name',
            'b.slug as brand_slug',
            'ss.rating as seller_rating',
            'ss.slug as seller_slug',
            'ss.no_of_ratings as seller_no_of_ratings',
            'ss.logo as seller_profile',
            'ss.store_name as store_name',
            'ss.store_description',
            'u.username as seller_name',
            'c.name as category_name',
            'c.slug as category_slug',
            DB::raw('SUM(oi.quantity) as total_sale'),
            DB::raw('(SELECT GROUP_CONCAT(tax.percentage) FROM taxes as tax WHERE FIND_IN_SET(tax.id, p.tax)) as tax_percentage'),
            DB::raw('GROUP_CONCAT(DISTINCT tax_id.id) as tax_id'),
            DB::raw('GROUP_CONCAT(DISTINCT pa.attribute_value_ids) as attr_value_ids' . $discount_filter_data)
        )
        ->leftJoin('seller_data as sd', 'p.seller_id', '=', 'sd.id')
        // ->leftJoin('seller_store as ss', 'p.seller_id', '=', 'ss.seller_id')
        ->leftJoin('seller_store as ss', function ($join) use ($store_id) {
            $join->on('p.seller_id', '=', 'ss.seller_id')
                ->where('ss.store_id', '=', $store_id);
        })
        ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
        ->leftJoin('brands as b', 'p.brand', '=', 'b.id')
        ->leftJoin('users as u', 'u.id', '=', 'sd.user_id')
        ->leftJoin('product_variants as pv', 'p.id', '=', 'pv.product_id')
        ->leftJoin('order_items as oi', 'oi.product_variant_id', '=', 'pv.id')
        ->leftJoin('taxes as tax_id', function ($join) {
            $join->on(DB::raw('FIND_IN_SET(tax_id.id, p.tax)'), '>', DB::raw('0'));
        })
        ->leftJoin('product_attributes as pa', 'pa.product_id', '=', 'p.id')
        ->leftJoin('taxes as tax', function ($join) {
            $join->on(DB::raw('FIND_IN_SET(tax.id, p.tax)'), '>', DB::raw('0'));
        });

    if (isset($filter) && !empty($filter['search'])) {
        $tags = explode(" ", $filter['search']);
        $product_query->where(function ($product_query) use ($tags, $filter) {
            foreach ($tags as $i => $tag) {
                if ($i == 0) {
                    $product_query->where('p.tags', 'like', '%' . trim($tag) . '%');
                } else {
                    $product_query->orWhere('p.tags', 'like', '%' . trim($tag) . '%');
                }
            }
            $product_query->orWhere('p.name', 'like', '%' . trim($filter['search']) . '%');
        });
    }

    if (isset($filter) && !empty($filter['flag']) && $filter['flag'] != "null" && $filter['flag'] != "") {
        $flag = $filter['flag'];
        if ($flag == 'low') {
            $product_query->where(function ($product_query) use ($low_stock_limit) {
                $product_query->where(function ($product_query) {
                    $low_stock_limit = isset($settings['low_stock_limit']) ? $settings['low_stock_limit'] : 5;
                    $product_query->whereNotNull('p.stock_type')
                        ->where('p.stock', '<=', $low_stock_limit)
                        ->where('p.availability', '=', '1');
                })->orWhere(function ($product_query) use ($low_stock_limit) {
                    $product_query->where('pv.stock', '<=', $low_stock_limit)
                        ->where('pv.availability', '=', '1');
                });
            });
        } else {
            $product_query->where(function ($product_query) {
                $product_query->orWhere('p.availability', '=', '0')
                    ->orWhere('pv.availability', '=', '0')
                    ->where('p.stock', '=', '0')
                    ->orWhere('pv.stock', '=', '0');
            });
        }
    }

    if (isset($filter) && !empty($filter['tags'])) {
        $tags = explode(",", $filter['tags']);
        $product_query->where(function ($product_query) use ($tags) {
            foreach ($tags as $i => $tag) {
                if ($i == 0) {
                    $product_query->where('p.tags', 'like', '%' . trim($tag) . '%');
                } else {
                    $product_query->orWhere('p.tags', 'like', '%' . trim($tag) . '%');
                }
            }
        });
    }

    if (isset($filter) && !empty($filter['brand'])) {
        $product_query->where('p.brand', trim($filter['brand']));
    }

    if (isset($filter) && !empty($filter['slug'])) {
        $product_query->where('p.slug', $filter['slug']);
    }

    if (isset($filter['max_price']) && $filter['max_price'] > 0 && isset($filter['min_price']) && $filter['min_price'] > 0) {
        $max_price = $filter['max_price'];
        $min_price = $filter['min_price'];
        $product_query->where(function ($product_query) use ($max_price, $min_price) {
            $product_query->where(function ($product_query) use ($max_price, $min_price) {
                $product_query->whereRaw("(
                    CASE
                        WHEN pv.special_price > 0 THEN
                            pv.special_price * (1 + (IFNULL((
                                SELECT MAX(tax.percentage)
                                FROM taxes as tax
                                WHERE FIND_IN_SET(tax.id, p.tax)
                            ), 0) / 100))
                        ELSE
                            pv.price * (1 + (IFNULL((
                                SELECT MAX(tax.percentage)
                                FROM taxes as tax
                                WHERE FIND_IN_SET(tax.id, p.tax)
                            ), 0) / 100))
                    END
                ) BETWEEN ? AND ?", [$min_price, $max_price]);
            });
        });
    }

    if (isset($filter) && !empty($filter['attribute_value_ids'])) {
        $attributeValueIds = $filter['attribute_value_ids'];
        foreach ($attributeValueIds as $attributeValueId) {
            $product_query->whereRaw('FIND_IN_SET(?, pa.attribute_value_ids) > 0', [$attributeValueId]);
        }
    }
    // if (isset($filter['show_only_active_products']) && $filter['show_only_active_products'] == 0) {

    //     $where = [];
    // } else {
    //     $where = ['p.status' => '1', 'pv.status' => 1, 'sd.status' => 1];
    // }
    if (isset($filter['show_only_active_products']) && $filter['show_only_active_products'] == 0) {
        $where = [];
    } else {
        $where = ['pv.status' => 1, 'sd.status' => 1];

        if (isset($from_seller) && $from_seller == 0) {
            $where['p.status'] = '1';
        }
    }
    if (isset($type) && $type == 'simple_product') {
        $product_query->where('p.type', 'simple_product');
    }
    if (isset($type) && $type == 'variable_product') {
        $product_query->where('p.type', 'variable_product');
    }
    if (isset($type) && $type == 'physical_product') {
        $product_query->whereIn('p.type', ['simple_product', 'variable_product']);
    }

    if (isset($type) && $type == 'digital_product') {
        $product_query->where('p.type', 'digital_product');
    }

    if (isset($filter['show_only_stock_product']) && $filter['show_only_stock_product'] == 1) {
        $product_query->where(function ($query) {
            $query->whereNotNull('p.stock_type');
        });
    }
    if (isset($filter['rating'])) {
        $product_query->where(function ($query) use ($filter) {
            $query->where('p.rating', '>=', $filter['rating']);
        });
    }
    if (isset($sort) && $sort == 'most_popular_products') {
        $sort = 'p.rating';
        $order = 'desc';
    }
    if ((isset($filter['minimum_price']) && $filter['minimum_price'] !== '') || (isset($filter['maximum_price']) && $filter['maximum_price'] !== '')) {

        $minPrice = $filter['minimum_price'];
        $maxPrice = $filter['maximum_price'];

        $product_query->where(function ($query) use ($minPrice, $maxPrice) {
            $query->where(function ($query) use ($minPrice, $maxPrice) {
                $query->where('pv.special_price', '>', 0)
                    ->whereBetween('pv.special_price', [$minPrice, $maxPrice]);
            })
                ->orWhere(function ($query) use ($minPrice, $maxPrice) {
                    $query->where('pv.special_price', '=', 0)
                        ->whereBetween('pv.price', [$minPrice, $maxPrice]);
                });
        });
    }

    if (isset($filter['show_only_physical_product']) && $filter['show_only_physical_product'] == 1) {
        $product_query->whereNotIn('p.type', ['digital_product']);
    }
    if (isset($filter) && !empty($filter['product_type']) && strtolower($filter['product_type']) == 'most_selling_products') {

        $sort = 'total_sale';
        $order = 'desc';
    }
    if (isset($category_id) && !empty($category_id)) {
        if (is_array($category_id) && !empty($category_id)) {
            $product_query->where(function ($product_query) use ($category_id) {
                $product_query->whereIn('p.category_id', $category_id)
                    ->orWhereIn('c.parent_id', $category_id);
            });
        } else {
            $product_query->where('p.category_id', $category_id);
        }
    }

    if (isset($brand_id) && !empty($brand_id)) {
        if (is_array($brand_id) && !empty($brand_id)) {
            $product_query->where(function ($product_query) use ($brand_id) {
                $product_query->whereIn('p.brand', $brand_id);
            });
        } else {
            $where['p.brand'] = $brand_id;
        }
    }

    if (isset($store_id) && !empty($store_id)) {
        $product_query->where('p.store_id', $store_id);
    }
    if (isset($filter) && !empty($filter['product_type']) && strtolower($filter['product_type']) == 'products_on_sale') {
        $product_query->where('pv.special_price', '>', '0');
    }
    if (isset($filter) && !empty($filter['product_type']) && strtolower($filter['product_type']) == 'top_rated_product_including_all_products') {
        $sort = 'p.rating';
        $order = 'desc';
        $product_query->orderBy('p.rating', 'desc')
            ->orderBy('p.no_of_ratings', 'desc');
    }
    if (isset($filter) && !empty($filter['product_type']) && strtolower($filter['product_type']) == 'top_rated_products') {
        $product_query->where('p.no_of_ratings', '>', 0)
            ->orderBy('p.rating', 'desc')
            ->orderBy('p.no_of_ratings', 'desc');
    }
    if ($sort == 'pv.price' && !empty($sort) && $sort != null) {
        $expression = "IF(pv.special_price > 0,
            IF(p.is_prices_inclusive_tax = 1,
                pv.special_price,
                pv.special_price + ((pv.special_price * p.tax) / 100)
            ),
            IF(p.is_prices_inclusive_tax = 1,
                pv.price,
                pv.price + ((pv.price * p.tax) / 100)
            )
        ) " . $order;
        $product_query->orderByRaw($expression);
    }

    if (isset($id) && !empty($id) && $id !== null) {
        if (is_array($id) && !empty($id)) {

            $product_query = $product_query->whereIn('p.id', $id);
        } else {
            if (isset($filter) && !empty($filter['is_similar_products']) && $filter['is_similar_products'] == '1') {
                $product_query->where('p.id', '!=', $id);
            } else {

                $product_query->where('p.id', $id);
            }
        }
    }

    $product_query->where($where)->orderBy($sort, $order);

    if (isset($seller_id) && !empty($seller_id) && $seller_id != "") {
        $product_query->where('p.seller_id', $seller_id);
    }

    if (isset($filter['show_only_stock_product']) && $filter['show_only_stock_product'] == 1) {
        $product_query->where(function ($query) {
            $query->whereNotNull('p.stock')
                ->orWhereNotNull('pv.stock');
        });
    }
    if (!isset($filter['flag']) && empty($filter['flag'])) {
        $product_query->where(function ($product_query) {
            $product_query->orWhere('c.status', '1')
                ->orWhere('c.status', '0');
        });
    }
    if (isset($filter['discount']) && !empty($filter['discount']) && $filter['discount'] != "") {
        $discount_pr = $filter['discount'];
        $product_query->groupBy('p.id')
            ->havingRaw("cal_discount_percentage >= $discount_pr")
            ->havingRaw("cal_discount_percentage > 0");
    } else {
        $product_query->groupBy('p.id');
    }

    if ($limit != NULL || $offset != NULL) {

        $product_query->skip($offset)->take($limit);
    }

    if (isset($filter) && !empty($filter['product_type']) && $filter['product_type'] == 'new_added_products') {
        $product_query->orderBy('p.id', 'desc');
    }

    if (isset($filter) && !empty($filter['product_type']) && $filter['product_type'] == 'old_products_first') {
        $product_query->orderBy('p.id', 'asc');
    }

    if (isset($filter) && !empty($filter['product_variant_ids'])) {
        if (is_array($filter['product_variant_ids'])) {

            $product_query->whereIn('pv.id', $filter['product_variant_ids']);
        }
    }
    if ($sort !== null && $sort == 'discount') {
        $product_query->orderByRaw('pv.special_price > 0 DESC');
    }
    if (isset($from_seller) && $from_seller == 1) {
        $product_query->whereIn('p.status', [1, 2]);
    }
    if (isset($filter['discount']) && !empty($filter['discount']) && $filter['discount'] != "") {
        $product_query->orderBy('cal_discount_percentage', 'desc');
    } else {
        if ($sort !== null || $order !== null && $sort !== 'pv.price') {
            $product_query->orderBy('pv.price', 'desc');
        }
        $product_query->orderBy('p.row_order', 'asc');
    }
    $product_query->groupBy('p.id');
    // dd($product_query->toSql(),$product_query->getBindings());
    $product = $product_query->get()->toArray();

    $attribute_values_ids = array();
    $temp = [];
    $min_price = getPrice('min', $store_id);
    $max_price = getPrice('max', $store_id);

    $response['total'] = '0';

    $weekly_sales = DB::table('order_items as oi')
        ->join('product_variants as pv', 'oi.product_variant_id', '=', 'pv.id')
        ->join('products as p', 'pv.product_id', '=', 'p.id')
        ->select('p.id', DB::raw('SUM(oi.quantity) as weekly_sale'))
        ->where('oi.created_at', '>=', now()->subDays(7))
        ->where('oi.order_type', '=', 'regular_order')
        ->groupBy('p.id')
        ->pluck('weekly_sale', 'id')
        ->toArray();
    // dd($weekly_sales);
    $max_weekly_sale = !empty($weekly_sales) ? max($weekly_sales) : 0;

    if (!empty($product)) {
        for ($i = 0; $i < count($product); $i++) {
            // dd($product[$i]->total_sale);
            $productId = $product[$i]->id;
            $product[$i]->translated_name = json_decode($product[$i]->name);
            $product[$i]->translated_short_description = json_decode($product[$i]->short_description);
            if (($is_detailed_data != null && $is_detailed_data == 1)) {
                $product_faq = getProductFaqs('', $product[$i]->id);
                foreach ($product_faq['data'] as $faq) {
                    $faq->answer = $faq->answer ?? "";
                }

                $product[$i]->product_faq = isset($product_faq) && !empty($product_faq) ? $product_faq : [];

                $rating = fetchRating($product[$i]->id, '', 8, 0, '', 'desc', '', 1);
                $product[$i]->product_rating_data = $rating ?? [];

                $product[$i]->price_range = getPriceRangeOfProduct($productId);
            }
            $product[$i]->attributes = getAttributeValuesByPid($productId);
            // dd($product[$i]->attributes);
            $variants = getVariantsValuesByPid($product[$i]->id);

            $total_stock = 0;

            foreach ($variants as $variant) {

                $stock = (isset($variant->stock) && !empty($variant->stock)) ? $variant->stock : 0;
                $total_stock += $stock;
                $product[$i]->total_stock = isset($total_stock) && !empty($total_stock) ? (string) $total_stock : '';
            }
            $product[$i]->variants = $variants;

            $product[$i]->min_max_price = getMinMaxPriceOfProduct($productId);

            $product[$i]->tax_id = intval($product[$i]->tax_id) > 0 ? $product[$i]->tax_id : '0';

            $taxes = [];
            $tax_ids = explode(",", $product[$i]->tax_id);

            $taxes = Tax::whereIn('id', $tax_ids)->get()->toArray();
            $taxes = array_column($taxes, 'title');
            // $product[$i]->tax_names = implode(",", $taxes);

            $translatedTaxes = [];

            foreach ($taxes as $tax) {
                $translatedTaxes[] = getDynamicTranslation('taxes', 'title', $product[$i]->tax_id, $language_code);
            }

            $product[$i]->tax_names = implode(",", $translatedTaxes);

            $tax_percentages = [];
            $tax_ids = explode(",", $product[$i]->tax_id);

            $tax_percentages = Tax::whereIn('id', $tax_ids)->get()->toArray();
            $tax_percentages = array_column($tax_percentages, 'percentage');
            $product[$i]->tax_percentage = implode(",", $tax_percentages);

            // Define properties and their default values in an array
            $key = [
                'product_type' => 'type',
                'stock_type' => 'stock_type',
                'product_identity' => 'product_identity',
                'stock' => 'stock',
                'relative_path' => 'image',
                'video_relative_path' => 'video',
                'seller_no_of_ratings' => 'seller_no_of_ratings',
                'video_type' => 'video_type',
                'attr_value_ids' => 'attr_value_ids',
                'made_in' => 'made_in',
                'hsn_code' => 'hsn_code',
                'brand' => 'brand',
                'warranty_period' => 'warranty_period',
                'guarantee_period' => 'guarantee_period',
                'total_allowed_quantity' => 'total_allowed_quantity',
                'download_allowed' => 'download_allowed',
                'download_type' => 'download_type',
                'download_link' => 'download_link',
                'status' => 'status',
                'attribute_value_ids' => $product[$i]->attr_value_ids != "null" ? $product[$i]->attr_value_ids : '',
                'brand_name' => 'brand_name',
                'brand_slug' => 'brand_slug',
            ];

            // Set properties using array mapping
            foreach ($key as $value => $source) {
                $product[$i]->$value = isset($product[$i]->$source) && (!empty($product[$i]->$source) || $product[$i]->$source != "") ? $product[$i]->$source : '';
            }

            /* outputing escaped data */

            $product[$i]->deliverable_type = $product[$i]->deliverable_type;
            $product[$i]->name = getDynamicTranslation('products', 'name', $product[$i]->id, $language_code);

            // new arrival tags based on newly added product(weekly)

            if (isset($product[$i]->created_at) && strtotime($product[$i]->created_at) >= strtotime('-7 days')) {
                $product[$i]->new_arrival = true;
            } else {
                $product[$i]->new_arrival = false;
            }

            // end new arrival tags based on newly added product(weekly)

            // best seller tag based on most selling product (weekly)

            $weeklySale = $weekly_sales[$productId] ?? 0;
            $product[$i]->best_seller = ($max_weekly_sale > 0 && $weeklySale >= ($max_weekly_sale * 0.8));

            // end best seller tag based on most selling product (weekly)

            $product[$i]->store_name = (isset($product[$i]->store_name) && $product[$i]->store_name !== null) ? outputEscaping($product[$i]->store_name) : "";
            $product[$i]->seller_rating = (isset($product[$i]->seller_rating) && !empty($product[$i]->seller_rating)) ? outputEscaping(number_format($product[$i]->seller_rating, 1)) : 0;
            $product[$i]->store_description = (isset($product[$i]->store_description) && !empty($product[$i]->store_description)) ? outputEscaping($product[$i]->store_description) : "";
            $product[$i]->seller_profile = outputEscaping(asset($product[$i]->seller_profile));
            $product[$i]->seller_name = outputEscaping($product[$i]->seller_name);
            $product[$i]->short_description = getDynamicTranslation('products', 'short_description', $product[$i]->id, $language_code);
            $product[$i]->description = (isset($product[$i]->description) && !empty($product[$i]->description)) ? outputEscaping($product[$i]->description) : "";
            $product[$i]->extra_description = (isset($product[$i]->extra_description) && !empty($product[$i]->extra_description) && $product[$i]->extra_description != 'NULL') ? outputEscaping($product[$i]->extra_description) : "";
            $product[$i]->pickup_location = isset($product[$i]->pickup_location) && !empty($product[$i]->pickup_location) ? $product[$i]->pickup_location : '';
            $product[$i]->download_link = isset($product[$i]->download_link) && ($product[$i]->download_link != '') ? getMediaImageUrl($product[$i]->download_link) : '';
            $product[$i]->seller_slug = isset($product[$i]->seller_slug) && !empty($product[$i]->seller_slug) ? outputEscaping($product[$i]->seller_slug) : "";
            $product[$i]->total_sale = isset($product[$i]->total_sale) && !empty($product[$i]->total_sale) ? outputEscaping($product[$i]->total_sale) : "0";
            if (isset($filter['discount']) && !empty($filter['discount']) && $filter['discount'] != "") {
                $product[$i]->cal_discount_percentage = outputEscaping(number_format($product[$i]->cal_discount_percentage, 2));
            }
            $product[$i]->cancelable_till = isset($product[$i]->cancelable_till) && !empty($product[$i]->cancelable_till) ? $product[$i]->cancelable_till : '';
            $product[$i]->indicator = isset($product[$i]->indicator) && !empty($product[$i]->indicator) ? (string) $product[$i]->indicator : '0';
            $product[$i]->deliverable_zones_ids = isset($product[$i]->deliverable_zones) && !empty($product[$i]->deliverable_zones) ? $product[$i]->deliverable_zones : '';
            $product[$i]->rating = outputEscaping(number_format($product[$i]->rating, 2));
            $product[$i]->availability = isset($product[$i]->availability) && ($product[$i]->availability != "") ? intval($product[$i]->availability) : '';
            $product[$i]->stock = isset($product[$i]->stock) && ($product[$i]->stock != "") ? (string) $product[$i]->stock : '';
            $product[$i]->sku = isset($product[$i]->sku) && ($product[$i]->sku != "") ? $product[$i]->sku : '';
            /* getting zones from ids */

            if ($product[$i]->deliverable_type != 'NONE' && $product[$i]->deliverable_type != 'ALL') {
                $zones = [];
                $zone_ids = explode(",", $product[$i]->deliverable_zones);
                $zones = Zone::whereIn('id', $zone_ids)->get();

                $translatedZones = [];

                foreach ($zones as $zone) {
                    $translatedZones[] = getDynamicTranslation('zones', 'name', $zone->id, $language_code);
                }

                $product[$i]->deliverable_zones = implode(",", $translatedZones);
            } else {
                $product[$i]->deliverable_zones = '';
            }
            $product[$i]->category_name = (isset($product[$i]->category_name) && !empty($product[$i]->category_name)) ? getDynamicTranslation('categories', 'name', $product[$i]->category_id, $language_code) : '';
            $product[$i]->brand_name = (isset($product[$i]->brand_name) && !empty($product[$i]->brand_name)) ? getDynamicTranslation('brands', 'name', $product[$i]->brand, $language_code) : '';
            /* check product delivrable or not */

            if ($is_deliverable != NULL) {
                $zipcode = fetchDetails('zipcodes', ['id' => $is_deliverable], '*');

                if (!empty($zipcode)) {
                    $product[$i]->is_deliverable = isProductDelivarable($type = 'zipcode', $zipcode[0]->id, $product[$i]->id);
                } else {
                    $product[$i]['is_deliverable'] = false;
                }
            } else {
                $product[$i]->is_deliverable = false;
            }
            if ($product[$i]->deliverable_type == 1) {
                $product[$i]->is_deliverable = true;
            }
            $product[$i]->tags = (!empty($product[$i]->tags)) ? explode(",", $product[$i]->tags) : [];

            $product[$i]->video = (isset($product[$i]->video_type) && (!empty($product[$i]->video_type) || $product[$i]->video_type != NULL)) ? (($product[$i]->video_type == 'youtube' || $product[$i]->video_type == 'vimeo') ? $product[$i]->video : asset('storage/' . $product[$i]->video)) : "";
            $product[$i]->minimum_order_quantity = isset($product[$i]->minimum_order_quantity) && (!empty($product[$i]->minimum_order_quantity)) ? $product[$i]->minimum_order_quantity : 1;
            $product[$i]->quantity_step_size = isset($product[$i]->quantity_step_size) && (!empty($product[$i]->quantity_step_size)) ? $product[$i]->quantity_step_size : 1;

            if (!empty($product[$i]->variants)) {
                $count_stock = [];
                $is_purchased_count = [];
                for ($k = 0; $k < count($product[$i]->variants); $k++) {
                    $product[$i]->variants[$k]->product_name = getDynamicTranslation('products', 'name', $product[$i]->variants[$k]->product_id, $language_code);
                    $product[$i]->variants[$k]->attribute_set = isset($product[$i]->variants[$k]->attribute_set) && ($product[$i]->variants[$k]->attribute_set != null) ? $product[$i]->variants[$k]->attribute_set : '';
                    $product[$i]->variants[$k]->stock_type = isset($product[$i]->stock_type) ? (string) $product[$i]->stock_type : '';
                    $product[$i]->variants[$k]->sku = isset($product[$i]->variants[$k]->sku) && ($product[$i]->variants[$k]->sku != null) ? $product[$i]->variants[$k]->sku : '';

                    $product[$i]->variants[$k]->variant_ids = isset($product[$i]->variants[$k]->variant_ids) && ($product[$i]->variants[$k]->variant_ids != null) ? $product[$i]->variants[$k]->variant_ids : '';
                    $product[$i]->variants[$k]->attr_name = isset($product[$i]->variants[$k]->attr_name) && ($product[$i]->variants[$k]->attr_name != null) ? $product[$i]->variants[$k]->attr_name : '';
                    $product[$i]->variants[$k]->variant_values = isset($product[$i]->variants[$k]->variant_values) && ($product[$i]->variants[$k]->variant_values != null) ? $product[$i]->variants[$k]->variant_values : '';
                    $product[$i]->variants[$k]->attribute_value_ids = isset($product[$i]->variants[$k]->attribute_value_ids) && ($product[$i]->variants[$k]->attribute_value_ids != null) ? $product[$i]->variants[$k]->attribute_value_ids : '';
                    $variant_other_images = $variant_other_images_sm = $variant_other_images_md = json_decode((string) $product[$i]->variants[$k]->images, 1);
                    if (!empty($variant_other_images[0]) && isset($variant_other_images[0])) {

                        $product[$i]->variants[$k]->variant_relative_path = isset($product[$i]->variants[$k]->images) && !empty($product[$i]->variants[$k]->images) ? json_decode($product[$i]->variants[$k]->images) : [];

                        $counter = 0;
                        foreach ($variant_other_images_md as $row) {
                            $variant_other_images_md[$counter] = getImageUrl($variant_other_images_md[$counter], 'thumb', 'md');
                            $counter++;
                        }
                        $product[$i]->variants[$k]->images_md = isset($variant_other_images_md) && !empty($variant_other_images_md) ? $variant_other_images_md : "";

                        $counter = 0;
                        foreach ($variant_other_images_sm as $row) {
                            $variant_other_images_sm[$counter] = getImageUrl($variant_other_images_sm[$counter], 'thumb', 'sm');
                            $counter++;
                        }
                        $product[$i]->variants[$k]->images_sm = $variant_other_images_sm;

                        $counter = 0;
                        foreach ($variant_other_images as $row) {
                            $variant_other_images[$counter] = getMediaImageUrl($variant_other_images[$counter]);
                            $counter++;
                        }
                        $product[$i]->variants[$k]->images = isset($variant_other_images) && !empty($variant_other_images) ? $variant_other_images : "";
                    } else {
                        $product[$i]->variants[$k]->images = array();
                        $product[$i]->variants[$k]->images_md = array();
                        $product[$i]->variants[$k]->images_sm = array();
                        $product[$i]->variants[$k]->variant_relative_path = array();
                    }
                    $product[$i]->variants[$k]->product_image = isset($product[$i]->variants[$k]->product_image) && !empty($product[$i]->variants[$k]->product_image) ? getMediaImageUrl($product[$i]->variants[$k]->product_image) : '';
                    $product[$i]->variants[$k]->swatche_type = (!empty($product[$i]->variants[$k]->swatche_type)) ? $product[$i]->variants[$k]->swatche_type : "0";
                    if (isset($product[$i]->variants[$k]->swatche_type) && $product[$i]->variants[$k]->swatche_type == 2) {

                        $product[$i]->variants[$k]->swatche_value = (!empty($product[$i]->variants[$k]->swatche_value)) ? getImageUrl($product[$i]->variants[$k]->swatche_value) : "";
                    }
                    $product[$i]->variants[$k]->swatche_value = (!empty($product[$i]->variants[$k]->swatche_value)) ? $product[$i]->variants[$k]->swatche_value : "0";
                    if (($product[$i]->stock_type == 0 || $product[$i]->stock_type == null)) {
                        if ($product[$i]->availability != null || $product[$i]->availability != "") {
                            $product[$i]->variants[$k]->availability = intval($product[$i]->availability);
                        }
                    } else {
                        $product[$i]->variants[$k]->availability = $product[$i]->variants[$k]->availability;
                        array_push($count_stock, $product[$i]->variants[$k]->availability);
                    }

                    if (($product[$i]->stock_type == 0)) {
                        $product[$i]->variants[$k]->stock = isset($product[$i]->variants[$k]->stock) && !empty($product[$i]->variants[$k]->stock) ? (string) getStock($product[$i]->id, 'product') : '';
                    } else {
                        $product[$i]->variants[$k]->stock = isset($product[$i]->variants[$k]->stock) && !empty($product[$i]->variants[$k]->stock) ? (string) getStock($product[$i]->variants[$k]->id, 'variant') : '';
                    }
                    $percentage = (isset($product[$i]->tax_percentage) && intval($product[$i]->tax_percentage) > 0 && $product[$i]->tax_percentage != null) ? $product[$i]->tax_percentage : '';
                    if ((isset($product[$i]->is_prices_inclusive_tax) && $product[$i]->is_prices_inclusive_tax == 0)) {

                        if (isset($from_seller) && $from_seller == 1) {
                            //in seller get_products return orignal price without tax
                            $product[$i]->variants[$k]->price = strval($product[$i]->variants[$k]->price);
                            $product[$i]->variants[$k]->price_with_tax = strval(calculatePriceWithTax($percentage, $product[$i]->variants[$k]->price));

                            $product[$i]->variants[$k]->special_price = strval($product[$i]->variants[$k]->special_price);
                            $product[$i]->variants[$k]->special_price_with_tax = strval(calculatePriceWithTax($percentage, $product[$i]->variants[$k]->special_price));

                            //convert price in multi currency
                            $product[$i]->variants[$k]->currency_price_data = getPriceCurrency($product[$i]->variants[$k]->price);
                            $product[$i]->variants[$k]->currency_special_price_data = getPriceCurrency($product[$i]->variants[$k]->special_price);
                        } else {

                            $product[$i]->variants[$k]->price = strval(calculatePriceWithTax($percentage, $product[$i]->variants[$k]->price));
                            $product[$i]->variants[$k]->special_price = strval(calculatePriceWithTax($percentage, $product[$i]->variants[$k]->special_price));

                            //convert price in multi currency
                            $product[$i]->variants[$k]->currency_price_data = getPriceCurrency($product[$i]->variants[$k]->price);
                            $product[$i]->variants[$k]->currency_special_price_data = getPriceCurrency($product[$i]->variants[$k]->special_price);
                        }
                    } else {
                        if (isset($from_seller) && $from_seller == 1) {
                            $product[$i]->variants[$k]->price = strval($product[$i]->variants[$k]->price);
                            $product[$i]->variants[$k]->price_with_tax = strval($product[$i]->variants[$k]->price);

                            $product[$i]->variants[$k]->special_price = strval($product[$i]->variants[$k]->special_price);
                            $product[$i]->variants[$k]->special_price_with_tax = strval($product[$i]->variants[$k]->special_price);

                            //convert price in multi currency
                            $product[$i]->variants[$k]->currency_price_data = getPriceCurrency($product[$i]->variants[$k]->price);
                            $product[$i]->variants[$k]->currency_special_price_data = getPriceCurrency($product[$i]->variants[$k]->special_price);
                        } else {
                            $product[$i]->variants[$k]->price = strval($product[$i]->variants[$k]->price);

                            $product[$i]->variants[$k]->special_price = strval($product[$i]->variants[$k]->special_price);

                            //convert price in multi currency
                            $product[$i]->variants[$k]->currency_price_data = getPriceCurrency($product[$i]->variants[$k]->price);
                            $product[$i]->variants[$k]->currency_special_price_data = getPriceCurrency($product[$i]->variants[$k]->special_price);
                        }
                    }
                    if (isset($user_id) && $user_id != NULL && $is_detailed_data !== '' && $is_detailed_data == 1) {
                        $userCartData = Cart::where([
                            'product_variant_id' => $product[$i]->variants[$k]->id,
                            'user_id' => $user_id,
                            'is_saved_for_later' => 0
                        ])->select('qty as cart_count')->get()->toArray();

                        if (!empty($userCartData)) {
                            $product[$i]->variants[$k]->cart_count = $userCartData[0]['cart_count'];
                        } else {
                            $product[$i]->variants[$k]->cart_count = "0";
                        }
                        $is_purchased = OrderItems::where([
                            'product_variant_id' => $product[$i]->variants[$k]->id,
                            'user_id' => $user_id
                        ])->orderBy('id', 'desc')->limit(1)->get()->toArray();

                        if (!empty($is_purchased) && strtolower($is_purchased[0]['active_status']) == 'delivered') {
                            array_push($is_purchased_count, 1);
                            $product[$i]->variants[$k]->is_purchased = 1;
                        } else {
                            array_push($is_purchased_count, 0);
                            $product[$i]->variants[$k]->is_purchased = 0;
                        }

                        $is_purchased_count = array_count_values($is_purchased_count);
                        $is_purchased_count = array_keys($is_purchased_count);
                        $product[$i]->is_purchased = (isset($is_purchased) && array_sum($is_purchased_count) == 1) ? true : false;

                        $userRating = ProductRating::select('rating', 'comment')
                            ->where('user_id', $user_id)
                            ->where('product_id', $product[$i]->id)
                            ->get()
                            ->toArray();

                        if (!empty($userRating)) {
                            $product[$i]->user = (isset($userRating) && (!empty($userRating))) ? $userRating[0] : [];
                            $product[$i]->user['comment'] = $product[$i]->user['comment'] ?? "";
                        }
                    } else {
                        $product[$i]->variants[$k]->cart_count = "0";
                    }
                }
            }
            if (isset($product[$i]->stock_type) && !empty($product[$i]->stock_type)) {
                //Case 2 & 3: Product level (variable product) || Variant level (variable product)
                if ($product[$i]->stock_type == 1 || $product[$i]->stock_type == 2) {
                    // Ensure $count_stock is an array and not null
                    if (isset($count_stock) && is_array($count_stock)) {
                        // Filter out non-integer and non-string values from $count_stock array
                        $count_stock_filtered = array_filter($count_stock, function ($value) {
                            return is_int($value) || is_string($value);
                        });

                        // Count occurrences of each value
                        $counts = array_count_values($count_stock_filtered);

                        // Sum the counts
                    }
                }
            }

            if (isset($user_id) && $user_id != null) {
                $fav = Favorite::where(['product_id' => $product[$i]->id, 'user_id' => $user_id, 'product_type' => 'regular'])->count();

                $product[$i]->is_favorite = $fav;
            } else {
                $product[$i]->is_favorite = 0;
            }
            // dd($product[$i]->image);
            $product[$i]->image_md = getImageUrl($product[$i]->image, 'thumb', 'md');
            $product[$i]->image_sm = getImageUrl($product[$i]->image, 'thumb', 'sm');
            $product[$i]->image = getMediaImageUrl(ltrim($product[$i]->image, '/'));
            $other_images = $other_images_sm = $other_images_md = json_decode($product[$i]->other_images, 1);
            if (!empty($other_images)) {

                $k = 0;
                foreach ($other_images_md as $row) {
                    $other_images_md[$k] = getImageUrl($row, 'thumb', 'md');
                    $k++;
                }
                $other_images_md = (array) $other_images_md;
                $other_images_md = array_values($other_images_md);
                $product[$i]->other_images_md = $other_images_md;

                $k = 0;
                foreach ($other_images_sm as $row) {
                    $other_images_sm[$k] = getImageUrl($row, 'thumb', 'sm');
                    $k++;
                }
                $other_images_sm = (array) $other_images_sm;
                $other_images_sm = array_values($other_images_sm);
                $product[$i]->other_images_sm = $other_images_sm;

                $k = 0;
                foreach ($other_images as $row) {
                    $other_images[$k] = getMediaImageUrl($row);
                    $k++;
                }
                $other_images = (array) $other_images;
                $other_images = array_values($other_images);
                $product[$i]->other_images = $other_images;
            } else {
                $product[$i]->other_images = array();
                $product[$i]->other_images_sm = array();
                $product[$i]->other_images_md = array();
            }

            $tags_to_strip = array("table", "<th>", "<td>");
            $replace_with = array("", "h3", "p");
            $n = 0;
            foreach ($tags_to_strip as $tag) {
                $product[$i]->description = !empty($product[$i]->description) ? outputEscaping(str_replace('\r\n', '&#13;&#10;', (string) $product[$i]->description)) : "";
                $product[$i]->extra_description = !empty($product[$i]->extra_description) && $product[$i]->extra_description != null ? outputEscaping(str_replace('\r\n', '&#13;&#10;', (string) $product[$i]->extra_description)) : "";
                $n++;
            }
            $variant_attributes = [];
            $attributes_array = explode(
                ',',
                isset($product[$i]->variants) && !empty($product[$i]->variants) && isset($product[$i]->variants[0]->attr_name)
                    ? $product[$i]->variants[0]->attr_name
                    : ""
            );


            foreach ($attributes_array as $attribute) {
                $attribute = trim($attribute);


                $key = array_search($attribute, array_column($product[$i]->attributes, 'name'), false);

                if (($key === 0 || !empty($key)) && isset($product[0]->attributes[$key])) {

                    $variant_attributes[$key]['ids'] = $product[0]->attributes[$key]['ids'];
                    $variant_attributes[$key]['value'] = $product[0]->attributes[$key]['value'];
                    $variant_attributes[$key]['swatche_type'] = isset($product[0]->attributes[$key]['swatche_type']) ? $product[0]->attributes[$key]['swatche'] : '';
                    $variant_attributes[$key]['swatche_value'] = isset($product[0]->attributes[$key]['swatche_value']) ? $product[0]->attributes[$key]['swatche_value'] : '';
                    $variant_attributes[$key]['attr_name'] = $attribute;
                }
            }

            array_push($attribute_values_ids, $product[$i]->attr_value_ids ?? '');
            $product[$i]->variant_attributes = $variant_attributes;
        }

        if (isset($total_data[0]->cal_discount_percentage)) {
            $dicounted_total = array_values(array_filter(explode(',', $total_data[0]->cal_discount_percentage)));
        } else {
            $dicounted_total = 0;
        }

        $response['total'] = (isset($filter) && !empty($filter['discount'])) ? count($dicounted_total) : $total;
        $attribute_values_ids = implode(",", $attribute_values_ids);
        $attr_value_ids = array_filter(array_unique(explode(',', $attribute_values_ids)));
    }

    $response['min_price'] = (isset($min_price)) ? $min_price : "0";
    $response['max_price'] = (isset($max_price)) ? $max_price : "0";
    $response['category_ids'] = (isset($category_ids)) ? $category_ids : "";
    $response['brand_ids'] = (isset($brand_ids)) ? $brand_ids : "";
    $response['product'] = $product;
    if (isset($filter) && $filter != null) {
        if (!empty($attr_value_ids)) {
            $response['filters'] = getAttributeValuesById($attr_value_ids);
        }
    } else {
        $response['filters'] = [];
    }

    return $response;
}

function fetchComboProduct($user_id = NULL, $filter = NULL, $id = NULL, $limit = NULL, $offset = NULL, $sort = 'p.id', $order = 'DESC', $return_count = NULL, $is_deliverable = NULL, $seller_id = NULL, $store_id = NULL, $category_id = '', $brand_id = '', $type = '', $from_seller = '', $language_code = '')
{

    $settings = getSettings('system_settings', true);
    $settings = json_decode($settings, true);
    $language_code = isset($language_code) && !empty($language_code) ? $language_code : get_language_code();
    $low_stock_limit = isset($settings['low_stock_limit']) ? $settings['low_stock_limit'] : 5;
    $total_query = DB::table('combo_products as p')
        ->select(
            'p.*',
            'sd.status as seller_status',
            'a.id as attribute_id',
            'tax.id as tax_id',
            'prod.id as product_id',
            'prod.name as product_name',
            'prod.category_id',
            'prod.brand',
            'pav.id as attribute_value_id',
            'pa.id as product_attribute_id',
            DB::raw('GROUP_CONCAT(DISTINCT pav.id) AS attr_value_ids'),
            DB::raw('
            CASE
                WHEN p.special_price > 0 THEN ((p.price - p.special_price) / p.price) * 100
                ELSE 0
            END AS cal_discount_percentage
        ')
        )
        ->leftJoin('seller_data as sd', 'p.seller_id', '=', 'sd.id')
        ->leftJoin('combo_product_attributes as a', 'a.id', '=', 'p.attribute_value_ids')
        ->leftJoin('taxes as tax', function ($join) {
            $join->on(DB::raw('FIND_IN_SET(tax.id, p.tax)'), '>', DB::raw('0'));
        })
        ->leftJoin('products as prod', function ($join) {
            $join->on(DB::raw('FIND_IN_SET(prod.id, p.product_ids)'), '>', DB::raw('0'));
        })
        ->leftJoin('attribute_values as pav', function ($join) {
            $join->on(DB::raw('FIND_IN_SET(pav.id, p.attribute_value_ids)'), '>', DB::raw('0'));
        })
        ->leftJoin('product_attributes as pa', 'pa.id', '=', 'pav.attribute_id')
        ->leftJoin('product_variants as pv', function ($join) {
            $join->on(DB::raw('FIND_IN_SET(pv.product_id, p.product_ids)'), '>', DB::raw('0'));
        });

    // Apply filter for attribute_value_ids
    if (isset($filter) && !empty($filter['attribute_value_ids'])) {
        $attribute_value_ids = $filter['attribute_value_ids'];

        $total_query->where(function ($query) use ($attribute_value_ids) {
            foreach ($attribute_value_ids as $attr_value_id) {
                $query->whereRaw("FIND_IN_SET(?, p.attribute_value_ids)", [$attr_value_id])
                    ->orWhereRaw("FIND_IN_SET(?, pav.id)", [$attr_value_id])
                    ->orWhereRaw("FIND_IN_SET(?, pv.attribute_value_ids)", [$attr_value_id]);
            }
        });
    }
    if (is_array($category_id)) {
        $total_query->whereIn('prod.category_id', $category_id);
    } else {
        if (isset($category_id) && !empty($category_id)) {
            $total_query->where('prod.category_id', $category_id);
        }
    }
    if (isset($brand_id) && !empty($brand_id)) {
        $total_query->where('prod.brand', $brand_id);
    };

    if (isset($filter['show_only_stock_product']) && $filter['show_only_stock_product'] == 1) {
        $total_query->where(function ($query) {
            $query->whereNotNull('p.stock');
        });
    }

    if ($sort == 'p.price' && !empty($sort) && $sort != null) {
        $expression = "IF(p.special_price > 0,
            IF(p.is_prices_inclusive_tax = 1,
            p.special_price,
            p.special_price + ((p.special_price * p.tax) / 100)
            ),
            IF(p.is_prices_inclusive_tax = 1,
                p.price,
                p.price + ((p.price * p.tax) / 100)
            )
        ) " . $order;
        $total_query->orderByRaw($expression);
    }
    if (isset($filter['show_only_active_products']) && $filter['show_only_active_products'] == 0) {
        $where = [];
    } else {
        $where = ['p.status' => '1', 'sd.status' => '1'];
    }

    if (isset($type) && $type == 'physical_product') {
        $total_query->where('p.product_type', 'physical_product');
    }
    if (isset($type) && $type == 'digital_product') {
        $total_query->where('p.product_type', 'digital_product');
    }
    if (isset($filter['show_only_stock_product']) && $filter['show_only_stock_product'] == 1) {
        $total_query->where(function ($query) {
            $query->whereNotNull('p.stock');
        });
    }
    if (isset($filter) && !empty($filter['product_type']) && strtolower($filter['product_type']) == 'most_selling_products') {
        $sort = 'total_sale';
        $order = 'desc';
    }

    if (isset($filter) && !empty($filter['search'])) {
        $tags = explode(" ", $filter['search']);
        $total_query->where(function ($total_query) use ($tags, $filter) {
            foreach ($tags as $i => $tag) {
                if ($i == 0) {
                    $total_query->where('p.tags', 'like', '%' . trim($tag) . '%');
                } else {
                    $total_query->orWhere('p.tags', 'like', '%' . trim($tag) . '%');
                }
            }
            $total_query->orWhere('p.title', 'like', '%' . trim($filter['search']) . '%');
        });
    }

    if (isset($filter) && !empty($filter['flag']) && $filter['flag'] != "null" && $filter['flag'] != "") {
        $flag = $filter['flag'];
        if ($flag == 'low') {
            $total_query->where(function ($total_query) use ($low_stock_limit) {
                $total_query->where(function ($total_query) {
                    $low_stock_limit = isset($settings['low_stock_limit']) ? $settings['low_stock_limit'] : 5;
                    $total_query->whereNotNull('p.stock_type')
                        ->where('p.stock', '<=', $low_stock_limit)
                        ->where('p.availability', '=', '1');
                })->orWhere(function ($total_query) use ($low_stock_limit) {
                    $total_query->where('p.stock', '<=', $low_stock_limit)
                        ->where('p.availability', '=', '1');
                });
            });
        } else {
            $total_query->where(function ($total_query) {
                $total_query->orWhere('p.availability', '=', '0')
                    ->orWhere('p.availability', '=', '0')
                    ->where('p.stock', '=', '0')
                    ->orWhere('p.stock', '=', '0');
            });
        }
    }

    if (isset($filter['max_price']) && $filter['max_price'] > 0 && isset($filter['min_price']) && $filter['min_price'] > 0) {
        $max_price = $filter['max_price'];
        $min_price = $filter['min_price'];
        $total_query->where(function ($total_query) use ($max_price, $min_price) {
            $total_query->where(function ($total_query) use ($max_price, $min_price) {
                $total_query->whereRaw("(
                    CASE
                        WHEN p.special_price > 0 THEN
                            p.special_price * (1 + (IFNULL((
                                SELECT MAX(tax.percentage)
                                FROM taxes as tax
                                WHERE FIND_IN_SET(tax.id, p.tax)
                            ), 0) / 100))
                        ELSE
                            p.price * (1 + (IFNULL((
                                SELECT MAX(tax.percentage)
                                FROM taxes as tax
                                WHERE FIND_IN_SET(tax.id, p.tax)
                            ), 0) / 100))
                    END
                ) BETWEEN ? AND ?", [$min_price, $max_price]);
            });
        });
    }

    if (isset($filter) && !empty($filter['tags'])) {
        $tags = explode(",", $filter['tags']);
        $total_query->where(function ($total_query) use ($tags) {
            foreach ($tags as $i => $tag) {
                if ($i == 0) {
                    $total_query->where('p.tags', 'like', '%' . trim($tag) . '%');
                } else {
                    $total_query->orWhere('p.tags', 'like', '%' . trim($tag) . '%');
                }
            }
        });
    }


    if (isset($filter) && !empty($filter['slug'])) {
        $total_query->where('p.slug', $filter['slug']);
    }

    if (isset($seller_id) && !empty($seller_id) && $seller_id != "") {
        $total_query->where('p.seller_id', $seller_id);
    }

    if (isset($store_id) && !empty($store_id)) {

        $where['p.store_id'] = $store_id;
    }

    $total_query->where($where);
    if (isset($filter) && !empty($filter['product_type']) && strtolower($filter['product_type']) == 'products_on_sale') {
        $total_query->where('p.special_price', '>', '0');
    }
    if ((isset($filter['minimum_price']) && $filter['minimum_price'] !== '') || (isset($filter['maximum_price']) && $filter['maximum_price'] !== '')) {
        $minPrice = $filter['minimum_price'];
        $maxPrice = $filter['maximum_price'];



        $total_query->where(function ($query) use ($minPrice, $maxPrice) {
            $query->where(function ($query) use ($minPrice, $maxPrice) {
                $query->where('p.special_price', '>', 0)
                    ->whereBetween('p.special_price', [$minPrice, $maxPrice]);
            })
                ->orWhere(function ($query) use ($minPrice, $maxPrice) {
                    $query->where('p.special_price', '=', 0)
                        ->whereBetween('p.price', [$minPrice, $maxPrice]);
                });
        });
    }
    if (isset($filter) && !empty($filter['product_type']) && strtolower($filter['product_type']) == 'top_rated_products') {
        $sort = null;
        $order = null;
        $total_query->orderBy('p.rating', 'desc')
            ->orderBy('p.no_of_ratings', 'desc');
        $where['p.no_of_ratings > '] = 0;
    }

    if (isset($filter) && !empty($filter['product_type']) && strtolower($filter['product_type']) == 'top_rated_product_including_all_products') {
        $sort = 'p.rating';
        $order = 'desc';
        $total_query->orderBy('p.rating', 'desc')
            ->orderBy('p.no_of_ratings', 'desc');
    }

    if (isset($filter) && !empty($filter['product_type']) && $filter['product_type'] == 'new_added_products') {
        $sort = 'p.id';
        $order = 'desc';
    }



    if (isset($id) && !empty($id) && $id != null) {

        if (is_array($id) && !empty($id)) {
            $total_query->whereIn('p.id', $id);
        } else {
            $total_query->where('p.id', $id);
        }
    }

    if (isset($filter['discount']) && !empty($filter['discount']) && $filter['discount'] != "") {
        $discount_pr = $filter['discount'];
        $total_query->groupBy('p.id')
            ->havingRaw("cal_discount_percentage <= $discount_pr")
            ->havingRaw("cal_discount_percentage > 0");
    }
    // else {
    //     $total_query->groupBy('p.id');
    // }


    if ($sort !== null || $order !== null && $sort !== 'p.price') {
    } else {
        $total_query->orderBy('p.id', 'DESC');
    }
    if ($sort !== null && $sort == 'discount') {
        $total_query->orderByRaw('p.special_price > 0 DESC');
    }
    if (isset($filter['discount']) && !empty($filter['discount']) && $filter['discount'] != "") {
        $total_query->orderBy('cal_discount_percentage', 'desc');
    } else {
        if ($sort !== null || $order !== null && $sort !== 'p.price') {
        }
        $total_query->orderBy('p.id', 'DESC');
    }
    $total_query->groupBy('p.id');
    // dd($total_query->tosql(),$total_query->getbindings());
    $total_data = $total_query->get();

    $total = $total_data != '' ? count($total_data) : '';
    $category_ids = collect($total_data)->pluck('category_id')->unique()->values()->all();
    $brand_ids = collect($total_data)->pluck('brand')->unique()->values()->all();
    $product_query = DB::table('combo_products as p')
        ->select(
            'p.*',
            'ss.rating as seller_rating',
            'ss.slug as seller_slug',
            'ss.no_of_ratings as seller_no_of_ratings',
            'ss.logo as seller_profile',
            'ss.store_name as store_name',
            'ss.store_description',
            'u.username as seller_name',
            DB::raw('(SELECT GROUP_CONCAT(tax.percentage) FROM taxes as tax WHERE FIND_IN_SET(tax.id, p.tax)) as tax_percentage'),
            DB::raw('GROUP_CONCAT(DISTINCT tax_id.id) as tax_id'),
            DB::raw(
                '
            CASE
                WHEN p.special_price > 0 THEN ((p.price - p.special_price) / p.price) * 100
                ELSE 0
            END AS cal_discount_percentage'
            ),
            'pv.attribute_value_ids AS variant_attribute_value_ids',
            'prod.id AS product_id',
            'prod.name AS product_name'
        )
        ->leftJoin('seller_data as sd', 'p.seller_id', '=', 'sd.id')
        // ->leftJoin('seller_store as ss', 'p.seller_id', '=', 'ss.seller_id')
        ->leftJoin('seller_store as ss', function ($join) use ($store_id) {
            $join->on('p.seller_id', '=', 'ss.seller_id')
                ->where('ss.store_id', '=', $store_id);
        })
        ->leftJoin('users as u', 'u.id', '=', 'sd.user_id')
        ->leftJoin('taxes as tax_id', function ($join) {
            $join->on(DB::raw('FIND_IN_SET(tax_id.id, p.tax)'), '>', DB::raw('0'));
        })
        ->leftJoin('combo_product_attribute_values as av', function ($join) {
            $join->on(DB::raw('FIND_IN_SET(av.id, p.attribute_value_ids)'), '>', DB::raw('0'));
        }, null, null, 'inner')
        ->leftJoin('combo_product_attributes as a', 'a.id', '=', 'av.combo_product_attribute_id')
        ->leftJoin('taxes as tax', function ($join) {
            $join->on(DB::raw('FIND_IN_SET(tax.id, p.tax)'), '>', DB::raw('0'));
        })
        ->leftJoin('attribute_values as pav', function ($join) {
            $join->on(DB::raw('FIND_IN_SET(pav.id, p.attribute_value_ids)'), '>', DB::raw('0'));
        })
        ->leftJoin('product_attributes as pa', 'pa.id', '=', 'pav.attribute_id')
        ->leftJoin('products as prod', function ($join) {
            $join->on(DB::raw('FIND_IN_SET(prod.id, p.product_ids)'), '>', DB::raw('0'));
        })
        ->leftJoin('product_variants as pv', function ($join) {
            $join->on(DB::raw('FIND_IN_SET(pv.product_id, p.product_ids)'), '>', DB::raw('0'));
        });

    // Check if filter is provided
    if (isset($filter) && !empty($filter['attribute_value_ids'])) {
        $attribute_value_ids = $filter['attribute_value_ids'];

        // Applying the filter for attribute_value_ids
        $product_query->where(function ($query) use ($attribute_value_ids) {
            foreach ($attribute_value_ids as $attr_value_id) {
                $query->whereRaw("FIND_IN_SET(?, p.attribute_value_ids)", [$attr_value_id])
                    ->orWhereRaw("FIND_IN_SET(?, pv.attribute_value_ids)", [$attr_value_id]);
            }
        });
    }

    if (is_array($category_id)) {
        $product_query->whereIn('prod.category_id', $category_id);
    } else {
        if (isset($category_id) && !empty($category_id)) {
            $product_query->where('prod.category_id', $category_id);
        }
    }
    if (isset($brand_id) && !empty($brand_id)) {
        $product_query->where('prod.brand', $brand_id);
    }
    if (isset($type) && $type == 'physical_product') {
        $product_query->where('p.product_type', 'physical_product');
    }
    if (isset($type) && $type == 'digital_product') {
        $product_query->where('p.product_type', 'digital_product');
    }
    if ((isset($filter['minimum_price']) && $filter['minimum_price'] !== '') || (isset($filter['maximum_price']) && $filter['maximum_price'] !== '')) {
        $minPrice = $filter['minimum_price'];
        $maxPrice = $filter['maximum_price'];
        $product_query->where(function ($query) use ($minPrice, $maxPrice) {
            $query->where(function ($query) use ($minPrice, $maxPrice) {
                $query->where('p.special_price', '>', 0)
                    ->whereBetween('p.special_price', [$minPrice, $maxPrice]);
            })
                ->orWhere(function ($query) use ($minPrice, $maxPrice) {
                    $query->where('p.special_price', '=', 0)
                        ->whereBetween('p.price', [$minPrice, $maxPrice]);
                });
        });
    }

    if (isset($filter) && !empty($filter['search'])) {
        $tags = explode(" ", $filter['search']);
        $product_query->where(function ($product_query) use ($tags, $filter) {
            foreach ($tags as $i => $tag) {
                if ($i == 0) {
                    $product_query->where('p.tags', 'like', '%' . trim($tag) . '%');
                } else {
                    $product_query->orWhere('p.tags', 'like', '%' . trim($tag) . '%');
                }
            }
            $product_query->orWhere('p.title', 'like', '%' . trim($filter['search']) . '%');
        });
    }

    if (isset($filter) && !empty($filter['flag']) && $filter['flag'] != "null" && $filter['flag'] != "") {
        $flag = $filter['flag'];
        if ($flag == 'low') {
            $product_query->where(function ($product_query) use ($low_stock_limit) {
                $product_query->where(function ($product_query) {
                    $low_stock_limit = isset($settings['low_stock_limit']) ? $settings['low_stock_limit'] : 5;
                    $product_query->whereNotNull('p.stock_type')
                        ->where('p.stock', '<=', $low_stock_limit)
                        ->where('p.availability', '=', '1');
                })->orWhere(function ($product_query) use ($low_stock_limit) {
                    $product_query->where('p.stock', '<=', $low_stock_limit)
                        ->where('p.availability', '=', '1');
                });
            });
        } else {
            $product_query->where(function ($product_query) {
                $product_query->orWhere('p.availability', '=', '0')
                    ->orWhere('p.availability', '=', '0')
                    ->where('p.stock', '=', '0')
                    ->orWhere('p.stock', '=', '0');
            });
        }
    }

    if (isset($filter) && !empty($filter['tags'])) {
        $tags = explode(",", $filter['tags']);
        $product_query->where(function ($product_query) use ($tags) {
            foreach ($tags as $i => $tag) {
                if ($i == 0) {
                    $product_query->where('p.tags', 'like', '%' . trim($tag) . '%');
                } else {
                    $product_query->orWhere('p.tags', 'like', '%' . trim($tag) . '%');
                }
            }
        });
    }
    if (isset($filter) && !empty($filter['product_variant_ids'])) {
        if (is_array($filter['product_variant_ids'])) {
            $product_query->whereIn('p.id', $filter['product_variant_ids']);
        }
    }


    if ($sort == 'p.price' && !empty($sort) && $sort != null) {
        $expression = "IF(p.special_price > 0,
            IF(p.is_prices_inclusive_tax = 1,
                p.special_price,
                p.special_price + ((p.special_price * p.tax) / 100)
            ),
            IF(p.is_prices_inclusive_tax = 1,
                p.price,
                p.price + ((p.price * p.tax) / 100)
            )
        ) " . $order;
        $product_query->orderByRaw($expression);
    }
    if (isset($filter) && !empty($filter['product_type']) && strtolower($filter['product_type']) == 'top_rated_product_including_all_products') {
        $sort = 'p.rating';
        $order = 'desc';
        $product_query->orderBy('p.rating', 'desc')
            ->orderBy('p.no_of_ratings', 'desc');
    }
    if (isset($filter) && !empty($filter['product_type']) && strtolower($filter['product_type']) == 'top_rated_products') {
        $sort = 'p.rating';
        $order = 'desc';
        $product_query->orderBy('p.rating', 'desc')
            ->orderBy('p.no_of_ratings', 'desc');
        $where['p.no_of_ratings > '] = 0;
    }
    if (isset($filter) && !empty($filter['slug'])) {
        $product_query->where('p.slug', $filter['slug']);
    }


    if ((isset($filter['minimum_price']) && $filter['minimum_price'] !== '') || (isset($filter['maximum_price']) && $filter['maximum_price'] !== '')) {
        $minPrice = $filter['minimum_price'];
        $maxPrice = $filter['maximum_price'];
        $product_query->where(function ($query) use ($minPrice, $maxPrice) {
            $query->where(function ($query) use ($minPrice, $maxPrice) {
                $query->where('p.special_price', '>', 0)
                    ->whereBetween('p.special_price', [$minPrice, $maxPrice]);
            })
                ->orWhere(function ($query) use ($minPrice, $maxPrice) {
                    $query->where('p.special_price', '=', 0)
                        ->whereBetween('p.price', [$minPrice, $maxPrice]);
                });
        });
    }

    if (isset($filter) && !empty($filter['product_type']) && strtolower($filter['product_type']) == 'most_selling_products') {
        $sort = 'total_sale';
        $order = 'desc';
    }


    if (isset($store_id) && !empty($store_id)) {
        $product_query->where('p.store_id', $store_id);
    }
    if (isset($filter) && !empty($filter['product_type']) && strtolower($filter['product_type']) == 'products_on_sale') {
        $product_query->where('p.special_price', '>', '0');
    }

    if (isset($filter) && !empty($filter['product_type']) && $filter['product_type'] == 'new_added_products') {
        $product_query->orderBy('p.id', 'desc');
    }

    if (isset($filter) && !empty($filter['product_type']) && $filter['product_type'] == 'old_products_first') {
        $product_query->orderBy('p.id', 'asc');
    }
    if (isset($id) && !empty($id) && $id !== null) {
        if (is_array($id) && !empty($id)) {

            $product_query = $product_query->whereIn('p.id', $id);
        } else {
            if (isset($filter) && !empty($filter['is_similar_products']) && $filter['is_similar_products'] == '1') {
                $product_query->where('p.id', '!=', $id);
            } else {

                $product_query->where('p.id', $id);
            }
        }
    }

    if (isset($seller_id) && !empty($seller_id) && $seller_id != "") {
        $product_query->where('p.seller_id', $seller_id);
    }

    if (isset($filter['show_only_stock_product']) && $filter['show_only_stock_product'] == 1) {
        $product_query->where(function ($query) {
            $query->whereNotNull('p.stock');
        });
    }

    if (isset($filter['show_only_active_products']) && $filter['show_only_active_products'] == 0) {
        $where = [];
    } else {
        $product_query->where('p.status', '1');
        $product_query->where('sd.status', '1');
    }
    if ($sort !== null && $sort == 'discount') {
        $product_query->orderByRaw('p.special_price > 0 DESC');
    }
    if (isset($filter['discount']) && !empty($filter['discount']) && $filter['discount'] != "") {
        $discount_pr = $filter['discount'];
        $product_query->groupBy('p.id')
            ->havingRaw("cal_discount_percentage <= $discount_pr")
            ->havingRaw("cal_discount_percentage > 0");
    }
    // else {
    //     $product_query->groupBy('p.id');
    // }

    if (isset($filter['discount']) && !empty($filter['discount']) && $filter['discount'] != "") {
        $product_query->orderBy('cal_discount_percentage', 'desc');
    } else {
        if ($sort !== null || $order !== null && $sort !== 'p.price') {
            $product_query->orderBy('p.id', 'desc');
        }
    }

    if ($limit !== null || $offset !== null) {

        $product_query->skip($offset)->take($limit);
    }
    $product_query->groupBy('p.id');
    // dd($product_query->tosql(),$product_query->getbindings());
    $product = $product_query->get()->toArray();

    $min_price = getComboPrice('min', $store_id);
    $max_price = getComboPrice('max', $store_id);

    $weekly_sales = DB::table('order_items as oi')
        ->join('combo_products as cp', 'cp.id', '=', 'oi.product_variant_id')
        ->select('cp.id', DB::raw('SUM(oi.quantity) as weekly_sale'))
        ->where('oi.created_at', '>=', now()->subDays(7))
        ->where('oi.order_type', '=', 'combo_order')
        ->groupBy('cp.id')
        ->pluck('weekly_sale', 'id')
        ->toArray();
    // dd($weekly_sales);
    $max_weekly_sale = !empty($weekly_sales) ? max($weekly_sales) : 0;

    if (!empty($product)) {

        for ($i = 0; $i < count($product); $i++) {

            $rating = fetchComboRating($product[$i]->id, '', 8, 0, '', 'desc', '', 1);
            $product[$i]->translated_name = json_decode($product[$i]->title);
            $product[$i]->translated_short_description = json_decode($product[$i]->short_description);
            $product[$i]->product_name = getDynamicTranslation('products', 'name', $product[$i]->product_id, $language_code);
            $product[$i]->title = getDynamicTranslation('combo_products', 'title', $product[$i]->id, $language_code);
            $product[$i]->short_description = getDynamicTranslation('combo_products', 'short_description', $product[$i]->id, $language_code);
            if ((isset($product[$i]->is_prices_inclusive_tax) && $product[$i]->is_prices_inclusive_tax == 0)) {

                if (isset($from_seller) && $from_seller == 1) {
                    //in seller get_products return orignal price without tax
                    $percentage = (isset($product[$i]->tax_percentage) && intval($product[$i]->tax_percentage) > 0 && $product[$i]->tax_percentage != null) ? $product[$i]->tax_percentage : '0';
                    $product[$i]->price = strval($product[$i]->price);
                    $product[$i]->price_with_tax = strval(calculatePriceWithTax($percentage, $product[$i]->price));

                    $product[$i]->special_price = strval($product[$i]->special_price);
                    $product[$i]->special_price_with_tax = strval(calculatePriceWithTax($percentage, $product[$i]->special_price));

                    //convert price in multi currency
                    $product[$i]->currency_price_data = getPriceCurrency($product[$i]->price);
                    $product[$i]->currency_special_price_data = getPriceCurrency($product[$i]->special_price);
                } else {

                    $percentage = (isset($product[$i]->tax_percentage) && intval($product[$i]->tax_percentage) > 0 && $product[$i]->tax_percentage != null) ? $product[$i]->tax_percentage : '0';

                    $product[$i]->price = strval(calculatePriceWithTax($percentage, $product[$i]->price));

                    $product[$i]->special_price = strval(calculatePriceWithTax($percentage, $product[$i]->special_price));
                    //convert price in multi currency
                    $product[$i]->currency_price_data = getPriceCurrency($product[$i]->price);
                }
            } else {

                if (isset($from_seller) && $from_seller == 1) {
                    //in seller get_products return orignal price without tax
                    $percentage = (isset($product[$i]->tax_percentage) && intval($product[$i]->tax_percentage) > 0 && $product[$i]->tax_percentage != null) ? $product[$i]->tax_percentage : '0';
                    $product[$i]->price = strval($product[$i]->price);
                    $product[$i]->price_with_tax = $product[$i]->price;

                    $product[$i]->special_price = strval($product[$i]->special_price);
                    $product[$i]->special_price_with_tax = $product[$i]->special_price;

                    //convert price in multi currency
                    $product[$i]->currency_price_data = getPriceCurrency($product[$i]->price);
                    $product[$i]->currency_special_price_data = getPriceCurrency($product[$i]->special_price);
                } else {
                    $product[$i]->price = strval($product[$i]->price);
                    $product[$i]->special_price = strval($product[$i]->special_price);

                    //convert price in multi currency
                    $product[$i]->currency_price_data = getPriceCurrency($product[$i]->price);
                    // $product[$i]->currency_special_price_data = getPriceCurrency($product[$i]->special_price);
                }
            }
            $product[$i]->product_rating_data = isset($rating) ? $rating : [];

            $product[$i]->tax_id = ((isset($product[$i]->tax_id) && intval($product[$i]->tax_id) > 0) && $product[$i]->tax_id != "") ? $product[$i]->tax_id : '0';
            $taxes = [];
            $tax_ids = explode(",", $product[$i]->tax_id);
            $taxes = Tax::whereIn('id', $tax_ids)->get()->toArray();
            $taxes = array_column($taxes, 'title');
            // $product[$i]->tax_names = implode(",", $taxes);
            $translatedTaxes = [];

            foreach ($taxes as $tax) {
                $translatedTaxes[] = getDynamicTranslation('taxes', 'title', $product[$i]->tax_id, $language_code);
            }

            $product[$i]->tax_names = implode(",", $translatedTaxes);
            $tax_percentages = [];
            $tax_ids = explode(",", $product[$i]->tax_id);
            $tax_percentages = Tax::whereIn('id', $tax_ids)->get()->toArray();
            $tax_percentages = array_column($tax_percentages, 'percentage');
            $product[$i]->tax_percentage = implode(",", $tax_percentages);

            $product[$i]->attributes = getComboAttributeValuesByPid($product[$i]->id);
            if ($product[$i]->other_images === null || $product[$i]->other_images === "null") {
                $product[$i]->other_images_relative_path = [];
            } else {

                $product[$i]->other_images_relative_path = !empty($product[$i]->other_images) ? json_decode($product[$i]->other_images) : [];
            }

            $product[$i]->min_max_price = getMinMaxPriceOfComboProduct($product[$i]->id);
            $product[$i]->min_max_price['discount_in_percentage'] = isset($product[$i]->min_max_price['discount_in_percentage']) && $product[$i]->min_max_price['discount_in_percentage'] !== null ? $product[$i]->min_max_price['discount_in_percentage'] : '';
            $product[$i]->type = "combo-product";
            $product[$i]->stock_type = isset($product[$i]->stock_type) && ($product[$i]->stock_type != '') ? $product[$i]->stock_type : '';
            $product[$i]->product_variant_ids = isset($product[$i]->product_variant_ids) && ($product[$i]->product_variant_ids != null) ? $product[$i]->product_variant_ids : '';
            $product[$i]->stock = isset($product[$i]->stock) && ($product[$i]->stock != '') ? (string) $product[$i]->stock : '';
            $other_images = $other_images_sm = $other_images_md = json_decode($product[$i]->other_images, 1);

            if (!empty($other_images)) {

                $k = 0;
                foreach ($other_images_md as $row) {
                    $other_images_md[$k] = getImageUrl($row, 'thumb', 'md');
                    $k++;
                }
                $other_images_md = (array) $other_images_md;
                $other_images_md = array_values($other_images_md);
                $product[$i]->other_images_md = $other_images_md;

                $k = 0;
                foreach ($other_images_sm as $row) {
                    $other_images_sm[$k] = getImageUrl($row, 'thumb', 'sm');
                    $k++;
                }
                $other_images_sm = (array) $other_images_sm;
                $other_images_sm = array_values($other_images_sm);
                $product[$i]->other_images_sm = $other_images_sm;

                $k = 0;
                foreach ($other_images as $row) {
                    $other_images[$k] = getMediaImageUrl($row);
                    $k++;
                }
                $other_images = (array) $other_images;
                $other_images = array_values($other_images);
                $product[$i]->other_images = $other_images;
            } else {
                $product[$i]->other_images = array();
                $product[$i]->other_images_sm = array();
                $product[$i]->other_images_md = array();
            }

            $product[$i]->delivery_charges = isset($product[$i]->delivery_charges) && ($product[$i]->delivery_charges != '') ? $product[$i]->delivery_charges : '';
            $product[$i]->download_type = isset($product[$i]->download_type) && ($product[$i]->download_type != '') ? $product[$i]->download_type : '';
            $product[$i]->download_link = isset($product[$i]->download_link) && ($product[$i]->download_link != '') ? getMediaImageUrl($product[$i]->download_link) : '';
            $product[$i]->relative_path = isset($product[$i]->image) && !empty($product[$i]->image) ? $product[$i]->image : '';

            $product[$i]->attr_value_ids = isset($product[$i]->attr_value_ids) && !empty($product[$i]->attr_value_ids) ? $product[$i]->attr_value_ids : '';
            if (isset($user_id) && $user_id != null) {
                $fav = Favorite::where(['product_id' => $product[$i]->id, 'user_id' => $user_id, 'product_type' => 'combo'])->count();

                $product[$i]->is_favorite = $fav;
            } else {
                $product[$i]->is_favorite = 0;
            }

            $product[$i]->name = outputEscaping($product[$i]->title);
            $image = getMediaImageUrl($product[$i]->image);

            $product[$i]->image = $image;
            $product[$i]->store_name = outputEscaping($product[$i]->store_name);
            $product[$i]->seller_rating = (isset($product[$i]->seller_rating) && !empty($product[$i]->seller_rating)) ? outputEscaping(number_format($product[$i]->seller_rating, 1)) : "0";
            $product[$i]->store_description = (isset($product[$i]->store_description) && !empty($product[$i]->store_description)) ? outputEscaping($product[$i]->store_description) : "";
            $product[$i]->has_similar_product = (isset($product[$i]->has_similar_product) && !empty($product[$i]->has_similar_product)) ? outputEscaping($product[$i]->has_similar_product) : "";
            $product[$i]->similar_product_ids = (isset($product[$i]->similar_product_ids) && !empty($product[$i]->similar_product_ids)) ? outputEscaping($product[$i]->similar_product_ids) : "";
            $product[$i]->seller_profile = outputEscaping(asset($product[$i]->seller_profile));
            $product[$i]->seller_name = outputEscaping($product[$i]->seller_name);
            // $product[$i]->short_description = outputEscaping($product[$i]->short_description);
            $product[$i]->description = (isset($product[$i]->description) && !empty($product[$i]->description)) ? outputEscaping($product[$i]->description) : "";
            $product[$i]->pickup_location = isset($product[$i]->pickup_location) && !empty($product[$i]->pickup_location) ? $product[$i]->pickup_location : '';

            $product[$i]->seller_slug = isset($product[$i]->seller_slug) && !empty($product[$i]->seller_slug) ? outputEscaping($product[$i]->seller_slug) : "";
            $product[$i]->deliverable_type = $product[$i]->deliverable_type;

            // new arrival tags based on newly added product(weekly)

            if (isset($product[$i]->created_at) && strtotime($product[$i]->created_at) >= strtotime('-7 days')) {
                $product[$i]->new_arrival = true;
            } else {
                $product[$i]->new_arrival = false;
            }

            // end new arrival tags based on newly added product(weekly)


            // best seller tag based on most selling product (weekly)

            $weeklySale = $weekly_sales[$product[$i]->id] ?? 0;
            $product[$i]->best_seller = ($max_weekly_sale > 0 && $weeklySale >= ($max_weekly_sale * 0.8));

            // end best seller tag based on most selling product (weekly)

            if (isset($filter['discount']) && !empty($filter['discount']) && $filter['discount'] != "") {
                $product[$i]->cal_discount_percentage = outputEscaping(number_format($product[$i]->cal_discount_percentage, 2));
            }
            $product[$i]->cancelable_till = isset($product[$i]->cancelable_till) && !empty($product[$i]->cancelable_till) ? $product[$i]->cancelable_till : '';
            $product[$i]->deliverable_zones_ids = isset($product[$i]->deliverable_zones) && !empty($product[$i]->deliverable_zones) ? $product[$i]->deliverable_zones : '';
            $product[$i]->availability = isset($product[$i]->availability) && ($product[$i]->availability != "") ? intval($product[$i]->availability) : '';
            $product[$i]->sku = isset($product[$i]->sku) && ($product[$i]->sku != "") ? $product[$i]->sku : '';
            /* getting zipcodes from ids */
            if ($product[$i]->deliverable_type != 'NONE' && $product[$i]->deliverable_type != 'ALL') {
                $zones = [];
                $zone_ids = explode(",", $product[$i]->deliverable_zones);
                $zones = Zone::whereIn('id', $zone_ids)->get();

                $translatedZones = [];

                foreach ($zones as $zone) {
                    $translatedZones[] = getDynamicTranslation('zones', 'name', $zone->id, $language_code);
                }

                $product[$i]->deliverable_zones = implode(",", $translatedZones);
            } else {
                $product[$i]->deliverable_zones = '';
            }
            $product[$i]->category_name = (isset($product[$i]->category_name) && !empty($product[$i]->category_name)) ? getDynamicTranslation('categories', 'name', $product[$i]->category_id, $language_code) : '';
            // $product[$i]->category_name = (isset($product[$i]->category_name) && !empty($product[$i]->category_name)) ? $product[$i]->category_name : '';
            /* check product delivrable or not */

            if ($is_deliverable != NULL) {
                $zipcode = fetchDetails('zipcodes', ['zipcode' => $is_deliverable], '*');
                if (!empty($zipcode)) {
                    $product[$i]->is_deliverable = isProductDelivarable($type = 'zipcode', $zipcode[0]->id, $product[$i]->id, 'combo');
                } else {
                    $product[$i]->is_deliverable = false;
                }
            } else {
                $product[$i]->is_deliverable = false;
            }
            if ($product[$i]->deliverable_type == 1) {
                $product[$i]->is_deliverable = true;
            }

            $product[$i]->tags = (!empty($product[$i]->tags)) ? explode(",", $product[$i]->tags) : [];
            $product[$i]->minimum_order_quantity = isset($product[$i]->minimum_order_quantity) && (!empty($product[$i]->minimum_order_quantity)) ? $product[$i]->minimum_order_quantity : 1;
            $product[$i]->quantity_step_size = isset($product[$i]->quantity_step_size) && (!empty($product[$i]->quantity_step_size)) ? $product[$i]->quantity_step_size : 1;
            $product_ids = $product[$i]->product_ids;

            $is_purchased = OrderItems::where([
                'product_variant_id' => $product[$i]->id,
                'user_id' => $user_id
            ])->orderBy('id', 'desc')->limit(1)->get()->toArray();
            if (!empty($is_purchased) && strtolower($is_purchased[0]['active_status']) == 'delivered') {

                $product[$i]->is_purchased = 1;
            } else {

                $product[$i]->is_purchased = 0;
            }
            $similar_product_ids = $product[$i]->similar_product_ids;
            $product_details = Product::select('id', 'name', 'image', 'type', 'slug', 'category_id', 'brand', 'tax', 'is_prices_inclusive_tax')->whereIn('id', explode(',', $product_ids))->get()->toarray();
            for ($k = 0; $k < count($product_details); $k++) {
                $product_details[$k]['image'] = asset('storage' . $product_details[$k]['image']);
                $variants = getVariantsValuesByPid($product_details[$k]['id']);
                $tax_percentages = [];
                $tax_ids = explode(",", $product_details[$k]['tax']);
                $tax_percentages = Tax::whereIn('id', $tax_ids)->get()->toArray();
                $tax_percentages = array_column($tax_percentages, 'percentage');
                $product_details[$k]['tax_percentage'] = implode(",", $tax_percentages);
                $product_details[$k]['name'] = getDynamicTranslation('products', 'name', $product_details[$k]['id'], $language_code);
                foreach ($variants as &$variant) {
                    $variant->product_name = getDynamicTranslation('products', 'name', $variant->product_id, $language_code);
                    if ((isset($product_details[$k]['is_prices_inclusive_tax']) && $product_details[$k]['is_prices_inclusive_tax'] == 0)) {
                        $percentage = (isset($product_details[$k]['tax_percentage']) && intval($product_details[$k]['tax_percentage']) > 0 && $product_details[$k]['tax_percentage'] != null) ? $product_details[$k]['tax_percentage'] : '';
                        $variant->price = strval(calculatePriceWithTax($percentage, $variant->price));
                        $variant->special_price = strval(calculatePriceWithTax($percentage, $variant->special_price));
                    } else {
                        $variant->price = strval($variant->price);

                        $variant->special_price = strval($variant->special_price);
                    }
                    // Check if 'images' is a string "[]" and convert it to an empty array []
                    if ($variant->images === "[]" || $variant->images == null) {
                        $variant->images = [];
                    } else {
                        $variant->images = json_decode($variant->images);
                    }
                }
                $product_details[$k]['variants'] = $variants;
            }


            $similar_product_details = ComboProduct::select('title', 'image', 'id')->whereIn('id', explode(',', $similar_product_ids))->get()->toarray();
            for ($s = 0; $s < count($similar_product_details); $s++) {
                $similar_product_details_image = asset('storage' . $similar_product_details[$s]['image']);
                $similar_product_details[$s]['image'] = $similar_product_details_image;
                $similar_product_details[$s]['title'] = getDynamicTranslation('combo_products', 'title', $similar_product_details[$s]['id'], $language_code);
            }

            $product[$i]->product_details = $product_details;
            $product[$i]->similar_product_details = $similar_product_details;

            if (isset($total_data[0]->cal_discount_percentage)) {
                $dicounted_total = array_values(array_filter(explode(',', $total_data[0]->cal_discount_percentage)));
            } else {
                $dicounted_total = 0;
            }
            $response['total'] = (isset($filter) && !empty($filter['discount'])) ? count($dicounted_total) : $total;
        }
    }
    $response['min_price'] = (isset($min_price)) ? $min_price : "0";
    $response['max_price'] = (isset($max_price)) ? $max_price : "0";
    $response['total'] = $total;
    $response['category_ids'] = $category_ids;
    $response['brand_ids'] = $brand_ids;
    $response['combo_product'] = $product;
    return $response;
}

function getStock($id, $type)
{
    if ($type == 'variant') {
        $table = 'product_variants';
    } else {
        $table = 'products';
    }

    $stock = DB::table($table)
        ->where('id', $id)
        ->select('stock')
        ->first();

    return $stock ? $stock->stock : null;
}

function getAttributeValuesByPid($id)
{

    $attribute_values = DB::table('product_attributes as pa')
        ->selectRaw("
        GROUP_CONCAT(av.id ORDER BY av.id ASC) as ids,
        GROUP_CONCAT(' ', av.value ORDER BY av.id ASC) as value,
        a.name as attr_name,
        a.id as attr_id,
        a.name")
        ->join('attribute_values as av', DB::raw('FIND_IN_SET(av.id, pa.attribute_value_ids)'), '>', DB::raw('0'))
        ->join('attributes as a', 'a.id', '=', 'av.attribute_id')
        ->where('pa.product_id', $id)
        ->groupBy('a.name')
        ->get()
        ->toArray();
    // dd(DB::getQueryLog());
    // dd($attribute_values);
    if (!empty($attribute_values)) {
        foreach ($attribute_values as &$attribute_value) {
            $attribute_value = ((array) $attribute_value);
        }
    }
    return $attribute_values;
}

function getComboAttributeValuesByPid($id)
{

    $attribute_values = DB::table('combo_products as pa')
        ->selectRaw('group_concat(av.id) as ids, group_concat(av.value) as value, a.name as attr_name, a.name,a.id as attr_id')
        ->join('combo_product_attribute_values as av', function ($join) {
            $join->on(DB::raw('find_in_set(av.id, pa.attribute_value_ids)'), '>', DB::raw('0'));
        }, null, null, 'inner')
        ->join('combo_product_attributes as a', 'a.id', '=', 'av.combo_product_attribute_id')
        ->where('pa.id', $id)
        ->where('a.status', 1)
        ->where('av.status', 1)
        ->groupBy('a.name')
        ->get()
        ->toArray();

    if (!empty($attribute_values)) {
        foreach ($attribute_values as &$attribute_value) {
            $attribute_value = ((array) $attribute_value);
        }
    }

    return $attribute_values;
}

function getPrice($type = 'max', $store_id = null)
{
    static $result = null;

    if ($result === null) {
        $result = DB::table('products as p')
            ->select(
                DB::raw(
                    'IF(pv.special_price > 0, pv.special_price, pv.price) as pr_price,
                p.is_prices_inclusive_tax,
                IF(p.is_prices_inclusive_tax = 0 AND p.tax IS NOT NULL,
                    (SELECT percentage FROM taxes WHERE id = p.tax AND status = 1),
                    0) as tax_percentage'
                )
            )
            ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
            ->leftJoin('seller_data as sd', 'p.seller_id', '=', 'sd.id')
            ->leftJoin('product_variants as pv', 'p.id', '=', 'pv.product_id')
            ->leftJoin('product_attributes as pa', 'pa.product_id', '=', 'p.id')
            ->where('p.status', 1)
            ->where('pv.status', 1)
            ->where('sd.status', 1)
            ->where('p.store_id', $store_id)
            ->where(function ($query) {
                $query->where('c.status', 1)
                    ->orWhere('c.status', 0);
            })
            ->get()->toArray();
    }

    $prices = array_map(function ($item) {
        $price = floatval($item->pr_price);
        if ($item->is_prices_inclusive_tax == 0) {
            $tax_percentage = floatval($item->tax_percentage);
            $price += $price * ($tax_percentage / 100);
        }
        return $price;
    }, $result);

    if (!empty($prices)) {
        return ($type == 'min') ? min($prices) : max($prices);
    } else {
        return 0;
    }
}

function getComboPrice($type = "max", $store_id = null)
{
    $result = DB::table('combo_products as p')
        ->select(
            DB::raw(
                'IF(p.special_price > 0, p.special_price, p.price) as pr_price,
            p.is_prices_inclusive_tax,
            IF(p.is_prices_inclusive_tax = 0 AND p.tax IS NOT NULL,
                (SELECT percentage FROM taxes WHERE id = p.tax AND status = 1),
                0) as tax_percentage'
            )
        )
        ->leftJoin('seller_data as sd', 'p.seller_id', '=', 'sd.id')
        ->where('p.status', 1)
        ->where('sd.status', 1)
        ->where('p.store_id', $store_id)
        ->get()->toArray();

    $prices = array_map(function ($item) {
        $price = floatval($item->pr_price);
        if ($item->is_prices_inclusive_tax == 0) {
            $tax_percentage = floatval($item->tax_percentage);
            $price += $price * ($tax_percentage / 100);
        }
        return $price;
    }, $result);

    if (!empty($prices)) {
        return ($type == 'min') ? min($prices) : max($prices);
    } else {
        return 0;
    }
}

function getMinMaxPriceOfProduct($product_id = '')
{
    // Construct the base query
    $query = DB::table('products as p')
        ->leftJoin('taxes as tax_id', function ($join) {
            $join->on(DB::raw('FIND_IN_SET(tax_id.id, p.tax)'), '>', DB::raw('0'));
        })
        ->leftJoin('product_variants as pv', 'p.id', '=', 'pv.product_id')
        ->select(
            'p.is_prices_inclusive_tax',
            'pv.price',
            'pv.special_price',
            DB::raw('(SELECT GROUP_CONCAT(tax.percentage) FROM taxes as tax WHERE FIND_IN_SET(tax.id, p.tax)) as tax_percentage'),
        );

    // Apply product ID filter if provided
    if (!empty($product_id)) {
        $query->where('p.id', $product_id);
    }

    // Execute the query and retrieve results
    $response = $query->get()->toArray();

    // Calculate tax amount
    $price_tax_amount = $special_price_tax_amount = 0;
    if (isset($response[0]->is_prices_inclusive_tax) && $response[0]->is_prices_inclusive_tax == 0) {

        $total_tax = array_sum(explode(',', floatval($response[0]->tax_percentage)));
        $price_tax_amount = $total_tax / 100 * $response[0]->price;
        $special_price_tax_amount = $total_tax / 100 * $response[0]->special_price;
    }

    // Calculate min and max prices considering tax
    $min_price = collect($response)->pluck('price')->min() + $price_tax_amount;
    $max_price = collect($response)->pluck('price')->max() + $price_tax_amount;
    $special_min_price = collect($response)->pluck('special_price')->min() + $special_price_tax_amount;
    $special_max_price = collect($response)->pluck('special_price')->max() + $special_price_tax_amount;

    // Calculate discount in percentage
    $discount_in_percentage = findDiscountInPercentage($special_min_price, $min_price);

    return compact('min_price', 'max_price', 'special_min_price', 'special_max_price', 'discount_in_percentage');
}

function getMinMaxPriceOfComboProduct($product_id = '')
{
    $query = DB::table('combo_products as p')
        ->leftJoin('taxes as tax_id', function ($join) {
            $join->on(DB::raw('FIND_IN_SET(tax_id.id, p.tax)'), '>', DB::raw('0'));
        })
        ->select(
            'p.is_prices_inclusive_tax',
            'p.price',
            'p.special_price',
            DB::raw('(SELECT GROUP_CONCAT(tax.percentage) FROM taxes as tax WHERE FIND_IN_SET(tax.id, p.tax)) as tax_percentage'),
        );


    if (!empty($product_id)) {
        $query->where('p.id', $product_id);
    }

    $response = $query->get()->toArray();

    if ((isset($response[0]->is_prices_inclusive_tax) && $response[0]->is_prices_inclusive_tax == 0)) {
        $percentage = (isset($response[0]->tax_percentage) && intval($response[0]->tax_percentage) > 0) ? $response[0]->tax_percentage : '0';
        $tax_percentage = explode(',', $percentage);
        $total_tax = array_sum($tax_percentage);

        $price_tax_amount = $response[0]->price * ($total_tax / 100);

        $special_price_tax_amount = $response[0]->special_price * ($total_tax / 100);
    } else {
        $price_tax_amount = 0;
        $special_price_tax_amount = 0;
    }

    $data['min_price'] = collect($response)->pluck('price')->min() + $price_tax_amount;
    $data['max_price'] = collect($response)->pluck('price')->max() + $price_tax_amount;
    $data['special_price'] = collect($response)->pluck('special_price')->min() + $special_price_tax_amount;
    $data['max_special_price'] = collect($response)->pluck('special_price')->max() + $special_price_tax_amount;

    $data['discount_in_percentage'] = findDiscountInPercentage($data['special_price'] + $special_price_tax_amount, $data['min_price'] + $price_tax_amount);

    return $data;
}
function findDiscountInPercentage($special_price, $price)
{
    $diff_amount = $price - $special_price;
    if ($diff_amount > 0) {
        return intval(($diff_amount * 100) / $price);
    }
}
function subCategories($id, $level)
{
    $level = $level + 1;
    $category = Category::find($id);
    $categories = $category->children;

    $i = 0;
    foreach ($categories as $p_cat) {
        $categories[$i]->children = subCategories($p_cat->id, $level);
        $categories[$i]->text = e($p_cat->name); // Use the Laravel "e" helper for output escaping
        $categories[$i]->state = ['opened' => true];
        $categories[$i]->level = $level;
        $p_cat['image'] = getImageUrl($p_cat['image'], 'thumb', 'md');
        $p_cat['banner'] = getImageUrl($p_cat['banner'], 'thumb', 'md');
        $i++;
    }

    return $categories;
}
function getSellerCategories($seller_id)
{
    $store_id = getStoreId();

    $level = 0;
    $seller_id = isset($seller_id) ? $seller_id : '';


    $seller_data = DB::table('seller_store')
        ->select('seller_store.category_ids')
        ->where('seller_store.store_id', $store_id)
        ->where('seller_store.seller_id', $seller_id)
        ->get();



    if ($seller_data->isEmpty()) {
        return [];
    }

    $category_ids = explode(",", $seller_data[0]->category_ids);

    $categories = Category::whereIn('id', $category_ids)
        ->where('status', 1)
        ->get()
        ->toArray();

    foreach ($categories as &$p_cat) {
        $p_cat['children'] = subCategories($p_cat['id'], $level);
        $p_cat['text'] = e($p_cat['name']);
        $p_cat['name'] = e($p_cat['name']);
        $p_cat['state'] = ['opened' => true];
        $p_cat['icon'] = "jstree-folder";
        $p_cat['level'] = $level;
        $p_cat['image'] = getImageUrl($p_cat['image'], 'thumb', 'md');
        $p_cat['banner'] = getImageUrl($p_cat['banner'], 'thumb', 'md');
    }

    if (!empty($categories)) {
        $categories[0]['total'] = count($category_ids);
    }

    return $categories;
}

function validateStock($product_variant_ids, $qtns, $product_type = "")
{
    // dd($product_type);
    $is_exceed_allowed_quantity_limit = false;
    $error = false;

    foreach ($product_variant_ids as $index => $product_variant_id) {

        if ($product_type[$index] == 'regular') {

            // $product_variant = Product_variants::with(['product'])
            //     ->where('id', $product_variant_id)
            //     ->first();

            $product_variant = Product_variants::with(['product:id,stock_type'])
                ->where('id', $product_variant_id)
                ->first();
            // dd($product_variant);
            // dd($product_variant->product);
            // dd($product_variant->stock_type);
        } else {

            $product_variant = ComboProduct::where('id', $product_variant_id)
                ->first();
        }
        if ($product_variant->total_allowed_quantity !== null && $product_variant->total_allowed_quantity > 0) {
            $total_allowed_quantity = intval($product_variant->total_allowed_quantity) - intval($qtns[$index]);

            if ($total_allowed_quantity < 0) {
                $error = true;
                $is_exceed_allowed_quantity_limit = true;
                $response['message'] = 'One of the products quantity exceeds the allowed limit. Please deduct some quantity in order to purchase the item';
                break;
            }
        }

        // dd(intval($product_variant->stock) - intval($qtns[$index]) < 0);

        // dd($product_variant->stock_type);
        if ($product_type[$index] == 'regular') {
            if (($product_variant->stock_type !== null || $product_variant->stock_type !== 'null') && $product_variant->stock_type !== '') {
                // dd(intval($product_variant->product->stock) - intval($qtns[$index]));
                if ($product_variant->stock_type == 0) {
                    if ($product_variant->product->stock !== null && $product_variant->product->stock !== '') {
                        $stock = intval($product_variant->product->stock) - intval($qtns[$index]);
                        if ($stock < 0 || $product_variant->product->availability == 0) {
                            $error = true;
                            $response['message'] = 'One of the product is out of stock.';
                        }
                        // dd($response);
                    }
                    // dd($product_variant->stock);
                } elseif ($product_variant->stock_type == 1 || $product_variant->stock_type == 2) {
                    // dd('here');
                    if ($product_variant->stock !== null && $product_variant->stock !== '') {
                        $stock = intval($product_variant->stock) - intval($qtns[$index]);
                        if ($stock < 0 || $product_variant->availability == 0) {
                            $error = true;
                            $response['message'] = 'One of the product is .';
                            break;
                        }
                    }
                }
            }
        } else {

            if ($product_variant->stock !== null && $product_variant->stock !== '') {
                $stock = intval($product_variant->stock) - intval($qtns[$index]);

                if ($stock < 0 || $product_variant->availability == 0) {
                    $error = true;
                    $response['message'] = 'One of the product is out of stock.';
                }
            }
        }
    }
    // dd($error);
    if ($error) {
        $response['error'] = true;
        if ($is_exceed_allowed_quantity_limit) {
            $response['message'] = 'One of the products quantity exceeds the allowed limit. Please deduct some quantity in order to purchase the item';
        } else {
            $response['message'] = "One of the product is out of stock.";
        }
    } else {
        $response['error'] = false;
        $response['message'] = "Stock available for purchasing.";
    }

    return $response;
}

function validateComboStock($product_ids, $qtns)
{
    $is_exceed_allowed_quantity_limit = false;
    $error = false;

    foreach ($product_ids as $index => $product_id) {
        $combo_product = ComboProduct::where('id', $product_id)
            ->first();

        if ($combo_product->total_allowed_quantity !== null && $combo_product->total_allowed_quantity >= 0) {

            $total_allowed_quantity = intval($combo_product->total_allowed_quantity) - intval($qtns[$index]);
            if ($total_allowed_quantity < 0) {
                $error = true;
                $is_exceed_allowed_quantity_limit = true;
                $response['message'] = 'One of the products quantity exceeds the allowed limit. Please deduct some quantity in order to purchase the item';
                break;
            }
        }

        if ($combo_product->stock !== null && $combo_product->stock !== '') {
            if ($combo_product->stock == 0) {
                if ($combo_product->product->stock !== null && $combo_product->product->stock !== '') {
                    $stock = intval($combo_product->product->stock) - intval($qtns[$index]);
                    if ($stock < 0 || $combo_product->product->availability == 0) {
                        $error = true;
                        $response['message'] = 'One of the product is out of stock.';
                    }
                }
            }
        }
    }
    if ($error) {
        $response['error'] = true;
        if ($is_exceed_allowed_quantity_limit) {
            $response['message'] = 'One of the products quantity exceeds the allowed limit. Please deduct some quantity in order to purchase the item';
        } else {
            $response['message'] = "One of the product is out of stock.";
        }
    } else {
        $response['error'] = false;
        $response['message'] = "Stock available for purchasing.";
    }

    return $response;
}

function addToCart($data, $check_status = true, $fromApp = false)
{
    $data = array_map('htmlspecialchars', $data);
    $product_type = $data['product_type'] != null ? explode(',', Str::lower($data['product_type'])) : [];
    $product_variant_ids = explode(',', $data['product_variant_id']);
    $store_id = explode(',', $data['store_id']);
    // dd($store_id);
    $qtys = explode(',', $data['qty']);

    if ($check_status == true) {

        $check_current_stock_status = validateStock($product_variant_ids, $qtys, $product_type);
        // dd($check_current_stock_status);
        if (!empty($check_current_stock_status) && $check_current_stock_status['error'] == true) {
            return $check_current_stock_status;
        }
    }

    foreach ($product_variant_ids as $index => $product_variant_id) {

        $cart_data = [
            'user_id' => $data['user_id'],
            'product_variant_id' => $product_variant_id,
            'qty' => $qtys[$index],
            'is_saved_for_later' => (isset($data['is_saved_for_later']) && !empty($data['is_saved_for_later']) && $data['is_saved_for_later'] == '1') ? $data['is_saved_for_later'] : '0',
            'store_id' => (isset($store_id) && !empty($store_id)) ? $store_id[$index] : '',
            // 'store_id' => (isset($store_id) && !empty($store_id)) ? $store_id : '',
            'product_type' => (isset($product_type) && !empty($product_type)) ? $product_type[$index] : '',
        ];

        if ($qtys[$index] == 0) {

            removeFromCart($cart_data);
        } else {

            $existing_cart_item = Cart::where(['user_id' => $data['user_id'], 'product_variant_id' => $product_variant_id])->first();


            if (!empty($existing_cart_item) && $existing_cart_item != null) {

                $existing_cart_item->update($cart_data);

                if ($fromApp == true) {

                    return true;
                } else {
                    return true;
                }
            } else {

                Cart::create($cart_data);
                if ($fromApp == true) {
                    return true;
                }
            }
        }
    }
    return false;
}

function removeFromCart($data)
{
    $is_saved_for_later = isset($data['is_saved_for_later']) ? $data['is_saved_for_later'] : 0;
    if (isset($data['user_id']) && !empty($data['user_id'])) {
        $query = Cart::where('user_id', $data['user_id']);

        if (isset($data['product_variant_id'])) {
            $product_variant_ids = explode(',', $data['product_variant_id']);
            $query->whereIn('product_variant_id', $product_variant_ids);
        }
        if (isset($data['product_type'])) {
            $product_types = explode(',', $data['product_type']);
            $query->whereIn('product_type', $product_types);
        }
        $query->where('store_id', $data['store_id']);
        $query->where('is_saved_for_later', $is_saved_for_later);

        return $query->delete();
    } else {
        return false;
    }
}

function getCartTotal($user_id, $product_variant_id = false, $is_saved_for_later = 0, $address_id = '', $store_id = '')
{
    // dd($address_id);
    $query = [];
    // get product details from products table
    // dd($is_saved_for_later);
    $product_query = DB::table('cart as c')
        ->select(
            'c.qty',
            'c.id as cart_id',
            'c.is_saved_for_later',
            'c.product_type as cart_product_type',
            'p.store_id',
            'p.cod_allowed',
            'p.seller_id',
            'p.type',
            'p.download_allowed',
            'p.minimum_order_quantity',
            'p.minimum_free_delivery_order_qty',
            'p.delivery_charges as product_delivery_charge',
            'p.slug',
            'p.quantity_step_size',
            'p.total_allowed_quantity',
            'p.name',
            'p.image',
            'p.category_id',
            'p.stock as product_stock',
            'p.availability as product_availability',
            'p.short_description',
            'p.pickup_location',
            'p.is_prices_inclusive_tax',
            'pv.weight',
            'c.user_id',
            'pv.*',
            DB::raw('(SELECT GROUP_CONCAT(tax.percentage) FROM taxes as tax WHERE FIND_IN_SET(tax.id, p.tax)) as tax_percentage'),
            DB::raw('(SELECT GROUP_CONCAT(tax_title.title) FROM taxes as tax_title WHERE FIND_IN_SET(tax_title.id, p.tax)) as tax_title')
        )
        ->when($product_variant_id, function ($query) use ($product_variant_id, $user_id) {
            $query->where('c.product_variant_id', $product_variant_id)->where('c.user_id', $user_id)->where('c.qty', '>=', 0);
        })
        ->when(!$product_variant_id, function ($query) use ($user_id) {
            $query->where('c.user_id', $user_id)->where('c.qty', '>=', 0);
        })

        ->where('is_saved_for_later', intval($is_saved_for_later))
        ->where('c.store_id', $store_id)
        ->join('product_variants as pv', 'pv.id', '=', 'c.product_variant_id')
        ->join('products as p', 'pv.product_id', '=', 'p.id')
        ->join('seller_data as sd', 'sd.id', '=', 'p.seller_id')
        ->leftJoin('taxes as tax_id', function ($join) {
            $join->on(DB::raw('FIND_IN_SET(tax_id.id, p.tax)'), '>', DB::raw('0'));
        })
        ->leftJoin('categories as ctg', 'p.category_id', '=', 'ctg.id')
        ->where(['p.status' => '1', 'pv.status' => 1, 'sd.status' => 1, 'c.product_type' => 'regular'])
        ->groupBy('c.id')
        ->orderBy('c.id', 'DESC')
        ->get();
    // get product details from combo_products table

    $combo_product_query = DB::table('cart as c')
        ->select(
            'c.qty',
            'c.id as cart_id',
            'c.is_saved_for_later',
            'c.product_type as cart_product_type',
            'cp.*',
            'cp.id as product_id',
            'cp.title as name',
            'cp.delivery_charges as product_delivery_charge',
            'cp.stock as product_stock',
            'cp.availability as product_availability',
            DB::raw('(SELECT GROUP_CONCAT(c_tax.percentage) FROM taxes as c_tax WHERE FIND_IN_SET(c_tax.id, cp.tax)) as tax_percentage'),
            DB::raw('(SELECT GROUP_CONCAT(c_tax_title.title) FROM taxes as c_tax_title WHERE FIND_IN_SET(c_tax_title.id, cp.tax)) as c_tax_title')
        )

        ->when($product_variant_id, function ($query) use ($product_variant_id, $user_id) {
            $query->where('c.product_variant_id', $product_variant_id)->where('c.user_id', $user_id)->where('c.qty', '>=', 0);
        })
        ->when(!$product_variant_id, function ($query) use ($user_id) {
            $query->where('c.user_id', $user_id)->where('c.qty', '>=', 0);
        })

        ->where('is_saved_for_later', intval($is_saved_for_later))
        ->where('c.store_id', $store_id)
        ->leftJoin('combo_products as cp', 'c.product_variant_id', '=', 'cp.id')
        ->join('seller_data as sd', 'sd.id', '=', 'cp.seller_id')
        ->leftJoin('taxes as c_tax_id', function ($join) {
            $join->on(DB::raw('FIND_IN_SET(c_tax_id.id, cp.tax)'), '>', DB::raw('0'));
        })
        ->where(['cp.status' => '1', 'sd.status' => 1, 'c.product_type' => 'combo'])
        ->groupBy('c.id')
        ->orderBy('c.id', 'DESC')
        ->get();

    $query = $product_query->merge($combo_product_query);
    // dd($query);
    $total = [];
    $item_total = [];
    $variant_id = [];
    $quantity = [];
    $percentage = [];
    $amount = [];
    $cod_allowed = 1;
    $download_allowed = [];
    $totalItems = 0;
    $product_qty = '';
    $product_ids = [];
    $cart_product_type = [];

    if (!$query->isEmpty()) {

        foreach ($query as $result) {
            $totalItems += $result->qty;
        }

        foreach ($query as $i => $item) {
            if ($item->cart_product_type != 'combo') {
                $category_ids[$i] = $item->category_id;
            }
            $product_ids[$i] = $item->product_id;
            $cart_product_type[$i] = $item->cart_product_type;
            $item = (array) $item;

            $prctg = isset($item['tax_percentage']) && intval($item['tax_percentage']) > 0 && $item['tax_percentage'] !== null ? $item['tax_percentage'] : '';
            $tax_title = $item['tax_title'] ?? '';
            $item['item_tax_percentage'] = $prctg;
            $item['tax_title'] = $tax_title;

            if ($item['is_prices_inclusive_tax'] == 0) {

                $tax_percentage = explode(',', $prctg);
                // $total_tax = array_sum($tax_percentage);
                $total_tax = array_sum(array_map('floatval', $tax_percentage));

                $price_tax_amount = $item['price'] * ($total_tax / 100);
                $special_price_tax_amount = $item['special_price'] * ($total_tax / 100);
            } else {
                $price_tax_amount = 0;
                $special_price_tax_amount = 0;
            }

            if ($item['cod_allowed'] === 0) {
                $cod_allowed = 0;
            }
            $variant_id[$i] = $item['id'];
            $quantity[$i] = intval($item['qty']);
            if (($item['special_price']) > 0) {
                $total[$i] = ($item['special_price'] + $special_price_tax_amount) * $item['qty'];
            } else {
                $total[$i] = ($item['price'] + $price_tax_amount) * $item['qty'];
            }
            $item_total[$i] = ($item['price'] + $price_tax_amount) * $item['qty'];

            $item['special_price'] = $item['special_price'] + $special_price_tax_amount;
            $item['price'] = $item['price'] + $price_tax_amount;

            $percentage[$i] = (isset($item['tax_percentage']) && ($item['tax_percentage']) > 0) ? $item['tax_percentage'] : 0;

            if ($percentage[$i] !== null && $percentage[$i] > 0) {
                $amount[$i] = !empty($special_price_tax_amount) ? $special_price_tax_amount : $price_tax_amount;
                $amount[$i] = $amount[$i] * $item['qty']; // added because tax amount is not changing based on qty
            } else {
                $amount[$i] = 0;
                $percentage[$i] = 0;
            }
            if ($item['cart_product_type'] != 'combo') {
                $item['product_variants'] = getVariantsValuesById($item['id']);
            } else {
                $item['type'] = 'combo';
            }
            array_push($download_allowed, $item['download_allowed']);

            $item['cart_count'] = $query->count();

            $item['total_items'] = $totalItems;
            $product_qty .= $item['qty'] . ',';

            $query[$i] = (object) $item;

            $item['image'] = getMediaImageUrl($item['image']);

            $items[] = $item;
        }

        $total = array_sum($total);
        $item_total = array_sum($item_total);


        $settings = getDeliveryChargeSetting($store_id);

        $shipping_settings = getSettings('shipping_method', true, true);
        $shipping_settings = json_decode($shipping_settings, true);

        $delivery_charge = '';
        // dd($address_id);
        if (!empty($address_id)) {

            $address = fetchDetails('addresses', ['id' => $address_id], ['area_id', 'area', 'pincode', 'city']);
            $pincode = $address != null ? $address[0]->pincode : 0;
            $zipcode_id = fetchDetails('zipcodes', ['zipcode' => $address[0]->pincode], 'id');
            $city_id = fetchDetails('cities', ['name' => $address[0]->city], 'id');

            if (isset($settings[0]->product_deliverability_type) && !empty($settings[0]->product_deliverability_type)) {
                if ($settings[0]->product_deliverability_type == 'city_wise_deliverability') {
                    $tmpRow['is_deliverable'] = (!empty($city_id[0]->id) && $city_id[0]->id > 0) ?
                        isProductDelivarable('city', $city_id[0]->id, $query[0]->product_id, $query[0]->cart_product_type)
                        : false;
                } else {
                    $tmpRow['is_deliverable'] = (!empty($zipcode_id[0]->id) && $zipcode_id[0]->id > 0) ?
                        isProductDelivarable('zipcode', $zipcode_id[0]->id, $query[0]->product_id, $query[0]->cart_product_type)
                        : false;
                }
            }
            // dd('here');
            $tmpRow['delivery_by'] = $tmpRow['is_deliverable'] ? "local" : ((isset($shipping_settings['shiprocket_shipping_method']) && $shipping_settings['shiprocket_shipping_method'] == 1) ? 'standard_shipping' : '');
            if (isset($tmpRow['delivery_by']) && $tmpRow['delivery_by'] === 'standard_shipping') {

                $parcels = makeShippingParcels($query);
                $parcels_details = checkParcelsDeliverability($parcels, $pincode);
                $delivery_charge = $parcels_details['delivery_charge_without_cod'];
            } else {
                // dd($query[0]->product_id);
                // dd($is_saved_for_later);
                $product_availability = checkCartProductsDeliverable($user_id, '', '', $store_id, '', '', $is_saved_for_later);
                // dd($product_availability);
                for ($i = 0; $i < count($query); $i++) {
                    $cart[$i]['product_qty'] = $product_availability[$i]['product_qty'];
                    $cart[$i]['minimum_free_delivery_order_qty'] = $product_availability[$i]['minimum_free_delivery_order_qty'];
                    $cart[$i]['product_delivery_charge'] = $product_availability[$i]['product_delivery_charge'];
                    $cart[$i]['currency_product_delivery_charge_data'] = getPriceCurrency($cart[$i]['product_delivery_charge']);
                    if (isset($cart[$i]['delivery_by']) && $cart[$i]['delivery_by'] == "standard_shipping") {
                        $standard_shipping_cart[] = $cart[$i];
                    } else {
                        $local_shipping_cart[] = $cart[$i];
                    }
                }

                // dd('here');

                $delivery_charge = getDeliveryCharge($address_id, $total, $local_shipping_cart, $store_id);

                // dd($delivery_charge);
                if (isset($settings[0]->delivery_charge_type) && !empty($settings[0]->delivery_charge_type) && $settings[0]->delivery_charge_type == 'product_wise_delivery_charge') {

                    $deliveryCharge = 0;
                    foreach ($delivery_charge as $row) {
                        $deliveryCharge += isset($row['delivery_charge']) && !empty($row['delivery_charge']) ? $row['delivery_charge'] : 0;
                    }
                    $delivery_charge = $deliveryCharge;
                }
                // dd($delivery_charge);
            }
        }


        $delivery_charge = isset($query[0]->type) && $query[0]->type == 'digital_product' ? 0 : $delivery_charge;
        $discount = $item_total - $total;
        // dd($discount);
        // dd($percentage);

        $tax_amount = array_sum($amount);
        $overall_amt = (float) $total + (float) $delivery_charge;
        $query[0]->is_cod_allowed = $cod_allowed;
        $query['sub_total'] = strval($total);
        $query['item_total'] = strval($item_total);
        $query['discount'] = strval($discount);
        $query['currency_sub_total_data'] = getPriceCurrency($query['sub_total']);
        $query['product_quantity'] = $product_qty;
        $query['quantity'] = strval(array_sum($quantity));
        // $query['tax_percentage'] = strval(array_sum($percentage));
        $query['tax_percentage'] = strval(array_sum(array_map('floatval', is_string($percentage) ? explode(',', $percentage) : $percentage)));
        $query['tax_amount'] = strval(array_sum($amount));
        $query['currency_tax_amount_data'] = getPriceCurrency($query['tax_amount']);
        $query['total_arr'] = $total;
        $query['currency_total_arr_data'] = getPriceCurrency($query['total_arr']);
        $query['variant_id'] = $variant_id;
        $query['delivery_charge'] = $delivery_charge;
        $query['currency_delivery_charge_data'] = getPriceCurrency($query['delivery_charge']);
        $query['overall_amount'] = strval($overall_amt);
        $query['currency_overall_amount_data'] = getPriceCurrency($query['overall_amount']);
        $query['amount_inclusive_tax'] = strval($overall_amt + $tax_amount);
        $query['currency_amount_inclusive_tax_data'] = getPriceCurrency($query['amount_inclusive_tax']);
        $query['download_allowed'] = $download_allowed;
        $query['cart_items'] = $items;
    }
    return $query;
}

function getDeliveryCharge($address_id, $total = 0, $cartData = [], $store_id = "")
{
    $total = str_replace(',', '', $total);

    $settings = getDeliveryChargeSetting($store_id);

    $address = Address::where('id', $address_id)->value('pincode');
    $address_city = Address::where('id', $address_id)->value('city');
    // dd($settings[0]->product_deliverability_type);
    if (isset($settings[0]->product_deliverability_type) && !empty($settings[0]->product_deliverability_type)) {
        if ($settings[0]->product_deliverability_type == 'city_wise_deliverability') {
            // dd('here');
            if (isset($settings[0]->delivery_charge_type) && !empty($settings[0]->delivery_charge_type) && $settings[0]->delivery_charge_type == 'city_wise_delivery_charge') {

                if (isset($address_city) && !empty($address_city)) {
                    $city = City::where('name', $address_city)->select('delivery_charges', 'minimum_free_delivery_order_amount')->first();
                    if ($city && isset($city->minimum_free_delivery_order_amount)) {
                        $min_amount = $city->minimum_free_delivery_order_amount;
                        $delivery_charge = $city->delivery_charges;
                    }
                    $d_charge = intval($total) < $min_amount || $total === 0 ? $delivery_charge : 0;
                    // dd($d_charge);
                    return number_format($d_charge, 2);
                }
            } else if (isset($settings[0]->delivery_charge_type) && !empty($settings[0]->delivery_charge_type) && $settings[0]->delivery_charge_type == 'global_delivery_charge') {

                $min_amount = $settings[0]->minimum_free_delivery_amount;
                $delivery_charge = $settings[0]->delivery_charge_amount;
                $d_charge = intval($total) < $min_amount || $total === 0 ? $delivery_charge : 0;

                return number_format($d_charge, 2);
            } else if (isset($settings[0]->delivery_charge_type) && !empty($settings[0]->delivery_charge_type) && $settings[0]->delivery_charge_type == 'product_wise_delivery_charge') {
                $d_charge = [];
                foreach ($cartData as $row) {
                    // dd($row['minimum_free_delivery_order_qty']);
                    // dd($row);
                    $temp['delivery_charge'] = $row['product_qty'] < $row['minimum_free_delivery_order_qty'] ? number_format($row['product_delivery_charge'], 2) : [];
                    // $temp['delivery_charge'] = number_format($row['product_delivery_charge'], 2);
                    // dd($temp['delivery_charge']);
                    array_push($d_charge, $temp);
                }
                // dd($d_charge);
                return $d_charge;
            }
        } else {
            // dd($settings[0]->delivery_charge_type);
            if (isset($settings[0]->delivery_charge_type) && !empty($settings[0]->delivery_charge_type) && $settings[0]->delivery_charge_type == 'zipcode_wise_delivery_charge') {
                // if (isset($address) && !empty($address)) {
                //     $zipcode = Zipcode::where('zipcode', $address)->select('delivery_charges', 'minimum_free_delivery_order_amount')->first();
                //     // dd($zipcode->minimum_free_delivery_order_amount);
                //     if ($zipcode && isset($zipcode->minimum_free_delivery_order_amount)) {
                //         $min_amount = $zipcode->minimum_free_delivery_order_amount;
                //         $delivery_charge = $zipcode->delivery_charges;
                //     }
                //     $d_charge = intval($total) < $min_amount || $total === 0 ? $delivery_charge : 0;
                //     return number_format($d_charge, 2);
                // }
                if (isset($address) && !empty($address)) {
                    $zipcode = Zipcode::where('zipcode', $address)->select('delivery_charges', 'minimum_free_delivery_order_amount')->first();

                    if ($zipcode) {
                        $min_amount = $zipcode->minimum_free_delivery_order_amount ?? 0;
                        $delivery_charge = $zipcode->delivery_charges ?? 0;

                        $d_charge = intval($total) < $min_amount || $total == 0 ? $delivery_charge : 0;
                        return number_format($d_charge, 2);
                    } else {
                        // No zipcode found, handle safely
                        return number_format(0, 2);
                    }
                } else {
                    // Address empty, handle safely
                    return number_format(0, 2);
                }
            } else if (isset($settings[0]->delivery_charge_type) && !empty($settings[0]->delivery_charge_type) && $settings[0]->delivery_charge_type == 'global_delivery_charge') {
                $min_amount = $settings[0]->minimum_free_delivery_amount;
                $delivery_charge = $settings[0]->delivery_charge_amount;
                $d_charge = intval($total) < $min_amount || $total === 0 ? $delivery_charge : 0;

                // dd($d_charge);
                return number_format($d_charge, 2);
            } else if (isset($settings[0]->delivery_charge_type) && !empty($settings[0]->delivery_charge_type) && $settings[0]->delivery_charge_type == 'product_wise_delivery_charge') {
                $d_charge = [];
                foreach ($cartData as $row) {
                    // $temp['delivery_charge'] = $row['product_qty'] < $row['minimum_free_delivery_order_qty'] ? number_format($row['product_delivery_charge'], 2) : [];
                    $temp['delivery_charge'] = number_format((float) $row['product_delivery_charge'], 2);
                    array_push($d_charge, $temp);
                }
                return $d_charge;
            }
        }
    }
}


function isProductDelivarable($type, $type_id, $product_id, $product_type = '')
{
    if ($type == 'zipcode') {
        $zipcode_id = $type_id;
    } elseif ($type == 'area') {
        $zipcode_id = Area::where('id', $type_id)->value('zipcode_id');
        $zipcode_id = $zipcode_id;
    } elseif ($type == 'city') {
        $city_id = $type_id;
    } else {
        return false;
    }
    // dd($zipcode_id);
    if (!empty($zipcode_id) && $zipcode_id != 0) {
        $table = $product_type === 'combo' || $product_type === 'combo-product' ? 'combo_products' : 'products';
        $model = $product_type === 'combo' || $product_type === 'combo-product' ? ComboProduct::class : Product::class;

        $deliverable_zones = getDeliverableZones($table, $product_id);
        //    dd($deliverable_zones);
        $zones_serviceable_zipcodes = getZonesServiceableByZipcode($deliverable_zones, $zipcode_id);
        // dd($zones_serviceable_zipcodes);
        $product = $model::join('seller_store', "$table.seller_id", '=', 'seller_store.seller_id')
            ->where("$table.id", $product_id)
            ->where(function ($query) use ($zones_serviceable_zipcodes, $zipcode_id, $table) {
                $query->where(function ($subquery) use ($zones_serviceable_zipcodes, $table) {
                    $subquery->where("$table.deliverable_type", '2')
                        ->whereIn("$table.deliverable_zones", $zones_serviceable_zipcodes);
                })
                    ->orWhere("$table.deliverable_type", '1');
            })
            ->count();
        return $product > 0;
    } elseif (!empty($city_id) && $city_id != 0) {
        // dd('here');
        $table = $product_type === 'combo' || $product_type === 'combo-product' ? 'combo_products' : 'products';
        $model = $product_type === 'combo' || $product_type === 'combo-product' ? ComboProduct::class : Product::class;

        $deliverable_zones = getDeliverableZones($table, $product_id);
        // dd($deliverable_zones);
        $zones_serviceable_cities = getZonesServiceableByCity($deliverable_zones, $city_id);
        // DD($zones_serviceable_cities);
        // $product = $model::join('seller_store', "$table.seller_id", '=', 'seller_store.seller_id')
        //     ->where("$table.id", $product_id)
        //     ->where(function ($query) use ($zones_serviceable_cities, $table) {
        //         $query->where(function ($subquery) use ($zones_serviceable_cities, $table) {
        //             $subquery->where("$table.deliverable_type", '2')
        //                 ->whereIn("$table.deliverable_zones", $zones_serviceable_cities);
        //         })
        //             ->orWhere("$table.deliverable_type", '1');
        //     })
        //     ->count();

        $product = $model::join('seller_store', "$table.seller_id", '=', 'seller_store.seller_id')
            ->where("$table.id", $product_id)
            ->where(function ($query) use ($zones_serviceable_cities, $table) {
                $query->where(function ($subquery) use ($zones_serviceable_cities, $table) {
                    $subquery->where("$table.deliverable_type", '2');
                    $subquery->where(function ($inner) use ($zones_serviceable_cities, $table) {
                        foreach ($zones_serviceable_cities as $zoneId) {
                            $inner->orWhereRaw("FIND_IN_SET(?, $table.deliverable_zones)", [$zoneId]);
                        }
                    });
                })->orWhere("$table.deliverable_type", '1');
            });
        $product = $product->count();
        // dd($product);
        return $product > 0;
    } else {
        return false;
    }
}

function isSellerDeliverable($type, $type_id, $seller_id, $store_id = '')
{
    if ($type == 'zipcode') {
        $zipcode_id = $type_id;
    } elseif ($type == 'area') {
        $zipcode_id = Area::where('id', $type_id)->value('zipcode_id');
        $zipcode_id = $zipcode_id;
    } elseif ($type == 'city') {
        $city_id = $type_id;
    } else {
        return false;
    }


    if (!empty($zipcode_id) && $zipcode_id != 0) {
        // dd('here');
        $deliverable_zones = getSellerDeliverableZones($seller_id, $store_id);

        $seller_store = DB::table('seller_store')->where('seller_id', $seller_id)->where('store_id', $store_id)->first();
        if ($seller_store) {
            if ($seller_store->deliverable_type == 1) {
                $all_zones = Zone::where('status', 1)->pluck('id')->toarray();
                $product = getZonesServiceableByZipcode($all_zones, $zipcode_id);
                return $product > 0;
            } else {
                // Check using FIND_IN_SET to match within comma-separated values
                $zones_serviceable_zipcodes = getZonesServiceableByZipcode($deliverable_zones, $zipcode_id);
                // dd($zones_serviceable_zipcodes);
                if (count($zones_serviceable_zipcodes) == 1) {
                    if ($zones_serviceable_zipcodes) {
                        $product = DB::table('seller_store')
                            ->whereRaw("FIND_IN_SET(?, deliverable_zones)", [$zones_serviceable_zipcodes])
                            ->where('seller_id', $seller_id)
                            ->where('store_id', $store_id)
                            ->count();


                        return $product > 0;
                    }
                } else {
                    if ($zones_serviceable_zipcodes) {
                        $product = DB::table('seller_store')->where('store_id', $store_id)->where('seller_id', $seller_id)
                            ->where(function ($query) use ($zones_serviceable_zipcodes) {
                                $query->where(function ($subquery) use ($zones_serviceable_zipcodes) {
                                    $subquery->where("seller_store.deliverable_type", '2')
                                        ->whereIn("seller_store.deliverable_zones", $zones_serviceable_zipcodes);
                                });
                            })
                            ->count();
                        return $product > 0;
                    }
                }
                return false;
            }
        }
    } elseif (!empty($city_id) && $city_id != 0) {
        $deliverable_zones = getSellerDeliverableZones($seller_id, $store_id);
        // dd($deliverable_zones);
        $seller_store = DB::table('seller_store')->where('seller_id', $seller_id)->where('store_id', $store_id)->first();
        if ($seller_store) {
            if ($seller_store->deliverable_type == 1) {
                $all_zones = Zone::where('status', 1)->pluck('id')->toarray();
                $product = getZonesServiceableByCity($all_zones, $city_id);
                return $product > 0;
            } else {
                // Check using FIND_IN_SET to match within comma-separated values
                $zones_serviceable_cities = getZonesServiceableByCity($deliverable_zones, $city_id);
                // dd(count($zones_serviceable_cities));
                if (count($zones_serviceable_cities) == 1) {
                    // dd('here');
                    $product = DB::table('seller_store')
                        ->whereRaw("FIND_IN_SET(?, deliverable_zones)", [$zones_serviceable_cities])
                        ->where('seller_id', $seller_id)
                        ->where('store_id', $store_id)
                        ->count();
                    // dd($product);
                    return $product > 0;
                } else {
                    if ($zones_serviceable_cities) {
                        $product = DB::table('seller_store')->where('store_id', $store_id)->where('seller_id', $seller_id)
                            ->where(function ($query) use ($zones_serviceable_cities) {
                                $query->where(function ($subquery) use ($zones_serviceable_cities) {
                                    $subquery->where("seller_store.deliverable_type", '2')
                                        ->whereIn("seller_store.deliverable_zones", $zones_serviceable_cities);
                                });
                            })
                            ->count();
                        // dd($product);
                        return $product > 0;
                    }
                }
                // return false;
            }
        }
    } else {
        return false;
    }
}
function getSellerDeliverableZones($seller_id, $store_id)
{
    $seller_deliverable_data = fetchDetails('seller_store', ['seller_id' => $seller_id, 'store_id' => $store_id], 'deliverable_zones');
    // dd($store_id);
    return isset($seller_deliverable_data) && !empty($seller_deliverable_data) ? explode(',', $seller_deliverable_data[0]->deliverable_zones) : [];
}
function getDeliverableZones($productTypeTable, $productId)
{
    $deliverable_zones = fetchDetails($productTypeTable, ['id' => $productId], 'deliverable_zones');
    return isset($deliverable_zones) && !empty($deliverable_zones) ? explode(',', $deliverable_zones[0]->deliverable_zones) : [];
}
// Helper function to fetch serviceable zone IDs for a specific zipcode
function getZonesServiceableByZipcode($deliverableZones, $zipcodeId)
{
    return Zone::whereIn('id', $deliverableZones)
        ->where('status', 1)
        ->get(['id', 'serviceable_zipcode_ids'])
        ->filter(function ($zone) use ($zipcodeId) {
            return in_array($zipcodeId, explode(',', $zone->serviceable_zipcode_ids));
        })
        ->pluck('id')
        ->all();
}

function getZonesServiceableByCity($deliverableZones, $cityId)
{
    // $query = Zone::whereIn('id', $deliverableZones)
    //     ->where('status', 1)
    //     ->select(['id', 'serviceable_city_ids']);

    // // dd($query->toSql(), $query->getBindings());
    // $test = $query->get()
    //     ->filter(function ($zone) use ($cityId) {
    //         return in_array($cityId, explode(',', $zone->serviceable_city_ids));
    //     })
    //     ->pluck('id')
    //     ->all();
    // dd($test);
    return Zone::whereIn('id', $deliverableZones)
        ->where('status', 1)
        ->get(['id', 'serviceable_city_ids'])
        ->filter(function ($zone) use ($cityId) {
            return in_array($cityId, explode(',', $zone->serviceable_city_ids));
        })
        ->pluck('id')
        ->all();
}
function getVariantsValuesById($id)
{
    $varaint_values = DB::table('product_variants as pv')
        ->select(DB::raw('pv.*, pv.product_id, group_concat(av.id separator ", ") as variant_ids,
                        group_concat(a.name separator ", ") as attr_name,
                        group_concat(av.value separator ", ") as variant_values'))
        ->join('attribute_values as av', function ($join) {
            $join->on(DB::raw('FIND_IN_SET(av.id, pv.attribute_value_ids) > 0'), '>', DB::raw('0'));
        }, null, null, 'left')
        ->join('attributes as a', 'a.id', '=', 'av.attribute_id', 'left')
        ->where('pv.id', $id)
        ->groupBy('pv.id')
        ->orderBy('pv.id')
        ->get()
        ->toArray();

    if (!empty($varaint_values)) {
        foreach ($varaint_values as &$variant) {
            $variant->images = isset($variant->images) && !empty($variant->images) ? json_decode($variant->images) : [];
            $variant = array_map(function ($value) {
                return $value === NULL ? "" : $value;
            }, (array) $variant);
        }
    }

    return $varaint_values;
}

function placeOrder($data, $for_web = '', $language_code = '')
{
    // dd($data);
    $store_id = isset($data['store_id']) && !empty($data['store_id']) ? $data['store_id'] : '';


    $product_variant_id = explode(',', $data['product_variant_id']);

    $cart_product_type = explode(',', $data['cart_product_type']);

    $quantity = explode(',', $data['quantity']);
    // dd($quantity);
    $check_current_stock_status = validateStock($product_variant_id, $quantity, $cart_product_type);

    if (isset($check_current_stock_status['error']) && $check_current_stock_status['error'] == true) {
        return json_encode($check_current_stock_status);
    }
    $total = 0;
    $promo_code_discount = 0;



    //fetch details from product_variants table for regular product

    $product_variant = Product_variants::select(
        'product_variants.*',
        'c.product_type as cart_product_type',
        DB::raw('(SELECT GROUP_CONCAT(tax.percentage) FROM taxes as tax WHERE FIND_IN_SET(tax.id, p.tax)) as tax_percentage'),
        DB::raw('(SELECT GROUP_CONCAT(tax.percentage) FROM taxes as tax WHERE FIND_IN_SET(tax.id, p.tax)) as tax_ids'),
        DB::raw('(SELECT GROUP_CONCAT(tax_name.title) FROM taxes as tax_name WHERE FIND_IN_SET(tax_name.id, p.tax)) as tax_name'),
        'p.seller_id',
        'p.name as product_name',
        'p.type as product_type',
        'p.is_prices_inclusive_tax',
        'p.download_link'
    )
        ->join('products as p', 'product_variants.product_id', '=', 'p.id')
        ->leftJoin('taxes as tax_id', function ($join) {
            $join->on(DB::raw('FIND_IN_SET(tax_id.id, p.tax)'), '>', DB::raw('0'));
        })
        ->leftJoin('cart as c', 'c.product_variant_id', '=', 'product_variants.id')
        ->whereIn('product_variants.id', $product_variant_id)
        ->where('c.product_type', 'regular')
        ->orderByRaw('FIELD(product_variants.id, ' . $data['product_variant_id'] . ')')
        ->get();



    //fetch details from combo_products table for combo product

    $combo_product_variant = ComboProduct::select(
        'combo_products.*',
        'c.product_type as cart_product_type',
        DB::raw('(SELECT GROUP_CONCAT(c_tax.percentage) FROM taxes as c_tax WHERE FIND_IN_SET(c_tax.id, combo_products.tax)) as tax_percentage'),
        DB::raw('(SELECT GROUP_CONCAT(c_tax.percentage) FROM taxes as c_tax WHERE FIND_IN_SET(c_tax.id, combo_products.tax)) as tax_ids'),
        DB::raw('(SELECT GROUP_CONCAT(c_tax_title.title) FROM taxes as c_tax_title WHERE FIND_IN_SET(c_tax_title.id, combo_products.tax)) as tax_name'),
        'combo_products.seller_id',
        'combo_products.title as product_name',
        'combo_products.product_type',
        'combo_products.is_prices_inclusive_tax',
        'combo_products.download_link'
    )
        ->leftJoin('taxes as c_tax', function ($join) {
            $join->on(DB::raw('FIND_IN_SET(c_tax.id, combo_products.tax)'), '>', DB::raw('0'));
        })
        ->leftJoin('cart as c', 'c.product_variant_id', '=', 'combo_products.id')
        ->whereIn('combo_products.id', $product_variant_id)
        ->where('c.product_type', 'combo')
        ->orderByRaw('FIELD(combo_products.id, ' . $data['product_variant_id'] . ')')
        ->get();



    //merge both collection
    $product_variant = $product_variant->merge($combo_product_variant);
    // dd($product_variant);
    if (!empty($product_variant)) {

        $system_settings = getSettings('system_settings', true, true);
        $system_settings = json_decode($system_settings, true);

        $seller_ids = $product_variant->pluck('seller_id')->unique()->values()->all();

        if ($system_settings['single_seller_order_system'] == '1') {
            if (isset($seller_ids) && count($seller_ids) > 1) {
                $response['error'] = true;
                $response['message'] = 'Only one seller products are allow in one order.';
                return $response;
            }
        }

        $delivery_charge = isset($data['delivery_charge']) && !empty($data['delivery_charge']) ? $data['delivery_charge'] : 0;
        $discount = isset($data['discount']) && !empty($data['discount']) ? $data['discount'] : 0;
        $gross_total = 0;
        $cart_data = [];


        for ($i = 0; $i < count($product_variant); $i++) {

            $pv_price[$i] = ($product_variant[$i]['special_price'] > 0 && $product_variant[$i]['special_price'] != null) ? $product_variant[$i]['special_price'] : $product_variant[$i]['price'];
            $tax_ids[$i] = (isset($product_variant[$i]['tax_ids']) && $product_variant[$i]['tax_ids'] != null) ? $product_variant[$i]['tax_ids'] : '0';
            $tax_percentage[$i] = (isset($product_variant[$i]['tax_percentage']) && $product_variant[$i]['tax_percentage'] != null) ? $product_variant[$i]['tax_percentage'] : '0';
            $tax_percntg[$i] = explode(',', $tax_percentage[$i]);
            $total_tax = array_sum($tax_percntg[$i]);
            if ((isset($product_variant[$i]['is_prices_inclusive_tax']) && $product_variant[$i]['is_prices_inclusive_tax'] == 0)) {
                $tax_amount[$i] = $pv_price[$i] * ($total_tax / 100);
                $pv_price[$i] = $pv_price[$i] + $tax_amount[$i];
            }

            $subtotal[$i] = ($pv_price[$i]) * $quantity[$i];
            $pro_name[$i] = $product_variant[$i]['product_name'];

            if ($product_variant[$i]['cart_product_type'] == 'regular') {
                $variant_info = getVariantsValuesById($product_variant[$i]['id']);
            } else {
                $variant_info = [];
            }

            $product_variant[$i]['variant_name'] = (isset($variant_info[0]['variant_values']) && !empty($variant_info[0]['variant_values'])) ? $variant_info[0]['variant_values'] : "";


            if ($tax_percentage[$i] != NUll && $tax_percentage[$i] > 0) {
                $tax_amount[$i] = round($subtotal[$i] * $total_tax / 100, 2);
            } else {
                $tax_amount[$i] = 0;
                $tax_percentage[$i] = 0;
            }
            $gross_total += $subtotal[$i];
            $total += $subtotal[$i];
            $total = round($total, 2);
            $gross_total = round($gross_total, 2);
            if ($product_variant[$i]->cart_product_type == 'regular') {
                $product_name = getDynamicTranslation(
                    'products',
                    'name',
                    $product_variant[$i]->product_id,
                    $language_code
                );
            } else {
                $product_name = getDynamicTranslation(
                    'combo_products',
                    'title',
                    $product_variant[$i]->product_id,
                    $language_code
                );
            }
            array_push(
                $cart_data,
                array(
                    'name' => $product_name,
                    'tax_amount' => $tax_amount[$i],
                    'qty' => $quantity[$i],
                    'sub_total' => $subtotal[$i],
                )
            );
        }


        $settings = getSettings('system_settings', true, true);
        $settings = json_decode($settings, true);
        $app_name = isset($settings['app_name']) && !empty($settings['app_name']) ? $settings['app_name'] : '';

        $currency = isset($settings['currency']) && !empty($settings['currency']) ? $settings['currency'] : '';
        if (isset($settings['minimum_cart_amount']) && !empty($settings['minimum_cart_amount'])) {
            $carttotal = $total + $delivery_charge;
            if ($carttotal < $settings['minimum_cart_amount']) {
                $response = [
                    'error' => true,
                    'message' => 'Total amount should be greater or equal to ' . $currency . $settings['minimum_cart_amount'] . ' total is ' . $currency . $carttotal,
                    'code' => 102,
                ];
                return $response;
            }
        }


        // add promocode calculation here
        if (isset($data['promo_code_id']) && !empty($data['promo_code_id'])) {
            $data['promo_code'] = fetchDetails('promo_codes', ['id' => $data['promo_code_id']], 'promo_code')[0]->promo_code;
            // dd($total);
            $promo_code = validatePromoCode($data['promo_code_id'], $data['user_id'], $total, 1);
            $promo_code = $promo_code->original;
            if ($promo_code['error'] == false) {

                if ($promo_code['data'][0]->discount_type == 'percentage') {
                    $promo_code_discount = (isset($promo_code['data'][0]->is_cashback) && $promo_code['data'][0]->is_cashback == 0) ? floatval($total * $promo_code['data'][0]->discount / 100) : 0;
                } else {
                    $promo_code_discount = (isset($promo_code['data'][0]->is_cashback) && $promo_code['data'][0]->is_cashback == 0) ? $promo_code['data'][0]->discount : 0;
                }
                if ($promo_code_discount <= $promo_code['data'][0]->max_discount_amount) {
                    $total = (isset($promo_code['data'][0]->is_cashback) && $promo_code['data'][0]->is_cashback == 0) ? floatval($total) - $promo_code_discount : floatval($total);
                } else {
                    $total = (isset($promo_code['data'][0]->is_cashback) && $promo_code['data'][0]->is_cashback == 0) ? floatval($total) - $promo_code['data'][0]->max_discount_amount : floatval($total);
                    $promo_code_discount = $promo_code['data'][0]->max_discount_amount;
                }
            } else {
                return $promo_code;
            }
        }
        // ---------------------------------------------------------

        //add create parcel seller wise code here

        $parcels = array();
        for ($i = 0; $i < count($product_variant_id); $i++) {
            // dd($product_variant[$i]);
            $product_variant[$i]['qty'] = $quantity[$i];
        }

        foreach ($product_variant as $product) {

            $prctg = (isset($product['tax_percentage']) && $product['tax_percentage'] != null) ? $product['tax_percentage'] : '0';
            if ((isset($product['is_prices_inclusive_tax']) && $product['is_prices_inclusive_tax'] == 0)) {
                $tax_percentage = explode(',', $prctg);
                $total_tax = array_sum($tax_percentage);

                $price_tax_amount = $product['price'] * ($total_tax / 100);
                $special_price_tax_amount = $product['special_price'] * ($total_tax / 100);
            } else {
                $price_tax_amount = 0;
                $special_price_tax_amount = 0;
            }

            if (floatval($product['special_price']) > 0) {
                $product['total'] = floatval($product['special_price'] + $special_price_tax_amount) * $product['qty'];
            } else {
                $product['total'] = floatval($product['price'] + $price_tax_amount) * $product['qty'];
            }
            if (isset($parcels[$product['seller_id']]['variant_id']) && !empty($product['id'])) {
                $parcels[$product['seller_id']]['variant_id'] .= $product['id'] . ',';
            } elseif (!empty($product['id'])) {
                $parcels[$product['seller_id']]['variant_id'] = $product['id'] . ',';
            }
            if (isset($parcels[$product['seller_id']]['total']) && !empty($product['total'])) {
                $parcels[$product['seller_id']]['total'] += $product['total'];
            } elseif (!empty($product['total'])) {
                $parcels[$product['seller_id']]['total'] = $product['total'];
            }
        }
        $parcel_sub_total = 0.0;
        // dd($parcels);
        foreach ($parcels as $seller_id => $parcel) {
            $parcel_sub_total += $parcel['total'];
        }
        // ---------------------------------------------------------

        // $final_total = $total + intval($delivery_charge) - $discount;
        // $final_total = $total + intval($delivery_charge) - $promo_code_discount;
        $final_total = $total + intval($delivery_charge);
        $final_total = round($final_total, 2);

        $total_payable = $final_total;
        // dd($final_total);
        if ($data['is_wallet_used'] == '1' && $data['wallet_balance_used'] <= $final_total) {

            $wallet_balance = updateWalletBalance('debit', $data['user_id'], $data['wallet_balance_used'], "Used against Order Placement");
            if ($wallet_balance['error'] == false) {
                $total_payable -= $data['wallet_balance_used'];
                $Wallet_used = true;
            } else {
                $response['error'] = true;
                $response['message'] = $wallet_balance['error_message'];
                return $response;
            }
        } else {
            if ($data['is_wallet_used'] == 1) {
                $response['error'] = true;
                $response['message'] = 'Wallet Balance should not exceed the total amount';
                return $response;
            }
        }


        // $status = (isset($data['payment_method'])) && (strtolower($data['payment_method']) == 'cod' || $data['payment_method'] == 'paystack' || $data['payment_method'] == 'stripe' || $data['payment_method'] == 'razorpay') ? 'received' : 'awaiting';
        $status = ((isset($data['status'])) && !empty($data['status'])) ? $data['status'] : 'awaiting';
        if (isset($data['wallet_balance_used']) && $data['wallet_balance_used'] == $final_total) {
            $status = 'received';
        }
        if ((isset($data['payment_method'])) && (strtolower($data['payment_method']) == 'cod')) {
            $status = 'received';
        }
        if ($data['is_wallet_used'] == '1') {
            $data['payment_method'] = 'wallet';
        }
        // dd($status);
        $order_payment_currency_data = fetchDetails('currencies', ['code' => $data['order_payment_currency_code']], ['id', 'exchange_rate']);
        $base_currency = getDefaultCurrency()->code;
        $order_data = [
            'user_id' => $data['user_id'],
            'mobile' => (isset($data['mobile']) && !empty($data['mobile']) && $data['mobile'] != '' && $data['mobile'] != 'NULL') ? $data['mobile'] : '',
            'total' => $gross_total,
            'promo_discount' => (isset($promo_code_discount) && $promo_code_discount != NULL) ? $promo_code_discount : '0',
            'total_payable' => $total_payable,
            'delivery_charge' => intval($delivery_charge),
            'is_delivery_charge_returnable' => isset($data['is_delivery_charge_returnable']) ? $data['is_delivery_charge_returnable'] : 0,
            'wallet_balance' => (isset($Wallet_used) && $Wallet_used == true) ? $data['wallet_balance_used'] : '0',
            'final_total' => $final_total,
            'discount' => $discount,
            'payment_method' => $data['payment_method'] ?? '',
            'promo_code_id' => (isset($data['promo_code_id'])) ? $data['promo_code_id'] : ' ',
            'email' => isset($data['email']) ? $data['email'] : ' ',
            'is_pos_order' => isset($data['is_pos_order']) ? $data['is_pos_order'] : 0,
            'is_shiprocket_order' => isset($data['delivery_type']) && !empty($data['delivery_type']) && $data['delivery_type'] == 'standard_shipping' ? 1 : 0,
            'order_payment_currency_id' => $order_payment_currency_data[0]->id ?? '',
            'order_payment_currency_code' => $data['order_payment_currency_code'] ?? "",
            'order_payment_currency_conversion_rate' => $order_payment_currency_data[0]->exchange_rate ?? '',
            'base_currency_code' => $base_currency,
        ];

        if (isset($data['address_id']) && !empty($data['address_id'])) {
            $order_data['address_id'] = (isset($data['address_id']) ? $data['address_id'] : '');
        }

        if (isset($data['delivery_date']) && !empty($data['delivery_date']) && !empty($data['delivery_time']) && isset($data['delivery_time'])) {
            $order_data['delivery_date'] = date('Y-m-d', strtotime($data['delivery_date']));
            $order_data['delivery_time'] = $data['delivery_time'];
        }
        $addressController = app(AddressController::class);
        if (isset($data['address_id']) && !empty($data['address_id'])) {

            $address_data = $addressController->getAddress(null, $data['address_id'], true);

            if (!empty($address_data)) {
                $order_data['latitude'] = $address_data[0]->latitude;
                $order_data['longitude'] = $address_data[0]->longitude;
                $order_data['address'] = (!empty($address_data[0]->address) && $address_data[0]->address != 'NULL') ? $address_data[0]->address . ', ' : '';
                $order_data['address'] .= (!empty($address_data[0]->landmark) && $address_data[0]->landmark != 'NULL') ? $address_data[0]->landmark . ', ' : '';
                $order_data['address'] .= (!empty($address_data[0]->area) && $address_data[0]->area != 'NULL') ? $address_data[0]->area . ', ' : '';
                $order_data['address'] .= (!empty($address_data[0]->city) && $address_data[0]->city != 'NULL') ? $address_data[0]->city . ', ' : '';
                $order_data['address'] .= (!empty($address_data[0]->state) && $address_data[0]->state != 'NULL') ? $address_data[0]->state . ', ' : '';
                $order_data['address'] .= (!empty($address_data[0]->country) && $address_data[0]->country != 'NULL') ? $address_data[0]->country . ', ' : '';
                $order_data['address'] .= (!empty($address_data[0]->pincode) && $address_data[0]->pincode != 'NULL') ? $address_data[0]->pincode : '';
            }
        } else {
            $order_data['address'] = "";
        }

        if (!empty($data['latitude']) && !empty($data['longitude'])) {
            $order_data['latitude'] = $data['latitude'];
            $order_data['longitude'] = $data['longitude'];
        }
        $order_data['notes'] = isset($data['order_note']) ? $data['order_note'] : '';
        $order_data['store_id'] = $store_id;

        $order = Order::forceCreate($order_data);

        $order_id = $order->id;

        for ($i = 0; $i < count($product_variant); $i++) {
            // dd($product_variant[$i]);
            if ($product_variant[$i]->cart_product_type == 'regular') {
                $product_name = getDynamicTranslation(
                    'products',
                    'name',
                    $product_variant[$i]->product_id,
                    $language_code
                );
            } else {
                $product_name = getDynamicTranslation(
                    'combo_products',
                    'title',
                    $product_variant[$i]->product_id,
                    $language_code
                );
            }
            $product_variant_data[$i] = [
                'user_id' => $data['user_id'],
                'order_id' => $order_id,
                'seller_id' => $product_variant[$i]['seller_id'],
                'product_name' =>  $product_name,
                // 'product_name' =>  $product_variant[$i]['product_name'],
                'variant_name' => $product_variant[$i]['variant_name'],
                'product_variant_id' => $product_variant[$i]['id'],
                'quantity' => $quantity[$i],
                'price' => $pv_price[$i],
                'tax_percent' => $total_tax,
                'tax_ids' => $tax_ids[$i],
                'tax_amount' => $tax_amount[$i],
                'sub_total' => $subtotal[$i],
                'status' => json_encode(array(array($status, date("d-m-Y h:i:sa")))),
                'active_status' => $status,
                'otp' => 0,
                'store_id' => $store_id,
                'order_type' => $product_variant[$i]['cart_product_type'] . "_order",
                'attachment' => $data['attachment_path'][$product_variant[$i]['id']] ?? "",
            ];
            // dd($product_variant_data[$i]);
            $order_items = OrderItems::forceCreate($product_variant_data[$i]);

            $order_item_id = $order_items->id;

            if (isset($product_variant[$i]['download_link']) && !empty($product_variant[$i]['download_link'])) {
                $hash_link = $product_variant[$i]['download_link'] . '?' . $order_item_id;
                $hash_link_data = ['hash_link' => $hash_link];
                OrderItems::where('id', $order_item_id)->update($hash_link_data);
            }
        }
        // add here  order_charges_parcel and insert in table
        $discount_percentage = 0.00;

        foreach ($parcels as $seller_id => $parcel) {
            $parcel['delivery_charge'] = 0;

            $discount_percentage = ($parcel['total'] * 100) / $parcel_sub_total;
            $seller_promocode_discount = ($promo_code_discount * $discount_percentage) / 100;
            $seller_delivery_charge = ($delivery_charge * $discount_percentage) / 100;
            $otp = mt_rand(100000, 999999);
            $order_item_ids = '';
            $varient_ids = explode(',', trim($parcel['variant_id'], ','));
            $parcel_total = $parcel['total'] + intval($parcel['delivery_charge']) - $seller_promocode_discount;
            $parcel_total = round($parcel_total, 2);
            foreach ($varient_ids as $ids) {
                $order_item_ids .= fetchDetails('order_items', ['seller_id' => $seller_id, 'product_variant_id' => $ids, 'order_id' => $order_id], 'id')[0]->id . ',';
            }
            $order_item_id = explode(',', trim($order_item_ids, ','));
            foreach ($order_item_id as $ids) {
                updateDetails(['otp' => $otp], ['id' => $ids], 'order_items');
            }

            $order_parcels = [
                'seller_id' => $seller_id,
                'product_variant_ids' => trim($parcel['variant_id'], ','),
                'order_id' => $order_id,
                'order_item_ids' => trim($order_item_ids, ','),
                'delivery_charge' => round($seller_delivery_charge, 2),
                'promo_code_id' => $data['promo_code_id'] ?? '',
                'promo_discount' => round($seller_promocode_discount, 2),
                'sub_total' => $parcel['total'],
                'total' => $parcel_total,
                'otp' => ($system_settings['order_delivery_otp_system'] == '1') ? $otp : 0,
            ];


            $order_charges = OrderCharges::forceCreate($order_parcels);
        }

        $product_variant_ids = explode(',', $data['product_variant_id']);

        $qtns = explode(',', $data['quantity'] ?? '');

        for ($i = 0; $i < count($product_variant_ids); $i++) {

            if ($cart_product_type[$i] == 'regular') {
                updateStock($product_variant_ids[$i], $qtns[$i], '');
            } else {
                updateComboStock($product_variant_ids[$i], $qtns[$i], '');
            }
        }



        $overall_total = array(
            'total_amount' => array_sum($subtotal),
            'delivery_charge' => $delivery_charge,
            'discount' => $discount,
            'tax_amount' => array_sum($tax_amount),
            // 'tax_percentage' => array_sum($tax_percentage),
            'tax_percentage' => array_sum(array_map('floatval', $tax_percentage)),
            'discount' => $order_data['promo_discount'],
            'wallet' => $order_data['wallet_balance'],
            'final_total' => $order_data['final_total'],
            'total_payable' => $order_data['total_payable'],
            'address' => (isset($order_data['address'])) ? $order_data['address'] : '',
            'payment_method' => $data['payment_method'] ?? ''
        );

        // add send notification,custom notificationa nd send mail code here

        $user_res = fetchDetails('users', ['id' => $data['user_id']], ['username', 'fcm_id', 'email']);
        $custom_notification = fetchDetails('custom_messages', ['type' => "place_order"], '*');
        $hashtag_customer_name = '< customer_name >';
        $hashtag_order_id = '< order_item_id >';
        $hashtag_application_name = '< application_name >';
        $string = isset($custom_notification) && !empty($custom_notification) ? json_encode($custom_notification[0]->message, JSON_UNESCAPED_UNICODE) : '';
        $hashtag = html_entity_decode($string);
        $notification_data = str_replace(array($hashtag_customer_name, $hashtag_order_id, $hashtag_application_name), array($user_res[0]->username, $order_id, $app_name), $hashtag);
        $message = outputEscaping(trim($notification_data, '"'));
        $title = "New order placed ID # " . $order_id;
        $customer_msg = (!empty($custom_notification)) ? $message : 'New order received for  ' . $app_name . ' please process it.';
        $fcm_ids = array();
        $seller_fcm_ids = array();
        $order_id = $order_id;
        foreach ($parcels as $seller_id => $parcel) {
            $seller_id = Seller::where('id', $seller_id)->value('user_id');
            $seller_res = fetchDetails('users', ['id' => $seller_id], ['username', 'fcm_id']);
            $results = UserFcm::join('users', 'user_fcm.user_id', '=', 'users.id')
                ->where('user_fcm.user_id', $seller_id)
                ->where('users.is_notification_on', 1)
                ->select('user_fcm.fcm_id')
                ->get();
            foreach ($results as $result) {
                if (is_object($result)) {
                    $seller_fcm_ids[] = $result->fcm_id;
                }
            }
        }
        $notification_store_id = $order->store_id;
        // dd($store_id);
        $fcmMsg = array(
            'title' => "$title",
            'body' => "$customer_msg",
            'type' => "order",
            'order_id' => "$order_id",
            'store_id' => "$store_id",
        );
        // dd($status);
        // dd($status !== 'awaiting');
        $sellerRegistrationIDs_chunks = array_chunk($seller_fcm_ids, 1000);
        if ($status !== 'awaiting' && $status !== 'Awaiting') {
            sendNotification('', $sellerRegistrationIDs_chunks, $fcmMsg);
        }



        $user_results = UserFcm::join('users', 'user_fcm.user_id', '=', 'users.id')
            ->where('user_fcm.user_id', $data['user_id'])
            ->where('users.is_notification_on', 1)
            ->select('user_fcm.fcm_id')
            ->get();
        foreach ($user_results as $result) {
            if (is_object($result)) {
                $fcm_ids[] = $result->fcm_id;
            }
        }

        $fcmMsg = array(
            'title' => "$title",
            'body' => "$customer_msg",
            'type' => "order",
            'order_id' => "$order_id",
            'store_id' => "$store_id",
        );
        $registrationIDs_chunks = array_chunk($fcm_ids, 1000);
        if ($status !== 'awaiting' && $status !== 'Awaiting') {
            sendNotification('', $registrationIDs_chunks, $fcmMsg);
            $userEmail = $user_res[0]->email;
            $invoiceUrl = url("/admin/orders/generat_invoice_PDF/$order_id");

            // Email Subject
            $subject = $app_name . ": Invoice for Your Order #$order_id - Thank You for Shopping with Us!";
            $userName = $user_res[0]->username;
            // Email Message
            $messageContent = "
            <p>Dear <strong>$userName</strong>,</p>
            <p>Thank you for your order with us! We appreciate your trust in our service.</p>
            <p>Your order has been successfully placed, and your invoice is ready for download.</p>
            <p><strong>Invoice Details:</strong></p>
            <ul>
                <li><strong>Order ID:</strong> #$order_id</li>
                <li><strong>Date:</strong> " . now()->format('d M, Y') . "</li>
            </ul>
            <p>You can download your invoice by clicking the link below:</p>
            <p><a href='$invoiceUrl' style='background:#007bff;color:white;padding:10px 15px;border-radius:5px;text-decoration:none;'>Download Invoice</a></p>
            <br>
            <p>If you have any questions, feel free to contact our support team.</p>
            <p>Best regards,</p>
            <p><strong>$app_name</strong></p>
        ";

            // Send email
            Mail::send([], [], function ($message) use ($userEmail, $subject, $messageContent) {
                $message->to($userEmail)
                    ->subject($subject)
                    ->html($messageContent);
            });
        }

        removeFromCart($data);
        foreach ($product_variant_data as &$order_item_data) {
            $order_item_data['attachment'] = asset('/storage/' . $order_item_data['attachment']);
        }
        // dd($product_variant_data);
        $user_balance = fetchDetails('users', ['id' => $data['user_id']], 'balance');
        $response = [
            'error' => false,
            'message' => 'Order Placed Successfully',
            'order_id' => $order_id,
            'final_total' => ($data['is_wallet_used'] == '1') ? $final_total -= $data['wallet_balance_used'] : $final_total,
            'total_payable' => $total_payable,
            'order_item_data' => $product_variant_data,
            'balance' => $user_balance,
        ];
        if ($for_web == 1) {
            return $response;
        } else {
            return response()->json($response);
        }
    } else {
        $user_balance = fetchDetails('users', ['id' => $data['user_id']], 'balance');
        $response = [
            'error' => true,
            'message' => "Product(s) Not Found!",
            'balance' => $user_balance,
        ];

        return response()->json($response);
    }
}
function generateInvoicePDF($id)
{
    $res = getOrderDetails(['o.id' => $id]);
    $seller_ids = array_values(array_unique(array_column($res, "seller_id")));
    $seller_user_ids = [];
    $promo_code = [];
    $items = [];

    foreach ($seller_ids as $id) {
        $seller_user_ids[] = Seller::where('id', $id)->value('user_id');
    }

    if (!empty($res)) {

        if (!empty($res[0]->promo_code_id)) {
            $promo_code = fetchDetails('promo_codes', ['id' => trim($res[0]->promo_code_id)]);
        }

        foreach ($res as $row) {
            $temp['product_id'] = $row->product_id;
            $temp['seller_id'] = $row->seller_id;
            $temp['product_variant_id'] = $row->product_variant_id;
            $temp['pname'] = $row->pname;
            $temp['quantity'] = $row->quantity;
            $temp['discounted_price'] = $row->discounted_price;
            $temp['tax_percent'] = $row->tax_percent;
            $temp['tax_amount'] = $row->tax_amount;
            $temp['price'] = $row->price;
            $temp['product_special_price'] = $row->product_special_price;
            $temp['product_price'] = $row->product_price;
            $temp['delivery_boy'] = $row->delivery_boy;
            $temp['mobile_number'] = $row->mobile_number;
            $temp['active_status'] = $row->oi_active_status;
            $temp['hsn_code'] = $row->hsn_code ?? '';
            $temp['is_prices_inclusive_tax'] = $row->is_prices_inclusive_tax;
            array_push($items, $temp);
        }
    }

    $item1 = InvoiceItem::make('Service 1')->pricePerUnit(2);
    $sellers = [
        'seller_ids' => $seller_ids,
        'seller_user_ids' => $seller_user_ids,
        'mobile_number' => $res[0]->mobile_number,
    ];

    $customer = new Buyer([
        'name' => $res[0]->uname,
        'custom_fields' => [
            'address' => $res[0]->address,
            'order_id' => $res[0]->id,
            'date_added' => $res[0]->created_at,
            'store_id' => $res[0]->store_id,
            'payment_method' => $res[0]->payment_method,
            'discount' => $res[0]->discount,
            'promo_code' => $promo_code[0]->promo_code ?? '',
            'promo_code_discount' => $promo_code[0]->discount ?? '',
            'promo_code_discount_type' => $promo_code[0]->discount_type ?? '',
        ],
    ]);

    $client = new Party([
        'custom_fields' => $sellers,
    ]);

    $invoice = Invoice::make()
        ->buyer($customer)
        ->seller($client)
        ->setCustomData($items)
        ->addItem($item1)
        ->template('invoice');

    return $invoice->stream();
}
function updateStock($product_variant_ids, $qtns, $type = '')
{

    $ids = implode(',', (array) $product_variant_ids);

    $productVariants = Product_variants::select('p.*', 'product_variants.*', 'p.id as p_id', 'product_variants.id as pv_id', 'p.stock as p_stock', 'product_variants.stock as pv_stock')
        ->whereIn('product_variants.id', is_array($product_variant_ids) ? $product_variant_ids : [$product_variant_ids])
        ->join('products as p', 'product_variants.product_id', '=', 'p.id')
        ->orderByRaw('FIELD(product_variants.id,' . $ids . ')')
        ->get();

    foreach ($productVariants as $i => $res) {

        if ($res->stock_type !== null || $res->stock_type !== "") {

            if ($res->stock_type == 0) {

                if ($type == 'plus') {

                    if ($res->p_stock !== null) {

                        $stock = ($res->p_stock) + intval(is_array($qtns) ? $qtns[$i] : $qtns);

                        Product::where('id', $res->product_id)->update(['stock' => $stock]);

                        if ($stock > 0) {
                            Product::where('id', $res->product_id)->update(['availability' => '1']);
                        }
                    }
                } else {

                    if ($res->p_stock !== null && $res->p_stock > 0) {
                        $stock = intval($res->p_stock) - intval(is_array($qtns) ? $qtns[$i] : $qtns);
                        Product::where('id', $res->product_id)->update(['stock' => $stock]);
                        if ($stock == 0) {
                            Product::where('id', $res->product_id)->update(['availability' => '0']);
                        }
                    }
                }
            }


            if ($res->stock_type == 1) {
                if ($type == 'plus') {

                    if ($res->pv_stock !== null) {

                        $stock = intval($res->pv_stock) + intval(is_array($qtns) ? $qtns[$i] : $qtns);

                        Product::where('id', $res->p_id)->update(['stock' => $stock]);
                        Product_variants::where('product_id', $res->product_id)->update(['stock' => $stock]);
                        if ($stock > 0) {
                            Product_variants::where('product_id', $res->product_id)->update(['availability' => '1']);
                        }
                    }
                } else {
                    if ($res->pv_stock !== null && $res->pv_stock > 0) {
                        $stock = intval($res->pv_stock) - intval(is_array($qtns) ? $qtns[$i] : $qtns);
                        Product::where('id', $res->p_id)->update(['stock' => $stock]);
                        Product_variants::where('product_id', $res->product_id)->update(['stock' => $stock]);
                        if ($stock == 0) {
                            Product_variants::where('product_id', $res->product_id)->update(['availability' => '0']);
                        }
                    }
                }
            }

            // Case 3 : Variant level (variable product)
            if ($res->stock_type == 2) {
                if ($type == 'plus') {
                    if ($res->pv_stock !== null) {
                        $stock = intval($res->pv_stock) + intval(is_array($qtns) ? $qtns[$i] : $qtns);
                        Product_variants::where('id', $res->id)->update(['stock' => $stock]);
                        if ($stock > 0) {
                            Product_variants::where('id', $res->id)->update(['availability' => '1']);
                        }
                    }
                } else {
                    if ($res->pv_stock !== null && $res->pv_stock > 0) {
                        $stock = intval($res->pv_stock) - intval(is_array($qtns) ? $qtns[$i] : $qtns);
                        Product_variants::where('id', $res->id)->update(['stock' => $stock]);
                        if ($stock == 0) {
                            Product_variants::where('id', $res->id)->update(['availability' => '0']);
                        }
                    }
                }
            }
        }
    }
}
function updateComboStock($id, $quantity, $type = '')
{
    if ($type == 'add' || $type == 'subtract') {

        // Find the combo product by its ID
        $comboProduct = ComboProduct::find($id);

        // If product not found, return 404 response
        if (!$comboProduct) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        if ($type == 'add') {
            // Add the quantity to the stock
            $comboProduct->stock += $quantity;

            // Update availability if stock is greater than 0
            if ($comboProduct->stock > 0) {
                $comboProduct->availability = 1;
            }
        } elseif ($type == 'subtract') {
            // Subtract the quantity from the stock
            $comboProduct->stock -= $quantity;

            // Ensure stock doesn't go below 0
            if ($comboProduct->stock < 0) {
                return response()->json(['message' => 'Stock cannot go negative'], 400);
            }

            // Update availability if stock is 0
            if ($comboProduct->stock == 0) {
                $comboProduct->availability = 0;
            }
        }

        // Save the updated combo product
        $saved = $comboProduct->save();

        return $saved;
    }

    return response()->json(['message' => 'Invalid operation type'], 400);
}




function updateWalletBalance($operation, $user_id, $amount, $message = "Balance Debited", $order_item_id = "", $is_refund = 0, $transaction_type = 'wallet')
{
    $user = User::find($user_id);

    if (!$user) {
        $response['error'] = true;
        $response['error_message'] = "User does not exist";
        $response['data'] = [];
        return $response;
    }

    if ($operation == 'debit' && $amount > $user->balance) {
        $response['error'] = true;
        $response['error_message'] = "Debited amount can't exceed the user balance!";
        $response['data'] = [];
        return $response;
    }

    if ($amount == 0) {
        $response['error'] = true;
        $response['error_message'] = "Amount can't be zero!";
        $response['data'] = [];
        return $response;
    }

    if ($user->balance >= 0) {
        $data = [
            'transaction_type' => $transaction_type,
            'user_id' => $user_id,
            'type' => $operation,
            'amount' => $amount,
            'message' => $message,
            'order_item_id' => $order_item_id,
            'is_refund' => $is_refund,
        ];

        $payment_data = Transaction::where('order_item_id', $order_item_id)->pluck('type')->first();

        if ($operation == 'debit') {
            $data['message'] = $message ?: 'Balance Debited';
            $data['type'] = 'debit';
            $data['status'] = 'success';
            $user->balance -= $amount;
        } else if ($operation == 'credit') {
            $data['message'] = $message ?? 'Balance Credited';
            $data['type'] = 'credit';
            $data['status'] = 'success';
            $data['order_id'] = $order_item_id;
            if ($payment_data != 'razorpay') {
                $user->balance += $amount;
            }
        } else {
            $data['message'] = $message ?: 'Balance refunded';
            $data['type'] = 'refund';
            $data['status'] = 'success';
            $data['order_id'] = $order_item_id;
            if ($payment_data != 'razorpay') {
                $user->balance += $amount;
            }
        }

        $user->save();

        $request = new \Illuminate\Http\Request($data);
        $transactionController = app(TransactionController::class);

        $transactionController->store($request);
        $response['error'] = false;
        $response['message'] = "Balance Update Successfully";
        $response['data'] = [];
    } else {
        $response['error'] = true;
        $response['error_message'] = ($user->balance != 0) ? "User's Wallet balance less than {$user->balance} can be used only" : "Doesn't have sufficient wallet balance to proceed further.";
        $response['data'] = [];
    }

    return $response;
}

function findMediaType($extenstion)
{
    $mediaTypes = config('eshop_pro.type');

    foreach ($mediaTypes as $mainType => $mediaType) {
        if (in_array(strtolower($extenstion), $mediaType['types'])) {

            return [$mainType, $mediaType['icon']];
        }
    }
    return false;
}


function getImageUrl($path, $image_type = '', $image_size = '', $file_type = 'image', $const = 'MEDIA_PATH')
{

    $pathParts = explode('/', $path);

    $subdirectory = implode("/", array_slice($pathParts, 0, -1));
    $image_name = end($pathParts);
    $file_main_dir = str_replace('\\', '/', public_path(config('constants.' . $const) . $subdirectory));

    if ($file_type == 'image') {


        $types = ['thumb', 'cropped'];
        $sizes = ['md', 'sm'];


        if (in_array(strtolower($image_type), $types) && in_array(strtolower($image_size), $sizes)) {

            $filepath = $file_main_dir . '/' . $image_type . '-' . $image_size . '/' . $image_name;


            if (File::exists($filepath)) {

                return asset(config('constants.' . $const) . '/' . $path);
            } elseif (File::exists($file_main_dir . '/' . $image_name)) {

                return asset(config('constants.' . $const) . '/' . $path);
            } else {
                return asset(Config::get('constants.NO_IMAGE'));
            }
        } else {


            if (File::exists($file_main_dir . '/' . $image_name)) {

                return asset(config('constants.' . $const) . '/' . $path);
            } else {
                return asset(Config::get('constants.NO_IMAGE'));
            }
        }
    } else {
        $file = new SplFileInfo($file_main_dir . '/' . $image_name);
        $ext = $file->getExtension();

        $media_data = findMediaType($ext);

        if (is_array($media_data) && isset($media_data[1])) {
            $imagePlaceholder = $media_data[1];
        } else {
            // Handle the case where media type is not found
            return asset(Config::get('constants.NO_IMAGE'));
        }

        $filepath = str_replace('\\', '/', public_path($imagePlaceholder));

        if (File::exists($filepath)) {
            return asset($imagePlaceholder);
        } else {
            return asset(Config::get('constants.NO_IMAGE')); // Assuming 'no_image' is defined in your config
        }
    }
}

function getInvoiceImage($image)
{

    $fileUrl = getMediaImageUrl($image, 'SELLER_IMG_PATH');
    $file = new SymfonyFile($fileUrl);
    $data = 'data:' . $file->getMimeType() . ';base64,' . base64_encode($file->getContent());
    return $data;
}

function fetchUsers($id)
{
    $userDetails = User::select(
        'users.id',
        'users.username',
        'users.email',
        'users.mobile',
        'users.balance',
        'users.dob',
        'users.referral_code',
        'users.friends_code',
        'c.name as cities',
        'a.name as area',
        'users.street',
        'users.pincode'
    )
        ->leftJoin('areas as a', 'users.area', '=', 'a.name')
        ->leftJoin('cities as c', 'users.city', '=', 'c.name')
        ->where('users.id', $id)
        ->first();

    return $userDetails;
}

function updateDetails($set, $where, $table)
{
    try {
        DB::beginTransaction();
        // Update the records
        DB::table($table)->where($where)->update($set);
        // Commit the transaction
        DB::commit();
        return true; // Updated successfully
    } catch (\Exception $e) {
        // Something went wrong, rollback the transaction
        DB::rollBack();
        return false;
    }
}

function validatePromoCode($promo_code, $user_id, $final_total, $for_place_order = 0, $language_code = '')
{
    // dd($promo_code);
    if (isset($promo_code) && !empty($promo_code)) {
        $originalDateTime = now();
        $carbonDateTime = Carbon::parse($originalDateTime);
        $currTime = $carbonDateTime->format('Y-m-d');
        // dd($for_place_order);
        if (isset($for_place_order) && $for_place_order == 1) {

            $promoCode = DB::table('promo_codes as pc')
                ->leftJoin('orders as o', 'o.promo_code_id', '=', 'pc.id')
                ->selectRaw('pc.*, COUNT(o.id) as promo_used_counter')
                ->selectSub(function ($query) use ($user_id, $promo_code) {
                    $query->selectRaw('COUNT(user_id)')
                        ->from('orders')
                        ->where('user_id', $user_id)
                        ->where('promo_code_id', $promo_code);
                }, 'user_promo_usage_counter')
                ->where('pc.id', $promo_code)
                ->where('pc.status', 1)
                ->whereDate('start_date', '<=', $currTime)
                ->whereDate('end_date', '>=', $currTime)
                ->get();
        } else {

            $promoCode = DB::table('promo_codes as pc')
                ->select(
                    'pc.*',
                    DB::raw('count(o.id) as promo_used_counter'),
                    DB::raw('(SELECT count(user_id) FROM orders WHERE user_id = ' . $user_id . ' AND promo_code = "' . $promo_code . '") as user_promo_usage_counter')
                )
                ->leftJoin('orders as o', 'o.promo_code_id', '=', 'pc.promo_code')
                ->where('pc.promo_code', $promo_code)
                ->where('pc.status', '1')
                ->whereDate('start_date', '<=', date('Y-m-d'))
                ->whereDate('end_date', '>=', date('Y-m-d'))
                ->groupBy('pc.promo_code')
                ->get();
            // dd($promoCode);
        }
        // dd($language_code);
        if (!$promoCode->isEmpty() && isset($promoCode[0]->id)) {

            if ($promoCode[0]->promo_used_counter < $promoCode[0]->no_of_users) {
                if ($final_total >= $promoCode[0]->minimum_order_amount) {
                    if ($promoCode[0]->repeat_usage == 1 && ($promoCode[0]->user_promo_usage_counter <= $promoCode[0]->no_of_repeat_usage)) {
                        if (intval($promoCode[0]->user_promo_usage_counter) <= intval($promoCode[0]->no_of_repeat_usage)) {

                            $response = [
                                'error' => false,
                                'message' => 'The promo code is valid',
                                'language_message_key' => 'the_promo_code_is_valid',
                            ];

                            if ($promoCode[0]->discount_type == 'percentage') {
                                $promo_code_discount = floatval($final_total * $promoCode[0]->discount / 100);
                            } else {
                                $promo_code_discount = $promoCode[0]->discount;
                            }
                            if ($promo_code_discount <= $promoCode[0]->max_discount_amount) {
                                $total = (isset($promoCode[0]->is_cashback) && $promoCode[0]->is_cashback == 0) ? floatval($final_total) - $promo_code_discount : floatval($final_total);
                            } else {
                                $total = (isset($promoCode[0]->is_cashback) && $promoCode[0]->is_cashback == 0) ? floatval($final_total) - $promoCode[0]->max_discount_amount : floatval($final_total);
                                $promo_code_discount = $promoCode[0]->max_discount_amount;
                            }

                            $promoCode[0]->final_total = strval(floatval($total));
                            $promoCode[0]->title = getDynamicTranslation('promo_codes', 'title', $promoCode[0]->id, $language_code);
                            $promoCode[0]->message = getDynamicTranslation('promo_codes', 'message', $promoCode[0]->id, $language_code);
                            $promoCode[0]->currency_final_total_data = getPriceCurrency($promoCode[0]->final_total);
                            $promoCode[0]->image = (isset($promoCode[0]->image) && !empty($promoCode[0]->image)) ? getImageUrl($promoCode[0]->image) : '';
                            $promoCode[0]->final_discount = strval(floatval($promo_code_discount));
                            $promoCode[0]->currency_final_discount_data = getPriceCurrency($promoCode[0]->final_discount);
                            $response['data'] = $promoCode;
                            return response()->json($response);
                        } else {
                            $response = [
                                'error' => true,
                                'message' => 'This promo code cannot be redeemed as it exceeds the usage limit',
                                'language_message_key' => 'promo_code_can_not_be_redeemed_as_it_exceeds_the_usage_limit',
                            ];
                            $response['data']['final_total'] = strval(floatval($final_total));
                            return response()->json($response);
                        }
                    } else if ($promoCode[0]->repeat_usage == 0 && ($promoCode[0]->user_promo_usage_counter <= 0)) {
                        if (intval($promoCode[0]->user_promo_usage_counter) <= intval($promoCode[0]->no_of_repeat_usage)) {

                            $response['error'] = false;
                            $response['message'] = 'The promo code is valid';
                            $response['language_message_key'] = 'the_promo_code_is_valid';

                            if ($promoCode[0]->discount_type == 'percentage') {
                                $promo_code_discount = floatval($final_total * $promoCode[0]->discount / 100);
                            } else {
                                $promo_code_discount = floatval($final_total - $promoCode[0]->discount);
                            }
                            if ($promo_code_discount <= $promoCode[0]->max_discount_amount) {
                                $total = (isset($promoCode[0]->is_cashback) && $promoCode[0]->is_cashback == 0) ? floatval($final_total) - $promo_code_discount : floatval($final_total);
                            } else {
                                $total = (isset($promoCode[0]->is_cashback) && $promoCode[0]->is_cashback == 0) ? floatval($final_total) - $promoCode[0]->max_discount_amount : floatval($final_total);
                                $promo_code_discount = $promoCode[0]->max_discount_amount;
                            }
                            $promoCode[0]->final_total = strval(floatval($total));
                            $promoCode[0]->final_discount = strval(floatval($promo_code_discount));
                            $promoCode[0]->title = getDynamicTranslation('promo_codes', 'title', $promoCode[0]->id, $language_code);
                            $promoCode[0]->message = getDynamicTranslation('promo_codes', 'message', $promoCode[0]->id, $language_code);
                            $response['data'] = $promoCode;
                            return response()->json($response);
                        } else {
                            $response = [
                                'error' => true,
                                'message' => 'This promo code cannot be redeemed as it exceeds the usage limit',
                                'language_message_key' => 'promo_code_can_not_be_redeemed_as_it_exceeds_the_usage_limit',
                            ];
                            $response['data']['final_total'] = strval(floatval($final_total));
                            return response()->json($response);
                        }
                    } else {
                        $response = [
                            'error' => true,
                            'message' => 'The promo has already been redeemed. cannot be reused',
                            'language_message_key' => 'promo_code_already_redeemed_can_not_be_resued',
                        ];
                        $response['data']['final_total'] = strval(floatval($final_total));
                        return response()->json($response);
                    }
                } else {
                    $response = [
                        'error' => true,
                        'message' => 'This promo code is applicable only for amount greater than or equal to ' . $promoCode[0]->minimum_order_amount,
                        'language_message_key' => 'this_promo_code_is_applicable_only_for_amount_greater_then_or_equal_to ' . $promoCode[0]->minimum_order_amount,
                    ];
                    $response['data']['final_total'] = strval(floatval($final_total));
                    return response()->json($response);
                }
            } else {
                $response = [
                    'error' => true,
                    'message' => "This promo code is applicable only for first " . $promoCode[0]->no_of_users . " users",
                ];
                $response['data']['final_total'] = strval(floatval($final_total));
                return response()->json($response);
            }
        } else {
            $response = [
                'error' => true,
                'message' => 'The promo code is not available or expired',
                'language_message_key' => 'the_promo_code_is_not_available_or_expired',
            ];
            $response['data']['final_total'] = strval(floatval($final_total));
            return response()->json($response);
        }
    }
}

function checkProductDeliverable($product_id, $zipcode = "", $zipcode_id = "", $store_id = '', $city_id = "", $product_type = 'regular')
{
    $products = $tmpRow = array();
    $settings = getSettings('shipping_method', true, true);
    $settings = json_decode($settings, true);
    $product_weight = 0;
    if ($product_type == "combo") {
        $product = fetchComboProduct(id: $product_id);
    } else {
        $product = fetchProduct(id: $product_id);
    }
    /* check in local shipping first */
    $tmpRow['is_deliverable'] = false;
    $tmpRow['delivery_by'] = '';
    if (isset($product['total']) && $product['total'] >= 1) {
        if ($product_type == "combo") {
            $product = $product['combo_product'][0];
        } else {
            $product = $product['product'][0];
        }
        if (isset($settings['local_shipping_method']) && $settings['local_shipping_method'] == 1) {

            $deliverabilitySettings = getDeliveryChargeSetting($store_id);
            if (isset($deliverabilitySettings[0]->product_deliverability_type) && !empty($deliverabilitySettings[0]->product_deliverability_type)) {
                if ($deliverabilitySettings[0]->product_deliverability_type == 'city_wise_deliverability') {
                    $tmpRow['is_deliverable'] = (!empty($city_id) && $city_id > 0) ?
                        isProductDelivarable('city', $city_id, $product->id, $product_type)
                        : false;
                } else {

                    $tmpRow['is_deliverable'] = !empty($zipcode_id) && $zipcode_id > 0 ?
                        isProductDelivarable('zipcode', $zipcode_id, $product->id, $product_type) :
                        false;
                }
            }


            $tmpRow['delivery_by'] = isset($tmpRow['is_deliverable']) && $tmpRow['is_deliverable'] ? 'local' : '';
        }
        /* check in standard shipping then */
        if (isset($settings['shiprocket_shipping_method']) && $settings['shiprocket_shipping_method'] == 1) {

            if (!$tmpRow['is_deliverable'] && $product->pickup_location != "") {
                $shiprocket = new Shiprocket();
                $pickup_pincode = fetchDetails('pickup_locations', ['pickup_location' => $product->pickup_location], 'pincode');
                $product_weight += $product->variants[0]->weight * 1;

                if (isset($zipcode) && !empty($zipcode)) {
                    if ($product_weight > 15) {
                        $tmpRow['is_deliverable'] = false;
                        $tmpRow['is_valid_wight'] = 0;
                        $tmpRow['message'] = "You cannot ship weight more then 15 KG";
                    } else {
                        $availibility_data = [
                            'pickup_postcode' => (isset($pickup_pincode[0]->pincode) && !empty($pickup_pincode[0]->pincode)) ? $pickup_pincode[0]->pincode : "",
                            'delivery_postcode' => $zipcode,
                            'cod' => 0,
                            'weight' => $product_weight,
                        ];


                        $check_deliveribility = $shiprocket->check_serviceability($availibility_data);
                        if (isset($check_deliveribility['status_code']) && $check_deliveribility['status_code'] == 422) {
                            $tmpRow['is_deliverable'] = false;
                            $tmpRow['message'] = "Invalid zipcode supplied!";
                        } else {

                            if (isset($check_deliveribility['status']) && $check_deliveribility['status'] == 200 && !empty($check_deliveribility['data']['available_courier_companies'])) {
                                $tmpRow['is_deliverable'] = true;
                                $tmpRow['delivery_by'] = "standard_shipping";
                                $estimate_date = $check_deliveribility['data']['available_courier_companies'][0]['etd'];
                                $tmpRow['estimate_date'] = $estimate_date;
                                $_SESSION['valid_zipcode'] = $zipcode;
                                $tmpRow['message'] = 'Product is deliverable by ' . $estimate_date;
                            } else {
                                $tmpRow['is_deliverable'] = false;
                                $tmpRow['message'] = $check_deliveribility['message'];
                            }
                        }
                    }
                } else {
                    $tmpRow['is_deliverable'] = false;
                    $tmpRow['message'] = 'Please select zipcode to check the deliveribility of item.';
                }
            }
        }

        $tmpRow['product_id'] = $product->id;
        $tmpRow['product_qty'] = 1;
        $products[] = $tmpRow;
        if (!empty($products)) {
            return $products;
        } else {
            return false;
        }
    }
}

function checkCartProductsDeliverable($user_id, $zipcode = "", $zipcode_id = "", $store_id = '', $city = "", $city_id = "", $is_saved_for_later = 0, $language_code = '')
{
    $products = $tmpRow = array();
    // $cart = getCartTotal($user_id, false, $is_saved_for_later, '', $store_id);
    $cart = getCartTotal($user_id, false, $is_saved_for_later, '', $store_id);
    // dd($cart);
    $settings = getSettings('shipping_method', true, true);
    $settings = json_decode($settings, true);

    if (!$cart->isEmpty()) {

        $product_weight = 0;

        for ($i = 0; $i < $cart[0]->cart_count; $i++) {
            /* check in local shipping first */
            $tmpRow['is_deliverable'] = false;
            $tmpRow['delivery_by'] = '';
            if (isset($settings['local_shipping_method']) && $settings['local_shipping_method'] == 1) {
                $deliverabilitySettings = getDeliveryChargeSetting($store_id);
                if (isset($deliverabilitySettings[0]->product_deliverability_type) && !empty($deliverabilitySettings[0]->product_deliverability_type)) {
                    // dd($deliverabilitySettings[0]->product_deliverability_type);
                    if ($deliverabilitySettings[0]->product_deliverability_type == 'city_wise_deliverability') {
                        // dd($city_id);
                        // dd('here');
                        $seller_deliverable = (!empty($city_id) && $city_id > 0) ? isSellerDeliverable('city', $city_id, $cart[$i]->seller_id, $store_id) : false;
                        // print_r('here');
                        // dd($seller_deliverable);
                        if ($seller_deliverable) {
                            $tmpRow['is_deliverable'] = (!empty($city_id) && $city_id > 0) ?
                                isProductDelivarable('city', $city_id, $cart[$i]->product_id, $cart[$i]->cart_product_type)
                                : false;
                        } else {
                            $tmpRow['is_deliverable'] = false;
                        }
                    } else {
                        $seller_deliverable = (!empty($zipcode_id) && $zipcode_id > 0) ? isSellerDeliverable('zipcode', $zipcode_id, $cart[$i]->seller_id, $store_id) : false;
                        //    dd($seller_deliverable);
                        if ($seller_deliverable) {
                            $tmpRow['is_deliverable'] = !empty($zipcode_id) && $zipcode_id > 0 ?
                                isProductDelivarable('zipcode', $zipcode_id, $cart[$i]->product_id, $cart[$i]->cart_product_type) :
                                false;
                        } else {
                            $tmpRow['is_deliverable'] = false;
                        }
                    }
                }

                $tmpRow['delivery_by'] = isset($tmpRow['is_deliverable']) && $tmpRow['is_deliverable'] ? 'local' : '';
            }

            /* check in standard shipping then */
            if (isset($settings['shiprocket_shipping_method']) && $settings['shiprocket_shipping_method'] == 1) {


                if (!$tmpRow['is_deliverable'] && $cart[$i]->pickup_location != "") {
                    $shiprocket = new Shiprocket();
                    $pickup_pincode = fetchDetails('pickup_locations', ['pickup_location' => $cart[$i]->pickup_location], 'pincode');
                    $product_weight += $cart[$i]->weight * $cart[$i]->qty;

                    if (isset($zipcode) && !empty($zipcode)) {
                        if ($product_weight > 15) {
                            $tmpRow['is_deliverable'] = false;
                            $tmpRow['is_valid_wight'] = 0;
                            $tmpRow['message'] = "You cannot ship weight more then 15 KG";
                        } else {
                            $availibility_data = [
                                'pickup_postcode' => (isset($pickup_pincode[0]->pincode) && !empty($pickup_pincode[0]->pincode)) ? $pickup_pincode[0]->pincode : "",
                                'delivery_postcode' => $zipcode,
                                'cod' => 0,
                                'weight' => $product_weight,
                            ];


                            $check_deliveribility = $shiprocket->check_serviceability($availibility_data);

                            if (isset($check_deliveribility['status_code']) && $check_deliveribility['status_code'] == 422) {
                                $tmpRow['is_deliverable'] = false;
                                $tmpRow['message'] = "Invalid zipcode supplied!";
                            } else {

                                if (isset($check_deliveribility['status']) && $check_deliveribility['status'] == 200 && !empty($check_deliveribility['data']['available_courier_companies'])) {
                                    $tmpRow['is_deliverable'] = true;
                                    $tmpRow['delivery_by'] = "standard_shipping";
                                    $estimate_date = $check_deliveribility['data']['available_courier_companies'][0]['etd'];
                                    $tmpRow['estimate_date'] = $estimate_date;
                                    $_SESSION['valid_zipcode'] = $zipcode;
                                    $tmpRow['message'] = 'Product is deliverable by ' . $estimate_date;
                                } else {
                                    $tmpRow['is_deliverable'] = false;
                                    $tmpRow['message'] = $check_deliveribility['message'];
                                }
                            }
                        }
                    } else {
                        $tmpRow['is_deliverable'] = false;
                        $tmpRow['message'] = 'Please select zipcode to check the deliveribility of item.';
                    }
                }
            }


            // dd($cart[$i]);
            $tmpRow['product_id'] = $cart[$i]->product_id;
            $tmpRow['product_qty'] = $cart[$i]->qty;
            $tmpRow['minimum_free_delivery_order_qty'] = $cart[$i]->minimum_free_delivery_order_qty;
            $tmpRow['product_delivery_charge'] = $cart[$i]->product_delivery_charge;
            $tmpRow['currency_product_delivery_charge_data'] = isset($cart[$i]->product_delivery_charge) ? getPriceCurrency($cart[$i]->product_delivery_charge) : 0;

            $tmpRow['variant_id'] = $cart[$i]->id;

            if ($cart[$i]->cart_product_type === 'regular') {
                // Get name from products table
                $tmpRow['name'] = getDynamicTranslation('products', 'name', $cart[$i]->product_id, $language_code);
            } else {
                // Get name from combo_products table
                $tmpRow['name'] = getDynamicTranslation('combo_products', 'title', $cart[$i]->product_id, $language_code);
            }


            $products[] = $tmpRow;
        }


        if (!empty($products)) {

            return $products;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function makeShippingParcels(Collection $data)
{
    $parcels = collect();

    $data->each(function ($product) use (&$parcels) {

        if (trim($product->pickup_location) !== '') {

            $sellerId = $product->seller_id;
            $pickupLocation = $product->pickup_location;
            $weight = $product->weight;

            if (!$parcels->has($sellerId)) {
                $parcels->put($sellerId, collect());
            }

            if (!$parcels[$sellerId]->has($pickupLocation)) {
                $parcels[$sellerId]->put($pickupLocation, collect(['weight' => 0]));
            }

            $parcels[$sellerId][$pickupLocation]['weight'] += $weight * $product->qty;
        }
    });

    return $parcels;
}

function checkParcelsDeliverability($parcels, $userPincode)
{
    $shiprocket = new Shiprocket();

    $minDays = $maxDays = $deliveryChargeWithCod = $deliveryChargeWithoutCod = 0;
    $data = [];

    foreach ($parcels as $sellerId => $parcel) {
        foreach ($parcel as $pickupLocation => $parcelWeight) {
            $pickupPostcode = fetchDetails('pickup_locations', ['pickup_location' => $pickupLocation], 'pincode');

            if (isset($parcel[$pickupLocation]['weight']) && $parcel[$pickupLocation]['weight'] > 15) {
                $data = "More than 15kg weight is not allowed";
            } else {
                $availabilityData = [
                    'pickup_postcode' => $pickupPostcode[0]->pincode,
                    'delivery_postcode' => $userPincode,
                    'cod' => 0,
                    'weight' => $parcelWeight['weight'],
                ];

                $checkDeliverability = $shiprocket->check_serviceability($availabilityData);
                $shiprocketData = shiprocketRecommendedData($checkDeliverability);

                $availabilityDataWithCod = [
                    'pickup_postcode' => $pickupPostcode[0]->pincode,
                    'delivery_postcode' => $userPincode,
                    'cod' => 1,
                    'weight' => $parcelWeight['weight'],
                ];

                $checkDeliverabilityWithCod = $shiprocket->check_serviceability($availabilityDataWithCod);
                $shiprocketDataWithCod = shiprocketRecommendedData($checkDeliverabilityWithCod);

                $data[$sellerId][$pickupLocation]['parcel_weight'] = $parcelWeight['weight'];
                $data[$sellerId][$pickupLocation]['pickup_availability'] = isset($shiprocketData['pickup_availability']) ? $shiprocketData['pickup_availability'] : '';
                $data[$sellerId][$pickupLocation]['courier_name'] = isset($shiprocketData['courier_name']) ? $shiprocketData['courier_name'] : '';
                $data[$sellerId][$pickupLocation]['delivery_charge_with_cod'] = isset($shiprocketDataWithCod['rate']) ? $shiprocketDataWithCod['rate'] : 0;
                $data[$sellerId][$pickupLocation]['currency_delivery_charge_with_cod'] = isset($shiprocketDataWithCod['rate']) ? getPriceCurrency($shiprocketDataWithCod['rate']) : 0;
                $data[$sellerId][$pickupLocation]['delivery_charge_without_cod'] = isset($shiprocketData['rate']) ? $shiprocketData['rate'] : 0;
                $data[$sellerId][$pickupLocation]['currency_delivery_charge_without_cod'] = isset($shiprocketData['rate']) ? getPriceCurrency($shiprocketData['rate']) : 0;

                $data[$sellerId][$pickupLocation]['estimate_date'] = isset($shiprocketData['etd']) ? $shiprocketData['etd'] : '';
                $data[$sellerId][$pickupLocation]['estimate_days'] = isset($shiprocketData['estimated_delivery_days']) ? $shiprocketData['estimated_delivery_days'] : '';

                $minDays = isset($shiprocketData['estimated_delivery_days']) && (empty($minDays) || $shiprocketData['estimated_delivery_days'] < $minDays) ? $shiprocketData['estimated_delivery_days'] : $minDays;
                $maxDays = isset($shiprocketData['estimated_delivery_days']) && (empty($maxDays) || $shiprocketData['estimated_delivery_days'] > $maxDays) ? $shiprocketData['estimated_delivery_days'] : $maxDays;

                $deliveryChargeWithCod += $data[$sellerId][$pickupLocation]['delivery_charge_with_cod'];
                $deliveryChargeWithoutCod += $data[$sellerId][$pickupLocation]['delivery_charge_without_cod'];
            }
        }
    }

    $deliveryDay = ($minDays == $maxDays) ? $minDays : $minDays . '-' . $maxDays;
    $shippingParcels = [
        'error' => false,
        'estimated_delivery_days' => $deliveryDay,
        'estimate_date' => isset($shiprocketData['etd']) ? $shiprocketData['etd'] : '',
        'delivery_charge' => 0,
        'delivery_charge_with_cod' => round($deliveryChargeWithCod),
        'currency_delivery_charge_with_cod' => getPriceCurrency($deliveryChargeWithCod),
        'delivery_charge_without_cod' => round($deliveryChargeWithoutCod),
        'currency_delivery_charge_without_cod' => getPriceCurrency($deliveryChargeWithoutCod),
        'data' => $data
    ];

    return $shippingParcels;
}


function shiprocketRecommendedData($shiprocketData)
{
    $result = [];


    if (isset($shiprocketData['data']) && !empty($shiprocketData['data'])) {

        if (isset($shiprocketData['data']['recommended_courier_company_id'])) {
            foreach ($shiprocketData['data']['available_courier_companies'] as $rd) {
                if ($shiprocketData['data']['recommended_courier_company_id'] == $rd['courier_company_id']) {
                    $result = $rd;
                    break;
                }
            }
        } else {
            foreach ($shiprocketData['data']['available_courier_companies'] as $rd) {
                if ($rd['courier_company_id']) {
                    $result = $rd;
                    break;
                }
            }
        }
        return $result;
    } else {
        return $shiprocketData;
    }
}

function isSingleSeller($product_variant_id, $user_id, $product_type = "", $store_id = '')
{

    if (isset($product_variant_id) && !empty($product_variant_id) && isset($user_id) && !empty($user_id)) {
        $pv_id = (strpos($product_variant_id, ",")) ? explode(",", $product_variant_id) : $product_variant_id;



        $exist_data = Cart::select('cart.product_variant_id', 'products.seller_id as product_seller_id', 'combo_products.seller_id')
            ->leftJoin('product_variants', 'product_variants.id', '=', 'cart.product_variant_id')
            ->leftJoin('products', 'products.id', '=', 'product_variants.product_id')
            ->leftJoin('combo_products', 'combo_products.id', '=', 'cart.product_variant_id')
            ->where('cart.user_id', $user_id)
            ->where('cart.is_saved_for_later', 0)
            ->where('cart.store_id', $store_id)
            ->groupBy('products.seller_id')
            ->groupBy('combo_products.seller_id')
            ->get()
            ->toArray();


        if (!empty($exist_data)) {


            $seller_ids = array_column($exist_data, "product_seller_id");
            $product_seller_ids = array_column($exist_data, "seller_id");

            // Combine both arrays into a single array
            $combined_seller_ids = array_merge($seller_ids, $product_seller_ids);

            // Filter out null values
            $combined_seller_ids = array_filter($combined_seller_ids, function ($value) {
                return $value !== null;
            });

            // Get unique values
            $unique_seller_ids = array_values(array_unique($combined_seller_ids));
        } else {
            // Clear to add to cart
            return true;
        }


        // Get seller ids of variants
        if ($product_type == 'regular') {
            $new_data = Product_variants::select('products.seller_id')
                ->join('products', 'products.id', '=', 'product_variants.product_id')
                ->whereIn('product_variants.id', is_array($pv_id) ? $pv_id : [$pv_id])
                ->first();
        } else {
            $new_data = ComboProduct::select('combo_products.seller_id')
                ->where('combo_products.id', $pv_id)
                ->first();
        }

        $new_seller_id = $new_data->seller_id;

        if (!empty($unique_seller_ids) && !empty($new_seller_id)) {
            if (in_array($new_seller_id, (array) $unique_seller_ids)) {
                // Clear to add to cart
                return true;
            } else {
                // Another seller id variant, give single seller error
                return false;
            }
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function isSingleProductType($product_variant_id, $user_id, $product_type, $store_id = "")
{
    if (isset($product_variant_id) && !empty($product_variant_id) && isset($user_id) && !empty($user_id)) {
        $pv_id = (strpos($product_variant_id, ",")) ? explode(",", $product_variant_id) : $product_variant_id;
        $product_type = (strpos($product_type, ",")) ? explode(",", $product_type) : $product_type;
        $Product_variants = Product_variants::select('products.type')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->whereIn('product_variants.id', is_array($pv_id) ? $pv_id : [$pv_id])
            ->get()
            ->toArray();
        $ComboProduct = ComboProduct::select('combo_products.product_type')
            ->whereIn('combo_products.id', is_array($pv_id) ? $pv_id : [$pv_id])
            ->get()->toArray();

        $is_single_product_type = array_merge($Product_variants, $ComboProduct);
        // CHECK FOR REGULAR PRODUCT
        $is_single_product_type = array_column($is_single_product_type, 'type');
        $hasDigitalProduct = in_array('digital_product', $is_single_product_type);
        $hasSimpleOrVariableProduct = in_array('simple_product', $is_single_product_type) || in_array('variable_product', $is_single_product_type) || in_array('physical_product', $is_single_product_type);
        if ($hasDigitalProduct && $hasSimpleOrVariableProduct) {
            return false;
        }


        $exist_data = Cart::select('cart.product_variant_id', 'products.type', 'combo_products.product_type')
            ->leftJoin('product_variants', 'product_variants.id', '=', 'cart.product_variant_id')
            ->leftJoin('products', 'products.id', '=', 'product_variants.product_id')
            ->leftJoin('combo_products', 'combo_products.id', '=', 'cart.product_variant_id')
            ->where('cart.user_id', $user_id)
            ->where('cart.store_id', $store_id)
            ->where('cart.is_saved_for_later', 0)
            ->groupBy('products.type')
            ->groupBy('combo_products.product_type')
            ->get()
            ->toArray();

        if (!empty($exist_data)) {

            $product_types = array_column($exist_data, "type");
            $combo_product_types = array_column($exist_data, "product_type");

            // Combine both arrays into a single array
            $combined_product_types = array_merge($product_types, $combo_product_types);

            // Filter out null values
            $combined_product_types = array_filter($combined_product_types, function ($value) {
                return $value !== null;
            });

            // Get unique values
            $unique_product_types = array_values(array_unique($combined_product_types));
        } else {
            // Clear to add cart
            return true;
        }

        // Get product types of variants
        if ($product_type == 'regular') {

            $new_data = Product_variants::select('products.type')
                ->join('products', 'products.id', '=', 'product_variants.product_id')
                ->whereIn('product_variants.id', is_array($pv_id) ? $pv_id : [$pv_id])
                ->get()
                ->toArray();
        } else {

            $new_data = ComboProduct::select('combo_products.product_type')
                ->where('combo_products.id', $pv_id)
                ->get()->toArray();;
        }
        if ($product_type == 'regular') {
            $new_product_type = $new_data[0]['type'];
        } else {
            $new_product_type = $new_data[0]['product_type'];
        }

        if (!empty($unique_product_types) && !empty($new_product_type)) {
            if (in_array($new_product_type, (array) $unique_product_types)) {
                // Clear to add to cart
                return true;
            } else {
                if (!in_array("digital_product", (array) $unique_product_types) && ($new_product_type == "variable_product" || $new_product_type == "simple_product" || $new_product_type == "physical_product")) {
                    return true;
                } else {
                    // Another product type, give single product type
                    return false;
                }
            }
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function getCartCount($user_id, $store_id = '')
{
    if (!empty($user_id)) {
        $count = Cart::where('user_id', $user_id)
            ->where('qty', '!=', 0)
            ->where('store_id', $store_id)
            ->where('is_saved_for_later', 0)
            ->distinct()
            ->count();
    } else {
        $count = 0;
    }
    return $count;
}


function isVariantAvailableInCart($product_variant_id, $user_id)
{
    // Use Eloquent to check if the variant is available in the cart\
    $cartItem = Cart::where('product_variant_id', $product_variant_id)
        ->where('user_id', $user_id)
        ->where('qty', '>', 0)
        ->where('is_saved_for_later', 0)
        ->select('id')
        ->first();


    return !is_null($cartItem);
}

function getSellerPermission($seller_id, $store_id, $permit = NULL)
{
    // Check if $seller_id is provided, otherwise get it from session
    $seller_id = (isset($seller_id) && !empty($seller_id)) ? $seller_id : Session::get('user_id');

    // Fetch seller store details
    $permits = fetchDetails('seller_store', ['seller_id' => $seller_id, 'store_id' => $store_id], 'permissions');

    // Check if $permits is not empty and has the necessary permissions data
    if (!empty($permits) && isset($permits[0])) {
        // If a specific permit is requested
        if (!empty($permit)) {
            $s_permits = json_decode($permits[0]->permissions, true);

            // Check if the requested permit exists in the permissions array
            return isset($s_permits[$permit]) ? $s_permits[$permit] : null;
        } else {
            // Return all permissions if no specific permit is requested
            return json_decode($permits[0]->permissions);
        }
    } else {
        // Handle case where $permits is empty or invalid
        return null; // Or return a default value like false if needed
    }
}


function fetchOrders($order_id = NULL, $user_id = NULL, $status = NULL, $delivery_boy_id = NULL, $limit = NULL, $offset = NULL, $sort = 'o.id', $order = 'DESC', $download_invoice = false, $start_date = null, $end_date = null, $search = null, $city_id = null, $area_id = null, $seller_id = null, $order_type = '', $from_seller = false, $store_id = null, $language_code = "")
{

    $total_query = DB::table('orders as o')
        ->select(DB::raw('COUNT(DISTINCT o.id) as total'), 'oi.order_type')
        ->leftJoin('users as u', 'u.id', '=', 'o.user_id')
        ->leftJoin('order_items as oi', 'o.id', '=', 'oi.order_id')
        ->leftJoin('product_variants as pv', 'pv.id', '=', 'oi.product_variant_id')
        ->leftJoin('products as p', 'pv.product_id', '=', 'p.id')
        ->leftJoin('order_trackings as ot', 'ot.order_item_id', '=', 'oi.id')
        ->leftJoin('addresses as a', 'a.id', '=', 'o.address_id')
        ->leftJoin('combo_products as cp', 'cp.id', '=', 'oi.product_variant_id')
        ->where(function ($query) {
            $query->where('oi.order_type', 'regular_order')
                ->orWhere('oi.order_type', 'combo_order');
        });
    if (isset($store_id) && $store_id !== NULL && !empty($store_id)) {
        $total_query->where('o.store_id', $store_id);
    }

    if (isset($order_id) && $order_id !== NULL && !empty($order_id)) {
        $total_query->where('o.id', $order_id);
    }

    if (isset($delivery_boy_id) && $delivery_boy_id !== null && !empty($delivery_boy_id)) {
        $total_query->where('oi.delivery_boy_id', $delivery_boy_id);
    }

    if (isset($user_id) && $user_id !== null && !empty($user_id)) {
        $total_query->where('o.user_id', $user_id);
    }

    if (isset($city_id) && $city_id !== null && !empty($city_id)) {
        $total_query->where('a.city_id', $city_id);
    }

    if (isset($area_id) && $area_id !== null && !empty($area_id)) {
        $total_query->where('a.area_id', $area_id);
    }

    if (isset($seller_id) && $seller_id !== null && !empty($seller_id)) {
        $total_query->where('oi.seller_id', $seller_id);
    }

    if (isset($order_type) && $order_type !== '' && $order_type === 'digital') {
        $total_query->where(function ($query) {
            $query->where('p.type', 'digital_product')
                ->orWhere('cp.product_type', 'digital_product');
        });
    }



    if (isset($order_type) && $order_type !== '' && $order_type === 'simple') {

        $total_query->where(function ($query) {
            $query->where('p.type', '!=', 'digital_product')
                ->orWhere('cp.product_type', '!=', 'digital_product');
        });
    }
    if (isset($status) && !empty($status) && $status != '' && is_array($status) && count($status) > 0) {
        $status = array_map('trim', $status);

        $total_query->whereIn('oi.active_status', $status);
    }

    if (isset($start_date) && $start_date !== null && isset($end_date) && $end_date !== null && !empty($end_date) && !empty($start_date)) {
        $total_query->whereDate('o.created_at', '>=', $start_date)
            ->whereDate('o.created_at', '<=', $end_date);
    }

    if (!empty($start_date)) {
        $total_query->whereDate('o.created_at', '>=', $start_date);
    }

    if (!empty($end_date)) {
        $total_query->whereDate('o.created_at', '<=', $end_date);
    }
    if (isset($search) && $search !== null && !empty($search)) {
        $filters = [
            'u.username' => $search,
            'u.email' => $search,
            'o.id' => $search,
            'o.mobile' => $search,
            'o.address' => $search,
            'o.payment_method' => $search,
            'o.delivery_time' => $search,
            'o.created_at' => $search,
            'oi.active_status' => $search,
            'p.name' => $search,
        ];

        $total_query->where(function ($query) use ($filters) {
            foreach ($filters as $column => $value) {
                $query->orWhere($column, 'LIKE', '%' . $value . '%');
            }
        });
    }
    if (isset($search) && $search !== null && !empty($search)) {
        $combo_filters = [
            'u.username' => $search,
            'u.email' => $search,
            'o.id' => $search,
            'o.mobile' => $search,
            'o.address' => $search,
            'o.payment_method' => $search,
            'o.delivery_time' => $search,
            'o.created_at' => $search,
            'oi.active_status' => $search,
            'cp.title' => $search,
        ];

        $total_query->where(function ($query) use ($filters) {
            foreach ($filters as $column => $value) {
                $query->orWhere($column, 'LIKE', '%' . $value . '%');
            }
        });
    }

    if (isset($seller_id) && $seller_id !== null) {
        $total_query->where('oi.active_status', '!=', 'awaiting');
    }
    $total_query->where('o.is_pos_order', 0);
    if ($sort === 'created_at') {
        $sort = 'o.created_at';
    }

    $total_query->orderBy($sort, $order);

    $orderCount = $total_query->get()->toArray();
    $total = "0";
    foreach ($orderCount as $row) {

        $total = $row->total;
    }
    if (empty($sort)) {
        $sort = 'o.created_at';
    }

    $regularOrderSearchRes = DB::table('orders AS o')
        ->select(
            'o.*',
            'u.username',
            'u.image as user_profile_image',
            'u.country_code',
            'p.name',
            'p.type',
            'p.id as product_id',
            'p.slug',
            'p.download_allowed',
            'p.pickup_location',
            'a.name AS order_recipient_person',
            'pv.special_price',
            'pv.price',
            'oc.delivery_charge AS seller_delivery_charge',
            'oc.promo_discount AS seller_promo_discount',
            'oi.order_type',
            'sd.user_id as main_seller_id',
        )
        ->leftJoin('users AS u', 'u.id', '=', 'o.user_id')
        ->leftJoin('order_items AS oi', 'o.id', '=', 'oi.order_id')
        ->leftJoin('seller_data AS sd', 'sd.id', '=', 'oi.seller_id')
        ->leftJoin('product_variants AS pv', 'pv.id', '=', 'oi.product_variant_id')
        ->leftJoin('addresses AS a', 'a.id', '=', 'o.address_id')
        ->leftJoin('order_charges AS oc', 'o.id', '=', 'oc.order_id')
        ->leftJoin('products AS p', 'pv.product_id', '=', 'p.id');

    if (isset($store_id) && $store_id != null) {
        $regularOrderSearchRes->where('o.store_id', $store_id);
    }

    if (isset($order_id) && $order_id != null) {
        $regularOrderSearchRes->where('o.id', $order_id);
    }

    if (isset($user_id) && $user_id != null) {
        $regularOrderSearchRes->where('o.user_id', $user_id);
    }

    if (isset($delivery_boy_id) && $delivery_boy_id != null) {
        $regularOrderSearchRes->where('oi.delivery_boy_id', $delivery_boy_id);
    }

    if (isset($seller_id) && $seller_id != null) {
        $regularOrderSearchRes->where(function ($query) use ($seller_id) {
            $query->where('oi.seller_id', $seller_id)
                ->orWhere('oc.seller_id', $seller_id);
        });
    }

    if (isset($start_date) && $start_date != null && isset($end_date) && $end_date != null) {
        $regularOrderSearchRes->whereDate('o.created_at', '>=', $start_date)
            ->whereDate('o.created_at', '<=', $end_date);
    }

    if (!empty($start_date)) {
        $regularOrderSearchRes->whereDate('o.created_at', '>=', $start_date);
    }

    if (!empty($end_date)) {
        $regularOrderSearchRes->whereDate('o.created_at', '<=', $end_date);
    }

    if (isset($order_type) && $order_type != '' && $order_type == 'digital') {

        $regularOrderSearchRes->where('p.type', 'digital_product');
    }

    if (isset($order_type) && $order_type != '' && $order_type == 'simple') {
        $regularOrderSearchRes->where('p.type', '!=', 'digital_product');
    }

    if (isset($status) && !empty($status) && $status != '' && is_array($status) && count($status) > 0) {
        $status = array_map('trim', $status);
        $regularOrderSearchRes->whereIn('oi.active_status', $status);
    }

    if (isset($filters) && !empty($filters)) {
        $regularOrderSearchRes->where(function ($query) use ($filters) {
            foreach ($filters as $column => $value) {
                $query->orWhere($column, 'LIKE', '%' . $value . '%');
            }
        });
    }
    $regularOrderSearchRes->where('o.is_pos_order', 0);
    $regularOrderSearchRes->groupBy('o.id');
    $regularOrderSearchRes->orderBy($sort, $order);
    $regularOrderSearchRes = $regularOrderSearchRes->get();
    $comboOrderSearchRes = DB::table('orders AS o')
        ->select(
            'o.*',
            'u.username',
            'u.image as user_profile_image',
            'u.country_code',
            'a.name AS order_recipient_person',
            'oc.delivery_charge AS seller_delivery_charge',
            'oc.promo_discount AS seller_promo_discount',
            'cp.title as name',
            'cp.id as product_id',
            'cp.product_type as type',
            'cp.download_allowed',
            'cp.pickup_location',
            'cp.special_price',
            'cp.price',
            'cp.slug',
            'oi.order_type',
            'sd.user_id as main_seller_id',
        )
        ->leftJoin('users AS u', 'u.id', '=', 'o.user_id')
        ->leftJoin('order_items AS oi', 'o.id', '=', 'oi.order_id')
        ->leftJoin('seller_data AS sd', 'sd.id', '=', 'oi.seller_id')
        ->leftJoin('combo_products as cp', 'cp.id', '=', 'oi.product_variant_id')
        ->leftJoin('addresses AS a', 'a.id', '=', 'o.address_id')
        ->leftJoin('order_charges AS oc', 'o.id', '=', 'oc.order_id');

    if (isset($store_id) && $store_id != null) {
        $comboOrderSearchRes->where('o.store_id', $store_id);
    }

    if (isset($order_id) && $order_id != null) {
        $comboOrderSearchRes->where('o.id', $order_id);
    }

    if (isset($user_id) && $user_id != null) {
        $comboOrderSearchRes->where('o.user_id', $user_id);
    }

    if (isset($delivery_boy_id) && $delivery_boy_id != null) {
        $comboOrderSearchRes->where('oi.delivery_boy_id', $delivery_boy_id);
    }

    if (isset($seller_id) && $seller_id != null) {
        $comboOrderSearchRes->where(function ($query) use ($seller_id) {
            $query->where('oi.seller_id', $seller_id)
                ->orWhere('oc.seller_id', $seller_id);
        });
    }

    if (isset($start_date) && $start_date != null && isset($end_date) && $end_date != null) {
        $comboOrderSearchRes->whereDate('o.created_at', '>=', $start_date)
            ->whereDate('o.created_at', '<=', $end_date);
    }


    if (!empty($start_date)) {
        $comboOrderSearchRes->whereDate('o.created_at', '>=', $start_date);
    }

    if (!empty($end_date)) {
        $comboOrderSearchRes->whereDate('o.created_at', '<=', $end_date);
    }


    if (isset($order_type) && $order_type != '' && $order_type == 'digital') {
        $comboOrderSearchRes->where('cp.product_type', 'digital_product');
    }

    if (isset($order_type) && $order_type != '' && $order_type == 'simple') {
        $comboOrderSearchRes->where('cp.product_type', '!=', 'digital_product');
    }

    if (isset($status) && !empty($status) && $status != '' && is_array($status) && count($status) > 0) {
        $status = array_map('trim', $status);
        $comboOrderSearchRes->whereIn('oi.active_status', $status);
    }

    if (isset($combo_filters) && !empty($combo_filters)) {
        $comboOrderSearchRes->where(function ($query) use ($combo_filters) {
            foreach ($combo_filters as $column => $value) {
                $query->orWhere($column, 'LIKE', '%' . $value . '%');
            }
        });
    }
    $comboOrderSearchRes->where('o.is_pos_order', 0);
    $comboOrderSearchRes->groupBy('o.id');
    $comboOrderSearchRes->orderBy($sort, $order);


    $comboOrderSearchRes = $comboOrderSearchRes->get();


    $searchRes = $regularOrderSearchRes->merge($comboOrderSearchRes)->unique('id');

    $searchRes = $searchRes->sortBy($sort);
    // Applying limit and offset
    if ($limit != null || $offset != null) {
        $searchRes = $searchRes->slice($offset)->take($limit);
    }

    // Convert the sorted and sliced collection back to array
    $orderDetails = $searchRes->values()->all();
    for ($i = 0; $i < count($orderDetails); $i++) {
        $prCondition = ($user_id != NULL && !empty(trim($user_id)) && is_numeric($user_id))
            ? " pr.user_id = $user_id "
            : "";

        $crCondition = ($user_id != NULL && !empty(trim($user_id)) && is_numeric($user_id))
            ? " cr.user_id = $user_id "
            : "";
        $regularOrderItemData = DB::table('order_items AS oi')
            ->select(
                'oi.*',
                'p.id AS product_id',
                'p.is_cancelable',
                'p.is_attachment_required',
                'p.is_prices_inclusive_tax',
                'p.cancelable_till',
                'p.type AS product_type',
                'p.slug',
                'p.download_allowed',
                'p.download_link',
                'ss.store_name',
                'u.longitude AS seller_longitude',
                'u.mobile AS seller_mobile',
                'u.address AS seller_address',
                'u.latitude AS seller_latitude',
                DB::raw('(SELECT username FROM users WHERE id = oi.delivery_boy_id) AS delivery_boy_name'),
                'ss.store_description',
                'ss.rating AS seller_rating',
                'ss.logo AS seller_profile',
                'ot.courier_agency',
                'ot.tracking_id',
                'ot.awb_code',
                'ot.url',
                // DB::raw('(SELECT username FROM users WHERE id = ' . $orderDetails[$i]->main_seller_id . ') AS seller_name'),
                DB::raw('(SELECT username FROM users WHERE id = ' . (!empty($orderDetails[$i]->main_seller_id) ? $orderDetails[$i]->main_seller_id : '0') . ') AS seller_name'),
                'p.is_returnable',
                'pv.special_price',
                'pv.price AS main_price',
                'p.image',
                'p.name AS product_name',
                'p.pickup_location',
                'pv.weight',
                'p.rating AS product_rating',
                'pr.rating AS user_rating',
                'pr.images AS user_rating_images',
                'pr.title AS user_rating_title',
                'pr.comment AS user_rating_comment',
                'oi.status AS status',
                DB::raw('(SELECT COUNT(id) FROM order_items WHERE order_id = oi.order_id) AS order_counter'),
                DB::raw('(SELECT COUNT(active_status) FROM order_items WHERE active_status = "cancelled" AND order_id = oi.order_id) AS order_cancel_counter'),
                DB::raw('(SELECT COUNT(active_status) FROM order_items WHERE active_status = "returned" AND order_id = oi.order_id) AS order_return_counter')
            )
            ->leftJoin('product_variants AS pv', 'pv.id', '=', 'oi.product_variant_id')
            ->leftJoin('products AS p', 'pv.product_id', '=', 'p.id')
            ->leftJoin('product_ratings AS pr', function ($join) use ($prCondition) {
                $join->on('pv.product_id', '=', 'pr.product_id');
                if (!empty($prCondition)) {
                    $join->whereRaw($prCondition);
                }
            })
            // ->leftJoin('seller_store AS ss', 'ss.seller_id', '=', 'oi.seller_id')
            ->leftJoin('seller_store AS ss', function ($join) {
                $join->on('ss.seller_id', '=', 'oi.seller_id')
                    ->on('ss.store_id', '=', 'oi.store_id');
            })
            ->leftJoin('users AS u', 'u.id', '=', 'ss.user_id')
            ->leftJoin('order_trackings AS ot', 'ot.order_item_id', '=', 'oi.id')
            ->leftJoin('users AS db', 'db.id', '=', 'oi.delivery_boy_id')
            ->leftJoin('users AS s', 's.id', '=', 'oi.seller_id')
            ->where('oi.order_type', 'regular_order')
            ->where('oi.order_id', $orderDetails[$i]->id)
            ->when(isset($seller_id) && $seller_id != null, function ($query) use ($seller_id) {
                $query->where('oi.seller_id', $seller_id)
                    ->where("oi.active_status", "!=", 'awaiting');
            })
            ->when(isset($order_type) && $order_type != '', function ($query) use ($order_type) {
                $query->where("p.type", $order_type == 'digital' ? '=' : '!=', 'digital_product');
            })
            ->when(isset($delivery_boy_id) && $delivery_boy_id != null, function ($query) use ($delivery_boy_id) {
                $query->where('oi.delivery_boy_id', '=', $delivery_boy_id);
            })
            ->when(isset($status) && !empty($status) && is_array($status) && count($status) > 0, function ($query) use ($status) {
                $query->whereIn('oi.active_status', array_map('trim', $status));
            })
            ->groupBy('oi.id')
            ->get();

        // dD($regularOrderItemData->toSql(),$regularOrderItemData->getbindings());

        $comboOrderItemData = DB::table('order_items AS oi')
            ->select(
                'oi.*',
                'cp.id AS product_id',
                'cp.is_cancelable',
                'cp.is_attachment_required',
                'cp.is_prices_inclusive_tax',
                'cp.cancelable_till',
                'cp.product_type',
                'cp.slug',
                'cp.download_allowed',
                'cp.download_link',
                'ss.store_name',
                'u.longitude AS seller_longitude',
                'u.mobile AS seller_mobile',
                'u.address AS seller_address',
                'u.latitude AS seller_latitude',
                DB::raw('(SELECT username FROM users WHERE id = oi.delivery_boy_id) AS delivery_boy_name'),
                'ss.store_description',
                'ss.rating AS seller_rating',
                'ss.logo AS seller_profile',
                'ot.courier_agency',
                'ot.tracking_id',
                'ot.awb_code',
                'ot.url',
                // DB::raw('(SELECT username FROM users WHERE id = ' . $orderDetails[$i]->main_seller_id . ') AS seller_name'),
                DB::raw('(SELECT username FROM users WHERE id = ' . (!empty($orderDetails[$i]->main_seller_id) ? $orderDetails[$i]->main_seller_id : '0') . ') AS seller_name'),
                'cp.is_returnable',
                'cp.special_price',
                'cp.price AS main_price',
                'cp.image',
                'cp.title AS product_name',
                'cp.pickup_location',
                'cp.weight',
                'cp.rating AS product_rating',
                'cr.rating AS user_rating',
                'cr.title AS user_rating_title',
                'cr.images AS user_rating_images',
                'cr.comment AS user_rating_comment',
                'oi.status AS status',
                DB::raw('(SELECT COUNT(id) FROM order_items WHERE order_id = oi.order_id) AS order_counter'),
                DB::raw('(SELECT COUNT(active_status) FROM order_items WHERE active_status = "cancelled" AND order_id = oi.order_id) AS order_cancel_counter'),
                DB::raw('(SELECT COUNT(active_status) FROM order_items WHERE active_status = "returned" AND order_id = oi.order_id) AS order_return_counter')
            )
            ->leftJoin('combo_products AS cp', 'cp.id', '=', 'oi.product_variant_id')
            ->leftJoin('combo_product_ratings AS cr', function ($join) use ($crCondition) {
                $join->on('cp.id', '=', 'cr.product_id');
                if (!empty($crCondition)) {
                    $join->whereRaw($crCondition);
                }
            })
            // ->leftJoin('seller_store AS ss', 'ss.seller_id', '=', 'oi.seller_id')
            ->leftJoin('seller_store AS ss', function ($join) {
                $join->on('ss.seller_id', '=', 'oi.seller_id')
                    ->on('ss.store_id', '=', 'oi.store_id');
            })
            ->leftJoin('order_trackings AS ot', 'ot.order_item_id', '=', 'oi.id')
            ->leftJoin('users AS u', 'u.id', '=', 'oi.user_id')
            ->orWhereIn('oi.order_id', [$orderDetails[$i]->id])
            ->where('oi.order_type', 'combo_order')
            ->when(isset($seller_id) && $seller_id != null, function ($query) use ($seller_id) {
                $query->where('oi.seller_id', $seller_id);
                $query->where("oi.active_status", "!=", 'awaiting');
            })
            ->when(isset($order_type) && $order_type != '' && $order_type == 'digital', function ($query) {
                $query->where("cp.product_type", '=', 'digital_product');
            })
            ->when(isset($order_type) && $order_type != '' && $order_type == 'simple', function ($query) {
                $query->where("cp.product_type", '!=', 'digital_product');
            })
            ->when(isset($delivery_boy_id) && $delivery_boy_id != null, function ($query) use ($delivery_boy_id) {
                $query->where('oi.delivery_boy_id', '=', $delivery_boy_id);
            })
            ->when(isset($status) && !empty($status) && $status != '' && is_array($status) && count($status) > 0, function ($query) use ($status) {
                $status = array_map('trim', $status);
                $query->whereIn('oi.active_status', $status);
            })
            ->groupBy('oi.id')
            ->get();


        $orderItemData = $regularOrderItemData->merge($comboOrderItemData);
        // dd($orderItemData);
        //get return request data
        $return_request = fetchDetails('return_requests', ['user_id' => $user_id]);

        if ($orderDetails[$i]->payment_method == "bank_transfer") {
            $bankTransfer = fetchDetails('order_bank_transfers', ['order_id' => $orderDetails[$i]->id], ['attachments', 'id', 'status']);
            $bankTransfer = collect($bankTransfer); // convert array to collection because laravel map function is expecting a collection
            if (!$bankTransfer->isEmpty()) {

                $bankTransfer = $bankTransfer->map(function ($attachment) {

                    return [
                        'id' => $attachment->id,
                        'attachment' => asset($attachment->attachments),
                        'banktransfer_status' => $attachment->status,
                    ];
                });
            }
        }

        $orderDetails[$i]->latitude = (isset($orderDetails[$i]->latitude) && !empty($orderDetails[$i]->latitude)) ? $orderDetails[$i]->latitude : "";
        $orderDetails[$i]->longitude = (isset($orderDetails[$i]->longitude) && !empty($orderDetails[$i]->longitude)) ? $orderDetails[$i]->longitude : "";
        $orderDetails[$i]->order_recipient_person = (isset($orderDetails[$i]->order_recipient_person) && !empty($orderDetails[$i]->order_recipient_person)) ? $orderDetails[$i]->order_recipient_person : "";
        $orderDetails[$i]->attachments = (isset($bankTransfer) && !empty($bankTransfer)) ? $bankTransfer : [];
        $orderDetails[$i]->notes = (isset($orderDetails[$i]->notes) && !empty($orderDetails[$i]->notes)) ? $orderDetails[$i]->notes : "";
        $orderDetails[$i]->payment_method = ($orderDetails[$i]->payment_method == 'bank_transfer') ? ucwords(str_replace('_', " ", $orderDetails[$i]->payment_method)) : $orderDetails[$i]->payment_method;
        $orderDetails[$i]->courier_agency = "";
        $orderDetails[$i]->tracking_id = "";
        $orderDetails[$i]->url = "";

        if (isset($orderDetails[$i]->address_id) && $orderDetails[$i]->address_id != "" && $orderDetails[$i]->address_id != null) {
            $city_id = fetchDetails('addresses', ['id' => $orderDetails[$i]->address_id], 'city_id');
            $city_id = $city_id[0]->city_id ?? [];
        } else {
            $city_id = [];
        }

        $orderDetails[$i]->is_shiprocket_order = (isset($city_id) && $city_id == 0) ? 1 : 0;

        if (isset($seller_id) && !empty($seller_id)) {
            if (isset($orderDetails[$i]->seller_delivery_charge)) {
                $orderDetails[$i]->delivery_charge = $orderDetails[$i]->seller_delivery_charge;
            } else {
                $orderDetails[$i]->delivery_charge = $orderDetails[$i]->delivery_charge;
            }
        } else {
            $orderDetails[$i]->delivery_charge = $orderDetails[$i]->delivery_charge;
        }

        if (isset($orderDetails[$i]->seller_promo_dicount)) {
            $orderDetails[$i]->promo_discount = $orderDetails[$i]->seller_promo_dicount;
        } else {
            $orderDetails[$i]->promo_discount = $orderDetails[$i]->promo_discount;
        }
        $returnable_count = 0;
        $cancelable_count = 0;
        $already_returned_count = 0;
        $already_cancelled_count = 0;
        $return_request_submitted_count = 0;
        $total_tax_percent = $total_tax_amount = $item_subtotal = 0;


        for ($k = 0; $k < count($orderItemData); $k++) {
            // dd($orderItemData[$k]->product_id);
            if ($orderItemData[$k]->order_type == 'regular_order') {
                // Get name from products table
                $orderItemData[$k]->name = getDynamicTranslation('products', 'name', $orderItemData[$k]->product_id, $language_code);
                $orderItemData[$k]->product_name = getDynamicTranslation('products', 'name', $orderItemData[$k]->product_id, $language_code);
            } else {
                // Get name from combo_products table
                $orderItemData[$k]->name = getDynamicTranslation('combo_products', 'title', $orderItemData[$k]->product_id, $language_code);
                $orderItemData[$k]->product_name = getDynamicTranslation('combo_products', 'title', $orderItemData[$k]->product_id, $language_code);
            }
            $download_allowed[] = isset($orderItemData[$k]->download_allowed) ? intval($orderItemData[$k]->download_allowed) : 0;
            if (isset($orderItemData[$k]->quantity) && $orderItemData[$k]->quantity != 0) {
                $price = $orderItemData[$k]->special_price != '' && $orderItemData[$k]->special_price != null && $orderItemData[$k]->special_price > 0 && $orderItemData[$k]->special_price < $orderItemData[$k]->main_price ? $orderItemData[$k]->special_price : $orderItemData[$k]->main_price;
                $amount = $orderItemData[$k]->quantity * $price;
            }
            // dd($orderItemData[$k]->main_price);
            if (!empty($orderItemData)) {
                $user_rating_images = json_decode($orderItemData[$k]->user_rating_images, true);
                $orderItemData[$k]->user_rating_images = array();

                if (!empty($user_rating_images)) {
                    $orderItemData[$k]->user_rating_images = array_map(function ($image) {
                        return getImageUrl($image, "", "", 'image');
                    }, $user_rating_images);
                }

                if (isset($orderItemData[$k]->is_prices_inclusive_tax) && $orderItemData[$k]->is_prices_inclusive_tax == 1) {
                    $price_tax_amount = $price - ($price * (100 / (100 + $orderItemData[$k]->tax_percent)));
                } else {
                    $price_tax_amount = $price * ($orderItemData[$k]->tax_percent / 100);
                }

                $orderItemData[$k]->is_cancelable = intval($orderItemData[$k]->is_cancelable);
                $orderItemData[$k]->is_attachment_required = intval($orderItemData[$k]->is_attachment_required);
                $orderItemData[$k]->tax_amount = isset($price_tax_amount) && !empty($price_tax_amount) ? (float) number_format($price_tax_amount, 2) : 0.00;
                $orderItemData[$k]->net_amount = $orderItemData[$k]->price - $orderItemData[$k]->tax_amount;
                $item_subtotal += $orderItemData[$k]->sub_total;
                $orderItemData[$k]->seller_name = (!empty($orderItemData[$k]->seller_name)) ? $orderItemData[$k]->seller_name : '';
                $orderItemData[$k]->awb_code = isset($orderItemData[$k]->awb_code) && !empty($orderItemData[$k]->awb_code) && $orderItemData[$k]->awb_code != 'NULL' ? $orderItemData[$k]->awb_code : '';
                $orderItemData[$k]->store_description = (!empty($orderItemData[$k]->store_description)) ? $orderItemData[$k]->store_description : '';
                $orderItemData[$k]->seller_rating = (!empty($orderItemData[$k]->seller_rating)) ? number_format($orderItemData[$k]->seller_rating, 1) : "0";
                $orderItemData[$k]->seller_profile = (!empty($orderItemData[$k]->seller_profile)) ? getImageUrl($orderItemData[$k]->seller_profile, "", "", 'image') : '';
                $orderItemData[$k]->seller_latitude = (isset($orderItemData[$k]->seller_latitude) && !empty($orderItemData[$k]->seller_latitude)) ? $orderItemData[$k]->seller_latitude : '';
                $orderItemData[$k]->seller_longitude = (isset($orderItemData[$k]->seller_longitude) && !empty($orderItemData[$k]->seller_longitude)) ? $orderItemData[$k]->seller_longitude : '';
                $orderItemData[$k]->seller_address = (isset($orderItemData[$k]->seller_address) && !empty($orderItemData[$k]->seller_address)) ? $orderItemData[$k]->seller_address : '';
                $orderItemData[$k]->seller_mobile = (isset($orderItemData[$k]->seller_mobile) && !empty($orderItemData[$k]->seller_mobile)) ? $orderItemData[$k]->seller_mobile : '';
                $orderItemData[$k]->attachment = (isset($orderItemData[$k]->attachment) && !empty($orderItemData[$k]->attachment)) ? asset('/storage/' . $orderItemData[$k]->attachment) : '';

                if (isset($seller_id) && $seller_id != null) {
                    $orderItemData[$k]->otp = (getSellerPermission($orderItemData[$k]->seller_id, $store_id, "view_order_otp")) ? $orderItemData[$k]->otp : "0";
                }
                $orderItemData[$k]->pickup_location = isset($orderItemData[$k]->pickup_location) && !empty($orderItemData[$k]->pickup_location) && $orderItemData[$k]->pickup_location != 'NULL' ? $orderItemData[$k]->pickup_location : '';
                $orderItemData[$k]->hash_link = isset($orderItemData[$k]->hash_link) && !empty($orderItemData[$k]->hash_link) && $orderItemData[$k]->hash_link != 'NULL' ? asset('storage' . $orderItemData[$k]->hash_link) : '';
                $varaint_data = getVariantsValuesById($orderItemData[$k]->product_variant_id);

                $orderItemData[$k]->varaint_ids = (!empty($varaint_data)) ? $varaint_data[0]['variant_ids'] : '';
                $orderItemData[$k]->variant_values = (!empty($varaint_data)) ? $varaint_data[0]['variant_values'] : '';
                $orderItemData[$k]->attr_name = (!empty($varaint_data)) ? $varaint_data[0]['attr_name'] : '';
                $orderItemData[$k]->product_rating = (!empty($orderItemData[$k]->product_rating)) ? number_format($orderItemData[$k]->product_rating, 1) : "0";
                $orderItemData[$k]->name = (!empty($orderItemData[$k]->name)) ? $orderItemData[$k]->name : $orderItemData[$k]->product_name;
                $orderItemData[$k]->variant_values = (!empty($orderItemData[$k]->variant_values)) ? $orderItemData[$k]->variant_values : $orderItemData[$k]->variant_values;
                $orderItemData[$k]->user_rating = (!empty($orderItemData[$k]->user_rating)) ? $orderItemData[$k]->user_rating : '0';
                $orderItemData[$k]->user_rating_comment = (!empty($orderItemData[$k]->user_rating_comment)) ? $orderItemData[$k]->user_rating_comment : '';
                $orderItemData[$k]->status = json_decode($orderItemData[$k]->status);

                if (!in_array($orderItemData[$k]->active_status, ['returned', 'cancelled'])) {
                    $total_tax_percent = $total_tax_percent + $orderItemData[$k]->tax_percent;
                    $total_tax_amount =  $orderItemData[$k]->tax_amount * $orderItemData[$k]->quantity;
                }

                $orderItemData[$k]->image_sm = (empty($orderItemData[$k]->image) || file_exists(public_path(config('constants.MEDIA_PATH') . $orderItemData[$k]->image)) == FALSE) ? str_replace('///', '/', getImageUrl('', '', '', 'image', 'NO_IMAGE')) : str_replace('///', '/', getImageUrl($orderItemData[$k]->image, 'thumb', 'sm'));
                $orderItemData[$k]->image_md = (empty($orderItemData[$k]->image) || file_exists(public_path(config('constants.MEDIA_PATH') . $orderItemData[$k]->image)) == FALSE) ? str_replace('///', '/', getImageUrl('', '', '', 'image', 'NO_IMAGE')) : str_replace('///', '/', getImageUrl($orderItemData[$k]->image, 'thumb', 'md'));
                $orderItemData[$k]->image = (empty($orderItemData[$k]->image) || file_exists(public_path(config('constants.MEDIA_PATH') . $orderItemData[$k]->image)) == FALSE) ? str_replace('///', '/', getImageUrl('', '', '', 'image', 'NO_IMAGE')) : str_replace('///', '/', getImageUrl($orderItemData[$k]->image));
                $orderItemData[$k]->is_already_returned = ($orderItemData[$k]->active_status == 'returned') ? '1' : '0';
                $orderItemData[$k]->is_already_cancelled = ($orderItemData[$k]->active_status == 'cancelled') ? '1' : '0';

                $return_request_key = array_search($orderItemData[$k]->id, array_column($return_request, 'order_item_id'));

                if ($return_request_key !== false) {
                    $orderItemData[$k]->return_request_submitted = $return_request[$return_request_key]->status;

                    if ($orderItemData[$k]->return_request_submitted == '1') {
                        $return_request_submitted_count += $orderItemData[$k]->return_request_submitted;
                    }
                } else {
                    $orderItemData[$k]->return_request_submitted = '';
                    $return_request_submitted_count = null;
                }

                $orderItemData[$k]->courier_agency = (isset($orderItemData[$k]->courier_agency) && !empty($orderItemData[$k]->courier_agency)) ? $orderItemData[$k]->courier_agency : "";
                $orderItemData[$k]->tracking_id = (isset($orderItemData[$k]->tracking_id) && !empty($orderItemData[$k]->tracking_id)) ? $orderItemData[$k]->tracking_id : "";
                $orderItemData[$k]->url = (isset($orderItemData[$k]->url) && !empty($orderItemData[$k]->url)) ? $orderItemData[$k]->url : "";
                $orderItemData[$k]->shiprocket_order_tracking_url = (isset($orderItemData[$k]->awb_code) && !empty($orderItemData[$k]->awb_code) && $orderItemData[$k]->awb_code != '' && $orderItemData[$k]->awb_code != null) ? "https://shiprocket.co/tracking/" . $orderItemData[$k]->awb_code : "";
                $orderItemData[$k]->deliver_by = (isset($orderItemData[$k]->delivery_boy_name) && !empty($orderItemData[$k]->delivery_boy_name)) ? $orderItemData[$k]->delivery_boy_name : "";
                $orderItemData[$k]->delivery_boy_id = (isset($orderItemData[$k]->delivery_boy_id) && !empty($orderItemData[$k]->delivery_boy_id)) ? $orderItemData[$k]->delivery_boy_id : "";
                $orderItemData[$k]->discounted_price = (isset($orderItemData[$k]->discounted_price) && !empty($orderItemData[$k]->discounted_price)) ? $orderItemData[$k]->discounted_price : "";
                $orderItemData[$k]->delivery_boy_name = (isset($orderItemData[$k]->delivery_boy_name) && !empty($orderItemData[$k]->delivery_boy_name)) ? $orderItemData[$k]->delivery_boy_name : "";

                if (($orderDetails[$i]->type == 'digital_product' && in_array(0, $download_allowed)) || ($orderDetails[$i]->type != 'digital_product' && in_array(0, $download_allowed))) {
                    $orderDetails[$i]->download_allowed = 0;
                    $orderItemData[$k]->download_link = '';
                    $orderItemData[$k]->download_allowed = 0;
                } else {
                    $orderDetails[$i]->download_allowed = 1;
                    $orderItemData[$k]->download_link = asset('storage' . $orderItemData[$k]->download_link);
                    $orderItemData[$k]->download_allowed = 1;
                }
                $orderItemData[$k]->email = (isset($orderItemData[$k]->email) && !empty($orderItemData[$k]->email) ? $orderItemData[$k]->email : '');

                $returnable_count += $orderItemData[$k]->is_returnable;
                $cancelable_count += $orderItemData[$k]->is_cancelable;
                $already_returned_count += $orderItemData[$k]->is_already_returned;
                $already_cancelled_count += $orderItemData[$k]->is_already_cancelled;

                $delivery_date = isset($orderItemData[$k]->status[3][1]) ? $orderItemData[$k]->status[3][1] : '';
                $settings = getSettings('system_settings', true, true);
                $settings = json_decode($settings, true);
                $timestemp = strtotime($delivery_date);
                $today = date('Y-m-d');
                $return_till = date('Y-m-d', strtotime($delivery_date . ' + ' . $settings['max_days_to_return_item'] . ' days'));

                // $orderItemData[$k]->is_returnable = isset($delivery_date) && !empty($delivery_date) && ($today < $return_till) ? 1 : 0;
                $orderItemData[$k]->is_returnable = $orderItemData[$k]->is_returnable;
            }
        }
        // dd($item_total);
        // dd($orderDetails[$i]->product_id);
        if ($orderDetails[$i]->order_type == 'regular_order') {
            // Get name from products table
            $orderDetails[$i]->name = getDynamicTranslation('products', 'name', $orderDetails[$i]->product_id, $language_code);
        } else {
            // Get name from combo_products table
            $orderDetails[$i]->name = getDynamicTranslation('combo_products', 'name', $orderDetails[$i]->product_id, $language_code);
        }
        $orderDetails[$i]->delivery_time = (isset($orderDetails[$i]->delivery_time) && !empty($orderDetails[$i]->delivery_time)) ? $orderDetails[$i]->delivery_time : "";
        $orderDetails[$i]->delivery_date = (isset($orderDetails[$i]->delivery_date) && !empty($orderDetails[$i]->delivery_date)) ? $orderDetails[$i]->delivery_date : "";
        $orderDetails[$i]->is_returnable = ($returnable_count >= 1 && isset($delivery_date) && !empty($delivery_date) && $today < $return_till) ? 1 : 0;
        $orderDetails[$i]->is_cancelable = ($cancelable_count >= 1) ? 1 : 0;
        $orderDetails[$i]->is_already_returned = ($already_returned_count == count($orderItemData)) ? '1' : '0';
        $orderDetails[$i]->is_already_cancelled = ($already_cancelled_count == count($orderItemData)) ? '1' : '0';

        $orderDetails[$i]->user_profile_image = getMediaImageUrl($orderDetails[$i]->user_profile_image, 'USER_IMG_PATH');

        if ($return_request_submitted_count == null) {
            $orderDetails[$i]->return_request_submitted = '';
        } else {
            $orderDetails[$i]->return_request_submitted = ($return_request_submitted_count == count($orderItemData)) ? '1' : '0';
        }

        if ((isset($delivery_boy_id) && $delivery_boy_id != null) || (isset($seller_id) && $seller_id != null)) {

            $orderDetails[$i]->total = strval($item_subtotal);
            $orderDetails[$i]->final_total = strval($item_subtotal + $orderDetails[$i]->delivery_charge);

            $orderDetails[$i]->total_payable = strval($item_subtotal + $orderDetails[$i]->delivery_charge - $orderDetails[$i]->promo_discount - $orderDetails[$i]->wallet_balance);
        } else {
            $orderDetails[$i]->total = strval($orderDetails[$i]->total);
        }
        $orderDetails[$i]->item_total = $orderDetails[$i]->total + $orderDetails[$i]->discount;
        $orderDetails[$i]->address = (isset($orderDetails[$i]->address) && !empty($orderDetails[$i]->address)) ? outputEscaping($orderDetails[$i]->address) : "";
        $orderDetails[$i]->username = outputEscaping($orderDetails[$i]->username);
        $orderDetails[$i]->country_code = (isset($orderDetails[$i]->country_code) && !empty($orderDetails[$i]->country_code)) ? $orderDetails[$i]->country_code : '';
        $orderDetails[$i]->total_tax_percent = strval($total_tax_percent);
        $orderDetails[$i]->total_tax_amount = strval($total_tax_amount);
        unset($orderDetails[$i]->main_seller_id);
        if (isset($seller_id) && $seller_id != null) {
            if ($download_invoice == true || $download_invoice == 1) {
            }
        } else {
            if ($download_invoice == true || $download_invoice == 1) {
            }
        }

        if (!empty($orderItemData)) {

            $orderDetails[$i]->order_items = $orderItemData;
        } else {
            $orderDetails[$i]->order_items = [];
        }
    }
    // $collection = collect($orderDetails);
    $filteredOrders = collect($orderDetails)->filter(function ($order) {
        return $order->order_items->isNotEmpty(); // Keep only orders with items
    })->values();
    // dd($filteredOrders);
    $order_data['total'] = $total;
    $order_data['order_data'] = $filteredOrders;
    return $order_data;
}


function setUserReturnRequest($data, $table = 'orders')
{

    if ($table == 'orders') {
        foreach ($data as $row) {
            $requestData = [
                'user_id' => $row['user_id'],
                'product_id' => $row['product_id'],
                'product_variant_id' => $row['product_variant_id'],
                'order_id' => $row['order_id'],
                'order_item_id' => $row['order_item_id']
            ];
            ReturnRequest::create($requestData);
        }
    } else {
        $requestData = [
            'user_id' => $data->user_id,
            'product_id' => $data->product_id,
            'product_variant_id' => $data->product_variant_id,
            'order_id' => $data->order_id,
            'order_item_id' => $data->order_item_id
        ];
        ReturnRequest::create($requestData);
    }
}

function validateOrderStatus($order_ids, $status, $table = 'order_items', $user_id = null, $fromuser = false, $parcel_type = '')
{
    $error = 0;
    $cancelable_till = '';
    $returnable_till = '';
    $is_already_returned = 0;
    $is_already_cancelled = 0;
    $is_returnable = 0;
    $is_cancelable = 0;
    $returnable_count = 0;
    $cancelable_count = 0;
    $return_request = 0;
    $check_status = ['received', 'processed', 'shipped', 'delivered', 'cancelled', 'returned'];
    $user = Auth::user();

    $roleIdsToCheck = [1, 3, 5];


    if (in_array(strtolower(trim($status)), $check_status)) {

        if ($table == 'order_items') {
            $activeStatus = OrderItems::whereIn('id', explode(',', $order_ids))->pluck('active_status')->toArray();

            if (in_array('cancelled', $activeStatus) || in_array('returned', $activeStatus)) {
                $response = [
                    'error' => true,
                    'message' => "You can't update status once an item is cancelled or returned",
                    'data' => [],
                ];

                return $response;
            }
        }
        if ($table == 'parcels') {

            $parcelIds = explode(',', $order_ids);

            $results = DB::table('parcels as p')
                ->leftJoin('parcel_items as pi', 'pi.parcel_id', '=', 'p.id')
                ->whereIn('p.id', $parcelIds)
                ->select('p.active_status', 'pi.order_item_id')
                ->get();

            $orderItemIds = $results->pluck('order_item_id')->toArray();

            $activeStatuses = $results->pluck('active_status')->toArray();

            if (in_array("cancelled", $activeStatuses) || in_array("returned", $activeStatuses)) {
                return [
                    'error' => true,
                    'message' => "You can't update status once item cancelled / returned",
                    'data' => []
                ];
            }

            if (empty($orderItemIds)) {
                return [
                    'error' => true,
                    'message' => "You can't update status. Something went wrong!",
                    'data' => []
                ];
            }
        }

        $query = DB::table('order_items as oi')
            ->select('oi.id as order_item_id', 'oi.user_id', 'oi.product_variant_id', 'oi.order_id');

        if ($parcel_type === 'combo_order') {
            $query->leftJoin('combo_products as cp', 'cp.id', '=', 'oi.product_variant_id')
                ->addSelect('cp.*');
        } else {
            $query->leftJoin('product_variants as pv', 'pv.id', '=', 'oi.product_variant_id')
                ->leftJoin('products as p', 'pv.product_id', '=', 'p.id')
                ->addSelect('p.*', 'pv.*');
        }
        $query->leftJoin('parcel_items as pi', 'pi.order_item_id', '=', 'oi.id')
            ->leftJoin('parcels as pr', 'pr.id', '=', 'pi.parcel_id')
            ->addSelect('pr.active_status', 'pr.status as parcel_status');


        if ($table === 'parcels') {
            $query->addSelect('pr.active_status', 'pr.status as order_item_status')
                ->whereIn('oi.id', $orderItemIds)
                ->groupBy('oi.id');
        } else {
            $query->addSelect('oi.active_status', 'oi.status as order_item_status');
            if ($table === 'orders') {
                $query->where('oi.order_id', $order_ids);
            } else {
                $query->whereIn('oi.id', explode(',', $order_ids));
            }
        }

        $productData = $query->get();

        $priority_status = [
            'received' => 0,
            'processed' => 1,
            'shipped' => 2,
            'delivered' => 3,
            'return_request_pending' => 4,
            'return_request_approved' => 5,
            'return_pickedup' => 8,
            'cancelled' => 6,
            'returned' => 7,
        ];

        $is_posted_status_set = $canceling_delivered_item = $returning_non_delivered_item = false;
        $is_posted_status_set_count = 0;

        for ($i = 0; $i < count($productData); $i++) {
            /* check if there are any products returnable or cancellable products available in the list or not */
            if ($productData[$i]->is_returnable == 1) {
                $returnable_count += 1;
            }
            if ($productData[$i]->is_cancelable == 1) {
                $cancelable_count += 1;
            }

            /* check if the posted status is present in any of the variants */
            $productData[$i]->order_item_status = json_decode($productData[$i]->order_item_status, true);
            $order_item_status = array_column($productData[$i]->order_item_status, '0');
            if (in_array($status, $order_item_status)) {
                $is_posted_status_set_count++;
            }


            /* if all are marked as same as posted status set the flag */
            if ($is_posted_status_set_count == count($productData)) {
                $is_posted_status_set = true;
            }

            /* check if user is cancelling the order after it is delivered */
            if (($status == "cancelled") && (in_array("delivered", $order_item_status) || in_array("returned", $order_item_status))) {
                $canceling_delivered_item = true;
            }

            /* check if user is returning non delivered item */
            if (($status == "returned") && !in_array("delivered", $order_item_status)) {
                $returning_non_delivered_item = true;
            }
        }
        if ($table == 'parcels' && $status == 'returned') {
            $response['error'] = true;
            $response['message'] = "You cannot return Parcel Order!";
            $response['data'] = array();
            return $response;
        }
        if ($is_posted_status_set == true) {
            $response['error'] = true;
            $response['message'] = "Order is already marked as $status. You cannot set it again!";
            $response['data'] = array();
            return $response;
        }

        if ($canceling_delivered_item == true) {
            /* when user is trying cancel delivered order / item */
            $response['error'] = true;
            $response['message'] = "You cannot cancel delivered or returned order / item. You can only return that!";
            $response['data'] = array();
            return $response;
        }
        if ($returning_non_delivered_item == true) {
            /* when user is trying return non delivered order / item */
            $response['error'] = true;
            $response['message'] = "You cannot return a non-delivered order / item. First it has to be marked as delivered and then you can return it!";
            $response['data'] = array();
            return $response;
        }

        $is_returnable = ($returnable_count >= 1) ? 1 : 0;
        $is_cancelable = ($cancelable_count >= 1) ? 1 : 0;

        for ($i = 0; $i < count($productData); $i++) {

            if ($productData[$i]->active_status == 'returned') {
                $error = 1;
                $is_already_returned = 1;
                break;
            }

            if ($productData[$i]->active_status == 'cancelled') {
                $error = 1;
                $is_already_cancelled = 1;
                break;
            }

            if ($status == 'returned' && $productData[$i]->is_returnable == 0) {
                $error = 1;
                break;
            }

            if ($status == 'returned' && $productData[$i]->is_returnable == 1 && $priority_status[$productData[$i]->active_status] < 3) {
                $error = 1;
                $returnable_till = 'delivery';
                break;
            }

            if ($status == 'cancelled' && $productData[$i]->is_cancelable == 1) {
                $max = $priority_status[$productData[$i]->cancelable_till];
                $min = $priority_status[$productData[$i]->active_status];

                if ($min > $max) {
                    $error = 1;
                    $cancelable_till = $productData[$i]->cancelable_till;
                    break;
                }
            }

            if ($status == 'cancelled' && $productData[$i]->is_cancelable == 0) {
                $error = 1;
                break;
            }
        }

        if ($status == 'returned' && $error == 1 && !empty($returnable_till)) {
            return response()->json([
                'error' => true,
                'message' => (count($productData) > 1) ? "One of the order item is not delivered yet!" : "The order item is not delivered yet!",
                'data' => [],
            ]);
        }

        if ($status == 'returned' && $error == 1 && !$user && !$user->roles->whereIn('role_id', $roleIdsToCheck)) {
            return response()->json([
                'error' => true,
                'message' => (count($productData) > 1) ? "One of the order item can't be returned!" : "The order item can't be returned!",
                'data' => $productData,
            ]);
        }

        if ($status == 'cancelled' && $error == 1 && !empty($cancelable_till) && !$user && !$user->roles->whereIn('role_id', $roleIdsToCheck)) {
            return response()->json([
                'error' => true,
                'message' => (count($productData) > 1) ? "One of the order item can be cancelled till " . $cancelable_till . " only" : "The order item can be cancelled till " . $cancelable_till . " only",
                'data' => [],
            ]);
        }

        if ($status == 'cancelled' && $error == 1 && !$user && !$user->roles->whereIn('role_id', $roleIdsToCheck)) {
            return response()->json([
                'error' => true,
                'message' => (count($productData) > 1) ? "One of the order item can't be cancelled!" : "The order item can't be cancelled!",
                'data' => [],
            ]);
        }

        for ($i = 0; $i < count($productData); $i++) {


            if ($status == 'returned' && $productData[$i]->is_returnable == 1 && $error == 0) {
                $error = 1;
                $return_request_flag = 1;

                $return_status = [
                    'is_already_returned' => $is_already_returned,
                    'is_already_cancelled' => $is_already_cancelled,
                    'return_request_submitted' => $return_request,
                    'is_returnable' => $is_returnable,
                    'is_cancelable' => $is_cancelable,
                ];

                if ($fromuser == true || $fromuser == 1) {


                    if ($table == 'order_items') {

                        if (isExist(['user_id' => $productData[$i]->user_id, 'order_item_id' => $productData[$i]->order_item_id, 'order_id' => $productData[$i]->order_id], 'return_requests')) {

                            $response['error'] = true;
                            $response['message'] = "Return request already submitted !";
                            $response['data'] = array();
                            $response['return_status'] = $return_status;
                            return $response;
                        }
                        $request_data_item_data = $productData[$i];
                        setUserReturnRequest($request_data_item_data, $table);
                    } else {
                        for ($j = 0; $j < count($productData); $j++) {
                            if (isExist(['user_id' => $productData[$i]->user_id, 'order_item_id' => $productData[$i]->order_item_id, 'order_id' => $productData[$i]->order_id], 'return_requests')) {

                                $response['error'] = true;
                                $response['message'] = "Return request already submitted !";
                                $response['data'] = array();
                                $response['return_status'] = $return_status;
                                return $response;
                            }
                        }
                        $request_data_overall_item_data = $productData[$i];
                        setUserReturnRequest($request_data_overall_item_data, $table);
                    }
                }

                $response['error'] = false;
                $response['message'] = "Return request submitted successfully !";
                $response['return_request_flag'] = 1;
                $response['data'] = array();
                return $response;
            }
        }
        $response['error'] = false;
        $response['message'] = " ";
        $response['data'] = array();

        return $response;
    } else {
        $response['error'] = true;
        $response['message'] = "Invalid Status Passed";
        $response['data'] = array();
        return $response;
    }
}

function update_order_item($id, $status, $return_request = 0, $fromapp = false)
{
    if ($return_request == 0) {
        $res = validateOrderStatus($id, $status, 'order_items', '', true);

        if ($res['error']) {
            $response['error'] = (isset($res['return_request_flag'])) ? false : true;
            $response['message'] = $res['message'];
            $response['data'] = $res['data'];
            return $response;
        }
    }
    if ($fromapp == true) {
        if ($status == 'returned') {
            $status = 'return_request_pending';
        }
    }
    $order_item_details = fetchDetails('order_items', ['id' => $id], ['order_id', 'seller_id']);
    $order_details = fetchOrders($order_item_details[0]->order_id);
    $order_tracking_data = getShipmentId($id, $order_item_details[0]->order_id);
    if (!empty($order_details) && !empty($order_item_details)) {
        $order_details = $order_details['order_data'];
        $order_items_details = $order_details[0]->order_items;
        $key = array_search($id, array_column($order_items_details->toArray(), 'id'));
        $order_id = $order_details[0]->id;
        $store_id = $order_details[0]->store_id;
        $user_id = $order_details[0]->user_id;
        $order_counter = $order_items_details[$key]->order_counter;
        $order_cancel_counter = $order_items_details[$key]->order_cancel_counter;
        $order_return_counter = $order_items_details[$key]->order_return_counter;
        $seller_id = Seller::where('id', $order_item_details[0]->seller_id)->value('user_id');
        $user_res = fetchDetails('users', ['id' => $seller_id], ['fcm_id', 'username']);

        $fcm_ids = array();
        $results = UserFcm::join('users', 'user_fcm.user_id', '=', 'users.id')
            ->where('user_fcm.user_id', $seller_id)
            ->where('users.is_notification_on', 1)
            ->select('user_fcm.fcm_id')
            ->get();
        foreach ($results as $result) {
            if (is_object($result)) {
                $fcm_ids[] = $result->fcm_id;
            }
        }
        $registrationIDs_chunks = array_chunk($fcm_ids, 1000);
        if ($order_items_details[$key]->active_status == 'cancelled') {
            $response['error'] = true;
            $response['message'] = 'Status Already Updated';
            $response['data'] = array();
            return $response;
        }
        if (updateOrder(['status' => $status], ['id' => $id], true, 'order_items')) {
            updateOrder(['active_status' => $status], ['id' => $id], false, 'order_items');

            //send notification while order cancelled
            if ($status == 'cancelled') {
                $fcm_admin_subject = 'Order cancelled';
                $fcm_admin_msg = 'Hello ' . $user_res[0]->username . 'order of order item id ' . $id . ' is cancelled.';
                if (!empty($fcm_ids)) {
                    $fcmMsg = array(
                        'title' => "$fcm_admin_subject",
                        'body' => "$fcm_admin_msg",
                        'type' => "place_order",
                        'store_id' => "$store_id",
                        'content_available' => true
                    );
                    sendNotification('', $registrationIDs_chunks, $fcmMsg,);
                }
                if (isset($order_tracking_data) && !empty($order_tracking_data) && $order_tracking_data != null) {
                    cancel_shiprocket_order($order_tracking_data[0]['shiprocket_order_id']);
                }
            }
        }

        $response['error'] = false;
        $response['message'] = 'Status Updated Successfully';
        $response['data'] = array();
        return $response;
    }
}

function updateOrder($set, $where, $isJson = false, $table = 'order_items', $fromUser = false, $is_digital_product = 0)
{

    if ($isJson == true) {
        $field = array_keys($set);
        $currentStatus = $set[$field[0]];

        $res = fetchDetails($table, $where, '*');
        if ($is_digital_product == 1) {
            $priorityStatus = [
                'received' => 0,
                'delivered' => 1,
            ];
        } else {
            if ($set['status'] != 'return_request_decline') {
                $priorityStatus = [
                    'received' => 0,
                    'processed' => 1,
                    'shipped' => 2,
                    'delivered' => 3,
                    'return_request_pending' => 4,
                    'return_request_approved' => 5,
                    'return_pickedup' => 8,
                    'cancelled' => 6,
                    'returned' => 7,
                ];
            } else {
                $priorityStatus = [
                    'received' => 0,
                    'processed' => 1,
                    'shipped' => 2,
                    'delivered' => 3,
                    'return_request_pending' => 4,
                    'return_request_decline' => 5,
                    'return_pickedup' => 8,
                    'cancelled' => 6,
                    'returned' => 7,
                ];
            }
        }
        if (count($res) >= 1) {
            $i = 0;
            foreach ($res as $row) {
                $set = [];
                $temp = [];
                $activeStatus = [];
                $activeStatus[$i] = json_decode($row->status, true);
                $currentSelectedStatus = end($activeStatus[$i]);
                $temp = $activeStatus[$i];
                $cnt = count($temp);
                $originalDateTime = now();
                $carbonDateTime = Carbon::parse($originalDateTime);
                $currTime = $carbonDateTime->format('d-m-Y h:i:sa');
                $minValue = (!empty($temp) && $temp[0][0] != 'awaiting') ? $priorityStatus[$currentSelectedStatus[0]] : -1;
                $maxValue = $priorityStatus[$currentStatus];
                if ($currentStatus == 'returned' || $currentStatus == 'cancelled') {
                    $temp[$cnt] = [$currentStatus, $currTime];
                } else {
                    foreach ($priorityStatus as $key => $value) {
                        if ($value > $minValue && $value <= $maxValue) {
                            $temp[$cnt] = [$key, $currTime];
                        }
                        ++$cnt;
                    }
                }
                $set = [$field[0] => json_encode(array_values($temp))];
                DB::beginTransaction();
                try {
                    DB::table($table)
                        ->where('id', $row->id)
                        ->update($set);

                    DB::commit();
                    $response = true;
                } catch (\Exception $e) {
                    DB::rollback();
                    $response = false;
                }

                // Additional code for commission and transactions can be added here
                if ($currentStatus == 'delivered') {
                    if ($table == "parcels") {
                        $parcel_items = fetchDetails('parcel_items', ['parcel_id' => $where['id']]);
                        $order_item_ids = array_map(function ($item) {
                            return $item->order_item_id;
                        }, $parcel_items);
                        $order_item_ids = $order_item_ids ?? [];
                        $order = fetchDetails('order_items', '', ['delivery_boy_id', 'order_id', 'sub_total', 'id', 'seller_id'], '', '', '', '', 'id', $order_item_ids);
                    } else {
                        $order = OrderItems::where($where)->first(['delivery_boy_id', 'order_id', 'sub_total']);
                    }
                    $order_id = $row->order_id;
                    $total_order_items = DB::table('order_items as oi')->where('order_id', $order_id)
                        ->selectRaw('COUNT(oi.id) as total')->get()->toarray();
                    $total_order_items = $total_order_items[0]->total > 0 ? $total_order_items[0]->total : 1;
                    $order_final_total = fetchDetails('orders', ['id' => $order_id], ['delivery_charge', 'total', 'final_total', 'payment_method', 'promo_discount', 'is_cod_collected', 'wallet_balance']);
                    $delivery_charges = intval($order_final_total[0]->delivery_charge);
                    $order_item_delivery_charges = $delivery_charges / $total_order_items * $total_order_items;
                    if ($table == "parcels") {
                        if (!empty($order)) {
                            $deliveryBoyId = isset($order['delivery_boy_id']) ? $order['delivery_boy_id'] : $order[0]->delivery_boy_id;
                            $subtotal_of_products = $order_final_total[0]->total;
                            $total = 0;
                            if ($deliveryBoyId > 0) {
                                $commission = 0;
                                $deliveryBoy = User::where('id', $deliveryBoyId)->first(['bonus', 'bonus_type']);
                                if (!empty($deliveryBoy)) {
                                    foreach ($order as $value) {
                                        $finalTotal = $total += $value->sub_total;
                                    }
                                    $settings = getSettings('system_settings', true, true);
                                    $settings = json_decode($settings, true);

                                    // Get bonus_type
                                    if ($deliveryBoy->bonus_type == "fixed_amount_per_order") {
                                        $commission = (isset($deliveryBoy->bonus) && $deliveryBoy->bonus > 0) ? $deliveryBoy->bonus : $settings['delivery_boy_bonus_percentage'];
                                    }

                                    if ($deliveryBoy->bonus_type == "percentage_per_order_item") {
                                        $commission = (isset($deliveryBoy->bonus) && $deliveryBoy->bonus > 0) ? $deliveryBoy->bonus : $settings['delivery_boy_bonus_percentage'];
                                        $commission = $finalTotal * ($commission / 100);

                                        if ($commission > $finalTotal) {
                                            $commission = $finalTotal;
                                        }
                                    }
                                }
                                if ($total > 0 && $subtotal_of_products > 0) {
                                    $total_discount_percentage = calculatePercentage($total, $subtotal_of_products);
                                }
                                $wallet_balance = $order_final_total[0]->wallet_balance ?? 0;
                                $promo_discount = $order_final_total[0]->promo_discount ?? 0;

                                if ($promo_discount != 0) {
                                    $promo_discount = calculatePrice($total_discount_percentage, $promo_discount);
                                }
                                if ($wallet_balance != 0) {
                                    $wallet_balance = calculatePrice($total_discount_percentage, $wallet_balance);
                                }
                                $total_amount_payable = intval($finalTotal + $order_item_delivery_charges - $wallet_balance - $promo_discount);
                                // Commission must be greater than zero to be credited into the account
                                if ($commission > 0) {
                                    $transactionData = [
                                        'transaction_type' => "wallet",
                                        'user_id' => $deliveryBoyId,
                                        'order_id' => $order[0]->order_id,
                                        'type' => "credit",
                                        'txn_id' => "",
                                        'amount' => $commission,
                                        'status' => "success",
                                        'message' => "Order delivery bonus for order item ID: #" . $order[0]->id,
                                    ];
                                    Transaction::create($transactionData);
                                    updateBalance($commission, $deliveryBoyId, 'add');
                                }
                                if (strtolower($order_final_total[0]->payment_method) == "cod") {
                                    $transactionData = [
                                        'transaction_type' => "transaction",
                                        'user_id' => $deliveryBoyId,
                                        'order_id' => $row->order_id,
                                        'type' => "delivery_boy_cash",
                                        'txn_id' => "",
                                        'amount' => $total_amount_payable,
                                        'status' => "1",
                                        'message' => "Delivery boy collected COD",
                                    ];

                                    Transaction::create($transactionData);
                                    updateCashReceived($finalTotal, $deliveryBoyId, "add");
                                }
                            }
                        }
                    }
                }

                ++$i;
            }
            return $response;
        }
    } else {
        DB::beginTransaction();
        try {
            DB::table($table)
                ->where($where)
                ->update($set);

            DB::commit();
            $response = true;
        } catch (\Exception $e) {
            DB::rollback();
            $response = false;
        }
        return $response;
    }
}

function updateBalance($amount, $deliveryBoyId, $action)
{
    /**
     * action = add / deduct
     */

    $user = User::find($deliveryBoyId);

    if (!$user) {
        return false; // User not found
    }

    if ($action == "add") {
        $user->balance += $amount;
    } else {
        $user->balance -= $amount;
    }
    return $user->save();
}

function updateCashReceived($amount, $deliveryBoyId, $action)
{
    /**
     * action = add / deduct
     */

    $user = User::find($deliveryBoyId);
    if (!$user) {
        return false; // User not found
    }

    if ($action == "add") {
        $user->cash_received += $amount;
    } elseif ($action == "deduct") {
        $user->cash_received -= $amount;
    }
    return $user->save();
}

function getShipmentId($itemId, $orderId)
{
    $query = OrderTracking::select('*')
        ->where('order_id', $orderId)
        ->whereRaw('FIND_IN_SET(?, order_item_id) <> 0', [$itemId])
        ->get()
        ->toArray();

    return !empty($query) ? $query : false;
}

function process_refund($id, $status, $type = 'order_items')
{
    $possibleStatus = ["cancelled", "returned"];

    if (!in_array($status, $possibleStatus)) {
        $response = [
            'error' => true,
            'message' => 'Refund cannot be processed. Invalid status',
            'data' => [],
        ];

        return $response;
    }
    if ($type == 'order_items') {
        /* fetch order_id */
        $order_item_details = fetchDetails('order_items', ['id' => $id], ['order_id', 'id', 'seller_id', 'sub_total', 'quantity', 'status', 'store_id']);


        /* fetch order and its complete details with order_items */
        $order_id = $order_item_details[0]->order_id;
        $seller_id = $order_item_details[0]->seller_id;
        $store_id = $order_item_details[0]->store_id;
        $system_settings = getSettings('system_settings', true);
        $system_settings = json_decode($system_settings, true);
        $order_item_data = fetchDetails('order_charges', ['order_id' => $order_id, 'seller_id' => $seller_id], 'sub_total');
        $order_total = 0.00;
        if (isset($order_item_data) && !empty($order_item_data)) {
            $order_total = floatval($order_item_data[0]->sub_total);
        }
        $order_item_total = $order_item_details[0]->sub_total;

        $order_details = fetchOrders($order_id);
        $order_details = $order_details['order_data'];

        $order_items_details = $order_details[0]->order_items;

        $key = array_search($id, array_column($order_items_details->toArray(), 'id'));

        $current_price = $order_items_details[$key]->sub_total;
        $order_item_id = $order_items_details[$key]->id;
        $currency = (isset($system_settings['currency']) && !empty($system_settings['currency'])) ? $system_settings['currency'] : '';
        $payment_method = $order_details[0]->payment_method;

        //check for order active status
        $active_status = json_decode($order_item_details[0]->status, true);

        if (strtolower($payment_method) != 'wallet') {
            if ($active_status[1][0] == 'cancelled' && $active_status[0][0] == 'awaiting') {
                $response['error'] = true;
                $response['message'] = 'Refund cannot be processed.';
                $response['data'] = array();
                return $response;
            }
        }

        $total = $order_details[0]->total;
        $is_delivery_charge_returnable = isset($order_details[0]->is_delivery_charge_returnable) && $order_details[0]->is_delivery_charge_returnable == 1 ? '1' : '0';
        $delivery_charge = (isset($order_details[0]->delivery_charge) && !empty($order_details[0]->delivery_charge)) ? $order_details[0]->delivery_charge : 0;
        $promo_code = $order_details[0]->promo_code ?? "";
        $promo_discount = $order_details[0]->promo_discount;
        $final_total = $order_details[0]->final_total;
        $wallet_balance = $order_details[0]->wallet_balance;
        $total_payable = $order_details[0]->total_payable;
        $user_id = $order_details[0]->user_id;

        $order_items_count = $order_details[0]->order_items[0]->order_counter;
        $cancelled_items_count = $order_details[0]->order_items[0]->order_cancel_counter;
        $returned_items_count = $order_details[0]->order_items[0]->order_return_counter;
        $last_item = 0;


        $fcm_ids = array();
        $results = UserFcm::join('users', 'user_fcm.user_id', '=', 'users.id')
            ->where('user_fcm.user_id', $user_id)
            ->where('users.is_notification_on', 1)
            ->select('user_fcm.fcm_id')
            ->get();
        foreach ($results as $result) {
            if (is_object($result)) {
                $fcm_ids[] = $result->fcm_id;
            }
        }

        if (($cancelled_items_count + $returned_items_count) == $order_items_count) {
            $last_item = 1;
        }
        $new_total = $total - $current_price;

        /* recalculate delivery charge */
        $new_delivery_charge = ($new_total > 0) ? recalulateDeliveryCharge($order_details[0]->address_id, $new_total, $delivery_charge, $store_id) : 0;

        /* recalculate promo discount */

        $new_promo_discount = recalculatePromoDiscount($promo_code, $promo_discount, $user_id, $new_total, $payment_method, $new_delivery_charge, $wallet_balance);

        $new_final_total = $new_total + $new_delivery_charge - $new_promo_discount;
        $bank_receipt = fetchDetails('order_bank_transfers', ['order_id' => $order_item_details[0]->order_id]);
        $bank_receipt_status = (isset($bank_receipt[0]->status)) ? $bank_receipt[0]->status : "";

        /* find returnable_amount, new_wallet_balance
        condition : 1
        */
        if (strtolower($payment_method) == 'cod' || $payment_method == 'Bank Transfer') {
            /* when payment method is COD or Bank Transfer and payment is not yet done */
            if (strtolower($payment_method) == 'cod' || ($payment_method == 'Bank Transfer' && (empty($bank_receipt_status) || $bank_receipt_status == "0" || $bank_receipt_status == "1"))) {
                $returnable_amount = ($wallet_balance <= $current_price) ? $wallet_balance : (($wallet_balance > 0) ? $current_price : 0);
                $returnable_amount = ($promo_discount != $new_promo_discount && $last_item == 0) ? $returnable_amount - $promo_discount + $new_promo_discount : $returnable_amount; /* if the new promo discount changed then adjust that here */
                $returnable_amount = ($returnable_amount < 0) ? 0 : $returnable_amount;

                /* if returnable_amount is 0 then don't change he wallet_balance */
                $new_wallet_balance = ($returnable_amount > 0) ? (($wallet_balance <= $current_price) ? 0 : (($wallet_balance - $current_price > 0) ? $wallet_balance - $current_price : 0)) : $wallet_balance;
            }
        }
        /* if it is any other payment method or bank transfer with accepted receipts then payment is already done
        condition : 2
        */
        if ((strtolower($payment_method) != 'cod' && $payment_method != 'Bank Transfer') || ($payment_method == 'Bank Transfer' && $bank_receipt_status == 2)) {
            $returnable_amount = $current_price;
            $returnable_amount = ($promo_discount != $new_promo_discount) ? $returnable_amount - $promo_discount + $new_promo_discount : $returnable_amount;
            $returnable_amount = ($last_item == 1 && $is_delivery_charge_returnable == 1) ? $returnable_amount + $delivery_charge : $returnable_amount;  /* if its the last item getting cancelled then check if we have to return delivery charge or not */
            $returnable_amount = ($returnable_amount < 0) ? 0 : $returnable_amount;
            $new_wallet_balance = ($last_item == 1) ? 0 : (($wallet_balance - $returnable_amount < 0) ? 0 : $wallet_balance - $returnable_amount);
        }

        /* find new_total_payable */
        if (strtolower($payment_method) != 'cod' && $payment_method != 'Bank Transfer') {
            /* online payment or any other payment method is used. and payment is already done */
            $new_total_payable = 0;
        } else {
            if ($bank_receipt_status == 2) {
                $new_total_payable = 0;
            } else {
                $new_total_payable = $new_final_total - $new_wallet_balance;
            }
        }

        if ($new_total == 0) {
            $new_total = $new_wallet_balance = $new_delivery_charge = $new_final_total = $new_total_payable = 0;
        }

        //custom message
        $custom_notification = fetchDetails('custom_messages', ['type' => "wallet_transaction"], '*');

        $hashtag_currency = '< currency >';
        $hashtag_returnable_amount = '< returnable_amount >';
        $string = isset($custom_notification[0]->message) ? json_encode($custom_notification[0]->message, JSON_UNESCAPED_UNICODE) : '';
        $hashtag = html_entity_decode($string);
        $data = str_replace(array($hashtag_currency, $hashtag_returnable_amount), array($currency, $returnable_amount), $hashtag);
        $message = outputEscaping(trim($data, '"'));
        $custom_message = (!empty($custom_notification)) ? $message : $currency . ' ' . $returnable_amount;
        $title = (!empty($custom_notification)) ? $custom_notification[0]->title : "Amount Credited To Wallet";


        if ($returnable_amount > 0) {

            $fcmMsg = array(
                'title' => "$title",
                'body' => "$custom_message",
                'type' => "wallet",
                'store_id' => "$store_id",
            );
            $registrationIDs_chunks = array_chunk($fcm_ids, 1000);
            sendNotification('', $registrationIDs_chunks, $fcmMsg);

            if ($order_details[0]->payment_method == 'RazorPay') {
                updateWalletBalance('refund', $user_id, $returnable_amount, 'Amount Refund for Order Item ID  : ' . $id, $order_item_id, '', 'razorpay');
            } else {
                updateWalletBalance('credit', $user_id, $returnable_amount, 'Refund Amount Credited for Order Item ID  : ' . $id, $order_item_id);
            }
        }

        // recalculate delivery charge and promocode for each seller

        $order_delivery_charge = fetchDetails('order_charges', ['order_id' => $order_id, 'seller_id' => $seller_id], 'delivery_charge');
        $order_charges_data = DB::table('order_charges')
            ->where('order_id', $order_id)
            ->where('seller_id', '<>', $seller_id)
            ->get();

        if (isset($order_delivery_charge) && !empty($order_delivery_charge)) {
            $parcel_total = floatval($order_total) - floatval($order_item_total);
            if ($parcel_total != 0) {
                $seller_promocode_discount_percentage = ($parcel_total * 100) / $new_total;
                $seller_promocode_discount = ($new_promo_discount * $seller_promocode_discount_percentage) / 100;
                $seller_delivery_charge = ($new_delivery_charge * $seller_promocode_discount_percentage) / 100;
                $parcel_final_total = $parcel_total + $seller_delivery_charge - $seller_promocode_discount;
                $set = [
                    'promo_discount' => round($seller_promocode_discount, 2),
                    'delivery_charge' => round($seller_delivery_charge, 2),
                    'sub_total' => round($parcel_total, 2),
                    'total' => round($parcel_final_total, 2)
                ];
                updateDetails($set, ['order_id' => $order_id, 'seller_id' => $seller_id], 'order_charges');
            }
        }
        if (isset($order_charges_data) && !empty($order_charges_data)) {
            foreach ($order_charges_data as $data) {

                $total = $data->sub_total + $data->promo_discount - $data->delivery_charge;

                $promocode_discount_percentage = ($data->sub_total * 100) / $new_total;
                $promocode_discount = ($new_promo_discount * $promocode_discount_percentage) / 100;
                $delivery_charge = ($new_delivery_charge * $promocode_discount_percentage) / 100;
                $final_total = $data->sub_total + $delivery_charge - $promocode_discount;
                $value = [
                    'promo_discount' => round($promocode_discount, 2),
                    'delivery_charge' => round($delivery_charge, 2),
                    'sub_total' => $data->sub_total,
                    'total' => round($final_total, 2)
                ];
                updateDetails($value, ['order_id' => $order_id, 'seller_id' => $data->seller_id], 'order_charges');
            }
        }
        // end

        $set = [
            'total' => $new_total,
            'final_total' => $new_final_total,
            'total_payable' => $new_total_payable,
            'promo_discount' => (!empty($new_promo_discount) && $new_promo_discount > 0) ? $new_promo_discount : 0,
            'delivery_charge' => $new_delivery_charge,
            'wallet_balance' => $new_wallet_balance
        ];
        updateDetails($set, ['id' => $order_id], 'orders');
        $response['error'] = false;
        $response['message'] = 'Status Updated Successfully';
        $response['data'] = array();
        return $response;
    } elseif ($type == 'orders') {
        /* if complete order is getting cancelled */
        $order_details = fetchOrders($id);

        $order_item_details = DB::table('order_items')
            ->select(DB::raw('SUM(tax_amount) as total_tax'), 'status')
            ->where('order_id', $order_details['order_data'][0]->id)->get();

        $order_details = $order_details['order_data'];
        $store_id = $order_details[0]->store_id;
        $payment_method = $order_details[0]->payment_method;

        $active_status = json_decode($order_item_details[0]->status, true);
        if (trim(strtolower($payment_method)) != 'wallet') {
            if (
                (isset($active_status[1][0]) && $active_status[1][0] == 'cancelled') ||
                (isset($active_status[0][0]) && $active_status[0][0] == 'awaiting')
            ) {
                $response['error'] = true;
                $response['message'] = 'Refund cannot be processed.';
                $response['data'] = array();
                return $response;
            }
        }

        $wallet_refund = true;
        $bank_receipt = fetchDetails('order_bank_transfers', ['order_id' => $id]);

        $is_transfer_accepted = 0;

        if ($payment_method == 'Bank Transfer') {
            if (!empty($bank_receipt)) {
                foreach ($bank_receipt as $receipt) {
                    if ($receipt->status == 2) {
                        $is_transfer_accepted = 1;
                        break;
                    }
                }
            }
        }
        if ($order_details[0]->wallet_balance == 0 && $status == 'cancelled' && $payment_method == 'Bank Transfer' && (!$is_transfer_accepted || empty($bank_receipt))) {
            $wallet_refund = false;
        } else {
            $wallet_refund = true;
        }

        $promo_discount = $order_details[0]->promo_discount;
        $final_total = $order_details[0]->final_total;
        $is_delivery_charge_returnable = isset($order_details[0]->is_delivery_charge_returnable) && $order_details[0]->is_delivery_charge_returnable == 1 ? '1' : '0';
        $payment_method = strtolower($payment_method);
        $total_tax_amount = $order_item_details[0]->total_tax;
        $wallet_balance = $order_details[0]->wallet_balance;
        $currency = (isset($system_settings['currency']) && !empty($system_settings['currency'])) ? $system_settings['currency'] : '';
        $user_id = $order_details[0]->user_id;
        $fcmMsg = array(
            'title' => "Amount Credited To Wallet",
        );
        $user_res = fetchDetails('users', ['id' => $user_id], 'fcm_id');
        $fcm_ids = array();
        $results = UserFcm::join('users', 'user_fcm.user_id', '=', 'users.id')
            ->where('user_fcm.user_id', $user_id)
            ->where('users.is_notification_on', 1)
            ->select('user_fcm.fcm_id')
            ->get();
        foreach ($results as $result) {
            if (is_object($result)) {
                $fcm_ids[] = $result->fcm_id;
            }
        }

        if ($wallet_refund == true) {
            if ($payment_method != 'cod') {
                /* update user's wallet */
                if ($is_delivery_charge_returnable == 1) {
                    $returnable_amount = $order_details[0]->total + $order_details[0]->delivery_charge;
                } else {
                    $returnable_amount = $order_details[0]->total;
                }

                if ($payment_method == 'bank transfer' && !$is_transfer_accepted) {
                    $returnable_amount = $returnable_amount - $order_details[0]->total_payable;
                }
                //send custom notifications
                $custom_notification = fetchDetails('custom_messages', ['type' => "wallet_transaction"], '*');
                $hashtag_currency = '< currency >';
                $hashtag_returnable_amount = '< returnable_amount >';
                $string = isset($custom_notification[0]->message) ? json_encode($custom_notification[0]->message, JSON_UNESCAPED_UNICODE) : '';
                $hashtag = html_entity_decode($string);
                $data = str_replace(array($hashtag_currency, $hashtag_returnable_amount), array($currency, $returnable_amount), $hashtag);
                $message = outputEscaping(trim($data, '"'));
                $title = (!empty($custom_notification)) ? $custom_notification[0]->title : "Amount Credited To Wallet";
                $body = (!empty($custom_notification)) ? $message : $currency . ' ' . $returnable_amount;
                $fcmMsg = array(
                    'title' => "$title",
                    'body' => "$body",
                    'type' => "wallet",
                    'store_id' => "$store_id",
                );
                sendNotification('', $fcm_ids, $fcmMsg);

                updateWalletBalance('credit', $user_id, $returnable_amount, 'Wallet Amount Credited for Order Item ID  : ' . $id);
            } else {
                if ($wallet_balance != 0) {
                    /* update user's wallet */
                    $returnable_amount = $wallet_balance;
                    //send custom notifications
                    $custom_notification = fetchDetails('custom_messages', ['type' => "wallet_transaction"], '*');
                    $hashtag_currency = '< currency >';
                    $hashtag_returnable_amount = '< returnable_amount >';
                    $string = isset($custom_notification[0]->message) ? json_encode($custom_notification[0]->message, JSON_UNESCAPED_UNICODE) : '';
                    $hashtag = html_entity_decode($string);
                    $data = str_replace(array($hashtag_currency, $hashtag_returnable_amount), array($currency, $returnable_amount), $hashtag);
                    $message = outputEscaping(trim($data, '"'));
                    $title = (!empty($custom_notification)) ? $custom_notification[0]->title : "Amount Credited To Wallet";
                    $body = (!empty($custom_notification)) ? $message : $currency . ' ' . $returnable_amount;
                    $fcmMsg = array(
                        'title' => "$title",
                        'body' => "$body",
                        'type' => "wallet",
                        'store_id' => "$store_id",
                    );
                    sendNotification('', $fcm_ids, $fcmMsg);


                    updateWalletBalance('credit', $user_id, $returnable_amount, 'Wallet Amount Credited for Order Item ID  : ' . $id);
                }
            }
        }
    }
}

function recalulateDeliveryCharge($address_id, $total, $old_delivery_charge, $store_id = '')
{

    $settings = getDeliveryChargeSetting($store_id);

    $min_amount = $settings[0]->minimum_free_delivery_amount;
    $d_charge = $old_delivery_charge;

    if ((isset($settings[0]->delivery_charge_type) && !empty($settings[0]->delivery_charge_type) && $settings[0]->delivery_charge_type == 'zipcode_wise_delivery_charge')) {


        if (isset($address_id) && !empty($address_id)) {
            $address = Address::where('id', $address_id)->value('pincode');
            $zipcode = Zipcode::where('zipcode', $address)->select('delivery_charges', 'minimum_free_delivery_order_amount')->first();

            if ($zipcode && isset($zipcode->minimum_free_delivery_order_amount)) {
                $min_amount = $zipcode->minimum_free_delivery_order_amount;
            }
        }
    }

    if ($total < $min_amount) {
        if ($old_delivery_charge == 0) {
            if (isset($address_id) && !empty($address_id)) {
                $d_charge = getDeliveryCharge($address_id, '', '', $store_id);
            } else {
                $d_charge = 0;
            }
        }
    }

    return $d_charge;
}

function recalculatePromoDiscount($promo_code, $promo_discount, $user_id, $total, $payment_method, $delivery_charge, $wallet_balance)
{

    /* recalculate promocode discount if the status of the order_items is cancelled or returned */
    $promo_code_discount = $promo_discount;
    if (isset($promo_code) && !empty($promo_code) && $promo_code != ' ') {

        $promo_code = validatePromoCode($promo_code, $user_id, $total, true)->original;

        if ($promo_code['error'] == false) {

            if ($promo_code['data'][0]->discount_type == 'percentage') {
                $promo_code_discount = floatval($total * $promo_code['data'][0]->discount / 100);
            } else {
                $promo_code_discount = $promo_code['data'][0]->discount;
            }
            if (trim(strtolower($payment_method)) != 'cod' && $payment_method != 'Bank Transfer') {
                /* If any other payment methods are used like razorpay, paytm, flutterwave or stripe then
                     obviously customer would have paid complete amount so making total_payable = 0*/
                $total_payable = 0;
                if ($promo_code_discount > $promo_code['data'][0]->max_discount_amount) {
                    $promo_code_discount = $promo_code['data'][0]->max_discount_amount;
                }
            } else {
                /* also check if the previous discount and recalculated discount are
                     different or not, then only modify total_payable*/
                if ($promo_code_discount <= $promo_code['data'][0]->max_discount_amount && $promo_discount != $promo_code_discount) {
                    $total_payable = floatval($total) + $delivery_charge - $promo_code_discount - $wallet_balance;
                } else if ($promo_discount != $promo_code_discount) {
                    $total_payable = floatval($total) + $delivery_charge - $promo_code['data'][0]->max_discount_amount - $wallet_balance;
                    $promo_code_discount = $promo_code['data'][0]->max_discount_amount;
                }
            }
        } else {
            $promo_code_discount = 0;
        }
    }
    return $promo_code_discount;
}

function getSliders($id = '', $type = '', $type_id = '', $store_id = '')
{
    $query = Slider::query();

    if (!empty($id)) {
        $query->where('id', $id);
    }
    if (!empty($type)) {
        $query->where('type', $type);
    }
    if (!empty($type_id)) {
        $query->where('type_id', $type_id);
    }
    if (!empty($store_id)) {
        $query->where('store_id', $store_id);
    }

    $res = $query->get();
    $res = $res->map(function ($d) {
        if ($d->type === "default") {
            $d['link'] = '';
        }

        if (!empty($d->type)) {
            if ($d->type === "categories") {
                $typeDetails = DB::table('categories')->where('id', $d->type_id)->select('slug')->first();
                if (!empty($typeDetails)) {
                    $d['link'] = customUrl('categories/' . $typeDetails->slug . '/products');
                }
            } elseif ($d->type === "products") {
                $typeDetails = DB::table('products')->where('id', $d->type_id)->select('slug')->first();
                if (!empty($typeDetails)) {
                    $d['link'] = customUrl('products/' . $typeDetails->slug);
                }
            } elseif ($d->type === "combo_products") {
                $typeDetails = DB::table('combo_products')->where('id', $d->type_id)->select('slug')->first();
                if (!empty($typeDetails)) {
                    $d['link'] = customUrl('combo-products/' . $typeDetails->slug);
                }
            }
        }

        $d['image'] = dynamic_image(getMediaImageUrl($d->image), 1920);

        return $d;
    });

    return $res;
}

function getPriceRangeOfProduct($product_id = '')
{
    $system_settings = getSettings('system_settings', true, true);
    $system_settings = json_decode($system_settings, true);

    $currency = ($system_settings['currency'] ?? '');

    $query = DB::table('products as p')
        ->leftJoin('product_variants as pv', 'p.id', '=', 'pv.product_id')
        ->leftJoin('taxes as tax_id', function ($join) {
            $join->on(DB::raw('FIND_IN_SET(tax_id.id, p.tax)'), '>', DB::raw('0'));
        });

    if (!empty($product_id)) {
        $query->where('p.id', $product_id);
    }

    $response = $query->select(
        'p.is_prices_inclusive_tax',
        'pv.price',
        'pv.special_price',
        DB::raw('(SELECT GROUP_CONCAT(tax.percentage) FROM taxes as tax WHERE FIND_IN_SET(tax.id, p.tax)) as tax_percentage')
    )->get();

    if (count($response) == 1) {
        $percentage = ($response[0]->tax_percentage && intval($response[0]->tax_percentage) > 0) ? $response[0]->tax_percentage : 0;

        if (($response[0]->is_prices_inclusive_tax == 0)) {
            $tax_percentage = explode(',', $percentage);
            $total_tax = array_sum($tax_percentage);

            $price_tax_amount = $response[0]->price * ($total_tax / 100);
            $special_price_tax_amount = $response[0]->special_price * ($total_tax / 100);
        } else {
            $price_tax_amount = 0;
            $special_price_tax_amount = 0;
        }

        $price = ($response[0]->special_price == 0) ? $response[0]->price + $price_tax_amount : $response[0]->special_price + $special_price_tax_amount;
        $data['range'] = $currency . "<small style='font-size: 20px;'>" . number_format($price, 2) . "</small>";
    } else {

        $min_special_prices = $response->pluck('special_price')->filter(function ($price) {
            return $price != 0;
        });

        $min_special_price = $min_special_prices->min();
        $max_price = $response->max('price');

        $percentage = $response->first()->tax_percentage ?? '0';

        if ((!isset($response->first()->is_prices_inclusive_tax) || $response->first()->is_prices_inclusive_tax) || $percentage > 0) {
            $tax_percentage = explode(',', $percentage);
            $total_tax = array_sum($tax_percentage);


            $min_price_tax_amount = $min_special_price * ($total_tax / 100);
            $min_special_price += $min_price_tax_amount;

            $max_price_tax_amount = $max_price * ($total_tax / 100);
            $max_price += $max_price_tax_amount;
        }

        return [
            'min_special_price' => $min_special_price,
            'max_price' => $max_price,
        ];
    }

    return $data;
}

function getOrderDetails($where = null, $status = false, $sellerId = null, $store_id = '')
{
    // get data of regular order items
    $regularOrderItemData = DB::table('order_items as oi')
        ->select(
            'oi.*',
            'ot.courier_agency',
            'ot.tracking_id',
            'ot.url',
            'oi.otp as item_otp',
            'a.name as user_name',
            'oi.id as order_item_id',
            'oi.seller_id as oi_seller_id',
            'p.*',
            'v.product_id',
            'o.*',
            'o.email as user_email',
            'o.id as order_id',
            'o.total as order_total',
            'o.wallet_balance',
            'oi.active_status as oi_active_status',
            'u.email',
            'u.username as uname',
            'oi.status as order_status',
            'oi.attachment',
            'p.id as product_id',
            'p.pickup_location as pickup_location',
            'p.slug as product_slug',
            'p.sku as product_sku',
            'v.sku',
            't.txn_id',
            'oi.price',
            'p.name as pname',
            'p.type',
            'p.image as product_image',
            'p.is_prices_inclusive_tax',
            'p.is_attachment_required',
            'u.image as user_profile',
            'ss.store_name',
            'ss.logo as shop_logo',
            'v.price as product_price',
            'v.special_price as product_special_price',
            DB::raw('(SELECT username FROM users db where db.id=oi.delivery_boy_id ) as delivery_boy'),
            DB::raw('(SELECT mobile FROM addresses a where a.id=o.address_id ) as mobile_number')
        )
        ->leftJoin('product_variants as v', 'oi.product_variant_id', '=', 'v.id')
        ->leftJoin('transactions as t', 'oi.order_id', '=', 't.order_id')
        ->leftJoin('products as p', 'p.id', '=', 'v.product_id')
        ->leftJoin('users as u', 'u.id', '=', 'oi.user_id')
        ->leftJoin('orders as o', 'o.id', '=', 'oi.order_id')
        ->leftJoin('order_trackings as ot', 'ot.order_item_id', '=', 'oi.id')
        ->leftJoin('addresses as a', 'a.id', '=', 'o.address_id')
        ->leftJoin('seller_store as ss', 'ss.seller_id', '=', 'oi.seller_id')
        ->where('oi.order_type', 'regular_order');

    $regularOrderItemData->where('o.is_pos_order', 0);

    if (isset($where) && $where != null) {
        $regularOrderItemData->where($where);
        if ($status == true) {
            $regularOrderItemData->whereNotIn('oi.active_status', ['cancelled', 'returned']);
        }
    }

    if (!isset($where) && $status == true) {
        $regularOrderItemData->whereNotIn('oi.active_status', ['cancelled', 'returned']);
    }
    if (isset($store_id) && !empty($store_id)) {
        $regularOrderItemData->where('oi.store_id', $store_id);
    }

    $regularOrderItemData->groupBy('oi.id');
    $regularOrderItemData = $regularOrderItemData->get()->toArray();

    // dd($regularOrderItemData);
    // get data of combo order items

    $comboOrderItemData = DB::table('order_items as oi')
        ->select(
            'oi.*',
            'ot.courier_agency',
            'ot.tracking_id',
            'ot.url',
            'oi.otp as item_otp',
            'a.name as user_name',
            'oi.id as order_item_id',
            'cp.*',
            'cp.id as product_id',
            'o.*',
            'o.email as user_email',
            'o.id as order_id',
            'oi.seller_id as oi_seller_id',
            'o.total as order_total',
            'o.wallet_balance',
            'oi.active_status as oi_active_status',
            'u.email',
            'u.username as uname',
            'oi.status as order_status',
            'cp.id as product_id',
            'cp.pickup_location as pickup_location',
            'cp.slug as product_slug',
            'cp.sku as product_sku',
            'cp.sku',
            't.txn_id',
            'oi.price',
            'cp.title as pname',
            'cp.product_type as type',
            'cp.image as product_image',
            'cp.is_prices_inclusive_tax',
            'u.image as user_profile',
            'ss.store_name',
            'ss.logo as shop_logo',
            'cp.price as product_price',
            'cp.special_price as product_special_price',
            DB::raw('(SELECT username FROM users db where db.id=oi.delivery_boy_id ) as delivery_boy'),
            DB::raw('(SELECT mobile FROM addresses a where a.id=o.address_id ) as mobile_number')
        )

        ->leftJoin('combo_products AS cp', 'cp.id', '=', 'oi.product_variant_id')
        ->leftJoin('transactions as t', 'oi.order_id', '=', 't.order_id')
        ->leftJoin('users as u', 'u.id', '=', 'oi.user_id')
        ->leftJoin('orders as o', 'o.id', '=', 'oi.order_id')
        ->leftJoin('order_trackings as ot', 'ot.order_item_id', '=', 'oi.id')
        ->leftJoin('addresses as a', 'a.id', '=', 'o.address_id')
        ->leftJoin('seller_store as ss', 'ss.seller_id', '=', 'oi.seller_id')
        ->where('oi.order_type', 'combo_order');


    $comboOrderItemData->where('o.is_pos_order', 0);

    if (isset($where) && $where != null) {
        $comboOrderItemData->where($where);
        if ($status == true) {
            $comboOrderItemData->whereNotIn('oi.active_status', ['cancelled', 'returned']);
        }
    }

    if (!isset($where) && $status == true) {
        $comboOrderItemData->whereNotIn('oi.active_status', ['cancelled', 'returned']);
    }
    if (isset($store_id) && !empty($store_id)) {
        $comboOrderItemData->where('oi.store_id', $store_id);
    }
    $comboOrderItemData->groupBy('oi.id');
    $comboOrderItemData = $comboOrderItemData->get()->toArray();

    $orderResult = array_merge($regularOrderItemData, $comboOrderItemData);


    if (!empty($orderResult)) {
        foreach ($orderResult as $key => $value) {
            $orderResult[$key] = outputEscaping($value);
        }
    }

    return $orderResult;
}
function processReferralBonus($user_id, $order_id, $status)
{
    /*
        $user_id = 99;              << user ID of the person whose order is being marked not the friend's ID who is going to get the bonus
        $status = "delivered";      << current status of the order
        $order_id = 644;            << Order which is being marked as delivered

    */
    $settings = getSettings('system_settings', true);
    $settings = json_decode($settings, true);
    if (isset($settings['refer_and_earn_status']) && $settings['refer_and_earn_status'] == 1 && $status == "delivered") {
        $user = fetchUsers($user_id);

        /* check if user has set friends code or not */
        if (isset($user[0]->friends_code) && !empty($user[0]->friends_code)) {

            /* find number of previous orders of the user */
            $total_orders = fetchDetails('orders', ['user_id' => $user_id], 'COUNT(id) as total');
            $total_orders = $total_orders[0]->total;

            if ($total_orders < $settings['number_of_times_bonus_given_to_customer']) {

                /* find a friends account details */
                $friend_user = fetchDetails('users', ['referral_code' => $user[0]->friends_code], ['id', 'username', 'email', 'mobile', 'balance']);
                if (!empty($friend_user)) {
                    $order = fetchOrders($order_id);
                    $final_total = $order['order_data'][0]->final_total;
                    if ($final_total >= $settings['minimum_refer_and_earn_amount']) {
                        $referral_bonus = 0;
                        if ($settings['refer_and_earn_method'] == 'percentage') {
                            $referral_bonus = $final_total * ($settings['minimum_refer_and_earn_bonus'] / 100);
                            if ($referral_bonus > $settings['max_refer_and_earn_amount']) {
                                $referral_bonus = $settings['max_refer_and_earn_amount'];
                            }
                        } else {
                            $referral_bonus = $settings['minimum_refer_and_earn_bonus'];
                        }

                        $referral_id = "refer-and-earn-" . $order_id;
                        $previous_referral = fetchDetails('transactions', ['order_id' => $referral_id], ['id', 'amount']);
                        if (empty($previous_referral)) {

                            $transaction_data = new Request([
                                'transaction_type' => "wallet",
                                'user_id' => $friend_user[0]->id,
                                'order_id' => $referral_id,
                                'type' => "credit",
                                'txn_id' => "",
                                'amount' => $referral_bonus,
                                'status' => "success",
                                'message' => "Refer and Earn bonus on " . $user[0]->username . "'s order",
                            ]);
                            $transactionController = app(TransactionController::class);
                            $transactionController->store($transaction_data);

                            if (updateBalance($referral_bonus, $friend_user[0]['id'], 'add')) {
                                $response['error'] = false;
                                $response['message'] = "User's wallet credited successfully";
                                return $response;
                            }
                        } else {
                            $response['error'] = true;
                            $response['message'] = "Bonus is already given for the following order!";
                            return $response;
                        }
                    } else {
                        $response['error'] = true;
                        $response['message'] = "This order amount is not eligible refer and earn bonus!";
                        return $response;
                    }
                } else {
                    $response['error'] = true;
                    $response['message'] = "Friend user not found for the used referral code!";
                    return $response;
                }
            } else {
                $response['error'] = true;
                $response['message'] = "Number of orders have exceeded the eligible first few orders!";
                return $response;
            }
        } else {
            $response['error'] = true;
            $response['message'] = "No friends code found!";
            return $response;
        }
    } else {
        if ($status == "delivered") {
            $response['error'] = true;
            $response['message'] = "Referred and earn system is turned off";
            return $response;
        } else {
            $response['error'] = true;
            $response['message'] = "Status must be set to delivered to get the bonus";
            return $response;
        }
    }
}

function get_shiprocket_order($shiprocket_order_id)
{
    $shiprocket = new Shiprocket();
    $res = $shiprocket->get_specific_order($shiprocket_order_id);
    return $res;
}

function shiprocket_recomended_data($shiprocket_data)
{

    $result = array();
    if (isset($shiprocket_data['data']['recommended_courier_company_id'])) {
        foreach ($shiprocket_data['data']['available_courier_companies'] as $rd) {
            if ($shiprocket_data['data']['recommended_courier_company_id'] == $rd['courier_company_id']) {
                $result = $rd;
                break;
            }
        }
    } else {
        foreach ($shiprocket_data['data']['available_courier_companies'] as $rd) {
            if ($rd['courier_company_id']) {
                $result = $rd;
                break;
            }
        }
    }
    return $result;
}

function generate_awb($shipment_id)
{
    $order_tracking = fetchDetails('order_trackings', ['shipment_id' => $shipment_id], 'courier_company_id');
    $courier_company_id = $order_tracking[0]->courier_company_id;

    $shiprocket = new Shiprocket();
    $res = $shiprocket->generate_awb($shipment_id);

    if (isset($res['awb_assign_status']) && $res['awb_assign_status'] == 1) {
        $order_tracking_data = [
            'awb_code' => $res['response']['data']['awb_code'],
        ];
        $res_shippment_data = $shiprocket->get_order($shipment_id);
        updateDetails($order_tracking_data, ['shipment_id' => $shipment_id], 'order_trackings');
    } else {
        $res = $shiprocket->generate_awb($shipment_id);
        $order_tracking_data = [
            'awb_code' => $res['response']['data']['awb_code'],
        ];
        $res_shippment_data = $shiprocket->get_order($shipment_id);
        updateDetails($order_tracking_data, ['shipment_id' => $shipment_id], 'order_trackings');
    }

    return $res;
}

function send_pickup_request($shipment_id)
{

    $shiprocket = new Shiprocket();
    $res = $shiprocket->request_for_pickup($shipment_id);
    if (isset($res['pickup_status']) && $res['pickup_status'] == 1) {

        $order_tracking_data = [
            'pickup_status' => $res['pickup_status'],
            'pickup_scheduled_date' => $res['response']['pickup_scheduled_date'],
            'pickup_token_number' => $res['response']['pickup_token_number'],
            'status' => $res['response']['status'],
            'pickup_generated_date' => json_encode(array($res['response']['pickup_generated_date'])),
            'data' => $res['response']['data'],
        ];
        updateDetails($order_tracking_data, ['shipment_id' => $shipment_id], 'order_trackings');
    }
    return $res;
}

function cancel_shiprocket_order($shiprocket_order_id)
{
    $shiprocket = new Shiprocket();
    $res = $shiprocket->cancel_order($shiprocket_order_id);

    if (isset($res['status']) && $res['status'] == 200 || $res['status_code'] == 200) {
        $is_canceled = [
            'is_canceled' => 1,
        ];
        updateDetails($is_canceled, ['shiprocket_order_id' => $shiprocket_order_id], 'order_trackings');
        $order_tracking = fetchdetails('order_trackings', ['shiprocket_order_id' => $shiprocket_order_id]);

        $parcel_id = $order_tracking[0]->parcel_id;
        $uniqueStatus = ["processed"];

        $active_status = "cancelled";
        $status = json_encode($uniqueStatus);

        $old_active_status_data = fetchDetails('parcels', ['id' => $parcel_id], ['active_status', 'store_id']);

        $old_active_status = $old_active_status_data[0]->active_status ?? "";
        $store_id = $old_active_status_data[0]->store_id ?? "";

        if ($old_active_status != "processed" || $old_active_status != "canceled") {

            if (updateOrder(['status' => 'cancelled'], ['id' => $parcel_id], true, 'parcels')) {
                updateOrder(['active_status' => $active_status], ['id' => $parcel_id], false, 'parcels');
                $parcel_item_details = fetchDetails('parcel_items', ['parcel_id' => $parcel_id]);
                foreach ($parcel_item_details as $item) {

                    updateOrder(['status' => 'cancelled'], ['id' => $item->order_item_id], true, 'order_items', false);
                    updateOrder(['active_status' => $active_status], ['id' => $item->order_item_id], false, 'order_items');
                }
            }
        }
        $parcel_details = viewAllParcels($order_tracking[0]->order_id, $parcel_id, '', 0, 10, 'DESC', 1, '', '', $store_id);

        $res['data'] = $parcel_details->original['data'][0];
    }
    return $res;
}
function update_shiprocket_order_status($tracking_id)
{
    $order_tracking_details = fetchDetails("order_trackings", ['tracking_id' => $tracking_id, 'is_canceled' => 0], ['order_id', 'parcel_id']);

    if (empty($order_tracking_details) && !isset($order_tracking_details[0]->parcel_id)) {
        return [
            'error' => true,
            'message' => "Something Went Wrong. Order Not Found.",
            'data' => []
        ];
    }
    $parcel_id = $order_tracking_details[0]->parcel_id;
    $order_id = $order_tracking_details[0]->order_id;
    $shiprocket = new Shiprocket();
    $res = $shiprocket->tracking_order($tracking_id);


    if (isset($res[0][$tracking_id]['tracking_data']) && !empty($res[0][$tracking_id]['tracking_data'])) {

        $active_status = "";
        $status = [];
        $active_status_code = $res[0][$tracking_id]['tracking_data']['shipment_status'];

        $awb_code = $res[0][$tracking_id]['tracking_data']['shipment_track'][0]['awb_code'];
        $track_url = $res[0][$tracking_id]['tracking_data']['track_url'];
        $data = [
            'url' => $track_url,
            'awb_code' => $awb_code
        ];

        if ($active_status_code != 8) {
            updateDetails($data, ['tracking_id' => $tracking_id], 'order_trackings');
        }

        $track_activities = $res[0][$tracking_id]['tracking_data']['shipment_track_activities'];
        $shiprocket_status_codes = config('ezeemart.shiprocket_status_codes');

        foreach ($shiprocket_status_codes as $status) {

            if ($active_status_code == $status['code']) {
                $active_status = $status['description'];
            }
            if (($track_activities) != null) {
                foreach ($track_activities as $track_list) {
                    if ($track_list['sr-status'] == $status['code']) {
                        $data = [
                            $status['description'],
                            $track_list['date'],
                        ];
                        array_push($status, $data);
                    }
                }
            }
        }

        if ($active_status == 'delivered') {
            $data = [
                $active_status,
                $res[0]['tracking_data']['shipment_track'][0]['delivered_date'] ?? date("Y-m-d") . " " . date("h:i:sa")
            ];
            array_push($status, $data);
        }
        if (empty($active_status) && empty($status)) {
            $response['error'] = true;
            $response['message'] = "Check Status Manually From Given Tracking Url!";
            $response['data'] = [
                'track_url' => $track_url
            ];
            return $response;
        }
        $parcel_item_details = fetchDetails('parcel_items', ['parcel_id' => $parcel_id]);

        $parcel_items = fetchDetails('parcels', ['id' => $parcel_id]);
        if (empty($parcel_items) || empty($parcel_item_details)) {
            $response['error'] = true;
            $response['message'] = "Something Went Wrong. Order Not Found.";
            $response['data'] = [
                'track_url' => $track_url
            ];
            return $response;
        }

        if (!empty($active_status) && empty($status)) {
            $status = [[$active_status, date("Y-m-d") . " " . date("h:i:sa")]];
        }
        if (empty($active_status) && !empty($status)) {
            $active_status = $parcel_items[0]->active_status;
        }

        $uniqueStatus = [];
        // remove duplicate status
        foreach ($status as $entry) {

            $status = $entry;
            if (!in_array($status, array_column($uniqueStatus, 0))) {
                $uniqueStatus[] = $entry;
            }
        }

        $response_data = [];
        $active_status = str_replace(" ", "_", $active_status);
        if ($active_status == "cancelled") {
            $data += [
                'is_canceled' => 1
            ];
            $uniqueStatus = ["processed"];
            $active_status = "cancelled";
            updateDetails($data, ['tracking_id' => $tracking_id], 'order_trackings');
        }
        $status = json_encode($uniqueStatus);
        if (updateOrder(['status' => 'cancelled'], ['id' => $parcel_id], true, 'parcels')) {
            updateOrder(['active_status' => $active_status], ['id' => $parcel_id], false, 'parcels');

            foreach ($parcel_item_details as $item) {
                updateOrder(['status' => 'cancelled'], ['id' => $item->order_item_id], true, 'order_items', false);
                updateOrder(['active_status' => $active_status], ['id' => $item->order_item_id], false, 'order_items');
                $data = [
                    'consignment_id' => $parcel_id,
                    'order_item_id' => $item->order_item_id,
                    'status' => $active_status
                ];
                array_push($response_data, $data);
            }
        }
        if ($active_status == "cancelled") {
            $response['error'] = true;
            $response['message'] = "Shiprocket Order Is Cancelled!";
            $response['data'] = [
                'track_url' => $track_url
            ];
        } else {
            $response['error'] = false;
            $response['message'] = "Status Updated Successfully";
            $response['data'] = $response_data;
        }
        return $response;
    } else {
        return [
            'error' => true,
            'message' => $tracking_data['error'] ?? 'Tracking data not available'
        ];
    }
}

function generate_label($shipment_id)
{
    $shiprocket = new Shiprocket();
    $res = $shiprocket->generate_label($shipment_id);

    if (isset($res['label_created']) && $res['label_created'] == 1) {
        $label_data = [
            'label_url' => $res['label_url'],
        ];
        updateDetails($label_data, ['shipment_id' => $shipment_id], 'order_trackings');
    }
    return $res;
}

function generate_invoice($shiprocket_order_id)
{
    $shiprocket = new Shiprocket();
    $res = $shiprocket->generate_invoice($shiprocket_order_id);

    if (isset($res['is_invoice_created']) && $res['is_invoice_created'] == 1) {
        $invoice_data = [
            'invoice_url' => $res['invoice_url'],
        ];
        updateDetails($invoice_data, ['shiprocket_order_id' => $shiprocket_order_id], 'order_trackings');
    }
    return $res;
}

function fetchRating($productId = null, $userId = null, $limit = null, $offset = null, $sort = null, $order = null, $ratingId = null, $hasImages = null, $count_empty_comments = false, $rating = '')
{

    $query = DB::table('product_ratings as pr')
        ->leftJoin('users as u', 'u.id', '=', 'pr.user_id');

    $selectColumns = [
        'pr.*',
        'u.username as user_name',
        'u.image as user_profile',
    ];

    if (!empty($productId)) {
        $query->where('pr.product_id', $productId);
    }
    if (!empty($userId)) {

        $query->where('pr.user_id', $userId);
    }

    if (!empty($ratingId)) {
        $query->where('pr.id', $ratingId);
    }
    if (!empty($rating)) {
        $rating = floatval($rating);
        $query->whereBetween('pr.rating', [$rating, $rating + 0.3]);
    }
    if (!empty($sort) && !empty($order)) {
        $query->orderBy($sort, $order);
    }

    if (!empty($limit) && !empty($offset)) {
        $query->skip($offset)->take($limit);
    }

    $productRatings = $query->get($selectColumns)->toArray();
    foreach ($productRatings as $rating) {
        $images = json_decode($rating->images, true);
        $rating->images = [];
        $rating->user_name = $rating->user_name ?? "";
        $rating->user_profile = $rating->user_profile ?? "";
        $rating->comment = $rating->comment ?? "";
        if (!empty($images)) {
            if ($images !== null) {
                foreach ($images as $image) {
                    $rating->images = [...$rating->images, getImageUrl($image)];
                }
            }
        } else {
            $rating->images = [];
        }
        // if (!empty($rating->user_profile)) {
        // $rating->user_profile = asset(config('constants.USER_IMG_PATH') . $rating->user_profile);
        $rating->user_profile = (!empty($rating->user_profile) && file_exists(public_path(config('constants.USER_IMG_PATH') . $rating->user_profile))
            ? getMediaImageUrl($rating->user_profile, 'USER_IMG_PATH')
            : getImageUrl('no-user-img.jpeg', '', '', 'image', 'NO_USER_IMAGE'));

        // dd($rating->user_profile);
        // }
    }

    $totalRating = DB::table('product_ratings as pr')
        ->leftJoin('users as u', 'u.id', '=', 'pr.user_id')
        ->where('pr.product_id', $productId)
        ->count('pr.id');

    $totalImages = DB::table('product_ratings')
        ->where('product_id', $productId)
        ->whereNotNull('images')
        ->count(DB::raw('LENGTH(images) - LENGTH(REPLACE(images, ",", "")) + 1'));

    $totalReviewsWithImages = DB::table('product_ratings as pr')
        ->where('product_id', $productId)
        ->whereNotNull('images')
        ->count('pr.id');

    $totalReviews = DB::table('product_ratings as pr')
        ->where('product_id', $productId)
        ->select(
            DB::raw('count(pr.id) as total'),
            DB::raw('sum(case when CEIL(pr.rating) = 1 then 1 else 0 end) as rating_1'),
            DB::raw('sum(case when CEIL(pr.rating) = 2 then 1 else 0 end) as rating_2'),
            DB::raw('sum(case when CEIL(pr.rating) = 3 then 1 else 0 end) as rating_3'),
            // Grouping ratings from 4.0 to 4.4 under 4 stars and from 4.5 to 5.0 under 5 stars
            DB::raw('sum(case when pr.rating >= 4 and pr.rating < 4.5 then 1 else 0 end) as rating_4'),
            DB::raw('sum(case when pr.rating >= 4.5 then 1 else 0 end) as rating_5')
        )
        ->first();
    // Remove the count() method here

    $no_of_reviews = 0;
    if ($count_empty_comments) {
        $no_of_reviews = ProductRating::where('product_id', $productId)
            ->where(function ($query) {
                $query->whereNotNull('comment')
                    ->where('comment', '!=', '');
            })
            ->count();
    }
    // Check if $totalReviews is not null before accessing its properties

    if ($totalReviews) {
        $result = [
            'total_images' => $totalImages ?? $totalRating,
            'total_reviews_with_images' => $totalReviewsWithImages,
            'no_of_rating' => $totalRating,
            'total_reviews' => $totalReviews->total ?? "",
            'star_1' => $totalReviews->rating_1 ?? "0",
            'star_2' => $totalReviews->rating_2 ?? "0",
            'star_3' => $totalReviews->rating_3 ?? "0",
            'star_4' => $totalReviews->rating_4 ?? "0",
            'star_5' => $totalReviews->rating_5 ?? "0",
            'product_rating' => $productRatings,
            'no_of_reviews' => $no_of_reviews
        ];
    } else {
        $result = [
            'total_images' => $totalImages ?: $totalRating,
            'total_reviews_with_images' => $totalReviewsWithImages,
            'no_of_rating' => $totalRating,
            'total_reviews' => 0,
            'star_1' => 0,
            'star_2' => 0,
            'star_3' => 0,
            'star_4' => 0,
            'star_5' => 0,
            'product_rating' => $productRatings,
            'no_of_reviews' => $no_of_reviews
        ];
    }

    return $result;
}

function getProductFaqs($id = null, $product_id = null, $user_id = '', $search = '', $limit = '', $offset = '', $sort = '', $order = '', $is_seller = false, $seller_id = '')
{
    $limit = $limit ?: 10;
    $offset = $offset ?: 0;
    $sort = $sort ?: 'pf.id';
    $order = $order ?: 'desc';

    $query = DB::table('product_faqs AS pf')
        ->leftJoin('users AS u', 'u.id', '=', 'pf.user_id')
        ->leftJoin('products AS p', 'p.id', '=', 'pf.product_id')
        ->leftJoin('users AS answered_by_user', 'answered_by_user.id', '=', 'pf.answered_by'); // Join the users table again to get the username for answered_by


    // Apply filters
    if (!empty($id)) {
        $query->where('pf.id', $id);
    }

    if (!empty($product_id)) {
        $query->where('pf.product_id', $product_id);
    }

    if (!empty($user_id)) {
        $query->where('pf.user_id', $user_id);
    }

    if (!empty($seller_id)) {
        $query->where('pf.seller_id', $seller_id);
    }

    // Search filter
    if (!empty($search)) {
        $query->where(function ($query) use ($search) {
            $query->where('pf.question', 'like', '%' . $search . '%')
                ->orWhere('pf.answer', 'like', '%' . $search . '%');
        });
    }

    // Count total records
    $total = $query->count();

    // Retrieve data with pagination
    $data = $query
        ->select('pf.*', 'u.username as user_username', 'answered_by_user.username as answered_by') // Select the username for answered_by
        ->orderBy($sort, $order)
        ->offset($offset)
        ->limit($limit)
        ->get();

    // Replace null values with empty strings
    $data = $data->map(function ($item) {
        unset($item->created_at, $item->updated_at);
        foreach ($item as $key => $value) {
            $item->$key = $value ?: '';
        }
        return $item;
    });

    return [
        'total' => $total,
        'data' => $data,
    ];
}



function getCategories($id = null, $limit = '', $offset = '', $sort = 'row_order', $order = 'ASC', $has_child_or_item = 'true', $slug = '', $ignore_status = '', $seller_id = '')
{
    $level = 0;

    if ($ignore_status == 1) {
        $where = isset($id) ? ['c1.id' => $id] : ['c1.parent_id' => 0];
    } else {
        $where = isset($id) ? ['c1.id' => $id, 'c1.status' => 1] : ['c1.parent_id' => 0, 'c1.status' => 1];
    }


    $query = DB::table('categories as c1')
        ->select('c1.*')
        ->where($where);

    if (!empty($slug)) {
        $query->where('c1.slug', $slug);
    }

    if ($has_child_or_item === 'false') {
        $query->leftJoin('categories as c2', 'c2.parent_id', '=', 'c1.id')
            ->leftJoin('products as p', 'p.category_id', '=', 'c1.id')
            ->where(function ($query) {
                $query->orWhere('c1.id', '=', DB::raw('p.category_id'))
                    ->orWhere('c2.parent_id', '=', 'c1.id');
            })
            ->groupBy('c1.id');
    }

    if (!empty($limit) || !empty($offset)) {
        $query->offset($offset)->limit($limit);
    }

    $query->orderBy($sort, $order);
    $categories = $query->get();
    $countRes = DB::table('categories as c1')->where($where)->count();



    $i = 0;
    foreach ($categories as $pCat) {
        $categories[$i]->children = subCategories($pCat->id, $level);
        $categories[$i]->text = e($pCat->name);
        $categories[$i]->name = e($categories[$i]->name);
        $categories[$i]->state = ['opened' => true];
        $categories[$i]->icon = "jstree-folder";
        $categories[$i]->level = $level;
        $categories[$i]->image = getImageUrl($categories[$i]->image, 'thumb', 'sm');
        $categories[$i]->banner = getImageUrl($categories[$i]->banner, 'thumb', 'md');
        $i++;
    }


    if (isset($categories[0])) {
        $categories[0]->total = $countRes;
    }
    $response = [
        'categories' => $categories,
        'countRes' => $countRes,
    ];

    return $response;
}

function getSellers($zipcode_id = "", $limit = NULL, $offset = '', $sort = 'u.id', $order = 'DESC', $search = NULL, $filter = [])
{
    $where = [
        'u.active' => 1,
        'sd.status' => 1,
        'p.status' => 1
    ];

    if (!empty($filter['slug'])) {
        $where['sd.slug'] = $filter['slug'];
    }

    if (!empty(request()->input('seller_id'))) {
        $where['sd.id'] = request()->input('seller_id');
    }

    $multipleWhere = [];
    if (!empty($search)) {
        $multipleWhere = [
            'u.id' => $search,
            'u.username' => $search,
            'u.email' => $search,
            'u.mobile' => $search,
            'u.address' => $search,
            'u.balance' => $search,
            'sd.store_name' => $search
        ];
    }

    $countQuery = DB::table('users as u')
        ->select(DB::raw('COUNT(DISTINCT u.id) as total'))
        ->join('roles as r', 'r.id', '=', 'u.role_id')
        ->join('seller_data as sd', 'sd.user_id', '=', 'u.id')
        ->join('products as p', 'p.seller_id', '=', 'sd.id')
        ->where($where);

    if (!empty($multipleWhere)) {
        $countQuery->where(function ($query) use ($multipleWhere) {
            foreach ($multipleWhere as $column => $value) {
                $query->orWhere($column, 'LIKE', '%' . $value . '%');
            }
        });
    }

    if (!empty($zipcode_id)) {
        $countQuery->where(function ($query) use ($zipcode_id) {
            $query->whereRaw("(deliverable_type='2' and FIND_IN_SET(?, deliverable_zipcodes)) or deliverable_type = '1' OR (deliverable_type='3' and NOT FIND_IN_SET(?, deliverable_zipcodes))", [$zipcode_id, $zipcode_id]);
        });
    }

    $total = $countQuery->first()->total;

    $searchQuery = DB::table('users as u')
        ->select('u.*', 'sd.*', 'u.id as seller_id')
        ->join('roles as r', 'r.id', '=', 'u.role_id')
        ->join('seller_data as sd', 'sd.user_id', '=', 'u.id')
        ->join('products as p', 'p.seller_id', '=', 'sd.id')
        ->where($where);

    if (!empty($multipleWhere)) {
        $searchQuery->where(function ($query) use ($multipleWhere) {
            foreach ($multipleWhere as $column => $value) {
                $query->orWhere($column, 'LIKE', '%' . $value . '%');
            }
        });
    }

    if (!empty($zipcode_id)) {
        $searchQuery->where(function ($query) use ($zipcode_id) {
            $query->whereRaw("(deliverable_type='2' and FIND_IN_SET(?, deliverable_zipcodes)) or deliverable_type = '1' OR (deliverable_type='3' and NOT FIND_IN_SET(?, deliverable_zipcodes))", [$zipcode_id, $zipcode_id]);
        });
    }

    $offer_search_res = $searchQuery->groupBy('u.id')->orderBy($sort, $order)->limit($limit)->offset($offset)->get();

    $bulkData = [
        'error' => $offer_search_res->isEmpty(),
        'message' => $offer_search_res->isEmpty() ? 'Seller(s) does not exist' : 'Seller retrieved successfully',
        'total' => $offer_search_res->isEmpty() ? 0 : $total,
        'data' => []
    ];

    $rows = [];
    foreach ($offer_search_res as $row) {
        $where = [
            'p.seller_id' => $row->id,
            'p.status' => '1',
            'pv.status' => 1
        ];

        $totalProducts = DB::table('products as p')
            ->leftJoin('seller_data as sd', 'p.seller_id', '=', 'sd.id')
            ->leftJoin('product_variants as pv', 'p.id', '=', 'pv.product_id')
            ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
            ->where($where)
            ->where(function ($query) {
                $query->orWhere('c.status', '1')->orWhere('c.status', '0');
            })
            ->first();

        $tempRow = [
            'seller_id' => $row->id,
            'seller_name' => $row->username,
            'email' => $row->email,
            'mobile' => $row->mobile,
            'slug' => $row->slug,
            'seller_rating' => $row->rating,
            'no_of_ratings' => $row->no_of_ratings,
            'store_name' => $row->store_name,
            'store_url' => $row->store_url,
            'store_description' => $row->store_description,
            'seller_profile' => getImageUrl($row->logo),
            'balance' => number_format($row->balance, 2),
            'total_products' => $totalProducts->total,
            'products' => fetchProduct('', '', '', '', 10, 0, '', '', '', '', $row->id, '')
        ];

        $rows[] = $tempRow;
    }

    $bulkData['data'] = $rows;

    return $bulkData;
}
function getBlogs($id = null, $offset = 0, $limit = 10, $sort = 'id', $order = 'Desc', $search = null, $category_id = null)
{

    $blogData = [];

    $where = ['status' => 1];
    if (!is_null($category_id)) {
        $where['category_id'] = $category_id;
    }

    if (!is_null($id)) {
        $where['id'] = $id;
    }

    $countQuery = DB::table('blogs')->select(DB::raw('COUNT(id) as total'));
    if (!is_null($search)) {
        $countQuery->where(function ($query) use ($search) {
            $query->orWhere('title', 'LIKE', "%$search%")
                ->orWhere('slug', 'LIKE', "%$search%");
        });
    }
    $countQuery->where($where);

    $countResult = $countQuery->get()->first();
    $query = DB::table('blogs')->select('*');
    if (!is_null($search)) {
        $query->where(function ($query) use ($search) {
            $query->orWhere('title', 'LIKE', "%$search%")
                ->orWhere('slug', 'LIKE', "%$search%");
        });
    }
    $query->where($where);
    $query->orderBy($sort, $order);
    $query->offset($offset);
    $query->limit($limit);

    $searchResult = $query->get();

    $blogData['total'] = $countResult->total;
    $blogData['data'] = $searchResult;

    foreach ($blogData['data'] as $key => $data) {
        $blogData['data'][$key]->image = getImageUrl($data->image);
    }


    return $blogData;
}

function getFaqs($offset = 0, $limit = 10, $sort = 'id', $order = 'Desc')
{
    $faqs_data = [];

    // Get the total count
    $faqs_data['total'] = Faq::where('status', 1)->count();

    // Get the paginated and sorted data
    $faqs_data['data'] = Faq::where('status', 1)
        ->orderBy($sort, $order)
        ->skip($offset)
        ->take($limit)
        ->get();

    return $faqs_data;
}

function countNewOrders($type = '')
{
    $user = Auth::user();
    $store_id = getStoreId();
    // Calculate the total orders for the current month
    $currentMonthOrders = DB::table('orders as o')
        ->select(DB::raw('count(o.id) as counter'))
        ->where('o.store_id', $store_id)
        ->whereBetween('o.created_at', [now()->startOfMonth(), now()->endOfMonth()])
        ->get()[0]->counter;

    // Calculate the total orders for the previous month
    $previousMonthOrders = DB::table('orders as o')
        ->select(DB::raw('count(o.id) as counter'))
        ->where('o.store_id', $store_id)
        ->whereBetween('o.created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
        ->get()[0]->counter;

    // Calculate the percentage change
    $percentageChange = 0;
    if ($previousMonthOrders > 0) {
        $percentageChange = (($currentMonthOrders - $previousMonthOrders) / $previousMonthOrders) * 100;
    }

    // Continue with the existing logic in your countNewOrders function
    $query = DB::table('orders as o')
        ->where('o.store_id', $store_id)
        ->select(DB::raw('count(o.id) as counter'));

    if (!empty($type) && $type != 'api') {
        if ($user->role_id == 3) {
            $user_id = $user->id;
            $query->join('order_items as oi', 'oi.order_id', '=', 'o.id', 'left')
                ->where('oi.delivery_boy_id', $user_id);
        }
    }

    if ($user !== null && $user->role_id == 3) {
        $user_id = $user->id;
        $query->join('order_items as oi', 'oi.order_id', '=', 'o.id', 'left')
            ->where('oi.delivery_boy_id', $user_id);
    }

    $result = $query->get();
    $totalOrders = $result[0]->counter;

    return [
        'total_orders' => $totalOrders,
        'current_month_orders' => $currentMonthOrders,
        'previous_month_orders' => $previousMonthOrders,
        'percentage_change' => $percentageChange,
    ];
}

function countNewUsers()
{
    $totalUsers = $currentMonthUsers = DB::table('users as u')
        ->where('u.role_id', 2)
        ->count();
    // Calculate the total users for the current month
    $currentMonthUsers = DB::table('users as u')
        ->whereBetween('u.created_at', [now()->startOfMonth(), now()->endOfMonth()])
        ->where('u.role_id', 2)
        ->count();

    // Calculate the total users for the previous month
    $previousMonthUsers = DB::table('users as u')
        ->where('u.role_id', 2)
        ->whereYear('u.created_at', now()->subMonth()->year)
        ->whereMonth('u.created_at', now()->subMonth()->month)
        ->count();

    $activeUser = DB::table('users as u')
        ->where('u.role_id', 2)
        ->where('u.active', 1)
        ->count();
    $InactiveUser = DB::table('users as u')
        ->where('u.role_id', 2)
        ->where(function ($query) {
            $query->where('u.active', 0)
                ->orWhereNull('u.active');
        })
        ->count();

    // Calculate the percentage change
    $percentageChange = 0;
    if ($previousMonthUsers > 0) {
        $percentageChange = (($currentMonthUsers - $previousMonthUsers) / $previousMonthUsers) * 100;
    }
    $roundedPercentageChange = round($percentageChange, 2);


    return [
        'total_users' => $totalUsers,
        'current_month_users' => $currentMonthUsers,
        'previous_month_users' => $previousMonthUsers,
        'percentage_change' => $roundedPercentageChange,
        'active_user' => $activeUser,
        'inactive_user' => $InactiveUser,
    ];
}

function countDeliveryBoys()
{
    $counter = DB::table('users as u')
        ->where('u.role_id', 3)
        ->count();

    return $counter;
}

function getPromoCodes($limit = null, $offset = null, $sort = 'id', $order = 'DESC', $search = null, $store_id = null)
{
    $query = DB::table('promo_codes as p')
        ->select(
            'p.id',
            DB::raw('DATEDIFF(end_date, start_date) as remaining_days'),
            'p.promo_code',
            'p.image',
            'p.message',
            'p.store_id',
            'p.start_date',
            'p.end_date',
            'p.discount',
            'p.repeat_usage',
            'p.minimum_order_amount as min_order_amt',
            'p.no_of_users',
            'p.discount_type',
            'p.max_discount_amount as max_discount_amt',
            'p.no_of_repeat_usage',
            'p.status',
            'p.is_cashback',
            'p.list_promocode'
        )
        ->whereRaw('(CURDATE() between start_date AND end_date)')
        ->where('p.status', 1)
        ->where('p.store_id', $store_id)
        ->where('p.list_promocode', 1);

    if ($search) {
        $query->where(function ($query) use ($search) {
            $query->orWhere('p.id', 'LIKE', "%{$search}%")
                ->orWhere('p.promo_code', 'LIKE', "%{$search}%")
                ->orWhere('p.message', 'LIKE', "%{$search}%")
                ->orWhere('p.start_date', 'LIKE', "%{$search}%")
                ->orWhere('p.end_date', 'LIKE', "%{$search}%")
                ->orWhere('p.discount', 'LIKE', "%{$search}%")
                ->orWhere('p.repeat_usage', 'LIKE', "%{$search}%")
                ->orWhere('p.max_discount_amount', 'LIKE', "%{$search}%");
        });
    }


    $total = $query->count();

    $promoCodes = $query
        ->orderBy($sort, $order)
        ->when($limit, function ($query, $limit) use ($offset) {
            return $query->skip($offset)->take($limit);
        })
        ->get();

    $promo_code_data = [
        'error' => $promoCodes->isEmpty(),
        'message' => $promoCodes->isEmpty() ? 'Promo code(s) does not exist' : 'Promo code(s) retrieved successfully',
        'total' => $total,
        'data' => $promoCodes->map(function ($row) {
            return [
                'id' => $row->id,
                'promo_code' => $row->promo_code,
                'message' => $row->message,
                'store_id' => $row->store_id,
                'start_date' => $row->start_date,
                'end_date' => $row->end_date,
                'discount' => $row->discount,
                'repeat_usage' => $row->repeat_usage == '1' ? 'Allowed' : 'Not Allowed',
                'min_order_amt' => $row->min_order_amt,
                'no_of_users' => $row->no_of_users,
                'discount_type' => $row->discount_type,
                'max_discount_amt' => $row->max_discount_amt,
                'image' => getMediaImageUrl($row->image),
                'no_of_repeat_usage' => $row->no_of_repeat_usage,
                'status' => $row->status,
                'is_cashback' => $row->is_cashback,
                'list_promocode' => $row->list_promocode,
                'remaining_days' => $row->remaining_days,
            ];
        }),
    ];

    return $promo_code_data;
}
function ordersCount($status = "", $sellerId = "", $orderType = "", $store_id = "", $deliveryBoyId = "")
{

    $query = DB::table('order_items AS oi')
        ->select(DB::raw('COUNT(DISTINCT oi.order_id) as total'))
        ->leftJoin('orders AS o', 'o.id', '=', 'oi.order_id')
        ->leftJoin('product_variants AS pv', 'pv.id', '=', 'oi.product_variant_id')
        ->leftJoin('products AS p', 'p.id', '=', 'pv.product_id');

    if (!empty($orderType)) {
        if ($orderType == 'digital') {
            $query->where('p.type', 'digital_product');
            $query->where('oi.active_status', $status);
        } elseif ($orderType == 'simple') {
            $query->where('p.type', '!=', 'digital_product');
            $query->where('oi.active_status', $status);
        }
    } elseif (!empty($status)) {
        $query->where('oi.active_status', $status);
    }

    if (!empty($sellerId)) {
        $query->where('oi.seller_id', $sellerId)
            ->where('oi.active_status', '!=', 'awaiting');
    }
    if (!empty($deliveryBoyId)) {
        $query->where('oi.delivery_boy_id', $deliveryBoyId);
    }
    if (!empty($store_id)) {
        $query->where('oi.store_id', $store_id)
            ->where('oi.active_status', '!=', 'awaiting');
    }
    $query->where('o.is_pos_order', 0);
    $result = $query->first();

    return $result->total;
}

function fetchOrderItems($orderItemId = null, $userId = null, $status = null, $deliveryBoyId = null, $limit = 25, $offset = '0', $sort = null, $order = null, $startDate = null, $endDate = null, $search = null, $sellerId = null, $orderId = null, $store_id = "", $language_code = '')
{

    // Type casting to ensure correct data types
    $limit = is_numeric($limit) ? (int) $limit : 25;
    $offset = is_numeric($offset) ? (int) $offset : 0;

    $res = getOrderDetails(['o.id' => $orderId, 'oi.seller_id' => $sellerId], '', '', $store_id);

    if (empty($res)) {
        $order_type = fetchDetails('order_items', ['order_id' => $orderId], ['order_type']);
        $order_type = isset($order_type) && !empty($order_type) ? $order_type[0]->order_type : "";
    } else {
        $order_type = isset($res) && !empty($res) ? $res[0]->order_type : "";
    }


    $query = DB::table('order_items as oi')
        ->join('users as u', 'u.id', '=', 'oi.delivery_boy_id', 'left')
        ->join('orders as o', 'o.id', '=', 'oi.order_id')
        ->join('users as un', 'un.id', '=', 'o.user_id', 'left')
        ->join('product_variants as pv', 'pv.id', '=', 'oi.product_variant_id', 'left');

    if ($order_type == 'combo_order') {
        $query->join('combo_products as cp', 'cp.id', '=', 'oi.product_variant_id', 'left')
            ->select('oi.*', 'un.username', 'o.wallet_balance', 'o.promo_discount', 'o.final_total', 'o.total', 'u.mobile', 'o.address_id', 'u.email', 'o.notes', 'o.created_at', 'o.delivery_date', 'o.delivery_time', 'o.payment_method', 'o.is_cod_collected', 'o.total_payable', 'o.promo_discount', 'cp.id as product_id', 'cp.slug as product_slug', 'cp.sku', 'cp.title as pname', 'cp.is_returnable', 'cp.pickup_location', 'cp.is_cancelable', 'cp.is_attachment_required', 'cp.is_prices_inclusive_tax', 'cp.download_allowed', 'cp.image', 'cp.product_type as product_type', 'cp.id as combo_product_id', 'o.total as subtotal_of_order_items');
    } else {
        $query->join('products as p', 'p.id', '=', 'pv.product_id', 'left')
            ->select('oi.*', 'un.username', 'o.wallet_balance', 'o.promo_discount', 'o.total', 'o.final_total', 'u.mobile', 'u.email', 'o.address_id', 'o.notes', 'o.created_at', 'o.delivery_date', 'o.delivery_time', 'o.is_cod_collected', 'o.total_payable', 'o.payment_method', 'o.promo_discount', 'p.slug as product_slug', 'p.id as product_id', 'p.sku', 'p.name as pname', 'p.pickup_location', 'p.is_returnable', 'p.is_cancelable', 'p.is_attachment_required', 'p.is_prices_inclusive_tax', 'p.hsn_code', 'p.download_allowed', 'p.image', 'p.type as product_type', 'o.total as subtotal_of_order_items');
    }
    if ($order_type === 'combo_order') {
        $query->join('seller_data as sd', 'sd.id', '=', 'cp.seller_id', 'left')
            ->join('seller_store as ss', 'ss.seller_id', '=', 'cp.seller_id', 'left');
    } else {
        $query->join('seller_data as sd', 'sd.id', '=', 'p.seller_id', 'left')
            ->join('seller_store as ss', 'ss.seller_id', '=', 'p.seller_id', 'left');
    }
    if (!empty($store_id)) {
        $query->where('oi.store_id', $store_id);
    }
    if (!empty($orderItemId)) {
        if (is_array($orderItemId)) {
            $query->whereIn('oi.id', $orderItemId);
        } else {
            $query->where('oi.id', $orderItemId);
        }
    }

    if (!empty($status)) {
        if (is_array($status)) {
            $query->whereIn('oi.active_status', $status);
        } else {
            $query->where('oi.active_status', $status);
        }
    }
    if (!empty($orderId)) {

        $query->where('oi.order_id', $orderId);
    }
    if (!empty($deliveryBoyId)) {
        $query->where('oi.delivery_boy_id', $deliveryBoyId);
    }
    if (!empty($sellerId)) {
        $query->where('oi.seller_id', $sellerId);
    }
    if (!empty($startDate) && !empty($endDate)) {
        $query->whereDate('oi.created_at', '>=', $startDate)
            ->whereDate('oi.created_at', '<=', $endDate);
    }
    if (!empty($startDate)) {
        $query->whereDate('o.created_at', '>=', $startDate);
    }
    if (!empty($endDate)) {
        $query->whereDate('o.created_at', '<=', $endDate);
    }
    if (!empty($search)) {
        $query->where(function ($subQuery) use ($search) {
            $subQuery->orWhere('u.username', 'like', '%' . $search . '%')
                ->orWhere('u.email', 'like', '%' . $search . '%')
                ->orWhere('oi.id', 'like', '%' . $search . '%');
        });
    }
    // Apply sorting
    if ($sort === 'created_at') {
        $sort = 'oi.created_at';
    }
    $query->orderBy($sort, $order);

    // Clone the query for the total count before applying pagination
    $totalQuery = clone $query;
    $totalCount = $totalQuery->count(DB::raw('DISTINCT oi.id')); // Count distinct order items to avoid duplicate rows due to joins

    // Apply pagination (limit and offset)
    if ($limit && $offset) {
        $query->skip($offset)->take($limit);
    }

    // Retrieve the order item data
    $order_item_data = $query->groupBy('oi.id')->get();

    // Initialize order details array
    $order_details = [];

    for ($k = 0; $k < count($order_item_data); $k++) {
        // dd($order_item_data[$k]->product_id);
        $multipleWhere = ['seller_id' => $order_item_data[$k]->seller_id, 'order_id' => $order_item_data[$k]->order_id];
        $order_charge_data = OrderCharges::where($multipleWhere)->get()->toArray();
        $return_request = fetchDetails('return_requests', ['user_id' => $userId]);
        // dd($order_item_data[$k]->order_type);
        if ($order_item_data[$k]->order_type == 'regular_order') {
            $product_name = getDynamicTranslation(
                'products',
                'name',
                $order_item_data[$k]->product_id,
                $language_code
            );
        } elseif ($order_item_data[$k]->order_type == 'combo_order') {
            $product_name = getDynamicTranslation(
                'combo_products',
                'title',
                $order_item_data[$k]->product_variant_id,
                $language_code
            );
        }

        $order_item_data[$k]->pname = $product_name;
        // Decode status
        $order_item_data[$k]->status = json_decode($order_item_data[$k]->status);
        // Handle OTP logic
        if ($order_item_data[$k]->otp != 0) {
            $order_item_data[$k]->otp = $order_item_data[$k]->otp;
        } elseif ($order_charge_data[0]['otp'] != 0) {
            $order_item_data[$k]->otp = $order_charge_data[0]['otp'];
        } else {
            $order_item_data[$k]->otp = '';
        }

        // Format status dates
        if (!empty($order_item_data[$k]->status)) {
            foreach ($order_item_data[$k]->status as $index => $status) {
                $order_item_data[$k]->status[$index][1] = date('d-m-Y h:i:sa', strtotime($status[1]));
            }
        }
        $order_item_data[$k]->image = getMediaImageUrl($order_item_data[$k]->image);
        // Set default values if not available
        $order_item_data[$k]->delivery_boy_id = $order_item_data[$k]->delivery_boy_id ?: '';
        $delivery_boy = User::where('id', $order_item_data[$k]->delivery_boy_id)->select('username')->get()->toarray();
        $delivery_boy = isset($delivery_boy) && !empty($delivery_boy[0]) ? $delivery_boy[0]['username'] : "";
        $order_item_data[$k]->discounted_price = $order_item_data[$k]->discounted_price ?: '';
        $order_item_data[$k]->deliver_by = $delivery_boy;

        // Variants data
        $variant_data = getVariantsValuesById($order_item_data[$k]->product_variant_id);
        $order_item_data[$k]->variant_ids = $variant_data[0]['variant_ids'] ?? '';
        $order_item_data[$k]->variant_values = $variant_data[0]['variant_values'] ?? '';
        $order_item_data[$k]->attr_name = $variant_data[0]['attr_name'] ?? '';


        // Handle return/cancel logic
        $returnable_count = 0;
        $cancelable_count = 0;
        $already_returned_count = 0;
        $already_cancelled_count = 0;
        $return_request_submitted_count = 0;
        $total_tax_percent = 0;
        $total_tax_amount = 0;
        // Aggregate returnable and cancelable status
        if (!in_array($order_item_data[$k]->active_status, ['returned', 'cancelled'])) {
            $total_tax_percent += $order_item_data[$k]->tax_percent;
            $total_tax_amount += $order_item_data[$k]->tax_amount;
        }
        $order_item_data[$k]->is_already_returned = ($order_item_data[$k]->active_status == 'returned') ? '1' : '0';
        $order_item_data[$k]->is_already_cancelled = ($order_item_data[$k]->active_status == 'cancelled') ? '1' : '0';

        $return_request_key = array_search($order_item_data[$k]->id, array_column($return_request, 'order_item_id'));
        if ($return_request_key !== false) {
            $order_item_data[$k]->return_request_submitted = $return_request[$return_request_key]->status;
            if ($order_item_data[$k]->return_request_submitted == '1') {
                $return_request_submitted_count++;
            }
        } else {
            $order_item_data[$k]->return_request_submitted = '';
        }

        $returnable_count += $order_item_data[$k]->is_returnable;
        $cancelable_count += $order_item_data[$k]->is_cancelable;
        $already_returned_count += $order_item_data[$k]->is_already_returned;
        $already_cancelled_count += $order_item_data[$k]->is_already_cancelled;

        // Prepare final order details for each item
        $order_details[$k]['is_returnable'] = ($returnable_count >= 1) ? '1' : '0';
        $order_details[$k]['is_cancelable'] = ($cancelable_count >= 1) ? '1' : '0';
        $order_details[$k]['is_already_returned'] = ($already_returned_count == count($order_item_data)) ? '1' : '0';
        $order_details[$k]['is_already_cancelled'] = ($already_cancelled_count == count($order_item_data)) ? '1' : '0';
        $order_details[$k]['return_request_submitted'] = ($return_request_submitted_count == count($order_item_data)) ? '1' : '0';

        $order_details[$k]['username'] = outputEscaping($order_item_data[$k]->username);
        $order_details[$k]['total_tax_percent'] = strval($total_tax_percent);
        $order_details[$k]['total_tax_amount'] = strval($total_tax_amount);
    }
    // Prepare final response
    // dd($order_item_data);
    $order_data['total'] = $totalCount;
    $order_data['order_data'] = (!empty($order_item_data)) ? array_values($order_item_data->toArray()) : [];
    $order_data['order_details'] = (!empty($order_details)) ? $order_details : [];
    return $order_data;
}


function getTransactions($id = '', $user_id = '', $transaction_type = '', $type = '', $search = '', $offset = 0, $limit = 25, $sort = 'id', $order = 'DESC')
{
    $where = $multipleWhere = [];
    $countQuery = DB::table('transactions')->select(DB::raw('COUNT(id) as total'));

    if (!empty($user_id)) {
        $where[] = ['user_id', '=', $user_id];
    }

    if (!empty($transaction_type)) {
        $where[] = ['transaction_type', '=', $transaction_type];
    }
    if (!empty($type)) {
        $where[] = ['type', '=', $type];
    }

    if (!empty($id)) {
        $where[] = ['id', '=', $id];
    }

    if (!empty($search)) {
        $multipleWhere = [
            ['id', 'LIKE', '%' . $search . '%'],
            ['transaction_type', 'LIKE', '%' . $search . '%'],
            ['type', 'LIKE', '%' . $search . '%'],

        ];
    }

    if (!empty($where)) {
        $countQuery->where($where);
    }

    if (!empty($multipleWhere)) {
        $countQuery->where(function ($query) use ($multipleWhere) {
            foreach ($multipleWhere as $condition) {
                $query->orWhere($condition[0], $condition[1], $condition[2]);
            }
        });
    }

    $total = $countQuery->first()->total;

    $transactionsQuery = DB::table('transactions')->select('*');

    if (!empty($where)) {
        $transactionsQuery->where($where);
    }

    if (!empty($multipleWhere)) {
        $transactionsQuery->where(function ($query) use ($multipleWhere) {
            foreach ($multipleWhere as $condition) {
                $query->orWhere($condition[0], $condition[1], $condition[2]);
            }
        });
    }

    $transactions = $transactionsQuery
        ->offset($offset)
        ->limit($limit)
        ->orderBy($sort, $order)
        ->get();
    $transactions = $transactions->map(function ($item) {
        // Replace \n and \r with empty string in the message field
        $item->message = str_replace(["\n", "\r"], '', $item->message);

        // Check for null values in all fields and replace them with an empty string
        array_walk_recursive($item, function (&$value) {
            $value = $value === null ? '' : $value;
        });

        return $item;
    });



    // Your additional data processing here

    return ['data' => $transactions, 'total' => $total];
}

function countProductsStockLowStatus($seller_id = "", $store_id = "")
{

    $settings = getSettings('system_settings', true);
    $settings = json_decode($settings, true);
    $lowStockLimit = isset($settings['low_stock_limit']) ? $settings['low_stock_limit'] : 5;

    $countRes = DB::table('products as p')
        ->selectRaw('COUNT(DISTINCT(p.id)) as total')
        ->join('product_variants', 'product_variants.product_id', '=', 'p.id')
        ->whereNotNull('p.stock_type')
        ->where(function ($query) use ($lowStockLimit) {
            $query->where('p.stock', '<=', $lowStockLimit)
                ->where('p.availability', '=', '1')
                ->orWhere(function ($query) use ($lowStockLimit) {
                    $query->where('product_variants.stock', '<=', $lowStockLimit)
                        ->where('product_variants.availability', '=', '1');
                });
        });
    if (!empty($seller_id) && $seller_id != '') {
        $countRes->where('p.seller_id', '=', $seller_id);
    }

    if (!empty($store_id) && $store_id != '') {
        $countRes->where('p.store_id', '=', $store_id);
    }


    $productCount = $countRes->first();
    $regularProductCount = $productCount->total;

    $combocountRes = DB::table('combo_products as cp')
        ->selectRaw('COUNT(DISTINCT(cp.id)) as total')
        ->where('cp.seller_id', '=', $seller_id)
        ->where('cp.store_id', '=', $store_id)
        ->whereRaw('CAST(cp.stock AS SIGNED) <= ?', [$lowStockLimit]) // Cast stock to integer
        ->where('cp.availability', '=', '1');

    $comboCount = $combocountRes->first();
    $comboProductCount = $comboCount->total;

    return $regularProductCount + $comboProductCount;
}


function countProductsAvailabilityStatus($seller_id = "", $store_id = "")
{
    $countRes = DB::table('products as p')
        ->selectRaw('COUNT(DISTINCT(p.id)) as total')
        ->join('product_variants', 'product_variants.product_id', '=', 'p.id')
        ->whereNotNull('p.stock_type')
        ->where(function ($query) {
            $query->where('p.stock', 0)
                ->where('p.availability', 0)
                ->orWhere(function ($query) {
                    $query->where('product_variants.stock', 0)
                        ->where('product_variants.availability', 0);
                });
        });

    if (!empty($seller_id) && $seller_id != '') {
        $countRes->where('p.seller_id', $seller_id);
    }
    if (!empty($store_id) && $store_id != '') {
        $countRes->where('p.store_id', $store_id);
    }

    $productCount = $countRes->first();

    return $productCount->total;
}

function countProducts($seller_id = "", $store_id = "")
{
    $query = DB::table('products');

    if (!empty($seller_id) && $seller_id != '') {
        $query->where('seller_id', '=', $seller_id);
    }
    if (!empty($store_id) && $store_id != '') {
        $query->where('store_id', '=', $store_id);
    }

    $count = $query->count();

    return $count;
}

function count_new_user()
{
    $count = DB::table('users as u')
        ->selectRaw('COUNT(u.id) as counter')
        ->where('u.role_id', 2)
        ->count();

    return $count;
}

// get store id from session

function getStoreId()
{
    return session('store_id') !== null && !empty(session('store_id')) ? session('store_id') : "";
}

// function getDeliveryBoys($id, $search, $offset, $limit, $sort, $order, $seller_city = '', $seller_zipcode = '', $store_deliverability_type = '', $seller_zone_ids = "")
// {
//     // dd($seller_zone_ids);
//     $multipleWhere = '';
//     $where = [];
//     $where['u.role_id'] = 3;

//     if (!empty($search)) {
//         $multipleWhere = [
//             'u.id',
//             'u.username',
//             'u.email',
//             'u.mobile',
//             'c.name',
//         ];
//     }

//     if (!empty($id)) {
//         $where['u.id'] = $id;
//     }
//     // dd($seller_zone_ids);
//     // Count query
//     $count_res = DB::table('users as u')
//         ->select(DB::raw('COUNT(DISTINCT u.id) as total'), 'c.name as city_name')
//         ->leftJoin('cities as c', 'u.city', '=', 'c.id')
//         ->where(function ($query) use ($seller_zone_ids) {
//             if (!empty($seller_zone_ids)) {
//                 foreach ($seller_zone_ids as $zone_id) {
//                     $query->orWhereRaw("FIND_IN_SET(?, u.serviceable_zones)", [$zone_id]);
//                 }
//             }
//         })
//         ->groupBy('c.name');

//     if (!empty($multipleWhere)) {
//         $count_res->where(function ($query) use ($multipleWhere, $search) {
//             foreach ($multipleWhere as $field) {
//                 $query->orWhere($field, 'like', "%$search%");
//             }
//         });
//     }

//     if (!empty($where)) {
//         $count_res->where($where);
//     }

//     $delivery_boy_count = $count_res->get()->first();
//     $total = $delivery_boy_count ? $delivery_boy_count->total : 0;

//     // Search query
//     $search_res = DB::table('users as u')
//         ->select('u.*', 'c.name as city_name')
//         ->leftJoin('cities as c', 'u.city', '=', 'c.id')
//         ->where(function ($query) use ($seller_zone_ids) {
//             if (!empty($seller_zone_ids)) {
//                 foreach ($seller_zone_ids as $zone_id) {
//                     $query->orWhereRaw("FIND_IN_SET(?, u.serviceable_zones)", [$zone_id]);
//                 }
//             }
//         });

//     if (!empty($multipleWhere)) {
//         $search_res->where(function ($query) use ($multipleWhere, $search) {
//             foreach ($multipleWhere as $field) {
//                 $query->orWhere($field, 'like', "%$search%");
//             }
//         });
//     }

//     if (!empty($where)) {
//         $search_res->where($where);
//     }

//     $delivery_boy_search_res = $search_res->groupBy('u.id')
//         ->orderBy($sort, $order)
//         ->skip($offset)
//         ->take($limit)
//         ->get();

//     $delivery_boy_search_res = $delivery_boy_search_res->toArray();
//     $rows = [];
//     $bulkData = [];
//     $bulkData['error'] = empty($delivery_boy_search_res);
//     $bulkData['message'] = empty($delivery_boy_search_res) ? 'Delivery(s) does not exist' : 'Delivery boys retrieved successfully';
//     $bulkData['total'] = empty($delivery_boy_search_res) ? 0 : $total;
//     $bulkData['data'] = [];

//     if (!empty($delivery_boy_search_res)) {
//         foreach ($delivery_boy_search_res as $row) {
//             $tempRow['id'] = $row->id ?? '';
//             $tempRow['name'] = str_replace("\r\n", "", $row->username) ?? '';
//             $tempRow['mobile'] = $row->mobile ?? '';
//             $tempRow['email'] = $row->email ?? '';
//             $tempRow['balance'] = $row->balance ?? '';
//             $tempRow['city'] = $row->city_name ?? '';
//             $tempRow['image'] = getMediaImageUrl($row->image, 'DELIVERY_BOY_IMG_PATH');
//             $tempRow['street'] = $row->street ?? '';
//             $tempRow['status'] = $row->active ?? '';
//             $tempRow['date'] = Carbon::parse($row->created_at)->format('d-m-Y') ?? '';

//             $rows[] = $tempRow;
//         }
//         $bulkData['data'] = $rows;
//     }

//     return $bulkData;
// }
function getDeliveryBoys($id, $search, $offset, $limit, $sort, $order, $seller_city = '', $seller_zipcode = '', $store_deliverability_type = '', $seller_zone_ids = "", $deliverable_type = "")
{
    // dd($deliverable_type);
    $multipleWhere = '';
    $where = ['u.role_id' => 3];

    if (!empty($search)) {
        $multipleWhere = [
            'u.id',
            'u.username',
            'u.email',
            'u.mobile',
            'c.name',
        ];
    }

    if (!empty($id)) {
        $where['u.id'] = $id;
    }
    // dd($store_deliverability_type);
    // Count Query
    $count_res = DB::table('users as u')
        ->select(DB::raw('COUNT(DISTINCT u.id) as total'), 'c.name as city_name')
        ->leftJoin('cities as c', 'u.city', '=', 'c.id')
        ->when($deliverable_type != 1 && !empty($seller_zone_ids), function ($query) use ($seller_zone_ids) {
            $query->where(function ($subQuery) use ($seller_zone_ids) {
                foreach ($seller_zone_ids as $zone_id) {
                    $subQuery->orWhereRaw("FIND_IN_SET(?, u.serviceable_zones)", [$zone_id]);
                }
            });
        })
        ->where($where)
        ->groupBy('c.name');

    if (!empty($multipleWhere)) {
        $count_res->where(function ($query) use ($multipleWhere, $search) {
            foreach ($multipleWhere as $field) {
                $query->orWhere($field, 'like', "%$search%");
            }
        });
    }

    $delivery_boy_count = $count_res->get()->first();
    $total = $delivery_boy_count ? $delivery_boy_count->total : 0;

    // Search Query
    $search_res = DB::table('users as u')
        ->select('u.*', 'c.name as city_name')
        ->leftJoin('cities as c', 'u.city', '=', 'c.id')
        ->when($deliverable_type != 1 && !empty($seller_zone_ids), function ($query) use ($seller_zone_ids) {
            $query->where(function ($subQuery) use ($seller_zone_ids) {
                foreach ($seller_zone_ids as $zone_id) {
                    $subQuery->orWhereRaw("FIND_IN_SET(?, u.serviceable_zones)", [$zone_id]);
                }
            });
        })
        ->where($where);

    if (!empty($multipleWhere)) {
        $search_res->where(function ($query) use ($multipleWhere, $search) {
            foreach ($multipleWhere as $field) {
                $query->orWhere($field, 'like', "%$search%");
            }
        });
    }

    $delivery_boy_search_res = $search_res->groupBy('u.id')
        ->orderBy($sort, $order)
        ->skip($offset)
        ->take($limit)
        ->get()
        ->toArray();

    $rows = [];
    $bulkData = [];
    $bulkData['error'] = empty($delivery_boy_search_res);
    $bulkData['message'] = empty($delivery_boy_search_res) ? 'Delivery(s) does not exist' : 'Delivery boys retrieved successfully';
    $bulkData['total'] = empty($delivery_boy_search_res) ? 0 : $total;
    $bulkData['data'] = [];

    if (!empty($delivery_boy_search_res)) {
        foreach ($delivery_boy_search_res as $row) {
            $tempRow['id'] = $row->id ?? '';
            $tempRow['name'] = str_replace("\r\n", "", $row->username) ?? '';
            $tempRow['mobile'] = $row->mobile ?? '';
            $tempRow['email'] = $row->email ?? '';
            $tempRow['balance'] = $row->balance ?? '';
            $tempRow['city'] = $row->city_name ?? '';
            $tempRow['image'] = getMediaImageUrl($row->image, 'DELIVERY_BOY_IMG_PATH');
            $tempRow['street'] = $row->street ?? '';
            $tempRow['status'] = $row->active ?? '';
            $tempRow['date'] = Carbon::parse($row->created_at)->format('d-m-Y') ?? '';

            $rows[] = $tempRow;
        }
        $bulkData['data'] = $rows;
    }

    return $bulkData;
}
function getUserBalance($user_id)
{
    $user = User::where('id', $user_id)->select('balance')->first();

    return $user ? $user->balance : 0;
}

function addBankTransferProof($data)
{

    foreach ($data['attachments'] as $attachment) {

        OrderBankTransfers::create([
            'order_id' => $data['order_id'],
            'attachments' => $attachment['image_path'],
        ]);
    }

    return true;
}


function sendDigitalProductMail($to, $subject, $emailMessage, $attachment)
{

    try {
        Mail::send([], [], function (Message $message) use ($to, $subject, $emailMessage, $attachment) {
            $email_settings = getSettings('email_settings', true);
            $email_settings = json_decode($email_settings, true);
            $message->to($to)
                ->subject($subject)
                ->html($emailMessage)
                ->from($email_settings['email'], env('APP_NAME'));
        });

        $response = [
            'error' => false,
            'message' => 'Email Sent'
        ];
    } catch (\Exception $e) {
        $response = [
            'error' => true,
            'message' => $e->getMessage()
        ];
    }

    return $response;
}

function sendCustomMail($to, $subject, $emailMessage, $attachment)
{

    try {
        Mail::send([], [], function (Message $message) use ($to, $subject, $emailMessage, $attachment) {
            $email_settings = getSettings('email_settings', true);
            $email_settings = json_decode($email_settings, true);
            $message->to($to)
                ->subject($subject)
                ->html($emailMessage)
                ->from($email_settings['email'], env('APP_NAME'));
        });

        $response = [
            'error' => false,
            'message' => 'Email Sent'
        ];
    } catch (\Exception $e) {
        $response = [
            'error' => true,
            'message' => $e->getMessage()
        ];
    }

    return $response;
}


function sendContactUsMail($from, $subject, $emailMessage)
{
    try {
        Mail::send([], [], function (Message $message) use ($from, $subject, $emailMessage) {
            $email_settings = getSettings('email_settings', true);
            $email_settings = json_decode($email_settings, true);
            $message->from($from)
                ->subject($subject)
                ->html($emailMessage)
                ->to($email_settings['email'], env('APP_NAME'));
        });

        $response = [
            'error' => false,
            'message' => 'Email Sent'
        ];
    } catch (\Exception $e) {
        $response = [
            'error' => true,
            'message' => $e->getMessage()
        ];
    }

    return $response;
}
function sendOrderConfirmation($email, $subject, $messageContent)
{
    Log::info("Attempting to send email to: $email");

    try {
        Mail::send([], [], function ($message) use ($email, $subject, $messageContent) {
            $message->to($email)
                ->subject($subject)
                ->html($messageContent);
        });

        Log::info("Mail sent successfully to: $email");

        return response()->json(['message' => 'Email sent successfully!']);
    } catch (\Exception $e) {
        Log::error("Mail sending failed: " . $e->getMessage());

        return response()->json(['error' => 'Mail sending failed', 'details' => $e->getMessage()], 500);
    }
}
function sendMailTemplate($to, $template_key, $givenLanguage = "", $data = [], $subjectData = [])
{
    if ($givenLanguage == "") {
        $givenLanguage = session("locale") ?? "default";
    }

    $viewpath = "components.utility.email_templates.$template_key.";
    if (View::exists($viewpath . $givenLanguage)) {
        $viewpath .= $givenLanguage;
    } else {
        $viewpath .= "default";
    }

    $emailMessage = view($viewpath, $data)->render();
    $subject = strip_tags(view($viewpath . "-subject", $subjectData)->render());
    $response = sendCustomMail($to, $subject, $emailMessage, "");
    return $response;
}

function deliveryBoyOrdersCount($status = "", $deliveryBoyId = "", $table = '')
{
    if ($table == 'parcels') {
        $query = Parcel::query()->selectRaw('count(DISTINCT `id`) as total');

        if (!empty($status)) {
            $query->where('active_status', $status);
        }
        if (!empty($deliveryBoyId)) {
            $query->where('delivery_boy_id', $deliveryBoyId);
        }

        $result = $query->get()->first();

        return $result->total;
    } else {
        $query = OrderItems::query()->selectRaw('count(DISTINCT `order_id`) as total');

        if (!empty($status)) {
            $query->where('active_status', $status);
        }

        if (!empty($deliveryBoyId)) {
            $query->where('delivery_boy_id', $deliveryBoyId);
        }

        $result = $query->get()->first();

        return $result->total;
    }
}

function validateOtp($otp, $orderItemId = null, $orderId = null, $sellerId = null, $parcel_id = '')
{

    $orderItem = OrderItems::where('id', $orderItemId)->first(['otp']);
    $parcel_details = Parcel::where('id', $parcel_id)->get();

    $orderCharge = OrderCharges::where('order_id', $orderId)
        ->where('seller_id', $sellerId)
        ->first(['otp']);
    if (
        ($orderItem && $orderItem->otp != 0 && $orderItem->otp == $otp) ||
        ($orderCharge && $orderCharge->otp != 0 && $orderCharge->otp == $otp) || ($parcel_details[0]->otp != 0 && $parcel_details[0]->otp == $otp)
    ) {
        return true;
    } else {
        return false;
    }
}

function deleteImage($id, $path, $field, $imgName, $tableName, $isJson = true)
{
    DB::beginTransaction();

    try {
        if ($isJson) {
            $imageSet = DB::table($tableName)->where('id', $id)->value($field);
            $diffNewImageSet = array_diff(json_decode($imageSet, true), [$imgName]);
            $newImageSet = json_encode(array_values($diffNewImageSet));
            DB::table($tableName)->where('id', $id)->update([$field => $newImageSet]);
        } else {
            DB::table($tableName)->where('id', $id)->update([$field => null]);
        }

        DB::commit();
        $response = true;
    } catch (\Exception $e) {
        DB::rollBack();
        $response = false;
    }

    return $response;
}

function getFormatedDate($date)
{
    $date = Carbon::parse($date);
    $formattedDate = $date->format('d-m-Y h:i:sa');
    return $formattedDate;
}

function verifyPaymentTransaction($transaction_id = '', $payment_method = '', $additional_data = [])
{
    $transaction_id = $transaction_id !== '' && $transaction_id !== null ? $transaction_id : '';
    $payment_method = $payment_method !== '' && $payment_method !== null ? $payment_method : '';
    $additional_data = $additional_data !== '' ? $additional_data : [];

    if (empty(trim($payment_method))) {
        $response = [
            'error' => true,
            'message' => 'Invalid payment method supplied',
            'code' => 102,
        ];
        return response()->json($response);
    }
    switch ($payment_method) {
        case 'razorpay':
            $razorpay = new Razorpay();
            $payment = $razorpay->fetch_payments($transaction_id);

            if (!empty($payment) && isset($payment['status'])) {

                if ($payment['status'] == 'authorized') {
                    /* if the payment is authorized try to capture it using the API */
                    $capture_response = $razorpay->capture_payment($payment['amount'], $transaction_id, $payment['currency']);
                    if ($capture_response['status'] == 'captured') {
                        $response['error'] = false;
                        $response['message'] = "Payment captured successfully";
                        $response['amount'] = $capture_response['amount'] / 100;
                        $response['data'] = $capture_response;
                        return $response;
                    } else if ($capture_response['status'] == 'refunded') {
                        $response['error'] = true;
                        $response['message'] = "Payment is refunded.";
                        $response['amount'] = $capture_response['amount'] / 100;
                        $response['data'] = $capture_response;
                        return $response;
                    } else {
                        $response['error'] = true;
                        $response['message'] = "Payment could not be captured.";
                        $response['amount'] = (isset($capture_response['amount'])) ? $capture_response['amount'] / 100 : 0;
                        $response['data'] = $capture_response;
                        return $response;
                    }
                } else if ($payment['status'] == 'captured') {
                    $response['error'] = false;
                    $response['message'] = "Payment captured successfully";
                    $response['amount'] = $payment['amount'] / 100;
                    $response['data'] = $payment;
                    return $response;
                } else if ($payment['status'] == 'created') {
                    $response['error'] = true;
                    $response['message'] = "Payment is just created and yet not authorized / captured!";
                    $response['amount'] = $payment['amount'] / 100;
                    $response['data'] = $payment;
                    return $response;
                } else {
                    $response['error'] = true;
                    $response['message'] = "Payment is " . ucwords($payment['status']) . "! ";
                    $response['amount'] = (isset($payment['amount'])) ? $payment['amount'] / 100 : 0;
                    $response['data'] = $payment;
                    return $response;
                }
            } else {
                $response['error'] = true;
                $response['message'] = "Payment not found by the transaction ID!";
                $response['amount'] = 0;
                $response['data'] = [];
                return $response;
            }
            break;
        case 'paystack':
            $paystack = new Paystack();
            $payment = $paystack->verify_transaction($transaction_id);

            if (!empty($payment)) {
                $payment = json_decode($payment, true);
                if (isset($payment['data']['status']) && $payment['data']['status'] == 'success') {
                    $response['error'] = false;
                    $response['message'] = "Payment is successful";
                    $response['amount'] = (isset($payment['data']['amount'])) ? $payment['data']['amount'] / 100 : 0;
                    $response['data'] = $payment;
                    return $response;
                } elseif (isset($payment['data']['status']) && $payment['data']['status'] != 'success') {
                    $response['error'] = true;
                    $response['message'] = "Payment is " . ucwords($payment['data']['status']) . "! ";
                    $response['amount'] = (isset($payment['data']['amount'])) ? $payment['data']['amount'] / 100 : 0;
                    $response['data'] = $payment;
                    return $response;
                } else {
                    $response['error'] = true;
                    $response['message'] = "Payment is unsuccessful! ";
                    $response['amount'] = (isset($payment['data']['amount'])) ? $payment['data']['amount'] / 100 : 0;
                    $response['data'] = $payment;
                    return $response;
                }
            } else {
                $response['error'] = true;
                $response['message'] = "Payment not found by the transaction ID!";
                $response['amount'] = 0;
                $response['data'] = [];
                return $response;
            }
            break;
    }
}


// function currentCurrencyPrice($price, $with_symbol = false)
// {
//     // $system_settings = getSettings('system_settings', true, true);
//     // $system_settings = json_decode($system_settings, true);
//     // if (!isset($system_settings['currency_setting'])) {
//     //     session()->forget("system_settings");
//     //     $system_settings = getSettings('system_settings', true, true);
//     //     $system_settings = json_decode($system_settings, true);
//     // }
//     // $currency_code = session('currency') ?? $system_settings['currency_setting']['code'];
//     // $currency_details = getCurrencyCodeSettings($currency_code);
//     // $currency_symbol = $currency_details[0]->symbol ?? $system_settings['currency_setting']['symbol'];
//     // $amount = (float) $price * number_format((float) $currency_details[0]->exchange_rate, 2);
//     // if ($with_symbol == true) {
//     //     return $currency_symbol . number_format($amount, 2);
//     // }
//     // return $amount;
//     $system_settings = getSettings('system_settings', true, true);
//     $system_settings = json_decode($system_settings, true);
//     $currency = DB::table('currencies')->select("*")->where(['is_default' => 1])->get()->toArray();
//     if ($currency != NULL) {
//         $currency = $currency[0];
//     }

//     $currency_symbol = $currency->symbol ?? $system_settings['currency_setting']['symbol'];
//     $amount = (float) $price * number_format((float) $currency->exchange_rate, 2);
//     // dd($currency->exchange_rate);
//     if ($with_symbol == true) {
//         return $currency_symbol . number_format($amount, 2);
//     }
//     return $amount;
// }
function currentCurrencyPrice($price, $with_symbol = false)
{
    $system_settings = getSettings('system_settings', true, true);
    $system_settings = json_decode($system_settings, true);
    if (!isset($system_settings['currency_setting'])) {
        session()->forget("system_settings");
        $system_settings = getSettings('system_settings', true, true);
        $system_settings = json_decode($system_settings, true);
    }
    $currency_code = session('currency') ?? $system_settings['currency_setting']['code'];

    $currency_details = getCurrencyCodeSettings($currency_code);
    $currency_symbol = $currency_details[0]->symbol ?? $system_settings['currency_setting']['symbol'];
    $amount = (float) $price * number_format((float) $currency_details[0]->exchange_rate, 2);
    if ($with_symbol == true) {
        return $currency_symbol . number_format($amount, 2);
    }
    return $amount;
}
function getPriceCurrency($price)
{
    $currencies = getAllCurrency();

    $tempRow = [];
    foreach ($currencies as $currency) {
        $amount = (float) $price * number_format((float) $currency->exchange_rate, 2);
        $tempRow['currency_code'] = $currency->code;
        $tempRow['symbol'] = $currency->symbol;
        $tempRow['exchange_rate'] = number_format((float) $currency->exchange_rate, 2);

        $tempRow['amount'] = formatePriceDecimal($amount);

        $rows[$currency->code] = $tempRow;
    }
    return $rows;
}

function getAuthenticatedUser()
{
    // Check the 'web' guard (users)
    if (Auth::guard('web')->check()) {
        return Auth::guard('web')->user();
    }
    // No user is authenticated
    return null;
}

function createRow($product, $variant, $category_name, $from_seller = '')
{

    $action = '<div class="d-flex align-items-center ">
                <a href="#" class="btn edit-seller-stock single_action_button" title="Edit" data-id="' . $variant->id . '" data-bs-toggle="modal" data-bs-target="#edit_modal">
                    <i class="bx bx-pencil mx-2"></i>
                </a>
                </div>';


    if ($product->stock_type == 0 || $product->stock_type == null) {

        $stock_status = $product->availability == 1 ? '<label class="badge bg-success">In Stock</label>' : '<label class="badge bg-danger">Out of Stock</label>';
    } else {
        $stock_status = $variant->availability == 1 ? '<label class="badge bg-success">In Stock</label>' : '<label class="badge bg-danger">Out of Stock</label>';
    }
    $price = isset($variant->special_price) && $variant->special_price > 0 ? $variant->special_price : $variant->price;
    $route_name = isset($from_seller) && $from_seller == '1' ? 'seller.dynamic_image' : 'admin.dynamic_image';
    $language_code = get_language_code();
    // Define the image parameters
    $image_params = [
        'url' => $product->image,
        'width' => 60,
        'quality' => 90
    ];

    // Generate the product image URL using the determined route name
    $product_image = route($route_name, $image_params);
    $tempRow = [
        'id' => $variant->id,
        'category_name' => getDynamicTranslation('categories', 'name', $category_name[0]->id, $language_code),

        'price' => formateCurrency(formatePriceDecimal($price)),
        'stock_count' => $product->stock_type == 0 ? $product->stock : $variant->stock,
        'stock_status' => $stock_status,

        'name' => '<div class="d-flex align-items-center"><a href="' . getMediaImageUrl($product_image) . '" data-lightbox="image-' . $variant->id . '"><img src=' . $product_image . ' class="rounded mx-2"></a><div class="ms-2"><p class="m-0">' . getDynamicTranslation('products', 'name', $product->id, $language_code) . '</p><p>' . (isset($variant->variant_values) ? '(' . str_replace(",", ", ", $variant->variant_values) . ')' : '') . '</p></div></div>',
        'operate' => $action
    ];
    return $tempRow;
}
function formateCurrency($price, $currency = '', $before = true)
{
    $baseCurrency = getDefaultCurrency()->symbol;

    $currency_symbol = isset($currency) && !empty($currency) ? $currency : $baseCurrency;
    if ($before == true) {
        return $currency_symbol . $price;
    } else {
        return $price . $currency_symbol;
    }
}
function formateCurrency_1($price, $currency = '', $before = true)
{
    $baseCurrency = getDefaultCurrency()->symbol;

    $currency_symbol = isset($currency) && !empty($currency) ? $currency : $baseCurrency;
    if ($before == true) {
        return $currency_symbol . $price;
    } else {
        return $price . $currency_symbol;
    }
}
function formatePriceDecimal($price)
{
    return number_format($price, 2);
}
function formatePriceDecimal_1($price)
{
    // If price is not numeric or null, set it to 0 or handle accordingly
    if (!is_numeric($price)) {
        $price = 0;
    }

    return number_format((float) $price, 2);
}

function create_label($variable, $title = '')
{
    if ($title == '') {
        $title = $variable;
    }

    return "
        <div class='col-md-12 col-lg-4'>
            <div class='mb-3'>
                <label for='$variable' class='form-label'>$title</label>
                <input type='text' class='form-control' name='$variable'
                       value='" . e(trans("admin_labels.$variable")) . "'>
            </div>
        </div>";
}
function create_front_label($variable, $title = '')
{
    if ($title == '') {
        $title = $variable;
    }

    return "
        <div class='col-md-12 col-lg-4'>
            <div class='mb-3'>
                <label for='$variable' class='form-label'>$title</label>
                <input type='text' class='form-control' name='$variable'
                       value='" . e(trans("front_messages.$variable")) . "'>
            </div>
        </div>";
}


if (!function_exists('renderCategories')) {
    function renderCategories($categories, $parent_id = 0, $depth = 0, $selected_id = null)
    {
        $language_code = get_language_code();
        $html = '';
        foreach ($categories as $category) {
            if ($category['parent_id'] == $parent_id) {
                $is_selected = ($category['id'] == $selected_id) ? 'selected' : '';
                $bold_style = ($depth == 0) ? 'font-weight: bold;' : '';
                $padding = str_repeat('&nbsp;', $depth * 4);
                $prefix = str_repeat('-', $depth); // use this if want to add - in left side of sub catgory name
                $html .= sprintf(
                    '<option value="%s" %s style="padding-left: %spx; %s">%s%s</option>',
                    htmlspecialchars($category['id']),
                    $is_selected,
                    $depth * 20,
                    $bold_style,
                    $padding,
                    htmlspecialchars(getDynamicTranslation('categories', 'name', $category['id'], $language_code))
                );
                $html .= renderCategories($categories, $category['id'], $depth + 1, $selected_id);
            }
        }
        return $html;
    }
}

function labels($path, $label)
{
    return !trans()->has($path) ? $label : trans($path);
}

function isForeignKeyInUse($tables, $columns, $id, $is_comma_seprated_values = 0)
{
    // Ensure $tables is an array
    $tables = is_array($tables) ? $tables : $tables;

    if ($is_comma_seprated_values == 1) {

        if (DB::table($tables)->whereRaw("FIND_IN_SET(?, $columns)", [$id])->exists()) {
            return true;
        }
    }
    if (is_string($tables)) {
        if (Schema::hasTable($tables) && Schema::hasColumn($tables, $columns)) {
            return DB::table($tables)->where($columns, $id)->exists();
        }
        return false;
    } else {
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                foreach ($columns as $column) {
                    if (Schema::hasColumn($table, $column)) {
                        if ($column === 'category_ids') {
                            if (DB::table($table)->whereRaw("FIND_IN_SET(?, $column)", [$id])->exists()) {
                                return true;
                            }
                        } else {
                            if (DB::table($table)->where($column, $id)->exists()) {
                                return true;
                            }
                        }
                    }
                }
            }
        }
        return false;
    }
}
function AdmintotalEarnings()
{
    $store_id = getStoreId();
    $select = DB::raw('SUM(sub_total) as total');

    $total = OrderItems::select($select)
        ->where('store_id', $store_id)
        ->get()
        ->first();

    $formattedTotal = number_format($total->total ?? 0, 2, '.', ',');

    return $formattedTotal;
}

function getFavorites($user_id, $limit = 10, $offset = 0, $store_id = null, $select = "*")
{
    $userFavorites = DB::table('favorites as f')
        ->join('products as p', 'p.id', '=', 'f.product_id')
        ->where('f.user_id', $user_id);

    $combo_favorites = DB::table('favorites as f')
        ->join('combo_products as p', 'p.id', '=', 'f.product_id')
        ->where('f.user_id', $user_id);
    if ($store_id != null) {
        $userFavorites->where('p.store_id', $store_id);
        $combo_favorites->where('p.store_id', $store_id);
    }
    $total = clone $userFavorites;
    $total = $total->select(DB::raw('COUNT(f.id) as total'))->get();

    $combo_total = clone $combo_favorites;
    $combo_total = $combo_total->select(DB::raw('COUNT(f.id) as total'))->get();

    $favorites = $userFavorites
        ->select('p.*')
        ->orderBy('f.id', 'DESC')
        ->limit($limit)
        ->offset($offset)
        ->get();
    $combo_favorites = $combo_favorites
        ->select('p.*')
        ->orderBy('f.id', 'DESC')
        ->limit($limit)
        ->offset($offset)
        ->get();

    $favorites = json_decode(json_encode($favorites), true);
    $combo_favorites = json_decode(json_encode($combo_favorites), true);
    $res['regular_product'] = array_map(function ($d) {
        $d['image_md'] = getImageUrl($d['image'], 'thumb', 'md');
        $d['image_sm'] = getImageUrl($d['image'], 'thumb', 'sm');
        $d['image'] = getImageUrl($d['image']);
        $d['variants'] = getVariantsValuesByPid($d['id']);
        $d['min_max_price'] = getMinMaxPriceOfProduct($d['id']);
        return $d;
    }, $favorites);
    $res['combo_products'] = array_map(function ($d) {
        $d['image_md'] = getImageUrl($d['image'], 'thumb', 'md');
        $d['image_sm'] = getImageUrl($d['image'], 'thumb', 'sm');
        $d['image'] = getImageUrl($d['image']);
        $d['variants'] = getVariantsValuesByPid($d['id']);
        $d['min_max_price'] = getMinMaxPriceOfProduct($d['id']);
        return $d;
    }, $combo_favorites);
    $res['favorites_count'] = $total[0]->total + $combo_total[0]->total;
    return $res;
}

function getComboProductFaqs($id = null, $product_id = null, $user_id = '', $search = '', $limit = '', $offset = '', $sort = '', $order = '', $is_seller = false, $seller_id = '')
{

    $limit = $limit != '' ? $limit : 10;

    $order = $order != '' ? $order : 'desc';
    $multipleWhere = [];
    if (!empty($search)) {
        $multipleWhere = [
            'pf.id' => $search,
            'pf.product_id' => $search,
            'pf.user_id' => $search,
            'pf.question' => $search,
            'pf.answer' => $search,
        ];
    }

    $query = DB::table('combo_product_faqs AS pf')
        ->leftJoin('users AS u', 'u.id', '=', 'pf.user_id')
        ->leftJoin('combo_products AS p', 'p.id', '=', 'pf.product_id');

    if (empty($sort)) {
        $sort = 'pf.id';
    }


    if (!empty($search)) {
        $query->where(function ($query) use ($multipleWhere) {
            foreach ($multipleWhere as $column => $value) {
                $query->orWhere($column, 'like', '%' . $value . '%');
            }
        });
    }

    if (!empty($id)) {
        $query->where('pf.id', $id);
    }

    if (!empty($product_id)) {
        $query->where('pf.product_id', $product_id);
    }

    if (!empty($user_id)) {
        $query->where('pf.user_id', $user_id);
    }

    if (!empty($seller_id)) {
        $query->where('pf.seller_id', $seller_id);
    }

    // Count total records
    $total = $query->count();

    // Retrieve data with pagination
    $data = $query
        ->select('pf.*', 'u.username')
        ->orderBy($sort, $order)
        ->offset($offset)
        ->limit($limit)
        ->get();

    $response = [
        'total' => $total,
        'data' => $data,
    ];

    return $response;
}

function fetchComboRating($productId = null, $userId = null, $limit = null, $offset = null, $sort = null, $order = null, $ratingId = null, $hasImages = null)
{

    $query = DB::table('combo_product_ratings as pr')
        ->leftJoin('users as u', 'u.id', '=', 'pr.user_id');

    $selectColumns = [
        'pr.*',
        'u.username as user_name',
        'u.image as user_profile',
    ];

    if (!empty($productId)) {
        $query->where('pr.product_id', $productId);
    }
    if (!empty($userId)) {

        $query->where('pr.user_id', $userId);
    }

    if (!empty($ratingId)) {
        $query->where('pr.id', $ratingId);
    }

    if (!empty($sort) && !empty($order)) {
        $query->orderBy($sort, $order);
    }

    if (!empty($limit) && !empty($offset)) {
        $query->skip($offset)->take($limit);
    }


    $appUrl = config('app.url');

    $productRatings = $query->get($selectColumns)->toArray();
    foreach ($productRatings as $rating) {
        $images = json_decode($rating->images, true);
        $rating->images = [];
        if (!empty($images)) {
            if ($images !== null) {
                foreach ($images as $image) {
                    $rating->images = [...$rating->images, getImageUrl($image)];
                }
            }
        } else {
            $rating->images = [];
        }
    }

    $totalRating = DB::table('combo_product_ratings as pr')
        ->leftJoin('users as u', 'u.id', '=', 'pr.user_id')
        ->where('pr.product_id', $productId)
        ->count('pr.id');

    $totalImages = DB::table('combo_product_ratings')
        ->where('product_id', $productId)
        ->whereNotNull('images')
        ->count(DB::raw('LENGTH(images) - LENGTH(REPLACE(images, ",", "")) + 1'));

    $totalReviewsWithImages = DB::table('combo_product_ratings as pr')
        ->where('product_id', $productId)
        ->whereNotNull('images')
        ->count('pr.id');

    $totalReviews = DB::table('combo_product_ratings as pr')
        ->where('product_id', $productId)
        ->select(
            DB::raw('count(pr.id) as total'),
            DB::raw('sum(case when CEIL(pr.rating) = 1 then 1 else 0 end) as rating_1'),
            DB::raw('sum(case when CEIL(pr.rating) = 2 then 1 else 0 end) as rating_2'),
            DB::raw('sum(case when CEIL(pr.rating) = 3 then 1 else 0 end) as rating_3'),
            DB::raw('sum(case when CEIL(pr.rating) = 4 then 1 else 0 end) as rating_4'),
            DB::raw('sum(case when CEIL(pr.rating) = 5 then 1 else 0 end) as rating_5')
        )
        ->first(); // Remove the count() method here

    // Check if $totalReviews is not null before accessing its properties

    if ($totalReviews) {
        $result = [
            'total_images' => $totalImages ?: $totalRating,
            'total_reviews_with_images' => $totalReviewsWithImages,
            'no_of_rating' => $totalRating,
            'total_reviews' => $totalReviews->total,
            'star_1' => $totalReviews->rating_1,
            'star_2' => $totalReviews->rating_2,
            'star_3' => $totalReviews->rating_3,
            'star_4' => $totalReviews->rating_4,
            'star_5' => $totalReviews->rating_5,
            'product_rating' => $productRatings,
        ];
    } else {
        $result = [
            'total_images' => $totalImages ?: $totalRating,
            'total_reviews_with_images' => $totalReviewsWithImages,
            'no_of_rating' => $totalRating,
            'total_reviews' => 0,
            'star_1' => 0,
            'star_2' => 0,
            'star_3' => 0,
            'star_4' => 0,
            'star_5' => 0,
            'product_rating' => $productRatings,
        ];
    }

    return $result;
}

if (!function_exists('isEmailConfigured')) {

    function isEmailConfigured()
    {

        $email_settings = getSettings('email_settings', true, true);
        $email_settings = json_decode($email_settings, true);

        if (
            isset($email_settings['email']) && !empty($email_settings['email']) &&
            isset($email_settings['password']) && !empty($email_settings['password']) &&
            isset($email_settings['smtp_host']) && !empty($email_settings['smtp_host']) &&
            isset($email_settings['smtp_port']) && !empty($email_settings['smtp_port'])
        ) {
            return true;
        } else {
            return false;
        }
    }
}

function getPreviousAndNextItemWithId($table, $id, $storeId)
{
    $previous_product = DB::table($table)
        ->where('id', '<', $id)
        ->where('store_id', $storeId)
        ->orderBy('id', 'DESC')
        ->first();
    $next_product = DB::table($table)
        ->where('id', '>', $id)
        ->where('store_id', $storeId)
        ->orderBy('id', 'ASC')
        ->first();
    $next_id = $next_product->id ?? null;
    $previous_id = $previous_product->id ?? null;
    if ($table == 'combo_products') {
        $products = fetchComboProduct(id: [($next_id), ($previous_id)]);
        $next_product = $products['combo_product'][0] ?? "";
        $previous_product = $products['combo_product'][1] ?? "";
    } else {
        $products = fetchProduct(null, null, [($next_id), ($previous_id)]);
        $next_product = $products['product'][0] ?? "";
        $previous_product = $products['product'][1] ?? "";
    }
    $result['next_product'] = $next_product;
    $result['previous_product'] = $previous_product;
    return $result;
}


function removeMediaFile($path, $disk)
{


    // Instantiate the Spatie Media Library Filesystem
    $mediaFileSystem = app(MediaFilesystem::class);

    // Instantiate the FilesystemFactory
    $filesystem = app(FilesystemFactory::class);

    // Instantiate the CustomFileRemover with the dependencies
    $fileRemover = new CustomFileRemover($mediaFileSystem, $filesystem);

    if ($disk == 's3') {
        // Get the last two segments of the path
        $path = implode('/', array_slice(explode('/', $path), -2));
    }


    $fileRemover->removeFile($path, $disk);
}

function getDeliveryChargeSetting($store_id)
{
    $res = fetchDetails('stores', ['id' => $store_id], ['delivery_charge_type', 'delivery_charge_amount', 'minimum_free_delivery_amount', 'product_deliverability_type']);
    if (isset($res) && !empty($res)) {
        return $res;
    } else {
        return false;
    }
}
// function for check isset and not empty
function isKeySetAndNotEmpty($array, $key)
{
    return isset($array[$key]) && !empty($array[$key]);
}

function getAttributeValuesByAttrName($attr_name)
{
    $attributes = fetchDetails('attributes', ['name' => $attr_name, 'status' => "1"]);
    $attribute_id = [];
}

function getAttributeIdsByValue($values, $names)
{
    $names = str_replace('-', ' ', $names);
    $attribute_ids = DB::table('attribute_values as av')
        ->select('av.id')
        ->join('attributes as a', 'av.attribute_id', '=', 'a.id')
        ->whereIn('av.value', $values)
        ->whereIn('a.name', $names)
        ->get()
        ->pluck('id')
        ->toArray();

    return $attribute_ids;
}
function getComboProductAttributeIdsByValue($values, $names)
{
    $names = str_replace('-', ' ', $names);
    $attribute_ids = DB::table('combo_product_attribute_values as av')
        ->select('av.id')
        ->join('combo_product_attributes as a', 'av.combo_product_attribute_id', '=', 'a.id')
        ->whereIn('av.value', $values)
        ->whereIn('a.name', $names)
        ->get()
        ->pluck('id')
        ->toArray();

    return $attribute_ids;
}

function calculatePriceWithTax($percentage, $price)
{
    $tax_percentage = explode(',', ($percentage));
    // $total_tax = array_sum($tax_percentage); add floatval becuse showing 500 in web
    $total_tax = array_sum(array_map('floatval', $tax_percentage));
    $price_tax_amount = $price * ($total_tax / 100);
    $price_with_tax_amount = $price + $price_tax_amount;

    return $price_with_tax_amount;
}

// sms gateway

function parse_sms(string $string = "", string $mobile = "", string $sms = "", string $country_code = "")
{
    $parsedString = str_replace("{only_mobile_number}", $mobile, $string);
    $parsedString = str_replace("{message}", $sms, $parsedString); // Use $parsedString as the third argument

    return $parsedString;
}
function set_user_otp($mobile, $otp)
{
    $dateString = date('Y-m-d H:i:s');
    $time = strtotime($dateString);

    $otps = fetchdetails('otps', ['mobile' => $mobile]);
    $data['otp'] = $otp;
    $data['created_at'] = $time;

    foreach ($otps as $user) {
        if (isset($user->mobile) && !empty($user->mobile)) {
            send_sms($mobile, "please don't share with anyone $otp");
            DB::table('otps')->where('id', $user->id)->update($data); // Updated to use the $data array

            return [
                "error" => false,
                "message" => "OTP sent successfully.",
                "data" => $data
            ];
        }
    }

    // If no user is found or an error occurs
    return [
        "error" => true,
        "message" => "Something went wrong."
    ];
}



function checkOTPExpiration($otpTime)
{

    $time = date('Y-m-d H:i:s');
    $currentTime = strtotime($time);
    $timeDifference = $currentTime - $otpTime;


    if ($timeDifference <= 60) {
        return [
            "error" => false,
            "message" => "Success: OTP is valid."
        ];
    } else {
        return [
            "error" => true,
            "message" => "OTP has expired."
        ];
    }
}

function getMediaImageUrl($image, $const = 'MEDIA_PATH')
{
    // dd($image);
    // check if image url is from s3 or loacl storage and return url according to that
    $imageUrl = !Str::contains($image, 'https:')
        ? (!empty($image) && file_exists(public_path(config('constants.' . $const) . $image)) ? asset(config('constants.' . $const) . $image) : asset(config('constants.NO_IMAGE')))
        : $image;

    return $imageUrl;
}

function getFrontMediaImageUrl($image, $const = 'MEDIA_PATH')
{

    // check if image url is from s3 or loacl storage and return url according to that
    $imageUrl = !Str::contains($image, 'https:')
        ? (!empty($image) && file_exists(public_path(config('constants.' . $const) . $image)) ? asset(config('constants.' . $const) . $image) : asset(config('constants.NO_IMAGE')))
        : $image;

    return $imageUrl;
}

function setUrlParameter($url, $paramName, $paramValue)
{
    $paramName = str_replace(' ', '-', $paramName);
    if ($paramValue == null || $paramValue == '') {
        return preg_replace('/[?&]' . preg_quote($paramName) . '=[^&#]*(#.*)?$/', '$1', $url);
    }
    $pattern = '/\b(' . preg_quote($paramName) . '=).*?(&|#|$)/';
    if (preg_match($pattern, $url)) {
        return preg_replace($pattern, '$1' . $paramValue . '$2', $url);
    }
    $url = preg_replace('/[?#]$/', '', $url);
    return $url . (strpos($url, '?') > 0 ? '&' : '?') . $paramName . '=' . $paramValue;
}


function customUrl($name)
{
    $store = session()->get('store_slug');
    if (Route::has($name)) {
        return route($name, ['store' => $store]);
    }
    $url = setUrlParameter($name, 'store', $store);

    return url($url);
}

function dynamic_image($image, $width, $quantity = 90)
{
    return route('front_end.dynamic_image', [
        'url' => getMediaImageUrl($image),
        'width' => $width,
        'quality' => $quantity,
    ]);
}


if (!function_exists('get_system_update_info')) {
    function get_system_update_info()
    {
        $updatePath = Config::get('constants.UPDATE_PATH');
        $updaterPath = $updatePath . 'updater.json';
        $subDirectory = (File::exists($updaterPath) && File::exists($updatePath . 'update/updater.json')) ? 'update/' : '';

        if (File::exists($updaterPath) || File::exists($updatePath . $subDirectory . 'updater.json')) {
            $updaterFilePath = File::exists($updaterPath) ? $updaterPath : $updatePath . $subDirectory . 'updater.json';
            $updaterContents = File::get($updaterFilePath);

            // Check if the file contains valid JSON data
            if (!json_decode($updaterContents)) {
                throw new \RuntimeException('Invalid JSON content in updater.json');
            }

            $linesArray = json_decode($updaterContents, true);

            if (!isset($linesArray['version'], $linesArray['previous'], $linesArray['manual_queries'], $linesArray['query_path'])) {
                throw new \RuntimeException('Invalid JSON structure in updater.json');
            }
        } else {
            throw new \RuntimeException('updater.json does not exist');
        }

        $dbCurrentVersion = Updates::latestById()->first();

        $data['db_current_version'] = $dbCurrentVersion ? $dbCurrentVersion->version : '0.0.0';
        // if ($data['db_current_version'] == $linesArray['version']) {
        //     $data['updated_error'] = true;
        //     $data['message'] = 'Oops!. This version is already updated into your system. Try another one.';
        //     return $data;
        // }
        if ($data['db_current_version'] == $linesArray['previous']) {
            $data['file_current_version'] = $linesArray['version'];
        } else {
            $data['sequence_error'] = true;
            $data['message'] = 'Oops!. Update must performed in sequence.';
            return $data;
        }

        $data['query'] = $linesArray['manual_queries'];
        $data['query_path'] = $linesArray['query_path'];

        return $data;
    }
}

if (!function_exists('get_current_version')) {

    function get_current_version()
    {
        $dbCurrentVersion = Updates::latestById()->first();
        return $dbCurrentVersion ? $dbCurrentVersion->version : '1.0.0';
    }
}
function sendCustomNotificationOnPaymentSuccess($order_id, $user_id)
{
    // Fetch custom notification template
    $custom_notification = fetchdetails('custom_messages', ['type' => 'place_order'], '*');

    // Replace placeholders in title
    $hashtag_order_id = '< order_id >';
    $title_template = json_encode($custom_notification[0]->title, JSON_UNESCAPED_UNICODE);
    $title_template_decoded = html_entity_decode($title_template);
    $title = str_replace($hashtag_order_id, $order_id, $title_template_decoded);
    $title = trim($title, '"');

    // Replace placeholders in message
    $hashtag_application_name = '< application_name >';
    $message_template = json_encode($custom_notification[0]->message, JSON_UNESCAPED_UNICODE);
    $message_template_decoded = html_entity_decode($message_template);
    $app_name = $system_settings['app_name'] ?? Setting::where('variable', 'app_name')->value('value');
    $message = str_replace($hashtag_application_name, $app_name, $message_template_decoded);
    $message = trim($message, '"');

    // Default FCM message and subject
    $fcm_admin_subject = !empty($custom_notification) ? $title : 'New order placed ID #' . $order_id;
    $fcm_admin_msg = !empty($custom_notification) ? $message : 'New order received for ' . $app_name . ', please process it.';

    // Fetch user FCM details
    $user_fcm = fetchdetails('users', ['id' => $user_id], ['fcm_id', 'mobile', 'email']);
    $user_fcm_id[] = !empty($user_fcm[0]->fcm_id) ? [$user_fcm[0]->fcm_id] : [];

    // Get Firebase project and service account details
    $firebase_project_id = Setting::where('variable', 'firebase_project_id')->value('value');
    $service_account_file = DB::table('settings')->where('variable', 'service_account_file')->value('value');

    // If Firebase details are available, send the notification
    if (!empty($user_fcm_id) && !empty($firebase_project_id) && !empty($service_account_file)) {
        $fcmMsg = [
            'title' => $fcm_admin_subject,
            'body' => $fcm_admin_msg,
            'image' => '',
            'type' => 'place_order',
        ];

        // Call function to send notification
        sendnotification('', $user_fcm_id, $fcmMsg);
    }
}

function getCityNamesFromIds($cityIds, $language_code = '')
{
    $cityIdsArray = explode(',', $cityIds);

    $cities = DB::table('cities')->whereIn('id', $cityIdsArray)->get();

    $translated_names = [];

    foreach ($cities as $city) {
        if ($language_code) {
            $translated_name = getDynamicTranslation('cities', 'name', $city->id, $language_code);
        } else {
            $translated_name = $city->name;
        }

        $translated_names[] = $translated_name;
    }

    return $translated_names;
}


function getZipcodesFromIds($zipcodeIds)
{
    $zipcodeIdsArray = explode(',', $zipcodeIds);

    // Fetch zipcodes from the database
    return DB::table('zipcodes')->whereIn('id', $zipcodeIdsArray)->pluck('zipcode')->toArray();
}
function getZones($request, $language_code = '')
{
    // dd($request);
    $validator = Validator::make($request->all(), [
        'limit' => 'numeric',
        'offset' => 'numeric',
        'search' => 'string|nullable',
        'seller_id' => 'numeric|nullable',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'error' => true,
            'message' => $validator->errors()->all(),
            'code' => 102,
        ]);
    }

    $order = $request->input('order', 'DESC');
    $sort = $request->input('sort', 'id');
    $limit = $request->input('limit', 25);
    $offset = $request->input('offset', 0);
    $search = trim($request->input('search'));
    $seller_id = $request->input('seller_id');
    $language_code = isset($language_code) && !empty($language_code) ? $language_code : $request->input('language_code');
    // dd($language_code);
    // Base query
    $query = Zone::where('status', 1);

    if ($seller_id) {
        $seller = DB::table('seller_store as ss')
            ->where('ss.seller_id', $seller_id)
            ->select('ss.deliverable_type', 'ss.deliverable_zones', 'ss.seller_id')
            ->first();
        if ($seller && !empty($seller->deliverable_zones)) {
            $zone_ids = is_string($seller->deliverable_zones) ? explode(',', $seller->deliverable_zones) : $seller->deliverable_zones;
            $query->whereIn('id', $zone_ids);
        } else {
            return response()->json([
                'error' => true,
                'message' => 'No deliverable zones found for this seller',
                'language_message_key' => 'seller_zones_not_found',
                'total' => 0,
                'data' => [],
            ]);
        }
    }

    // Search filter
    if ($search) {
        $query->where('name', 'like', '%' . $search . '%');
    }

    $total = $query->count();
    $zones = $query->orderBy($sort, $order)
        ->offset($offset)
        ->limit($limit)
        ->get();

    $city_ids = [];
    $zipcode_ids = [];

    foreach ($zones as $zone) {
        $ids = is_string($zone->serviceable_city_ids) ? explode(',', $zone->serviceable_city_ids) : $zone->serviceable_city_ids;
        $city_ids = array_merge($city_ids, $ids);

        $ids = is_string($zone->serviceable_zipcode_ids) ? explode(',', $zone->serviceable_zipcode_ids) : $zone->serviceable_zipcode_ids;
        $zipcode_ids = array_merge($zipcode_ids, $ids);
    }

    $city_ids = array_unique($city_ids);
    $zipcode_ids = array_unique($zipcode_ids);

    $cities = City::whereIn('id', $city_ids)->get()->keyBy('id');
    $zipcodes = Zipcode::whereIn('id', $zipcode_ids)->get()->keyBy('id');

    $response_data = $zones->map(function ($zone) use ($cities, $zipcodes, $language_code) {
        $city_ids = is_string($zone->serviceable_city_ids) ? explode(',', $zone->serviceable_city_ids) : $zone->serviceable_city_ids;
        $zipcode_ids = is_string($zone->serviceable_zipcode_ids) ? explode(',', $zone->serviceable_zipcode_ids) : $zone->serviceable_zipcode_ids;

        return [
            'zone_id' => $zone->id,
            'zone_name' => getDynamicTranslation('zones', 'name', $zone->id, $language_code),
            'cities' => collect($city_ids)->map(function ($city_id) use ($cities, $language_code) {
                $city = $cities->get($city_id);
                return $city ? [
                    'city_id' => $city->id,
                    'city_name' => getDynamicTranslation('cities', 'name', $city->id, $language_code),
                    'delivery_charges' => $city->delivery_charges,
                ] : null;
            })->filter(),
            'zipcodes' => collect($zipcode_ids)->map(function ($zipcode_id) use ($zipcodes) {
                $zipcode = $zipcodes->get($zipcode_id);
                return $zipcode ? [
                    'zipcode_id' => $zipcode->id,
                    'zipcode' => $zipcode->zipcode,
                    'delivery_charges' => $zipcode->delivery_charges,
                ] : null;
            })->filter(),
        ];
    });

    return response()->json([
        'error' => $response_data->isEmpty(),
        'message' => $response_data->isEmpty() ? 'Zones not found' : 'Zones retrieved successfully',
        'language_message_key' => $response_data->isEmpty() ? 'zones_not_found' : 'zones_retrieved_successfully',
        'total' => $total,
        'data' => $response_data,
    ]);
}

function createParcel($request)
{

    $parcel_title = $request->parcel_title;
    $parcel_order_type = $request->parcel_order_type;
    $order_item_ids = $request->selected_items;
    $order_id = $request->order_id;
    $product_variant_ids = [];
    $items = fetchDetails('order_items', ['order_id' => $order_id], ['active_status', 'id', 'order_id', 'product_variant_id']);
    foreach ($items as $item) {
        foreach ($order_item_ids as $order_item_id) {
            if ($order_item_id == $item->id) {
                if (isExist(['order_item_id' => $item->id], 'parcel_items')) {
                    return [
                        "error" => true,
                        "message" => 'Parcel is Already Created!',
                    ];
                }
                array_push($product_variant_ids, $item->product_variant_id);
                if ($item->active_status == 'draft' || $item->active_status == 'awaiting') {
                    return [
                        "error" => true,
                        "message" => 'You can\'t ship order Right now Because Order is In Awaiting State, Payment verification is not Done Yet!',

                    ];
                }
                if ($item->active_status == 'cancelled' || $item->active_status == 'delivered') {
                    return [
                        "error" => true,
                        "message" => 'You can\'t ship Order Because Order is ' . $item->active_status,
                    ];
                }
            }
        }
    }
    $orders = fetchDetails('orders', ['id' => $order_id], ['delivery_charge', 'store_id']);
    if (isset($orders) && empty($orders)) {
        return [
            "error" => true,
            "message" => 'Order Not Found',

        ];
    }
    $status = "processed";

    $orders_delivery_charges = $orders[0]->delivery_charge;
    $store_id = $orders[0]->store_id;
    $parcels = fetchdetails('parcels', ['order_id' => $order_id], 'delivery_charge');
    $flag = false;
    $delivery_charge = "0";
    foreach ($parcels as $parcel) {
        if ($parcel->delivery_charge == $orders_delivery_charges) {
            $flag = true;
            break;
        }
    }
    if ($flag == false) {
        $delivery_charge = $orders_delivery_charges;
    }
    $otp = random_int(100000, 999999);

    if (isset($parcel_title) && !empty($parcel_title)) {
        $parcel = [
            'name' => $parcel_title,
            'type' => $parcel_order_type,
            'order_id' => $order_id,
            'store_id' => $store_id,
            'otp' => $otp,
            'delivery_charge' => $delivery_charge,
            'active_status' => $status,
            'status' => json_encode([["received", date("Y-m-d") . " " . date("h:i:sa")], ["processed", date("Y-m-d") . " " . date("h:i:sa")]]),
        ];
    } else {
        return [
            "error" => true,
            "message" => 'Please Enter Parcel Title',

        ];
    }
    if (isset($product_variant_ids) && empty($product_variant_ids)) {
        return [
            "error" => true,
            "message" => 'Product Variant Id not found',
        ];
    }
    $product_variant_id = is_string($product_variant_ids) ? explode(",", $product_variant_ids) : $product_variant_ids;
    $order_items_data = OrderItems::select(["product_variant_id", "quantity", "delivered_quantity", "id", "order_id", 'price'])->whereIn("product_variant_id", $product_variant_id)->where("order_id", $order_id)->get()->toArray();

    $parcel = Parcel::create($parcel);
    $parcel_id = $parcel->id;
    $parcel_data = [];
    $response = [];

    foreach ($order_items_data as $row) {
        $unit_price = $row['price'];
        $response[] = [
            "id" => $row["id"],
            "quantity" => (int) $row["quantity"],
            "unit_price" => $unit_price,
            "delivered_quantity" => (int) $row["quantity"],
            "product_variant_id" => $row["product_variant_id"],
            "parcel_id" => $parcel_id
        ];
        $parcel_data[] = [
            "parcel_id" => $parcel_id,
            "store_id" => $store_id,
            "order_item_id" => $row["id"],
            "quantity" => $row["quantity"],
            "unit_price" => $unit_price,
            "product_variant_id" => $row["product_variant_id"],
        ];
        updateOrder(['status' => $status], ['id' => $row["id"]], true, 'order_items');
        updateOrder(['active_status' => $status], ['id' => $row["id"]], false, 'order_items');
        updateDetails([
            "delivered_quantity" => (int) $row["quantity"]
        ], ["id" => $row["id"]], "order_items");
    }
    Parcelitem::insert($parcel_data);
    return [
        "error" => false,
        "message" => 'Parcel Created Successfully.',
        "data" => $response
    ];
}
function deleteParcel($parcel_id)
{
    $parcel_items = fetchDetails('parcel_items', ['parcel_id' => $parcel_id], ['order_item_id', 'quantity']);
    if (isset($parcel_items) && empty($parcel_items)) {
        return [
            "error" => true,
            "message" => 'parcel Not Found',
        ];
    }
    $parcel = fetchDetails('parcels', ['id' => $parcel_id], 'active_status');
    $priority_status = [
        'received' => 0,
        'processed' => 1,
        'shipped' => 2,
        'delivered' => 3,
        'return_request_pending' => 4,
        'return_request_decline' => 5,
        'cancelled' => 6,
        'returned' => 7,
    ];
    if (!empty($parcel)) {
        if ($priority_status[$parcel[0]->active_status] >= $priority_status['shipped']) {
            return [
                "error" => true,
                "message" => 'Cannot delete parcel after it has been Shipped',
            ];
        }
    }

    if (
        OrderTracking::where('parcel_id', $parcel_id)
        ->where('is_canceled', 0)
        ->where('shiprocket_order_id', '!=', '')
        ->exists()
    ) {
        return [
            "error" => true,
            "message" => 'The parcel cannot be deleted as a Shiprocket order has been created. Please cancel the Shiprocket order first.',
        ];
    }
    $order_item_id = [];
    foreach ($parcel_items as $item) {
        $order_item = fetchDetails('order_items', ['id' => $item->order_item_id], 'delivered_quantity');
        foreach ($order_item as $data) {
            $quantity = $item->quantity;
            $delivered_quantity = $data->delivered_quantity;
            $updated_delivered_quantity = (int) $delivered_quantity - (int) $quantity;

            updateDetails([
                "delivered_quantity" => $updated_delivered_quantity
            ], ["id" => $item->order_item_id], "order_items");
        }
        array_push($order_item_id, $item->order_item_id);
        updateOrder(['status' => json_encode([["received", date("d-m-y") . " " . date("h:i:sa")]])], ['id' => $item->order_item_id], false, 'order_items');
        updateOrder(['active_status' => 'received'], ['id' => $item->order_item_id], false, 'order_items');
    }
    deleteDetails(['id' => $parcel_id], 'parcels');
    deleteDetails(['parcel_id' => $parcel_id], 'parcel_items');

    $response_data = [];
    foreach ($order_item_id as $val) {
        $order_items = fetchDetails('order_items', ['id' => $val], ['id', 'product_variant_id', 'quantity', 'delivered_quantity', 'price']);
        foreach ($order_items as $order_item_data) {
            $unit_price = $order_item_data->price;
            $response_data[] = [
                "id" => $order_item_data->id,
                "delivered_quantity" => (int) $order_item_data->delivered_quantity,
                "quantity" => (int) $order_item_data->quantity,
                "product_variant_id" => $order_item_data->product_variant_id,
                "unit_price" => $unit_price
            ];
        }
    }
    return [
        "error" => false,
        "message" => 'Parcel Deleted Successfully.',
        "data" => $response_data
    ];
}

function viewAllParcels($order_id = '', $parcel_id = '', $seller_id = '', $offset = '', $limit = '', $order = 'DESC', $in_detail = 1, $delivery_boy_id = '', $multiple_status = '', $store_id = '', $parcel_type = "", $from_app = '')
{
    $order_parcel_type = '';
    if (!empty($parcel_type)) {
        $order_parcel_type = $parcel_type;
    } elseif ($parcel_id) {
        $order_parcel_type_data = fetchDetails('parcels', ['id' => $parcel_id], 'type');
        $order_parcel_type = $order_parcel_type_data[0]->type ?? "";
    }

    // Count query
    $count_query = DB::table('parcels as p')
        ->join('orders as o', 'p.order_id', '=', 'o.id')
        ->join('order_items as oi', 'oi.order_id', '=', 'p.order_id')
        ->join('users as u', 'u.id', '=', 'o.user_id')
        ->select(DB::raw('COUNT(DISTINCT(p.id)) as total'));

    // Apply filters to count query
    if (!empty($order_id)) {
        $count_query->where('o.id', $order_id);
    } elseif (!empty($seller_id)) {
        $count_query->where('oi.seller_id', $seller_id);
    }
    if (!empty($parcel_id)) {
        $count_query->where('p.id', $parcel_id);
    }
    if (!empty($delivery_boy_id)) {
        $count_query->where('p.delivery_boy_id', $delivery_boy_id);
    }
    if (!empty($order_parcel_type)) {
        $count_query->where('p.type', $order_parcel_type);
    }

    if (!empty($multiple_status)) {
        $count_query->whereIn('p.active_status', is_array($multiple_status) ? $multiple_status : [$multiple_status]);
    }

    $total = $count_query->first()->total ?? 0;

    // Search query
    $search_query = DB::table('parcels as p')
        ->join('parcel_items as pi', 'pi.parcel_id', '=', 'p.id')
        ->join('orders as o', 'p.order_id', '=', 'o.id')
        ->join('order_items as oi', 'oi.id', '=', 'pi.order_item_id')
        ->join('users as u', 'u.id', '=', 'o.user_id')
        ->select([
            'u.username',
            'u.email',
            'u.mobile',
            'u.latitude',
            'u.longitude',
            'u.image as user_profile',
            'p.id',
            'p.store_id',
            'p.order_id',
            'p.delivery_boy_id',
            'p.name',
            'p.status',
            'p.type',
            'p.active_status',
            'o.created_at',
            'p.otp',
            'p.created_at as parcel_created_at',
            'oi.seller_id',
            'o.payment_method',
            'o.address as user_address',
            'o.delivery_charge',
            'o.wallet_balance',
            'o.discount',
            'o.promo_discount',
            'o.total_payable',
            'o.notes',
            'o.delivery_date',
            'o.delivery_time',
            'o.is_cod_collected',
            'o.is_shiprocket_order',
            'o.final_total',
            'o.total',
        ])
        ->leftJoin('products as prod', function ($join) {
            $join->on('prod.id', '=', 'pi.product_variant_id')
                ->where('p.type', '!=', 'combo_order');
        })
        ->leftJoin('combo_products as combo_prod', function ($join) {
            $join->on('combo_prod.id', '=', 'pi.product_variant_id')
                ->where('p.type', '=', 'combo_order');
        });

    if (!empty($order_id)) {
        $search_query->where('o.id', $order_id);
    } elseif (!empty($seller_id)) {
        $search_query->where('oi.seller_id', $seller_id);
    }
    if (!empty($parcel_id)) {
        $search_query->where('p.id', $parcel_id);
    }
    if (!empty($delivery_boy_id)) {
        $search_query->where('p.delivery_boy_id', $delivery_boy_id);
    }

    if (!empty($multiple_status)) {
        $search_query->whereIn('p.active_status', is_array($multiple_status) ? $multiple_status : [$multiple_status]);
    }
    if ($from_app !== 1 && !empty($order_parcel_type)) {
        $search_query->where('p.type', $order_parcel_type);
        if ($order_parcel_type == 'combo_order') {
            $search_query->join('combo_products as cp', 'cp.id', '=', 'pi.product_variant_id')
                ->addSelect('cp.image as product_image');
        } else {
            $search_query->join('product_variants as pv', 'pv.id', '=', 'pi.product_variant_id')
                ->join('products as prod_alias', 'prod_alias.id', '=', 'pv.product_id')
                ->addSelect('prod_alias.image as product_image');
        }
    }

    // Execute search query
    $results = $search_query->groupBy('p.id')
        ->offset($offset)
        ->limit($limit)
        ->orderBy('p.id', $order)
        ->get();

    $parcel_list = [];

    foreach ($results as $row) {

        $parcel_id = $row->id;

        // Process sellers details
        $seller_details = DB::table('seller_store as ss')
            ->join('users as u', 'u.id', '=', 'ss.user_id')
            ->where('ss.seller_id', $row->seller_id)
            ->select('ss.store_name', 'u.username as seller_name', 'u.address', 'u.mobile', 'ss.logo as store_image', 'u.latitude', 'u.longitude')
            ->first();

        $delivery_boy_details = DB::table('users as u')
            ->where('u.id', $row->delivery_boy_id)
            ->select('u.id', 'u.username', 'u.address', 'u.mobile', 'u.email', 'u.image')
            ->first();

        if (isset($delivery_boy_details->image) && !empty($delivery_boy_details->image)) {
            $delivery_boy_details->image = getMediaImageUrl($delivery_boy_details->image);
        }

        if (!empty($seller_details->store_image)) {
            $seller_details->store_image = asset($seller_details->store_image);
        }

        // Tracking details
        $tracking_details = DB::table('order_trackings as ot')
            ->where('ot.parcel_id', $row->id)
            ->where('ot.is_canceled', 0)
            ->first();

        // Cancelled tracking details
        $cancelled_tracking_details = DB::table('order_trackings as ot')
            ->where('ot.parcel_id', $row->id)
            ->where('ot.is_canceled', 1)
            ->first();

        // Parcel items
        $parcel_items = DB::table('parcel_items')
            ->where('parcel_id', $parcel_id)
            ->get();
        $items = [];
        $subtotal = 0;
        $total_tax_amount = 0;
        $total_tax_percent = 0;
        $total_unit_price = 0;
        foreach ($parcel_items as $item) {
            $store_id = isset($store_id) && !empty($store_id) ? $store_id : $item->store_id;
            $order_item_details = [];
            if ($in_detail == 1) {
                $product_details = fetchOrderItems($item->order_item_id, '', '', '', '', '', 'id', 'DESC', '', '', '', $row->seller_id, $row->order_id, $store_id);

                if (!empty($product_details)) {
                    $total_tax_amount += isset($product_details['order_data'][0]->tax_amount) ? $product_details['order_data'][0]->tax_amount : 0;
                    $total_tax_percent += isset($product_details['order_data'][0]->tax_percent) ? $product_details['order_data'][0]->tax_percent : 0;
                    $subtotal += isset($product_details['order_data'][0]->sub_total) ? $product_details['order_data'][0]->sub_total : 0;
                    unsetUnnecessaryKeys($product_details);
                    $order_item_details = (array) $product_details;
                }
            }
            $total_unit_price += $item->unit_price;
            $order_item = [
                'id' => $item->id,
                'product_variant_id' => $item->product_variant_id,
                'order_item_id' => $item->order_item_id,
                'unit_price' => $item->unit_price,
                'quantity' => $item->quantity
            ] + $order_item_details;

            array_push($items, $order_item);
        }
        // Calculate total payable
        $total_order_items = DB::table('order_items')
            ->where('order_id', $row->order_id)
            ->count();
        $delivery_charges = $row->delivery_charge;
        $item_delivery_charges = $delivery_charges / $total_order_items * count($parcel_items);

        $total_discount_percentage = ($subtotal > 0 && $row->total > 0) ? calculatePercentage($subtotal, $row->total) : 0;

        $promo_discount = $row->promo_discount ? calculatePrice($total_discount_percentage, $row->promo_discount) : 0;
        $wallet_balance = $row->wallet_balance ? calculatePrice($total_discount_percentage, $row->wallet_balance) : 0;

        $row->wallet_balance = (string) (int) $wallet_balance;
        $row->total_payable = (string) (int) ($subtotal + $item_delivery_charges - $promo_discount - $wallet_balance);

        $parcel_data = [
            'id' => $row->id ?? "",
            'store_id' => $row->store_id ?? "",
            'parcel_type' => $row->parcel_type ?? "",
            'username' => $row->username ?? "",
            'email' => $row->email ?? "",
            'mobile' => $row->mobile ?? "",
            'order_id' => $row->order_id ?? "",
            'name' => $row->name ?? "",
            'parcel_name' => $row->name ?? "",
            'longitude' => $row->longitude ?? "",
            'latitude' => $row->latitude ?? "",
            'created_date' => $row->parcel_created_at ?? "",
            'otp' => $row->otp ?? "",
            'seller_id' => $row->seller_id ?? "",
            'payment_method' => $row->payment_method ?? "",
            'user_address' => $row->user_address ?? "",
            'user_profile' => asset($row->user_profile) ?? "",
            'total' => $row->total ?? "",
            'total_unit_price' => $total_unit_price,
            'delivery_charge' => $item_delivery_charges,
            'delivery_boy_id' => $row->delivery_boy_id ?? "",
            'wallet_balance' => $row->wallet_balance,
            'discount' => $row->discount,
            'tax_percent' => (string) $total_tax_percent,
            'tax_amount' => (string) $total_tax_amount,
            'promo_discount' => $promo_discount,
            'total_payable' => $row->total_payable,
            'final_total' => $row->final_total,
            'notes' => $row->notes ?? "",
            'delivery_date' => $row->delivery_date ?? "",
            'delivery_time' => $row->delivery_time ?? "",
            'is_cod_collected' => $row->is_cod_collected,
            'is_shiprocket_order' => $row->is_shiprocket_order ?? 0,
            'active_status' => $row->active_status,
            'status' => json_decode($row->status),
            'tracking_details' => $tracking_details,
            'cancelled_tracking_details' => $cancelled_tracking_details,
            'items' => $items,
            'seller_details' => $seller_details,
            'delivery_boy_details' => $delivery_boy_details
        ];

        array_push($parcel_list, $parcel_data);
    }
    return response()->json([
        'error' => empty($parcel_list) ? true : false,
        'message' => empty($parcel_list) ? 'No data found' : 'Parcel retrieved successfully',
        'data' => $parcel_list,
        'total' => $total
    ]);
}




function unsetUnnecessaryKeys(&$product_details)
{
    unset($product_details->order_item_id, $product_details->user_id, $product_details->delivery_boy_id, $product_details->order_id, $product_details->order_id, $product_details->discounted_price);
}

function calculatePercentage($total, $price)
{
    return ($price / $total) * 100;
}

function calculatePrice($totalDiscountPercentage, $price)
{
    return $totalDiscountPercentage > 0 ? ($totalDiscountPercentage * $price) / 100 : $price;
}
function ViewParcel($request, $orderId = null, $sellerId = null, $deliveryBoyId = null, $language_code = '')
{
    $offset = $request->input('offset', 0);
    $limit = $request->input('limit', 10);
    $sort = $request->input('sort', 'pc.id');
    $order = $request->input('order', 'desc');

    $paymentMethod = $request->input('payment_method');
    $orderStatus = $request->input('active_status');
    $search = trim($request->input('search'));
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');

    // Start building the query
    $query = DB::table('parcels as pc')
        ->join('parcel_items as pi', 'pi.parcel_id', '=', 'pc.id')
        ->join('orders as o', 'pc.order_id', '=', 'o.id')
        ->join('order_items as oi', 'oi.id', '=', 'pi.order_item_id')
        ->join('users as u', 'u.id', '=', 'o.user_id')
        ->select('pc.id', 'pc.order_id', 'pc.name', 'pc.active_status', 'pc.created_at', 'pc.otp')
        ->distinct();

    // Apply filters based on request parameters
    if ($orderId) {
        $query->where('o.id', $orderId);
    }

    if ($deliveryBoyId) {
        $query->where('pc.delivery_boy_id', $deliveryBoyId);
    }

    if ($sellerId) {
        $query->where('oi.seller_id', $sellerId);
    }

    if ($orderStatus) {
        if (strpos($orderStatus, ',') !== false) {
            $statuses = array_filter(array_map('trim', explode(',', $orderStatus)));
            $query->whereIn('pc.active_status', $statuses);
        } else {
            $query->where('pc.active_status', $orderStatus);
        }
    }

    if ($paymentMethod) {
        if ($paymentMethod == 'online-payment') {
            $query->where('o.payment_method', '!=', 'COD');
            $query->where('o.payment_method', '!=', 'cod');
        } else {
            $query->where('o.payment_method', $paymentMethod);
        }
    }

    if ($startDate && $endDate) {
        $query->whereDate('o.date_added', '>=', Carbon::parse($startDate)->toDateString())
            ->whereDate('o.date_added', '<=', Carbon::parse($endDate)->toDateString());
    }

    if ($search) {
        $query->where(function ($query) use ($search) {
            $query->where('pc.id', 'like', "%$search%")
                ->orWhere('pc.order_id', 'like', "%$search%")
                ->orWhere('pc.name', 'like', "%$search%")
                ->orWhere('pc.status', 'like', "%$search%")
                ->orWhere('pc.created_at', 'like', "%$search%");
        });
    }

    // Get the total count
    $total = $query->count('pc.id');

    // Get the paginated list of parcels
    $parcels = $query->orderBy($sort, $order)
        ->skip($offset)
        ->take($limit)
        ->get();

    $rows = [];
    foreach ($parcels as $parcel) {
        // Fetch parcel items and related order data
        $itemDetails = DB::table('parcel_items as pi')
            ->leftJoin('order_items as oi', 'oi.id', '=', 'pi.order_item_id')
            ->leftJoin('product_variants as pv', 'pv.id', '=', 'pi.product_variant_id')
            ->leftJoin('products as p', 'p.id', '=', 'pv.product_id')
            ->leftJoin('orders as o', 'o.id', '=', 'oi.order_id')
            ->leftJoin('users as u', 'u.id', '=', 'oi.user_id')
            ->leftJoin('parcels as pc', 'pc.id', '=', 'pi.parcel_id')
            ->leftJoin('seller_data as sd', 'sd.user_id', '=', 'oi.seller_id')
            ->leftJoin('seller_store as ss', 'ss.user_id', '=', 'oi.seller_id')
            ->where('pi.parcel_id', $parcel->id)
            ->select('oi.*', 'u.username', 'pc.active_status as active_status', 'pi.*', 'pv.product_id', 'ss.store_name', 'o.payment_method', 'o.mobile', 'p.image', 'o.created_at')
            ->get();
        // Create order link
        $orderLink = "<a href='" . route('delivery_boy.orders.edit', ['order' => $parcel->order_id]) . "' target='_blank'>" . $parcel->order_id . "</a>";

        $productNames = [];
        $quantities = [];
        foreach ($itemDetails as $itemDetail) {
            $translatedName = getDynamicTranslation('products', 'name', $itemDetail->product_id, $language_code);

            $productNames[] = $translatedName;
            $quantities[] = $itemDetail->quantity;
            $itemDetail->image = asset($itemDetail->image);
        }


        // Define action buttons for each parcel
        $operate = '<div class="dropdown bootstrap-table-dropdown">
                <a href="#" class="text-dark" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-ellipsis-v"></i>
                </a>
                <div class="dropdown-menu table_dropdown order_action_dropdown" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item" data-id="' . $parcel->id . '" data-items="' . json_encode($itemDetails) . '" href="' . route('delivery_boy.orders.edit', ['order' => $parcel->order_id, 'parcel_id' => $parcel->id]) . '">
                        <i class="bx bx-pencil"></i> Edit
                    </a>
                </div>
            </div>';
        $active_status = '<label class="badge ';
        switch ($parcel->active_status) {
            case 'awaiting':
                $active_status .= 'bg-secondary';
                break;
            case 'received':
                $active_status .= 'bg-primary';
                break;
            case 'processed':
                $active_status .= 'bg-info';
                break;
            case 'shipped':
                $active_status .= 'bg-warning';
                break;
            case 'delivered':
                $active_status .= 'bg-success';
                break;
            case 'returned':
            case 'cancelled':
                $active_status .= 'bg-danger';
                break;
            case 'return_request_decline':
                $active_status .= 'bg-danger';
                $parcel->active_status = 'Return Declined';
                break;
            case 'return_request_approved':
                $active_status .= 'bg-success';
                $parcel->active_status = 'Return Approved';
                break;
            case 'return_request_pending':
                $active_status .= 'bg-secondary';
                $parcel->active_status = 'Return Requested';
                break;
            default:
                $active_status .= 'bg-light text-dark';
        }
        $active_status .= '">' . $parcel->active_status . '</label>';
        $row = [
            'id' => $parcel->id,
            'order_link' => $orderLink,
            'order_id' => $parcel->order_id,
            'seller_id' => $parcel->seller_id ?? '',
            'username' => $itemDetails->first()->username ?? '',
            'mobile' => $itemDetails->first()->mobile ?? '',
            'product_name' => implode(', ', $productNames),
            'quantity' => implode(', ', $quantities),
            'name' => $parcel->name,
            'payment_method' => $itemDetails->first()->payment_method ?? '',
            'status' => $active_status,
            'active_status' => $parcel->active_status,
            'otp' => $parcel->otp ?? '',
            'created_at' => $parcel->created_at,
            'operate' => $operate,
            'parcel_items' => $itemDetails
        ];

        $rows[] = $row;
    }

    return [
        'total' => $total,
        'rows' => $rows
    ];
}
function getReturnOrderItemsList($deliveryBoyId = null, $search = "", $offset = 0, $limit = 10, $sort = "oi.id", $order = 'ASC', $sellerId = null, $fromApp = '0', $orderItemId = '', $isPrint = '0', $orderStatus = '', $paymentMethod = '')
{
    $filters = [
        'un.username' => $search,
        'u.username' => $search,
        'us.username' => $search,
        'un.email' => $search,
        'oi.id' => $search,
        'o.mobile' => $search,
        'o.address' => $search,
        'o.payment_method' => $search,
        'oi.sub_total' => $search,
        'o.delivery_time' => $search,
        'oi.active_status' => $search,
        'oi.product_name' => $search,
        'oi.created_at' => $search
    ];

    $baseQuery = DB::table('order_items as oi')
        ->join('users as u', 'u.id', '=', 'oi.delivery_boy_id', 'left')
        ->join('seller_store as ss', 'oi.seller_id', '=', 'ss.seller_id', 'left')
        ->join('users as us', 'us.id', '=', 'ss.user_id', 'left')
        ->join('orders as o', 'o.id', '=', 'oi.order_id', 'left')
        ->join('product_variants as v', 'oi.product_variant_id', '=', 'v.id', 'left')
        ->join('products as p', 'p.id', '=', 'v.product_id', 'left')
        ->join('users as un', 'un.id', '=', 'o.user_id', 'left');

    $applyFilters = function ($query) use ($filters, $deliveryBoyId, $sellerId, $orderItemId, $orderStatus, $paymentMethod) {
        if (!empty($filters)) {
            $query->where(function ($q) use ($filters) {
                foreach ($filters as $column => $value) {
                    $q->orWhere($column, 'like', '%' . $value . '%');
                }
            });
        }
        if ($deliveryBoyId) {
            $query->where('oi.delivery_boy_id', $deliveryBoyId)
                ->whereIn('oi.active_status', ['return_pickedup', 'return_request_approved', 'returned']);
        }
        if ($orderStatus) {
            $query->where('oi.active_status', $orderStatus);
        }
        if ($paymentMethod) {
            $query->where('o.payment_method', $paymentMethod);
        }
        if ($sellerId) {
            $query->where('oi.seller_id', $sellerId)
                ->where('oi.active_status', '!=', 'awaiting');
        }
        if ($orderItemId) {
            $query->where('oi.id', $orderItemId);
        }
        if (Request::has('user_id')) {
            $query->where('o.user_id', Request::get('user_id'));
        }
        if (Request::has('start_date') && Request::has('end_date')) {
            $query->whereBetween('oi.created_at', [Request::get('start_date'), Request::get('end_date')]);
        }
    };

    $total = (clone $baseQuery)
        ->select(DB::raw('COUNT(DISTINCT(o.id)) as total'))
        ->when(true, $applyFilters)
        ->value('total') ?? 0;

    $orderItems = $baseQuery
        ->join('order_trackings as ot', 'ot.order_item_id', '=', 'oi.id', 'left')
        ->join('transactions as t', 't.order_item_id', '=', 'oi.id', 'left')
        ->join('addresses as a', 'a.id', '=', 'o.address_id', 'left')
        ->select(
            'o.id as order_id',
            'o.created_at as order_date',
            'oi.id as order_item_id',
            'o.*',
            'oi.*',
            'ot.courier_agency',
            'ot.tracking_id',
            'ot.url',
            't.status as transaction_status',
            'u.username as delivery_boy',
            'un.username as username',
            'us.username as seller_name',
            'p.type as product_type',
            'p.image',
            'p.download_allowed',
            'a.*'
        )
        ->when(true, $applyFilters)
        ->groupBy('oi.order_id')
        ->orderBy($sort, $order)
        ->limit($limit)
        ->offset($offset)
        ->get();



    $rows = $orderItems->map(function ($row, $key) use ($deliveryBoyId) {

        $operate = '';
        if ($deliveryBoyId) {
            $operate = '<div class="d-flex align-items-center">
                <a href="' . url('delivery_boy/orders/' . $row->order_id . '/returned_orders/' . $row->order_item_id) . '" class="btn single_action_button" title="Edit">
                    <i class="bx bx-pencil mx-2"></i>
                </a>
            </div>';
        }

        $badgeClass = 'bg-secondary';
        $statusText = $row->active_status;

        switch ($row->active_status) {
            case 'returned':
            case 'cancelled':
                $badgeClass = 'bg-danger';
                break;
            case 'return_request_decline':
                $badgeClass = 'bg-danger';
                $statusText = 'Return Declined';
                break;
            case 'return_request_approved':
                $badgeClass = 'bg-success';
                $statusText = 'Return Approved';
                break;
            case 'return_request_pending':
                $badgeClass = 'bg-secondary';
                $statusText = 'Return Requested';
                break;
        }

        return [
            'id' => (string) ($key + 1),
            'order_id' => $row->order_id,
            'order_item_id' => $row->order_item_id,
            'user_id' => $row->user_id,
            'username' => $row->username,
            'seller_name' => $row->seller_name,
            'sub_total' => $row->sub_total,
            'product_name' => $row->product_name,
            'product_image' => !empty($row->image) ? getMediaImageUrl($row->image) : '',
            'product_type' => !empty($row->product_type) ? $row->product_type : '',
            'payment_method' => $row->payment_method,
            'variant_name' => $row->variant_name,
            'quantity' => $row->quantity,
            'discounted_price' => isset($row->discounted_price) && !empty($row->discounted_price) ? $row->discounted_price : '',
            'price' => isset($row->price) && !empty($row->price) ? $row->price : '',
            'active_status' => $row->active_status,
            'active_status_label' => '<label class="badge ' . $badgeClass . '">' . $statusText . '</label>',
            'created_at' => $row->order_date,
            'operate' => $operate
        ];
    })->toArray();

    $bulkData = ['total' => $total, 'rows' => $rows];
    return $fromApp == '1' && $isPrint == '1' ? $rows : response()->json($bulkData);
}
function updateOrderItemStatus($order_item_id, $update_data)
{
    $return_status = ['status' => '8'];
    OrderItems::where('id', $order_item_id)->update($update_data);

    ReturnRequest::where('order_item_id', $order_item_id)->update($return_status);
    return $update_data;
}
function curl($url, $method = 'GET', $data = [], $authorization = "")
{
    $ch = curl_init();
    $curl_options = array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded',
        )
    );

    if (!empty($authorization)) {
        $curl_options['CURLOPT_HTTPHEADER'][] = $authorization;
    }

    if (strtolower($method) == 'post') {
        $curl_options[CURLOPT_POST] = 1;
        $curl_options[CURLOPT_POSTFIELDS] = http_build_query($data);
    } else {
        $curl_options[CURLOPT_CUSTOMREQUEST] = 'GET';
    }
    curl_setopt_array($ch, $curl_options);

    $result = array(
        'body' => json_decode(curl_exec($ch), true),
        'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
    );
    return $result;
}
function formatStoreData($store_data, $isPublicDisk, $language_code = '')
{
    for ($i = 0; $i < count($store_data); $i++) {
        $zone_ids = explode(',', $store_data[$i]->deliverable_zones);
        $zones = Zone::whereIn('id', $zone_ids)->get();

        $translated_zones = $zones->map(function ($zone) use ($language_code) {
            return getDynamicTranslation('zones', 'name', $zone->id, $language_code);
        })->toArray();

        $store_data[$i]->zones = implode(',', $translated_zones) ?? '';
        $store_data[$i]->zone_ids = $store_data[$i]->deliverable_zones ?? '';
        $store_data[$i]->city_id = $store_data[$i]->city ?? "";
        $store_data[$i]->city = $store_data[$i]->city ? getDynamicTranslation('cities', 'name', $store_data[$i]->city, $language_code) : '';
        $store_data[$i]->zipcode_id = $store_data[$i]->zipcode ?? "";
        $store_data[$i]->zipcode = $store_data[$i]->zipcode ? Zipcode::where('id', $store_data[$i]->zipcode)->value('zipcode') : "";

        $store_data[$i]->logo = $isPublicDisk ? asset(config('constants.SELLER_IMG_PATH') . $store_data[$i]->logo) : $store_data[$i]->logo;
        $store_data[$i]->permissions = json_decode($store_data[$i]->permissions, true);
        $store_data[$i]->address_proof = $isPublicDisk ? asset(config('constants.SELLER_IMG_PATH') . $store_data[$i]->address_proof) : $store_data[$i]->address_proof;
        $store_data[$i]->other_documents = $isPublicDisk
            ? (is_array($decoded = json_decode((string) $store_data[$i]->other_documents, true)) ? array_map(fn($document) => asset(config('constants.SELLER_IMG_PATH') . '/' . $document), $decoded) : [])
            : (json_decode((string) $store_data[$i]->other_documents, true) ?: []);
        $store_data[$i]->store_thumbnail = $isPublicDisk ? asset(config('constants.SELLER_IMG_PATH') . $store_data[$i]->store_thumbnail) : $store_data[$i]->store_thumbnail;
    }
    return $store_data;
}
function formatSellerData($seller_data, $isPublicDisk)
{
    for ($k = 0; $k < count($seller_data); $k++) {
        $seller_data[$k]->national_identity_card = $isPublicDisk ? asset(config('constants.SELLER_IMG_PATH') . $seller_data[$k]->national_identity_card) : $seller_data[$k]->national_identity_card;
        $seller_data[$k]->authorized_signature = $isPublicDisk ? asset(config('constants.SELLER_IMG_PATH') . $seller_data[$k]->authorized_signature) : $seller_data[$k]->authorized_signature;
    }
    return $seller_data;
}
function formatUserData($user, $fcm_ids_array)
{
    return [
        'user_id' => $user->id ?? '',
        'ip_address' => $user->ip_address ?? '',
        'username' => $user->username ?? '',
        'email' => $user->email ?? '',
        'mobile' => $user->mobile ?? '',
        'image' => getMediaImageUrl($user->image, 'SELLER_IMG_PATH'),
        'balance' => $user->balance ?? '0',
        'activation_selector' => $user->activation_selector ?? '',
        'activation_code' => $user->activation_code ?? '',
        'forgotten_password_selector' => $user->forgotten_password_selector ?? '',
        'forgotten_password_code' => $user->forgotten_password_code ?? '',
        'forgotten_password_time' => $user->forgotten_password_time ?? '',
        'remember_selector' => $user->remember_selector ?? '',
        'remember_code' => $user->remember_code ?? '',
        'created_on' => $user->created_on ?? '',
        'last_login' => $user->last_login ?? '',
        'active' => $user->active ?? '',
        'is_notification_on' => $user->is_notification_on ?? '',
        'company' => $user->company ?? '',
        'address' => $user->address ?? '',
        'bonus' => $user->bonus ?? '',
        'cash_received' => $user->cash_received ?? '0.00',
        'dob' => $user->dob ?? '',
        'country_code' => $user->country_code ?? '',
        'city' => $user->city ?? '',
        'area' => $user->area ?? '',
        'street' => $user->street ?? '',
        'pincode' => $user->pincode ?? '',
        'apikey' => $user->apikey ?? '',
        'referral_code' => $user->referral_code ?? '',
        'friends_code' => $user->friends_code ?? '',
        'fcm_id' => array_values($fcm_ids_array) ?? '',
        'latitude' => $user->latitude ?? '',
        'longitude' => $user->longitude ?? '',
        'created_at' => $user->created_at ?? '',
        'type' => $user->type ?? '',
    ];
}
function validateRequest($request, $rules, $messages = [])
{
    $validator = Validator::make($request->all(), $rules, $messages);

    if ($validator->fails()) {
        return response()->json([
            'error' => true,
            'message' => $validator->errors()->first(),
            'code' => 102,
        ]);
    }

    return null; // No errors
}
if (!function_exists('getProductDisplayComponent')) {
    function getProductDisplayComponent($store_settings)
    {
        $style = $store_settings['products_display_style_for_web'] ?? 'products_display_style_for_web_1';

        return match ($style) {
            'products_display_style_for_web_2' => 'utility.cards.productCardTwo',
            'products_display_style_for_web_3' => 'utility.cards.productCardThree',
            'products_display_style_for_web_4' => 'utility.cards.productCardFour',
            'products_display_style_for_web_5' => 'utility.cards.productCardFive',
            default => 'utility.cards.productCardOne',
        };
    }
}
if (!function_exists('getCategoryDisplayComponent')) {
    function getCategoryDisplayComponent($store_settings)
    {
        $style = $store_settings['categories_display_style_for_web'] ?? 'categories_display_style_for_web_1';

        return match ($style) {
            'categories_display_style_for_web_2' => 'utility.categories.cards.listCardTwo',
            'categories_display_style_for_web_3' => 'utility.categories.cards.listCardThree',
            default => 'utility.categories.cards.listCardOne',
        };
    }
}
if (!function_exists('getWishlistDisplayComponent')) {
    function getWishlistDisplayComponent($store_settings)
    {
        $style = $store_settings['wishlist_display_style_for_web'] ?? 'wishlist_display_style_for_web_1';

        return match ($style) {
            'wishlist_display_style_for_web_2' => 'utility.wishlist.cards.listCardTwo',
            default => 'utility.wishlist.cards.listCardOne',
        };
    }
}
if (!function_exists('getBrandDisplayComponent')) {
    function getBrandDisplayComponent($store_settings)
    {
        $style = $store_settings['brands_display_style_for_web'] ?? 'brands_display_style_for_web_1';

        return match ($style) {
            'brands_display_style_for_web_2' => 'utility.brands.cards.listCardTwo',
            'brands_display_style_for_web_3' => 'utility.brands.cards.listCardThree',
            default => 'utility.brands.cards.listCardTwo',
        };
    }
}
if (!function_exists('getHomeTheme')) {
    function getHomeTheme($store_settings)
    {
        $home_theme = $store_settings['web_home_page_theme'] ?? 'web_home_page_theme_1';

        return match ($home_theme) {
            'web_home_page_theme_2' => 'livewire.' . config('constants.theme') . '.home.homeThemeTwo',
            'web_home_page_theme_3' => 'livewire.' . config('constants.theme') . '.home.homeThemeThree',
            'web_home_page_theme_4' => 'livewire.' . config('constants.theme') . '.home.homeThemeFour',
            'web_home_page_theme_5' => 'livewire.' . config('constants.theme') . '.home.homeThemeFive',
            'web_home_page_theme_6' => 'livewire.' . config('constants.theme') . '.home.homeThemeSix',
            default => 'livewire.' . config('constants.theme') . '.home.home',
        };
    }
}
if (!function_exists('getHeaderStyle')) {
    function getHeaderStyle($store_settings)
    {
        $home_theme = $store_settings['web_home_page_theme'] ?? 'web_home_page_theme_1';

        return match ($home_theme) {
            'web_home_page_theme_2' => 'components.header.headerThemeTwo',
            'web_home_page_theme_3' => 'components.header.headerThemeThree',
            'web_home_page_theme_4' => 'components.header.headerThemeFour',
            'web_home_page_theme_5' => 'components.header.headerThemeFive',
            'web_home_page_theme_6' => 'components.header.headerThemeSix',
            default => 'components.header.header',
        };
    }
}
if (!function_exists('getProductDetailsStyle')) {
    function getProductDetailsStyle($store_settings)
    {
        $home_theme = $store_settings['web_product_details_style'] ?? 'web_product_details_style_1';

        return match ($home_theme) {
            'web_product_details_style_2' => 'livewire.' . config('constants.theme') . '.products.detailsStyleTwo',
            default => 'livewire.' . config('constants.theme') . '.products.details',
        };
    }
}
// if (!function_exists('getDynamicTranslation')) {
//     function getDynamicTranslation($table, $column, $id, $language_key)
//     {
//         // Fetch the record from the database
//         $record = DB::table($table)->where('id', $id)->first();

//         // If no record is found, return null
//         if (!$record) {
//             return null;
//         }

//         // Decode the JSON column
//         $translations = json_decode($record->$column, true);

//         // Return translated value if available, otherwise fallback to 'name' column
//         return $translations[$language_key] ?? $record->name ?? null;
//     }
// }

if (!function_exists('getDynamicTranslation')) {
    function getDynamicTranslation($table, $column, $id, $language_key)
    {
        // dd($language_key);
        // Fetch the record from the database
        $record = DB::table($table)->where('id', $id)->first();
        // dd($id);
        // dd($record);
        // If no record is found, return null
        if (!$record) {
            return null;
        }

        // Decode the JSON column
        $translations = json_decode($record->$column, true);
        // dd($translations);
        // Check if the language key exists
        if (isset($translations[$language_key])) {
            // If the translation for the given language key exists, return it
            return $translations[$language_key];
        }

        // dd($translations['en']);
        // If the translation for the given language key does not exist, fallback to 'en'
        if (isset($translations['en'])) {
            return $translations['en'];  // Fallback to English if available
        }
        // If there's no translation at all, return the default 'name' column or null
        return $record->name ?? null;
    }
}
if (!function_exists('generateLanguageTabs')) {
    function generateLanguageTabs($languages, $nameKey = '', $nameValue = '', $inputName = '')
    {
        $html = '';

        foreach ($languages as $lang) {
            if ($lang->code !== 'en') {
                $html .= '
                <div class="tab-pane fade" id="content-' . $lang->code . '" role="tabpanel" aria-labelledby="tab-' . $lang->code . '">
                    <div class="mb-3">
                        <label for="translated_name_' . $lang->code . '" class="form-label">
                            ' . labels($nameKey, $nameValue) . ' (' . $lang->language . ')
                        </label>
                        <input type="text" class="form-control"
                               id="translated_name_' . $lang->code . '"
                               name="' . $inputName . '[' . $lang->code . ']"
                               value="">
                    </div>
                </div>';
            }
        }

        return $html;
    }
}
if (!function_exists('generateUpdateableLanguageTabs')) {
    function generateUpdateableLanguageTabs($languages, $data, $nameKey = '', $nameValue = '', $inputName = '')
    {
        $html = '';

        foreach ($languages as $lang) {
            if ($lang->code !== 'en') {
                $translatedValue = '';
                // dd($lang->code);
                if (isset($data) && property_exists(json_decode($data), $lang->code)) {
                    $translatedValue = json_decode($data)->{$lang->code};
                }
                $html .= '
                <div class="tab-pane fade" id="content-' . $lang->code . '" role="tabpanel" aria-labelledby="tab-' . $lang->code . '">
                    <div class="mb-3">
                        <label for="translated_name_' . $lang->code . '" class="form-label">
                            ' . labels($nameKey, $nameValue) . ' (' . $lang->language . ')
                        </label>
                        <input type="text" class="form-control"
                               id="translated_name_' . $lang->code . '"
                               name="' . $inputName . '[' . $lang->code . ']"
                               value="' . $translatedValue . '">
                    </div>
                </div>';
            }
        }

        return $html;
    }
}
if (!function_exists('generateLanguageTabsNav')) {
    function generateLanguageTabsNav($languages)
    {
        $html = '';

        foreach ($languages as $lang) {
            if ($lang->code !== 'en') {
                $html .= '
                <li class="nav-item" role="presentation">
                    <button class="language-nav-link nav-link" id="tab-' . $lang->code . '"
                            data-bs-toggle="tab" data-bs-target="#content-' . $lang->code . '"
                            type="button" role="tab"
                            aria-controls="content-' . $lang->code . '" aria-selected="false">
                        ' . $lang->language . '
                    </button>
                </li>';
            }
        }

        return $html;
    }
}
function get_language_code()
{
    // dd(session()->all());
    return session()->get('locale', 'en');
}
function validatePanelRequest($request, array $rules, array $messages = [], ?Closure $after = null)
{
    $validator = Validator::make($request->all(), $rules, $messages);
    if ($after) {
        $validator->after($after);
    }
    if ($validator->fails()) {
        return $request->ajax()
            ? response()->json(['errors' => $validator->errors()->all()], 422)
            : redirect()->back()->withErrors($validator)->withInput();
    }

    return null;
}
