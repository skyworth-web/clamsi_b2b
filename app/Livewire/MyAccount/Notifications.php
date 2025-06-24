<?php

namespace App\Livewire\MyAccount;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Notifications extends Component
{
    public function render()
    {
        $user = Auth::user();
        return view('livewire.'.config('constants.theme').'.my-account.notifications',[
            'user_info' => $user
        ])->title("Notifications |");
    }
}
