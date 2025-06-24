<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Response;

class CategoryController extends Controller
{

    public function getCategories($id = null, $limit = '', $offset = '', $sort = 'row_order', $order = 'ASC', $has_child_or_item = 'true', $slug = '', $ignore_status = '', $seller_id = '', $store_id = '')
    {
        $query = Category::with(['children' => function ($query) use ($has_child_or_item) {
            if ($has_child_or_item == 'false') {
                $query->withCount('products')
                    ->withCount('children')
                    ->havingRaw('(products_count > 0 OR children_count > 0)');
            } else {
                $query->with('children');
            }
        }]);

        if ($ignore_status == 1) {
            $query->where(function ($q) use ($id) {
                $q->whereNull('parent_id')
                    ->orWhere('parent_id', 0)
                    ->orWhere('id', $id);
            });
        } else {
            $query->where(function ($q) use ($id) {
                $q->where('status', 1)
                    ->whereNull('parent_id')
                    ->orWhere('status', 1)
                    ->where('parent_id', 0)
                    ->orWhere('id', $id)
                    ->where('status', 1);
            });
        }

        if (!empty($slug)) {
            $query->where('slug', $slug);
        }

        if (!empty($store_id)) {
            $query->where('store_id', $store_id);
        }

        if (!empty($limit) || !empty($offset)) {
            $query->offset($offset)->limit($limit);
        }

        $query->orderBy($sort, $order);

        $categories = $query->get();
        $language_code = get_language_code();
        // Fetch product count separately for each category
        foreach ($categories as $category) {
            $category->product_count = Product::where('category_id', $category->id)
                ->where('status', 1)
                ->count();
            $category->name = getDynamicTranslation('categories', 'name', $category->id, $language_code);
        }

        $countRes = Category::where(function ($q) use ($id, $ignore_status) {
            if ($ignore_status == 1) {
                $q->whereNull('parent_id')
                    ->orWhere('parent_id', 0)
                    ->orWhere('id', $id);
            } else {
                $q->where('status', 1)
                    ->whereNull('parent_id')
                    ->orWhere('status', 1)
                    ->where('parent_id', 0)
                    ->orWhere('id', $id)
                    ->where('status', 1);
            }
        })->count();

        $categories = $this->formatCategories($categories);

        if (!empty($categories)) {
            $categories[0]['total'] = $countRes;
        }

        return response()->json(compact('categories', 'countRes'));
    }


    private function formatCategories($categories, $level = 0)
    {
        $formattedCategories = [];
        $language_code = get_language_code();
        foreach ($categories as $category) {
            $category['text'] = e($category['name']);
            $category['name'] = getDynamicTranslation('categories', 'name', $category->id, $language_code);
            $category['state'] = ['opened' => true];
            $category['icon'] = "jstree-folder";
            $category['level'] = $level;
            $category['image'] = getMediaImageUrl($category['image']);
            $category['banner'] = getMediaImageUrl($category['banner']);

            if (!empty($category['children'])) {
                $category['children'] = $this->formatCategories($category['children'], $level + 1);
            }

            $formattedCategories[] = $category;
        }

        return $formattedCategories;
    }
}
