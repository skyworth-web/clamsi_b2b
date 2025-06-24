<?php

namespace App\Livewire\Products;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ComboProductDetails extends Component
{
    use WithFileUploads;

    protected $listeners = ['local_cart_data'];

    public $user_id;
    public function __construct()
    {
        $this->user_id = Auth::user() != '' ? Auth::user()->id : NUll;
    }

    public $product_details;

    public $product_id = "";

    public $pname = "";

    public $pdescription = "";
    public $image = "";
    public $slug = "";

    public $relative_products = [];

    public function mount($slug)
    {
        $this->slug = $slug;
    }
    public function render()
    {
        $user_id =  $this->user_id;
        $filter['slug'] = $this->slug;
        $store_id = session('store_id');
        $details = fetchComboProduct(user_id: $user_id, filter: $filter, store_id: $store_id);
        if (count($details['combo_product']) < 1) {
            abort(404);
            return;
        }
        $this->product_id = $details['combo_product'][0]->id ?? "";

        if ($this->product_id != "") {
            $combo_product = fetchDetails('combo_products', ['id' => $this->product_id], 'product_ids')[0];
            $product_ids = explode(",", $combo_product->product_ids);

            $categories_and_relative_products = DB::table('products')
                ->select('category_id', 'id')
                ->whereIn('id', $product_ids)
                ->union(
                    DB::table('products')
                        ->select('category_id', 'id')
                        ->whereIn('category_id', function ($query) use ($product_ids) {
                            $query->from('products')
                                ->select('category_id')
                                ->whereIn('id', $product_ids)
                                ->distinct();
                        })
                        ->whereNotIn('id', $product_ids)
                )
                ->get();
            $relative_products_id = $categories_and_relative_products->whereNotIn('id', $product_ids)->pluck('id')->toArray();

            $relative_product = fetchProduct(user_id: $user_id, id: $relative_products_id, store_id:$store_id);
        }
        $this->product_details = $details['combo_product'][0];
        $this->pname = $details['combo_product'][0]->name;
        $this->pdescription = $details['combo_product'][0]->short_description;
        $this->image = $details['combo_product'][0]->image;
        if ($this->product_id != "") {
            $store_id = session('store_id');
            $siblingsProduct = getPreviousAndNextItemWithId('combo_products', $this->product_id, $store_id);
            $bread_crumb = [
                'page_main_bread_crumb' => '<a wire:navigate href="' . customUrl('combo-products') . '">'  .labels('front_messages.combo_products', 'Combo Products') .'</a>',
                'right_breadcrumb' => array(
                    '<a wire:navigate href="' . customUrl('combo-products/' . $this->product_details->slug) . '">' . $this->pname . '</a>'
                )
            ];
        }
        $deliverabilitySettings = getDeliveryChargeSetting($store_id);

        return view('livewire.' . config('constants.theme') . '.products.combo-details', [
        // return view('livewire.' . config('constants.theme') . '.products.combo-detailsStyleTwo', [
            'product_details' => $details['combo_product'][0],
            'relative_products' => $relative_product['product'],
            'siblingsProduct' => $siblingsProduct,
            'product_id' => $this->product_id,
            'bread_crumb' => $bread_crumb,
            'deliverabilitySettings' => $deliverabilitySettings,

        ])->layoutData([
            'title' => $this->pname . " |",
            'metaKeys' =>  $this->pname,
            'metaDescription' =>  $this->pdescription,
            'metaImage' => $this->image
        ]);
    }
}
