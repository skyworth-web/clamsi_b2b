<?php

namespace App\Http\Controllers\Seller;

use App\Models\User;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where('status', 1)->get();
        return view('seller.pages.tables.categories', ['categories' => $categories]);
    }

    public function list(Request $request)
    {
        $store_id = getStoreId();
        $user_id = Auth::user()->id;

        $seller_id = Seller::where('user_id', $user_id)->value('id');

        $search = trim(request('search'));
        $sort = request('sort') ?: 'id';
        $order = request('order') ?: 'DESC';
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = request('limit') ?: 10;


        $seller_data = DB::table('seller_store')->select('category_ids')->where('seller_id', $seller_id)->where('store_id', $store_id)->get();

        if (!$seller_data) {
            return response()->json([
                "rows" => [],
                "total" => 0,
            ]);
        }

        $category_ids = explode(",", $seller_data[0]->category_ids);

        $category_data = Category::whereIn('id', $category_ids)->where('store_id', $store_id);
        if ($search) {
            $category_data->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('parent_id', 'like', '%' . $search . '%');
            });
        }
        $total = $category_data->count();

        $categories = $category_data->orderBy($sort, $order)->offset($offset)
            ->limit($limit)
            ->get();
        $language_code = get_language_code();
        $data = $categories->map(function ($c) use ($language_code) {
            $status = ($c->status == 1) ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Deactive</span>';
            $image = route('seller.dynamic_image', [
                'url' => getMediaImageUrl($c->image),
                'width' => 60,
                'quality' => 90
            ]);
            $banner = route('seller.dynamic_image', [
                'url' => getMediaImageUrl($c->banner),
                'width' => 60,
                'quality' => 90
            ]);
            return [
                'id' => $c->id,
                'name' => getDynamicTranslation('categories', 'name', $c->id, $language_code),
                'status' => $status,
                'image' => '<div><a href="' . getMediaImageUrl($c->image)  . '" data-lightbox="image-' . $c->id . '"><img src="' . $image  . '" alt="Avatar" class="rounded"/></a></div>',
                'banner' => '<div ><a href="' . getMediaImageUrl($c->banner) . '" data-lightbox="banner-' . $c->id . '"><img src="' . $banner  . '" alt="Avatar" class="rounded"/></a></div>',
            ];
        });

        return response()->json([
            "rows" => $data,
            "total" => $total,
        ]);
    }



    protected function getSubcategories($parentCategoryId)
    {
        $subcategories = Category::where('parent_id', $parentCategoryId)->get()->map(function ($sub) {
            return [
                'id' => $sub->id,
                'name' => $sub->name,
                'image' => asset('/storage/' . $sub->image),
                'banner' => asset('/storage/' . $sub->banner),
                'subcategories' => $this->getSubcategories($sub->id),
            ];
        });

        return $subcategories;
    }

    public function subCategories($id, $level, $language_code = '')
    {
        $level = $level + 1;
        $category = Category::find($id);
        $categories = $category->children;

        $i = 0;
        foreach ($categories as $p_cat) {
            $categories[$i]->children = $this->subCategories($p_cat->id, $level);
            $categories[$i]->text = getDynamicTranslation('categories', 'name', $p_cat['id'], $language_code);
            $categories[$i]->name = getDynamicTranslation('categories', 'name', $p_cat['id'], $language_code);
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
        $store_id = $request->store_id ?? getStoreId();
        $level = 0;
        $user_id = Auth::user()->id;
        $seller_id = Seller::where('user_id', $user_id)->value('id');
        $seller_data = DB::table('seller_store')
            ->select('category_ids', 'deliverable_type')
            ->where('store_id', $store_id)
            ->where('seller_id', $seller_id)->get()[0];


        if (!$seller_data) {
            return [];
        }

        $category_ids = explode(",", $seller_data->category_ids);

        $categories = Category::whereIn('id', $category_ids)
            ->where('status', 1)
            ->where('store_id', $store_id)
            ->get()
            ->toArray();
        $language_code = get_language_code();
        foreach ($categories as &$p_cat) {
            $p_cat['children'] = $this->subCategories($p_cat['id'], $level);
            $p_cat['text'] = getDynamicTranslation('categories', 'name', $p_cat['id'], $language_code);
            $p_cat['name'] = getDynamicTranslation('categories', 'name', $p_cat['id'], $language_code);
            $p_cat['state'] = ['opened' => true];
            $p_cat['icon'] = "jstree-folder";
            $p_cat['level'] = $level;
            $p_cat['image'] = getMediaImageUrl($p_cat['image']);
            $p_cat['banner'] = getMediaImageUrl($p_cat['banner']);
        }

        if (!empty($categories)) {
            $categories[0]['total'] = count($category_ids);
        }
        $categories[0]['deliverable_type'] = $seller_data->deliverable_type;
        return $categories;
    }

    public function get_seller_categories(Request $request, $language_code = '')
    {
        $store_id = $request->store_id ?? getStoreId();
        $user_id = Auth::user()->id;
        $seller_id = Seller::where('user_id', $user_id)->value('id');

        $level = 0;
        $seller_id = $request->seller_id ?? $seller_id;
        $search = trim($request->input('search', ''));

        $seller_data = DB::table('seller_store')
            ->select('category_ids')
            ->where('store_id', $store_id)
            ->where('seller_id', $seller_id)
            ->first();

        if (!$seller_data) {
            return response()->json([
                'categories' => [],
                'total' => 0
            ]);
        }

        $category_ids = explode(",", $seller_data->category_ids);

        // Apply search filter
        $categoriesQuery = Category::whereIn('id', $category_ids)
            ->where('status', 1)
            ->where('store_id', $store_id);

        if ($search) {
            $categoriesQuery->where('name', 'like', '%' . $search . '%');
        }

        $categories = $categoriesQuery->get()->toArray();

        foreach ($categories as &$p_cat) {
            $p_cat['children'] = $this->subCategories($p_cat['id'], $level, $language_code);
            $p_cat['text'] = getDynamicTranslation('categories', 'name', $p_cat['id'], $language_code);
            $p_cat['name'] = getDynamicTranslation('categories', 'name', $p_cat['id'], $language_code);
            $p_cat['state'] = ['opened' => true];
            $p_cat['icon'] = "jstree-folder";
            $p_cat['level'] = $level;
            $p_cat['image'] = getMediaImageUrl($p_cat['image']);
            $p_cat['banner'] = getMediaImageUrl($p_cat['banner']);
        }

        // Replace null values with empty strings
        foreach ($categories as &$category) {
            foreach ($category as $key => $value) {
                if ($value === null) {
                    $category[$key] = '';
                }
            }
        }
        unset($category);

        // Prepare the response with total as a separate key
        return response()->json([
            'categories' => $categories,
            'total' => count($categories)
        ]);
    }

    // public function get_seller_categories_filter(Request $request)
    // {
    //     $store_id = $request->store_id ?? getStoreId();
    //     $user_id = Auth::user()->id;
    //     $seller_id = Seller::where('user_id', $user_id)->value('id');

    //     $level = 0;
    //     $seller_id = $seller_id;
    //     $seller_data = DB::table('seller_store')
    //         ->select('category_ids')
    //         ->where('store_id', $store_id)
    //         ->where('seller_id', $seller_id)->get()[0];



    //     if (!$seller_data) {
    //         return [];
    //     }

    //     $category_ids = explode(",", $seller_data->category_ids);

    //     $categories = Category::whereIn('id', $category_ids)
    //         ->where('status', 1)
    //         ->where('store_id', $store_id)
    //         ->get()
    //         ->toArray();

    //     return $categories;
    // }
    public function get_seller_categories_filter(Request $request)
    {
        $store_id = $request->store_id ?? getStoreId();
        $category_ids = [];

        // Check if user is authenticated
        if (Auth::check()) {
            $user_id = Auth::id();
            $seller_id = Seller::where('user_id', $user_id)->value('id');

            if ($seller_id) {
                // Get seller store categories
                $seller_data = DB::table('seller_store')
                    ->select('category_ids')
                    ->where('store_id', $store_id)
                    ->where('seller_id', $seller_id)
                    ->first();

                if ($seller_data) {
                    $category_ids = explode(",", $seller_data->category_ids);
                }
            }
        } else {
            // If no authenticated user, fetch store-wise categories
            $category_ids = Category::where('store_id', $store_id)
                ->where('status', 1)
                ->pluck('id')
                ->toArray();
        }

        // Fetch categories based on the determined category IDs
        $categories = Category::whereIn('id', $category_ids)
            ->where('status', 1)
            ->where('store_id', $store_id)
            ->get()
            ->toArray();
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
        return $categories;
    }
    public function getCategoryDetails(Request $request)
    {
        $store_id = $request->store_id ?? getStoreId();
        $search = trim($request->input('search'));
        $limit = (int) $request->input('limit', 10);

        $category = Category::where('name', 'like', '%' . $search . '%')
            ->where('store_id', $store_id)
            ->where('status', 1)
            ->get(['id', 'parent_id', 'name']);

        $totalCount = Category::where('name', 'like', '%' . $search . '%')
            ->where('store_id', $store_id)
            ->selectRaw('count(id) as total')
            ->first()
            ->total;
        $language_code = get_language_code();
        $response = [
            'total' => $totalCount,
            'results' => $category->map(function ($category) use ($language_code) {
                return [
                    'id' => $category->id,
                    'text' => getDynamicTranslation('categories', 'name', $category->id, $language_code),
                    'parent_id' => $category->parent_id,
                ];
            }),
        ];

        return response()->json($response);
    }
}
