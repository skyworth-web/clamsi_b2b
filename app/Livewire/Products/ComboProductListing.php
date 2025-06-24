<?php

namespace App\Livewire\Products;

use Livewire\Component;
use Illuminate\Http\Request;
use Livewire\Attributes\Title;
use App\Models\ComboProductAttributeValue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class ComboProductListing extends Component
{
    #[Title('Combo Products |')]
    public $user_id;

    public $slug;
    public $routeType = "";
    public $section;
    public function __construct()
    {
        $this->user_id = Auth::user() != '' ? Auth::user()->id : NUll;
    }

    function mount(Request $request, $slug = null, $section = null)
    {
        $this->slug = $slug;
        $this->section = $section;
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
        }
    }

    public function render(Request $request)
    {
        $system_settings = getSettings('system_settings', true, true);
        $system_settings = json_decode($system_settings, true);
        $right_breadcrumb = [];
        $filter = [];
        $language_code = get_language_code();
        // category filter
        $sub_categories = [];

        // section filter
        $section = $this->section;
        if ($section != []) {
            if ($section[0]->product_type == 'custom_combo_products') {
                $product_ids = explode(",", $section[0]->product_ids);
                $product_variant_ids = [];
                $product_variants = fetchComboProduct(null, null, $product_ids);
                if (count($product_variants) >= 1) {
                    foreach ($product_variants['combo_product'] as $product) {
                        array_push($product_variant_ids, $product->id);
                    }
                }
                $filter['product_variant_ids'] = $product_variant_ids;
            } else {
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
        $filter['attribute_value_ids'] = getComboProductAttributeIdsByValue($attribute_values, $attribute_names);
        $filter_attribute_value_ids = $filter['attribute_value_ids'];

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
        } elseif ($sortBy == "oldest-first") {
            $filter['product_type'] = "old_products_first";
        } elseif ($sortBy == "price-asc") {
            $sort = 'p.price';
            $order = 'asc';
        } elseif ($sortBy == "price-desc") {
            $sort = 'p.price';
            $order = 'desc';
        }
        $store_id = session('store_id');
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

        $product_list = fetchComboProduct(user_id: $this->user_id, filter: $filter, sort: $sort, order: $order, store_id: $store_id);
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
        $attr_val_ids = [];
        foreach ($product_list['combo_product'] as $product) {
            $att_value_ids = $product->attr_value_ids;
            array_push($attr_val_ids, $att_value_ids);
        }
        $attr_val_ids = array_filter(array_merge(...array_map('str_getcsv', $attr_val_ids)));
        $attr_val_ids = array_unique($attr_val_ids);

        $attributeData = ComboProductAttributeValue::whereIn('combo_product_attribute_values.id', $attr_val_ids)
            ->join('combo_product_attributes as a', 'combo_product_attribute_values.combo_product_attribute_id', '=', 'a.id')
            ->select('combo_product_attribute_values.id as attribute_value_id', 'a.name as attribute_name', 'combo_product_attribute_values.value as attribute_values')
            ->get()->toArray();
        $groupedAttributes = [];
        foreach ($attributeData as $item) {
            $attributeName = $item['attribute_name'];
            if (!isset($groupedAttributes[$attributeName])) {
                $groupedAttributes[$attributeName] = [
                    'attribute_name' => $attributeName,
                    'attribute_values' => [],
                ];
            }
            $groupedAttributes[$attributeName]['attribute_values'][] = $item['attribute_values'];
            $is_checked = in_array($item['attribute_value_id'], $filter_attribute_value_ids);
            $groupedAttributes[$attributeName]['is_checked'][] = $is_checked;
        }
        // product filter by attributes

        $bread_crumb['page_main_bread_crumb'] = '<a href="' . customUrl('combo-products') . '">' .labels('front_messages.combo_products', 'Combo Products') .'</a>';

        if (count($right_breadcrumb) >= 1) {
            $bread_crumb['right_breadcrumb'] = $right_breadcrumb;
        }

        // per page
        $perPage = 20;
        if (isset($request->query()['perPage']) && ($request->query()['perPage'] != null)) {
            $perPage = $request->query()['perPage'];
            if (!is_int($perPage)) {
                $perPage = 20;
            }
        }
        // per page

        $products = collect($product_list['combo_product']);
        $page = request()->get('page', 1);
        if (isset($page)) {
            $paginator = new LengthAwarePaginator(
                $products->forPage((int)$page, (int)$perPage),
                $product_list['total'] ?? 0,
                (int)$perPage,
                (int)$page,
                ['path' => url()->current()]
            );
        }
        $product_list['combo_product'] = $paginator->items();
        $product_list['links'] = $paginator->links();

        $view_mode = $request->query('mode') ?? "";

        return view(
            'livewire.' . config('constants.theme') . '.products.listing',
            [
                'products_listing' => $product_list['combo_product'],
                'total_products' => $product_list['total'] ?? 0,
                'min_max_price' => $min_max_price,
                'links' => $product_list['links'],
                'Attributes' => $groupedAttributes,
                'filters' => $filter,
                'bySearch' => $bySearch,
                'sub_categories' => $sub_categories,
                'sorted_by' => $sorted_by,
                'bread_crumb' => $bread_crumb,
                'view_mode' => $view_mode,
                'perPage' => $perPage,
                'products_type' => "combo",

            ]
        );
    }
}
