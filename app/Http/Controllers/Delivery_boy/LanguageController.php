<?php

namespace App\Http\Controllers\Delivery_boy;

use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

class LanguageController extends Controller
{
    public function change(Request $request)
    {

        $request->validate([
            'lang' => 'required|string|max:255',
        ]);

        $is_rtl = fetchdetails('languages', ['code' => $request->lang], 'is_rtl');
        $is_rtl = isset($is_rtl) && !empty($is_rtl) ? $is_rtl[0]->is_rtl : '';

        app()->setLocale($request->lang);

        session()->put('locale', $request->lang);
        session()->put('is_rtl', $is_rtl);

        return redirect()->back();
    }

    public function setLanguage($locale)
    {
        config(['app.locale' => $locale]);
        session()->put('locale', $locale);

        return redirect()->back();
    }


    function list()
    {
        DB::enableQueryLog();
        $search = trim(request('search'));
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $limit = (request('limit')) ? request('limit') : 5;
        $pageNumber = request('offset') / $limit + 1;


        $languages = language::query()->when($search, function ($query) use ($search) {
            return $query->where('name', 'like', '%' . $search . '%')
                ->orWhere('id', 'like', '%' . $search . '%')
                ->orWhere('code', 'like', '%' . $search . '%');
        })
            ->orderBy($sort, $order)
            ->paginate($limit, ['*'], 'page', $pageNumber);

        $languages->transform(function ($item) {
            $item['delete'] = $item['code'] == 'en' ?
                "" :
                '<button type="button" class="btn btn-danger btn-sm delete-button" data-id="' . $item['id'] . '">
                    <i class="fa fa-trash"> </i>
                </button>';
            return $item;
        });


        return response()->json([
            "rows" => $languages->items(),
            'total' => $languages->total()
        ]);
    }
}
