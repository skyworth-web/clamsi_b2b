<?php

namespace App\Livewire\Products;

use Livewire\Component;
use Illuminate\Http\Request;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class Details extends Component
{
    use WithFileUploads;

    protected $listeners = ['local_cart_data', 'city'];

    public $store_id;
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

    public $pincode = "";
    public $city = "";

    public $relative_products = [];

    public function mount($slug)
    {
        $filter['slug'] = $slug;


        $user_id =  $this->user_id;
        $this->store_id = session('store_id');
        $store_id = $this->store_id;
        // dd($store_id);
        $details = fetchProduct(user_id: $user_id, filter: $filter, is_detailed_data: 1, store_id: $store_id);
        if ($details['total'] == 0) {
            $this->redirect('products', true);
            return;
        }
        $this->product_id = $details['product'][0]->id;
        $product_ids = [$this->product_id];
        if (count($product_ids) >= 1) {
            $category_id = fetchDetails('products', ['id' => $this->product_id], '*');
            $categories_id = $category_id[0]->category_id ?? "";
            $brand_id = $category_id[0]->brand ?? "";
            $tags = $category_id[0]->tags ?? "";
            $relative_products_id = DB::table('products as p')
                ->select('p.id')
                ->join('categories as c', 'p.category_id', '=', 'c.id')
                ->join('brands as b', 'p.brand', '=', 'b.id')
                ->where(function ($query) use ($categories_id, $brand_id, $tags, $store_id) {
                    $query->where(function ($subQuery) use ($categories_id, $brand_id, $store_id) {
                        $subQuery->where('p.category_id', $categories_id)
                            ->orWhere('p.brand', $brand_id)
                            ->orWhere('p.store_id', $store_id);
                    });
                })
                ->whereNotIn('p.id', $product_ids)
                ->groupBy('p.id')
                ->limit(10)
                ->get();

            $relative_id = [];
            foreach ($relative_products_id as $i => $relative_product_id) {
                $relative_id[$i] = $relative_product_id->id;
            }
            $relative_product = fetchProduct($user_id, "", $relative_id);
            $this->relative_products = $relative_product['product'];
        }
        $this->product_details = $details['product'][0];
        $this->pname = $details['product'][0]->name;
        $this->pdescription = $details['product'][0]->short_description;
        $this->image = $details['product'][0]->image;
    }
    public function render()
    {
        $product_id = $this->product_id;

        $store_id = $this->store_id;

        $deliverabilitySettings = getDeliveryChargeSetting($store_id);

        if ($product_id != "") {
            $siblingsProduct = getPreviousAndNextItemWithId('products', $product_id, $store_id);
            $bread_crumb = [
                'page_main_bread_crumb' => '<a wire:navigate href="' . customUrl('products') . '">' . labels('front_messages.products', 'Products') . '</a>',
                'right_breadcrumb' => array(
                    '<a wire:navigate href="' . customUrl('products/' . $this->product_details->slug) . '">' . $this->pname . '</a>'
                )
            ];
        }
        $store_settings = getStoreSettings();
        $details_style = getProductDetailsStyle($store_settings);
        $language_code = get_language_code();
        // dd($details_style);
        return view($details_style, [
            'product_details' => $this->product_details,
            'relative_products' => $this->relative_products,
            'siblingsProduct' => $siblingsProduct,
            'product_id' => $product_id,
            'bread_crumb' => $bread_crumb,
            'deliverabilitySettings' => $deliverabilitySettings,
            'language_code' => $language_code,
        ])->layoutData([
            'title' => $this->pname . " |",
            'metaKeys' =>  $this->pname,
            'metaDescription' =>  $this->pdescription,
            'metaImage' => $this->image
        ]);
    }

    public function city($city)
    {
        $this->city = $city;
    }

    public function check_product_deliverability(Request $request)
    {
        $store_id = session('store_id');
        $deliverabilitySettings = getDeliveryChargeSetting($store_id);
        $validator = Validator::make(
            $request->all(),
            [
                'product_type' => 'required',
                'product_id' => 'required|exists:products,id'
            ]
        );
        if ($deliverabilitySettings[0]->product_deliverability_type == 'city_wise_deliverability') {
            $validator = Validator::make(
                $request->all(),
                [
                    'city' => 'required'
                ]
            );
            $request['pincode'] = null;
        } else {
            $validator = Validator::make(
                $request->all(),
                [
                    'pincode' => 'required'
                ]
            );
            $request['city'] = null;
        }
        if ($validator->fails()) {
            $errors = $validator->errors();
            $response['error'] = true;
            $response['message'] = $errors;
            return $response;
        }
        $pincode = $request['pincode'];
        $city = $request['city'];
        $city_id = "";
        $pincode_id = "";
        if ($deliverabilitySettings[0]->product_deliverability_type == 'city_wise_deliverability') {
            $city_id = fetchDetails('cities', ['name' => $city]);
            if ($city_id != []) {
                $city_id = $city_id[0]->id;
            }
        } else {
            $pincode_id = fetchDetails('zipcodes', ['zipcode' => $pincode]);
            if ($pincode_id != []) {
                $pincode_id = $pincode_id[0]->id;
            }
        }
        $product_id = $request['product_id'];
        $product_type = $request['product_type'];
        return checkProductDeliverable(product_id: $product_id, store_id: $store_id, city_id: $city_id, zipcode_id: $pincode_id, zipcode: $pincode, product_type: $product_type);
    }
}
