<?php

namespace App\Http\Controllers\Admin;

use App\Models\OrderItems;
use App\Models\Product;
use App\Models\ProductRating;
use App\Models\Seller;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductRatingController extends Controller
{
    public function set_rating(Request $request, $files)
    {
        $data = $request->all();

        $rating = [
            'user_id' => $data['user_id'],
            'product_id' => $data['product_id'],
        ];

        if (isset($data['rating']) && !empty($data['rating'])) {
            $rating['rating'] = $data['rating'];
        }

        if (isset($data['comment']) && !empty($data['comment'])) {
            $rating['comment'] = $data['comment'];
        }
        if (isset($data['title']) && !empty($data['title'])) {
            $rating['title'] = $data['title'];
        }

        if ($files) {
            foreach ($files as $file) {
                if (is_array($file)) {
                    // If $file is an array, you need to iterate through its contents
                    foreach ($file as $f) {
                        $uploadedImage = $this->uploadFile($f);
                        $uploadedImages[] = $uploadedImage;
                    }
                } else {
                    // Handle the single file object
                    $uploadedImage = $this->uploadFile($file);
                    $uploadedImages[] = $uploadedImage;
                }
            }
        }

        $rating['images'] = isset($uploadedImages) && !empty($uploadedImages) ? json_encode($uploadedImages) : '';

        $existing_rating = ProductRating::where('user_id', $data['user_id'])
            ->where('product_id', $data['product_id'])
            ->first();

        if ($existing_rating) {
            $existing_rating->update($rating);
        } else {
            ProductRating::create($rating);
        }

        if (isset($data['rating']) && !empty($data['rating'])) {
            // Update product rating
            $product = Product::find($data['product_id']);
            $ratings = ProductRating::where('product_id', $data['product_id'])->count();
            $total_rating = ProductRating::where('product_id', $data['product_id'])->sum('rating');
            $new_rating = ($ratings > 0) ? round($total_rating / $ratings, 1, PHP_ROUND_HALF_UP) : 0;
            $product->update(['rating' => $new_rating, 'no_of_ratings' => $ratings]);

            // Update seller rating
            $store_id = $product->store_id;
            $seller_id = $product->seller_id;
            $seller_ratings = Product::where('seller_id', $seller_id)->where('rating', '>', 0)->count();

            $seller_total_rating = Product::where('seller_id', $seller_id)->sum('rating');
            $seller_new_rating = ($seller_ratings > 0) ? round($seller_total_rating / $seller_ratings, 1, PHP_ROUND_HALF_UP) : 0;

            DB::table('seller_store')
                ->where('seller_id', $seller_id)
                ->where('store_id', $store_id)
                ->update(['rating' => $seller_new_rating, 'no_of_ratings' => $seller_ratings]);
        }
        return true;
    }

    private function uploadFile($file)
    {
        $image_original_name = $file->getClientOriginalName();
        $image = Storage::disk('public')->putFileAs('review_images', $file, $image_original_name);
        return $image;
    }
    public function fetch_rating($product_id = '', $user_id = '', $limit = '', $offset = '', $sort = '', $order = '', $rating_id = '', $has_images = '', $rating = '', $count_empty_comments = false)
    {
        $query = DB::table('product_ratings')
            ->select(
                'product_ratings.*',
                'users.username as user_name',
                'users.image as user_profile'
            )
            ->leftJoin('users', 'users.id', '=', 'product_ratings.user_id');

        if (!empty($product_id)) {
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
        if (!empty($has_images) && $has_images == 1) {
            $query->whereNotNull('product_ratings.images')
                ->where('product_ratings.images', '!=', '');
        }

        $query->orderBy($sort, $order);
        $query->skip($offset)->take($limit);

        $rating_data = $query->get()->toArray();

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
        }

        $total_rating = ProductRating::selectRaw('count(product_ratings.id) as no_of_rating')
            ->where('product_id', $product_id)
            ->get()
            ->toArray();

        $total_images = ProductRating::selectRaw('ROUND(((LENGTH(`images`) - LENGTH(REPLACE(`images`, ",", ""))) / LENGTH(","))+1) as total')
            ->where('product_id', $product_id)
            ->get()
            ->toArray();

        $total_review_with_images = ProductRating::selectRaw('count(id) as total')
            ->where('product_id', $product_id)
            ->whereNotNull('images')
            ->get()
            ->toArray();

        $total_reviews = ProductRating::selectRaw('count(id) as total,
        sum(case when CEILING(rating) = 1 then 1 else 0 end) as rating_1,
        sum(case when CEILING(rating) = 2 then 1 else 0 end) as rating_2,
        sum(case when CEILING(rating) = 3 then 1 else 0 end) as rating_3,
        sum(case when CEILING(rating) = 4 then 1 else 0 end) as rating_4,
        sum(case when CEILING(rating) = 5 then 1 else 0 end) as rating_5')
            ->where('product_id', $product_id)
            ->get()
            ->toArray();

        // Count reviews with non-empty and non-null comments
        $no_of_reviews = 0;
        if ($count_empty_comments) {
            $no_of_reviews = ProductRating::where('product_id', $product_id)
                ->where(function ($query) {
                    $query->whereNotNull('comment')
                        ->where('comment', '!=', '');
                })
                ->count();
        }
        $res = [
            'total_images' => !empty($total_images) ? $total_images[0]['total'] : '',
            'total_reviews_with_images' => !empty($total_review_with_images) ? $total_review_with_images[0]['total'] : '',
            'no_of_rating' => !empty($total_rating) ? $total_rating[0]['no_of_rating'] : '',
            'total_reviews' => !empty($rating_data) ? count($rating_data) : '',
            'star_1' => !empty($total_reviews) ? $total_reviews[0]['rating_1'] : '',
            'star_2' => !empty($total_reviews) ? $total_reviews[0]['rating_2'] : '',
            'star_3' => !empty($total_reviews) ? $total_reviews[0]['rating_3'] : '',
            'star_4' => !empty($total_reviews) ? $total_reviews[0]['rating_4'] : '',
            'star_5' => !empty($total_reviews) ? $total_reviews[0]['rating_5'] : '',
            'product_rating' => $rating_data,
            'no_of_reviews' => $no_of_reviews
        ];

        return $res;
    }


    public function delete_rating($rating_id)
    {

        $rating_id = (int) $rating_id;
        $rating_details = ProductRating::find($rating_id);

        if ($rating_details) {
            $images = json_decode($rating_details->images, true);

            if (!empty($images)) {
                foreach ($images as $image) {
                    Storage::disk('public')->delete($image);
                    ;
                }
            }

            $rating_details->delete();

            $product = Product::find($rating_details->product_id);

            if ($product) {
                $product_ratings = ProductRating::selectRaw('count(rating) as no_of_ratings, sum(rating) as sum_of_rating')
                    ->where('product_id', $product->id)
                    ->first();

                $no_of_rating = $product_ratings->no_of_ratings;
                $total_rating = $product_ratings->sum_of_rating;

                $newrating = ($no_of_rating > 0) ? round($total_rating / $no_of_rating, 1, PHP_ROUND_HALF_UP) : 0;

                $product->update(['rating' => $newrating, 'no_of_ratings' => $no_of_rating]);

                $seller_rating = Product::selectRaw('count(rating) as no_of_ratings, sum(rating) as sum_of_rating')
                    ->where('seller_id', $product->seller_id)
                    ->where('rating', '>', 0)
                    ->first();

                $no_of_ratings_seller = $seller_rating->no_of_ratings;
                $total_rating_seller = $seller_rating->sum_of_rating;

                $new_rating_seller = ($no_of_ratings_seller > 0) ? round($total_rating_seller / $no_of_ratings_seller, 1, PHP_ROUND_HALF_UP) : 0;

                Seller::where('user_id', $product->seller_id)
                    ->update(['rating' => $new_rating_seller, 'no_of_ratings' => $no_of_ratings_seller]);
            }
            return true;
        } else {
            return false;
        }
    }
}
