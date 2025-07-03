<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Category;
use App\Models\CategorySliders;
use App\Models\Language;
use App\Models\Location;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;


class CategoryController extends Controller
{

    public function index()
    {
        $store_id = getStoreId();
        $languages = Language::all();
        $language_code = get_language_code();
        $categories = Category::where('status', 1)->where('store_id', $store_id)->orderBy('id', 'desc')->get();

        return view('admin.pages.forms.categories', ['categories' => $categories, 'languages' => $languages, 'language_code' => $language_code]);
    }

    public function store(Request $request)
    {
        $store_id = getStoreId();

        // Validate request data
        $rules = [
            'name' => 'required|string',
            'category_image' => 'required',
            'banner' => 'required',
            'translated_category_name' => 'nullable|array',
            'translated_category_name.*' => 'nullable|string',
        ];

        $validationResponse = validatePanelRequest($request, $rules);

        if ($validationResponse !== null) {
            return $validationResponse;
        }

        $category_data = $request->only(array_keys($rules));

        $existingCategory = Category::where('store_id', $store_id)
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.en')) = ?", [$category_data['name']])
            ->first();

        if ($existingCategory) {
            return response()->json([
                'error' => true,
                'message' => 'Category name already exists.',
                'language_message_key' => 'category_name_exists',
            ], 400);
        }

        // Handle translations
        $translations = ['en' => $category_data['name']];
        if (!empty($category_data['translated_category_name'])) {
            $translations = array_merge($translations, $category_data['translated_category_name']);
        }

        // Build data for storage
        $categoryData = [
            'name' => json_encode($translations, JSON_UNESCAPED_UNICODE),
            'slug' => generateSlug($translations['en'], 'categories'),
            'image' => $category_data['category_image'],
            'banner' => $request->banner,
            'parent_id' => $request->parent_id ?? 0,
            'style' => $request->category_style ?? '',
            'status' => 1,
            'store_id' => $store_id,
        ];

        Category::create($categoryData);

        $successMessage = labels('admin_labels.category_created_successfully', 'Category created successfully');
        return $request->ajax()
            ? response()->json(['message' => $successMessage])
            : redirect()->back()->with('success', $successMessage);
    }



    public function edit($id)
    {
        $store_id = getStoreId();
        $languages = Language::all();
        $language_code = get_language_code();
        $categories = Category::where('status', 1)
            ->where('store_id', $store_id)
            ->where('id', '!=', $id)
            ->get();

        $data = Category::where('store_id', $store_id)
            ->find($id);

        if ($data === null || empty($data)) {
            return view('admin.pages.views.no_data_found');
        } else {
            return view('admin.pages.forms.update_category', [
                'data' => $data,
                'categories' => $categories,
                'languages' => $languages,
                'language_code' => $language_code
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'name' => 'required|string',
            'category_image' => 'required',
        ];

        $validationResponse = validatePanelRequest($request, $rules);

        if ($validationResponse !== null) {
            return $validationResponse;
        }

        $category = Category::findOrFail($id);
        $category_data = $request->only(array_keys($rules));

        $newName = $category_data['name'];
        $currentTranslations = json_decode($category->name, true);
        $currentName = $currentTranslations['en'] ?? $category->name;
        $currentSlug = $category->slug;

        $storeId = getStoreId();
        $duplicate = Category::where('store_id', $storeId)
            ->where('id', '!=', $category->id)
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.en')) = ?", [$newName])
            ->exists();

        if ($duplicate) {
            return response()->json([
                'error' => true,
                'message' => 'Category name already exists.',
                'language_message_key' => 'category_name_exists',
            ], 400);
        }

        $translations = ['en' => $newName];
        if ($request->filled('translated_category_name')) {
            $translations = array_merge($translations, $request->translated_category_name);
        }

        // Update data
        $updateData = [
            'name' => json_encode($translations, JSON_UNESCAPED_UNICODE),
            'image' => $category_data['category_image'],
            'banner' => $request->banner,
            'parent_id' => $request->input('parent_id', 0),
            'slug' => ($currentName !== $newName)
                ? generateSlug($newName, 'categories', 'slug', $currentSlug, $currentName)
                : $currentSlug,
            'style' => $request->input('category_style', ''),
            'status' => 1,
        ];

        $category->update($updateData);

        $message = labels('admin_labels.category_updated_successfully', 'Category updated successfully');
        return $request->ajax()
            ? response()->json(['message' => $message, 'location' => route('categories.index')])
            : redirect()->back()->with('success', $message);
    }



    public function update_status($id)
    {
        $category = Category::findOrFail($id);
        $tables = ['products', 'seller_store'];
        $columns = ['category_id', 'category_ids'];
        if (isForeignKeyInUse($tables, $columns, $id)) {
            return response()->json([
                'status_error' => labels('admin_labels.cannot_deactivate_category_associated_with_products_seller', 'You cannot deactivate this category because it is associated with products and seller.')
            ]);
        } else {
            $category->status = $category->status == '1' ? '0' : '1';
            $category->save();
            return response()->json(['success' => labels('admin_labels.status_updated_successfully', 'Status updated successfully.')]);
        }
    }


    public function destroy($id)
    {
        // Find the category by ID
        $category = Category::find($id);

        // Define the tables and columns to check for foreign key constraints
        $tables = ['products', 'seller_store'];
        $columns = ['category_id', 'category_ids'];

        // Check if there are foreign key constraints
        if (isForeignKeyInUse($tables, $columns, $id)) {
            return response()->json([
                'error' => labels('admin_labels.cannot_delete_category_associated_with_products_seller', 'You cannot delete this category because it is associated with products and seller.')
            ]);
        }

        // Check if the category ID exists in the comma-separated category_ids of the category_sliders table
        $isCategoryInSliders = DB::table('category_sliders')
            ->where('category_ids', 'LIKE', '%' . $id . '%')
            ->exists();

        if ($isCategoryInSliders) {
            return response()->json([
                'error' => labels('admin_labels.cannot_delete_category_associated_with_sliders', 'You cannot delete this category because it is associated with category sliders.')
            ]);
        }

        // Attempt to delete the category
        if ($category && $category->delete()) {
            return response()->json([
                'error' => false,
                'message' => labels('admin_labels.category_deleted_successfully', 'Category deleted successfully!')
            ]);
        }

        return response()->json(['error' => labels('admin_labels.something_went_wrong', 'Something went wrong')]);
    }

    public function list(Request $request)
    {
        // Get the store ID
        $store_id = getStoreId();

        // Capture input parameters with defaults
        $search = trim($request->input('search', ''));
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'DESC');
        $offset = request()->input('search') || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = $request->input('limit', 10);
        $status = $request->input('status', '');
        // Build the query for categories
        $category_data = Category::where('store_id', $store_id);

        // Apply search filter if provided
        if (!empty($search)) {
            $category_data->where('name', 'like', '%' . $search . '%');
        }

        // Apply status filter only if status is provided
        if (!is_null($status) && $status !== '') {
            $category_data->where('status', $status);
        }

        // Count total records before applying pagination
        $total = $category_data->count();

        // Retrieve paginated data with sorting
        $categories = $category_data->orderBy($sort, $order)
            ->offset($offset)
            ->limit($limit)
            ->get();

        // Format data for response
        $data = $categories->map(function ($c) {

            // Format 'status' field with HTML select
            $status = '<select class="form-select status_dropdown change_toggle_status '
                . ($c->status == 1 ? 'active_status' : 'inactive_status')
                . '" data-id="' . $c->id
                . '" data-url="admin/categories/update_status/' . $c->id . '" aria-label="">'
                . '<option value="1" ' . ($c->status == 1 ? 'selected' : '') . '>Active</option>'
                . '<option value="0" ' . ($c->status == 0 ? 'selected' : '') . '>Deactive</option>'
                . '</select>';

            // Format 'image' and 'banner' fields with HTML tags
            $image = route('admin.dynamic_image', [
                'url' => getMediaImageUrl($c->image),
                'width' => 60,
                'quality' => 90
            ]);
            $banner = route('admin.dynamic_image', [
                'url' => getMediaImageUrl($c->banner),
                'width' => 60,
                'quality' => 90
            ]);
            $language_code = get_language_code();
            $image = '<div class="d-flex justify-content-around"><a href="' . getMediaImageUrl($c->image) . '" data-lightbox="image-' . $c->id . '"><img src="' . $image . '" alt="Avatar" class="rounded"/></a></div>';
            $banner = '<div><a href="' . getMediaImageUrl($c->banner) . '" data-lightbox="banner-' . $c->id . '"><img src="' . $banner . '" alt="Avatar" class="rounded"/></a></div>';

            // Format 'operate' field with dropdown menu HTML
            $action = '<div class="dropdown bootstrap-table-dropdown">
                <a href="#" class="text-dark" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="bx bx-dots-horizontal-rounded"></i>
                </a>
                <div class="dropdown-menu table_dropdown category_action_dropdown" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item dropdown_menu_items" href="' . route('categories.update', $c->id) . '"><i class="bx bx-pencil mx-2"></i> Edit</a>
                    <a class="dropdown-item delete-data dropdown_menu_items" data-url="' . route('admin.categories.destroy', $c->id) . '"><i class="bx bx-trash mx-2"></i> Delete</a>
                </div>
            </div>';

            return [
                'id' => $c->id,
                'name' => getDynamicTranslation('categories', 'name', $c->id, $language_code),
                'status' => $status,
                'image' => $image,
                'banner' => $banner,
                'operate' => $action,
            ];
        });

        // Return response as JSON
        return response()->json([
            "rows" => $data->toArray(), // Convert collection to array for JSON response
            "total" => $total,           // Return the total count
        ]);
    }



    public function get_seller_categories_filter()
    {
        $store_id = getStoreId();

        $seller_data = DB::table('seller_store')
            ->select('category_ids')
            ->where('store_id', $store_id)
            ->first();

        if (!$seller_data) {
            return [];
        }

        $category_ids = explode(",", $seller_data->category_ids);

        $categories = Category::whereIn('id', $category_ids)
            ->where('status', 1)
            ->where('store_id', $store_id)
            ->get();
        $language_code = get_language_code();
        $categories = $categories->map(function ($category) use ($language_code) {
            return [
                'id' => $category->id,
                'name' => getDynamicTranslation('categories', 'name', $category->id, $language_code),
                'slug' => $category->slug,
                'image' => $category->image,
                'status' => $category->status,
                'store_id' => $category->store_id,
            ];
        });

        return $categories->toArray();
    }

    public function getCategoryDetails(Request $request)
    {
        $store_id = getStoreId();
        $search = trim($request->input('search'));
        $limit = (int) $request->input('limit', 10);

        $category = Category::where('name', 'like', '%' . $search . '%')
            ->where('store_id', $store_id)
            ->where('status', 1)
            ->limit($limit)
            ->get(['id', 'name']);

        $totalCount = Category::where('name', 'like', '%' . $search . '%')
            ->where('store_id', $store_id)
            ->selectRaw('count(id) as total')
            ->first()
            ->total;
        $response = [
            'total' => $totalCount,
            'results' => $category->map(function ($category) {
                $language_code = get_language_code();
                return [
                    'id' => $category->id,
                    'text' => getDynamicTranslation('categories', 'name', $category->id, $language_code),
                ];
            }),
        ];

        return response()->json($response);
    }

    public function getCategories($id = null, $limit = '', $offset = '', $sort = 'row_order', $order = 'ASC', $has_child_or_item = 'true', $slug = '', $ignore_status = '', $seller_id = '', $store_id = '', $language_code = "")
    {
        // dd($has_child_or_item);
        $level = 0;
        $query = DB::table('categories as c1');

        $storeId = getStoreId();

        if (isset($storeId) && !empty($storeId)) {
            $query->where('c1.store_id', $storeId);
        }
        if (isset($store_id) && !empty($store_id)) {
            $query->where('c1.store_id', $store_id);
        }
        if ($ignore_status == 1) {
            $query->where('c1.id', $id);
        } else {
            $query->where('c1.id', $id)->where('c1.status', 1);
        }
        if (!empty($slug)) {
            $query->where('c1.slug', $slug);
        }

        if ($has_child_or_item == 'false') {
            $query->leftJoin('categories as c2', 'c2.parent_id', '=', 'c1.id')
                ->leftJoin('products as p', 'p.category_id', '=', 'c1.id')
                ->where(function ($q) {
                    $q->where('c1.id', 'p.category_id')
                        ->orWhere('c2.parent_id', 'c1.id');
                })
                ->groupBy('c1.id');
        }

        if (!empty($limit) || !empty($offset)) {
            $query->offset($offset)->limit($limit);
        }
        // dd($sort);
        $query->orderBy($sort, $order);
        // dd($query->toSql(), $query->getBindings());
        $categories = $query->get(['c1.*']);

        $countRes = count($categories);
        // dd($categories);
        $i = 0;
        foreach ($categories as $pCat) {
            $categories[$i]->children = $this->subCategories($pCat->id, $level);
            $categories[$i]->text = getDynamicTranslation('categories', 'name', $categories[$i]->id, $language_code);
            $categories[$i]->name = getDynamicTranslation('categories', 'name', $categories[$i]->id, $language_code);
            $categories[$i]->state = ['opened' => true];
            $categories[$i]->icon = "jstree-folder";
            $categories[$i]->level = $level;
            $categories[$i]->image = dynamic_image(getImageUrl($pCat->image, 'thumb', 'sm'), 400);
            $categories[$i]->banner = dynamic_image(getImageUrl($pCat->banner, 'thumb', 'md'), 400);
            $i++;
        }

        if (isset($categories[0])) {
            $categories[0]->total = $countRes;
        }

        return Response::json(compact('categories', 'countRes'));
    }


    public function get_categories($id = null, $limit = '', $offset = '', $sort = 'row_order', $order = 'ASC', $has_child_or_item = 'true', $slug = '', $ignore_status = '', $seller_id = '', $store_id = '', $search = '', $ids = '', $language_code = "")
    {
        $level = 0;
        $storeId = getStoreId();

        // Convert the comma-separated ids string to an array
        $idsArray = !empty($ids) ? explode(',', $ids) : [];
        // Initial count query to calculate the total number of categories
        $countQuery = DB::table('categories as c1');

        if (isset($storeId) && !empty($storeId)) {
            $countQuery->where('c1.store_id', $storeId);
        }
        if (isset($store_id) && !empty($store_id)) {
            $countQuery->where('c1.store_id', $store_id);
        }

        // If `ids` is provided, apply whereIn condition
        if (!empty($idsArray)) {
            $countQuery->whereIn('c1.id', $idsArray);
        } else {
            // Continue with other filters when no specific ids are provided
            if (!empty($id)) {
                $parentId = DB::table('categories')
                    ->where('id', $id)
                    ->value('parent_id');

                if ($parentId != 0) {
                    $countQuery->where('c1.id', $parentId);
                } else {
                    $countQuery->where('c1.id', $id);
                }
            } else {
                if ($ignore_status == 1) {
                    $countQuery->orWhere('c1.parent_id', 0);
                } else {
                    $countQuery->where(function ($q) {
                        $q->where('c1.parent_id', 0)->where('c1.status', 1);
                    });
                }
            }
        }

        if (!empty($slug)) {
            $countQuery->where('c1.slug', $slug);
        }

        if (!empty($search)) {
            $countQuery->leftJoin('categories as c2', 'c2.parent_id', '=', 'c1.id');
            $countQuery->where(function ($q) use ($search) {
                $q->where('c1.name', 'LIKE', '%' . $search . '%')
                    ->orWhere('c2.name', 'LIKE', '%' . $search . '%');
            });
        }

        // Get the total count
        $total = $countQuery->count();

        // Main query for fetching category data
        $query = DB::table('categories as c1');

        if (isset($storeId) && !empty($storeId)) {
            $query->where('c1.store_id', $storeId);
        }
        if (isset($store_id) && !empty($store_id)) {
            $query->where('c1.store_id', $store_id);
        }

        // If `ids` is provided, apply whereIn condition
        if (!empty($idsArray)) {
            $query->whereIn('c1.id', $idsArray);
        } else {
            // Continue with other filters when no specific ids are provided
            if (!empty($id)) {
                if ($parentId != 0) {
                    $query->where('c1.id', $parentId);
                } else {
                    $query->where('c1.id', $id);
                }
            } else {
                if ($ignore_status == 1) {
                    $query->orWhere('c1.parent_id', 0);
                } else {
                    $query->where(function ($q) {
                        $q->where('c1.parent_id', 0)->where('c1.status', 1);
                    });
                }
            }
        }

        if (!empty($slug)) {
            $query->where('c1.slug', $slug);
        }

        if (!empty($search)) {
            $query->leftJoin('categories as c2', 'c2.parent_id', '=', 'c1.id');
            $query->where(function ($q) use ($search) {
                $q->where('c1.name', 'LIKE', '%' . $search . '%')
                    ->orWhere('c2.name', 'LIKE', '%' . $search . '%');
            });
        }

        if (!empty($limit) || !empty($offset)) {
            $query->offset($offset)->limit($limit);
        }

        $query->orderBy($sort, $order);

        // Fetch the categories
        $categories = $query->get(['c1.*']);

        $i = 0;
        $language_code = isset($language_code) && !empty($language_code) ? $language_code : get_language_code();
        // dd($language_code);
        foreach ($categories as $pCat) {
            $childId = $id ?? null;
            $categories[$i]->children = $this->subCategories($pCat->id, $level, $language_code);
            // $categories[$i]->text = $pCat->name;
            $categories[$i]->text = getDynamicTranslation('categories', 'name', $categories[$i]->id, $language_code);
            $categories[$i]->name = getDynamicTranslation('categories', 'name', $categories[$i]->id, $language_code);
            $categories[$i]->state = ['opened' => true];
            $categories[$i]->icon = "jstree-folder";
            $categories[$i]->level = $level;
            $categories[$i]->image = dynamic_image(getImageUrl($pCat->image, 'thumb', 'sm'), 400);
            $categories[$i]->banner = dynamic_image(getImageUrl($pCat->banner, 'thumb', 'md'), 400);
            $i++;
        }
        // dd($categories);
        return Response::json(['categories' => $categories, 'total' => $total]);
    }

    public function subCategories($id, $level, $language_code = '')
    {
        // dd($language_code);
        $level = $level + 1;
        $category = Category::find($id);
        $categories = $category->children;
        $language_code = isset($language_code) && !empty($language_code) ? $language_code : get_language_code();
        $i = 0;
        foreach ($categories as $p_cat) {
            // dd('here');
            $categories[$i]->children = $this->subCategories($p_cat->id, $level, $language_code);
            $categories[$i]->text = getDynamicTranslation('categories', 'name', $p_cat->id, $language_code);
            $categories[$i]->name = getDynamicTranslation('categories', 'name', $p_cat->id, $language_code);
            $categories[$i]->state = ['opened' => true];
            $categories[$i]->level = $level;
            $p_cat['image'] = getMediaImageUrl($p_cat['image']);
            $p_cat['banner'] = getMediaImageUrl($p_cat['banner']);
            $i++;
        }

        return $categories;
    }


    public function getSellerCategories(Request $request)
    {
        // dd('here');
        $level = 0;
        $store_id = getStoreId();
        $sellerId = $request->seller_id ?? '';
        // Fetch category IDs from seller data
        $sellerData = DB::table('seller_store')
            ->select('category_ids', 'deliverable_type')
            ->where('store_id', $store_id)
            ->where('seller_id', $sellerId)
            ->first();
        // dd($sellerData);
        if (!$sellerData || empty($sellerData->category_ids)) {
            return []; // Return empty if no data is found
        }

        $categoryIds = explode(',', $sellerData->category_ids);

        // Fetch categories with the given IDs and status
        $categories = Category::whereIn('id', $categoryIds)
            ->where('status', 1)
            ->where('store_id', $store_id)
            ->get();

        $parentIds = []; // To store IDs of parent categories
        $filteredCategories = []; // To store the final categories list
        $language_code = get_language_code();
        foreach ($categories as $pCat) {
            // Check if the parent category already exists in the list
            if (!in_array($pCat->parent_id, $parentIds)) {
                $category = $pCat->toArray();

                // Append additional data to the category
                $category['children'] = $this->subCategories($pCat->id, $level);
                $category['text'] = getDynamicTranslation('categories', 'name', $pCat->id, $language_code);
                $category['name'] = getDynamicTranslation('categories', 'name', $pCat->id, $language_code);
                $category['state'] = ['opened' => true];
                $category['icon'] = "jstree-folder";
                $category['level'] = $level;
                $category['image'] = getMediaImageUrl($category['image']);
                $category['banner'] = getMediaImageUrl($category['banner']);

                $filteredCategories[] = $category;
                $parentIds[] = $pCat->id; // Add this category ID to parent IDs
            }
        }

        // Add total count to the first filtered category
        if (isset($filteredCategories[0])) {
            $filteredCategories[0]['total'] = count($categories);
        }
        $filteredCategories[0]['deliverable_type'] = $sellerData->deliverable_type;
        // dd($filteredCategories);
        return $filteredCategories;
    }

    public function categoryOrder()
    {
        $store_id = getStoreId();

        // Fetch only main categories (where parent_id is null or 0)
        $categories = Category::where('status', 1)
            ->where('store_id', $store_id)
            ->where(function ($query) {
                $query->whereNull('parent_id')
                    ->orWhere('parent_id', 0);
            })
            ->orderBy('row_order', 'asc')
            ->get();
        $language_code = get_language_code();
        return view('admin.pages.tables.category_order', ['categories' => $categories, 'language_code' => $language_code]);
    }
    public function updateCategoryOrder(Request $request)
    {

        $category_ids = $request->input('category_id');
        $i = 0;

        foreach ($category_ids as $category_id) {
            $data = [
                'row_order' => $i
            ];

            Category::where('id', $category_id)->update($data);

            $i++;
        }
        return response()->json(['error' => false, 'message' => 'Category Order Saved !']);
    }

    public function category_slider()
    {

        $store_id = getStoreId();
        $categories = Category::where('status', 1)->where('store_id', $store_id)->orderBy('id', 'desc')->get();
        $languages = Language::all();

        return view('admin.pages.forms.category_sliders', ['categories' => $categories, 'languages' => $languages]);
    }

    public function category_data(Request $request)
    {

        $store_id = getStoreId();
        $search = $request->input('term');
        $limit = (int) $request->input('limit', 10);


        // Query categories using where clause with name condition
        $query = Category::query()
            ->where('store_id', $store_id)
            ->where('status', 1)
            ->orderBy('id', 'desc');

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $categories = $query->paginate($limit);
        // Map categories to format for response
        $formattedCategories = $categories->getCollection()->map(function ($category) {
            $language_code = get_language_code();
            $level = 0;
            return [
                'id' => $category->id,
                'text' => getDynamicTranslation('categories', 'name', $category->id, $language_code),
                'image' => getMediaImageUrl($category->image),
                'parent_id' => $category->parent_id ?? "",
            ];
        });
        // Create a new collection instance with formatted categories
        $formattedCollection = new Collection($formattedCategories);

        // Construct the response
        $response = [
            'total' => $categories->total(),
            'results' => $formattedCollection,
        ];

        return response()->json($response);
    }

    public function store_category_slider(Request $request)
    {

        $store_id = getStoreId();

        $rules = [
            'title' => 'required',
            'translated_category_slider_title' => 'nullable|array',
            'translated_category_slider_title.*' => 'nullable|string',
            'category_slider_style' => 'required',
            'background_color' => 'required',
            'banner_image' => 'required',
        ];

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }

        $category_slider_data = $request->all();
        unset($category_slider_data['_method']);
        unset($category_slider_data['_token']);
        // Handle translations
        $translations = ['en' => $category_slider_data['title']];

        if (!empty($category_slider_data['translated_category_slider_title'])) {
            $translations = array_merge($translations, $category_slider_data['translated_category_slider_title']);
        }

        // Encode translations as JSON
        $category_slider_data['title'] = json_encode($translations, JSON_UNESCAPED_UNICODE);

        $category_slider_data['category_ids'] = isset($request->category_ids) ? implode(',', $request->category_ids) : '';

        // Rename the "category_slider_style" key to "style"
        $category_slider_data['style'] = $category_slider_data['category_slider_style'];
        unset($category_slider_data['category_slider_style']);
        unset($category_slider_data['translated_category_slider_title']);

        $category_slider_data['status'] = 1;
        $category_slider_data['store_id'] = isset($store_id) ? $store_id : '';

        $category_slider_data['banner_image'] = $request->banner_image;
        CategorySliders::create($category_slider_data);

        if ($request->ajax()) {
            return response()->json([
                'message' => labels('admin_labels.category_slider_created_successfully', 'Category slider created successfully')
            ]);
        }
    }

    public function category_sliders_list(Request $request)
    {
        $store_id = getStoreId();
        $search = trim($request->input('search'));
        $sort = $request->input('sort', 'category_sliders.id');
        $order = $request->input('order', 'DESC');
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $status = $request->input('status', '');

        // Start the query with join
        $category_slider_data = CategorySliders::select('category_sliders.*')
            ->leftJoin('categories', function ($join) {
                $join->on('categories.id', '=', DB::raw('FIND_IN_SET(categories.id, category_sliders.category_ids)'));
            })
            ->where('category_sliders.store_id', $store_id);

        // Filter by search term in title or category name
        if ($search) {
            $category_ids = Category::where('name', 'like', '%' . $search . '%')->pluck('id');
            // Check if any category ID matches
            if ($category_ids->isNotEmpty()) {
                $ids = $category_ids->implode(',');
                $category_slider_data->where(function ($query) use ($search, $ids) {
                    $query->where('category_sliders.title', 'like', '%' . $search . '%')
                        ->orWhereRaw("FIND_IN_SET(category_sliders.category_ids, '$ids')")
                        ->orWhere(DB::raw("FIND_IN_SET(categories.id, category_sliders.category_ids)"), '>', 0);
                });
            } else {
                $category_slider_data->where('category_sliders.title', 'like', '%' . $search . '%');
            }
        }
        // Apply status filter if provided
        if (!is_null($status) && $status !== '') {
            $category_slider_data->where('category_sliders.status', $status);
        }
        // Count total records for pagination
        $total = $category_slider_data->count();

        // Fetch paginated data
        $sliders = $category_slider_data->orderBy($sort, $order)
            ->offset($offset)
            ->limit($limit)
            ->get();

        // Prepare data for response
        $data = $sliders->map(function ($s) {
            $language_code = get_language_code();
            $delete_url = route('admin.category_sliders.destroy', $s->id);
            $edit_url = route('admin.category_sliders.update', $s->id);

            // Retrieve category names based on category_ids
            $categoryIds = explode(',', $s->category_ids);
            // $category_names = Category::whereIn('id', $categoryIds)->pluck('name')->implode(', ');
            $category_names = Category::whereIn('id', $categoryIds)
                ->get()
                ->map(fn($category) => getDynamicTranslation('categories', 'name', $category->id, $language_code))
                ->implode(', ');

            $action = '<div class="dropdown bootstrap-table-dropdown">
                        <a href="#" class="text-dark" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-horizontal-rounded"></i>
                        </a>
                        <div class="dropdown-menu table_dropdown category_slider_action_dropdown" aria-labelledby="dropdownMenuButton">
                            <a class="dropdown-item dropdown_menu_items" href="' . $edit_url . '"><i class="bx bx-pencil mx-2"></i> Edit</a>
                            <a class="dropdown-item delete-data dropdown_menu_items" data-url="' . $delete_url . '"><i class="bx bx-trash mx-2"></i> Delete</a>
                        </div>
                    </div>';

            return [
                'id' => $s->id,
                'title' => getDynamicTranslation('category_sliders', 'title', $s->id, $language_code),
                'categories' => $category_names,
                'status' => '<select class="form-select status_dropdown change_toggle_status ' . ($s->status == 1 ? 'active_status' : 'inactive_status') . '" data-id="' . $s->id . '" data-url="/admin/category_sliders/update_status/' . $s->id . '" aria-label="">
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


    public function update_category_slider_status($id)
    {
        $category_slider = CategorySliders::findOrFail($id);
        $category_slider->status = $category_slider->status == '1' ? '0' : '1';
        $category_slider->save();
        return response()->json(['success' => labels('admin_labels.status_updated_successfully', 'Status updated successfully.')]);
    }

    public function category_slider_destroy($id)
    {
        $category = CategorySliders::find($id);

        if ($category->delete()) {
            return response()->json([
                'error' => false,
                'message' => labels('admin_labels.slider_deleted_successfully', 'Slider deleted successfully!')
            ]);
        } else {
            return response()->json(['error' => labels('admin_labels.something_went_wrong', 'Something went wrong')]);
        }
    }

    public function category_slider_edit($data)
    {
        $store_id = getStoreId();
        $languages = Language::all();
        $category_sliders = CategorySliders::where('status', 1)->where('store_id', $store_id)->get();
        $language_code = get_language_code();
        $categories = Category::where('status', 1)->where('store_id', $store_id)->orderBy('id', 'desc')->get();

        $data = CategorySliders::where('store_id', $store_id)
            ->find($data);

        if ($data === null || empty($data)) {
            return view('admin.pages.views.no_data_found');
        } else {
            return view('admin.pages.forms.update_category_slider', [
                'data' => $data,
                'category_sliders' => $category_sliders,
                'categories' => $categories,
                'languages' => $languages,
                'language_code' => $language_code
            ]);
        }
    }


    public function category_slider_update(Request $request, $data)
    {

        // dd($request);
        $rules = [
            'title' => 'required',
            'category_slider_style' => 'required',
            'background_color' => 'required',
            'banner_image' => 'required',
        ];

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }

        $slider = CategorySliders::find($data);


        $new_name = $request->title;
        $translations = ['en' => $new_name];

        if (!empty($request->translated_category_slider_title)) {
            $translations = array_merge($translations, $request->translated_category_slider_title);
        }
        $category_slider_data['title'] = json_encode($translations, JSON_UNESCAPED_UNICODE);
        $category_slider_data['category_ids'] = isset($request->category_ids) ? implode(',', $request->category_ids) : '';
        $category_slider_data['style'] = $request->category_slider_style;
        $category_slider_data['status'] = 1;
        $category_slider_data['banner_image'] = $request->banner_image;
        $slider->update($category_slider_data);

        if ($request->ajax()) {
            return response()->json([
                'message' => labels('admin_labels.slider_updated_successfully', 'Slider updated successfully'),
                'location' => route('category_slider.index')
            ]);
        }
    }
    public function delete_selected_data(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:categories,id'
        ]);

        $tables = ['products', 'seller_store'];
        $columns = ['category_id', 'category_ids'];

        // Initialize an array to store the IDs that can't be deleted
        $nonDeletableIds = [];

        foreach ($request->ids as $id) {
            if (isForeignKeyInUse($tables, $columns, $id)) {
                // Collect the ID that cannot be deleted
                $nonDeletableIds[] = $id;
            }
        }
        // Check if there are any non-deletable IDs
        if (!empty($nonDeletableIds)) {
            return response()->json([
                'error' => labels('admin_labels.cannot_delete_category_associated_with_products_seller', 'You cannot delete these categories: ' . implode(', ', $nonDeletableIds) . ' because they are associated with products and sellers.'),
                'non_deletable_ids' => $nonDeletableIds
            ], 401);
        }

        // Proceed to delete the categories that are deletable
        Category::destroy($request->ids);

        return response()->json(['message' => 'Selected categories deleted successfully.']);
    }
    public function delete_selected_slider_data(Request $request)
    {

        // Validate the incoming request
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:category_sliders,id'
        ]);

        CategorySliders::destroy($request->ids);

        return response()->json(['message' => 'Selected sliders deleted successfully.']);
    }

    public function deleteWithProducts(Request $request)
    {
        $categoryId = $request->input('category_id');
        $moveToCategoryId = $request->input('move_to_category_id');
        $deleteProducts = $request->input('delete_products');

        $category = Category::find($categoryId);
        if (!$category) {
            return response()->json(['error' => 'Category not found.']);
        }

        // Check for category slider association (optional, similar to destroy)
        $isCategoryInSliders = \DB::table('category_sliders')
            ->where('category_ids', 'LIKE', '%' . $categoryId . '%')
            ->exists();
        if ($isCategoryInSliders) {
            return response()->json([
                'error' => labels('admin_labels.cannot_delete_category_associated_with_sliders', 'You cannot delete this category because it is associated with category sliders.')
            ]);
        }

        // Move or delete products
        if ($deleteProducts) {
            // Delete all products in this category
            Product::where('category_id', $categoryId)->delete();
        } elseif ($moveToCategoryId) {
            // Move products to another category
            Product::where('category_id', $categoryId)->update(['category_id' => $moveToCategoryId]);
        } else {
            return response()->json(['error' => 'No action selected for products.']);
        }

        // Delete the category
        $category->delete();
        return response()->json([
            'error' => false,
            'message' => labels('admin_labels.category_deleted_successfully', 'Category deleted successfully!')
        ]);
    }
}
