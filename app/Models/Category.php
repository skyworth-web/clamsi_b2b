<?php

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = [
        'name',
        'store_id',
        'slug',
        'parent_id',
        'image',
        'banner',
        'status',
        'style',
        'row_order',
        'clicks'
    ];

    public static function getCategories()
    {
        return static::all();
    }

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($category) {
    //         $category->slug = Str::slug($category->name);
    //         $count = 1;
    //         while (static::whereSlug($category->slug)->exists()) {
    //             $category->slug = Str::slug($category->name) . '-' . $count++;
    //         }
    //     });
    // }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function get_category_list(Request $request, $id = null)
    {

        $hasChildOrItem = $request->input('has_child_or_item', 'true');
        $limit = $request->input('limit', null);
        $offset = $request->input('offset', null);
        $sort = $request->input('sort', 'row_order');
        $order = $request->input('order', 'ASC');
        $slug = $request->input('slug', '');

        $query = Category::query();

        if (!is_null($id)) {
            $query->where('id', $id);
        } else {
            $query->where('parent_id', 0)->where('status', $ignoreStatus ? 1 : 0);
        }

        if (!empty($slug)) {
            $query->where('slug', $slug);
        }

        if ($hasChildOrItem == 'false') {
            $query->leftJoin('categories as c2', 'c2.parent_id', '=', 'c1.id')
                ->leftJoin('products as p', 'p.category_id', '=', 'c1.id')
                ->where(function ($query) {
                    $query->orWhereRaw('c1.id = p.category_id')
                        ->orWhereRaw('c2.parent_id = c1.id');
                })
                ->groupBy('c1.id');
        }

        if (!is_null($limit) || !is_null($offset)) {
            $query->skip($offset)->take($limit);
        }

        $query->orderBy($sort, $order);

        $categories = $query->get();

        // You can format the response as needed here

        return response()->json(['categories' => $categories]);
    }
}
