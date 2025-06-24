<?php

namespace App\Livewire\Header;

use App\Models\Product;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\CategoryController;
use App\Models\ComboProduct;

class SearchProduct extends Component
{
    public $search = "";
    public $user_id = "";
    public function __construct()
    {
        $this->user_id = Auth::user() != '' ? Auth::user()->id : NUll;
    }

    public function render()
    {
        $store_id = session('store_id');
        $search_products = [];
        $combo_search_products = [];

        if ($this->search !== "") {
            $search_pro_id = [];
            $search_result = Product::latest()
                ->when($this->search, function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('tags', 'like', '%' . $this->search . '%')
                        ->orWhere('short_description', 'like', '%' . $this->search . '%')
                        ->select('id');
                });
            $res = $search_result->get()->toArray();
            if (count($res) >= 1) {
                foreach ($res as $value) {
                    array_push($search_pro_id, $value['id']);
                }
                $search_products = fetchProduct(user_id: $this->user_id, id: $search_pro_id, store_id: $store_id);
                $search_products = $search_products['product'];
            }

            // combo product search
            $search_combo_product_id = [];
            $combo_search_result = ComboProduct::latest()
                ->when($this->search, function ($query) {
                    $query->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('tags', 'like', '%' . $this->search . '%')
                        ->orWhere('short_description', 'like', '%' . $this->search . '%')
                        ->select('id');
                });
            $combo_res = $combo_search_result->get()->toArray();
            if (count($combo_res) >= 1) {
                foreach ($combo_res as $data) {
                    array_push($search_combo_product_id, $data['id']);
                }
                $combo_search_products = fetchComboProduct(user_id: $this->user_id, id: $search_combo_product_id, limit: 10, store_id: $store_id);
                $combo_search_products = $combo_search_products['combo_product'];
            }
        }
        $categoryController = app(CategoryController::class);
        $topCategories = $categoryController->getCategories(null, "3", "", 'row_order', "DESC", 'true', "", "", "", $store_id);
        $topCategories = $topCategories->original['categories'];
        return view('components.header.search-product', [
            'search_products' => $search_products,
            'combo_search_products' => $combo_search_products,
            'topCategories' => $topCategories,
        ]);
    }
}
