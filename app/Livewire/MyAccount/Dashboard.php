<?php

namespace App\Livewire\MyAccount;

use App\Models\Address;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function mount()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
    }

    public function render()
    {
        $user = Auth::user();

        $default_address = Address::where('is_default', '1')
            ->where('user_id', $user->id)
            ->get();

        return view('livewire.' . config('constants.theme') . '.my-account.dashboard', [
            'user_info' => $user,
            'default_address' => $default_address
        ])->title("Dashboard |");
    }
}
