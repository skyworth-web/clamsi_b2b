<?php

namespace App\Livewire\Blogs;

use Livewire\Component;

class Details extends Component
{

    public $slug = "";

    public function mount($slug)
    {
        $this->slug = $slug;
    }

    public function render()
    {
        $store_id = session("store_id");
        $blog = fetchDetails('blogs', ['store_id' => $store_id, 'status' => 1, 'slug' => $this->slug], '*');
        if (count($blog) == 0) {
            abort(404);
            return;
        }
        $language_code = get_language_code();
        $bread_crumb = [
            'page_main_bread_crumb' => '<a wire:navigate href="' . customUrl('blogs') . '">' . labels('front_messages.blogs', 'Blogs') . '</a>',
            'right_breadcrumb' => array(
                getDynamicTranslation('blogs', 'title', $blog[0]->id, $language_code)
            )
        ];
        return view('livewire.' . config('constants.theme') . '.blogs.details',[
            'blog' => $blog,
            'bread_crumb' => $bread_crumb,
        ])->title(getDynamicTranslation('blogs', 'title', $blog[0]->id, $language_code) . ' Blogs |');
    }
}
