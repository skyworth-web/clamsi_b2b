<?php

namespace App\Livewire\Pages;

use App\Models\Faq as ModelsFaq;
use Livewire\Component;

class Faq extends Component
{
    public $search = "";
    public function render()
    {
        $res = [];
        if ($this->search != "") {
            $search_result = ModelsFaq::latest()
                ->when($this->search, function ($query) {
                    $query->where('status', "1")
                        ->where(function ($query) {
                            $query->orWhere('question', 'like', '%' . $this->search . '%')
                                ->orWhere('answer', 'like', '%' . $this->search . '%');
                        });
                });
            $res = $search_result->get()->toArray();
        }
        $faqs = fetchDetails('faqs', ['status' => "1"]);
        return view('livewire.' . config('constants.theme') . '.pages.faq', [
            'faqs' => $faqs,
            'search_result' => $res
        ])->title('FAQs | ');
    }
}