<?php
// Simple test script to verify remember device functionality
// Place this in your public directory and access via browser

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

echo "<h1>Remember Device Test</h1>";

// Check if user is authenticated
if (Auth::check()) {
    $user = Auth::user();
    echo "<p><strong>User:</strong> " . $user->name . " (ID: " . $user->id . ")</p>";
    echo "<p><strong>Mobile:</strong> " . $user->mobile . "</p>";
    
    // Check session state
    echo "<h2>Session State</h2>";
    echo "<p><strong>Session ID:</strong> " . Session::getId() . "</p>";
    echo "<p><strong>Remember Device:</strong> " . (Session::get('remember_device', false) ? 'Yes' : 'No') . "</p>";
    echo "<p><strong>Session Lifetime:</strong> " . Config::get('session.lifetime') . " minutes</p>";
    echo "<p><strong>Session Expire on Close:</strong> " . (Config::get('session.expire_on_close') ? 'Yes' : 'No') . "</p>";
    
    // Check cookies
    echo "<h2>Cookie State</h2>";
    $sessionCookieName = Config::get('session.cookie');
    echo "<p><strong>Session Cookie Name:</strong> " . $sessionCookieName . "</p>";
    echo "<p><strong>Session Cookie Exists:</strong> " . (isset($_COOKIE[$sessionCookieName]) ? 'Yes' : 'No') . "</p>";
    
    if (isset($_COOKIE[$sessionCookieName])) {
        echo "<p><strong>Session Cookie Value:</strong> " . $_COOKIE[$sessionCookieName] . "</p>";
    }
    
    // Check remember device cookies
    if ($user->mobile) {
        // Extract country code and mobile number
        $countries = DB::table('countries')->select('phonecode')->get();
        $countryCode = '';
        $mobileNumber = '';
        
        foreach ($countries as $country) {
            if (strpos($user->mobile, '+' . $country->phonecode) === 0) {
                $countryCode = $country->phonecode;
                $mobileNumber = substr($user->mobile, strlen('+' . $country->phonecode));
                break;
            }
        }
        
        if ($countryCode && $mobileNumber) {
            $cookieName = 'remember_device_' . md5($countryCode . $mobileNumber);
            echo "<p><strong>Remember Device Cookie Name:</strong> " . $cookieName . "</p>";
            echo "<p><strong>Remember Device Cookie Exists:</strong> " . (isset($_COOKIE[$cookieName]) ? 'Yes' : 'No') . "</p>";
            
            if (isset($_COOKIE[$cookieName])) {
                echo "<p><strong>Remember Device Cookie Value:</strong> " . $_COOKIE[$cookieName] . "</p>";
            }
        }
    }
    
    // Test setting remember device
    if (isset($_GET['test_set'])) {
        echo "<h2>Testing Remember Device Setting</h2>";
        
        Session::put('remember_device', true);
        Config::set('session.lifetime', 60 * 24 * 30); // 30 days
        Session::save();
        
        echo "<p>Remember device set to true</p>";
        echo "<p>Session lifetime set to 30 days</p>";
        echo "<p>Session saved</p>";
        
        // Redirect to refresh the page
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    
    echo "<p><a href='?test_set=1'>Test Set Remember Device</a></p>";
    
} else {
    echo "<p><strong>No user authenticated</strong></p>";
}

echo "<h2>All Cookies</h2>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";
?> 