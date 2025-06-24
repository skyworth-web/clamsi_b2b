<?php

namespace App\Providers;

use App;
use App\Models\Setting;
use App\Services\CustomFileRemover;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use App\Services\CustomPathGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\Support\FileRemover\FileRemover;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;
use Spatie\Permission\Models\Permission;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->singleton(PathGenerator::class, CustomPathGenerator::class);
        $this->app->singleton(FileRemover::class, CustomFileRemover::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $user = auth()->user(); // Retrieve authenticated user

            if ($user) {
                $permissions = $user->permissions;
                $user_role = $user->role;
                if ($user_role->name === 'super_admin') {
                    $permissions = Permission::all();
                }
                $view->with(['logged_in_user' => $user, 'user_permissions' => $permissions, 'user_role' => $user_role->name]);
            }
        });
        Paginator::useBootstrapFive();
        Model::unguard();

        $version = rand(0, 9999);


        $data = [];
        $sqlDumpPath = base_path('eshop_plus.sql');
        $installViewPath = resource_path('views/install.blade.php');

        $data = ['installViewPath' => $installViewPath, 'sqlDumpPath' => $sqlDumpPath];
        if (!file_exists($sqlDumpPath) && !file_exists($installViewPath)) {
            $system_settings = getSettings('system_settings', true);
            $system_settings = json_decode($system_settings, true);
            $web_settings = getSettings('web_settings', true);
            $web_settings = json_decode($web_settings, true);
            $pwa_settings = getSettings('pwa_settings', true);
            $pwa_settings = json_decode($pwa_settings, true);
            // dd($pwa_settings);
            // dd(getMediaImageUrl($system_settings['logo']));
            $currency_details = getDefaultCurrency();
            $currency_code = "";
            $currency_symbol = "";
            if ($currency_details != null) {
                $currency_symbol = $currency_details->symbol;
                $currency_code = $currency_details->code;
            }
            try {
                $email_settings = getSettings('email_settings', true);
                $email_settings = json_decode($email_settings, true);

                $firebase_settings = getSettings('firebase_settings', true);
                $firebase_settings = json_decode($firebase_settings, true);

                // google
                $firebase_settings['google_client_id'] =  $firebase_settings['google_client_id'] ?? '';
                $firebase_settings['google_client_secret'] =  $firebase_settings['google_client_secret'] ?? '';
                $firebase_settings['google_redirect_url'] =  $firebase_settings['google_redirect_url'] ?? '';

                // facebook
                $firebase_settings['facebook_client_id'] =  $firebase_settings['facebook_client_id'] ?? '';
                $firebase_settings['facebook_client_secret'] =  $firebase_settings['facebook_client_secret'] ?? '';
                $firebase_settings['facebook_redirect_url'] =  $firebase_settings['facebook_redirect_url'] ?? '';

                // email
                $email_settings['email'] =  $email_settings['email'] ?? '';
                $email_settings['password'] = $email_settings['password'] ?? '';
                $email_settings['smtp_host'] = $email_settings['smtp_host'] ?? '';
                $email_settings['smtp_port'] = $email_settings['smtp_port'] ?? '';
                $email_settings['email_content_type'] = $email_settings['email_content_type'] ?? '';
                $email_settings['smtp_encryption'] = $email_settings['smtp_encryption'] ?? '';

                // google
                config()->set('services.google.client_id', $firebase_settings['google_client_id']);
                config()->set('services.google.client_secret', $firebase_settings['google_client_secret']);
                config()->set('services.google.redirect', $firebase_settings['google_redirect_url']);

                // facebook
                config()->set('services.facebook.client_id', $firebase_settings['facebook_client_id']);
                config()->set('services.facebook.client_secret', $firebase_settings['facebook_client_secret']);
                config()->set('services.facebook.redirect', $firebase_settings['facebook_redirect_url']);

                // email
                config()->set('mail.mailers.smtp.host', $email_settings['smtp_host']);
                config()->set('mail.mailers.smtp.port', $email_settings['smtp_port']);
                config()->set('mail.mailers.smtp.encryption', $email_settings['smtp_encryption']);
                config()->set('mail.mailers.smtp.username', $email_settings['email']);
                config()->set('mail.mailers.smtp.password', $email_settings['password']);
                config()->set('mail.from.name', $system_settings['app_name']);
                config()->set('mail.from.address', $email_settings['email']);

                // dd(getMediaImageUrl($pwa_settings['logo']));
                Config::set([
                    // Manifest Config for PWA
                    'manifest.name' => $pwa_settings['name'],
                    'manifest.short_name' => $pwa_settings['short_name'],
                    'manifest.start_url' => '/',
                    'manifest.background_color' => $pwa_settings['background_color'],
                    'manifest.description' => $pwa_settings['description'],
                    'manifest.display' => 'fullscreen',
                    'manifest.theme_color' => $pwa_settings['theme_color'],
                    'manifest.icons' => [
                        [
                            'src' => getMediaImageUrl($pwa_settings['logo']),
                            'sizes' => '512x512',
                            'type' => 'image/png',
                            'purpose' => 'any maskable',
                        ],
                    ],
                ]);
            } catch (\Throwable $th) {
            }
            $data += ['system_settings' => $system_settings, 'web_settings' => $web_settings, 'currency_symbol' => $currency_symbol, 'currency_code' => $currency_code, 'version' => $version];
        }
        view()->share($data);
    }
}
