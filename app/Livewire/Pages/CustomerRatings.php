<?php

namespace App\Livewire\Pages;

use Livewire\Component;
use App\Models\ProductRating;
use Livewire\WithFileUploads;
use App\Models\ComboProductRating;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CustomerRatings extends Component
{
    use WithFileUploads;

    protected $listeners = ['updateRating'];

    public $user_id;
    public function __construct()
    {
        $this->user_id = Auth::user() != '' ? Auth::user()->id : NUll;
    }

    public $product_details;

    public $product_id = "";

    public $product_type = "";

    public $review_id;

    public $comment = "";

    public $title = "";

    public $images = [];

    public $rating;

    public $is_disabled = false;

    public function render()
    {
        $product_details = $this->product_details;
        $this->product_type = $product_details->type;
        if ($product_details->type == "combo-product") {
            $user_review = fetchDetails('combo_product_ratings', ['user_id' => $this->user_id, "product_id" => $product_details->id]);
        } else {
            $user_review = fetchDetails('product_ratings', ['user_id' => $this->user_id, "product_id" => $product_details->id]);
        }
        $this->review_id = $user_review[0]->id ?? "";
        $this->product_id = $product_details->id ?? "";

        if (isset($this->review_id) && !empty($this->review_id)) {
            $this->rating = (isset($user_review[0]->rating)) ? $user_review[0]->rating : "";

            $this->comment = (isset($user_review[0]->comment)) ? $user_review[0]->comment : "";

            $this->title = (isset($user_review[0]->title)) ? $user_review[0]->title : "";
        }

        $product_ratings = $this->getProductRating($this->product_id, $product_details->type);
        foreach ($product_ratings as $key => $ratings) {
            $user_profile = fetchDetails('users', ['id' => $ratings->user_id], ['image', 'username']);
            $product_ratings[$key]->user_profile = $user_profile[$key]->image ?? "";
            $product_ratings[$key]->user_name = $user_profile[$key]->username ?? "";
        }

        $sortedReviews = $this->sortReviews($product_ratings, $this->user_id);
        $sortedReviews = array_slice($sortedReviews, 0, 3);
        return view('components.utility.others.customer-ratings', [
            'customer_reviews' => $sortedReviews,
            'product_details' => $this->product_details
        ]);
    }

    public function sortReviews($reviews, $UserId)
    {
        $sortedReviews = [];
        foreach ($reviews as $review) {
            if ($review->user_id == $UserId) {
                array_unshift($sortedReviews, $review);
            } else {
                $sortedReviews[] = $review;
            }
        }
        return $sortedReviews;
    }

    public function getProductRating($product_id, $type)
    {
        if ($type == 'combo-product') {
            $product_ratings = fetchDetails('combo_product_ratings', ['product_id' => $product_id]);
        } else {
            $product_ratings = fetchDetails('product_ratings', ['product_id' => $product_id]);
        }
        return $product_ratings;
    }

    public function updateRating($update_rating)
    {
        $this->rating = $update_rating;
    }

    public function save_review()
    {
        if ($this->is_disabled == false) {
            $validator = Validator::make(
                [
                    'rating' => $this->rating,
                    'title' => $this->title,
                    'comment' => $this->comment,
                    'images.*' => $this->images,
                ],
                [
                    'rating' => 'required',
                    'title' => 'required',
                    'comment' => 'required',
                    'images.*' => 'image|max:2048'
                ],
                [
                    'comment' => 'Please Write a Review'
                ]
            );
            if ($validator->fails()) {
                $errors = $validator->errors();
                $this->dispatch('validationErrorshow',['data' => $errors]);
                $response['error'] = true;
                $response['message'] = $errors;
                return $response;
            }
            $images = [];
            foreach ($this->images as $key => $image) {
                $imageName = 'image_' . time() . '_' . $key . '.' . $image->getClientOriginalExtension();
                $review_image = $image->storeAs('review_image', $imageName, 'public');
                array_push($images, $review_image);
            }
            $validated['product_id'] = $this->product_id;
            $validated['title'] = $this->title;
            $validated['rating'] = $this->rating;
            $validated['comment'] = $this->comment;
            $validated['user_id'] = Auth::user()->id;

            if ($this->review_id) {
                if ($this->product_type == "combo-product") {
                    $existingReview = ComboProductRating::findOrFail($this->review_id);
                } else {
                    $existingReview = ProductRating::findOrFail($this->review_id);
                }
                if (empty($images)) {
                    $validated['images'] = $existingReview['images'];
                } else {
                    $validated['images'] = $images;
                    $existingReview['images'] = json_decode($existingReview['images']);
                    foreach ($existingReview['images'] as $existingImage) {
                        if (Storage::exists("public/" . $existingImage)) {
                            Storage::delete("public/" . $existingImage);
                        }
                    }
                }
                $existingReview->update($validated);
                $this->dispatch('showSuccess', 'The review has been successfully updated.');
            } else {
                $validated['images'] = json_encode($images);
                if ($this->product_type == "combo-product") {
                    ComboProductRating::create($validated);
                } else {
                    ProductRating::create($validated);
                }
                $this->dispatch('showSuccess', 'The review has been successfully added.');
                $this->is_disabled = true;
            }
            if ($this->product_type == "combo-product") {
                $averageRating = ComboProductRating::where(["product_id" => $this->product_id])->avg('rating');
            } else {
                $averageRating = ProductRating::where(["product_id" => $this->product_id])->avg('rating');
            }
            $ratingUpdate = [
                'rating' => $averageRating
            ];
            if (!$this->review_id) {
                $ratingUpdate['no_of_ratings'] = DB::raw('no_of_ratings + 1');
            }
            if ($this->product_type == "combo-product") {
                updateDetails($ratingUpdate, ['id' => $validated['product_id']], 'combo_products');
            } else {
                updateDetails($ratingUpdate, ['id' => $validated['product_id']], 'products');
            }
            return;
        }
    }

    public function delete_rating()
    {
        if ($this->review_id) {
            if ($this->product_type == "combo-product") {
                $existingReview = ComboProductRating::findOrFail($this->review_id);
            } else {
                $existingReview = ProductRating::findOrFail($this->review_id);
            }

            $delete = $existingReview->delete();
            $this->dispatch('showSuccess', 'The review has been successfully removed.');
            $this->is_disabled = false;
            if ($this->product_type == "combo-product") {
                $averageRating = ComboProductRating::where(["product_id" => $this->product_id])->avg('rating');
            } else {
                $averageRating = ProductRating::where(["product_id" => $this->product_id])->avg('rating');
            }
            $ratingUpdate = [
                'no_of_ratings' => DB::raw('no_of_ratings - 1'),
                'rating' => $averageRating
            ];
            if ($this->product_type == "combo-product") {
                $update = updateDetails($ratingUpdate, ['id' => $this->product_id], 'combo_products');
            } else {
                $update = updateDetails($ratingUpdate, ['id' => $this->product_id], 'products');
            }
        }
    }
}
