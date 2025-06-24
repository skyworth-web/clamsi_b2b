<?php

namespace App\Http\Controllers\Admin;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CategorySliders;
use App\Models\City;
use App\Models\ComboProduct;
use App\Models\Language;
use App\Models\Offer;
use App\Models\OfferSliders;
use App\Models\Product;
use App\Models\Promocode;
use App\Models\Section;
use App\Models\Store;
use App\Models\Tax;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class LanguageController extends Controller
{
    public function index()
    {
        $languages = Language::all();

        $language_code = session()->get('locale') ?? 'en';
        // dd($language_code);
        $current_language = fetchdetails('languages', ['code' => $language_code], 'language');
        return view('admin.pages.forms.language', compact('languages', 'language_code', 'current_language'));
        // return view('admin.pages.forms.language');
    }
    public function store(Request $request)
    {
        $rules = [
            'language' => 'required',
            'code' => 'required',
        ];

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }
        // Create or retrieve the language
        $language = Language::firstOrCreate([
            'language' => strtolower($request->language),
            'code' => strtolower($request->code),
            'native_language' => strtolower($request->native_language),
            'is_rtl' => isset($request->is_rtl) && $request->is_rtl == "on" ? 1 : 0,
        ]);

        // Return the response
        if ($request->ajax()) {
            return response()->json(['message' => labels('admin_labels.language_added_successfully', 'Language Added Successfully')]);
        }
    }


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
    // public function savelabel(Request $request, Language $lang)
    // {
    //     $data = $request->except(["_token", "_method"]);

    //     $langstr = '';

    //     foreach ($data as $key => $value) {
    //         $label_data = strip_tags($value);
    //         $label_key = $key;
    //         $langstr .= "'" . $label_key . "' => '$label_data'," . "\n";
    //     }
    //     $langstr_final = "<?php return [" . "\n\n\n" . $langstr . "];";
    //     $root = base_path("/resources/lang");
    //     $dir = $root . '/' . $request->langcode;
    //     if (!file_exists($dir)) {
    //         mkdir($dir, 0755, true);
    //     }
    //     $filename = $dir . '/admin_labels.php';
    //     // dd($filename);
    //     file_put_contents($filename, $langstr_final);
    //     return response()->json(['error' => false, 'message' => labels('admin_labels.language_labels_added_successfully', 'Language labels added successfully')]);
    // }


    public function savelabel(Request $request)
    {
        if ($request->hasFile('translation_file')) {
            $file = $request->file('translation_file');

            if ($file->isValid()) {

                // Use the PHP file directly
                $data = include($file->getRealPath());

                if (!is_array($data)) {
                    return response()->json(['error' => true, 'message' => 'Uploaded PHP file must return an array.']);
                }

                // Prepare data for saving
                $langstr = '';
                foreach ($data as $key => $value) {
                    $label_data = strip_tags($value);
                    $label_key = $key;
                    $langstr .= "'" . $label_key . "' => '" . addslashes($label_data) . "',\n";
                }

                // Final content to be written in the PHP file
                $langstr_final = "<?php return [\n\n" . $langstr . "];";

                // Get the current language code (from session or request)
                $language_code = session()->get('locale');

                // Define the directory for the language
                $dir = base_path("/resources/lang/{$language_code}");
                if (!file_exists($dir)) {
                    mkdir($dir, 0755, true);
                }

                // Set the filename to 'admin_labels.php'
                $filename = $dir . '/admin_labels.php';

                // Save the PHP file with the fixed name
                file_put_contents($filename, $langstr_final);

                // Return success response
                return response()->json([
                    'error' => false,
                    'message' => labels('admin_labels.language_labels_added_successfully', 'Language labels added successfully')
                ]);
            } else {
                return response()->json(['error' => true, 'message' => 'Uploaded file is invalid.']);
            }
        } else {
            return response()->json(['error' => true, 'message' => 'No file uploaded.']);
        }
    }

    public function setLanguage($locale)
    {
        config(['app.locale' => $locale]);
        session()->put('locale', $locale);

        return redirect()->back();
    }

    public function manageLanguage()
    {
        return view('admin.pages.tables.manage_languages');
    }

    public function delete($id)
    {
        $language = Language::findOrFail($id);
        $code = $language->code;

        // Path to the language folder
        $folderPath = resource_path("lang/$code");

        // Check if folder exists and delete it
        if (File::isDirectory($folderPath)) {
            File::deleteDirectory($folderPath);
        }

        // Delete language from the database
        if ($language->delete()) {
            return response()->json([
                'error' => false,
                'message' => labels('admin_labels.language_deleted_successfully', 'Language Deleted Successfully')
            ]);
        } else {
            return response()->json([
                'error' => true,
                'message' => labels('admin_labels.error_occurred_while_deleting_language', 'Error occurred while deleting language')
            ]);
        }
    }

    function list()
    {
        DB::enableQueryLog();
        $search = trim(request('search', ''));
        $sort = request('sort', 'id');
        $order = request('order', 'DESC');
        $limit = request('limit', 5);
        $offset = request('offset', 0);
        $pageNumber = ($offset / $limit) + 1;

        $languages = Language::query()
            ->when($search, function ($query) use ($search) {
                return $query->where('language', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->orderBy($sort, $order)
            ->paginate($limit, ['*'], 'page', $pageNumber);

        $languages->transform(function ($item) {
            // Adjust route names if needed
            $edit_url = route('languages.edit', $item['id']);
            $delete_url = route('languages.destroy', $item['id']);

            // Action dropdown menu
            $item['operate'] = '<div class="dropdown bootstrap-table-dropdown">
                    <a href="#" class="text-dark" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="bx bx-dots-horizontal-rounded"></i>
                    </a>
                    <div class="dropdown-menu table_dropdown language_action_dropdown">
                        <a class="dropdown-item edit-language"
                            href="#"
                            data-bs-toggle="modal"
                            data-bs-target="#editLanguageModal"
                            data-id="' . $item['id'] . '"
                            data-code="' . $item['code'] . '"
                            data-name="' . htmlspecialchars($item['language'], ENT_QUOTES, 'UTF-8') . '">
                            <i class="bx bx-pencil mx-2"></i> Edit
                        </a>';

            if ($item['code'] !== 'en') {
                $item['operate'] .= '<a class="dropdown-item delete-language"
                            href="#"
                            data-url="' . $delete_url . '">
                            <i class="bx bx-trash mx-2"></i> Delete
                        </a>';
            }

            $item['operate'] .= '</div>
                </div>';


            return $item;
        });

        return response()->json([
            "rows" => $languages->items(),
            'total' => $languages->total(),
        ]);
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'language' => 'required|string|max:255'
        ]);

        $language = Language::find($id);
        if (!$language) {
            return response()->json(['error' => true, 'message' => labels('admin_labels.language_not_found', 'Language not found')]);
        }

        $language->update([
            'language' => $request->language
        ]);
        return response()->json(['error' => false, 'message' => labels('admin_labels.language_updated_successfully', 'Language updated successfully')]);
    }
    public function bulk_upload()
    {
        return view('admin.pages.forms.translation_bulk_upload');
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
            'brands' => ['model' => Brand::class, 'column' => ['name']],
            'categories' => ['model' => Category::class, 'column' => ['name']],
            'category_sliders' => ['model' => CategorySliders::class, 'column' => ['title']],
            'taxes' => ['model' => Tax::class, 'column' => ['title']],
            'cities' => ['model' => City::class, 'column' => ['name']],
            'blogs' => ['model' => Blog::class, 'column' => ['title']],
            'offers' => ['model' => Offer::class, 'column' => ['title']],
            'offer_sliders' => ['model' => OfferSliders::class, 'column' => ['title']],
            'zones' => ['model' => Zone::class, 'column' => ['name']],
            'stores' => ['model' => Store::class, 'column' => ['name', 'description']],
            'sections' => ['model' => Section::class, 'column' => ['title', 'short_description']],
            'products' => ['model' => Product::class, 'column' => ['name', 'short_description']],
            'combo_products' => ['model' => ComboProduct::class, 'column' => ['title', 'short_description']],
            'promo_codes' => ['model' => Promocode::class, 'column' => ['title', 'message']],
            'blog_categories' => ['model' => BlogCategory::class, 'column' => ['name']],
        ];
        $csv = fopen($uploaded_file->getRealPath(), 'r');

        $headers = fgetcsv($csv);
        $notFoundIds = [];
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

            if (!$model::find($recordId)) {
                $notFoundIds[] = $recordId;
                continue;
            }

            $data = [];

            // foreach ($column_names as $column) {
            //     $jsonString = trim($rowData[$column] ?? '');
            //     $jsonString = stripslashes($jsonString);
            //     $decoded = json_decode($jsonString, true);

            //     if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            //         $data[$column] = json_encode($decoded, JSON_UNESCAPED_UNICODE);
            //     }
            // }
            foreach ($column_names as $column) {
                $jsonString = trim($rowData[$column] ?? '');
                $jsonString = stripslashes($jsonString);
                $decoded = json_decode($jsonString, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $data[$column] = json_encode($decoded, JSON_UNESCAPED_UNICODE);
                } else {
                    // If it's not JSON, just store it as plain text
                    $data[$column] = $jsonString;
                }
            }
            // dd($data);
            $model::updateOrCreate(
                ['id' => $recordId],
                $data
            );
        }

        fclose($csv);

        if (!empty($notFoundIds)) {
            return response()->json([
                'error' => true,
                'message' => 'These IDs were not found in the database.' . implode(', ', $notFoundIds),
                'not_found_ids' => $notFoundIds,
            ]);
        }
        if (!empty($mismatchedRows)) {
            $response['error'] = true;
            $response['message'] = ($response['message'] ?? '') . ' Some rows had column mismatch.';
            $response['mismatched_rows'] = $mismatchedRows;
        }


        return response()->json(['error' => 'false', 'message' =>  labels('admin_labels.upload_complete', 'Upload Complete')]);
    }
    public function export_translation_csv()
    {
        $tableMappings = [
            'brands' => ['table' => 'brands', 'columns' => ['id', 'name']],
            'categories' => ['table' => 'categories', 'columns' => ['id', 'name']],
            'category_sliders' => ['table' => 'category_sliders', 'columns' => ['id', 'title']],
            'taxes' => ['table' => 'taxes', 'columns' => ['id', 'title']],
            'cities' => ['table' => 'cities', 'columns' => ['id', 'name']],
            'blogs' => ['table' => 'blogs', 'columns' => ['id', 'title']],
            'offers' => ['table' => 'offers', 'columns' => ['id', 'title']],
            'offer_sliders' => ['table' => 'offer_sliders', 'columns' => ['id', 'title']],
            'zones' => ['table' => 'zones', 'columns' => ['id', 'name']],
            'stores' => ['table' => 'stores', 'columns' => ['id', 'name', 'description']],
            'sections' => ['table' => 'sections', 'columns' => ['id', 'title', 'short_description']],
            'products' => ['table' => 'products', 'columns' => ['id', 'name', 'short_description']],
            'combo_products' => ['table' => 'combo_products', 'columns' => ['id', 'title', 'short_description']],
            'promo_codes' => ['table' => 'promo_codes', 'columns' => ['id', 'title', 'message']],
            'blog_categories' => ['table' => 'blog_categories', 'columns' => ['id', 'name']],
        ];

        $zipFileName = 'bulk_translations_' . now()->format('Y-m-d_H-i-s') . '.zip';
        $zipFilePath = storage_path("app/{$zipFileName}");

        $zip = new ZipArchive;

        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return response()->json(['error' => 'Could not create ZIP archive.'], 500);
        }

        foreach ($tableMappings as $key => $config) {
            $tableName = $config['table'];
            $columns = $config['columns'];

            $rows = DB::table($tableName)->select($columns)->orderBy('id')->get();

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

        // ✅ Response to download
        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }

    public function downloadLanguageFile($language_code)
    {
        $filePath = base_path("resources/lang/{$language_code}/admin_labels.php");
        if (file_exists($filePath)) {
            return response()->download($filePath, "admin_labels.php");
        } else {
            return redirect()->back()->with('error', 'Language file not found for code: ' . $language_code);
        }
    }
}
