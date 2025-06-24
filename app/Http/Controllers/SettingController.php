<?php

namespace App\Http\Controllers;

class SettingController extends Controller
{
    public function getFirebaseCredentials()
    {
        $firebase_settings = getSettings('firebase_settings');
        $firebase_settings = json_decode($firebase_settings, true);
        return $firebase_settings;
    }
}
