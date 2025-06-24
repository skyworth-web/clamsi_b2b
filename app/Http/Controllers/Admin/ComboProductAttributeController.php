<?php

namespace App\Http\Controllers\Admin;

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

        return view('admin.pages.forms.combo_attributes');
    }



    public function store(Request $request)
    {

        $store_id = getStoreId();

        $rules = [
            'name' => 'required',
            'value' => 'required',
        ];

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }


        $attribute_data = [
            'name' => $request->name,
        ];

        $attribute_data['store_id'] = $store_id;
        $attribute_data['status'] = 1;

        // Check if the attribute name already exists
        $attribute = ComboProductAttribute::where('name', $request->name)->first();

        if ($attribute === null) {
            $attribute = ComboProductAttribute::create($attribute_data);
        }

        // Check for existing attribute values and add new ones
        $newValues = $request->value;
        $newValues = json_decode($newValues, true);
        foreach ($newValues as $newValue) {

            $existingValue = ComboProductAttributeValue::where('value', $newValue['value'])
                ->where('combo_product_attribute_id', $attribute->id)
                ->first();

            if ($existingValue === null) {
                $attributeValuesData = [
                    'value' => $newValue['value'],
                    'combo_product_attribute_id' => $attribute->id,
                    'store_id' => $store_id
                ];

                ComboProductAttributeValue::create($attributeValuesData);
            } else {
                if ($request->ajax()) {
                    return response()->json([
                        'error_message' => labels('admin_labels.combination_already_exists', 'Combination already exists')
                    ]);
                }
            }
        }
        if ($request->ajax()) {
            return response()->json([
                'message' => labels('admin_labels.attribute_added_successfully', 'Attribute added successfully.')
            ]);
        }
    }

    public function list()
    {
        $store_id = getStoreId();

        $search = trim(request('search'));
        $sort = request('sort') ?: 'id';
        $order = request('order') ?: 'DESC';
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = request('limit', 10);

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
                return [
                    'id' => $attribute->id,
                    'name' => $attribute->name,
                    'value' => $attribute->attribute_values->pluck('value')->implode(','),
                    'status' => '<select class="form-select status_dropdown change_toggle_status ' . ($attribute->status == 1 ? 'active_status' : 'inactive_status') . '" data-id="' . $attribute->id . '" data-url="/admin/combo_product_attributes/update_status/' . $attribute->id . '" aria-label="">
                  <option value="1" ' . ($attribute->status == 1 ? 'selected' : '') . '>Active</option>
                  <option value="0" ' . ($attribute->status == 0 ? 'selected' : '') . '>Deactive</option>
              </select>',
                    'operate' => ' <div class="d-flex align-items-center">
            <a class="single_action_button" href="' . $edit_url . '"><i class="bx bx-pencil mx-2"></i></a>
        </div>'
                ];
            });

        return response()->json([
            "rows" => $attributes,
            "total" => $total,
        ]);
    }


    public function update_status($id)
    {
        $plan = ComboProductAttribute::findOrFail($id);
        $plan->status = $plan->status == '1' ? '0' : '1';
        $plan->save();
        return response()->json(['success' => labels('admin_labels.status_updated_successfully', 'Status updated successfully.')]);
    }

    public function edit($data)
    {

        $store_id = getStoreId();

        $attribute_data = ComboProductAttribute::where('id', $data)->where('store_id', $store_id)->with('attribute_values')->first();

        if ($attribute_data === null || empty($attribute_data)) {
            return view('admin.pages.views.no_data_found');
        } else {
            $attribute_values = $attribute_data->attribute_values->pluck('value')->implode(',');
            return view('admin.pages.forms.update_combo_attributes', [
                'attribute_data' => $attribute_data,
                'attribute_values' => $attribute_values
            ]);
        }
    }

    public function update(Request $request, $data)
    {

        $attribute_data = $request->validate([
            'name' => 'required',
            'value' => 'required',
        ]);

        $attribute = ComboProductAttribute::find($data);

        $attribute_data = [
            'name' => $request->name,
        ];

        $attribute->update($attribute_data);


        $newValues = $request->value;
        $newValues = json_decode($newValues, true);
        foreach ($newValues as $newValue) {

            $existingValue = ComboProductAttributeValue::where('value', $newValue['value'])
                ->where('combo_product_attribute_id', $attribute->id)
                ->first();

            if ($existingValue === null) {
                $attributeValuesData = [
                    'value' => $newValue['value'],
                    'combo_product_attribute_id' => $attribute->id,
                ];

                $attribute->update($attribute_data);
            }
        }
        if ($request->ajax()) {
            return response()->json([
                'message' => labels('admin_labels.attribute_updated_successfully', 'Attribute updated Successfully!'),
                'location' => route('admin.combo_product_attributes.index')
            ]);
        }
    }
}
