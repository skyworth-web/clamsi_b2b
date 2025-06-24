<?php

namespace App\Http\Controllers;

use App\Models\Brand;

class BrandController extends Controller
{
    public function getBrands($id = NULL, $limit = '', $offset = '', $sort = 'id', $order = 'ASC',$store_id = "")
    {
        $where = null;
        if (isset($store_id) && !empty($store_id)) {
            $where = ['b.store_id' => $store_id];
        }
        $query = Brand::from('brands as b')
            ->select('b.id as brand_id', 'b.name as brand_name', 'b.slug as brand_slug', 'b.image as brand_img','b.store_id as store_id')
            ->leftJoin('products as p', 'p.brand', '=', 'b.name')
            ->where('b.status','1')
            ->where($where)
            ->groupBy('b.id');

        if (!empty($limit) || !empty($offset)) {
            $query->offset($offset)->limit($limit);
        }
        $query->orderBy('b.id', $order);

        $brands = $query->get();
        $brands->each(function ($brand) {
            $language_code = get_language_code();

            $brand->brand_img = dynamic_image(getMediaImageUrl($brand->brand_img),400);
            $brand->brand_name = getDynamicTranslation('brands', 'name', $brand->brand_id, $language_code);;
        });

        $count_res = Brand::from('brands as b')->count();

        return [
            'brands' => json_decode(json_encode($brands), true),
            'total' => $count_res,
        ];
    }
}
