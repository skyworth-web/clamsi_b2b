<?php

namespace App\Livewire\Brands;

use Livewire\Component;

class Listing extends Component
{
    public function render()
    {
        $store_id = session('store_id');
        $brands = fetchDetails('brands',['store_id' => $store_id,'status'=>'1']);
        $bread_crumb = 'brands';
        return view('livewire.'.config('constants.theme').'.brands.listing',[
            'brands' => $brands,
            'breadcrumb' => $bread_crumb
        ])->title("Brands |");
    }
}
