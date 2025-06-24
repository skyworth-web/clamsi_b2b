<?php

namespace App\Livewire\Onboard;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Onboard extends Component
{
    public function render()
    {
        
        // $system_settings = getSettings('system_settings', true, true);
        // $system_settings = json_decode($system_settings);
        // $authentication_method = $system_settings->authentication_method ?? "";
        // return view('livewire.' . config('constants.theme') . '.register-and-login.register', [
        //     'authentication_method' => $authentication_method
        // ])->title("Sign Up |");
        
        
      return view('livewire.elegant.onboard.onboard')->layout('livewire.blank');
    }
}