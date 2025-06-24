<?php

namespace App\Livewire\MyAccount;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Wallet extends Component
{
    protected $listeners = ['refreshComponent'];

    public function render()
    {
        $user = Auth::user();
        $user = $this->get_user_info();
        $payment_method = getSettings('payment_method', true,true);
        $payment_method = json_decode($payment_method);
        return view('livewire.' . config('constants.theme') . '.my-account.wallet', [
            'user_info' => $user,
            'payment_method' => $payment_method,
        ])->title("Wallet |");
    }

    public function get_user_info()
    {
        return Auth::user();
    }

    public function refreshComponent()
    {
        $this->dispatch('$refresh');
    }
}
