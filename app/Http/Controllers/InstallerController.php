<?php

namespace App\Http\Controllers;

use PDO;
use PDOException;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;

class InstallerController extends Controller
{
    public function index()
    {
        $installViewPath = resource_path('views/install.blade.php');
        if (File::exists($installViewPath)) {
            // Check if the symlink function is available
            if (function_exists('symlink')) {
                try {
                    // Attempt to create the symbolic link between the public directory and the storage directory
                    Artisan::call('storage:link');
                } catch (\Exception $e) {
                    // Log or handle the exception (if needed)
                }
            }
            // Regardless of whether the symlink function is available or not, proceed to clear cache and return to the install view
            return $this->clearAndReturnToInstallView();
        } else {
            return redirect('/');
        }
    }
    private function clearAndReturnToInstallView()
    {
        // Clear cache
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        // Return install view
        return view('install');
    }

    public function config_db(Request $request)
    {
        $formFields = Validator::make($request->all(), [
            'db_name' => ['required'],
            'db_host_name' => ['required'],
            'db_user_name' => ['required'],
            'db_password' => 'nullable',
        ]);
        if ($formFields->fails()) {
            $response = [
                'error' => true,
                'message' => $formFields->errors()->all(),
                'code' => 102,
            ];
            return response()->json($response);
        }
        try {
            // Replace these values with your actual database configuration
            $pdo = new PDO("mysql:host={$request['db_host_name']};dbname={$request['db_name']}", $request['db_user_name'], $request['db_password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $envFilePath = base_path('.env');
            $envContent = file_get_contents($envFilePath);

            $envContent = preg_replace('/^DB_HOST=.*$/m', "DB_HOST={$request['db_host_name']}", $envContent);
            $envContent = preg_replace('/^DB_DATABASE=.*$/m', "DB_DATABASE={$request['db_name']}", $envContent);
            $envContent = preg_replace('/^DB_USERNAME=.*$/m', "DB_USERNAME={$request['db_user_name']}", $envContent);
            $envContent = preg_replace('/^DB_PASSWORD=.*$/m', "DB_PASSWORD={$request['db_password']}", $envContent);
            file_put_contents($envFilePath, $envContent);
            return response()->json(['error' => false, 'message' => 'Connected to the database successfully.']);
        } catch (PDOException $e) {
            return response()->json(['error' => true, 'message' => "Connection failed: " . $e->getMessage()]);
        }
    }

    public function install(Request $request)
    {
        ini_set('max_execution_time', 900);

        // Validate user-related fields
        $userFields = Validator::make($request->all(), [
            'username' => ['required'],
            'email' => ['required', 'email'],
            'mobile' => ['required', 'numeric', 'min:10'],
            'password' => 'required|min:6|confirmed'
        ]);
        if ($userFields->fails()) {
            $response = [
                'error' => true,
                'message' => $userFields->errors()->all(),
                'code' => 102,
            ];
            return response()->json($response);
        }
        // Hash the password
        $data['username'] = $request['username'];
        $data['mobile'] = $request['mobile'];
        $data['email'] = $request['email'];
        $data['password'] = bcrypt($request['password']);
        $data['role_id'] = "1";
        $data['active'] = "1";
        Artisan::call('config:cache');
        Artisan::call('config:clear');
        DB::purge('mysql');
        // Import the SQL dump file
        $installViewPath = resource_path('views/install.blade.php');
        $sqlDumpPath = base_path('eshop_plus.sql');
        if (file_exists($sqlDumpPath)) {
            // Clear the database
            DB::statement('SET FOREIGN_KEY_CHECKS = 0'); // Disable foreign key checks
            $tables = DB::select('SHOW TABLES');
            foreach ($tables as $table) {
                $table_name = $table->{'Tables_in_' . env('DB_DATABASE')};
                DB::statement('DROP TABLE IF EXISTS ' . $table_name);
            }
            DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // Enable foreign key checks

            // Import SQL dump
            $sql = file_get_contents($sqlDumpPath);
            DB::unprepared($sql);

            $user = User::create($data);
            if ($user) {

                File::delete($installViewPath);
                unlink($sqlDumpPath);

                Artisan::call('cache:clear');
                Artisan::call('config:clear');
                Artisan::call('route:clear');
                Artisan::call('view:clear');

                return response()->json(['error' => false, 'message' => 'Congratulations! Installation completed successfully.','install' => true]);
            } else {
                return response()->json(['error' => true, 'message' => 'Oops! Installation failed. Please try again.']);
            }
        } else {
            return response()->json(['error' => true, 'message' => 'Oops! Installation couldn\'t process.']);
        }
    }
}
