<?php

namespace App\Livewire\Blogs;

use App\Models\Blog;
use Illuminate\Http\Request;
use Livewire\Component;
use Illuminate\Pagination\LengthAwarePaginator;

class Listing extends Component
{

    public $search = "";

    public function render(Request $request)
    {
        $store_id = session('store_id');
        $blogs = [];
        if ($this->search != "") {
            $search_blog_id = [];
            $search_result = Blog::latest()
                ->when($this->search, function ($query) {
                    $query->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%')
                        ->select('id');
                });
            $res = $search_result->get()->toArray();

            if (count($res) >= 1) {
                foreach ($res as $value) {
                    array_push($search_blog_id, $value['id']);
                }
                $blogs = fetchDetails('blogs', ['store_id' => $store_id, 'status' => 1], '*', where_in_key: "id", where_in_value: $search_blog_id);
            }
        } else {
            $blogs = fetchDetails('blogs', ['store_id' => $store_id, 'status' => 1], '*','50','0','id','DESC');
        }
        $blogs_count = count($blogs);
        $perPage = 9;
        if (isset($request->query()['perPage']) && ($request->query()['perPage'] != null)) {
            $perPage = $request->query()['perPage'];
        }
        if (count($blogs) >= 1) {
            $products = collect($blogs);
            $page = request()->get('page', 1);
            if (isset($page)) {
                $paginator = new LengthAwarePaginator(
                    $products->forPage((int)$page, (int)$perPage),
                    $blogs_count,
                    (int)$perPage,
                    (int)$page,
                    ['path' => url()->current()]
                );
            }
            $blogs['listing'] = $paginator->items();
            $blogs['links'] = $paginator->links();
        }

        return view('livewire.' . config('constants.theme') . '.blogs.listing', [
            'blogs' => $blogs,
            'perPage' => $perPage,
            'blogs_count' => $blogs_count,
        ])->title('Blogs |');
    }
}
