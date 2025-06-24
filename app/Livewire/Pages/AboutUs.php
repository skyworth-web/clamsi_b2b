<?php

namespace App\Livewire\Pages;

use Livewire\Component;

class AboutUs extends Component
{
    public function render()
    {
        $about_us = getSettings('about_us',true,true);
        $about_us = json_decode($about_us);
        $about_us = $about_us->about_us;

        $settings = getSettings('web_settings',true,true);
        $settings = json_decode($settings);
        return view('livewire.'.config('constants.theme').'.pages.about-us',[
            "about_us" => $about_us,
            "settings" => $settings,
        ])->title("About Us |");
    }
}
