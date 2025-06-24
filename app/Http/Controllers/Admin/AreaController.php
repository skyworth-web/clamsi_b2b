<?php

namespace App\Http\Controllers\Admin;

use App\Models\Area;
use App\Models\City;
use App\Models\Language;
use App\Models\Setting;
use App\Models\Zipcode;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;

class AreaController extends Controller
{

    // zipcode

    public function displayZipcodes()
    {
        $cities = City::get();
        $language_code = get_language_code();
        return view('admin.pages.forms.zipcodes', ['cities' => $cities, 'language_code' => $language_code]);
    }

    public function storeZipcodes(Request $request)
    {

        $rules = [
            'city' => 'required',
            'zipcode' => 'required',
            'minimum_free_delivery_order_amount' => 'required',
            'delivery_charges' => 'required',
        ];

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }


        if (Zipcode::where(['city_id' => $request->city, 'zipcode' => $request->zipcode])->exists()) {
            return response()->json(['error_message' => labels('admin_labels.combination_already_exist', 'Combination Already Exist ! Provide a unique Combination')]);
        }

        $zipcode = new Zipcode();
        $zipcode->city_id = $request->city;
        $zipcode->zipcode = $request->zipcode;
        $zipcode->minimum_free_delivery_order_amount = $request->minimum_free_delivery_order_amount;
        $zipcode->delivery_charges = $request->delivery_charges;
        $zipcode->save();

        if ($request->ajax()) {
            return response()->json(['message' => labels('admin_labels.zipcode_added_successfully', 'Zipcode added successully')]);
        }
    }

    public function zipcodeList()
    {
        $search = trim(request('search'));
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = (request('limit')) ? request('limit') : "10";
        $multipleWhere = [
            'zipcodes.id' => $search,
            'zipcodes.zipcode' => $search,

        ];


        $query = Zipcode::query();


        $query->where(function ($query) use ($multipleWhere) {
            foreach ($multipleWhere as $column => $value) {
                $query->orWhere($column, 'like', '%' . $value . '%');
            }
        });

        if (isset($where) && !empty($where)) {
            $query->where($where);
        }
        $total = $query->count();


        $search_query = DB::table('zipcodes');

        if (!Schema::hasColumn('zipcodes', 'city_id')) {
            $search_query->select('*');
        } else {
            $search_query->select('zipcodes.*', 'cities.name as city_name')
                ->leftJoin('cities', 'zipcodes.city_id', '=', 'cities.id');
        }

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $search_query->where(function ($search_query) use ($multipleWhere) {
                foreach ($multipleWhere as $column => $value) {
                    $search_query->orWhere($column, 'like', '%' . $value . '%');
                }
            });
        }

        if (isset($where) && !empty($where)) {
            $search_query->where($where);
        }

        $result = $search_query->orderBy($sort, $order)
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->toArray();

        $language_code = get_language_code();
        if (!empty($result)) {
            foreach ($result as $row) {
                // dd($row);
                $tempRow['id'] = $row->id;
                $tempRow['zipcode'] = $row->zipcode;
                if (!Schema::hasColumn('zipcodes', 'city_id')) {
                    $tempRow['city_name'] = '';
                    $tempRow['minimum_free_delivery_order_amount'] = 0;
                    $tempRow['delivery_charges'] = 0;
                } else {
                    $tempRow['city_name'] = getDynamicTranslation('cities', 'name', $row->city_id, $language_code);
                    $tempRow['minimum_free_delivery_order_amount'] = $row->minimum_free_delivery_order_amount;
                    $tempRow['delivery_charges'] = $row->delivery_charges;
                }
                $delete_url = route('admin.zipcodes.destroy', $row->id);
                $action = '<div class="dropdown bootstrap-table-dropdown">
            <a href="#" class="text-dark" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="bx bx-dots-horizontal-rounded"></i>
            </a>
            <div class="dropdown-menu table_dropdown zipcode_action_dropdown" aria-labelledby="dropdownMenuButton">
            <a class="dropdown-item dropdown_menu_items edit-zipcode" data-id="' . $row->id . '"><i class="bx bx-pencil mx-2"></i> Edit</a>
                <a class="dropdown_menu_items dropdown-item delete-data" data-url="' . $delete_url . '"><i class="bx bx-trash mx-2"></i> Delete</a>
            </div>
        </div>';
                $tempRow['operate'] = $action;

                $rows[] = $tempRow;
            }

            return response()->json([
                "rows" => $rows,
                "total" => $total,
            ]);
        }
    }

    public function zipcodeDestroy($id)
    {
        $zipcode = Zipcode::find($id);

        if ($zipcode->delete()) {
            return response()->json(['error' => false, 'message' => labels('admin_labels.zipcode_deleted_successfully', 'Zipcode deleted successfully')]);
        } else {
            return response()->json(['error' => labels('admin_labels.something_went_wrong', 'Something went wrong')]);
        }
    }

    // city

    public function displayCity()
    {
        $languages = Language::all();
        return view('admin.pages.forms.city', ['languages' => $languages]);
    }

    public function storeCity(Request $request)
    {
        $rules = [
            'name' => 'required|unique:cities',
            'minimum_free_delivery_order_amount' => 'required',
            'delivery_charges' => 'required',
        ];

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }
        $city = new City();
        $translations = [
            'en' => $request->name
        ];
        if (!empty($request->translated_city_name)) {
            $translations = array_merge($translations, $request->translated_city_name);
        }
        // dd($translations);
        $city->name = json_encode($translations, JSON_UNESCAPED_UNICODE);
        $city->minimum_free_delivery_order_amount = $request->minimum_free_delivery_order_amount;
        $city->delivery_charges = $request->delivery_charges;

        $city->save();

        if ($request->ajax()) {
            return response()->json(['message' => labels('admin_labels.city_added_successfully', 'City added successfully')]);
        }
    }


    public function cityList(Request $request)
    {
        $search = trim(request('search'));
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = (request('limit')) ? request('limit') : "10";

        $city_data = City::when($search, function ($query) use ($search) {
            return $query->where('name', 'like', '%' . $search . '%');
        });

        $total = $city_data->count();

        // Use Paginator to handle the server-side pagination
        $cities = $city_data->orderBy($sort, $order)->offset($offset)
            ->limit($limit)
            ->get();

        // Prepare the data for the "Actions" field
        $data = $cities->map(function ($c) {
            $delete_url = route('admin.city.destroy', $c->id);
            $action = '<div class="dropdown bootstrap-table-dropdown">
                <a href="#" class="text-dark" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="bx bx-dots-horizontal-rounded"></i>
                </a>
                <div class="dropdown-menu table_dropdown city_action_dropdown" aria-labelledby="dropdownMenuButton">
                <a class="dropdown-item dropdown_menu_items edit-city" data-id="' . $c->id . '"><i class="bx bx-pencil mx-2"></i> Edit</a>
                    <a class="dropdown-item delete-data dropdown_menu_items" data-url="' . $delete_url . '"><i class="bx bx-trash mx-2"></i> Delete</a>
                </div>
            </div>';
            $language_code = get_language_code();
            return [
                'id' => $c->id,
                'name' => getDynamicTranslation('cities', 'name', $c->id, $language_code),
                'minimum_free_delivery_order_amount' => $c->minimum_free_delivery_order_amount,
                'delivery_charges' => $c->delivery_charges,
                'operate' => $action,
            ];
        });

        return response()->json([
            "rows" => $data, // Return the formatted data for the "Actions" field
            "total" => $total,
        ]);
    }

    public function cityDestroy($id)
    {
        $city = City::find($id);
        if (isForeignKeyInUse('zipcodes', 'city_id', $id)) {
            return response()->json(['error' => labels('admin_labels.you_cannot_delete_this_city_because_it_is_assoicated_with_zipcode', 'You cannot delete this city because it is associated with zipcode.')]);
        }

        if ($city->delete()) {
            return response()->json(['error' => false, 'message' => labels('admin_labels.city_deleted_successfully', 'City deleted successfully')]);
        }
        return response()->json(['error' => labels('admin_labels.something_went_wrong', 'Something went wrong')]);
    }

    public function getCities(Request $request)
    {
        $search = trim($request->search);

        $cities = City::where('name', 'like', '%' . $search . '%')->get();
        $language_code = get_language_code();
        $data = array();
        foreach ($cities as $city) {
            $data[] = array("id" => $city->id, "text" => getDynamicTranslation('cities', 'name', $city->id, $language_code));
        }
        return response()->json($data);
    }

    public function getCitiesList($sort = "c.name", $order = "ASC", $search = "", $limit = '', $offset = '', $language_code = '')
    {

        $query = City::select('cities.*')
            ->leftJoin('areas', 'cities.id', '=', 'areas.city_id');

        if (!empty($search)) {
            $query->where('cities.name', 'like', '%' . $search . '%');
        }
        $totalRecords = $query->count();
        $cities = $query->orderBy($sort, $order)
            ->skip($offset)
            ->take($limit)
            ->get();
        // Remove created_at and updated_at fields from each item in the collection
        $cities->each(function ($item) use ($language_code) {
            $item->name = getDynamicTranslation('cities', 'name', $item->id, $language_code);
            unset($item->created_at);
            unset($item->updated_at);
        });

        $bulkData = [
            'error' => $cities->isEmpty(),
            'total' => $totalRecords,
            'language_message_key' => 'cities_retrived_successfully',
            'data' => $cities->isEmpty() ? [] : $cities->toArray(),
        ];


        return response()->json($bulkData);
    }


    public function get_zipcodes(Request $request)
    {
        $search = trim($request->search) ?? "";
        $zipcodes = Zipcode::where('zipcode', 'like', '%' . $search . '%')->get();

        $data = array();
        foreach ($zipcodes as $zipcode) {
            $data[] = array("id" => $zipcode->id, "text" => $zipcode->zipcode);
        }
        return response()->json($data);
    }

    public function getZipcodes($search = '', $limit = '', $offset = 0)
    {
        $search = !empty($search) ? $search : "";
        $limit = !empty($limit) ? $limit : null;

        $zipcode = new Zipcode();

        $query = $zipcode;
        $totalQuery = clone $zipcode;

        if (!empty($search)) {
            $query = $query->where('zipcode', 'like', '%' . $search . '%');
            $totalQuery = $totalQuery->where('zipcode', 'like', '%' . $search . '%');
        }

        $total = $totalQuery->count();

        if (!is_null($limit)) {
            $query = $query->take($limit);
        }

        if (!is_null($offset)) {
            $query = $query->skip($offset);
        }

        $zipcodes = $query->get();


        $bulkData = [
            'error' => $zipcodes->isEmpty(),
            'message' => $zipcodes->isEmpty() ? labels('admin_labels.zipcode_not_exist', 'Zipcode not exist') : labels('admin_labels.zipcode_retrived_successfully', 'Zipcode retrived successfully'),
            'total' => $zipcodes->isEmpty() ? 0 : $total,
            'data' => $zipcodes
        ];

        return $bulkData;
    }

    public function location_bulk_upload()
    {
        return view('admin.pages.forms.location_bulk_upload');
    }

    public function process_bulk_upload(Request $request)
    {
        if (!$request->hasFile('upload_file')) {
            return response()->json(['error' => 'true', 'message' => labels('admin_labels.please_choose_file', 'Please Choose File')]);
        }

        // Validate allowed mime types
        $allowed_mime_types = [
            'text/x-comma-separated-values',
            'text/comma-separated-values',
            'application/x-csv',
            'text/x-csv',
            'text/csv',
            'application/csv',
        ];

        $uploaded_file = $request->file('upload_file');
        $uploaded_mime_type = $uploaded_file->getClientMimeType();

        if (!in_array($uploaded_mime_type, $allowed_mime_types)) {
            return response()->json(['error' => 'true', 'message' => labels('admin_labels.invalid_file_format', 'Invalid File Format')]);
        }
        $location_type = $request->location_type;
        $csv = $_FILES['upload_file']['tmp_name'];
        $temp = 0;
        $temp1 = 0;
        $handle = fopen($csv, "r");

        $type = $request->type;
        $language_code = get_language_code();
        if ($type == 'upload' && $location_type == 'zipcode') {
            while (($row = fgetcsv($handle, 10000, ",")) != FALSE) {
                if ($temp != 0) {
                    if (empty($row[0]) && $row[0] == "") {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.zipcode_empty_at_row', 'Zipcode is empty at row') . $temp]);
                    }

                    if (empty($row[1]) && $row[1] == "") {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.city_id_empty_at_row', 'City id is empty at row') . $temp]);
                    }

                    if (!empty($row[1]) && $row[1] != "") {
                        if (!isExist(['id' => $row[1]], 'cities')) {
                            return response()->json(['error' => 'true', 'message' => labels('admin_labels.city_not_exist_database_at_row', 'City does not exist in your database at row') . $temp]);
                        }
                    }

                    if (empty($row[2]) && $row[2] == "") {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.minimum_free_delivery_order_amount_is_empty_at_row', 'Minimum Free Delivery Order Amount is empty at row') . $temp]);
                    }
                    if (empty($row[3]) && $row[3] == "") {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.delivery_charges_empty_at_row', 'Delivery Charges is empty at row') . $temp]);
                    }


                    if (Zipcode::where(['city_id' => $row[1], 'zipcode' => $row[0]])->exists()) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.combination_already_exists', 'Combination Already Exists! Provide a unique Combination at row') . $temp]);
                    }
                }
                $temp++;
            }
            fclose($handle);
            $handle = fopen($csv, "r");
            while (($row = fgetcsv($handle, 10000, ",")) != FALSE) {
                if ($temp1 !== 0) {
                    $data = [
                        'zipcode' => $row[0],
                        'city_id' => $row[1],
                        'minimum_free_delivery_order_amount' => $row[2],
                        'delivery_charges' => $row[3],
                    ];
                    Zipcode::create($data);
                }
                $temp1++;
            }
            fclose($handle);
            return response()->json(['error' => 'false', 'message' => labels('admin_labels.zipcode_uploaded_successfully', 'Zipcode uploaded successfully')]);
        } else if ($type == 'upload' && $location_type == 'city') {
            while (($row = fgetcsv($handle, 10000, ",")) != FALSE) {
                if ($temp != 0) {
                    if (empty($row[0])) {
                        return response()->json(['message' => 'Name is empty at row ' . $temp]);
                    }
                    if (empty($row[1]) && $row[1] == "") {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.minimum_free_delivery_order_amount_is_empty_at_row', 'Minimum Free Delivery Order Amount is empty at row') . $temp]);
                    }
                    if (empty($row[2]) && $row[2] == "") {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.delivery_charges_empty_at_row', 'Delivery Charges is empty at row') . $temp]);
                    }
                    if (!empty($row[0])) {
                        if (isExist(['name' => $row[0]], 'cities')) {
                            return response()->json(['error' => 'true', 'message' => labels('admin_labels.city_already_exist_provide_another_city_name_at_row', 'City Already Exist! Provide another city name at row') . $temp]);
                        }
                    }
                }
                $temp++;
            }
            fclose($handle);
            $handle = fopen($csv, "r");
            while (($row = fgetcsv($handle, 10000, ",")) !== FALSE) {
                if ($temp1 !== 0) {
                    $city_name = trim($row[0]);
                    $city_name = stripslashes($city_name);

                    $decoded_city_name = json_decode($city_name, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        return response()->json(['error' => 'true', 'message' => "Invalid JSON format in city name at row {$temp1}"]);
                    }

                    $data = [
                        'name' => json_encode($decoded_city_name, JSON_UNESCAPED_UNICODE),
                        'minimum_free_delivery_order_amount' => $row[1] ?? null,
                        'delivery_charges' => $row[2] ?? null,
                    ];

                    City::create($data);
                }
                $temp1++;
            }
            fclose($handle);
            return response()->json(['error' => 'false', 'message' => labels('admin_labels.city_uploaded_successfully', 'City uploaded successfully!')]);
        } else if ($type == 'upload' && $location_type == 'zone') {
            while (($row = fgetcsv($handle, 10000, ",")) != FALSE) {
                $validator = Validator::make($request->all(), [
                    'upload_file' => 'required|mimes:csv,txt|max:2048',
                ]);

                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()->all()], 422);
                }
                $file = $request->file('upload_file');
                $csv = Reader::createFromPath($file->getPathname(), 'r');
                $csv->setHeaderOffset(0);

                $records = $csv->getRecords();
                $errors = [];
                $zoneRows = [];

                foreach ($records as $index => $row) {
                    $rowValidator = Validator::make($row, [
                        'name' => 'required',
                        'serviceable_zipcode_id' => 'required|integer',
                        'zipcode_delivery_charge' => 'required|numeric',
                        'serviceable_city_id' => 'required|integer',
                        'city_delivery_charge' => 'required|numeric',
                    ]);

                    if ($rowValidator->fails()) {
                        $errors[] = [
                            'row' => $index + 1,
                            'errors' => $rowValidator->errors()->all(),
                        ];
                        continue;
                    }
                    $zoneRows[$row['name']]['zipcode_group'][] = [
                        'serviceable_zipcode_id' => $row['serviceable_zipcode_id'],
                        'zipcode_delivery_charge' => $row['zipcode_delivery_charge'],
                    ];
                    $zoneRows[$row['name']]['city_group'][] = [
                        'serviceable_city_id' => $row['serviceable_city_id'],
                        'city_delivery_charge' => $row['city_delivery_charge'],
                    ];
                }

                if (!empty($errors)) {
                    return response()->json(['errors' => $errors], 422);
                }
                foreach ($zoneRows as $name => $groups) {
                    $zipcode_ids = collect($groups['zipcode_group'])->pluck('serviceable_zipcode_id')->implode(',');
                    $city_ids = collect($groups['city_group'])->pluck('serviceable_city_id')->implode(',');

                    $existing_zone = Zone::where('serviceable_zipcode_ids', $zipcode_ids)
                        ->where('serviceable_city_ids', $city_ids)
                        ->first();

                    if ($existing_zone) {
                        return response()->json([
                            'error' => true,
                            'message' => 'A zone with the same serviceable cities and zipcodes already exists as ' . getDynamicTranslation('zones', 'name', $existing_zone->id, $language_code),
                        ]);
                    }

                    $data = [
                        'name' => $name,
                        'serviceable_zipcode_ids' => $zipcode_ids,
                        'serviceable_city_ids' => $city_ids,
                        'status' => 1,
                    ];
                    Zone::create($data);
                    foreach ($groups['zipcode_group'] as $zipcode) {
                        Zipcode::where('id', $zipcode['serviceable_zipcode_id'])
                            ->update(['delivery_charges' => $zipcode['zipcode_delivery_charge']]);
                    }
                    foreach ($groups['city_group'] as $city) {
                        City::where('id', $city['serviceable_city_id'])
                            ->update(['delivery_charges' => $city['city_delivery_charge']]);
                    }
                }

                return response()->json(['error' => 'false', 'message' => labels('admin_labels.zones_uploaded_successfully', 'Zones uploaded successfully!')]);
            }
        } else if ($type == 'update' && $location_type == 'zipcode') {
            while (($row = fgetcsv($handle, 10000, ",")) != FALSE) //get row vales
            {
                if ($temp != 0) {
                    if (empty($row[0])) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.zipcode_id_empty_at_row', 'Zipcode id empty at row') . $temp]);
                    }

                    if (!empty($row[0]) && $row[0] != "") {
                        if (!isExist(['id' => $row[0]], 'zipcodes')) {
                            return response()->json(['error' => 'true', 'message' => labels('admin_labels.zipcode_not_exist_database_at_row', 'Zipcode id is not exist in your database at row') . $temp]);
                        }
                    }

                    if (empty($row[1]) && $row[1] == "") {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.zipcode_empty_at_row', 'Zipcode is empty at row') . $temp]);
                    }

                    if (empty($row[2]) && $row[2] == "") {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.city_id_empty_at_row', 'City id is empty at row') . $temp]);
                    }

                    if (!empty($row[2]) && $row[2] != "") {
                        if (!isExist(['id' => $row[2]], 'cities')) {
                            return response()->json(['error' => 'true', 'message' => labels('admin_labels.city_not_exist_database_at_row', 'City does not exist in your database at row') . $temp]);
                        }
                    }

                    if (empty($row[3]) && $row[3] == "") {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.minimum_free_delivery_order_amount_is_empty_at_row', 'Minimum Free Delivery Order Amount is empty at row') . $temp]);
                    }
                    if (empty($row[4]) && $row[4] == "") {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.delivery_charges_empty_at_row', 'Delivery Charges is empty at row') . $temp]);
                    }
                }
                $temp++;
            }
            fclose($handle);
            $handle = fopen($csv, "r");
            while (($row = fgetcsv($handle, 10000, ",")) != FALSE) {
                if ($temp1 != 0) {
                    $zipcode_id = $row[0];
                    $zipcode = fetchDetails('zipcodes', ['id' => $zipcode_id], '*');
                    if (!empty($zipcode)) {
                        if (!empty($row[1])) {
                            $data['zipcode'] = $row[1];
                            $data['city_id'] = $row[2];
                            $data['minimum_free_delivery_order_amount'] = $row[3];
                            $data['delivery_charges'] = $row[4];
                            $existing_zipcode = Zipcode::where(['city_id' => $row[1], 'zipcode' => $row[0]])->exists();
                            $data['zipcode'] = $row[1];
                            if ($existing_zipcode) {
                                return response()->json(['error' => 'true', 'message' => "Zipcode '{$data['zipcode']}' already exists. Please provide another zipcode."]);
                            }
                        } else {
                            $data['zipcode'] = $zipcode[0]['zipcode'];
                            $data['city_id'] = $zipcode[0]['city_id'];
                            $data['minimum_free_delivery_order_amount'] = $zipcode[0]['minimum_free_delivery_order_amount'];
                            $data['delivery_charges'] = $zipcode[0]['delivery_charges'];
                        }
                        Zipcode::where('id', $zipcode_id)->update($data);
                    } else {
                        return response()->json(['error' => 'true', 'message' => 'Zipcode id: ' . $zipcode_id . ' not exist!']);
                    }
                }
                $temp1++;
            }
            fclose($handle);
            return response()->json([
                'error' => 'false',
                'message' => labels('admin_labels.zipcodes_updated_successfully', 'Zipcodes updated successfully')
            ]);
        } else if ($type == 'update' && $location_type == 'city') {
            while (($row = fgetcsv($handle, 10000, ",")) != FALSE) {
                if ($temp != 0) {
                    if (empty($row[0])) {
                        return response()->json([
                            'error' => 'true',
                            'message' => labels('admin_labels.city_id_empty_at_row', 'City id empty at row')
                                . $temp
                        ]);
                    }

                    if (!empty($row[0]) && $row[0] != "") {
                        if (!isExist(['id' => $row[0]], 'cities')) {
                            return response()->json([
                                'error' => 'true',
                                'message' => labels('admin_labels.city_id_not_exist_database_at_row', 'City id is not exist in your database at row')
                                    . $temp
                            ]);
                        }
                    }

                    if (empty($row[1])) {
                        return response()->json([
                            'error' => 'true',
                            'message' => labels('admin_labels.city_empty_at_row', 'City empty at row')
                                . $temp
                        ]);
                    }
                }
                $temp++;
            }
            fclose($handle);
            $handle = fopen($csv, "r");
            // while (($row = fgetcsv($handle, 10000, ",")) != FALSE) {
            //     if ($temp1 != 0) {
            //         $city_id = $row[0];
            //         $city = fetchDetails('cities', ['id' => $city_id], '*');
            //         if (!empty($city)) {
            //             if (!empty($row[1])) {
            //                 $data['name'] = $row[1];
            //                 $existing_city = City::where('name', $data['name'])->first();
            //                 if ($existing_city) {
            //                     return response()->json(['error' => 'true', 'message' => "City name '{$data['name']}' already exists. Please provide another name."]);
            //                 }
            //             } else {
            //                 $data['name'] = $city[0]['name'];
            //             }
            //             City::where('id', $city_id)->update($data);
            //         } else {
            //             return response()->json(['error' => 'true', 'message' => 'City id: ' . $city_id . ' not exist!']);
            //         }
            //     }
            //     $temp1++;
            // }
            while (($row = fgetcsv($handle, 10000, ",")) !== FALSE) {
                if ($temp1 !== 0) {
                    $city_id = $row[0];
                    $city = fetchDetails('cities', ['id' => $city_id], '*');

                    if (!empty($city)) {
                        $data = [];
                        if (!empty($row[1])) {
                            $city_name = trim($row[1]);
                            $city_name = stripslashes($city_name);

                            $decoded_city_name = json_decode($city_name, true);

                            if (json_last_error() !== JSON_ERROR_NONE) {
                                return response()->json(['error' => 'true', 'message' => "Invalid JSON format in name at row {$temp1}"]);
                            }

                            $data['name'] = json_encode($decoded_city_name, JSON_UNESCAPED_UNICODE);
                        }
                        $data['minimum_free_delivery_order_amount'] = !empty($row[2]) ? $row[2] : '';
                        $data['delivery_charges'] = !empty($row[3]) ? $row[3] : '';

                        City::where('id', $city_id)->update($data);
                    }
                }
                $temp1++;
            }
            fclose($handle);
            return response()->json([
                'error' => 'false',
                'message' => labels('admin_labels.city_updated_successfully', 'City updated successfully!')
            ]);
        } else if ($type == 'update' && $location_type == 'zone') {
            while (($row = fgetcsv($handle, 10000, ",")) != FALSE) {
                $validator = Validator::make($request->all(), [
                    'upload_file' => 'required|mimes:csv,txt|max:2048',
                ]);

                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()->all()], 422);
                }
                $file = $request->file('upload_file');
                $csv = Reader::createFromPath($file->getPathname(), 'r');
                $csv->setHeaderOffset(0);

                $records = $csv->getRecords();
                $errors = [];
                $zipcode_group = [];
                $city_group = [];

                foreach ($records as $index => $row) {
                    // Validate each row
                    $rowValidator = Validator::make($row, [
                        'id' => 'required|integer|exists:zones,id',
                        'name' => 'nullable|string',
                        'serviceable_zipcode_id' => 'required|integer',
                        'zipcode_delivery_charge' => 'required|numeric',
                        'serviceable_city_id' => 'required|integer',
                        'city_delivery_charge' => 'required|numeric',
                    ]);

                    if ($rowValidator->fails()) {
                        $errors[] = [
                            'row' => $index + 1,
                            'errors' => $rowValidator->errors()->all(),
                        ];
                        continue;
                    }
                    $zone_data = Zone::find($row['id']);

                    if (!$zone_data) {
                        $errors[] = [
                            'row' => $index + 1,
                            'errors' => ['Zone with ID ' . $row['id'] . ' not found.'],
                        ];
                        continue;
                    }
                    if (!empty($row['name'])) {
                        $zone_data->name = $row['name'];
                    }
                    $zipcode_group[] = [
                        'serviceable_zipcode_id' => $row['serviceable_zipcode_id'],
                        'zipcode_delivery_charge' => $row['zipcode_delivery_charge'],
                    ];

                    $city_group[] = [
                        'serviceable_city_id' => $row['serviceable_city_id'],
                        'city_delivery_charge' => $row['city_delivery_charge'],
                    ];
                    Zipcode::where('id', $row['serviceable_zipcode_id'])
                        ->update(['delivery_charges' => $row['zipcode_delivery_charge']]);
                    City::where('id', $row['serviceable_city_id'])
                        ->update(['delivery_charges' => $row['city_delivery_charge']]);
                }
                if (!empty($errors)) {
                    return response()->json(['errors' => $errors], 422);
                }
                $serviceable_zipcode_ids = collect($zipcode_group)->pluck('serviceable_zipcode_id')->implode(',');
                $serviceable_city_ids = collect($city_group)->pluck('serviceable_city_id')->implode(',');
                if ($zone_data->serviceable_zipcode_ids == $serviceable_zipcode_ids && $zone_data->serviceable_city_ids == $serviceable_city_ids) {
                    $zone_data->update([
                        'name' => $zone_data->name,
                        'serviceable_zipcode_ids' => $serviceable_zipcode_ids,
                        'serviceable_city_ids' => $serviceable_city_ids,
                    ]);
                } else {
                    $existing_zone = Zone::where('serviceable_zipcode_ids', $serviceable_zipcode_ids)
                        ->where('serviceable_city_ids', $serviceable_city_ids)
                        ->where('id', '!=', $zone_data->id)
                        ->first();

                    if ($existing_zone) {
                        return response()->json([
                            'error' => true,
                            'message' => 'A zone with the same serviceable cities and zipcodes already exists as ' . getDynamicTranslation('zones', 'name', $existing_zone->id, $language_code),
                        ]);
                    }
                    $zone_data->update([
                        'name' => !empty($row['name']) ? $row['name'] : $zone_data->name,
                        'serviceable_zipcode_ids' => $serviceable_zipcode_ids,
                        'serviceable_city_ids' => $serviceable_city_ids,
                    ]);
                }
                return response()->json([
                    'error' => 'false',
                    'message' => labels('admin_labels.zone_updated_successfully', 'Zone updated successfully!')
                ]);
            }
        } else {
            return response()->json([
                'error' => 'true',
                'message' => labels('admin_labels.invalid_type_or_type_location', 'Invalid Type or Type Location!')
            ]);
        }
    }


    public function zipcodesEdit($id)
    {
        return $this->editData(Zipcode::class, $id, labels('admin_labels.data_not_found', 'Data Not Found'));
    }

    public function zipcodeShow($id)
    {

        $zipcode = Zipcode::findOrFail($id);

        return response()->json($zipcode);
    }

    public function zipcodesUpdate(Request $request, $id)
    {

        $fields = ['zipcode', 'city_id', 'minimum_free_delivery_order_amount', 'delivery_charges'];
        return $this->updateData(
            $request,
            Zipcode::class,
            $id,
            $fields,
            labels('admin_labels.zipcode_updated_successfully', 'Zipcode updated successfully'),
            'zipcode'
        );
    }
    public function distroyZipcode($id)
    {

        $zipcode = Zipcode::find($id);

        if ($zipcode->delete()) {
            return response()->json([
                'error' => false,
                'message' => labels('admin_labels.zipcode_deleted_successfully', 'Zipcode deleted successfully!')
            ]);
        } else {
            return response()->json(['error' => labels('admin_labels.something_went_wrong', 'Something went wrong')]);
        }
    }

    public function cityEdit($id)
    {
        return $this->editData(City::class, $id, labels('admin_labels.data_not_found', 'Data Not Found'));
    }

    public function cityUpdate(Request $request, $id)
    {
        $fields = ['name', 'minimum_free_delivery_order_amount', 'delivery_charges'];

        return $this->updateData(
            $request,
            City::class,
            $id,
            $fields,
            labels('admin_labels.city_updated_successfully', 'City updated successfully'),
            'city'
        );
    }

    // general function for fetch edit data

    public function editData($model_name, $id, $error_message)
    {
        $data = $model_name::find($id);

        if (!$data) {
            return response()->json(['error' => true, 'message' => $error_message], 404);
        }
        return response()->json($data);
    }

    // general function for update fetched data

    public function updateData(Request $request, $model_name, $id, $fields, $success_message, $table_name = "")
    {
        $data = $model_name::find($id);
        // dd($model_name);
        if (!$data) {
            return response()->json(['error' => true, 'message' => labels('admin_labels.data_not_found', 'Data Not Found')], 404);
        } else {
            // Define validation rules
            $rules = [];
            foreach ($fields as $field) {
                $rules[$field] = 'required';
            }

            // Validate the request
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $errors = $validator->errors();

                if ($request->ajax()) {
                    return response()->json(['errors' => $errors->all()], 422);
                }
                return redirect()->back()->withErrors($errors)->withInput();
            }

            // Update the data
            if (count($fields) === 1) {
                // dd($data);
                $field = reset($fields);
                $data->{strtolower($field)} = $request->input($field);
            } else {
                if (isset($table_name) && $table_name == 'zipcode') {
                    foreach ($fields as $field) {
                        $data->{strtolower($field)} = $request->input($field);
                    }
                } else {
                    foreach ($fields as $field) {
                        $existingTranslations = json_decode($request->input('name'), true);

                        // Ensure it's an array
                        if (!is_array($existingTranslations)) {
                            $existingTranslations = [];
                        }

                        $existingTranslations['en'] = $request->input('name');
                        // dd($request->translated_city_name);
                        if (!empty($request->translated_city_name)) {
                            $existingTranslations = array_merge($existingTranslations, $request->translated_city_name);
                        }
                        // dd($existingTranslations);
                        $data->name = json_encode($existingTranslations, JSON_UNESCAPED_UNICODE);
                        $data->minimum_free_delivery_order_amount = $request->minimum_free_delivery_order_amount;
                        $data->delivery_charges = $request->minimum_free_delivery_order_amount;
                        // $data->{strtolower($field)} = $request->input($field);
                    }
                }
            }
            // dd($data);
            $data->save();

            if ($request->ajax()) {
                return response()->json(['message' => $success_message]);
            }
        }
    }


    public function getAreaByCity($city_id, $sort = "zipcode", $order = "ASC", $search = "", $limit = '', $offset = '')
    {

        $where = [];
        $multipleWhere = [];
        if (!empty($search)) {
            $multipleWhere = [
                'z.zipcode' => $search,

            ];
        }

        if ($city_id != '') {
            $where[] = ['city_id', '=', $city_id];
        }

        $query = DB::table('zipcodes as z')
            ->where($where)
            ->orderBy($sort, $order);

        if (!empty($limit)) {
            $query->limit($limit);
        }


        $query->where(function ($q) use ($multipleWhere) {
            foreach ($multipleWhere as $column => $value) {
                $q->orWhere($column, 'like', '%' . $value . '%');
            }
        });


        if (!empty($offset)) {
            $query->offset($offset);
        }

        $areas = $query->select('z.zipcode', 'z.id as id')->get();




        $bulkData = [];
        $bulkData['error'] = $areas->isEmpty();
        $bulkData['data'] = $areas->toArray();

        return $bulkData;
    }
    public function delete_selected_data(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:zipcodes,id'
        ]);

        foreach ($request->ids as $id) {
            $zipcodes = Zipcode::find($id);

            if ($zipcodes) {
                Zipcode::where('id', $id)->delete();
            }
        }

        return response()->json([
            'error' => false,
            'message' => labels('admin_labels.zipcode_deleted_successfully', 'Selected zipcodes deleted successfully!'),
        ]);
    }
    public function delete_selected_city_data(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:cities,id'
        ]);

        foreach ($request->ids as $id) {
            $city = City::find($id);

            if ($city) {
                City::where('id', $id)->delete();
            }
        }

        return response()->json([
            'error' => false,
            'message' => labels('admin_labels.cities_deleted_successfully', 'Selected cities deleted successfully!'),
        ]);
    }
}
