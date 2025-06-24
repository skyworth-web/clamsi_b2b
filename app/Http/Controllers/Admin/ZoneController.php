<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Models\City;
use App\Models\Language;
use App\Models\Slider;
use App\Models\Zipcode;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;


class ZoneController extends Controller
{
    public function index()
    {
        $languages = Language::all();
        return view('admin.pages.forms.zones', ['languages' => $languages]);
    }
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'zipcode_group' => 'required|array',
            'zipcode_group.*.serviceable_zipcode_id' => 'required|numeric',
            'zipcode_group.*.zipcode_delivery_charge' => 'required|numeric|min:0',
            'city_group' => 'required|array',
            'city_group.*.serviceable_city_id' => 'required|numeric',
            'city_group.*.city_delivery_charge' => 'required|numeric|min:0',
        ];
        $messages = [
            'name.required' => 'The zone name is required.',
            'zipcode_group.required' => 'Please select serviceable zipcodes.',
            'zipcode_group.*.serviceable_zipcode_id.required' => 'Please select a valid serviceable zipcode.',
            'zipcode_group.*.zipcode_delivery_charge.required' => 'Please provide a valid delivery charge for the zipcode.',
            'city_group.required' => 'Please select serviceable cities.',
            'city_group.*.serviceable_city_id.required' => 'Please select a valid serviceable city.',
            'city_group.*.city_delivery_charge.required' => 'Please provide a valid delivery charge for the city.',
        ];

        $validationResponse = validatePanelRequest($request, $rules, $messages);

        if ($validationResponse !== null) {
            return $validationResponse;
        }

        $zipcode_ids = collect($request->zipcode_group)->pluck('serviceable_zipcode_id')->implode(',');
        $city_ids = collect($request->city_group)->pluck('serviceable_city_id')->implode(',');

        $existing_zone = Zone::where('serviceable_zipcode_ids', $zipcode_ids)
            ->where('serviceable_city_ids', $city_ids)
            ->first();

        if ($existing_zone) {
            return response()->json([
                'error' => true,
                'message' => 'A zone with the same serviceable cities and zipcodes already exists as ' . $existing_zone->name,
            ], 422);
        }
        $translations = [
            'en' => $request->name
        ];
        if (!empty($request['translated_zone_name'])) {
            $translations = array_merge($translations, $request['translated_zone_name']);
        }

        $data['name'] = json_encode($translations, JSON_UNESCAPED_UNICODE);
        $data['serviceable_zipcode_ids'] = $zipcode_ids;
        $data['serviceable_city_ids'] = $city_ids;
        $data['status'] = 1;

        Zone::create($data);

        foreach ($request->zipcode_group as $zipcode) {
            Zipcode::where('id', $zipcode['serviceable_zipcode_id'])
                ->update(['delivery_charges' => $zipcode['zipcode_delivery_charge']]);
        }

        foreach ($request->city_group as $city) {
            City::where('id', $city['serviceable_city_id'])
                ->update(['delivery_charges' => $city['city_delivery_charge']]);
        }

        if ($request->ajax()) {
            return response()->json([
                'message' => labels('admin_labels.zone_created_successfully', 'Zone created successfully'),
                'error' => false
            ]);
        }
    }

    public function list(Request $request)
    {
        $search = trim($request->input('search'));
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'DESC');
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = $request->input('limit', 50);

        $zones_data = Zone::when($search, function ($query) use ($search) {
            return $query->where('name', 'like', '%' . $search . '%');
        });

        $total = $zones_data->count();
        $zones = $zones_data
            ->orderBy($sort, $order)
            ->offset($offset)
            ->limit($limit)
            ->get();
        $language_code = get_language_code();
        $zones = $zones->map(function ($z) use ($language_code) {
            $delete_url = route('admin.zones.destroy', $z->id);
            $edit_url = route('admin.zones.edit', $z->id);
            $status = '<select class="form-select status_dropdown change_toggle_status ' . ($z->status == 1 ? 'active_status' : 'inactive_status') . '" data-id="' . $z->id . '" data-url="/admin/zones/update_status/' . $z->id . '" aria-label="">
            <option value="1" ' . ($z->status == 1 ? 'selected' : '') . '>Active</option>
            <option value="0" ' . ($z->status == 0 ? 'selected' : '') . '>Deactive</option>
        </select>';
            $action = '<div class="dropdown bootstrap-table-dropdown">
                <a href="#" class="text-dark" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="bx bx-dots-horizontal-rounded"></i>
                </a>
                <div class="dropdown-menu table_dropdown order_action_dropdown" aria-labelledby="dropdownMenuButton">
                <a class="dropdown-item dropdown_menu_items" href="' . $edit_url . '"><i class="bx bx-pencil mx-2"></i>Edit</a>
                    <a class="dropdown-item delete-data dropdown_menu_items" data-url="' . $delete_url . '"><i class="bx bx-trash mx-2"></i>Delete</a>
                </div>
            </div>';
            $z->action = $action;
            $z->name = getDynamicTranslation('zones', 'name', $z->id, $language_code);
            $z->status = $status;
            $z->serviceable_zipcodes = $this->get_zipcodes_by_ids($z->serviceable_zipcode_ids);
            $z->serviceable_cities = $this->get_cities_by_ids($z->serviceable_city_ids, $language_code);

            return $z;
        });

        return response()->json([
            "rows" => $zones,
            "total" => $total,
        ]);
    }
    public function get_zipcodes_by_ids($zipcode_ids)
    {
        $ids_array = explode(',', $zipcode_ids);
        $zipcodes = Zipcode::whereIn('id', $ids_array)->get();
        $zipcodes_array = $zipcodes->pluck('zipcode')->toArray();
        $comma_seprated_zipcodes = implode(',', $zipcodes_array);

        return $comma_seprated_zipcodes;
    }
    public function get_cities_by_ids($city_ids, $language_code)
    {
        $ids_array = explode(',', $city_ids);

        $cities = City::whereIn('id', $ids_array)->get();

        $translated_names = [];

        foreach ($cities as $city) {
            $translated_name = getDynamicTranslation('cities', 'name', $city->id, $language_code);
            $translated_names[] = $translated_name;
        }

        $comma_seprated_cities = implode(',', $translated_names);

        return $comma_seprated_cities;
    }

    public function edit($id)
    {
        $zone = Zone::select('id', 'name', 'serviceable_zipcode_ids', 'serviceable_city_ids')
            ->where('id', $id)
            ->first();
        $languages = Language::all();
        $zipcode_ids = explode(',', $zone->serviceable_zipcode_ids);
        $city_ids = explode(',', $zone->serviceable_city_ids);

        $zipcodes = Zipcode::whereIn('id', $zipcode_ids)
            ->get(['id', 'zipcode', 'delivery_charges']);

        $cities = City::whereIn('id', $city_ids)
            ->get(['id', 'name', 'delivery_charges']);

        $all_zipcodes = Zipcode::orderBy('id', 'desc')->get();

        $all_cities = City::orderBy('id', 'desc')->get();
        $language_code = get_language_code();

        if (request()->ajax()) {
            return response()->json([
                'zone' => $zone,
                'zipcodes' => $zipcodes,
                'cities' => $cities,
                'all_zipcodes' => $all_zipcodes,
                'all_cities' => $all_cities,
            ]);
        }

        return view('admin.pages.forms.update_zone', compact('zone', 'zipcodes', 'cities', 'all_zipcodes', 'all_cities', 'languages', 'language_code'));
    }

    public function update(Request $request, $id)
    {
        $zone_data = Zone::find($id);

        if (!$zone_data) {
            return response()->json(['error' => true, 'message' => labels('admin_labels.data_not_found', 'Data Not Found')], 404);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:zones,name,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ], 422);
        }
        $existingTranslations = json_decode($zone_data->name, true) ?? [];

        $existingTranslations['en'] = $request->name;

        if (!empty($request->translated_zone_name)) {
            $existingTranslations = array_merge($existingTranslations, $request->translated_zone_name);
        }

        // Encode updated translations to store as JSON
        $data['name'] = json_encode($existingTranslations, JSON_UNESCAPED_UNICODE);


        if (isset($request->zipcode_group) && is_array($request->zipcode_group)) {
            $serviceable_zipcode_ids = [];
            foreach ($request->zipcode_group as $zipcode) {
                $serviceable_zipcode_ids[] = $zipcode['serviceable_zipcode_id'];

                Zipcode::where('id', $zipcode['serviceable_zipcode_id'])
                    ->update(['delivery_charges' => $zipcode['zipcode_delivery_charge']]);
            }

            $data['serviceable_zipcode_ids'] = implode(',', $serviceable_zipcode_ids);
        } else {
            $data['serviceable_zipcode_ids'] = $zone_data->serviceable_zipcode_ids;
        }

        if (isset($request->city_group) && is_array($request->city_group)) {
            $serviceable_city_ids = [];
            foreach ($request->city_group as $city) {
                $serviceable_city_ids[] = $city['serviceable_city_id'];

                City::where('id', $city['serviceable_city_id'])
                    ->update(['delivery_charges' => $city['city_delivery_charge']]);
            }

            $data['serviceable_city_ids'] = implode(',', $serviceable_city_ids);
        } else {
            $data['serviceable_city_ids'] = $zone_data->serviceable_city_ids;
        }

        $existing_zone = Zone::where('serviceable_zipcode_ids', $data['serviceable_zipcode_ids'])
            ->where('serviceable_city_ids', $data['serviceable_city_ids'])
            ->where('id', '!=', $id)
            ->first();

        if ($existing_zone) {
            return response()->json([
                'error' => true,
                'message' => 'A zone with the same serviceable cities and zipcodes already exists as ' . $existing_zone->name,
            ], 422);
        }
        $zone_data->update($data);

        return response()->json([
            'error' => false,
            'message' => labels('admin_labels.zone_updated_successfully', 'Zone updated successfully'),
            'data' => $zone_data,
            'location' => route('admin.zones.index')
        ]);
    }

    public function update_status($id)
    {
        $zone = Zone::findOrFail($id);
        $zone->status = $zone->status == '1' ? '0' : '1';
        $zone->save();
        return response()->json(['success' => labels('admin_labels.status_updated_successfully', 'Status updated successfully.')]);
    }
    public function destroy($id)
    {
        $zone = Zone::find($id);

        if ($zone->delete()) {
            return response()->json([
                'error' => false,
                'message' => labels('admin_labels.zone_deleted_successfully', 'Zone deleted successfully!')
            ]);
        } else {
            return response()->json(['error' => labels('admin_labels.something_went_wrong', 'Something went wrong')]);
        }
    }

    public function delete_selected_data(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:zones,id'
        ]);

        foreach ($request->ids as $id) {
            $zones = Zone::find($id);
            if ($zones) {
                Zone::where('id', $id)->delete();
            }
        }
        Zone::destroy($request->ids);

        return response()->json(['message' => 'Selected data deleted successfully.']);
    }
    public function zone_data(Request $request)
    {
        $search = trim($request->input('search'));
        $limit = (int) $request->input('limit', 50);

        $query = Zone::where('status', 1)
            ->where(function ($query) use ($search) {
                $query->where('id', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%');
            });


        $zones = $query->limit($limit)->get(['id', 'name', 'serviceable_city_ids', 'serviceable_zipcode_ids']);
        $total = $query->count();

        $cities = [];
        $zipcodes = [];

        foreach ($zones as $zone) {
            $city_ids = explode(',', $zone->serviceable_city_ids);
            $zipcode_ids = explode(',', $zone->serviceable_zipcode_ids);

            $cities = array_unique(array_merge($cities, $city_ids));
            $zipcodes = array_unique(array_merge($zipcodes, $zipcode_ids));
        }

        $city_names = City::whereIn('id', $cities)->pluck('name', 'id')->toArray();

        $zipcode_names = Zipcode::whereIn('id', $zipcodes)->pluck('zipcode', 'id')->toArray();
        $language_code = get_language_code();
        $response = [
            'total' => $total,
            'results' => $zones->map(function ($zone) use ($city_names, $zipcode_names, $language_code) {
                $city_ids = explode(',', $zone->serviceable_city_ids);
                $zipcode_ids = explode(',', $zone->serviceable_zipcode_ids);
                return [
                    'id' => $zone->id,
                    'text' => getDynamicTranslation('zones', 'name', $zone->id, $language_code), // Translate zone name
                    'serviceable_cities' => implode(', ', array_map(function ($city_id) use ($city_names, $language_code) {
                        return getDynamicTranslation('cities', 'name', $city_id, $language_code) ?? ($city_names[$city_id] ?? null);
                    }, $city_ids)), // Translate city names
                    'serviceable_zipcodes' => implode(', ', array_map(function ($zipcode_id) use ($zipcode_names) {
                        return $zipcode_names[$zipcode_id] ?? null;
                    }, $zipcode_ids)), // Zipcode remains unchanged
                ];
            }),
        ];

        return response()->json($response);
    }
    public function seller_zones_data(Request $request)
    {
        $store_id = getStoreId();
        // dd($request);
        $search = trim($request->input('search'));
        $seller_id = isset($request->seller_id) && !empty($request->seller_id) ? $request->seller_id : "";
        $limit = (int) $request->input('limit', 50);
        $seller_zones = fetchDetails('seller_store', ['seller_id' => $seller_id, 'store_id' => $store_id], ['deliverable_type', 'deliverable_zones']);
        $seller_zones = isset($seller_zones) && !empty($seller_zones) ? $seller_zones[0] : [];

        $query = Zone::where('status', 1)
            ->where(function ($query) use ($search) {
                $query->where('id', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%');
            });

        if ($seller_zones->deliverable_type == '2' || $seller_zones->deliverable_type == '3') {
            $zone_ids = explode(',', $seller_zones->deliverable_zones);
            // dd($zone_ids);
            $query->whereIn('id', $zone_ids);
        }

        $zones = $query->limit($limit)->get(['id', 'name', 'serviceable_city_ids', 'serviceable_zipcode_ids']);
        // dd($zones);
        $total = $query->count();

        $cities = [];
        $zipcodes = [];

        foreach ($zones as $zone) {
            $city_ids = explode(',', $zone->serviceable_city_ids);
            $zipcode_ids = explode(',', $zone->serviceable_zipcode_ids);

            $cities = array_unique(array_merge($cities, $city_ids));
            $zipcodes = array_unique(array_merge($zipcodes, $zipcode_ids));
        }

        $city_names = City::whereIn('id', $cities)->pluck('name', 'id')->toArray();

        $zipcode_names = Zipcode::whereIn('id', $zipcodes)->pluck('zipcode', 'id')->toArray();
        $language_code = get_language_code();
        $response = [
            'total' => $total,
            'results' => $zones->map(function ($zone) use ($city_names, $zipcode_names, $language_code) {
                $city_ids = explode(',', $zone->serviceable_city_ids);
                $zipcode_ids = explode(',', $zone->serviceable_zipcode_ids);

                return [
                    'id' => $zone->id,
                    'text' => getDynamicTranslation('zones', 'name', $zone->id, $language_code), // Translate zone name
                    'serviceable_cities' => implode(', ', array_map(function ($city_id) use ($city_names, $language_code) {
                        return getDynamicTranslation('cities', 'name', $city_id, $language_code) ?? ($city_names[$city_id] ?? null);
                    }, $city_ids)), // Translate city names
                    'serviceable_zipcodes' => implode(', ', array_map(function ($zipcode_id) use ($zipcode_names) {
                        return $zipcode_names[$zipcode_id] ?? null;
                    }, $zipcode_ids)), // Zipcode remains unchanged
                ];
            }),
        ];

        return response()->json($response);
    }
}
