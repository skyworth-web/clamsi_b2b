<?php

namespace App\Http\Controllers\Admin;

use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

use function PHPUnit\Framework\directoryExists;

class FrontLanguageController extends Controller
{
    public function index()
    {
        $languages = Language::all();

        $language_code = session()->get('locale');

        $current_language = fetchdetails('languages', ['code' => $language_code], 'language');
        return view('admin.pages.forms.web_language', compact('languages', 'language_code', 'current_language'));
    }
    public function store(Request $request)
    {
        // Validation

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
            'is_rtl' => isset($request->is_rtl) && $request->is_rtl == "on" ? 1 : 0,
        ]);

        // Return the response
        if ($request->ajax()) {
            return response()->json([
                'message' => labels('admin_labels.language_added_successfully', 'Language Added Successfully')
            ]);
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

    //     if (!File::isDirectory($dir)) {
    //         // Create the directory if it doesn't exist
    //         File::makeDirectory($dir, 0755, true);
    //     }

    //     $filename = $dir . '/front_messages.php';

    //     // Save the file
    //     file_put_contents($filename, $langstr_final);

    //     return response()->json([
    //         'error' => false, 'message' => labels('admin_labels.language_labels_added_successfully', 'Language labels added successfully')
    //     ]);
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

                // Set the filename to 'front_messages.php'
                $filename = $dir . '/front_messages.php';

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



    public function delete($id)
    {
        $l =  Language::findOrFail($id);
        $code = $l->code;
        $folderPath = app()->basePath("lang/$code");

        if (File::isDirectory($folderPath)) {
            // The folder exists in the lang folder
            File::deleteDirectory($folderPath);
        }

        $language =  language::findOrFail($id)->delete();

        if ($language) {
            return response()->json([
                'error' => false,
                'message' => labels('admin_labels.language_deleted_successfully', 'Language Deleted Successfully')
            ]);
        } else {
            return response()->json([
                'error' => false,
                'message' => labels('admin_labels.error_occurred_while_deleting_language', 'Error occurred while deleting language')
            ]);
        }
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
    public function downloadLanguageFile($language_code)
    {
        $filePath = base_path("resources/lang/{$language_code}/front_messages.php");
        if (file_exists($filePath)) {
            return response()->download($filePath, "front_messages.php");
        } else {
            return redirect()->back()->with('error', 'Language file not found for code: ' . $language_code);
        }
    }
}
