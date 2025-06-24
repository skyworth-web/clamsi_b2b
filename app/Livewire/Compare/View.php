<?php

namespace App\Livewire\Compare;

use Illuminate\Http\Request;
use Livewire\Component;

class View extends Component
{
    public function render()
    {
        $bread_crumb = [
            'page_main_bread_crumb' => labels('front_messages.compare', 'Compare'),
        ];
        return view('livewire.' . config('constants.theme') . '.compare.view', [
            'bread_crumb' => $bread_crumb
        ])->title("Compare Items |");
    }

    public function add_to_compare(Request $request)
    {
        // dd($request);
        $store_id = session('store_id');
        // $obj = json_decode(($request['product_id']));
        $obj = $request['product_id'];
        $combo_product_id = [];
        $regular_product_id = [];
        $language_code = get_language_code();
        foreach ($obj as $data) {
            // dd($data);
            if ($data['product_type'] == "combo") {
                array_push($combo_product_id, $data['product_id']);
            } else {
                array_push($regular_product_id, $data['product_id']);
            }
        }
        $products = [];
        if (count($regular_product_id) >= 1) {
            $products = fetchProduct(id: $regular_product_id, store_id: $store_id);
            if ($products['total'] >= 1) {
                $products = $products['product'];
                foreach ($products as $key => $product) {
                    // dd(currentCurrencyPrice($product->min_max_price['max_price']),true);
                    $products[$key]->image = dynamic_image($product->image, 150);
                    $products[$key]->category_name = getDynamicTranslation('categories', 'name', $products[$key]->category_id, $language_code);
                    $products[$key]->brand_name = getDynamicTranslation('brands', 'name', $products[$key]->brand, $language_code) ?? "";
                    $products[$key]->min_max_price['max_price'] = currentCurrencyPrice($product->min_max_price['max_price']);
                    $products[$key]->min_max_price['special_min_price'] = currentCurrencyPrice($product->min_max_price['special_min_price'], true);
                }
            }
        }
        $combo_products = [];
        if (count($combo_product_id) >= 1) {
            $combo_products = fetchComboProduct(id: $combo_product_id, store_id: $store_id);
            if ($combo_products['total'] >= 1) {
                $combo_products = $combo_products['combo_product'];
                foreach ($combo_products as $key => $combo_product) {
                    $combo_products[$key]->image = dynamic_image($combo_product->image, 150);
                    $combo_products[$key]->price = currentCurrencyPrice($combo_product->price);
                    $combo_products[$key]->special_price = currentCurrencyPrice($combo_product->special_price);
                }
            }
        }
        $data = [
            'regular_product' => $products,
            'combo_products' => $combo_products,
        ];
        $response['error'] = false;
        $response['message'] = 'Compare Product Added Successfully';
        $response['data'] = (!empty($data)) ? $data : [];
        return $response;
    }
}
