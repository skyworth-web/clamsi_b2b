<?php

namespace App\Livewire\MyAccount;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class LiveChat extends Component
{
    public function render()
    {
        $user = Auth::user();
        return view('livewire.' . config('constants.theme') . '.my-account.live-chat', [
            'user_info' => $user,
        ])->title("Wishlist |");
    }
}
