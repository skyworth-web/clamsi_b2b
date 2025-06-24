<?php

namespace App\Livewire\Footer;

use Livewire\Component;

class Footer extends Component
{

    public function render()
    {
        $settings = getSettings('web_settings',true,true);
        $settings = json_decode($settings);
        return view('components.footer.footer',[
            'settings'=>$settings,
        ]);
    }
}
