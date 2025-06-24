<?php

namespace App\Livewire\Products;

use Livewire\Component;
use Illuminate\Http\Request;
use Livewire\Attributes\Title;
use App\Models\Attribute_values;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class Listing extends Component
{
    #[Title('Product Listing |')]
    public $user_id;

    public $slug;
    public $routeType = "";
    public $section;
    public $category;
    public function __construct()
    {
        $this->user_id = Auth::user() != '' ? Auth::user()->id : NUll;
    }

    function mount(Request $request, $slug = null, $section = null)
    {
        $this->slug = $slug;
        $url = $request->url();
        $store_id = session('store_id');
        if (str_contains($url, '/section/')) {
            $this->routeType = 'section';
            if ($this->slug != "") {
                $this->section = fetchDetails('sections', ['id' => $this->slug, 'store_id' => $store_id]);
                if ($this->section == []) {
                    $this->redirect('products', true);
                }
            }
        } elseif (str_contains($url, '/categories/')) {
            $this->routeType = 'category';
            if ($this->slug != "") {
                $this->category = fetchDetails('categories', ['slug' => $this->slug, 'store_id' => $store_id]);
                if ($this->category == []) {
                    $this->redirect('products', true);
                }
            }
        }
    }

    public function render(Request $request)
    {
        $system_settings = getSettings('system_settings', true, true);
        $system_settings = json_decode($system_settings, true);
        $right_breadcrumb = [];
        $language_code = get_language_code();
        $filter = [];
        $store_id = session('store_id');
        // category filter
        $category_id = null;
        $sub_categories = [];
        if ($this->category != []) {
            $category = $this->category;
            $breadcrumb = '<a href="' . customUrl('categories') . '"> Categories </a> <ion-icon class="align-text-top icon"
                name="chevron-forward-outline"></ion-icon>' . getDynamicTranslation('categories', 'name', $category[0]->id, $language_code);
            array_push($right_breadcrumb, $breadcrumb);
            $sub_categories = fetchDetails('categories', ['parent_id' => $category[0]->id]);
            $category_id = $category[0]->id;
        }
        // category filter

        // section filter
        $section = $this->section;
        if ($section != []) {
            if ($section[0]->product_type == 'custom_products') {
                $product_ids = explode(",", $section[0]->product_ids);
                $product_variant_ids = [];
                $product_variants = fetchProduct(null, null, $product_ids);
                if (count($product_variants) >= 1) {
                    foreach ($product_variants['product'] as $product) {
                        array_push($product_variant_ids, $product->variants[0]->id);
                    }
                }
                $filter['product_variant_ids'] = $product_variant_ids;
            } else {
                $category_id = explode(",", $section[0]->categories);
                $filter['product_type'] = $section[0]->product_type;
            }
            $breadcrumb = 'Section <ion-icon class="align-text-top icon"
                    name="chevron-forward-outline"></ion-icon>' . getDynamicTranslation('sections', 'short_description', $section[0]->id, $language_code);
            array_push($right_breadcrumb, $breadcrumb);
        }
        // section filter

        $sortBy = $request->query('sort');
        $bySearch = $request->query('search');

        // by search filter
        if ($bySearch != null) {
            $filter['search'] = $bySearch;
            $breadcrumb = 'Search <ion-icon class="align-text-top icon"
            name="chevron-forward-outline"></ion-icon>' . $bySearch;
            array_push($right_breadcrumb, $breadcrumb);
        }
        // by search filter

        $sort = "";
        $order = "";
        $attribute_values = '';
        $attribute_names = '';
        foreach ($request->query() as $key => $value) {
            if (strpos($key, 'filter-') !== false) {
                if (!empty($attribute_values)) {
                    $attribute_values .= "|" . $request->query($key, true);
                } else {
                    $attribute_values = $request->query($key, true);
                }

                $key = str_replace('filter-', '', $key);
                if (!empty($attribute_names)) {
                    $attribute_names .= "|" . $key;
                } else {
                    $attribute_names = $key;
                }
            }
        }
        $attribute_values = explode('|', $attribute_values ?? '');
        $attribute_names = explode('|', $attribute_names ?? '');
        $filter['attribute_value_ids'] = getAttributeIdsByValue($attribute_values, $attribute_names);
        $filter_attribute_value_ids = $filter['attribute_value_ids'];

        // brand filter
        if (isset($request->query()['brand']) && !empty($request->query()['brand'])) {
            $brand_slug = $request->query()['brand'];
            $brand = fetchDetails('brands', ['slug' => $brand_slug, 'status' => '1']);
            $filter['brand'] = $brand[0]->id;
            $breadcrumb = '<a href="' . customUrl('brands') . '"> Brands </a> <ion-icon class="align-text-top icon"
            name="chevron-forward-outline"></ion-icon>' . getDynamicTranslation('brands', 'name', $brand[0]->id, $language_code);
            array_push($right_breadcrumb, $breadcrumb);
        }
        // brand filter

        // tags filter
        if (isset($request->query()['tag']) && !empty($request->query()['tag'])) {
            $tags = $request->query()['tag'];
            $filter['tags'] = $tags;
            $breadcrumb = 'Tags<ion-icon class="align-text-top icon"
            name="chevron-forward-outline"></ion-icon>' . $tags;
            array_push($right_breadcrumb, $breadcrumb);
        }
        // tags filter

        // product sort by
        if ($sortBy == "top-rated") {
            $filter['product_type'] = "top_rated_product_including_all_products";
        } elseif ($sortBy == "latest-products") {
            $filter['product_type'] = "new_added_products";
            $sort = 'p.id';
            $order = 'desc';
        } elseif ($sortBy == "oldest-first") {
            $filter['product_type'] = "old_products_first";
            $sort = 'p.id';
            $order = 'asc';
        } elseif ($sortBy == "price-asc") {
            $sort = 'pv.price';
            $order = 'asc';
        } elseif ($sortBy == "price-desc") {
            $sort = 'pv.price';
            $order = 'desc';
        }
        $sorted_by = "";
        if (isset($request->query()['sort']) && !empty($request->query()['sort'])) {
            $sorted_by = $request->query()['sort'];
        }
        // product sort by

        // min max price filter
        if (isset($request->query()['min_price']) && ($request->query()['min_price'] != null) && isset($request->query()['max_price']) && ($request->query()['max_price'] != null)) {
            $filter['min_price'] = $request->query()['min_price'];
            $filter['max_price'] = $request->query()['max_price'];
        }
        // min max price filter
        $brands = fetchDetails('brands', ['store_id' => $store_id, 'status' => '1']);
        foreach ($brands as $brand) {
            $brand->is_checked = false;
            if (isset($filter['brand']) && !empty($filter['brand'])) {
                $is_checked = ($brand->id == $filter['brand']) ? true : false;
                $brand->is_checked = $is_checked;
            }
        }
        $product_list = fetchProduct($this->user_id, $filter, null, $category_id, null, null, $sort, $order, null, null, null, null, $store_id);

        if (isset($request->query()['min_price']) && ($request->query()['min_price'] != null) && isset($request->query()['max_price']) && ($request->query()['max_price'] != null)) {
            $selected_min_price = $request->query()['min_price'];
            $selected_max_price = $request->query()['max_price'];
        }
        $min_max_price = [
            'min_price' => $product_list['min_price'],
            'max_price' => $product_list['max_price'],
            'selected_min_price' => $selected_min_price ?? $product_list['min_price'],
            'selected_max_price' => $selected_max_price ?? $product_list['max_price']
        ];

        // product filter by attributes
        $product_attributes = DB::table('products as p')
            ->leftJoin('product_attributes as pa', 'pa.product_id', '=', 'p.id')
            ->where('p.store_id', $store_id)
            ->select('p.id', 'pa.attribute_value_ids')
            ->get()->toArray();
        $attr_val_ids = [];
        foreach ($product_attributes as $product_attr) {
            $att_value_ids = $product_attr->attribute_value_ids;
            array_push($attr_val_ids, $att_value_ids);
        }
        $attr_val_ids = array_filter(array_merge(...array_map('str_getcsv', $attr_val_ids)));
        $attr_val_ids = array_unique($attr_val_ids);

        $attributeData = Attribute_values::whereIn('attribute_values.id', $attr_val_ids)
            ->join('attributes as a', 'attribute_values.attribute_id', '=', 'a.id')
            ->select('attribute_values.id as attribute_value_id', 'a.name as attribute_name', 'attribute_values.value as attribute_values', 'attribute_values.swatche_type', 'attribute_values.swatche_value')
            ->where('a.status', 1)
            ->get()->toArray();
        $groupedAttributes = [];
        foreach ($attributeData as $item) {
            $attributeName = $item['attribute_name'];
            if (!isset($groupedAttributes[$attributeName])) {
                $groupedAttributes[$attributeName] = [
                    'attribute_name' => $attributeName,
                    'attribute_values' => [],
                    'swatche_type' => [],
                    'swatche_value' => []
                ];
            }
            $groupedAttributes[$attributeName]['attribute_values'][] = $item['attribute_values'];
            $groupedAttributes[$attributeName]['swatche_type'][] = $item['swatche_type'];
            $groupedAttributes[$attributeName]['swatche_value'][] = $item['swatche_value'];
            $is_checked = in_array($item['attribute_value_id'], $filter_attribute_value_ids);
            $groupedAttributes[$attributeName]['is_checked'][] = $is_checked;
        }
        // product filter by attributes

        $bread_crumb['page_main_bread_crumb'] = '<a href="' . customUrl('products') . '">' . labels('front_messages.products', 'Products') . '</a>';

        if (count($right_breadcrumb) >= 1) {
            $bread_crumb['right_breadcrumb'] = $right_breadcrumb;
        }

        // per page
        $perPage = 20;
        if (isset($request->query()['perPage']) && ($request->query()['perPage'] != null)) {
            $perPage = $request->query()['perPage'];
            $perPage = (int)($perPage);
            if ($perPage == 0) {
                $perPage = 20;
            }
        }
        if (!in_array($perPage, [12, 16, 20, 24])) {
            $perPage = 20;
        }
        // per page

        $products = collect($product_list['product']);
        $page = request()->get('page', 1);
        if (isset($page)) {
            $paginator = new LengthAwarePaginator(
                $products->forPage((int)$page, (int)$perPage),
                $product_list['total'],
                (int)$perPage,
                (int)$page,
                ['path' => url()->current()]
            );
        }
        $product_list['product'] = $paginator->items();
        $product_list['links'] = $paginator->links();
        $language_code = get_language_code();
        $view_mode = $request->query('mode') ?? "";
        return view(
            'livewire.' . config('constants.theme') . '.products.listing',
            [
                'products_listing' => $product_list['product'],
                'total_products' => $product_list['total'],
                'min_max_price' => $min_max_price,
                'links' => $product_list['links'],
                'Attributes' => $groupedAttributes,
                'filters' => $filter,
                'bySearch' => $bySearch,
                'sub_categories' => $sub_categories,
                'sorted_by' => $sorted_by,
                'brands' => $brands,
                'bread_crumb' => $bread_crumb,
                'view_mode' => $view_mode,
                'perPage' => $perPage,
                'products_type' => "regular",
                'language_code' => $language_code
            ]
        );
    }
}
