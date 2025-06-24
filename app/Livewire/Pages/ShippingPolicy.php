<?php

namespace App\Livewire\Pages;

use Livewire\Component;

class ShippingPolicy extends Component
{
    public function render()
    {
        $shipping_policy = json_decode(getSettings('shipping_policy',true,true), true);
        $data = $shipping_policy['shipping_policy'];

        return view('livewire.'.config('constants.theme').'.pages.shipping-policy',[
            'shipping_policy' => $data
        ])->title("Shipping Policy |");
    }
}
