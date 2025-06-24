<?php

namespace App\Http\Controllers\Seller;

use App\Models\ComboProduct;
use App\Models\Language;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use ZipArchive;

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
    public function bulk_upload()
    {
        return view('seller.pages.forms.translation_bulk_upload');
    }

    public function process_bulk_upload(Request $request)
    {
        if (!$request->hasFile('upload_file')) {
            return response()->json(['error' => true, 'message' => 'Please choose a file']);
        }

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
        $type = $request->type;

        if (!in_array($uploaded_mime_type, $allowed_mime_types)) {
            return response()->json(['error' => true, 'message' => 'Invalid file format']);
        }

        $model_type = [
            'products' => ['model' => Product::class, 'column' => ['name', 'short_description']],
            'combo_products' => ['model' => ComboProduct::class, 'column' => ['title', 'short_description']],
        ];

        $user_id = Auth::id();
        $seller_id = Seller::where('user_id', $user_id)->value('id');

        if (!$seller_id) {
            return response()->json(['error' => true, 'message' => 'Seller not found.']);
        }

        $csv = fopen($uploaded_file->getRealPath(), 'r');
        $headers = fgetcsv($csv);

        $notFoundIds = [];
        $unauthorizedIds = [];
        $mismatchedRows = [];

        $model = $model_type[$type]['model'];
        $column_names = $model_type[$type]['column'];

        while (($row = fgetcsv($csv)) !== false) {
            if (count($headers) !== count($row)) {
                $mismatchedRows[] = $row;
                continue;
            }

            $rowData = array_combine($headers, $row);
            $recordId = $rowData['id'] ?? null;

            $record = $model::find($recordId);

            if (!$record) {
                $notFoundIds[] = $recordId;
                continue;
            }

            if (!isset($record->seller_id) || $record->seller_id != $seller_id) {
                $unauthorizedIds[] = $recordId;
                continue;
            }

            $data = [];

            foreach ($column_names as $column) {
                $jsonString = trim($rowData[$column] ?? '');
                $jsonString = stripslashes($jsonString);
                $decoded = json_decode($jsonString, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $data[$column] = json_encode($decoded, JSON_UNESCAPED_UNICODE);
                }
            }

            $record->update($data);
        }

        fclose($csv);

        // $response = ['error' => false, 'message' => labels('admin_labels.upload_complete', 'Upload Complete')];

        // if (!empty($notFoundIds)) {
        //     $response['error'] = true;
        //     $response['message'] = 'Some IDs were not found in the database.' . implode(', ', $notFoundIds);
        //     $response['not_found_ids'] = $notFoundIds;
        // }

        // if (!empty($unauthorizedIds)) {
        //     $response['error'] = true;
        //     $response['message'] .= ' Some records do not belong to you.' . implode(', ', $unauthorizedIds);
        //     $response['unauthorized_ids'] = $unauthorizedIds;
        // }

        // if (!empty($mismatchedRows)) {
        //     $response['error'] = true;
        //     $response['message'] .= ' Some rows had column mismatch.';
        //     $response['mismatched_rows'] = $mismatchedRows;
        // }
        if (!empty($notFoundIds) || !empty($unauthorizedIds) || !empty($mismatchedRows)) {
            $response = ['error' => true, 'message' => ''];

            if (!empty($notFoundIds)) {
                $response['message'] .= 'Some IDs were not found in the database. ' . implode(', ', $notFoundIds) . '. ';
                $response['not_found_ids'] = $notFoundIds;
            }

            if (!empty($unauthorizedIds)) {
                $response['message'] .= 'Some records do not belong to you. ' . implode(', ', $unauthorizedIds) . '. ';
                $response['unauthorized_ids'] = $unauthorizedIds;
            }

            if (!empty($mismatchedRows)) {
                $response['message'] .= 'Some rows had column mismatch.';
                $response['mismatched_rows'] = $mismatchedRows;
            }
        } else {
            $response = ['error' => false, 'message' => labels('admin_labels.upload_complete', 'Upload Complete')];
        }
        return response()->json($response);
    }


    public function export_translation_csv()
    {
        $tableMappings = [
            'products' => ['table' => 'products', 'columns' => ['id', 'name', 'short_description']],
            'combo_products' => ['table' => 'combo_products', 'columns' => ['id', 'title', 'short_description']],
        ];
        $user_id = Auth::user()->id;
        $seller_id = Seller::where('user_id', $user_id)->value('id');
        $zipFileName = 'bulk_translations_' . now()->format('Y-m-d_H-i-s') . '.zip';
        $zipFilePath = storage_path("app/{$zipFileName}");

        $zip = new ZipArchive;

        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return response()->json(['error' => 'Could not create ZIP archive.'], 500);
        }

        foreach ($tableMappings as $key => $config) {
            $tableName = $config['table'];
            $columns = $config['columns'];

            $rows = DB::table($tableName)->select($columns)->where('seller_id', $seller_id)->orderBy('id')->get();

            $csvHandle = fopen('php://temp', 'r+');
            fputcsv($csvHandle, $columns);

            foreach ($rows as $row) {
                $data = [];
                foreach ($columns as $col) {
                    $data[] = $row->$col;
                }
                fputcsv($csvHandle, $data);
            }

            rewind($csvHandle);
            $csvContent = stream_get_contents($csvHandle);
            fclose($csvHandle);

            $zip->addFromString("{$key}_bulk_translation.csv", $csvContent);
        }

        $zip->close();

        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }
}
