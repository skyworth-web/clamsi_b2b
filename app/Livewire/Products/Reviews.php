<?php

namespace App\Livewire\Products;

use Livewire\Component;

class Reviews extends Component
{
    public $product_id = "";
    public function mount($slug)
    {
        $product_id = fetchDetails('products',['slug'=> $slug], 'id');
        $this->product_id = $product_id[0]->id;
    }

    public function render()
    {
        $reviews = fetchDetails('product_ratings', ['product_id' => $this->product_id]);
        foreach ($reviews as $key => $ratings) {
            $user_profile = fetchDetails('users', ['id' => $ratings->user_id], ['image', 'username']);
            $reviews[$key]->user_profile = $user_profile[$key]->image ?? "frontend/elegant/user-profile-icon.jpg";
            $reviews[$key]->user_name = $user_profile[$key]->username ?? "";
        }
        return view('livewire.' . config('constants.theme') . '.products.reviews', [
            'customer_reviews' => $reviews
        ]);
    }
}
