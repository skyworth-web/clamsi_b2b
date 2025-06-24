<?php

namespace App\Livewire\Sellers;

use App\Models\Store;
use Livewire\Component;
use Illuminate\Pagination\LengthAwarePaginator;



class Listing extends Component
{
    public function render(Store $store)
    {
        $store_id = session('store_id');
        $seller_listing = fetchDetails('seller_store', ['store_id' => $store_id]);

        // Initialize $sellers to avoid "Undefined variable" error
        $sellers = [
            'listing' => [],
            'links' => ''
        ];

        if (count($seller_listing) >= 1) {
            $total_products = count($seller_listing);
            $products = collect($seller_listing);
            $page = request()->get('page', 1);
            $perPage = 12;

            $paginator = new LengthAwarePaginator(
                $products->forPage((int) $page, (int) $perPage),
                $total_products,
                (int) $perPage,
                (int) $page,
                ['path' => url()->current()]
            );

            $sellers['listing'] = $paginator->items();
            $sellers['links'] = $paginator->links();
        }

        $bread_crumb['page_main_bread_crumb'] = '<a href="' . customUrl('sellers') . '">' . labels("front_messages.sellers", "Sellers") . '</a>';

        return view('livewire.' . config('constants.theme') . '.sellers.listing', [
            'Sellers' => $sellers,
            'bread_crumb' => $bread_crumb
        ])->title('Sellers | ');
    }

}
