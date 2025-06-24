<?php

namespace App\Livewire\Cart;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AddressController;

class Checkout extends Component
{
    protected $listeners = ['refreshComponent', 'get_selected_address', 'get_selected_promo', 'is_wallet_use'];
    public $user_id = "";
    public function __construct()
    {
        $this->user_id = Auth::user() != '' ? Auth::user()->id : NUll;
    }
    public $store_id = "";
    public $product_type = "";
    public $cart_count = "";
    public $selected_address_id = "";
    public $selected_address = "";
    public $selected_promo_code = "";
    public $is_wallet_use = false;
    public $wallet_used_balance = "";

    public function mount()
    {
        $store_id = session('store_id');
        $this->store_id = $store_id;
        $cart_data = $this->get_user_cart($this->user_id, $store_id);
        if (count($cart_data) < 1) {
            return $this->redirect(customUrl('/'), true);
        }
    }

    public function render()
    {
        $store_id = $this->store_id;
        $addressController = app(AddressController::class);
        $addresses = $addressController->getAddress($this->user_id);
        $default_address = [];
        if (!empty($addresses)) {
            if (isset($this->selected_address_id) && !empty($this->selected_address_id)) {
                $default_address = $this->selected_address;
            } else {
                $default_address = array_values(array_filter($addresses, function ($item) {
                    return $item->is_default == 1;
                }));
            }
            if (empty($default_address)) {
                $default_address = $addresses;
            }
        }

        $user_details = fetchUsers($this->user_id);
        $wallet_balance = $user_details['balance'];

        $promo_codes = getPromoCodes(store_id: $store_id);
        $cart_data = $this->get_user_cart($this->user_id, $store_id, ($default_address[0]->id ?? ""));
        if (count($cart_data) < 1) {
            return $this->redirect(customUrl('/'), true);
        }
        $final_total = $cart_data['overall_amount'];
        if (isset($this->selected_promo_code) && !empty($this->selected_promo_code)) {
            $is_promo_valid = validatePromoCode($this->selected_promo_code, $this->user_id, $final_total, 1);
            if ($is_promo_valid->original['error'] == false) {
                $is_promo_valid->original['data'][0]->final_discount = currentCurrencyPrice($is_promo_valid->original['data'][0]->final_discount, true);
                $final_total = $is_promo_valid->original['data'][0]->final_total;
                $this->dispatch('validate_promo_code', is_promo_valid: $is_promo_valid->original);
            } else {
                $this->dispatch('validate_promo_code', is_promo_valid: $is_promo_valid->original);
            }
        }
        if ($this->is_wallet_use == true) {
            $wallet_balance = $wallet_balance - $final_total;
            if ($wallet_balance <= 0) {
                if ($wallet_balance < 0) {
                    $this->wallet_used_balance =  $final_total + $wallet_balance;
                    $final_total = - ($wallet_balance);
                } else {
                    $this->wallet_used_balance = $final_total;
                    $final_total = 0;
                }
                $wallet_balance = 0;
            } else {
                $this->wallet_used_balance = $final_total;
                $final_total = 0;
            }
        }
        $this->cart_count = (count($cart_data) >= 1) ? count($cart_data['cart_items']) : "";
        $this->store_id = $store_id;
        $bread_crumb = [
            'page_main_bread_crumb' => '<a wire:navigate href="' . customUrl('cart') . '">' . labels('front_messages.cart', 'Cart') . '</a>',
            'right_breadcrumb' => array(labels('front_messages.checkout', 'Checkout'))
        ];

        $pincode = $default_address[0]->pincode ?? "";
        $zipcode = fetchDetails('zipcodes', ['zipcode' => $pincode], 'id');
        $zipcode_id = (!empty($zipcode) ? $zipcode[0]->id : "");

        $city = $default_address[0]->city ?? "";
        $city_id = $default_address[0]->city_id ?? "";
        // dd($city_id);
        $settings = getDeliveryChargeSetting($store_id);
        $product_availability = "";
        // dd($city_id);
        if (isset($settings[0]->product_deliverability_type) && !empty($settings[0]->product_deliverability_type)) {
            if ($settings[0]->product_deliverability_type == 'city_wise_deliverability') {
                $product_availability = checkCartProductsDeliverable($this->user_id, '', '', $store_id, $city, $city_id);
                // dd($product_availability);
            } else {
                // dd($zipcode_id);
                $product_availability = checkCartProductsDeliverable($this->user_id, $pincode, $zipcode_id, $store_id);
                // dd($product_availability);
            }
        }
        // dd($product_availability);
        $time_slot_config = getSettings('time_slot_config', true, true);
        $time_slot_config = json_decode($time_slot_config);
        $time_slots = fetchDetails('time_slots', ['status' => 1]);

        $payment_method = getSettings('payment_method', true, true);
        $payment_method = json_decode($payment_method);
        return view('livewire.' . config('constants.theme') . '.cart.checkout', [
            'cart_data' => $cart_data,
            'final_total' => $final_total,
            'product_availability' => $product_availability,
            'addresses' => $addresses,
            'promo_codes' => $promo_codes,
            'wallet_balance' => $wallet_balance,
            'default_address' => $default_address,
            'bread_crumb' => $bread_crumb,
            'time_slot_config' => $time_slot_config,
            'time_slots' => $time_slots,
            'payment_method' => $payment_method,
            'user_details' => $user_details,
        ])->title('Checkout |');
    }

    public function get_user_cart($user_id, $store_id, $address_id = "")
    {
        $cart_data = getCartTotal($user_id, false, 0, $address_id, $store_id);
        return $cart_data;
    }

    public function get_selected_address($address_id)
    {
        $this->selected_address_id = $address_id;
        $addressController = app(AddressController::class);
        $selected_address = $addressController->getAddress($this->user_id, $address_id);
        $this->selected_address = $selected_address;
    }

    public function get_selected_promo($promo_code)
    {
        // dd($promo_code);
        $this->selected_promo_code = $promo_code;
    }
    public function is_wallet_use($is_wallet_use)
    {
        $this->is_wallet_use = $is_wallet_use;
    }

    public function validatePromoCode($promo_code, $user_id, $final_total)
    {
        $validate_promo = validatePromoCode($promo_code, $user_id, $final_total);
        return $validate_promo;
    }

    public function refreshComponent()
    {
        $this->dispatch('$refresh');
    }

    public function remove_from_cart($id)
    {
        $data = [
            'variant_id' => $id,
            'product_type' => $this->product_type,
            'store_id' => $this->store_id,
            'user_id' => $this->user_id,
            'cart_count' => $this->cart_count,
        ];
        $this->dispatch('remove_from_cart', data: $data);
    }
}