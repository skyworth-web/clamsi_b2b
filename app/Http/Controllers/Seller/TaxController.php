<?php

namespace App\Http\Controllers\Seller;

use App\Models\Tax;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\HtmlString;

class TaxController extends Controller
{
    public function index()
    {
        return view('seller.pages.tables.tax');
    }

    public function list()
    {
        $search = trim(request('search'));
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = (request('limit')) ? request('limit') : "10";

        $taxes = Tax::when($search, function ($query) use ($search) {
            return $query->where('title', 'like', '%' . $search . '%');
        });

        $total = $taxes->count();
        $language_code = get_language_code();
        $taxes = $taxes->orderBy($sort, $order)
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(function ($t)  use ($language_code) {
                $status = ($t->status == 1) ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Deactive</span>';
                return [
                    'id' => $t->id,
                    'title' => getDynamicTranslation('taxes', 'title', $t->id, $language_code),
                    'percentage' => $t->percentage,
                    'status' => $status,
                ];
            });

        return response()->json([
            "rows" => $taxes,
            "total" => $total,
        ]);
    }

    public function getTaxes(Request $request)
    {
        $search = trim($request->search) ?? "";
        $taxes = Tax::where('title', 'like', '%' . $search . '%')->where('status', 1)->get();
        $language_code = get_language_code();
        $data = array();
        foreach ($taxes as $tax) {
            $data[] = array("id" => $tax->id, "text" => getDynamicTranslation('taxes', 'title', $tax->id, $language_code) . ' (' . $tax->percentage . '%)');
        }
        return response()->json($data);
    }
}
