<?php

namespace App\Livewire\Pages;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ModelCart extends Component
{
    protected $listeners = ['refreshComponent'];

    public $user_id;
    public function __construct()
    {
        $this->user_id = Auth::user() != '' ? Auth::user()->id : NUll;
    }
    public $variant_id = "";
    public $qty = "";
    public $product_type = "";
    public $store_id = "";
    public $cart_count = "";
    public function render()
    {
        $store_id = session('store_id');
        $this->store_id = $store_id;
        $cart_data = $this->get_user_cart($this->user_id, $store_id);
        $this->cart_count = (count($cart_data) >= 1) ? count($cart_data['cart_items']) : "";
        return view('components.utility.others.model-cart', [
            'cart_data' => $cart_data
        ]);
    }

    public function refreshComponent()
    {
        $this->dispatch('$refresh');
    }

    public function get_user_cart($user_id, $store_id)
    {
        $cart_details = getCartTotal($user_id, false, 0, "", $store_id);
        return $cart_details;
    }

    public function remove_from_cart($id)
    {
        $cart_item = fetchDetails('cart',['product_variant_id'=> $id],'product_type');
        $data = [
            'variant_id'=>$id,
            'store_id'=>$this->store_id,
            'user_id'=>$this->user_id,
            'cart_count'=>$this->cart_count,
        ];
        if ($cart_item != []) {
            $data['product_type'] = $cart_item[0]->product_type;
        }
        $this->dispatch('remove_from_cart', data: $data);
    }
}
