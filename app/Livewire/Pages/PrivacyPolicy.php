<?php

namespace App\Livewire\Pages;

use Livewire\Component;

class PrivacyPolicy extends Component
{
    public function render()
    {
        $privacy_policy = json_decode(getSettings('privacy_policy',true,true), true);
        $data = $privacy_policy['privacy_policy'];

        return view('livewire.'.config('constants.theme').'.pages.privacy-policy',[
            'privacy_policy' => $data
        ])->title("Privacy Policy |");
    }
}
