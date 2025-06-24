<?php

namespace App\Livewire\Pages;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class QuickviewModel extends Component
{

    protected $listeners = ['quick_view', 'clear_quickview_modal'];

    public $product_id = "";

    public $product_type = "";

    public $user_id = "";

    public function __construct()
    {
        $this->user_id = (Auth::user() != null) ? Auth::user()->id : "";
    }

    public function render()
    {
        $store_id = session('store_id');
        $product = [];
        if (!empty($this->product_id)) {
            if ($this->product_type == "combo-product") {
                $product = fetchComboProduct(user_id:$this->user_id,id:$this->product_id,store_id:$store_id);
                if (count($product) >= 1) {
                    $product = $product['combo_product'];
                }
            } else {
                $product = fetchProduct(user_id:$this->user_id,id:$this->product_id,store_id:$store_id,is_detailed_data:1);
                if (count($product) >= 1) {
                    $product = $product['product'];
                }
            }
            $this->dehydrate();
        }
        $language_code = get_language_code();
        return view('components.utility.others.quickview-model', [
            'product' => $product,
            'language_code'=>$language_code,
        ]);
    }

    public function quick_view($id,$product_type)
    {
        $this->product_id = $id;
        $this->product_type = $product_type;
    }

    public function dehydrate()
    {
        $this->dispatch('quickview');
    }

    public function clear_quickview_modal()
    {
        $this->product_id = "";
        $this->dispatch('$refresh');
    }
}
