<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Attribute;
use Illuminate\Http\Request;
use App\Models\Attribute_values;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class AttributeController extends Controller
{
    public function index()
    {
        $attributes = Attribute::where('status', 1)->get();
        return view('admin.pages.forms.attributes', ['attributes' => $attributes]);
    }

    public function store(Request $request)
    {
        // dd($request);
        $store_id = getStoreId();

        $rules = [
            'attribute_value' => 'required',
            'category_id' => 'required|exists:categories,id',
        ];
        if ($request->attribute_id == 0) {
            $rules['name'] = 'required';
        } else {
            $rules['attribute_id'] = 'required|exists:attributes,id';
        }
        if ($validationResponse = validatePanelRequest($request, $rules)) {
            return $validationResponse;
        }
        $filteredValues = array_filter($request->attribute_value, fn($val) => !is_null($val) && $val !== '');

        if (empty($filteredValues)) {
            return response()->json([
                'error' => true,
                'error_message' => 'Please provide at least one valid attribute value.'
            ]);
        }
        if ($request->name != null) {
            $name = $request->name;
        } else {

            $name = fetchDetails('attributes', ['id' => $request->attribute_id], 'name');
            $name = isset($name[0]) ? $name[0]->name : '';
        }
        $attribute_data = [
            'name' => $name,
            'category_id' => $request->category_id,
            'status' => '1',
            'store_id' => $store_id,
        ];
        if (!isset($request->attribute_value) && $request->attribute_value == '') {
            return response()->json([
                'error' => true,
                'error_message' => labels('admin_labels.invalid_status_or_id_value', 'Please Enter Attribute Values')
            ]);
        }

        $result = DB::table('attributes')
            ->join('attribute_values', 'attributes.id', '=', 'attribute_values.attribute_id')
            ->where('attributes.category_id', $request->category_id)
            ->where('attributes.name', $name)
            ->whereIn('attribute_values.value', $request->attribute_value)
            ->select('attributes.id as attribute_id', 'attribute_values.id as attribute_value_id')
            ->first();

        $attribute = Attribute::where('name', '=', $name)->where('category_id', '=', $request->category_id)->first();


        if ($result === null) {

            if ($attribute === null) {
                $attribute =  Attribute::create($attribute_data);
            }
            for ($i = 0; $i < count($request->attribute_value); $i++) {
                $attribute_values_data = [
                    'value' => isset($request->attribute_value) ? $request->attribute_value[$i] : '',
                    'attribute_id' => $attribute->id,
                    'swatche_type' => $request->swatche_type[$i],
                    'swatche_value' => $request->swatche_value[$i],
                    'status' => '1',
                ];
                Attribute_values::insert($attribute_values_data);
            }

            if ($request->ajax()) {
                return response()->json(['error' => false, 'message' =>
                labels('admin_labels.attribute_added_successfully', 'Attribute added successfully'), 'addAttribute' => true]);
            }
        } else {
            if ($request->ajax()) {
                return response()->json([
                    'error' => true,
                    'message' => labels('admin_labels.combination_already_exist', 'Combination already exist')
                ], 422);
            }
        }
    }

    public function list()
    {
        $store_id = getStoreId();
        $search = trim(request('search'));
        $sort = request('sort', 'id');
        $order = request('order', 'DESC');
        $limit = request("limit", 10);
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;

        $attributes = Attribute::with('attribute_values', 'category')
            ->where('store_id', $store_id)
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('id', 'like', '%' . $search . '%')
                        ->orWhereHas('attribute_values', function ($q) use ($search) {
                            $q->where('value', 'like', '%' . $search . '%');
                        });
                });
            });

        $total = $attributes->count();

        $attributes = $attributes->orderBy($sort, $order)
            ->skip($offset)
            ->take($limit)
            ->get();

        $attributes = $attributes->map(function ($attribute) {
            $language_code = get_language_code();
            return [
                'id' => $attribute->id,
                'name' => $attribute->name,
                'value' => $attribute->attribute_values->pluck('value')->implode(','),
                'category' => getDynamicTranslation('categories', 'name', $attribute->category->id, $language_code) ?? "",
                'status' => '<select class="form-select status_dropdown change_toggle_status ' . ($attribute->status == 1 ? 'active_status' : 'inactive_status') . '" data-id="' . $attribute->id . '" data-url="/attribute/update_status/' . $attribute->id . '" aria-label="">
                <option value="1" ' . ($attribute->status == 1 ? 'selected' : '') . '>Active</option>
                <option value="0" ' . ($attribute->status == 0 ? 'selected' : '') . '>Deactive</option>
            </select>',
            ];
        });

        return response()->json([
            "rows" => $attributes,
            "total" => $total,
        ]);
    }
    public function update_status($id)
    {
        $attribute = Attribute::findOrFail($id);

        $attribute->status = $attribute->status == '1' ? '0' : '1';
        $attribute->save();
        return response()->json(['success' => labels('admin_labels.status_updated_successfully', 'Status updated successfully.')]);
    }

    public function edit($data)
    {

        $attribute_data = Attribute::where('id', $data)->with('attribute_values')->first();
        $attribute_values = $attribute_data->attribute_values->pluck('value')->implode(',');
        return view('partner.pages.update_attributes', [
            'attribute_data' => $attribute_data,
            'attribute_values' => $attribute_values
        ]);
    }

    public function update(Request $request, $data)
    {
        $attribute_data = $request->validate([
            'name' => 'required',
            'value' => 'required',
        ]);

        $attribute = Attribute::find($data);

        $attribute->update($attribute_data);

        return redirect('/partner/attributes')->with(
            'message',
            labels('admin_labels.attribute_updated_successfully', 'Attribute updated Successfully!')
        );
    }

    public function getAttributes(Request $request)
    {


        $attributes = DB::table('attribute_values as attr_val')
            ->select('attr_val.id', 'attr.name as attr_name', 'attr_val.value', 'attr.id as attr_id')
            ->join('attributes as attr', 'attr.id', '=', 'attr_val.attribute_id')
            ->where('attr.status', 1)
            ->where('attr.category_id', $request->category_id)
            ->get()->toArray();
        $attributes_refind = array();



        for ($i = 0; $i < count($attributes); $i++) {
            if (!array_key_exists($attributes[$i]->attr_name, $attributes_refind)) {

                $attributes_refind[$attributes[$i]->attr_name] = array();
                for ($j = 0; $j < count($attributes); $j++) {
                    if ($attributes[$i]->attr_name == $attributes[$j]->attr_name) {

                        $attributes_refind[$attributes[$j]->attr_name][$j]['id'] = $attributes[$j]->id ?? '';
                        $attributes_refind[$attributes[$j]->attr_name][$j]['text'] = $attributes[$j]->value ?? '';
                        $attributes_refind[$attributes[$j]->attr_name][$j]['data_values'] = $attributes[$j]->value ?? '';
                        $attributes_refind[$attributes[$j]->attr_name][$j]['attr_id'] = $attributes[$j]->attr_id ?? '';


                        $attributes_refind[$attributes[$j]->attr_name] = array_values($attributes_refind[$attributes[$j]->attr_name]);
                    }
                }
            }
        }

        if (!empty($attributes_refind)) {
            $response['error'] = false;
            $response['data'] = $attributes_refind;
        } else {
            $response['error'] = true;
            $response['data'] = [];
        }
        return $response;
    }
}
