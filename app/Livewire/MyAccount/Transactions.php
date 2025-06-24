<?php

namespace App\Livewire\MyAccount;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Transactions extends Component
{
    public function render()
    {
        $user = Auth::user();
        return view('livewire.'.config('constants.theme').'.my-account.transactions',[
            'user_info' => $user,
        ])->title("Transactions |");
    }
}
