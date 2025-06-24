<?php

namespace App\Livewire\Cart;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Listing extends Component
{
    protected $listeners = ['refreshComponent'];
    public $user_id = "";
    public function __construct()
    {
        $this->user_id = Auth::user() != '' ? Auth::user()->id : NUll;
    }
    public $store_id = "";
    public $product_type = "";
    public $cart_count = "";

    public function render()
    {
        $store_id = session('store_id');
        $this->store_id = $store_id;
        $cart_data = $this->get_user_cart($this->user_id, $store_id);
        // dd($cart_data['cart_items']);
        $this->cart_count = (count($cart_data) >= 1) ? count($cart_data['cart_items']) : "";
        $cart_product_types = [];
        $product_ids = [];
        $related_product = [];
        if (count($cart_data) >= 1) {
            foreach ($cart_data['cart_items'] as $cart_item) {
                array_push($cart_product_types, $cart_item['cart_product_type']);
                array_push($product_ids, $cart_item['product_id']);
            }
            $related_product = $this->related_product($product_ids, $cart_product_types, $this->user_id, $store_id);
        }
        $save_for_later = $this->get_user_cart($this->user_id, $store_id, 1);
        if (count($save_for_later) >= 1) {
            $save_for_later['cart_count'] = count($save_for_later['cart_items']);
            $save_for_later['heading'] = [
                'title' => 'Save For Later',
                'short_description' => $save_for_later['cart_count'] . ' Items in Save for Later',
            ];
        }

        $related_product_heading = [
            'title' => labels('front_messages.related_products', 'Related Products'),
            'short_description' => labels('front_messages.products_you_may_like', 'Products You May Like'),
        ];
        $language_code = get_language_code();
        $bread_crumb = [
            'page_main_bread_crumb' => labels('front_messages.cart', 'Cart'),

        ];
        return view('livewire.' . config('constants.theme') . '.cart.view-cart', [
            'cart_data' => $cart_data,
            'related_product_heading' => $related_product_heading,
            'save_for_later' => $save_for_later,
            'related_product' => $related_product,
            'bread_crumb' => $bread_crumb,
            'language_code' => $language_code,
        ])->title('Product Cart |');
    }

    // public function move_to_cart($id)
    // {
    //     updateDetails(['is_saved_for_later' => 0], ['product_variant_id' => $id], 'cart');
    // }


    public function move_to_cart($id)
    {
        $store_id = session('store_id');
        $user_id = $this->user_id;

        $cart_item = DB::table('cart')
            ->where('product_variant_id', $id)
            ->where('user_id', $user_id)
            ->where('store_id', $store_id)
            ->where('is_saved_for_later', 1)
            ->first();

        if (!$cart_item) {
            $this->dispatch('cart-updated', message: 'Cart item not found.');
            return;
        }

        // Get moving product type
        $moving_product_type = DB::table('product_variants')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->where('product_variants.id', $cart_item->product_variant_id)
            ->value('products.type');

        // Get existing cart items types
        $cart_items = DB::table('cart')
            ->join('product_variants', 'cart.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->where('cart.user_id', $user_id)
            ->where('cart.store_id', $store_id)
            ->where('cart.is_saved_for_later', 0)
            ->pluck('products.type')
            ->toArray();

        $cart_items[] = $moving_product_type;

        if (count(array_unique($cart_items)) > 1) {
            $this->dispatch('cart-updated', message: 'You can only add either digital or physical product.');
            return;
        }

        updateDetails(['is_saved_for_later' => 0], ['product_variant_id' => $id], 'cart');

        // $this->dispatch('cart-updated', message: 'Product moved to cart successfully.');
    }


    public function save_for_later($id)
    {
        updateDetails(['is_saved_for_later' => 1], ['product_variant_id' => $id], 'cart');
    }

    public function get_user_cart($user_id, $store_id, $save_later = 0)
    {
        $cart_data = getCartTotal($user_id, false, $save_later, "", $store_id);
        return $cart_data;
    }

    public function related_product($product_ids, $cart_product_type, $user_id, $store_id)
    {
        $categories_id = [];

        foreach ($product_ids as $i => $product_id) {
            if ($cart_product_type[$i] == 'regular') {
                $category_id = fetchDetails('products', ['id' => $product_id], 'category_id');
                $categories_id[$i] = $category_id[0]->category_id ?? '';
            }
        }
        $relative_product = [];
        if (count($categories_id) >= 1) {
            $relative_products_id = DB::table('products')
                ->select('id')
                ->where('category_id', $store_id)
                ->where('store_id', $categories_id)
                ->whereNotIn('id', $product_ids)
                ->get();
            $relative_id = [];
            foreach ($relative_products_id as $i => $relative_product_id) {
                $relative_id[$i] = $relative_product_id->id;
            }
            $relative_product = fetchProduct($user_id, "", $relative_id, '', '', '', '', '', '', '', '', '', $store_id);
        }
        return $relative_product;
    }

    public function refreshComponent()
    {
        $this->dispatch('$refresh');
    }

    public function remove_from_cart($id)
    {
        $cart_item = fetchDetails('cart', ['product_variant_id' => $id], 'product_type');
        $data = [
            'variant_id' => $id,
            'store_id' => $this->store_id,
            'user_id' => $this->user_id,
            'cart_count' => $this->cart_count,
        ];
        if ($cart_item != []) {
            $data['product_type'] = $cart_item[0]->product_type;
        }
        $this->dispatch('remove_from_cart', data: $data);
    }
}