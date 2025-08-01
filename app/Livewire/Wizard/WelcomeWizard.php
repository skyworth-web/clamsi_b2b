<?php

namespace App\Livewire\Wizard;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class WelcomeWizard extends Component
{
    public $showWizard = false;
    public $country_search = '';

    public $step = 0;
    public $form = [
        'business_name' => '',
        'company_name' => '', // Buyer only, optional
        'preferred_supplier_countries' => [], // Buyer only, optional (array)
        'email' => '',
        'country_id' => '',
        'country' => '',
        'website' => '',
        'mobile_number' => '',
        'country_code' => '',
        'otp' => '',
        'otp_digits' => ['', '', '', ''],
        'categories' => [],
        'name' => '',
        'address' => '',
        'zip_code' => '',
        'terms' => false,
        'latitude' => '',
        'longitude' => '',
        'password' => '',
        'password_confirmation' => '',
    ];
    public $otpSent = false;
    public $errorMessage = '';
    public $countries;
    public $categories;
    public $otpSentAt = null;
    public $otpTries = 0;
    public $otpResendTimeout = 60; // seconds
    public $otpVerified = false; // Track if OTP is verified
    public $otpVerifiedMobile = null; // Track which mobile was verified
    public $successMessage = '';
    public $redirectUrl = null;
    public $rememberDevice = null; // Track remember device choice

    public function mount()
    {
        if (Auth::check()) {
            $this->redirectAuthenticatedUser();
        }
        $this->countries = DB::table('countries')->select('id', 'name', 'phonecode', 'iso2')->get();
        $this->categories = DB::table('categories')->select('id', 'name')->get();
        \Log::info('Countries loaded:', ['count' => $this->countries->count()]);
        $this->detectUserLocation();
        
        // Check for remember device cookies
        $this->checkRememberDeviceCookies();
    }
    
    private function checkRememberDeviceCookies()
    {
        // Check if user is already authenticated and has remember device cookie
        if (Auth::check()) {
            $user = Auth::user();
            $mobile = $user->mobile;
            
            if ($mobile) {
                // Extract country code and mobile number from the full mobile number
                $countryCode = '';
                $mobileNumber = '';
                
                // Try to find the country code by matching against known codes
                foreach ($this->countries as $country) {
                    $phoneCode = $country->phonecode;
                    if (strpos($mobile, '+' . $phoneCode) === 0) {
                        $countryCode = $phoneCode;
                        $mobileNumber = substr($mobile, strlen('+' . $phoneCode));
                        break;
                    }
                }
                
                if ($countryCode && $mobileNumber) {
                    $cookieName = 'remember_device_' . md5($countryCode . $mobileNumber);
                    $hasRememberCookie = request()->hasCookie($cookieName);
                    
                    if ($hasRememberCookie) {
                        session(['remember_device' => true]);
                        
                        // Update session configuration for remember device
                        config(['session.lifetime' => 60 * 24 * 30]); // 30 days
                        
                        session()->save();
                        
                        \Log::info('[RememberDevice] Found remember device cookie for authenticated user', [
                            'user_id' => $user->id,
                            'mobile' => $mobile,
                            'country_code' => $countryCode,
                            'mobile_number' => $mobileNumber,
                            'cookie_name' => $cookieName,
                            'session_remember_device' => session('remember_device', false),
                            'session_id' => session()->getId(),
                            'session_lifetime_config' => config('session.lifetime')
                        ]);
                    }
                }
            }
        }
    }

    public function focusNext($index)
    {
        if (!empty($this->form['otp_digits'][$index]) && $index < 3) { // Changed to 3
            $this->dispatch('focusNext', ['index' => $index]);
        }
    }

    public function detectUserLocation()
    {
        // Only detect location if no country code is already set
        if (!empty($this->form['country_code'])) {
            \Log::info('Country code already set, skipping location detection:', ['country_code' => $this->form['country_code']]);
            return;
        }

        $clientIp = request()->ip();
        if (!$clientIp || $clientIp === '127.0.0.1') {
            $clientIp = request()->header('CF-Connecting-IP')
                ?? request()->header('X-Forwarded-For')
                ?? request()->header('X-Real-IP')
                ?? 'unknown';
        }

        \Log::info('Client IP detected:', ['ip' => $clientIp]);

        try {
            $response = Http::get("http://ip-api.com/json/{$clientIp}");
            if ($response->successful()) {
                $data = $response->json();
                $isoCode = $data['countryCode'] ?? '';
                \Log::info('IP-API response:', ['ip' => $clientIp, 'countryCode' => $isoCode]);

                if ($isoCode) {
                    $country = $this->countries->firstWhere('iso2', $isoCode);
                    if ($country) {
                        $this->form['country_id'] = $country->id;
                        if (empty($this->form['country_code'])) {
                            $this->form['country_code'] = $country->phonecode;
                        }
                        $this->form['country'] = $country->id;
                        \Log::info('Country detected via IP:', [
                            'country' => $country->name,
                            'id' => $country->id,
                            'iso2' => $isoCode
                        ]);
                        return;
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('IP geolocation failed:', ['ip' => $clientIp, 'error' => $e->getMessage()]);
        }

        $country = $this->countries->firstWhere('iso2', 'US');
        if ($country) {
            $this->form['country_id'] = $country->id;
            if (empty($this->form['country_code'])) {
                $this->form['country_code'] = $country->phonecode;
            }
            $this->form['country'] = $country->id;
            \Log::info('Defaulted to US:', ['id' => $country->id]);
        }
    }

    public function processGeolocation($latitude, $longitude)
    {
        $this->form['latitude'] = $latitude;
        $this->form['longitude'] = $longitude;
        \Log::info('Geolocation saved:', ['latitude' => $latitude, 'longitude' => $longitude]);
    }

    public function toggleCategory($categoryId)
    {
        if (!is_array($this->form['categories'])) {
            $this->form['categories'] = [];
        }

        if (in_array($categoryId, $this->form['categories'])) {
            $this->form['categories'] = array_diff($this->form['categories'], [$categoryId]);
        } else {
            $this->form['categories'][] = $categoryId;
        }

        $this->form['categories'] = array_values($this->form['categories']);
        \Log::info('Category toggled:', ['categoryId' => $categoryId]);
    }

    public function startWizard()
    {
        $this->resetForm();
        $this->showWizard = true;
        $this->step = 0;
        $this->errorMessage = '';
        $this->detectUserLocation();
        $this->dispatch('update-step');
    }
    
    public function startWizardLogin()
    {
        $this->resetForm();
        $this->showWizard = true;
        $this->step = 'login';
        $this->errorMessage = '';
        $this->detectUserLocation();
        $this->dispatch('update-step');
    }
    
    public function closeWizard()
    {
        \Log::info('closeWizard called');
        $this->resetForm();
        $this->showWizard = false;
        $this->step = 0;
        $this->errorMessage = '';
        $this->dispatch('update-step');
    }
    
    public function startSupplierLogin()
    {
        $this->step = 11;
        $this->errorMessage = '';
        $this->dispatch('update-step');
    }

    public function startSupplierRegistration()
    {
        $this->step = 1;
        $this->errorMessage = '';
        $this->dispatch('update-step');
    }

    public function startBuyerRegistration()
    {
        $this->step = 6;
        $this->errorMessage = '';
        $this->dispatch('update-step');
    }

    public function goBack()
    {
        \Log::info('goBack called', [
            'current_step' => $this->step,
            'flow' => ($this->step >= 6 && $this->step <= 10) ? 'buyer' : (($this->step >= 1 && $this->step <= 5) ? 'supplier' : 'other')
        ]);
        // Buyer registration step-back map
        $buyerBackMap = [10 => 9, 9 => 8, 8 => 6, 6 => 0];
        if (array_key_exists($this->step, $buyerBackMap)) {
            $prevStep = $this->step;
            $this->step = $buyerBackMap[$this->step];
            \Log::info('goBack: Buyer flow mapped back', ['from_step' => $prevStep, 'to_step' => $this->step]);
        } elseif ($this->step == 1) { // Supplier first step
            \Log::info('goBack: Supplier first step, returning to selection screen', ['from_step' => $this->step]);
            $this->step = 0;
        } elseif ($this->step > 0) {
            $prevStep = $this->step;
            $this->step--;
            \Log::info('goBack: Decremented step', ['from_step' => $prevStep, 'to_step' => $this->step]);
            // If going back to mobile step, reset OTP only if mobile/country_code changed
            if (in_array($this->step, [2, 8])) { // Supplier or buyer mobile step
                $currentMobile = '+' . ltrim($this->form['country_code'], '+') . $this->form['mobile_number'];
                if ($this->otpVerifiedMobile !== $currentMobile) {
                    $this->otpVerified = false;
                    $this->otpVerifiedMobile = null;
                    $this->otpSent = false;
                    \Log::info('goBack: Reset OTP state', ['step' => $this->step]);
                }
            }
            $this->errorMessage = '';
        } else {
            \Log::info('goBack: step <= 0, hiding wizard');
            $this->showWizard = false;
        }
        \Log::info('goBack: Final step after goBack', ['final_step' => $this->step]);
        $this->dispatch('update-step');
    }

    public function submitStep1()
    {
        $this->validate([
            'form.business_name' => 'required|string|max:255',
            'form.email' => 'required|email|unique:users,email|unique:suppliers,email',
            'form.country_id' => 'required|exists:countries,id',
            'form.website' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^(https?:\/\/)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(\/.*)?$/'
            ],
        ]);

        $this->errorMessage = '';
        $this->step = 2;
        $this->dispatch('update-step');
    }

   public function submitStep2()
{
    $this->successMessage = '';
    // Debug: log the entire form state
    \Log::info('submitStep2 form state', $this->form);
    // Check resend timer
    if ($this->otpSentAt && now()->diffInSeconds($this->otpSentAt) < $this->otpResendTimeout) {
        $this->errorMessage = 'Please wait before resending OTP.';
        return;
    }
    // Validate country code and mobile number
    $this->validate([
        'form.country_code' => 'required|string|regex:/^\+?[0-9]{1,4}$/',
        'form.mobile_number' => [
            'required',
            'numeric',
            'regex:/^[0-9]{7,15}$/', // Allow 7 to 15 digits (E.164 standard)
            function ($attribute, $value, $fail) {
                // Combine country code and mobile number
                $countryCode = preg_replace('/\D/', '', (string) $this->form['country_code']);
                $mobileNumber = preg_replace('/\D/', '', (string) $this->form['mobile_number']);
                $mobile = '+' . $countryCode . $mobileNumber;

                // Check if mobile exists in users table
                if (DB::table('users')->where('mobile', $mobile)->exists()) {
                    $fail('This mobile number is already registered.');
                }

                // Check if mobile exists in suppliers table
                if (DB::table('suppliers')->where('mobile_number', $mobile)->exists()) {
                    $fail('This mobile number is already registered as a supplier.');
                }
            },
        ],
    ]);

    \Log::info('Input values:', [
        'country_code' => $this->form['country_code'],
        'mobile_number' => $this->form['mobile_number']
    ]);

    $countryCode = preg_replace('/\D/', '', (string) $this->form['country_code']);
    $mobileNumber = preg_replace('/\D/', '', (string) $this->form['mobile_number']);
    $mobile = '+' . $countryCode . $mobileNumber;

    // Check if mobile already exists in users table
    if (DB::table('users')->where('mobile', $mobile)->exists()) {
        $this->errorMessage = 'This mobile number is already registered.';
        return;
    }

    // Handle specific case for Cyprus (+357)
    if ($countryCode === '357' && strlen($mobileNumber) > 8) {
        $mobileNumber = substr($mobileNumber, 0, 8);
        $mobile = '+357' . $mobileNumber;
        $this->form['mobile_number'] = $mobileNumber;
        \Log::info('Trimmed mobile number:', ['mobile' => $mobile]);
    }

    // TEMPORARILY BYPASSED FOR TESTING - TWILIO API CALL COMMENTED OUT
    /*
    $accountSid = config('services.twilio.sid');
    $authToken = config('services.twilio.token');
    $verifyServiceSid = config('services.twilio.verify_sid');

    \Log::info('Mobile number format:', ['mobile' => $mobile]);

    $url = "https://verify.twilio.com/v2/Services/$verifyServiceSid/Verifications";
    $data = [
        'To' => $mobile,
        'Channel' => 'sms'
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_USERPWD, "$accountSid:$authToken");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    */
    
    // TEMPORARY: Always return success for testing
    $httpCode = 200;
    $response = '{"status": "pending"}';
    $curlError = null;
    
    if ($httpCode >= 200 && $httpCode < 300) {
        \Log::info('OTP sent successfully:', ['mobile' => $mobile, 'response' => $response]);

        $otp = rand(1000, 9999); // 4-digit OTP
        DB::table('otps')->insert([
            'mobile' => $mobile,
            'otp' => $otp,
            'varified' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->otpSent = true;
        $this->otpSentAt = now();
        $this->otpTries = 0;
        $this->errorMessage = '';
        $this->successMessage = 'A new OTP was sent to your mobile number.';
        $this->step = 3;
        $this->dispatch('update-step');
    } else {
        \Log::error('Twilio OTP sending failed:', [
            'mobile' => $mobile,
            'http_code' => $httpCode,
            'response' => $response,
            'curl_error' => $curlError
        ]);
        $this->errorMessage = 'Failed to send OTP. Please try again.';
        $this->successMessage = '';
    }
}

    public function submitStep3()
    {
        $this->successMessage = '';
        if ($this->otpTries >= 5) {
            $this->errorMessage = 'Too many attempts. Please resend OTP.';
            return;
        }
        $this->form['otp'] = implode('', $this->form['otp_digits']);

        $this->validate([
            'form.otp' => 'required|string|size:4', // Changed to 4-digit OTP
        ]);

        $countryCode = preg_replace('/\D/', '', (string) $this->form['country_code']);
        $mobileNumber = preg_replace('/\D/', '', (string) $this->form['mobile_number']);
        $mobile = '+' . $countryCode . $mobileNumber;

        if ($countryCode === '357' && strlen($mobileNumber) > 8) {
            $mobileNumber = substr($mobileNumber, 0, 8);
            $mobile = '+357' . $mobileNumber;
            $this->form['mobile_number'] = $mobileNumber;
            \Log::info('Trimmed mobile number:', ['mobile' => $mobile]);
        }

        // TEMPORARILY BYPASSED FOR TESTING - TWILIO API CALL COMMENTED OUT
        /*
        $accountSid = config('services.twilio.sid');
        $authToken = config('services.twilio.token');
        $verifyServiceSid = config('services.twilio.verify_sid');

        \Log::info('Verification attempt:', ['mobile' => $mobile, 'otp' => $this->form['otp']]);

        $url = "https://verify.twilio.com/v2/Services/$verifyServiceSid/VerificationCheck";
        $data = [
            'To' => $mobile,
            'Code' => $this->form['otp']
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$accountSid:$authToken");
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        $responseData = json_decode($response, true);
        */
        
        // TEMPORARY: Always return success for testing
        $httpCode = 200;
        $responseData = ['status' => 'approved'];
        $curlError = null;
        
        \Log::info('Verification attempt (BYPASSED):', ['mobile' => $mobile, 'otp' => $this->form['otp']]);

        if ($httpCode >= 200 && $httpCode < 300 && isset($responseData['status']) && $responseData['status'] === 'approved') {
            $otpRecord = DB::table('otps')
                ->where('mobile', $mobile)
                ->where('varified', 0)
                ->first();

            if ($otpRecord) {
                DB::table('otps')
                    ->where('id', $otpRecord->id)
                    ->update(['varified' => 1, 'updated_at' => now()]);
            }

            $this->otpVerified = true;
            $this->otpVerifiedMobile = $mobile;
            $this->errorMessage = '';
            $this->step = 4;
            $this->dispatch('update-step');
        } else {
            $this->otpTries++;
            \Log::error('Twilio OTP verification failed:', [
                'mobile' => $mobile,
                'otp' => $this->form['otp'],
                'http_code' => $httpCode,
                'response' => $response ?? 'N/A',
                'curl_error' => $curlError
            ]);
            $this->errorMessage = 'Invalid OTP. Please try again.';
            $this->successMessage = '';
        }
    }

    public function handlePaste($event, $index)
    {
        $pastedData = $event['clipboardData']['text'] ?? '';
        $pastedData = preg_replace('/\D/', '', $pastedData);
        if (strlen($pastedData) >= 4) { // Changed to 4-digit OTP
            for ($i = 0; $i < 4; $i++) { // Changed to 4
                $this->form['otp_digits'][$i] = $pastedData[$i] ?? '';
            }
            $this->form['otp'] = implode('', $this->form['otp_digits']);
        }
    }

    public function submitStep4()
    {
        $this->validate([
            'form.categories' => 'required|array|min:1',
            'form.categories.*' => 'exists:categories,id',
        ]);

        $this->errorMessage = '';
        $this->step = 5;
        $this->dispatch('update-step');
    }

    public function submitStep5()
    {
        \Log::info('submitStep5: called', [
            'form' => $this->form,
        ]);
        $this->validate([
            'form.name' => 'required|string|max:255',
            'form.business_name' => 'required|string|max:255',
            'form.email' => 'required|email|unique:users,email|unique:suppliers,email',
            'form.country' => 'required|exists:countries,id',
            'form.categories' => 'required|array|min:1',
            'form.address' => 'required|string|max:255',
            'form.zip_code' => 'required|string|max:20',
            'form.terms' => 'accepted',
            'form.website' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^(https?:\/\/)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(\/.*)?$/'
            ],
            'form.country_code' => 'required|string|regex:/^\+?[0-9]{1,4}$/',
            'form.mobile_number' => [
                'required',
                'numeric',
                'regex:/^[0-9]{7,15}$/',
                function ($attribute, $value, $fail) {
                $countryCode = preg_replace('/\D/', '', (string) $this->form['country_code']);
                $mobileNumber = preg_replace('/\D/', '', (string) $this->form['mobile_number']);
                $mobile = '+' . $countryCode . $mobileNumber;
                if (DB::table('users')->where('mobile', $mobile)->exists()) {
                    $fail('This mobile number is already registered.');
                }
                if (DB::table('suppliers')->where('mobile_number', $mobile)->exists()) {
                    $fail('This mobile number is already registered as a supplier.');
                }
                },
            ],
            'form.latitude' => 'nullable|numeric|between:-90,90',
            'form.longitude' => 'nullable|numeric|between:-180,180',
        ]);
        
        $countryCode = preg_replace('/\D/', '', (string) $this->form['country_code']);
        $mobileNumber = preg_replace('/\D/', '', (string) $this->form['mobile_number']);
        $mobile = '+' . $countryCode . $mobileNumber;

        DB::beginTransaction();
        try {
            $userId = DB::table('users')->insertGetId([
                'username' => Str::slug($this->form['name']),
                'email' => $this->form['email'],
                // 'password' => Hash::make($this->form['password']), // Removed
                'role_id' => 4,
                'company' => $this->form['business_name'],
                'address' => $this->form['address'],
                'country_code' => $this->form['country'],
                'latitude' => $this->form['latitude'] ?: null,
                'longitude' => $this->form['longitude'] ?: null,
                'mobile' => '+' . preg_replace('/\D/', '', (string) $this->form['country_code']) . preg_replace('/\D/', '', (string) $this->form['mobile_number']),
                'created_at' => now(),
                'updated_at' => now(),
                'active' => 1,
                'avatar' => 'avatar.png',
                'disk' => 'public',
            ]);

            $user = \App\Models\User::find($userId);

            DB::table('suppliers')->insert([
                'user_id' => $userId,
                'business_name' => $this->form['business_name'],
                'email' => $this->form['email'],
                'mobile_number' => $this->form['country_code'] . $this->form['mobile_number'],
                'preference_id' => $this->form['categories'][0] ?? null,
                'country_id' => $this->form['country_id'],
                'website' => $this->form['website'],
                'created_at' => now(),
            ]);

            DB::table('addresses')->insert([
                'user_id' => $userId,
                'name' => $this->form['name'],
                'type' => 'billing',
                'mobile' => $this->form['mobile_number'],
                'address' => $this->form['address'],
                'country_code' => ltrim($this->form['country_code'], '+'),
                'latitude' => $this->form['latitude'] ?: null,
                'longitude' => $this->form['longitude'] ?: null,
                'city' => '',
                'area' => '',
                'pincode' => '',
                'is_default' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            Auth::login($user);
            \Log::info('Supplier registered and logged in:', [
                'user_id' => $userId,
                'role_id' => $user->role_id,
                'session_id' => session()->getId(),
                'remember_device' => session('remember_device', false),
                // 'showRememberModal' => true, // Remove this
                // 'redirectUrl' => route('seller.home'), // Remove this
            ]);
            $this->redirectUrl = route('seller.home');
            $this->dispatch('registration-success', message: 'Registration successful!', redirectUrl: $this->redirectUrl);
            return;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Supplier registration failed:', ['error' => $e->getMessage()]);
            $this->errorMessage = 'Registration failed. Please try again.';
        }
    }

    public function submitStep6()
    {
        $this->validate([
            'form.business_name' => 'required|string|max:255',
            'form.email' => 'required|email|unique:users,email',
            'form.country_id' => 'required|exists:countries,id',
        ]);

        $this->errorMessage = '';
        $this->step = 8;
        $this->dispatch('update-step');
    }

    public function submitStep8()
    {
        $this->successMessage = '';
        // Debug: log the entire form state
        \Log::info('submitStep8 form state', $this->form);
         $this->validate([
            'form.address' => 'required|string|max:255',
            'form.country_code' => 'required|string|regex:/^\+?[0-9]{1,4}$/',
            'form.mobile_number' => [
                'required',
                'numeric',
                'regex:/^[0-9]{7,15}$/',
                function ($attribute, $value, $fail) {
                    $countryCode = preg_replace('/\D/', '', (string) $this->form['country_code']);
                    $mobileNumber = preg_replace('/\D/', '', (string) $this->form['mobile_number']);
                    $mobile = '+' . $countryCode . $mobileNumber;
                    if (DB::table('users')->where('mobile', $mobile)->exists()) {
                        $fail('This mobile number is already registered.');
                    }
                    if (DB::table('suppliers')->where('mobile_number', $mobile)->exists()) {
                        $fail('This mobile number is already registered as a supplier.');
                    }
                },
            ],
            'form.zip_code' => 'required|string|max:32',
        ]);

        // Send OTP to buyer mobile
        $countryCode = preg_replace('/\D/', '', (string) $this->form['country_code']);
        $mobileNumber = preg_replace('/\D/', '', (string) $this->form['mobile_number']);
        $mobile = '+' . $countryCode . $mobileNumber;

        // Check if mobile already exists in users table
        if (DB::table('users')->where('mobile', $mobile)->exists()) {
            $this->errorMessage = 'This mobile number is already registered.';
            return;
        }

        // TEMPORARILY BYPASSED FOR TESTING - TWILIO API CALL COMMENTED OUT
        /*
        $accountSid = config('services.twilio.sid');
        $authToken = config('services.twilio.token');
        $verifyServiceSid = config('services.twilio.verify_sid');
        $url = "https://verify.twilio.com/v2/Services/$verifyServiceSid/Verifications";
        $data = [
            'To' => $mobile,
            'Channel' => 'sms'
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$accountSid:$authToken");
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        */
        
        // TEMPORARY: Always return success for testing
        $httpCode = 200;
        $response = '{"status": "pending"}';
        $curlError = null;
        
        if ($httpCode >= 200 && $httpCode < 300) {
            $otp = rand(1000, 9999); // 4-digit OTP
            $existingOtp = DB::table('otps')->where('mobile', $mobile)->first();
            if ($existingOtp) {
                DB::table('otps')
                    ->where('mobile', $mobile)
                    ->update([
                        'otp' => $otp,
                        'varified' => 0,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('otps')->insert([
                    'mobile' => $mobile,
                    'otp' => $otp,
                    'varified' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $this->otpSent = true;
            $this->otpSentAt = now();
            $this->otpTries = 0;
            $this->errorMessage = '';
            $this->successMessage = 'A new OTP was sent to your mobile number.';
            $this->step = 9;
            $this->dispatch('update-step');
        } else {
            $responseData = json_decode($response, true);
            if (!empty($responseData['message'])) {
                $this->errorMessage = 'Failed to send OTP: ' . $responseData['message'];
            } else if (isset($responseData['code']) && $responseData['code'] == 60200) {
                $this->errorMessage = 'Invalid phone number. Please check your country code and number.';
            } else {
                $this->errorMessage = 'Failed to send OTP. Please check your number and try again.';
            }
            $this->successMessage = '';
            return;
        }
    }

    public function resendBuyerOtp()
    {
        $this->successMessage = '';
        if ($this->otpSentAt && now()->diffInSeconds($this->otpSentAt) < $this->otpResendTimeout) {
            $this->errorMessage = 'Please wait before resending OTP.';
            return;
        }
        $this->otpTries = 0;
        $this->submitStep8();
        if ($this->otpSent) {
            $this->successMessage = 'A new OTP was sent to your mobile number.';
        }
    }

    public function submitBuyerOtp()
    {
        $this->successMessage = '';
        if ($this->otpTries >= 5) {
            $this->errorMessage = 'Too many attempts. Please resend OTP.';
            return;
        }
        $this->form['otp'] = implode('', $this->form['otp_digits']);
        $this->validate([
            'form.otp' => 'required|string|size:4',
        ]);
        $countryCode = preg_replace('/\D/', '', (string) $this->form['country_code']);
        $mobileNumber = preg_replace('/\D/', '', (string) $this->form['mobile_number']);
        $mobile = '+' . $countryCode . $mobileNumber;

        // TEMPORARILY BYPASSED FOR TESTING - TWILIO API CALL COMMENTED OUT
        /*
        $accountSid = config('services.twilio.sid');
        $authToken = config('services.twilio.token');
        $verifyServiceSid = config('services.twilio.verify_sid');
        $url = "https://verify.twilio.com/v2/Services/$verifyServiceSid/VerificationCheck";
        $data = [
            'To' => $mobile,
            'Code' => $this->form['otp']
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$accountSid:$authToken");
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        $responseData = json_decode($response, true);
        */
        
        // TEMPORARY: Always return success for testing
        $httpCode = 200;
        $responseData = ['status' => 'approved'];
        $curlError = null;
        
        if ($httpCode >= 200 && $httpCode < 300 && isset($responseData['status']) && $responseData['status'] === 'approved') {
            $this->otpVerified = true;
            $this->otpVerifiedMobile = $mobile;
            $this->errorMessage = '';
            \Log::info('OTP Verified State (submitBuyerOtp):', [
                'otpVerified' => $this->otpVerified,
                'otpVerifiedMobile' => $this->otpVerifiedMobile,
                'currentMobile' => $mobile,
                'step' => $this->step
            ]);
            $this->step = 10;
            $this->dispatch('update-step'); 
        } else {
            $this->otpTries++;
            $this->errorMessage = 'Invalid OTP. Please try again.';
            $this->successMessage = '';
        }
    }

    public function submitStep10()
    {
        $this->validate([
            'form.name' => 'required|string|max:255',
            'form.categories' => 'required|array|min:1',
            'form.terms' => 'accepted',
            'form.website' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^(https?:\/\/)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(\/.*)?$/'
            ],
            'form.company_name' => 'nullable|string|max:255',
            'form.latitude' => 'nullable|numeric|between:-90,90',
            'form.longitude' => 'nullable|numeric|between:-180,180',
        ]);
        $countryCode = preg_replace('/\D/', '', (string) $this->form['country_code']);
        $mobileNumber = preg_replace('/\D/', '', (string) $this->form['mobile_number']);
        $mobile = '+' . $countryCode . $mobileNumber;
        DB::beginTransaction();
        try {
            $userId = DB::table('users')->insertGetId([
                'username' => Str::slug($this->form['name']),
                'email' => $this->form['email'],
                'role_id' => 2,
                'company' => $this->form['business_name'],
                'address' => $this->form['address'],
                'country_code' => $this->form['country_id'],
                'pincode' => $this->form['zip_code'],
                'mobile' => '+' . preg_replace('/\D/', '', (string) $this->form['country_code']) . preg_replace('/\D/', '', (string) $this->form['mobile_number']),
                'latitude' => $this->form['latitude'] ?: null,
                'longitude' => $this->form['longitude'] ?: null,
                'created_at' => now(),
                'updated_at' => now(),
                'active' => 1,
                'avatar' => 'avatar.png',
                'disk' => 'public',
            ]);
            $user = \App\Models\User::find($userId);
            DB::table('addresses')->insert([
                'user_id' => $userId,
                'name' => $this->form['name'],
                'type' => 'shipping',
                'mobile' => ltrim($mobile, '+'),
                'address' => $this->form['address'],
                'country_code' => $this->form['country_id'],
                'latitude' => $this->form['latitude'] ?: null,
                'longitude' => $this->form['longitude'] ?: null,
                'pincode' => $this->form['zip_code'],
                'city' => '',
                'area' => '',
                'is_default' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::commit();
            Auth::login($user);
            \Log::info('Buyer registered and logged in:', [
                'user_id' => $userId,
                'role_id' => $user->role_id,
                'session_id' => session()->getId(),
                'remember_device' => session('remember_device', false)
            ]);
            $this->redirectUrl = route('home');
            $this->dispatch('registration-success', message: 'Registration successful!', redirectUrl: $this->redirectUrl);
            return;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Buyer registration failed:', ['error' => $e->getMessage()]);
            $this->errorMessage = 'Registration failed. Please try again.';
        }
    }
    
    public function submitStep11()
    {
        $this->successMessage = '';
        // Check resend timer
        if ($this->otpSentAt && now()->diffInSeconds($this->otpSentAt) < $this->otpResendTimeout) {
            $this->errorMessage = 'Please wait before resending OTP.';
            return;
        }
    // Validate country code and mobile number
    $this->validate([
            'form.country_code' => 'required|string|regex:/^\\+?[0-9]{1,4}$/',
        'form.mobile_number' => [
            'required',
            'numeric',
                'regex:/^[0-9]{7,15}$/', // Allow 7 to 15 digits (E.164 standard)
            function ($attribute, $value, $fail) {
                $countryCode = preg_replace('/\D/', '', (string) $this->form['country_code']);
                $mobileNumber = preg_replace('/\D/', '', (string) $this->form['mobile_number']);
                $mobile = '+' . $countryCode . $mobileNumber;
                if (!DB::table('users')->where('mobile', $mobile)->exists() && !DB::table('suppliers')->where('mobile_number', $mobile)->exists()) {
                    $fail('This mobile number is not registered.');
                }
            },
        ],
    ]);
    $countryCode = preg_replace('/\D/', '', (string) $this->form['country_code']);
    $mobileNumber = preg_replace('/\D/', '', (string) $this->form['mobile_number']);
    $mobile = '+' . $countryCode . $mobileNumber;
    if ($countryCode === '357' && strlen($mobileNumber) > 8) {
        $mobileNumber = substr($mobileNumber, 0, 8);
        $mobile = '+357' . $mobileNumber;
        }
        
        // TEMPORARILY BYPASSED FOR TESTING - TWILIO API CALL COMMENTED OUT
        /*
        $accountSid = config('services.twilio.sid');
        $authToken = config('services.twilio.token');
        $verifyServiceSid = config('services.twilio.verify_sid');
    $url = "https://verify.twilio.com/v2/Services/$verifyServiceSid/Verifications";
    $data = [
        'To' => $mobile,
        'Channel' => 'sms'
    ];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_USERPWD, "$accountSid:$authToken");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    */
    
    // TEMPORARY: Always return success for testing
    $httpCode = 200;
    $response = '{"status": "pending"}';
    $curlError = null;
    
    if ($httpCode >= 200 && $httpCode < 300) {
        $otp = rand(1000, 9999); // 4-digit OTP
        $existingOtp = DB::table('otps')->where('mobile', $mobile)->first();
        if ($existingOtp) {
            DB::table('otps')
                ->where('mobile', $mobile)
                ->update([
                    'otp' => $otp,
                        'varified' => 0,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('otps')->insert([
                'mobile' => $mobile,
                'otp' => $otp,
                'varified' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $this->otpSent = true;
        $this->otpSentAt = now();
        $this->otpTries = 0;
        $this->errorMessage = '';
        $this->successMessage = 'A new OTP was sent to your mobile number.';
        $this->step = 12;
        $this->dispatch('update-step');
    } else {
        $responseData = json_decode($response, true);
        if (!empty($responseData['message'])) {
            $this->errorMessage = 'Failed to send OTP: ' . $responseData['message'];
        } else if (isset($responseData['code']) && $responseData['code'] == 60200) {
            $this->errorMessage = 'Invalid phone number. Please check your country code and number.';
        } else {
            $this->errorMessage = 'Failed to send OTP. Please check your number and try again.';
        }
        $this->successMessage = '';
    }
    }
    
     public function submitStep12()
    {
        $this->successMessage = '';
        if ($this->otpTries >= 5) {
            $this->errorMessage = 'Too many attempts. Please resend OTP.';
            return;
        }
        $this->form['otp'] = implode('', $this->form['otp_digits']);

        $this->validate([
            'form.otp' => 'required|string|size:4', // Changed to 4-digit OTP
        ]);

        $countryCode = preg_replace('/\D/', '', (string) $this->form['country_code']);
        $mobileNumber = preg_replace('/\D/', '', (string) $this->form['mobile_number']);
        $mobile = '+' . $countryCode . $mobileNumber;

        if ($countryCode === '357' && strlen($mobileNumber) > 8) {
            $mobileNumber = substr($mobileNumber, 0, 8);
            $mobile = '+357' . $mobileNumber;
        }

        // TEMPORARILY BYPASSED FOR TESTING - TWILIO API CALL COMMENTED OUT
        /*
        $accountSid = config('services.twilio.sid');
        $authToken = config('services.twilio.token');
        $verifyServiceSid = config('services.twilio.verify_sid');

        \Log::info('Verification attempt:', ['mobile' => $mobile, 'otp' => $this->form['otp']]);

        $url = "https://verify.twilio.com/v2/Services/$verifyServiceSid/VerificationCheck";
        $data = [
            'To' => $mobile,
            'Code' => $this->form['otp']
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$accountSid:$authToken");
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        $responseData = json_decode($response, true);
        */
        
        // TEMPORARY: Always return success for testing
        $httpCode = 200;
        $responseData = ['status' => 'approved'];
        $curlError = null;
        
        \Log::info('Verification attempt (BYPASSED):', ['mobile' => $mobile, 'otp' => $this->form['otp']]);

        if ($httpCode >= 200 && $httpCode < 300 && isset($responseData['status']) && $responseData['status'] === 'approved') {
            $this->errorMessage = '';
            $user = \App\Models\User::where('mobile', $mobile)->first();
            if ($user) {
                Auth::login($user);
                
                // Check if user has remember device cookie and set session flag accordingly
                $cookieName = 'remember_device_' . md5($this->form['country_code'] . $this->form['mobile_number']);
                $hasRememberCookie = request()->hasCookie($cookieName);
                
                if ($hasRememberCookie) {
                    session(['remember_device' => true]);
                    session()->save();
                }
                
                \Log::info('submitStep12: User logged in', [
                    'user_id' => $user->id, 
                    'role_id' => $user->role_id, 
                    'rememberDevice' => $this->rememberDevice,
                    'hasRememberCookie' => $hasRememberCookie,
                    'session_remember_device' => session('remember_device', false),
                    'session_id' => session()->getId()
                ]);
                
                $this->redirectUrl = $user->role_id == 4 ? route('seller.home') : route('home');
                $this->dispatch('registration-success', message: 'Registration successful!', redirectUrl: $this->redirectUrl);
                return;
            } else if (DB::table('suppliers')->where('mobile_number', $mobile)->exists()) {
                $supplier = DB::table('suppliers')->where('mobile_number', $mobile)->first();
                if ($supplier && $supplier->user_id) {
                    $user = \App\Models\User::find($supplier->user_id);
                    if ($user) {
                        Auth::login($user);
                        
                        // Check if user has remember device cookie and set session flag accordingly
                        $cookieName = 'remember_device_' . md5($this->form['country_code'] . $this->form['mobile_number']);
                        $hasRememberCookie = request()->hasCookie($cookieName);
                        
                        if ($hasRememberCookie) {
                            session(['remember_device' => true]);
                            session()->save();
                        }
                        
                        \Log::info('submitStep12: Supplier user logged in', [
                            'user_id' => $user->id, 
                            'role_id' => $user->role_id, 
                            'rememberDevice' => $this->rememberDevice,
                            'hasRememberCookie' => $hasRememberCookie,
                            'session_remember_device' => session('remember_device', false),
                            'session_id' => session()->getId()
                        ]);
                        
                        $this->redirectUrl = $user->role_id == 4 ? route('seller.home') : route('home');
                        $this->dispatch('registration-success', message: 'Registration successful!', redirectUrl: $this->redirectUrl);
                        return;
                    }
                }
            }
            // Not found, show error
            $this->errorMessage = 'No account found for this mobile number.';
            return;
        } else {
            $this->otpTries++;
            \Log::error('Twilio OTP verification failed:', [
                'mobile' => $mobile,
                'otp' => $this->form['otp'],
                'http_code' => $httpCode,
                'response' => $response,
                'curl_error' => $curlError
            ]);
            $this->errorMessage = 'Invalid OTP. Please try again.';
            $this->successMessage = '';
        }
    }

    public function sendLoginOtp()
    {
        $this->successMessage = '';
        
        // Force refresh form state from request
        if (request()->has('form.country_code')) {
            $this->form['country_code'] = request()->input('form.country_code');
        }
        if (request()->has('form.mobile_number')) {
            $this->form['mobile_number'] = request()->input('form.mobile_number');
        }
        
        // Also check for direct form data
        if (request()->has('country_code')) {
            $this->form['country_code'] = request()->input('country_code');
        }
        if (request()->has('mobile_number')) {
            $this->form['mobile_number'] = request()->input('mobile_number');
        }
        
        // Force update from any JavaScript-sent data
        if (request()->has('data.countryCode')) {
            $this->form['country_code'] = request()->input('data.countryCode');
        }
        if (request()->has('data.mobileNumber')) {
            $this->form['mobile_number'] = request()->input('data.mobileNumber');
        }
        
        // Debug: Log the current form state
        \Log::info('sendLoginOtp: Current form state', [
            'country_code' => $this->form['country_code'],
            'mobile_number' => $this->form['mobile_number'],
            'request_data' => request()->all(),
            'full_form' => $this->form
        ]);
        
        // Check resend timer
        if ($this->otpSentAt && now()->diffInSeconds($this->otpSentAt) < $this->otpResendTimeout) {
            $this->errorMessage = 'Please wait before resending OTP.';
            return;
        }
        $this->validate([
            'form.country_code' => 'required|string|regex:/^\+?[0-9]{1,4}$/',
            'form.mobile_number' => [
                'required',
                'numeric',
                'regex:/^[0-9]{7,15}$/',
            ],
        ]);
        // Check for remember device cookie and skip OTP if present
        $cookieName = 'remember_device_' . md5($this->form['country_code'] . $this->form['mobile_number']);
        \Log::info('[RememberDevice] Checking cookie', ['cookieName' => $cookieName, 'country_code' => $this->form['country_code'], 'mobile_number' => $this->form['mobile_number'], 'hasCookie' => request()->hasCookie($cookieName)]);
        if (request()->hasCookie($cookieName)) {
            $user = \App\Models\User::where('mobile', '+' . preg_replace('/\D/', '', (string) $this->form['country_code']) . preg_replace('/\D/', '', (string) $this->form['mobile_number']))->first();
            if ($user) {
                \Auth::login($user);
                
                // Set remember_device session flag to true since user has the cookie
                session(['remember_device' => true]);
                session()->save();
                
                \Log::info('sendLoginOtp: User logged in via cookie', [
                    'user_id' => $user->id, 
                    'role_id' => $user->role_id,
                    'remember_device' => session('remember_device', false),
                    'session_id' => session()->getId()
                ]);
                
                $this->redirectUrl = $user->role_id == 4 ? route('seller.home') : route('home');
                $this->dispatch('registration-success', message: 'Registration successful!', redirectUrl: $this->redirectUrl);
                return;
            }
        }
        $countryCode = preg_replace('/\D/', '', (string) $this->form['country_code']);
        $mobileNumber = preg_replace('/\D/', '', (string) $this->form['mobile_number']);
        $mobile = '+' . $countryCode . $mobileNumber;
        if ($countryCode === '357' && strlen($mobileNumber) > 8) {
            $mobileNumber = substr($mobileNumber, 0, 8);
            $mobile = '+357' . $mobileNumber;
        }
        
        // TEMPORARILY BYPASSED FOR TESTING - TWILIO API CALL COMMENTED OUT
        /*
        $accountSid = config('services.twilio.sid');
        $authToken = config('services.twilio.token');
        $verifyServiceSid = config('services.twilio.verify_sid');
        $url = "https://verify.twilio.com/v2/Services/$verifyServiceSid/Verifications";
        $data = [
            'To' => $mobile,
            'Channel' => 'sms'
        ];
        \Log::info('Twilio Verify sendLoginOtp request:', ['url' => $url, 'data' => $data]);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$accountSid:$authToken");
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        \Log::info('Twilio Verify sendLoginOtp response:', ['mobile' => $mobile, 'http_code' => $httpCode, 'response' => $response, 'curl_error' => $curlError]);
        */
        
        // TEMPORARY: Always return success for testing
        $httpCode = 200;
        $response = '{"status": "pending"}';
        $curlError = null;
        
        \Log::info('Twilio Verify sendLoginOtp (BYPASSED):', ['mobile' => $mobile, 'http_code' => $httpCode, 'response' => $response, 'curl_error' => $curlError]);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            $this->otpSent = true;
            $this->otpSentAt = now();
            $this->otpTries = 0;
            $this->errorMessage = '';
            $this->successMessage = 'A new OTP was sent to your mobile number.';
        } else {
            $this->errorMessage = 'Failed to send OTP. Please try again.';
            $this->successMessage = '';
        }
    }

    public function verifyLoginOtp()
    {
        $this->successMessage = '';
        if ($this->otpTries >= 5) {
            $this->errorMessage = 'Too many attempts. Please resend OTP.';
            return;
        }
        $this->form['otp'] = implode('', $this->form['otp_digits']);
        $this->validate([
            'form.otp' => 'required|string|size:4',
        ]);
        $countryCode = preg_replace('/\D/', '', (string) $this->form['country_code']);
        $mobileNumber = preg_replace('/\D/', '', (string) $this->form['mobile_number']);
        $mobile = '+' . $countryCode . $mobileNumber;
        if ($countryCode === '357' && strlen($mobileNumber) > 8) {
            $mobileNumber = substr($mobileNumber, 0, 8);
            $mobile = '+357' . $mobileNumber;
        }

        // TEMPORARILY BYPASSED FOR TESTING - TWILIO API CALL COMMENTED OUT
        /*
        $accountSid = config('services.twilio.sid');
        $authToken = config('services.twilio.token');
        $verifyServiceSid = config('services.twilio.verify_sid');
        $url = "https://verify.twilio.com/v2/Services/$verifyServiceSid/VerificationCheck";
        $data = [
            'To' => $mobile,
            'Code' => $this->form['otp']
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$accountSid:$authToken");
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        $responseData = json_decode($response, true);
        */
        
        // TEMPORARY: Always return success for testing
        $httpCode = 200;
        $responseData = ['status' => 'approved'];
        $curlError = null;
        
        if ($httpCode >= 200 && $httpCode < 300 && isset($responseData['status']) && $responseData['status'] === 'approved') {
            $user = \App\Models\User::where('mobile', $mobile)->first();
            if ($user) {
                \Auth::login($user);
                
                // Check if user has remember device cookie and set session flag accordingly
                $cookieName = 'remember_device_' . md5($this->form['country_code'] . $this->form['mobile_number']);
                $hasRememberCookie = request()->hasCookie($cookieName);
                
                if ($hasRememberCookie) {
                    session(['remember_device' => true]);
                    session()->save();
                }
                
                \Log::info('verifyLoginOtp: User logged in', [
                    'user_id' => $user->id, 
                    'role_id' => $user->role_id, 
                    'rememberDevice' => $this->rememberDevice,
                    'hasRememberCookie' => $hasRememberCookie,
                    'session_remember_device' => session('remember_device', false),
                    'session_id' => session()->getId()
                ]);
                
                $this->redirectUrl = $user->role_id == 4 ? route('seller.home') : route('home');
                $this->dispatch('registration-success', message: 'Registration successful!', redirectUrl: $this->redirectUrl);
                return;
            }
            $supplier = DB::table('suppliers')->where('mobile_number', $mobile)->first();
            if ($supplier && $supplier->user_id) {
                $user = \App\Models\User::find($supplier->user_id);
                if ($user) {
                    \Auth::login($user);
                    
                    // Check if user has remember device cookie and set session flag accordingly
                    $cookieName = 'remember_device_' . md5($this->form['country_code'] . $this->form['mobile_number']);
                    $hasRememberCookie = request()->hasCookie($cookieName);
                    
                    if ($hasRememberCookie) {
                        session(['remember_device' => true]);
                        session()->save();
                    }
                    
                    \Log::info('verifyLoginOtp: Supplier user logged in', [
                        'user_id' => $user->id, 
                        'role_id' => $user->role_id, 
                        'rememberDevice' => $this->rememberDevice,
                        'hasRememberCookie' => $hasRememberCookie,
                        'session_remember_device' => session('remember_device', false),
                        'session_id' => session()->getId()
                    ]);
                    
                    $this->redirectUrl = $user->role_id == 4 ? route('seller.home') : route('home');
                    $this->dispatch('registration-success', message: 'Registration successful!', redirectUrl: $this->redirectUrl);
                    return;
                }
            }
            $this->errorMessage = 'No account found for this mobile number.';
            return;
        } else {
            $this->otpTries++;
            $this->errorMessage = 'Invalid OTP. Please try again.';
            $this->successMessage = '';
        }
    }
    private function resetForm()
    {
        $this->form = array_fill_keys(array_keys($this->form), '');
        $this->form['otp_digits'] = ['', '', '', '']; // Changed to 4 digits
        $this->form['categories'] = [];
        $this->form['terms'] = false;
        $this->step = 0;
        $this->showWizard = false;
        $this->otpSent = false;
        $this->otpVerified = false;
        $this->otpVerifiedMobile = null;
        $this->errorMessage = '';
        $this->successMessage = '';
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['form.country_code', 'form.mobile_number'])) {
            $currentMobile = '+' . ltrim($this->form['country_code'], '+') . $this->form['mobile_number'];
            if ($this->otpVerifiedMobile !== $currentMobile) {
                $this->otpVerified = false;
                $this->otpVerifiedMobile = null;
                $this->otpSent = false;
                $this->otpSentAt = null;
                $this->otpTries = 0;
                $this->successMessage = '';
            }
        }
    }

    public function updateFormField($field, $value)
    {
        \Log::info('updateFormField called:', ['field' => $field, 'value' => $value]);
        
        if (strpos($field, 'form.') === 0) {
            $field = substr($field, 5); // Remove 'form.' prefix
        }
        
        if (isset($this->form[$field])) {
            $oldValue = $this->form[$field];
            $this->form[$field] = $value;
            \Log::info('Form field updated:', [
                'field' => $field, 
                'old_value' => $oldValue,
                'new_value' => $value, 
                'form_country_code' => $this->form['country_code']
            ]);
        } else {
            \Log::warning('Form field not found:', ['field' => $field, 'available_fields' => array_keys($this->form)]);
        }
    }

    #[On('updateFormField')]
    public function handleUpdateFormField($field, $value)
    {
        $this->updateFormField($field, $value);
    }

    #[On('refreshFormState')]
    public function refreshFormState()
    {
        \Log::info('Form state refreshed:', ['form' => $this->form]);
        $this->dispatch('formStateUpdated', form: $this->form);
    }

    #[On('syncFormState')]
    public function syncFormState($countryCode = null, $mobileNumber = null)
    {
        if ($countryCode) {
            $this->form['country_code'] = $countryCode;
        }
        if ($mobileNumber) {
            $this->form['mobile_number'] = $mobileNumber;
        }
        
        \Log::info('Form state synced:', [
            'country_code' => $this->form['country_code'],
            'mobile_number' => $this->form['mobile_number']
        ]);
    }

    public function render()
    {
        $locale = app()->getLocale();
        $categories = $this->categories->map(function ($category) use ($locale) {
            $name = json_decode($category->name, true);
            $category->display_name = $name[$locale] ?? $name['en'] ?? 'Unknown';
            return $category;
        });

        return view('livewire.elegant.onboard.welcome-wizard', [
            'countries' => $this->countries,
            'categories' => $categories,
        ])->layout('livewire.blank');
    }

    // Add next step methods for OTP steps
    public function nextAfterOtpSupplier()
    {
        if ($this->otpVerified && $this->otpVerifiedMobile === ('+' . ltrim($this->form['country_code'], '+') . $this->form['mobile_number'])) {
            $this->step = 4;
            $this->dispatch('update-step');
        }
    }
    public function nextAfterOtpBuyer()
    {
        \Log::info('NextAfterOtpBuyer called:', [
            'otpVerified' => $this->otpVerified,
            'otpVerifiedMobile' => $this->otpVerifiedMobile,
            'currentMobile' => '+' . ltrim($this->form['country_code'], '+') . $this->form['mobile_number'],
            'step' => $this->step
        ]);
        if ($this->otpVerified && $this->otpVerifiedMobile === ('+' . ltrim($this->form['country_code'], '+') . $this->form['mobile_number'])) {
            $this->step = 10;
            $this->dispatch('update-step');
        }
    }

    public function redirectAuthenticatedUser()
    {
        if (Auth::user()->role_id == 4) {
            return redirect()->route('seller.home');
        } else {
            return redirect()->route('home');
        }
    }

    // Add a Livewire method to set remember device
    public function setRememberDevice($value)
    {
        try {
            \Log::info('[RememberDevice] setRememberDevice called with value:', ['value' => $value, 'type' => gettype($value)]);
            
            $this->rememberDevice = $value ? true : false;
            session(['remember_device' => $this->rememberDevice]);
            
            // Force session to be saved immediately
            session()->save();
            
            \Log::info('[RememberDevice] setRememberDevice completed', [
                'input_value' => $value,
                'rememberDevice' => $this->rememberDevice,
                'session_id' => session()->getId(),
                'user_id' => auth()->id(),
                'session_remember_device' => session('remember_device', false),
                'session_all' => session()->all()
            ]);
            
            // Force refresh the session cookie with new lifetime
            if ($this->rememberDevice && auth()->check()) {
                $this->refreshSessionCookie();
                
                // Update session configuration for remember device
                config(['session.lifetime' => 60 * 24 * 30]); // 30 days
                
                // Force session to be saved again with new configuration
                session()->save();
                
                \Log::info('[RememberDevice] Session updated for remember device', [
                    'session_lifetime_config' => config('session.lifetime'),
                    'session_remember_device' => session('remember_device', false)
                ]);
            }
            
            // Return success to JavaScript
            return ['success' => true, 'rememberDevice' => $this->rememberDevice];
        } catch (\Exception $e) {
            \Log::error('[RememberDevice] Error in setRememberDevice:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    // Method to refresh session cookie with new lifetime
    private function refreshSessionCookie()
    {
        try {
            $minutes = 60 * 24 * 30; // 30 days
            
            // Update session configuration
            config(['session.lifetime' => $minutes]);
            
            $cookie = cookie(
                config('session.cookie'),
                session()->getId(),
                $minutes,
                config('session.path'),
                config('session.domain'),
                config('session.secure'),
                config('session.http_only'),
                false,
                config('session.same_site')
            );
            
            // Set the cookie in the response
            response()->withCookie($cookie);
            
            // Force session to be saved
            session()->save();
            
            \Log::info('[RememberDevice] Session cookie refreshed', [
                'session_id' => session()->getId(),
                'user_id' => auth()->id(),
                'cookie_lifetime_minutes' => $minutes,
                'cookie_name' => config('session.cookie'),
                'session_lifetime_config' => config('session.lifetime')
            ]);
        } catch (\Exception $e) {
            \Log::error('[RememberDevice] Error in refreshSessionCookie:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    // Method to set remember device cookie from server side
    public function setRememberDeviceCookie()
    {
        try {
        if (!auth()->check()) {
            return ['success' => false, 'message' => 'User not authenticated'];
        }
        
        $user = auth()->user();
        $mobile = $user->mobile;
        
        if (!$mobile) {
            return ['success' => false, 'message' => 'No mobile number found'];
        }
        
        // Try to find the country code by matching against known codes
        $countryCode = '';
        $mobileNumber = '';
        
        foreach ($this->countries as $country) {
            $phoneCode = $country->phonecode;
            if (strpos($mobile, '+' . $phoneCode) === 0) {
                $countryCode = $phoneCode;
                $mobileNumber = substr($mobile, strlen('+' . $phoneCode));
                break;
            }
        }
        
        if (!$countryCode || !$mobileNumber) {
            return ['success' => false, 'message' => 'Could not parse mobile number'];
        }
        
        $cookieName = 'remember_device_' . md5($countryCode . $mobileNumber);
        $minutes = 60 * 24 * 30; // 30 days
        
            // In Livewire context, we can't directly set cookies in response
            // Instead, we'll return the cookie data for JavaScript to set
            \Log::info('[RememberDevice] Remember device cookie data prepared', [
            'cookie_name' => $cookieName,
            'cookie_lifetime_minutes' => $minutes,
            'user_id' => auth()->id(),
            'mobile' => $mobile,
            'country_code' => $countryCode,
            'mobile_number' => $mobileNumber
        ]);
        
        return [
            'success' => true, 
                'message' => 'Remember device cookie data prepared',
            'cookie_name' => $cookieName,
                'cookie_lifetime_minutes' => $minutes,
                'cookie_value' => '1',
                'cookie_path' => '/',
                'cookie_domain' => config('session.domain'),
                'cookie_secure' => config('session.secure'),
                'cookie_http_only' => false,
                'cookie_same_site' => config('session.same_site')
            ];
        } catch (\Exception $e) {
            \Log::error('[RememberDevice] Error in setRememberDeviceCookie:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to prepare remember device cookie'
            ];
        }
    }
    
    // Test method to verify remember device functionality
    public function testRememberDevice()
    {
        try {
        $rememberDevice = session('remember_device', false);
        $sessionId = session()->getId();
        $userId = auth()->id();
        
        \Log::info('[RememberDevice] Test method called', [
            'rememberDevice' => $rememberDevice,
            'session_id' => $sessionId,
            'user_id' => $userId,
            'session_data' => session()->all()
        ]);
        
        return [
            'rememberDevice' => $rememberDevice,
            'sessionId' => $sessionId,
            'userId' => $userId,
            'success' => true
        ];
        } catch (\Exception $e) {
            \Log::error('[RememberDevice] Error in testRememberDevice:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'rememberDevice' => false,
                'sessionId' => 'Error',
                'userId' => null
            ];
        }
    }
    
    // Method to get current session state
    public function getSessionState()
    {
        try {
        $sessionData = [
            'remember_device' => session('remember_device', false),
            'session_id' => session()->getId(),
            'user_id' => auth()->id(),
            'is_authenticated' => auth()->check(),
            'session_lifetime' => config('session.lifetime'),
            'session_expire_on_close' => config('session.expire_on_close'),
        ];
        
        \Log::info('[RememberDevice] Session state requested', $sessionData);
        
        return $sessionData;
        } catch (\Exception $e) {
            \Log::error('[RememberDevice] Error in getSessionState:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'remember_device' => false,
                'session_id' => 'Error',
                'user_id' => null,
                'is_authenticated' => false,
                'session_lifetime' => 0,
                'session_expire_on_close' => false
            ];
        }
    }
    
    // Method to check cookie state
    public function checkCookieState()
    {
        try {
        $cookieData = [
            'session_cookie_name' => config('session.cookie'),
            'session_cookie_exists' => request()->hasCookie(config('session.cookie')),
            'session_cookie_value' => request()->cookie(config('session.cookie')),
            'remember_device_session' => session('remember_device', false),
            'user_id' => auth()->id(),
            'is_authenticated' => auth()->check(),
        ];
        
        // Check for remember device cookies
        if (auth()->check()) {
            $user = auth()->user();
            $mobile = $user->mobile;
            
            if ($mobile) {
                // Try to find the country code by matching against known codes
                foreach ($this->countries as $country) {
                    $phoneCode = $country->phonecode;
                    if (strpos($mobile, '+' . $phoneCode) === 0) {
                        $countryCode = $phoneCode;
                        $mobileNumber = substr($mobile, strlen('+' . $phoneCode));
                        break;
                    }
                }
                
                if (isset($countryCode) && isset($mobileNumber)) {
                    $cookieName = 'remember_device_' . md5($countryCode . $mobileNumber);
                    $cookieData['remember_device_cookie_name'] = $cookieName;
                    $cookieData['remember_device_cookie_exists'] = request()->hasCookie($cookieName);
                    $cookieData['remember_device_cookie_value'] = request()->cookie($cookieName);
                }
            }
        }
        
        \Log::info('[RememberDevice] Cookie state requested', $cookieData);
        
        return $cookieData;
        } catch (\Exception $e) {
            \Log::error('[RememberDevice] Error in checkCookieState:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'session_cookie_name' => 'Error',
                'session_cookie_exists' => false,
                'session_cookie_value' => null,
                'remember_device_session' => false,
                'user_id' => null,
                'is_authenticated' => false
            ];
        }
    }
    
    // Method to handle redirect after remember device is set
    public function redirectAfterRememberDevice()
    {
        try {
        // Ensure session is saved
        session()->save();
        
        \Log::info('[RememberDevice] redirectAfterRememberDevice called', [
            'rememberDevice' => session('remember_device', false),
            'session_id' => session()->getId(),
            'user_id' => auth()->id()
        ]);
        
        // Force refresh the session cookie if remember device is enabled
        if (session('remember_device', false) && auth()->check()) {
            $this->refreshSessionCookie();
        }
        
            // Return redirect URL instead of actual redirect for Livewire compatibility
        if (auth()->check()) {
            $user = auth()->user();
            $redirectUrl = $user->role_id == 4 ? route('seller.home') : route('home');
                return [
                    'success' => true,
                    'redirect_url' => $redirectUrl,
                    'message' => 'Redirecting to appropriate page'
                ];
        }
        
            return [
                'success' => true,
                'redirect_url' => '/',
                'message' => 'Redirecting to home page'
            ];
        } catch (\Exception $e) {
            \Log::error('[RememberDevice] Error in redirectAfterRememberDevice:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'redirect_url' => '/',
                'message' => 'Failed to determine redirect URL'
            ];
        }
    }
    
    // Method to force page reload for remember device
    public function reloadPageForRememberDevice()
    {
        try {
        \Log::info('[RememberDevice] reloadPageForRememberDevice called', [
            'rememberDevice' => session('remember_device', false),
            'session_id' => session()->getId(),
            'user_id' => auth()->id()
        ]);
        
        // Force refresh the session cookie
        if (session('remember_device', false) && auth()->check()) {
            $this->refreshSessionCookie();
            
            // Ensure session configuration is updated
            config(['session.lifetime' => 60 * 24 * 30]); // 30 days
            
            // Force session to be saved
            session()->save();
            
            \Log::info('[RememberDevice] Session updated for remember device', [
                'session_id' => session()->getId(),
                'user_id' => auth()->id(),
                'session_lifetime_config' => config('session.lifetime'),
                'remember_device' => session('remember_device', false)
            ]);
        }
        
            // Return an array instead of JSON response for Livewire compatibility
            return [
            'success' => true,
            'reload' => true,
            'message' => 'Page will reload to apply remember device settings'
            ];
        } catch (\Exception $e) {
            \Log::error('[RememberDevice] Error in reloadPageForRememberDevice:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'reload' => false,
                'message' => 'Failed to reload page'
            ];
        }
    }

    // Method to check current session configuration
    public function checkSessionConfiguration()
    {
        try {
        $config = [
            'session_lifetime' => config('session.lifetime'),
            'session_expire_on_close' => config('session.expire_on_close'),
            'session_cookie_name' => config('session.cookie'),
            'session_driver' => config('session.driver'),
            'remember_device_session' => session('remember_device', false),
            'session_id' => session()->getId(),
            'user_id' => auth()->id(),
            'is_authenticated' => auth()->check(),
        ];
        
        \Log::info('[RememberDevice] Session configuration check', $config);
        
        return $config;
        } catch (\Exception $e) {
            \Log::error('[RememberDevice] Error in checkSessionConfiguration:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'session_lifetime' => 0,
                'session_expire_on_close' => false,
                'session_cookie_name' => 'Error',
                'session_driver' => 'Error',
                'remember_device_session' => false,
                'session_id' => 'Error',
                'user_id' => null,
                'is_authenticated' => false
            ];
        }
    }
    
    // Method to force update session configuration
    public function forceUpdateSessionConfig()
    {
        try {
            if (session('remember_device', false)) {
                $minutes = 60 * 24 * 30; // 30 days
                
                // Update session configuration
                config(['session.lifetime' => $minutes]);
                
                // Force session to be saved
                session()->save();
                
                \Log::info('[RememberDevice] Session configuration forced update', [
                    'session_lifetime' => config('session.lifetime'),
                    'session_id' => session()->getId(),
                    'user_id' => auth()->id(),
                    'remember_device' => session('remember_device', false)
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Session configuration updated',
                    'session_lifetime' => config('session.lifetime')
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Remember device not enabled'
                ];
            }
        } catch (\Exception $e) {
            \Log::error('[RememberDevice] Error in forceUpdateSessionConfig:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}