<?php

namespace App\Http\Controllers\admin;

use App\Models\Category;
use App\Models\ComboProduct;
use App\Models\Language;
use App\Models\Product;
use App\Models\Section;
use App\Models\Slider;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

class FeaturedSectionsController extends Controller
{

    public function index()
    {
        $store_id = getStoreId();
        $languages = Language::all();
        $categories = Category::where('status', 1)->where('store_id', $store_id)->orderBy('id', 'desc')->get();
        return view('admin.pages.tables.featured_section', ['categories' => $categories, 'languages' => $languages]);
    }
    public function store(Request $request)
    {

        $store_id = getStoreId();
        // dd($request->product_type);
        $rules = [
            'title' => 'required',
            'short_description' => 'required',
            'style' => 'required',
            'product_type' => 'required',
            'banner_image' => 'required',
            'background_color' => ['required', 'regex:/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'header_style' => 'required',
        ];

        // Conditionally add 'categories' rule
        if (!in_array($request->product_type, ['custom_products', 'custom_combo_products', 'digital_product'])) {
            $rules['categories'] = 'required|array|min:1';
        }

        // If custom products, override rules
        if ($request->product_type === 'custom_products') {
            $rules = [
                'product_ids' => 'required|array|min:1',
                'banner_image' => 'required',
                'background_color' => ['required', 'regex:/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
                'header_style' => 'required',
            ];
        }

        $messages = [
            'background_color.regex' => 'The background color must be a valid hexadecimal code (e.g., #FFF or #FFFFFF).',
            'product_ids.required' => labels('admin_labels.select_at_least_one_product', 'Please select at least one product.'),
        ];

        if ($response = validatePanelRequest($request, $rules, $messages)) {
            return $response;
        } else {
            $existing_section = Section::where('store_id', getStoreId())
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.en')) = ?", $request->title)
                ->first();

            if ($existing_section) {
                return response()->json([
                    'error' => true,
                    'message' => 'Section already exists.',
                    'language_message_key' => 'section_exists',
                ], 400);
            }
            if (isset($request->product_ids) && !empty($request->product_ids) && $request->product_type == 'custom_products') {
                $product_ids = implode(',', $request->product_ids);
            } elseif (isset($request->digital_product_ids) && !empty($request->digital_product_ids) && $request->product_type == 'digital_product') {
                $product_ids = implode(',', $request->digital_product_ids);
            } elseif (isset($request->product_ids) && !empty($request->product_ids) && $request->product_type == 'custom_combo_products') {
                $product_ids = implode(',', $request->product_ids);
            } else {
                $product_ids = null;
            }
            $translations = [
                'en' => $request->title
            ];
            if (!empty($request['translated_featured_section_title'])) {
                $translations = array_merge($translations, $request['translated_featured_section_title']);
            }
            $translation_descriptions = [
                'en' => $request->short_description
            ];
            if (!empty($request['translated_featured_section_description'])) {
                $translation_descriptions = array_merge($translations, $request['translated_featured_section_description']);
            }

            $section_data['title'] = json_encode($translations, JSON_UNESCAPED_UNICODE);
            $section_data['banner_image'] = $request->banner_image;
            $section_data['background_color'] = $request->background_color;
            $section_data['short_description'] = json_encode($translation_descriptions, JSON_UNESCAPED_UNICODE);
            $section_data['product_type'] = $request->product_type;
            $section_data['categories'] = (isset($request->categories) && !empty($request->categories)) ? implode(',', $request->categories) : null;
            $section_data['product_ids'] = $product_ids;
            $section_data['style'] = $request->style;
            $section_data['header_style'] = $request->header_style;
            $section_data['store_id'] = $store_id;

            unset($section_data['translated_featured_section_title'], $section_data['translated_featured_section_description']);

            Section::create($section_data);

            if ($request->ajax()) {
                return response()->json([
                    'message' => labels('admin_labels.feature_section_created_successfully', 'Feature Section created successfully')
                ]);
            }
        }
    }

    public function list()
    {
        $store_id = getStoreId();

        $search = trim(request('search'));
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = (request('limit')) ? request('limit') : "10";
        $section = Section::where('store_id', $store_id)
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                        ->orWhere('short_description', 'LIKE', '%' . $search . '%');
                });
            });
        $product_type_labels = [
            'new_added_products' => 'New Added Products',
            'products_on_sale' => 'Products on Sale',
            'top_rated_products' => 'Top Rated Products',
            'most_selling_products' => 'Most Selling Products',
            'custom_products' => 'Custom Products',
            'digital_product' => 'Digital Product',
            'custom_combo_products' => 'Custom Combo Products'
        ];
        $styles = [
            'style_1' => 'Style 1',
            'style_2' => 'Style 2',
            'style_3' => 'Style 3',

        ];
        $section->where('store_id', $store_id);
        $total = $section->count();
        $language_code = get_language_code();
        $section = $section->orderBy($sort, $order)
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(function ($s) use ($product_type_labels, $styles, $language_code) {
                $edit_url = route('feature_section.edit', $s->id);
                $delete_url = route('feature_section.destroy', $s->id);
                $action = '<div class="dropdown bootstrap-table-dropdown">
                    <a href="#" class="text-dark" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="bx bx-dots-horizontal-rounded"></i>
                    </a>
                    <div class="dropdown-menu table_dropdown offer_action_dropdown" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item dropdown_menu_items" href="' . $edit_url . '"><i class="bx bx-pencil mx-2"></i> Edit</a>
                        <a class="dropdown-item delete-data dropdown_menu_items" data-url="' . $delete_url . '"><i class="bx bx-trash mx-2"></i> Delete</a>
                    </div>
                </div>';
                $banner_image = route('admin.dynamic_image', [
                    'url' => getMediaImageUrl($s->banner_image),
                    'width' => 60,
                    'quality' => 90
                ]);
                return [
                    'id' => $s->id,
                    'title' => getDynamicTranslation('sections', 'title', $s->id, $language_code),
                    'short_description' => getDynamicTranslation('sections', 'short_description', $s->id, $language_code),
                    'style' => isset($styles[$s->style]) ? $styles[$s->style] : $s->style,
                    'banner_image' => '<div class="d-flex justify-content-around"><a href="' . getMediaImageUrl($s->banner_image)  . '" data-lightbox="banner-' . $s->id . '"><img src="' . $banner_image . '" alt="Avatar" class="rounded"/></a></div>',
                    'categories' => $s->categories,
                    'product_ids' => $s->product_ids,
                    'product_type' => isset($product_type_labels[$s->product_type]) ? $product_type_labels[$s->product_type] : $s->product_type,
                    'date' => Carbon::parse($s->created_at)->format('d-m-Y'),
                    'operate' => $action,
                ];
            });

        return response()->json([
            "rows" => $section,
            "total" => $total,
        ]);
    }
    public function edit($data)
    {
        $store_id = getStoreId();
        $languages = Language::all();
        $data = Section::where('store_id', $store_id)
            ->find($data);
        $language_code = get_language_code();
        if ($data === null || empty($data)) {
            return view('admin.pages.views.no_data_found');
        } else {
            $categories = Category::where('status', 1)->where('store_id', $store_id)->orderBy('id', 'desc')->get();
            $product_details = Product::whereIn('id', explode(',', $data->product_ids))->where('store_id', $store_id)
                ->orderBy('id', 'desc')
                ->get()
                ->toArray();

            $combo_product_details = ComboProduct::whereIn('id', explode(',', $data->product_ids))->where('store_id', $store_id)
                ->orderBy('id', 'desc')
                ->get()
                ->toArray();

            return view('admin.pages.forms.update_featured_section', compact('data', 'categories', 'product_details', 'combo_product_details', 'languages', 'language_code'));
        }
    }

    public function update(Request $request, $data)
    {

        $rules = [
            'title' => 'required',
            'short_description' => 'required',
            'style' => 'required',
            'product_type' => 'required',
            'banner_image' => 'required',
            'background_color' => ['required', 'regex:/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'header_style' => 'required',
        ];

        $messages = [
            'background_color.regex' => 'The background color must be a valid hexadecimal code (e.g., #FFF or #FFFFFF).',
        ];

        if ($response = validatePanelRequest($request, $rules, $messages)) {
            return $response;
        } else {

            if (isset($request->product_ids) && !empty($request->product_ids) && $request->product_type == 'custom_products') {
                $product_ids = implode(',', $request->product_ids);
            } elseif (isset($request->digital_product_ids) && !empty($request->digital_product_ids) && $request->product_type == 'digital_product') {
                $product_ids = implode(',', $request->digital_product_ids);
            } elseif (isset($request->combo_product_ids) && !empty($request->combo_product_ids) && $request->product_type == 'custom_combo_products') {
                $product_ids = implode(',', $request->combo_product_ids);
            } else {
                $product_ids = null;
            }
            $section = Section::find($data);
            // dd($section);
            $existing_section = Section::where('store_id', getStoreId())
                ->where('id', '!=', $section->id)
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.en')) = ?", [$request->title])
                ->first();

            if ($existing_section) {
                return response()->json([
                    'error' => true,
                    'message' => 'Section already exists.',
                    'language_message_key' => 'section_exists',
                ], 400);
            }

            $existingTranslations = json_decode($section->title, true) ?? [];
            $existingDescriptionTranslations = json_decode($section->short_description, true) ?? [];

            $existingTranslations['en'] = $request->title;
            $existingDescriptionTranslations['en'] = $request->short_description;

            if (!empty($request->translated_featured_section_title)) {
                $existingTranslations = array_merge($existingTranslations, $request->translated_featured_section_title);
            }
            if (!empty($request->translated_featured_section_description)) {
                $existingDescriptionTranslations = array_merge($existingDescriptionTranslations, $request->translated_featured_section_description);
            }
            // Encode updated translations to store as JSON
            $section_data['title'] = json_encode($existingTranslations, JSON_UNESCAPED_UNICODE);
            $section_data['short_description'] = json_encode($existingDescriptionTranslations, JSON_UNESCAPED_UNICODE);
            $section_data['product_type'] = $request->product_type;
            $section_data['categories'] = (isset($request->categories) && !empty($request->categories)) ? implode(',', $request->categories) : null;
            $section_data['product_ids'] = $product_ids;
            $section_data['style'] = $request->style;
            $section_data['banner_image'] = $request->banner_image;
            $section_data['background_color'] = $request->background_color;
            $section_data['header_style'] = $request->header_style;

            Section::where('id', $data)->update($section_data);

            if ($request->ajax()) {
                return response()->json([
                    'message' => labels('admin_labels.feature_section_updated_successfully', 'Feature Section updated successfully'),
                    'location' => route('feature_section.index')
                ]);
            }
        }
    }

    public function destroy($id)
    {
        $section = Section::find($id);

        if ($section->delete()) {
            return response()->json([
                'error' => false,
                'message' => labels('admin_labels.featured_section_deleted_successfully', 'Featured section deleted successfully!')
            ]);
        } else {
            return response()->json(['error' => labels('admin_labels.something_went_wrong', 'Something went wrong')]);
        }
    }

    public function sectionOrder()
    {
        $store_id = getStoreId();
        $sections = Section::where('store_id', $store_id)->orderBy('row_order', 'asc')->get();
        $language_code = get_language_code();
        return view('admin.pages.tables.section_order', ['sections' => $sections, 'language_code', $language_code]);
    }

    public function updateSectionOrder(Request $request)
    {

        $section_ids = $request->input('section_id');
        $i = 0;

        foreach ($section_ids as $section_id) {
            $data = [
                'row_order' => $i
            ];

            Section::where('id', $section_id)->update($data);

            $i++;
        }
        return response()->json(['error' => false, 'message' => labels('admin_labels.section_order_saved', 'Section Order Saved!')]);
    }
    public function delete_selected_data(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:sections,id'
        ]);

        foreach ($request->ids as $id) {
            $sections = Section::find($id);

            if ($sections) {
                Section::where('id', $id)->delete();
            }
        }
        Section::destroy($request->ids);

        return response()->json(['message' => 'Selected data deleted successfully.']);
    }
}
