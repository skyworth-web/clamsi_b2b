<?php

namespace App\Livewire\Header;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\CategoryController;

class Header extends Component
{
    protected $listeners = ['cart_count', 'changeLang', 'changeCurrency'];

    public $cart_count = "";

    public $user_id = "";
    public function __construct()
    {
        $this->user_id = Auth::user() != '' ? Auth::user()->id : NUll;
    }

    public function cart_count($cart_count)
    {
        $this->cart_count = $cart_count;
    }

    public function mount()
    {
        $store_id = session('store_id');
        if ($store_id == "") {
            abort(503);
        }
    }

    public function render()
    {
        $settings = getSettings('web_settings', true, true);
        $settings = json_decode($settings);


        $currencies = fetchDetails('currencies', ['status' => 1]) ?? [];

        $languages = fetchDetails('languages') ?? [];

        $store_id = session('store_id');

        $store_details = fetchDetails('stores', ['status' => 1], '*');
        $categoryController = app(CategoryController::class);
        $categories = $categoryController->getCategories(sort: 'row_order', order: "ASC", store_id: $store_id);
        $categories = $categories->original;
        $store_settings = getStoreSettings();
        $header_style = getHeaderStyle($store_settings);
        return view($header_style, [
            'settings' => $settings,
            'currencies' => $currencies,
            'languages' => $languages,
            'stores' => $store_details,
            'categories' => $categories,
        ]);
    }

    public function changeLang($lang)
    {
        if ($lang != "") {
            $is_rtl = fetchdetails('languages', ['code' => $lang], 'is_rtl');
            $is_rtl = isset($is_rtl) && !empty($is_rtl) ? $is_rtl[0]->is_rtl : '';
            app()->setLocale($lang);
            session()->put('locale', $lang);
            session()->put('is_rtl', $is_rtl);
            return $this->redirect(" ", true);
        }
    }
    public function changeCurrency($currency)
    {
        if ($currency != "") {
            session()->put('currency', $currency);
            return $this->redirect(" ", true);
        }
    }
}
