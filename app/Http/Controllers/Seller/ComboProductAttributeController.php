<?php

namespace App\Http\Controllers\Seller;

use App\Models\ComboProductAttribute;
use App\Models\ComboProductAttributeValue;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

class ComboProductAttributeController extends Controller
{
    public function index()
    {
        $store_id = getStoreId();

        return view('seller.pages.tables.combo_attributes');
    }


    public function list()
    {

        $store_id = getStoreId();


        $search = trim(request()->input('search'));
        $sort = request()->input('sort', 'id');
        $order = request()->input('order', 'DESC');
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = request()->input('limit', 10);


        $attributes = ComboProductAttribute::with('attribute_values')
            ->where('store_id', $store_id);


        if ($search) {
            $attributes->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%')
                    ->orWhereHas('attribute_values', function ($query) use ($search) {
                        $query->where('value', 'like', '%' . $search . '%');
                    });
            });
        }


        $total = $attributes->count();


        $attributes = $attributes->orderBy($sort, $order)
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(function ($attribute) {

                $edit_url = route('admin.combo_product_attributes.update', $attribute->id);


                $status = '<a class="form-switch change_toggle_status" data-id=' . $attribute->id . ' data-toggle-status=' . $attribute->status . ' data-url="/admin/combo_product_attributes/update_status/' . $attribute->id . '">';
                $status .= '<input class="form-check-input" type="checkbox" role="switch" ' . ($attribute->status == 1 ? 'checked' : '') . '></a>';


                return [
                    'id' => $attribute->id,
                    'name' => $attribute->name,
                    'value' => $attribute->attribute_values->pluck('value')->implode(','),
                    'status' => $status,
                    'action' => '<div class="dropdown bootstrap-table-dropdown">
                    <a href="#" class="text-dark" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-ellipsis-v"></i>
                    </a>
                    <div class="dropdown-menu table_dropdown attribute_action_dropdown" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item" href="' . $edit_url . '"><i class="bx bx-pencil"></i> Edit</a>
                    </div>
                </div>'
                ];
            });


        return response()->json([
            "rows" => $attributes,
            "total" => $total,
        ]);
    }


}
