<?php

namespace App\Http\Controllers\Admin;

use ZipArchive;
use App\Models\Setting;
use App\Models\Updates;
use App\Models\Currency;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    public function index()
    {
        return view('admin.pages.forms.settings');
    }

    public function systemSettings()
    {

        $timezone = timezoneList();

        $supported_locales_list = config('eshop_pro.supported_locales_list');

        $settings = getSettings('system_settings', true);
        $settings = json_decode($settings, true);

        return view('admin.pages.forms.system_settings', [
            'timezone' => $timezone,
            'supported_locales_list' => $supported_locales_list,
            'settings' => $settings
        ]);
    }

    public function updater()
    {
        $settings = getSettings('system_settings', true);
        $settings = json_decode($settings, true);

        return view('admin.pages.forms.updater');
    }

    public function registration()
    {
        $settings = getSettings('system_settings', true);
        $settings = json_decode($settings, true);

        return view('admin.pages.forms.system_registration');
    }
    public function systemRegister(Request $request)
    {
        $rules = [
            'app_purchase_code' => 'required'
        ];

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }
        $app_purchase_code = $request->app_purchase_code ?? "";
        if ((isset($app_purchase_code) && !empty($app_purchase_code))) {
            $url = config('app.url');
            $app_code = config('constants.APP_CODE');
            $app_url = "https://wrteam.in/validator/home/validator_new?purchase_code=$app_purchase_code&domain_url=" . $url . "&item_id=" . $app_code;
            $app_result = curl($app_url);
            if (isset($app_result['body']) && !empty($app_result['body'])) {
                if (isset($app_result['body']['error']) && $app_result['body']['error'] == 0) {
                    $doctor_brown = getSettings('doctor_brown', true);
                    $doctor_brown = json_decode($doctor_brown, true);
                    if (empty($doctor_brown)) {
                        $doctor_brown_data = [
                            'code_bravo' => $app_result["body"]["purchase_code"],
                            'time_check' => $app_result["body"]["token"],
                            'code_adam' => $app_result["body"]["username"],
                            'dr_firestone' => $app_result["body"]["item_id"],
                        ];
                        $data = [
                            'variable' => 'doctor_brown',
                            'value' => json_encode($doctor_brown_data),
                        ];
                        Setting::create($data);
                        return response()->json([
                            'error' => false,
                            'message' => labels('admin_labels.system_registered_successfully', 'System Registered Successfully')
                        ]);
                    }
                } else {
                    $response = [
                        'error' => true,
                        'error_message' => $app_result['body']['message'] ? $app_result['body']['message'] : 'Invalid code supplied!.',
                    ];
                    return response()->json($response);
                }
            } else {
                $response = [
                    'error' => true,
                    'error_message' => 'Somthing Went wrong. Please contact Super admin.',
                ];
                return response()->json($response);
            }
        }
    }
    public function WebsystemRegister(Request $request)
    {

        $rules = [
            'web_purchase_code' => 'required'
        ];

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }
        $web_purchase_code = $request->web_purchase_code ?? "";
        if ((isset($web_purchase_code) && !empty($web_purchase_code))) {
            $url = config('app.url');
            $web_code = config('constants.WEB_CODE');
            $app_url = "https://wrteam.in/validator/home/validator_new?purchase_code=$web_purchase_code&domain_url=" . $url . "&item_id=" . $web_code;
            $app_result = curl($app_url);
            if (isset($app_result['body']) && !empty($app_result['body'])) {
                if (isset($app_result['body']['error']) && $app_result['body']['error'] == 0) {
                    $doctor_brown = getSettings('web_doctor_brown', true);
                    $doctor_brown = json_decode($doctor_brown, true);
                    if (empty($doctor_brown)) {
                        $doctor_brown_data = [
                            'web_code_bravo' => $app_result["body"]["purchase_code"],
                            'web_time_check' => $app_result["body"]["token"],
                            'web_code_adam' => $app_result["body"]["username"],
                            'web_dr_firestone' => $app_result["body"]["item_id"],
                        ];
                        $data = [
                            'variable' => 'web_doctor_brown',
                            'value' => json_encode($doctor_brown_data),
                        ];
                        Setting::create($data);
                        return response()->json([
                            'error' => false,
                            'message' => labels('admin_labels.system_registered_successfully', 'System Registered Successfully')
                        ]);
                    }
                } else {
                    $response = [
                        'error' => true,
                        'error_message' => $app_result['body']['message'] ? $app_result['body']['message'] : 'Invalid code supplied!.',
                    ];
                    return response()->json($response);
                }
            } else {
                $response = [
                    'error' => true,
                    'error_message' => 'Somthing Went wrong. Please contact Super admin.',
                ];
                return response()->json($response);
            }
        }
    }


    public function systemUpdate(Request $request)
    {
        ini_set('max_execution_time', 900);
        $zip = new ZipArchive();
        $updatePath = Config::get('constants.UPDATE_PATH');
        $fullUpdatePath = public_path($updatePath);

        if (!empty($_FILES['update_file']['name'][0])) {
            if (!File::exists(public_path($updatePath))) {
                File::makeDirectory(public_path($updatePath), 0777, true);
            }
            $uploadData = $request->file('update_file');
            if (isset($uploadData[0])) {
                $uploadData = $uploadData[0];
            }
            $ext = trim(strtolower($uploadData->getClientOriginalExtension()));

            if ($ext != "zip") {
                Session::flash('error', 'Please insert a valid Zip File.');
                $response = [
                    "error" => true,
                    "message" => "Please insert a valid Zip File.",
                ];
                return response()->json($response);
            }

            if ($uploadData->move(public_path($updatePath))) {
                $filename = $uploadData->getFilename();
                $zip = new ZipArchive();
                $res = $zip->open(public_path($updatePath) . $filename);
                Log::info('ZIP Extract Here' . json_encode($res));
                if ($res === true) {
                    $extractPath = public_path($updatePath);
                    $zip->extractTo($extractPath);
                    $zip->close();

                    if (file_exists($updatePath . "package.json") || file_exists($updatePath . "plugin/package.json")) {
                        $system_info = get_system_update_info();
                        if (isset($system_info['updated_error']) || isset($system_info['sequence_error'])) {
                            $response = [
                                'error' => true,
                                'message' => $system_info['message']
                            ];
                            Session::flash('error', $system_info['message']);
                            File::deleteDirectory($updatePath);
                            return response()->json($response);
                        }

                        /* Plugin / Module installer script */
                        $sub_directory = (file_exists($updatePath . "plugin/package.json")) ? "plugin/" : "";

                        if (file_exists($updatePath . $sub_directory . "package.json")) {
                            $package_data = file_get_contents($updatePath . $sub_directory . "package.json");

                            $package_data = json_decode($package_data, true);
                            // if (!empty($package_data)) {
                            //     // Migrate the database changes
                            //     $pathToMigrationDir = public_path($updatePath) . $sub_directory . 'update-files/database/migrations';
                            //     $pathToMigrationDir = str_replace('/', DIRECTORY_SEPARATOR, $pathToMigrationDir);
                            //     $pathToMigrations = 'public/' . $updatePath . $sub_directory . 'update-files/database/migrations';
                            //     $pathToMigrations = str_replace('/', DIRECTORY_SEPARATOR, $pathToMigrations);

                            //     if (is_dir($pathToMigrationDir)) {
                            //         try {
                            //             Artisan::call('migrate', ['--path' => $pathToMigrations]);
                            //         } catch (\Throwable $e) {
                            //             // Handle any exceptions or errors
                            //         }
                            //     }

                            //     // Handle manual queries if any
                            //     if (isset($package_data['manual_queries']) && $package_data['manual_queries']) {
                            //         if (isset($package_data['query_path']) && $package_data['query_path'] != "") {
                            //             $sqlContent = File::get($fullUpdatePath . $package_data['query_path']);
                            //             $queries = explode(';', $sqlContent);

                            //             foreach ($queries as $query) {
                            //                 $query = trim($query);
                            //                 if (!empty($query)) {
                            //                     try {
                            //                         DB::statement($query);
                            //                     } catch (\Throwable $e) {
                            //                         // Handle any exceptions or errors
                            //                     }
                            //                 }
                            //             }
                            //         }
                            //     }

                            //     // Update version and finalize
                            //     $data = array('version' => $system_info['file_current_version'] ? $system_info['file_current_version'] : '1.0.0');
                            //     Updates::create($data);

                            //     File::deleteDirectory(public_path($updatePath));

                            //     // Clear application caches
                            //     Artisan::call('cache:clear');
                            //     Artisan::call('config:clear');
                            //     Artisan::call('route:clear');
                            //     Artisan::call('view:clear');
                            //     $response = [
                            //         'error' => false,
                            //         'message' => 'Congratulations! Version ' . $package_data['version'] . ' is successfully installed.',
                            //     ];

                            //     Session::flash('message', 'Congratulations! Version ' . $package_data['version'] . ' is successfully installed.');
                            //     return response()->json($response);
                            // }
                            if (!empty($package_data)) {
                                // Folders Creation - check if folders.json is set if yes then create folders listed in that file /
                                if (isset($package_data['folders']) && !empty($package_data['folders'])) {
                                    $jsonFilePath = $updatePath . $sub_directory . $package_data['folders'];

                                    if (file_exists($jsonFilePath)) {
                                        $lines_array = file_get_contents($jsonFilePath);

                                        if ($lines_array !== false && !empty($lines_array)) {
                                            $lines_array = json_decode($lines_array, true);

                                            if ($lines_array !== null) {
                                                foreach ($lines_array as $key => $line) {
                                                    $sourcePath = public_path($key);
                                                    $destination = base_path($line);

                                                    // Ensure directory existence
                                                    if (!is_dir($destination) && !file_exists($destination)) {
                                                        mkdir($destination, 0777, true);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                // Files Copy - check if files.json is set if yes then copy the files listed in that file /
                                if (isset($package_data['files']) && !empty($package_data['files'])) {
                                    // copy files from source to destination as set in the file /
                                    if (file_exists($updatePath . $sub_directory . $package_data['files'])) {
                                        $lines_array = file_get_contents($updatePath . $sub_directory . $package_data['files']);
                                        if (!empty($lines_array)) {
                                            $lines_array = json_decode($lines_array);
                                            foreach ($lines_array as $key => $line) {

                                                $sourcePath = public_path($updatePath) . $sub_directory . $key;
                                                $sourcePath = str_replace('/', DIRECTORY_SEPARATOR, $sourcePath);

                                                $destination = base_path($line);
                                                $destination = str_replace('/', DIRECTORY_SEPARATOR, $destination);
                                                $destinationDirectory = dirname($destination);

                                                if (!is_dir($destinationDirectory)) {
                                                    mkdir($destinationDirectory, 0755, true);
                                                }

                                                if (file_exists($sourcePath)) {
                                                    copy($sourcePath, $destination);
                                                }
                                            }
                                        }
                                    }
                                }
                                // ZIP Extraction - check if archives.json is set if yes then extract the files on destination as mentioned /
                                if (isset($package_data['archives']) && !empty($package_data['archives'])) {
                                    // extract the archives in the destination folder as set in the file /
                                    if (file_exists($updatePath . $sub_directory . $package_data['archives'])) {
                                        $lines_array = file_get_contents($updatePath . $sub_directory . $package_data['archives']);
                                        if (!empty($lines_array)) {
                                            $lines_array = json_decode($lines_array);
                                            $zip = new ZipArchive;
                                            foreach ($lines_array as $source => $destination) {
                                                // $source = $updatePath . $sub_directory . $source; // Full path to source file
                                                $destination = base_path($destination);
                                                $destination = str_replace('/', DIRECTORY_SEPARATOR, $destination); // Replace forward slashes with the correct directory separator
                                                $res = $zip->open(public_path($updatePath) . $sub_directory . $source);
                                                if ($res === TRUE) {
                                                    $zip->extractTo($destination);
                                                    $zip->close();
                                                }
                                            }
                                        }
                                    }
                                }


                                // run the migration if there is any /
                                $pathToMigrationDir = public_path($updatePath) . $sub_directory . 'update-files/database/migrations';
                                $pathToMigrationDir = str_replace('/', DIRECTORY_SEPARATOR, $pathToMigrationDir);
                                $pathToMigrations = 'public/' . $updatePath . $sub_directory . 'update-files/database/migrations';
                                $pathToMigrations = str_replace('/', DIRECTORY_SEPARATOR, $pathToMigrations);

                                if (is_dir($pathToMigrationDir)) {
                                    try {
                                        Artisan::call('migrate', ['--path' => $pathToMigrations]);
                                    } catch (\Throwable $e) {
                                        // Handle any exceptions or errors
                                    }
                                }
                                Log::info("Migration completed");
                                Log::info("Starting manual queries execution");
                                Log::info("Package Data: " . json_encode($package_data, JSON_PRETTY_PRINT));
                                if (isset($package_data['manual_queries']) && $package_data['manual_queries']) {
                                    if (isset($package_data['query_path']) && $package_data['query_path'] != "") {
                                        $sqlContent = File::get($fullUpdatePath . $package_data['query_path']);
                                        $queries = explode(';', $sqlContent);
                                        Log::info("Starting manual queries execution");
                                        Log::info("Query content: " . $sqlContent);
                                        foreach ($queries as $query) {
                                            $query = trim($query);
                                            if (!empty($query)) {
                                                try {
                                                    DB::statement($query);
                                                } catch (\Throwable $e) {
                                                    Log::error("Error executing query: " . $e->getMessage());
                                                    // Handle any exceptions or errors
                                                }
                                            }
                                        }
                                    }
                                }

                                $data = array('version' => $system_info['file_current_version']);
                                Updates::create($data);

                                $response = [
                                    'error' => false,
                                    'message' => 'Congratulations! Version ' . $package_data['version'] . ' is successfully installed.',
                                ];

                                Session::flash('message', 'Congratulations! Version ' . $package_data['version'] . ' is successfully installed.');

                                File::deleteDirectory(public_path($updatePath));

                                // Clear application caches
                                Artisan::call('cache:clear');
                                Artisan::call('config:clear');
                                Artisan::call('route:clear');
                                Artisan::call('view:clear');

                                return response()->json($response);
                            } else {
                                Session::flash('error', 'Invalid plugin installer file!. No package data found / missing package data.');
                                $response = [
                                    'error' => true,
                                    'message' => 'Invalid plugin installer file!. No package data found / missing package data.',
                                ];
                                File::deleteDirectory(public_path($updatePath));
                                return response()->json($response);
                            }
                        }
                    } else {
                        Session::flash('error', 'Invalid update file! It seems like you are trying to update the system using the wrong file.');
                        $response = [
                            'error' => true,
                            'message' => 'Invalid update file! It seems like you are trying to update the system using the wrong file.',
                        ];

                        File::deleteDirectory(public_path($updatePath));
                    }
                } else {
                    Session::flash('error', 'Extraction failed.');
                    $response['error'] = true;
                    $response['message'] = "Extraction failed.";
                }
            } else {
                Session::flash('error', $uploadData->getErrorString());
                $response['error'] = true;
                $response['message'] = $uploadData->getErrorString();
            }
        } else {
            Session::flash('error', 'You did not select a file to upload.');
            $response['error'] = true;
            $response['message'] = 'You did not select a file to upload.';
        }
        return response()->json($response);
    }

    public function storeSystemSetting(Request $request)
    {

        if (!auth()->check()) {
            return redirect('admin/login')->refresh();
        }
        $rules = [
            'app_name' => 'required',
            'support_number' => 'required',
            'support_email' => 'required',
            'storage_type' => 'required',
            'on_boarding_media_type' => 'required',
            'current_version_of_android_app' => 'required',
            'current_version_of_ios_app' => 'required',
            'current_version_of_android_app_for_seller' => 'required',
            'current_version_of_ios_app_for_seller' => 'required',
            'current_version_of_android_app_for_delivery_boy' => 'required',
            'current_version_of_ios_app_for_delivery_boy' => 'required',
            'system_timezone' => 'required',
            'minimum_cart_amount' => 'required',
            'maximum_item_allowed_in_cart' => 'required',
            'low_stock_limit' => 'required',
            'max_days_to_return_item' => 'required',
            'delivery_boy_bonus' => 'required',
            'tax_name' => 'required',
            'tax_number' => 'required',
        ];
        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }
        // Prepare the data to be stored
        $data = [
            'variable' => 'system_settings',
            'value' => json_encode([
                'app_name' => $request->app_name,
                'support_number' => $request->support_number,
                'support_email' => $request->support_email,
                'logo' => $request->logo,
                'favicon' => $request->favicon,
                'on_boarding_image' => isset($request->on_boarding_image) ? $request->on_boarding_image : '',
                'on_boarding_video' => isset($request->on_boarding_video) ? $request->on_boarding_video : '',
                'storage_type' => $request->storage_type,
                'on_boarding_media_type' => $request->on_boarding_media_type,
                'current_version_of_android_app' => $request->current_version_of_android_app,
                'current_version_of_ios_app' => $request->current_version_of_ios_app,
                'current_version_of_android_app_for_seller' => $request->current_version_of_android_app_for_seller,
                'current_version_of_ios_app_for_seller' => $request->current_version_of_ios_app_for_seller,
                'current_version_of_android_app_for_delivery_boy' => $request->current_version_of_android_app_for_delivery_boy,
                'current_version_of_ios_app_for_delivery_boy' => $request->current_version_of_ios_app_for_delivery_boy,
                'order_delivery_otp_system' => isset($request->order_delivery_otp_system) && $request->order_delivery_otp_system == "on" ? 1 : 0,
                'system_timezone' => $request->system_timezone,
                'minimum_cart_amount' => $request->minimum_cart_amount,
                'maximum_item_allowed_in_cart' => $request->maximum_item_allowed_in_cart,
                'low_stock_limit' => $request->low_stock_limit,
                'max_days_to_return_item' => $request->max_days_to_return_item,
                'delivery_boy_bonus' => $request->delivery_boy_bonus,
                'enable_cart_button_on_product_list_view' => isset($request->enable_cart_button_on_product_list_view) && $request->enable_cart_button_on_product_list_view == "on" ? 1 : 0,
                'version_system_status' => isset($request->version_system_status) && $request->version_system_status == "on" ? 1 : 0,
                'expand_product_image' => isset($request->expand_product_image) && $request->expand_product_image == "on" ? 1 : 0,
                'tax_name' => $request->tax_name,
                'tax_number' => $request->tax_number,
                'google' => isset($request->google) && $request->google == "on" ? 1 : 0,
                'facebook' => isset($request->facebook) && $request->facebook == "on" ? 1 : 0,
                'apple' => isset($request->apple) && $request->apple == "on" ? 1 : 0,
                'refer_and_earn_status' => isset($request->refer_and_earn_status) && $request->refer_and_earn_status == "on" ? 1 : 0,
                'minimum_refer_and_earn_amount' => $request->minimum_refer_and_earn_amount,
                'minimum_refer_and_earn_bonus' => $request->minimum_refer_and_earn_bonus,
                'refer_and_earn_method' => $request->refer_and_earn_method,
                'max_refer_and_earn_amount' => $request->max_refer_and_earn_amount,
                'number_of_times_bonus_given_to_customer' => $request->number_of_times_bonus_given_to_customer,
                'wallet_balance_status' => isset($request->wallet_balance_status) && $request->wallet_balance_status == "on" ? 1 : 0,
                'wallet_balance_amount' => $request->wallet_balance_amount,
                'authentication_method' => $request->authentication_method,
                'store_currency' => $request->store_currency,
                'single_seller_order_system' => isset($request->single_seller_order_system) && $request->single_seller_order_system == "on" ? 1 : 0,
                'customer_app_maintenance_status' => isset($request->customer_app_maintenance_status) && $request->customer_app_maintenance_status == "on" ? 1 : 0,
                'seller_app_maintenance_status' => isset($request->seller_app_maintenance_status) && $request->seller_app_maintenance_status == "on" ? 1 : 0,
                'delivery_boy_app_maintenance_status' => isset($request->delivery_boy_app_maintenance_status) && $request->delivery_boy_app_maintenance_status == "on" ? 1 : 0,
                'message_for_customer_app' => $request->message_for_customer_app,
                'message_for_seller_app' => $request->message_for_seller_app,
                'message_for_delivery_boy_app' => $request->message_for_delivery_boy_app,
                'sidebar_color' => $request->sidebar_color,
                'sidebar_type' => $request->sidebar_type,
                'navbar_fixed' => isset($request->navbar_fixed) && $request->navbar_fixed == "on" ? 1 : 0,
                'theme_mode' => isset($request->theme_mode) && $request->theme_mode == "on" ? 1 : 0,
            ], JSON_UNESCAPED_SLASHES),
        ];

        // Check if settings already exist in the database
        session()->put('system_settings', $data['value']);
        $setting_data = Setting::where('variable', 'system_settings')->first();
        if ($setting_data == null) {
            // Create a new record if no settings found
            $settings = Setting::create($data);
            $settings->save();
            if ($request->ajax()) {
                return response()->json([
                    'message' => labels('admin_labels.settings_inserted_successfully', 'Settings inserted successfully')
                ]);
            }
        } else {
            // Update the existing record with the new settings
            $setting_data->update($data);
            if ($request->ajax()) {
                return response()->json([
                    'message' => labels('admin_labels.settings_updated_successfully', 'Settings updated successfully')
                ]);
            }
        }
    }

    public function emailSettings()
    {
        $settings = getSettings('email_settings', true);
        $settings = json_decode($settings, true);

        return view('admin.pages.forms.email_settings', [
            'settings' => $settings
        ]);
    }

    public function storeEmailSetting(Request $request)
    {
        if (!auth()->check()) {
            return redirect('admin/login')->refresh();
        }
        $rules = [
            'email' => 'required',
            'password' => 'required',
            'smtp_host' => 'required',
            'smtp_port' => 'required',
            'email_content_type' => 'required',
            'smtp_encryption' => 'required',
        ];
        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }
        // Prepare the data to be stored
        $data = [
            'variable' => 'email_settings',
            'value' => json_encode([
                'email' => $request->email,
                'password' => $request->password,
                'smtp_host' => $request->smtp_host,
                'smtp_port' => $request->smtp_port,
                'email_content_type' => $request->email_content_type,
                'smtp_encryption' => $request->smtp_encryption,
            ], JSON_UNESCAPED_SLASHES),
        ];
        // Check if settings already exist in the database
        $setting_data = Setting::where('variable', 'email_settings')->first();
        if ($setting_data == null) {
            // Create a new record if no settings found
            $settings = Setting::create($data);
            $settings->save();
            if ($request->ajax()) {
                return response()->json([
                    'message' => labels('admin_labels.settings_inserted_successfully', 'Settings inserted successfully')
                ]);
            }
        } else {
            // Update the existing record with the new settings
            $setting_data->update($data);
            if ($request->ajax()) {
                return response()->json([
                    'message' => labels('admin_labels.settings_updated_successfully', 'Settings updated successfully')
                ]);
            }
        }
    }

    public function paymentSettings()
    {

        $settings = getSettings('payment_method', true);
        $settings = json_decode($settings, true);

        return view('admin.pages.forms.payment_settings', [
            'settings' => $settings
        ]);
    }
    public function storePaymentSetting(Request $request)
    {

        $validator = null;

        if (
            $request->phonepe_method == '0' && $request->paypal_method == '0' && $request->razorpay_method == '0'
            && $request->paystack_method == '0' && $request->stripe_method == '0'
            && $request->cod_method == '0'
        ) {
            $response = [
                'error' => true,
                'error_message' => 'Please select at least one payment method!!',
            ];
            return response()->json($response);
        }

        if ($request->phonepe_method == '1') {
            $rules = [
                'phonepe_mode' => 'required',
                'phonepe_marchant_id' => 'required',
                'phonepe_salt_index' => 'required',
                'phonepe_salt_key' => 'required',
            ];

            if ($response = validatePanelRequest($request, $rules)) {
                return $response;
            }
        }

        if ($request->paypal_method == '1') {
            $rules = [
                'paypal_mode' => 'required',
                'paypal_business_email' => 'required',
                'paypal_client_id' => 'required',
                'currency_code' => 'required',
            ];

            if ($response = validatePanelRequest($request, $rules)) {
                return $response;
            }
        }

        if ($request->razorpay_method == '1') {
            $rules = [
                'razorpay_key_id' => 'required',
                'razorpay_secret_key' => 'required',
                'razorpay_webhook_secret_key' => 'required',
                'currency_code' => 'required',
            ];

            if ($response = validatePanelRequest($request, $rules)) {
                return $response;
            }
        }

        if ($request->paystack_method == '1') {
            $rules = [
                'paystack_key_id' => 'required',
                'paystack_secret_key' => 'required',
            ];

            if ($response = validatePanelRequest($request, $rules)) {
                return $response;
            }
        }

        if ($request->stripe_method == '1') {
            $rules = [
                'stripe_payment_mode' => 'required',
                'stripe_publishable_key' => 'required',
                'stripe_secret_key' => 'required',
                'stripe_webhook_secret_key' => 'required',
                'stripe_currency_code' => 'required',
            ];

            if ($response = validatePanelRequest($request, $rules)) {
                return $response;
            }
        }

        // Prepare the data to be stored
        $data = [
            'variable' => 'payment_method',
            'value' => json_encode([
                'phonepe_method' => isset($request->phonepe_method) && $request->phonepe_method == "1" ? 1 : 0,
                'phonepe_mode' => $request->phonepe_mode,
                'phonepe_marchant_id' => $request->phonepe_marchant_id,
                'phonepe_salt_index' => $request->phonepe_salt_index,
                'phonepe_salt_key' => $request->phonepe_salt_key,
                'paypal_method' => isset($request->paypal_method) && $request->paypal_method == "1" ? 1 : 0,
                'paypal_mode' => $request->paypal_mode,
                'paypal_business_email' => $request->paypal_business_email,
                'paypal_client_id' => $request->paypal_client_id,
                'currency_code' => $request->currency_code,
                'razorpay_method' => isset($request->razorpay_method) && $request->razorpay_method == "1" ? 1 : 0,
                'razorpay_mode' => $request->razorpay_mode,
                'razorpay_key_id' => $request->razorpay_key_id,
                'razorpay_secret_key' => $request->razorpay_secret_key,
                'razorpay_webhook_secret_key' => $request->razorpay_webhook_secret_key,
                'paystack_method' => isset($request->paystack_method) && $request->paystack_method == "1" ? 1 : 0,
                'paystack_key_id' => $request->paystack_key_id,
                'paystack_secret_key' => $request->paystack_secret_key,
                'stripe_method' => isset($request->stripe_method) && $request->stripe_method == "1" ? 1 : 0,
                'stripe_payment_mode' => $request->stripe_payment_mode,
                'stripe_publishable_key' => $request->stripe_publishable_key,
                'stripe_secret_key' => $request->stripe_secret_key,
                'stripe_webhook_secret_key' => $request->stripe_webhook_secret_key,
                'stripe_currency_code' => $request->stripe_currency_code,
                'notes' => $request->notes,
                'cod_method' => isset($request->cod_method) && $request->cod_method == "1" ? 1 : 0,
            ], JSON_UNESCAPED_SLASHES),
        ];
        // Check if settings already exist in the database
        $setting_data = Setting::where('variable', 'payment_method')->first();
        if ($setting_data == null) {
            // Create a new record if no settings found
            $settings = Setting::create($data);


            $settings->save();

            if ($request->ajax()) {
                return response()->json([
                    'message' => labels('admin_labels.settings_inserted_successfully', 'Settings inserted successfully')
                ]);
            }
        } else {
            // Update the existing record with the new settings
            $setting_data->update($data);

            if ($request->ajax()) {
                return response()->json([
                    'message' => labels('admin_labels.settings_updated_successfully', 'Settings updated successfully')
                ]);
            }
        }
    }

    public function shippingSettings()
    {
        $settings = getSettings('shipping_method', true);
        $settings = json_decode($settings, true);
        return view('admin.pages.forms.shipping_settings', [
            'settings' => $settings
        ]);
    }
    public function storeShippingSettings(Request $request)
    {

        if (!isset($request->local_shipping_method) && !isset($request->shiprocket_shipping_method)) {
            if ($request->ajax()) {
                return response()->json(['message' => 'Please select shipping method']);
            }
        }
        if ($request->shiprocket_shipping_method === "on") {
            $rules = [
                'email' => 'required',
                'password' => 'required',
                'webhook_token' => 'required',
            ];

            if ($response = validatePanelRequest($request, $rules)) {
                return $response;
            }
        }

        if ($request->standard_shipping_free_delivery === "on") {
            $rules = [
                'minimum_free_delivery_order_amount' => 'required',
            ];

            if ($response = validatePanelRequest($request, $rules)) {
                return $response;
            }
        }

        // Prepare the data to be stored
        $data = [
            'variable' => 'shipping_method',
            'value' => json_encode([
                'local_shipping_method' => isset($request->local_shipping_method) && $request->local_shipping_method == "on" ? 1 : 0,
                'shiprocket_shipping_method' => isset($request->shiprocket_shipping_method) && $request->shiprocket_shipping_method == "on" ? 1 : 0,
                'email' => $request->email,
                'password' => $request->password,
                'webhook_token' => $request->webhook_token,
                'standard_shipping_free_delivery' => isset($request->standard_shipping_free_delivery) && $request->standard_shipping_free_delivery == "on" ? 1 : 0,
                'minimum_free_delivery_order_amount' => $request->minimum_free_delivery_order_amount,
            ], JSON_UNESCAPED_SLASHES),
        ];
        // Check if settings already exist in the database
        $setting_data = Setting::where('variable', 'shipping_method')->first();
        if ($setting_data == null) {
            // Create a new record if no settings found
            $settings = Setting::create($data);
            $settings->save();
            if ($request->ajax()) {
                return response()->json([
                    'message' => labels('admin_labels.settings_inserted_successfully', 'Settings inserted successfully')
                ]);
            }
        } else {
            // Update the existing record with the new settings
            $setting_data->update($data);
            if ($request->ajax()) {
                return response()->json([
                    'message' => labels('admin_labels.settings_updated_successfully', 'Settings updated successfully')
                ]);
            }
        }
    }

    public function currencySettings()
    {
        $currencies = Currency::where('status', 1)->get();
        $app_id = getSettings('exchange_rate_app_id', true);
        $app_id = json_decode($app_id, true);

        return view('admin.pages.forms.currency_settings', ['currencies' => $currencies, 'app_id' => $app_id]);
    }

    public function storeCurrencySetting(Request $request)
    {
        $rules = [
            'name' => 'required',
            'code' => 'required',
            'symbol' => 'required',
            'exchange_rate' => 'required',
        ];

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }
        $currency = new Currency();
        $currency->name = $request->name;
        $currency->code = $request->code;
        $currency->symbol = $request->symbol;
        $currency->exchange_rate = $request->exchange_rate;
        $currency->status = 1;
        $currency->save();

        if ($request->ajax()) {
            return response()->json([
                'message' => labels('admin_labels.currency_added_successfully', 'Currency added successfully')
            ]);
        }
    }

    public function storeExchangeRateAapId(Request $request)
    {
        $rules = [
            'exchange_rate_app_id' => 'required',
        ];

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }
        $data = [
            'variable' => 'exchange_rate_app_id',
            'value' => json_encode([
                'exchange_rate_app_id' => isset($request->exchange_rate_app_id) && $request->exchange_rate_app_id != "" ? $request->exchange_rate_app_id : "",
            ], JSON_UNESCAPED_SLASHES),
        ];
        $exchange_rate_app_id = Setting::where('variable', 'exchange_rate_app_id')->first();
        if ($exchange_rate_app_id == null) {
            $settings = Setting::create($data);
            $settings->save();
            if ($request->ajax()) {
                return response()->json([
                    'message' => labels('admin_labels.data_inserted_successfully', 'Data inserted successfully')
                ]);
            }
        } else {
            $exchange_rate_app_id->update($data);
            if ($request->ajax()) {
                return response()->json([
                    'message' => labels('admin_labels.data_updated_successfully', 'Data updated successfully')
                ]);
            }
        }
    }




    public function updateExchangeRates($app_id)
    {
        $api_response = $this->get_exchange_rates($app_id);

        if ($api_response) {

            Currency::update_exchange_rate_from_api($api_response['rates'], $api_response['base']);

            return response()->json([
                'message' => labels('admin_labels.exchange_rates_updated_successfully', 'Exchange rates updated successfully')
            ]);
        }

        return response()->json([
            'error' => labels('admin_labels.unable_to_fetch_api_response', 'Unable to fetch API response')
        ], 500);
    }

    // Your existing get_exchange_rates function
    public function getExchangeRates($app_id)
    {
        $app_id = getSettings('exchange_rate_app_id', true);
        $app_id = json_decode($app_id, true);

        $app_id = $app_id['exchange_rate_app_id'];

        $url = "https://openexchangerates.org/api/latest.json?app_id={$app_id}";

        try {
            $response = Http::get($url);
            if ($response->successful()) {
                $data = $response->json();
                return $data;
            } else {
                return null;
            }
        } catch (\Exception $e) {
            return null;
        }
    }

    public function setDefaultCurrency(Request $request)
    {
        $currency_id = $request->input('currency_id');

        // First, update all currencies to 'is_default' = 0 (false)
        Currency::query()->update(['is_default' => 0]);

        // Then, set the selected currency to 'is_default' = 1 (true)
        $currency = Currency::find($currency_id);
        if ($currency) {
            $currency->is_default = 1;
            $currency->save();
        }

        if ($request->ajax()) {
            return response()->json([
                'message' => labels('admin_labels.default_currency_set_successfully', 'Default currency set successfully')
            ]);
        }
    }

    public function currencyList(Request $request)
    {
        $search = trim(request('search'));
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : "0";
        $limit = (request('limit')) ? request('limit') : "10";

        $city_data = Currency::when($search, function ($query) use ($search) {
            return $query->where('name', 'like', '%' . $search . '%');
        });

        $total = $city_data->count();

        // Use Paginator to handle the server-side pagination
        $currencies = $city_data->orderBy($sort, $order)->offset($offset)
            ->limit($limit)
            ->get();

        // Prepare the data for the "Actions" field
        $data = $currencies->map(function ($c) {
            $delete_url = route('currency.destroy', $c->id);
            $action = '<div class="dropdown bootstrap-table-dropdown">
                <a href="#" class="text-dark" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="bx bx-dots-horizontal-rounded"></i>
                </a>

                <div class="dropdown-menu table_dropdown" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item edit-currency dropdown_menu_items" data-id="' . $c->id . '"><i class="bx bx-pencil mx-2"></i> Edit</a>
                    <a class="dropdown-item delete-data dropdown_menu_items" data-url="' . $delete_url . '"><i class="bx bx-trash mx-2"></i> Delete</a>
                </div>
            </div>';

            return [
                'id' => $c->id,
                'name' => $c->name . '<label class="badge bg-success">' . ($c->is_default == 1 ? "Default" : "") . '</label>',
                'symbol' => $c->symbol,
                'exchange_rate' => $c->exchange_rate,
                'status' => '<select class="form-select status_dropdown change_toggle_status ' . ($c->status == 1 ? 'active_status' : 'inactive_status') . '" data-id="' . $c->id . '" data-url="/admin/currency/update_status/' . $c->id . '" aria-label="">
                  <option value="1" ' . ($c->status == 1 ? 'selected' : '') . '>Active</option>
                  <option value="0" ' . ($c->status == 0 ? 'selected' : '') . '>Deactive</option>
              </select>',
                'operate' => $action,
            ];
        });

        return response()->json([
            "rows" => $data,
            "total" => $total,
        ]);
    }

    public function currencyDestroy($id)
    {
        $currency = Currency::find($id);

        if (!$currency) {
            return response()->json(['error' => labels('admin_labels.data_not_found', 'Data Not Found')]);
        }

        if ($currency->is_default) {
            return response()->json([
                'error' => labels('admin_labels.cannot_delete_default_currency', 'You cannot delete the default currency. Please set another currency as default before deleting this.')
            ]);
        }

        if ($currency->delete()) {
            return response()->json([
                'error' => false,
                'message' => labels('admin_labels.currency_deleted_successfully', 'Currency deleted successfully!')
            ]);
        }
    }


    public function updateCurrencyStatus($id)
    {
        // Find the currency by ID
        $currency = Currency::findOrFail($id);

        // Check if the currency is marked as default (is_default = 1)
        if ($currency->is_default == 1) {
            // Return a response indicating that default currencies cannot be disabled
            return response()->json([
                'status_error' => labels('admin_labels.default_currency_cannot_be_disabled', 'Default currency cannot be disabled.')
            ]);
        }

        // Toggle the status between '1' (enabled) and '0' (disabled)
        $currency->status = $currency->status == '1' ? '0' : '1';
        $currency->save();

        // Return a success message
        return response()->json([
            'success' => labels('admin_labels.status_updated_successfully', 'Status updated successfully.')
        ]);
    }



    // notification and contacct setting

    public function notificationAndContactSettings()
    {
        $firebase_project_id = Setting::where('variable', 'firebase_project_id')
            ->value('value');
        $contact_us = getSettings('contact_us', true);
        $contact_us = json_decode($contact_us, true);

        $about_us = getSettings('about_us', true);
        $about_us = json_decode($about_us, true);

        return view('admin.pages.forms.notification_and_contact_settings', [
            'firebase_project_id' => $firebase_project_id,
            'contact_us' => $contact_us,
            'about_us' => $about_us
        ]);
    }

    public function storeNotificationSettings(Request $request)
    {
        // Validate the request data

        $rules = [
            'firebase_project_id' => 'required|string',
            'service_account_file' => 'nullable|mimes:json',
        ];

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }
        // Store Firebase Project ID in settings table
        DB::table('settings')->updateOrInsert(
            ['variable' => 'firebase_project_id'],
            ['value' => $request['firebase_project_id'], 'updated_at' => now()]
        );

        // Check if a file is uploaded
        if ($request->hasFile('service_account_file')) {
            // Get the uploaded file
            $file = $request->file('service_account_file');

            // Get the original file name
            $fileName = $file->getClientOriginalName();

            // Define the destination path
            $destinationPath = storage_path('app/public');

            // Move the file to the specified directory
            $file->move($destinationPath, $fileName);

            // Store the original file name in the settings table
            DB::table('settings')->updateOrInsert(
                ['variable' => 'service_account_file'],
                ['value' => $fileName, 'updated_at' => now()]
            );
        }

        // Redirect with a success message
        if ($request->ajax()) {
            return response()->json([
                'message' => labels('admin_labels.settings_updated_successfully', 'Settings updated successfully')
            ]);
        }
    }




    public function storeContactUs(Request $request)
    {
        return $this->storePoliciesAndContactSetting($request, 'contact_us');
    }

    public function storeAboutUs(Request $request)
    {
        return $this->storePoliciesAndContactSetting($request, 'about_us');
    }


    // system policies

    public function systemPolicies()
    {
        $privacy_policy = getSettings('privacy_policy', true);
        $privacy_policy = json_decode($privacy_policy, true);

        $shipping_policy = getSettings('shipping_policy', true);
        $shipping_policy = json_decode($shipping_policy, true);

        $terms_and_conditions = getSettings('terms_and_conditions', true);
        $terms_and_conditions = json_decode($terms_and_conditions, true);

        $return_policy = getSettings('return_policy', true);
        $return_policy = json_decode($return_policy, true);

        return view('admin.pages.forms.system_policies', [
            'privacy_policy' => $privacy_policy,
            'shipping_policy' => $shipping_policy,
            'return_policy' => $return_policy,
            'terms_and_conditions' => $terms_and_conditions
        ]);
    }

    public function storePrivacyPolicy(Request $request)
    {
        return $this->storePoliciesAndContactSetting($request, 'privacy_policy');
    }

    public function storeTermsAndCondition(Request $request)
    {
        return $this->storePoliciesAndContactSetting($request, 'terms_and_conditions');
    }

    public function storeShippingPolicy(Request $request)
    {
        return $this->storePoliciesAndContactSetting($request, 'shipping_policy');
    }

    public function storeReturnPolicy(Request $request)
    {
        return $this->storePoliciesAndContactSetting($request, 'return_policy');
    }

    // system policies pages

    public function privacyPolicy()
    {
        $privacy_policy = getSettings('privacy_policy', true);
        $privacy_policy = json_decode($privacy_policy, true);

        $setting = getSettings('system_settings', true);
        $setting = json_decode($setting, true);

        return view('admin.pages.views.privacy_policy', [
            'privacy_policy' => $privacy_policy,
            'setting' => $setting
        ]);
    }
    public function shippingPolicy()
    {
        $shipping_policy = getSettings('shipping_policy', true);
        $shipping_policy = json_decode($shipping_policy, true);

        $setting = getSettings('system_settings', true);
        $setting = json_decode($setting, true);

        return view('admin.pages.views.shipping_policy', [
            'shipping_policy' => $shipping_policy,
            'setting' => $setting
        ]);
    }
    public function termsAndConditions()
    {
        $terms_and_conditions = getSettings('terms_and_conditions', true);
        $terms_and_conditions = json_decode($terms_and_conditions, true);

        $setting = getSettings('system_settings', true);
        $setting = json_decode($setting, true);

        return view('admin.pages.views.terms_and_conditions', [
            'terms_and_conditions' => $terms_and_conditions,
            'setting' => $setting
        ]);
    }
    public function returnPolicy()
    {
        $return_policy = getSettings('return_policy', true);
        $return_policy = json_decode($return_policy, true);

        $setting = getSettings('system_settings', true);
        $setting = json_decode($setting, true);

        return view('admin.pages.views.return_policy', [
            'return_policy' => $return_policy,
            'setting' => $setting
        ]);
    }

    // admin and seller policies

    public function adminAndSellerPolicies()
    {
        $admin_privacy_policy = getSettings('admin_privacy_policy', true);
        $admin_privacy_policy = json_decode($admin_privacy_policy, true);

        $admin_terms_and_conditions = getSettings('admin_terms_and_conditions', true);
        $admin_terms_and_conditions = json_decode($admin_terms_and_conditions, true);

        $seller_privacy_policy = getSettings('seller_privacy_policy', true);
        $seller_privacy_policy = json_decode($seller_privacy_policy, true);

        $seller_terms_and_conditions = getSettings('seller_terms_and_conditions', true);
        $seller_terms_and_conditions = json_decode($seller_terms_and_conditions, true);

        return view('admin.pages.forms.admin_and_seller_policies', [
            'admin_privacy_policy' => $admin_privacy_policy,
            'admin_terms_and_conditions' => $admin_terms_and_conditions,
            'seller_terms_and_conditions' => $seller_terms_and_conditions,
            'seller_privacy_policy' => $seller_privacy_policy
        ]);
    }

    public function storeAdminPrivacyPolicy(Request $request)
    {
        return $this->storePoliciesAndContactSetting($request, 'admin_privacy_policy');
    }

    public function storeAdminTermsAndConditions(Request $request)
    {
        return $this->storePoliciesAndContactSetting($request, 'admin_terms_and_conditions');
    }

    public function storeSellerPrivacyPolicy(Request $request)
    {
        return $this->storePoliciesAndContactSetting($request, 'seller_privacy_policy');
    }

    public function storeSellerTermsAndConditions(Request $request)
    {
        return $this->storePoliciesAndContactSetting($request, 'seller_terms_and_conditions');
    }


    // seller policies page

    public function sellerTermsAndCondition()
    {
        $terms_and_conditions = getSettings('seller_terms_and_conditions', true);
        $terms_and_conditions = json_decode($terms_and_conditions, true);

        $setting = getSettings('system_settings', true);
        $setting = json_decode($setting, true);
        return view('admin.pages.views.terms_and_conditions', [
            'terms_and_conditions' => $terms_and_conditions,
            'setting' => $setting
        ]);
    }
    public function sellerPrivacyPolicy()
    {
        $privacy_policy = getSettings('seller_privacy_policy', true);
        $privacy_policy = json_decode($privacy_policy, true);

        $setting = getSettings('system_settings', true);
        $setting = json_decode($setting, true);

        return view('admin.pages.views.privacy_policy', [
            'privacy_policy' => $privacy_policy,
            'setting' => $setting
        ]);
    }

    // delivery boy policies

    public function deliveryBoyPolicies()
    {
        $delivery_boy_privacy_policy = getSettings('delivery_boy_privacy_policy', true);
        $delivery_boy_privacy_policy = json_decode($delivery_boy_privacy_policy, true);

        $delivery_boy_terms_and_conditions = getSettings('delivery_boy_terms_and_conditions', true);
        $delivery_boy_terms_and_conditions = json_decode($delivery_boy_terms_and_conditions, true);

        return view('admin.pages.forms.delivery_boy_policies', [
            'delivery_boy_privacy_policy' => $delivery_boy_privacy_policy,
            'delivery_boy_terms_and_conditions' => $delivery_boy_terms_and_conditions
        ]);
    }

    public function storeDeliveryBoyPrivacyPolicy(Request $request)
    {
        return $this->storePoliciesAndContactSetting($request, 'delivery_boy_privacy_policy');
    }

    public function storeDeliveryBoyTermsAndConditions(Request $request)
    {
        return $this->storePoliciesAndContactSetting($request, 'delivery_boy_terms_and_conditions');
    }

    // delivery boy  policies page

    public function deliveryBoyTermsAndCondition()
    {
        $terms_and_conditions = getSettings('delivery_boy_terms_and_conditions', true);
        $terms_and_conditions = json_decode($terms_and_conditions, true);

        $setting = getSettings('system_settings', true);
        $setting = json_decode($setting, true);
        return view('admin.pages.views.terms_and_conditions', [
            'terms_and_conditions' => $terms_and_conditions,
            'setting' => $setting
        ]);
    }
    public function deliveryBoyPrivacyPolicy()
    {
        $privacy_policy = getSettings('delivery_boy_privacy_policy', true);
        $privacy_policy = json_decode($privacy_policy, true);

        $setting = getSettings('system_settings', true);
        $setting = json_decode($setting, true);
        return view('admin.pages.views.privacy_policy', [
            'privacy_policy' => $privacy_policy,
            'setting' => $setting
        ]);
    }

    // general function for store policies and contact setting

    public function storePoliciesAndContactSetting(Request $request, $variable_name)
    {
        $validator = Validator::make($request->all(), [
            $variable_name => 'required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            if ($request->ajax()) {
                return response()->json(['errors' => $errors->all()], 422);
            }
            return redirect()->back()->withErrors($errors)->withInput();
        }

        $data = [
            'variable' => $variable_name,
            'value' => json_encode([
                $variable_name => isset($request->$variable_name) ? $request->$variable_name : '',
            ], JSON_UNESCAPED_SLASHES),
        ];

        $setting_data = Setting::where('variable', $variable_name)->first();
        if ($setting_data == null) {
            $settings = Setting::create($data);
            $settings->save();
            if ($request->ajax()) {
                return response()->json([
                    'message' => labels('admin_labels.settings_inserted_successfully', 'Settings inserted successfully')
                ]);
            }
        } else {
            $setting_data->update($data);
            if ($request->ajax()) {
                return response()->json([
                    'message' => labels('admin_labels.settings_updated_successfully', 'Settings updated successfully')
                ]);
            }
        }
    }

    public function webSettings()
    {

        $web_settings = getSettings('web_settings', true);
        $web_settings = json_decode($web_settings, true);

        $firebase_settings = getSettings('firebase_settings', true);
        $firebase_settings = json_decode($firebase_settings, true);
        return view('admin.pages.forms.web_settings', [
            'web_settings' => $web_settings,
            'firebase_settings' => $firebase_settings
        ]);
    }

    public function firebase()
    {
        $firebase_settings = getSettings('firebase_settings');
        $firebase_settings = json_decode($firebase_settings, true);
        // $firebase_settings = json_decode($firebase_settings['value'], true);
        if (isset($firebase_settings['value'])) {
            $firebase_settings = json_decode($firebase_settings['value'], true);
        }
        return view('admin.pages.forms.firebase_setting', [
            'firebase_settings' => $firebase_settings
        ]);
    }

    public function general_settings()
    {
        $web_settings = getSettings('web_settings', true);
        $web_settings = json_decode($web_settings, true);

        $firebase_settings = getSettings('firebase_settings', true);
        $firebase_settings = json_decode($firebase_settings, true);
        return view('admin.pages.forms.general_settings', [
            'web_settings' => $web_settings,
            'firebase_settings' => $firebase_settings
        ]);
    }

    public function pwa_settings()
    {
        $pwa_settings = getSettings('pwa_settings', true);
        $pwa_settings = json_decode($pwa_settings, true);

        $firebase_settings = getSettings('pwa_settings', true);
        $firebase_settings = json_decode($firebase_settings, true);
        return view('admin.pages.forms.pwa_settings', [
            'pwa_settings' => $pwa_settings,
        ]);
    }
    public function storePwaSettings(Request $request)
    {
        $rules = [
            'name' => 'required',
            'short_name' => 'required',
            'theme_color' => 'required',
            'background_color' => 'required',
            'description' => 'required',
            'logo' => 'required'
        ];

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }

        $data = [
            'variable' => 'pwa_settings',
            'value' => json_encode([
                'name' => $request->name,
                'short_name' => $request->short_name,
                'theme_color' => $request->theme_color,
                'background_color' => $request->background_color,
                'description' => $request->description,
                'logo' => $request->logo,
            ], JSON_UNESCAPED_SLASHES),
        ];
        // Check if settings already exist in the database
        $setting_data = Setting::where('variable', 'pwa_settings')->first();
        session()->put("pwa_settings", json_encode($data));
        if ($setting_data == null) {
            $settings = Setting::create($data);
            $settings->save();
            if ($request->ajax()) {
                return response()->json([
                    'message' => labels('admin_labels.settings_inserted_successfully', 'Settings inserted successfully')
                ]);
            }
        } else {
            // Update the existing record with the new settings
            $setting_data->update($data);
            if ($request->ajax()) {
                return response()->json([
                    'message' => labels('admin_labels.settings_updated_successfully', 'Settings updated successfully')
                ]);
            }
        }
    }
    public function storeWebSettings(Request $request)
    {
        $rules = [
            'site_title' => 'required',
            'support_number' => 'required',
            'support_email' => 'required',
            'copyright_details' => 'required',
            'address' => 'required',
            'app_short_description' => 'required',
            'map_iframe' => 'required',
            'meta_keywords' => 'required',
            'meta_description' => 'required',
            'logo' => 'required',
            'favicon' => 'required',
        ];
        if ($request->app_download_section === 'on') {
            $rules['app_download_section_title'] = 'required|string';
            $rules['app_download_section_tagline'] = 'required|string';
            $rules['app_download_section_short_description'] = 'required|string';
            $rules['app_download_section_playstore_url'] = 'required';
            $rules['app_download_section_appstore_url'] = 'required';
        }

        if ($request->shipping_mode === 'on') {
            $rules['shipping_title'] = 'required|string';
            $rules['shipping_description'] = 'required|string';
        }

        if ($request->return_mode === 'on') {
            $rules['return_title'] = 'required|string';
            $rules['return_description'] = 'required|string';
        }

        if ($request->support_mode === 'on') {
            $rules['support_title'] = 'required|string';
        }

        if ($request->safety_security_mode === 'on') {
            $rules['safety_security_title'] = 'required|string';
        }

        $messages = [
            'app_download_section_title.required' => 'App download section title is required.',
            'app_download_section_tagline.required' => 'App download section tagline is required.',
            'app_download_section_short_description.required' => 'App download section short description is required.',
            'app_download_section_playstore_url.required' => 'Playstore URL is required.',
            'app_download_section_appstore_url.required' => 'Appstore URL is required.',
        ];

        $validationResponse = validatePanelRequest($request, $rules, $messages);
        if ($validationResponse !== null) {
            return $validationResponse;
        }
        // Prepare the data to be stored
        $data = [
            'variable' => 'web_settings',
            'value' => json_encode([
                'site_title' => $request->site_title,
                'support_number' => $request->support_number,
                'support_email' => $request->support_email,
                'copyright_details' => $request->copyright_details,
                'logo' => $request->logo,
                'favicon' => $request->favicon,
                'address' => $request->address,
                'app_short_description' => $request->app_short_description,
                'map_iframe' => $request->map_iframe,
                'meta_keywords' => $request->meta_keywords,
                'meta_description' => $request->meta_description,
                'app_download_section' => isset($request->app_download_section) && $request->app_download_section == "on" ? 1 : 0,
                'app_download_section_title' => $request->app_download_section_title,
                'app_download_section_tagline' => $request->app_download_section_tagline,
                'app_download_section_short_description' => $request->app_download_section_short_description,
                'app_download_section_playstore_url' => $request->app_download_section_playstore_url,
                'app_download_section_appstore_url' => $request->app_download_section_appstore_url,
                'twitter_link' => $request->twitter_link,
                'facebook_link' => $request->facebook_link,
                'instagram_link' => $request->instagram_link,
                'youtube_link' => $request->youtube_link,
                'shipping_mode' => isset($request->shipping_mode) && $request->shipping_mode == "on" ? 1 : 0,
                'shipping_title' => $request->shipping_title,
                'shipping_description' => $request->shipping_description,
                'return_mode' => isset($request->return_mode) && $request->return_mode == "on" ? 1 : 0,
                'return_title' => $request->return_title,
                'return_description' => $request->return_description,
                'support_mode' => isset($request->support_mode) && $request->support_mode == "on" ? 1 : 0,
                'support_title' => $request->support_title,
                'support_description' => $request->support_description,
                'safety_security_mode' => isset($request->safety_security_mode) && $request->safety_security_mode == "on" ? 1 : 0,
                'safety_security_title' => $request->safety_security_title,
                'safety_security_description' => $request->safety_security_description,
            ], JSON_UNESCAPED_SLASHES),
        ];
        // Check if settings already exist in the database
        $setting_data = Setting::where('variable', 'web_settings')->first();
        if ($setting_data == null) {
            // Create a new record if no settings found
            $settings = Setting::create($data);
            $settings->save();
            if ($request->ajax()) {
                return response()->json([
                    'message' => labels('admin_labels.settings_inserted_successfully', 'Settings inserted successfully')
                ]);
            }
        } else {
            // Update the existing record with the new settings
            $setting_data->update($data);
            if ($request->ajax()) {
                return response()->json([
                    'message' => labels('admin_labels.settings_updated_successfully', 'Settings updated successfully')
                ]);
            }
        }
    }
    public function storeFirebaseSettings(Request $request)
    {

        $rules = [
            'apiKey' => 'required',
            'authDomain' => 'required',
            'databaseURL' => 'required',
            'projectId' => 'required',
            'messagingSenderId' => 'required',
            'appId' => 'required',
            'storageBucket' => 'required',
            'measurementId' => 'required',
        ];

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }
        // Prepare the data to be stored
        $data = [
            'variable' => 'firebase_settings',
            'value' => json_encode([
                'apiKey' => $request->apiKey,
                'authDomain' => $request->authDomain,
                'databaseURL' => $request->databaseURL,
                'projectId' => $request->projectId,
                'storageBucket' => $request->storageBucket,
                'messagingSenderId' => $request->messagingSenderId,
                'appId' => $request->appId,
                'measurementId' => $request->measurementId,
                'google_client_id' => $request->google_client_id,
                'google_client_secret' => $request->google_client_secret,
                'google_redirect_url' => $request->google_redirect_url,
                'facebook_client_id' => $request->facebook_client_id,
                'facebook_client_secret' => $request->facebook_client_secret,
                'facebook_redirect_url' => $request->facebook_redirect_url,
            ], JSON_UNESCAPED_SLASHES),
        ];
        // Check if settings already exist in the database
        $setting_data = Setting::where('variable', 'firebase_settings')->first();
        session()->put("firebase_settings", json_encode($data));
        if ($setting_data == null) {
            $settings = Setting::create($data);
            $settings->save();
            if ($request->ajax()) {
                return response()->json([
                    'message' => labels('admin_labels.settings_inserted_successfully', 'Settings inserted successfully')
                ]);
            }
        } else {
            // Update the existing record with the new settings
            $setting_data->update($data);
            if ($request->ajax()) {
                return response()->json([
                    'message' => labels('admin_labels.settings_updated_successfully', 'Settings updated successfully')
                ]);
            }
        }
    }

    public function editCurrency($id)
    {
        $currency = Currency::find($id);


        if (!$currency) {
            return response()->json(['error' => true, 'message' => labels('admin_labels.data_not_found', 'Data Not Found')], 404);
        }

        return response()->json($currency);
    }

    public function updateCurrency(Request $request, $id)
    {

        $currency = Currency::find($id);
        if (!$currency) {
            return response()->json(['error' => true, 'message' => labels('admin_labels.data_not_found', 'Data Not Found')], 404);
        } else {
            $rules = [
                'code' => 'required',
                'symbol' => 'required',
                'name' => 'required',
                'exchange_rate' => 'required',
            ];

            if ($response = validatePanelRequest($request, $rules)) {
                return $response;
            }
            $currency->code = $request->input('code');
            $currency->symbol = $request->input('symbol');
            $currency->name = $request->input('name');
            $currency->exchange_rate = $request->input('exchange_rate');

            $currency->save();

            if ($request->ajax()) {
                return response()->json(['message' => 'Currency updated successfully']);
            }
        }
    }


    private function getSettingsAndPolicy($policyName)
    {
        $setting = json_decode(getSettings('system_settings', true), true);
        $policy = json_decode(getSettings($policyName, true), true);

        return ['setting' => $setting, $policyName => $policy];
    }

    public function privacy_policy()
    {
        return view('admin.pages.views.privacy_policy', $this->getSettingsAndPolicy('privacy_policy'));
    }
    public function delivery_boy_privacy_policy()
    {
        return view('admin.pages.views.delivery_boy_privacy_policy', $this->getSettingsAndPolicy('delivery_boy_privacy_policy'));
    }

    public function terms_and_conditions()
    {
        return view('admin.pages.views.terms_and_conditions', $this->getSettingsAndPolicy('terms_and_conditions'));
    }
    public function delivery_boy_terms_and_conditions()
    {
        return view('admin.pages.views.delivery_boy_terms_and_conditions', $this->getSettingsAndPolicy('delivery_boy_terms_and_conditions'));
    }

    public function shipping_policy()
    {
        return view('admin.pages.views.shipping_policy', $this->getSettingsAndPolicy('shipping_policy'));
    }

    public function return_policy()
    {
        return view('admin.pages.views.return_policy', $this->getSettingsAndPolicy('return_policy'));
    }

    public function pusherSetting()
    {
        $settings = getSettings('pusher_settings', true);
        $settings = json_decode($settings, true);

        return view('admin.pages.forms.pusher_setting', [
            'settings' => $settings
        ]);
    }

    public function storePusherSetting(Request $request)
    {
        if (!auth()->check()) {
            return redirect('admin/login')->refresh();
        }
        $rules = [
            'pusher_app_cluster' => 'required',
            'pusher_app_secret' => 'required',
            'pusher_app_key' => 'required',
            'pusher_app_id' => 'required',
            'pusher_channel_name' => 'required',
        ];

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }
        $data = [
            'variable' => 'pusher_settings',
            'value' => json_encode([
                'pusher_app_cluster' => $request->pusher_app_cluster,
                'pusher_scheme' => $request->pusher_scheme,
                'pusher_port' => $request->pusher_port,
                'pusher_app_secret' => $request->pusher_app_secret,
                'pusher_app_key' => $request->pusher_app_key,
                'pusher_app_id' => $request->pusher_app_id,
                'pusher_channel_name' => $request->pusher_channel_name,
            ], JSON_UNESCAPED_SLASHES),
        ];
        // Check if settings already exist in the database
        $setting_data = Setting::where('variable', 'pusher_settings')->first();
        if ($setting_data == null) {
            // Create a new record if no settings found
            $settings = Setting::create($data);
            $settings->save();
            if ($request->ajax()) {
                return response()->json([
                    'message' => labels('admin_labels.settings_inserted_successfully', 'Settings inserted successfully')
                ]);
            }
        } else {
            // Update the existing record with the new settings
            $setting_data->update($data);
            if ($request->ajax()) {
                return response()->json([
                    'message' => labels('admin_labels.settings_updated_successfully', 'Settings updated successfully')
                ]);
            }
        }
    }

    public function removeSettingMedia(Request $request)
    {

        $system_settings = getSettings('system_settings', true);
        $system_settings = json_decode($system_settings, true);

        $images = $system_settings[$request['field']];

        $serch_index = array_search($request['img_name'], $images);
        if ($serch_index !== false) {
            unset($images[$serch_index]);
        }

        $system_settings[$request['field']] = $images;


        $data = [
            'variable' => 'system_settings',
            'value' => json_encode(
                $system_settings,
                JSON_UNESCAPED_SLASHES
            ),
        ];

        $setting_data = Setting::where('variable', 'system_settings')->first();
        if ($setting_data == null) {
            $response['is_deleted'] = false;
        } else {
            // Update the existing record with the new settings
            $setting_data->update($data);
            $response['is_deleted'] = true;
        }
        return response()->json([$response]);
    }


    public function theme()
    {
        return view('admin.pages.tables.themes');
    }
    public function theme_list(Request $request)
    {

        $search = trim($request->input('search'));
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'ASC');

        $themesQuery = DB::table('themes');

        if ($search) {
            $themesQuery->where('id', 'like', '%' . $search . '%')
                ->orWhere('name', 'like', '%' . $search . '%')
                ->orWhere('slug', 'like', '%' . $search . '%')
                ->orWhere('is_default', 'like', '%' . $search . '%')
                ->orWhere('status', 'like', '%' . $search . '%');
        }

        $total = $themesQuery->count();
        $themes = $themesQuery->orderBy($sort, $order)
            ->offset($offset)
            ->limit($limit)
            ->get();

        $rows = [];
        foreach ($themes as $theme) {
            $operate = '';
            $tempRow = [
                'id' => $theme->id,
                'name' => $theme->name,
                'image' => "<div class='image-box-100'><a href='" . getImageUrl($theme->image, "", "", "image", 'NO_USER_IMAGE') . "' data-lightbox='image-" . $theme->id . "'><img src='" . getMediaImageUrl($theme->image) . "' class='rounded'></a></div>",
                'is_default' => $theme->is_default == '1' ? '<label class="badge bg-success" >Yes</label>' : '<label class="badge bg-danger " >No</label>',
                'status' => $theme->status == '1' ? '<label class="badge bg-success " >Active</label>' : '<label class="badge bg-danger " >Deactive</label>',
                'created_on' => date('Y-m-d', strtotime($theme->created_on)),
            ];

            if ($theme->is_default != '1') {
                $operate .= '<a class="btn btn-success action-btn btn-xs update_default_theme mr-1 mb-1 ml-1" title="Default" href="javascript:void(0)" data-id="' . $theme->id . '" data-status="' . $theme->status . '" ><i class="fa fa-check-circle"></i></a>';
            }
            $rows[] = $tempRow;
        }

        $bulkData = [
            'total' => $total,
            'rows' => $rows
        ];

        return response()->json($bulkData);
    }

    public function sms_gateway()
    {
        $sms_gateway_settings = getSettings('sms_gateway_settings', true);
        $sms_gateway_settings = json_decode($sms_gateway_settings, true);

        return view('admin.pages.forms.sms_gateway_setting', [
            'sms_gateway_settings' => $sms_gateway_settings
        ]);
    }

    public function store_sms_data(Request $request)
    {
        $data = [
            'variable' => 'sms_gateway_settings',
            'value' => json_encode([
                'base_url' => $request->input('base_url', ''),
                'sms_gateway_method' => $request->input('sms_gateway_method', ''),
                'country_code_include' => $request->input('country_code_include', '0'),
                'header_key' => $request->input('header_key', ''),
                'header_value' => $request->input('header_value', ''),
                'text_format_data' => $request->input('text_format_data', ''),
                'params_key' => $request->input('params_key', ''),
                'params_value' => $request->input('params_value', ''),
                'body_key' => $request->input('body_key', ''),
                'body_value' => $request->input('body_value', ''),
            ], JSON_UNESCAPED_SLASHES),
        ];
        // Check if settings already exist in the database
        $setting_data = Setting::where('variable', 'sms_gateway_settings')->first();
        if ($setting_data == null) {
            // Create a new record if no settings found
            $settings = Setting::create($data);
            $settings->save();
            if ($request->ajax()) {
                return response()->json([
                    'error' => false,
                    'message' => labels('admin_labels.settings_inserted_successfully', 'Settings inserted successfully')
                ]);
            }
        } else {
            // Update the existing record with the new settings
            $setting_data->update($data);


            if ($request->ajax()) {
                return response()->json([
                    'error' => false,
                    'message' => labels('admin_labels.settings_updated_successfully', 'Settings updated successfully')
                ]);
            }
        }
    }

    public function s3StorageSetting()
    {
        $settings = getSettings('s3_storage_settings', true);
        $settings = json_decode($settings, true);

        return view('admin.pages.forms.s3_storage_setting', [
            'settings' => $settings
        ]);
    }

    public function store3StorageSetting(Request $request)
    {
        if (!auth()->check()) {
            return redirect('admin/login')->refresh();
        }
        $rules = [
            'aws_access_key_id' => 'required',
            'aws_secret_access_key' => 'required',
            'aws_default_region' => 'required',
            'aws_bucket' => 'required',
        ];

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }
        $data = [
            'variable' => 's3_storage_settings',
            'value' => json_encode([
                'aws_access_key_id' => $request->aws_access_key_id,
                'aws_secret_access_key' => $request->aws_secret_access_key,
                'aws_default_region' => $request->aws_default_region,
                'aws_bucket' => $request->aws_bucket,
                'aws_use_path_style_endpoint' => "true",
            ], JSON_UNESCAPED_SLASHES),
        ];
        // Check if settings already exist in the database
        $setting_data = Setting::where('variable', 's3_storage_settings')->first();


        if ($setting_data == null) {
            // Create a new record if no settings found
            $settings = Setting::create($data);
            $settings->save(); {
            }
            if ($request->ajax()) {
                return response()->json([
                    'message' => labels('admin_labels.settings_inserted_successfully', 'Settings inserted successfully')
                ]);
            }
        } else {
            // Update the existing record with the new settings
            $setting_data->update($data);
            if ($request->ajax()) {
                return response()->json([
                    'message' => labels('admin_labels.settings_updated_successfully', 'Settings updated successfully')
                ]);
            }
        }
    }
}
