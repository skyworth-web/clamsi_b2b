<?php

namespace App\Http\Controllers\Seller;

use App\Models\User;
use App\Models\Attribute;
use Illuminate\Http\Request;
use App\Models\Attribute_values;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AttributeController extends Controller
{
    public function index()
    {
        $attributes = Attribute::where('status', 1)->get();
        return view('seller.pages.tables.attributes', ['attributes' => $attributes]);
    }

    public function list(Request $request)
    {
        $store_id = !empty($request->store_id) ? $request->store_id : getStoreId();
        $search = trim($request->search);
        $sort = $request->sort ?? 'id';
        $order = $request->order ?? 'DESC';
        $limit = $request->limit ?? 10;
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;

        // Check if attribute_value_ids and attribute_ids are present in the request
        $attribute_value_ids = $request->attribute_value_ids ? explode(',', $request->attribute_value_ids) : [];
        $attribute_ids = $request->attribute_ids ? explode(',', $request->attribute_ids) : [];

        // Fetch attributes with applied filters
        $attributes = Attribute::where('store_id', $store_id)->where('status', 1)
            ->with('attribute_values')
            ->when($search, function ($query) use ($search) {
                return $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%')
                    ->orWhereHas('attribute_values', function ($query) use ($search) {
                        $query->where('value', 'like', '%' . $search . '%');
                    });
            })
            ->when(!empty($attribute_ids), function ($query) use ($attribute_ids) {
                // Filter by attribute_ids if provided
                return $query->whereIn('id', $attribute_ids);
            })
            ->when(!empty($attribute_value_ids), function ($query) use ($attribute_value_ids) {
                // Filter by attribute_value_ids if provided
                return $query->whereHas('attribute_values', function ($query) use ($attribute_value_ids) {
                    $query->whereIn('id', $attribute_value_ids);
                });
            });

        // Get the total count before applying limit and offset
        $total = $attributes->count();

        // Apply sorting, pagination, and fetch the data
        $attributes = $attributes->orderBy($sort, $order)
            ->skip($offset)
            ->take($limit)
            ->get();

        // Format the attributes data
        $attributes = $attributes->map(function ($attribute) {
            $status = ($attribute->status == 1) ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Deactive</span>';
            return [
                'id' => $attribute->id,
                'name' => $attribute->name,
                'attribute_value_id' => $attribute->attribute_values->pluck('id')->implode(','),
                'value' => $attribute->attribute_values->pluck('value')->implode(','),
                'status' => $status,
                'status_code' => $attribute->status,
            ];
        });

        // Return the response with attributes and total count
        return response()->json([
            "rows" => $attributes,
            "total" => $total,
        ]);
    }

    public function getAttributes(Request $request)
    {

        $attributes = DB::table('attribute_values as attr_val')
            ->select('attr_val.id', 'attr.name as attr_name', 'attr_val.value')
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

    public function getAttributeValue(Request $request)
    {
        $store_id = !empty(request('store_id')) ? request('store_id') : getStoreId();
        $search = trim(request('search'));
        $attribute_id = request('attribute_id');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $limit = request("limit", 10);
        $offset = request("offset", 0);

        $multipleWhere = [];
        $where = array();
        if (!empty($search)) {
            $multipleWhere = [
                'a.name' => $search,
                'av.value' => $search,
                'av.swatche_value' => $search
            ];
        }
        if (isset($attribute_id) && !empty($attribute_id)) {
            $where = ['av.attribute_id' => $attribute_id];
        }
        $totalCount = DB::table('attribute_values as av')
            ->join('attributes as a', 'a.id', '=', 'av.attribute_id')
            ->where('a.store_id', $store_id)
            ->where(function ($query) use ($multipleWhere) {
                foreach ($multipleWhere as $column => $value) {
                    $query->orWhere($column, 'like', '%' . $value . '%');
                }
            })
            ->where($where)
            ->where('av.status', '=', 1)
            ->where('a.status', '=', 1)
            ->count();
        $attribute = DB::table('attribute_values as av')
            ->select('av.*', 'a.name as attribute_name')
            ->join('attributes as a', 'a.id', '=', 'av.attribute_id')
            ->where('a.store_id', $store_id)
            ->where(function ($query) use ($multipleWhere) {
                foreach ($multipleWhere as $column => $value) {
                    $query->orWhere($column, 'like', '%' . $value . '%');
                }
            })
            ->where($where)
            ->where('av.status', '=', 1)
            ->where('a.status', '=', 1)
            ->groupBy('av.id')
            ->orderBy($sort, $order)
            ->skip($offset)
            ->take($limit)
            ->get();

        $attribute = $attribute->toArray();

        $bulkData = [];

        $bulkData['error'] = empty($attribute);
        $bulkData['message'] = empty($attribute) ? labels('admin_labels.attributes_not_found', 'Attributes Not Found')
            :
            labels('admin_labels.attributes_retrieved_successfully', 'Attributes Retrieved Successfully');

        if (!empty($attribute)) {
            for ($i = 0; $i < count($attribute); $i++) {
                $attribute[$i] = $attribute[$i];
            }
        }
        foreach ($attribute as &$item) {
            unset($item->created_at);
            unset($item->updated_at);
        }
        unset($item);
        $bulkData['total'] = $totalCount;
        $bulkData['data'] = empty($attribute) ? [] : $attribute;

        return $bulkData;
    }
}
