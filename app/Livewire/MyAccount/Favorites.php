<?php

namespace App\Livewire\MyAccount;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class Favorites extends Component
{
    protected $listeners = ['refreshComponent'];

    // public function render()
    // {
    //     $store_id = session('store_id');
    //     $user = Auth::user();

    //     $limit = request()->get('limit', 10);
    //     $offset = request()->get('offset', 0);
    //     $total = 0;

    //     // $res = getFavorites(user_id:$user->id,store_id:$store_id);
    //     $res = getFavorites(user_id: $user->id, limit: $limit, offset: $offset, store_id: $store_id);



    //     return view('livewire.' . config('constants.theme') . '.my-account.favorites', [
    //         'user_info' => $user,
    //         'regular_wishlist' => $res['regular_product'],
    //         'combo_wishlist' => $res['combo_products'],
    //     ])->title("Wishlist |");
    // }

    public function render()
    {
        $store_id = session('store_id');
        $user = Auth::user();
        // dd($user);
        if ($user != null) {
            $page = request()->get('page', 1);
            $perPage = 8;
            $offset = ($page - 1) * $perPage;

            $res = getFavorites(user_id: $user->id, limit: $perPage, offset: $offset, store_id: $store_id);
            $paginator = new LengthAwarePaginator(
                $res['regular_product'],
                $res['favorites_count'],
                $perPage,
                $page,
                ['path' => url()->current()]
            );

            return view('livewire.' . config('constants.theme') . '.my-account.favorites', [
                'user_info' => $user,
                'regular_wishlist' => $paginator->items(),
                'combo_wishlist' => $res['combo_products'],
                'favorites_count' => $res['favorites_count'],
                'links' => $paginator->links(),
            ])->title("Wishlist |");
        } else {
            abort(404);
        }
    }

    public function refreshComponent()
    {
        $this->dispatch('$refresh');
    }
}
