<?php

namespace App\Http\Controllers\Seller;

use App\Models\OrderItems;
use App\Models\ComboProduct;
use App\Models\ComboProductRating;
use App\Models\Seller;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComboProductRatingController extends Controller
{
    public function fetch_rating($product_id = '', $user_id = '', $limit = '', $offset = '', $sort = '', $order = '', $rating_id = '', $has_images = '', $rating = '')
    {
        if (!empty($product_id)) {
            $query = DB::table('combo_product_ratings')
                ->select(
                    'combo_product_ratings.*',
                    'users.username as user_name',
                    'users.image as user_profile'
                )
                ->leftJoin('users', 'users.id', '=', 'combo_product_ratings.user_id');
            $query->where('product_id', $product_id);
        }

        if (!empty($user_id)) {
            $query->where('user_id', $user_id);
        }

        if (!empty($rating_id)) {
            $query->where('id', $rating_id);
        }
        if (!empty($rating)) {
            $query->where('rating', $rating);
        }
        $query->orderBy($sort, $order);

        $query->skip($offset)->take($limit);

        if (

            !empty($has_images) && $has_images == 1
        ) {
            $query->whereNotNull('combo_product_ratings.images');
        }

        $combo_product_ratings = DB::table('combo_product_ratings')
            ->select(
                'combo_product_ratings.*',
                'users.username as user_name',
                'users.image as user_profile'
            )
            ->leftJoin('users', 'users.id', '=', 'combo_product_ratings.user_id');
        if (!empty($product_id)) {
            $combo_product_ratings->where('product_id', $product_id);
        }
        if (!empty($rating)) {
            $combo_product_ratings->where('rating', $rating);
        }

        if (!empty($user_id)) {
            $combo_product_ratings->where('user_id', $user_id);
        }

        if (!empty($rating_id)) {
            $combo_product_ratings->where('id', $rating_id);
        }

        $combo_product_ratings->orderBy($sort, $order);

        $combo_product_ratings->skip($offset)->take($limit);


        $rating_data =   $combo_product_ratings->get()->toArray();

        foreach ($rating_data as $rating) {

            if (($rating->images) != null) {
                $images = json_decode($rating->images, true);
                $images = array_map(function ($image) {
                    return asset('storage/' . $image);
                }, $images);
                $rating->images = $images;
            } else {
                $rating->images = [];
            }

            if (!empty($rating->user_profile)) {
                $rating->user_profile = asset(config('constants.USER_IMG_PATH') . $rating->user_profile);
            }

            $rating;
        }

        $total_rating = ComboProductRating::selectRaw('count(combo_product_ratings.id) as no_of_rating')
            ->join('users as u', 'u.id', '=', 'combo_product_ratings.user_id')
            ->where('product_id', $product_id)
            ->get()
            ->toArray();

        $total_images = ComboProductRating::selectRaw('ROUND(((LENGTH(`images`) - LENGTH(REPLACE(`images`, ",", ""))) / LENGTH(","))+1) as total')
            ->where('product_id', $product_id)
            ->get()
            ->toArray();

        $total_review_with_images = ComboProductRating::selectRaw('count(id) as total')
            ->where('product_id', $product_id)
            ->whereNotNull('images')
            ->get()
            ->toArray();

        $total_reviews = ComboProductRating::selectRaw('count(id) as total,
        sum(case when CEILING(rating) = 1 then 1 else 0 end) as rating_1,
        sum(case when CEILING(rating) = 2 then 1 else 0 end) as rating_2,
        sum(case when CEILING(rating) = 3 then 1 else 0 end) as rating_3,
        sum(case when CEILING(rating) = 4 then 1 else 0 end) as rating_4,
        sum(case when CEILING(rating) = 5 then 1 else 0 end) as rating_5')
            ->where('product_id', $product_id)
            ->get()
            ->toArray();

        if ($total_images != []) {
            $res['total_images'] = $total_rating[0]['no_of_rating'];
        }
        $res['total_images'] = !empty($total_rating) ? $total_rating[0]['no_of_rating'] : '';
        $res['total_images'] = !empty($total_images) ? $total_images[0]['total'] : '';
        $res['total_reviews_with_images'] = !empty($total_review_with_images) ? $total_review_with_images[0]['total'] : '';
        $res['no_of_rating'] = !empty($total_rating) ? $total_rating[0]['no_of_rating'] : '';
        $res['total_reviews'] = !empty($total_reviews) ? $total_reviews[0]['total'] : '';
        $res['star_1'] = !empty($total_reviews) ? $total_reviews[0]['rating_1'] : '';
        $res['star_2'] = !empty($total_reviews) ? $total_reviews[0]['rating_2'] : '';
        $res['star_3'] = !empty($total_reviews) ? $total_reviews[0]['rating_3'] : '';
        $res['star_4'] = !empty($total_reviews) ? $total_reviews[0]['rating_4'] : '';
        $res['star_5'] = !empty($total_reviews) ? $total_reviews[0]['rating_5'] : '';
        $res['product_rating'] = $rating_data;

        return $res;
    }

}
