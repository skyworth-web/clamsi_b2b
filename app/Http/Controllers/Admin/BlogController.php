<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\BlogCategory;
use App\Models\Blog;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BlogController extends Controller
{

    public function index()
    {
        $languages = Language::all();
        return view('admin.pages.forms.blog_categories', ['languages' => $languages]);
    }

    public function storeCategory(Request $request)
    {
        $store_id = getStoreId();

        $rules = [
            'name' => 'required',
            'translated_category_name' => 'sometimes|array',
            'translated_category_name.*' => 'nullable|string',
            'image' => 'required',
        ];
        $validationResponse = validatePanelRequest($request, $rules);
        if ($validationResponse) {
            return $validationResponse;
        }

        $category_data = $request->only(['name', 'translated_category_name', 'image']);

        $translations = [
            'en' => $category_data['name']
        ];

        if (!empty($category_data['translated_category_name'])) {
            $translations = array_merge($translations, $category_data['translated_category_name']);
        }

        $category_data['name'] = json_encode($translations, JSON_UNESCAPED_UNICODE);

        unset($category_data['translated_category_name']);

        $category_data['slug'] = generateSlug($translations['en'], 'blog_categories');
        $category_data['status'] = 1;
        $category_data['store_id'] = $store_id;

        BlogCategory::create($category_data);

        if ($request->ajax()) {
            return response()->json([
                'message' => labels('admin_labels.category_created_successfully', 'Category created successfully')
            ]);
        }
    }

    public function editCategory($data)
    {
        $store_id = getStoreId();
        $categories = BlogCategory::all();
        $data = BlogCategory::where('store_id', $store_id)
            ->find($data);
        $languages = Language::all();
        if ($data === null || empty($data)) {
            return view('admin.pages.views.no_data_found');
        } else {
            return view('admin.pages.forms.update_blog_category', [
                'data' => $data,
                'categories' => $categories,
                'languages' => $languages
            ]);
        }
    }
    public function updateCategory(Request $request, $data)
    {
        $rules = [
            'name' => 'required',
            'image' => 'required',
        ];

        $validationResponse = validatePanelRequest($request, $rules);
        if ($validationResponse) {
            return $validationResponse;
        }

        $category = BlogCategory::find($data);

        $category_data = $request->only(['name', 'translated_category_name', 'image']);

        $existingTranslations = json_decode($category->name, true) ?? [];

        $existingTranslations['en'] = $request->name;

        if (!empty($request->translated_category_name)) {
            $existingTranslations = array_merge($existingTranslations, $request->translated_category_name);
        }

        $category_data['name'] = json_encode($existingTranslations, JSON_UNESCAPED_UNICODE);

        $current_slug = $category->slug;
        $category_data['slug'] = generateSlug($existingTranslations['en'], 'brands', 'slug', $current_slug);

        $category_data['status'] = 1;

        $category->update($category_data);

        if ($request->ajax()) {
            return response()->json([
                'message' => labels('admin_labels.category_updated_successfully', 'Category updated successfully'),
                'location' => route('admin.blogs.index')
            ]);
        }
    }


    public function updateCategoryStatus($id)
    {
        $category = BlogCategory::findOrFail($id);

        if (isForeignKeyInUse('blogs', 'category_id', $id)) {
            return response()->json([
                'status_error' => labels('admin_labels.cannot_deactivate_category_associated_with_blogs', 'You cannot deactivate this category because it is associated with blogs.')
            ]);
        } else {
            $category->status = $category->status == '1' ? '0' : '1';
            $category->save();
            return response()->json(['success' => labels('admin_labels.status_updated_successfully', 'Status updated successfully.')]);
        }
    }


    public function destroyCategory($id)
    {
        $category = BlogCategory::find($id);

        if (isForeignKeyInUse('blogs', 'category_id', $id)) {
            return response()->json([
                'error' => labels('admin_labels.cannot_delete_category_associated_with_blogs', 'You cannot delete this category because it is associated with blogs.')
            ]);
        }
        if ($category) {
            $category->delete();
            return response()->json(['error' => false, 'message' => labels('admin_labels.blog_category_deleted_successfully', 'Blog Category deleted successfully!')]);
        } else {
            return response()->json(['error' => labels('admin_labels.data_not_found', 'Data Not Found')]);
        }
    }

    public function categoryList(Request $request)
    {
        $store_id = getStoreId();
        $search = trim(request('search'));
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = (request('limit')) ? request('limit') : "10";
        $status = $request->input('status') ?? '';

        $category_data = BlogCategory::when($search, function ($query) use ($search) {
            return $query->where('name', 'like', '%' . $search . '%');
        });
        if ($status !== '') {
            $category_data->where('status', $status);
        }
        $category_data->where('store_id', $store_id);
        $total = $category_data->count();

        // Use Paginator to handle the server-side pagination
        $blogs = $category_data->orderBy($sort, $order)->offset($offset)
            ->limit($limit)
            ->get();

        // Prepare the data for the "Actions" field
        $data = $blogs->map(function ($b) {
            $language_code = get_language_code();
            $delete_url = route('admin.blog_categories.destroy', $b->id);
            $edit_url = route('blog_categories.edit', $b->id);
            $action = '<div class="dropdown bootstrap-table-dropdown">
                <a href="#" class="text-dark" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="bx bx-dots-horizontal-rounded"></i>
                </a>
                <div class="dropdown-menu table_dropdown brand_action_dropdown" aria-labelledby="dropdownMenuButton">
                <a class="dropdown-item dropdown_menu_items" href="' . $edit_url . '"><i class="bx bx-pencil mx-2"></i> Edit</a>
                    <a class="dropdown-item delete-data dropdown_menu_items" data-url="' . $delete_url . '"><i class="bx bx-trash mx-2"></i> Delete</a>
                </div>
            </div>';
            $image = route('admin.dynamic_image', [
                'url' => getMediaImageUrl($b->image),
                'width' => 60,
                'quality' => 90
            ]);
            return [
                'id' => $b->id,
                'name' => getDynamicTranslation('blog_categories', 'name', $b->id, $language_code),
                'status' => '<div class="d-flex justify-content-center"><select class="form-select status_dropdown change_toggle_status ' . ($b->status == 1 ? 'active_status' : 'inactive_status') . '" data-id="' . $b->id . '" data-url="admin/blog_categories/update_status/' . $b->id . '" aria-label="">
                  <option value="1" ' . ($b->status == 1 ? 'selected' : '') . '>Active</option>
                  <option value="0" ' . ($b->status == 0 ? 'selected' : '') . '>Deactive</option>
              </div></select>',
                'image' => '<div class="d-flex justify-content-center"><a href="' . getMediaImageUrl($b->image)  . '" data-lightbox="image-' . $b->id . '"><img src="' . $image . '" alt="Avatar" class="rounded"/></a></div>',
                'operate' => $action,
            ];
        });

        return response()->json([
            "rows" => $data,
            "total" => $total,
        ]);
    }

    public function createBlog()
    {
        $store_id = getStoreId();
        $languages = Language::all();
        $categories = BlogCategory::where('status', 1)->where('store_id', $store_id)->orderBy('id', 'desc')->get();

        return view('admin.pages.forms.blogs', ['categories' => $categories, 'languages' => $languages]);
    }

    public function storeBlog(Request $request)
    {
        $store_id = getStoreId();

        $rules = [
            'title' => 'required',
            'translated_blog_title' => 'sometimes|array',
            'translated_blog_title.*' => 'nullable|string',
            'image' => 'required',
            'category_id' => 'required|exists:blog_categories,id',
            'description' => 'required',
        ];

        $validationResponse = validatePanelRequest($request, $rules);
        if ($validationResponse) {
            return $validationResponse;
        }

        $blog_data = $request->only(['title', 'translated_blog_title', 'imagetegory_id', 'description']);

        $translations = ['en' => $blog_data['title']];

        if (!empty($blog_data['translated_blog_title'])) {
            $translations = array_merge($translations, $blog_data['translated_blog_title']);
        }

        $blog_data['title'] = json_encode($translations, JSON_UNESCAPED_UNICODE);

        unset($blog_data['translated_blog_title']);

        $decodedTitle = json_decode($blog_data['title'], true);
        $blog_data['slug'] = generateSlug($decodedTitle['en'], 'blogs');
        $blog_data['image'] = $request->image;

        $blog_data['status'] = 1;
        $blog_data['store_id'] = $store_id;

        Blog::create($blog_data);

        if ($request->ajax()) {
            return response()->json([
                'message' => labels('admin_labels.blog_created_successfully', 'Blog created successfully')
            ]);
        }
    }


    public function getBlogCategories(Request $request)
    {

        $search = trim($request->search) ?? "";
        $store_id = getStoreId();

        $categories = BlogCategory::where('name', 'like', '%' . $search . '%')->where('store_id', $store_id)->where('status', 1)->get();
        $language_code = get_language_code();
        $data = array();
        foreach ($categories as $category) {
            $data[] = array("id" => $category->id, "text" => getDynamicTranslation('blog_categories', 'name', $category->id, $language_code));
        }
        return response()->json($data);
    }

    public function blogList(Request $request)
    {
        $store_id = getStoreId();

        $search = trim(request('search'));
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = (request('limit')) ? request('limit') : "10";
        $category_id = (request('category_id')) ? request('category_id') : "";
        $blog_data = Blog::when($search, function ($query) use ($search) {
            return $query->where('title', 'like', '%' . $search . '%');
        });

        if ($category_id !== '') {
            $blog_data->where('category_id', $category_id);
        }

        $blog_data->where('store_id', $store_id);
        $total = $blog_data->count();

        // Use Paginator to handle the server-side pagination
        $blogs = $blog_data->orderBy($sort, $order)->offset($offset)
            ->limit($limit)
            ->get();

        // Prepare the data for the "Actions" field
        $data = $blogs->map(function ($b) {
            $language_code = get_language_code();
            $delete_url = route('blogs.destroy', $b->id);
            $edit_url = route('blogs.edit', $b->id);
            $action = '<div class="dropdown bootstrap-table-dropdown">
                <a href="#" class="text-dark" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="bx bx-dots-horizontal-rounded"></i>
                </a>
                <div class="dropdown-menu table_dropdown blog_action_dropdown" aria-labelledby="dropdownMenuButton">
                <a class="dropdown-item dropdown_menu_items" href="' . $edit_url . '"><i class="bx bx-pencil mx-2"></i> Edit</a>
                    <a class="dropdown-item delete-data dropdown_menu_items" data-url="' . $delete_url . '"><i class="bx bx-trash mx-2"></i> Delete</a>
                </div>
            </div>';
            $image = route('admin.dynamic_image', [
                'url' => getMediaImageUrl($b->image),
                'width' => 60,
                'quality' => 90
            ]);
            return [
                'id' => $b->id,
                'title' => getDynamicTranslation('blogs', 'title', $b->id, $language_code),
                'status' => '<div><select class="form-select status_dropdown change_toggle_status ' . ($b->status == 1 ? 'active_status' : 'inactive_status') . '" data-id="' . $b->id . '" data-url="admin/blogs/update_status/' . $b->id . '" aria-label="">
                  <option value="1" ' . ($b->status == 1 ? 'selected' : '') . '>Active</option>
                  <option value="0" ' . ($b->status == 0 ? 'selected' : '') . '>Deactive</option>
              </div></select>',
                'image' => '<div class="d-flex justify-content-center"><a href="' . getMediaImageUrl($b->image)  . '" data-lightbox="image-' . $b->id . '"><img src="' . $image . '" alt="Avatar" class="rounded"/></a></div>',
                'operate' => $action,
            ];
        });

        return response()->json([
            "rows" => $data, // Return the formatted data for the "Actions" field
            "total" => $total,
        ]);
    }

    public function updateBlog(Request $request, $data)
    {
        $rules = [
            'title' => 'required',
            'image' => 'required',
            'category_id' => 'required|exists:blog_categories,id',
            'description' => 'required',
            'translated_blog_title' => 'nullable|array',
            'translated_blog_title.*' => 'nullable|string',
        ];

        $validationResponse = validatePanelRequest($request, $rules);

        if ($validationResponse) {
            return $validationResponse;
        }

        $blog = Blog::find($data);

        if (!$blog) {
            return response()->json(['error' => 'Blog not found.'], 404);
        }

        $blog_data = $request->all();

        unset($blog_data['_method']);
        unset($blog_data['_token']);

        $existingTranslations = json_decode($blog->title, true) ?? [];
        $existingTranslations['en'] = $request->title;

        if (!empty($request->translated_blog_title)) {
            $existingTranslations = array_merge($existingTranslations, $request->translated_blog_title);
        }
        $blog_data['title'] = json_encode($existingTranslations, JSON_UNESCAPED_UNICODE);

        $blog_data['image'] = $request->image;

        $blog_data['category_id'] = $request->category_id;
        $blog_data['description'] = $request->description;
        $newSlug = generateSlug($existingTranslations['en'], 'blogs', 'slug', $blog->slug);
        $blog_data['slug'] = $newSlug;
        $blog_data['status'] = 1;
        unset($blog_data['translated_blog_title']);

        $blog->update($blog_data);
        if ($request->ajax()) {
            return response()->json([
                'message' => 'Blog updated successfully',
                'location' => route('manage_blogs.index')
            ]);
        }
    }

    public function editBlog($data)
    {
        $store_id = getStoreId();
        $languages = Language::all();
        $categories = BlogCategory::where('status', '1')->get();
        $language_code = get_language_code();

        $data = Blog::where('store_id', $store_id)
            ->find($data);

        if ($data === null || empty($data)) {
            return view('admin.pages.views.no_data_found');
        } else {
            return view('admin.pages.forms.update_blog', [
                'data' => $data,
                'categories' => $categories,
                'languages' => $languages,
                'language_code' => $language_code
            ]);
        }
    }

    public function destroyBlog($id)
    {

        $blog = Blog::find($id);

        if ($blog->delete()) {
            return response()->json([
                'error' => false,
                'message' => labels('admin_labels.blog_deleted_successfully', 'Blog deleted successfully!')
            ]);
        } else {
            return response()->json(['error' => labels('admin_labels.something_went_wrong', 'Something went wrong')]);
        }
    }
    public function updateBlogStatus($id)
    {
        $blog = Blog::findOrFail($id);
        $blog->status = $blog->status == '1' ? '0' : '1';
        $blog->save();
        return response()->json(['success' => labels('admin_labels.status_updated_successfully', 'Status updated successfully.')]);
    }
    public function delete_selected_data(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:blog_categories,id'
        ]);

        $nonDeletableIds = [];

        foreach ($request->ids as $id) {

            if (isForeignKeyInUse('blogs', 'category_id', $id)) {

                $nonDeletableIds[] = $id;
            }
        }
        if (!empty($nonDeletableIds)) {
            return response()->json([
                'error' => labels(
                    'admin_labels.cannot_delete_category_associated_with_blogs',
                    'You cannot delete these categories: ' . implode(', ', $nonDeletableIds) . ' because they are associated with blogs'
                ),
                'non_deletable_ids' => $nonDeletableIds
            ], 401);
        }
        BlogCategory::destroy($request->ids);

        return response()->json(['message' => 'Selected categories deleted successfully.']);
    }
    public function delete_selected_blog_data(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:blogs,id'
        ]);

        foreach ($request->ids as $id) {
            $blog = Blog::find($id);

            if ($blog) {
                Blog::where('id', $id)->delete();
            }
        }
        Blog::destroy($request->ids);

        return response()->json(['message' => 'Selected data deleted successfully.']);
    }
}
