<?php

namespace App\Livewire\Pages;

use Livewire\Component;

class TermAndConditions extends Component
{
    public function render()
    {
        $terms_and_conditions = json_decode(getSettings('terms_and_conditions', true, true), true);
        $data = $terms_and_conditions['terms_and_conditions'];

        return view('livewire.' . config('constants.theme') . '.pages.term-and-conditions', [
            'terms_and_conditions' => $data
        ])->title("Terms & Conditions |");
    }
}
