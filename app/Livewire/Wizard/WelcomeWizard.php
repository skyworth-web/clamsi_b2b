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

    public function mount()
    {
        if (Auth::check()) {
            $this->redirectAuthenticatedUser();
        }
        $this->countries = DB::table('countries')->select('id', 'name', 'phonecode', 'iso2')->get();
        $this->categories = DB::table('categories')->select('id', 'name')->get();
        \Log::info('Countries loaded:', ['count' => $this->countries->count()]);
        $this->detectUserLocation();
    }

    public function focusNext($index)
    {
        if (!empty($this->form['otp_digits'][$index]) && $index < 3) { // Changed to 3
            $this->dispatch('focusNext', ['index' => $index]);
        }
    }

    public function detectUserLocation()
    {
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
                'response' => $response,
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
            \Log::info('Buyer registered and logged in:', ['user_id' => $userId]);
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

        if ($httpCode >= 200 && $httpCode < 300 && isset($responseData['status']) && $responseData['status'] === 'approved') {
            $this->errorMessage = '';
            $user = \App\Models\User::where('mobile', $mobile)->first();
            if ($user) {
                Auth::login($user);
                \Log::info('submitStep12: User logged in', ['user_id' => $user->id, 'role_id' => $user->role_id]);
                $this->redirectUrl = $user->role_id == 4 ? route('seller.home') : route('home');
                $this->dispatch('registration-success', message: 'Registration successful!', redirectUrl: $this->redirectUrl);
                return;
            } else if (DB::table('suppliers')->where('mobile_number', $mobile)->exists()) {
                $supplier = DB::table('suppliers')->where('mobile_number', $mobile)->first();
                if ($supplier && $supplier->user_id) {
                    $user = \App\Models\User::find($supplier->user_id);
                    if ($user) {
                        Auth::login($user);
                        \Log::info('submitStep12: Supplier user logged in', ['user_id' => $user->id, 'role_id' => $user->role_id]);
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
    
    public function submitStep12_old()
    {
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
            \Log::info('Trimmed mobile number:', ['mobile' => $mobile]);
        }
       

            $otpRecord = DB::table('otps')
                ->where('mobile', $mobile)
                ->where('varified', 0)
                ->first();
        if(!empty($otpRecord) && ($otpRecord->otp == $this->form['otp'])){
            
                DB::table('otps')
                    ->where('id', $otpRecord->id)
                    ->update(['varified' => 1, 'updated_at' => now()]);

            $this->errorMessage = '';
            if(DB::table('users')->where('mobile', $mobile)->exists()){
                $user = \App\Models\User::where('mobile', $mobile)->first();
                Auth::login($user);
                \Log::info('submitStep12_old: User logged in', ['user_id' => $user->id, 'role_id' => $user->role_id]);
                $this->redirectUrl = $user->role_id == 4 ? route('seller.home') : route('home');
                $this->dispatch('registration-success', message: 'Registration successful!', redirectUrl: $this->redirectUrl);
                return;
            }else if(DB::table('suppliers')->where('mobile_number', $mobile)->exists()){
                $user = DB::table('suppliers')->where('mobile_number', $mobile)->first();
                Auth::login($user);
                \Log::info('submitStep12_old: Supplier user logged in', ['user_id' => $user->id, 'role_id' => $user->role_id]);
                $this->redirectUrl = $user->role_id == 4 ? route('seller.home') : route('home');
                $this->dispatch('registration-success', message: 'Registration successful!', redirectUrl: $this->redirectUrl);
                return;
            }
             dd($this->form['otp']);
             
            $this->resetForm();
            return redirect()->route('home');
            $this->dispatch('update-step');
        } else {
            
            $this->errorMessage = 'Invalid OTP. Please try again.';
        }
    }

    public function sendLoginOtp()
    {
        $this->successMessage = '';
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
        if (request()->hasCookie($cookieName)) {
            $user = \App\Models\User::where('mobile', '+' . preg_replace('/\\D/', '', (string) $this->form['country_code']) . preg_replace('/\\D/', '', (string) $this->form['mobile_number']))->first();
            if ($user) {
                \Auth::login($user);
                \Log::info('sendLoginOtp: User logged in via cookie', ['user_id' => $user->id, 'role_id' => $user->role_id]);
                $this->redirectUrl = $user->role_id == 4 ? route('seller.home') : route('home');
                $this->dispatch('registration-success', message: 'Registration successful!', redirectUrl: $this->redirectUrl);
                return;
            }
        }
        $countryCode = preg_replace('/\\D/', '', (string) $this->form['country_code']);
        $mobileNumber = preg_replace('/\\D/', '', (string) $this->form['mobile_number']);
        $mobile = '+' . $countryCode . $mobileNumber;
        if ($countryCode === '357' && strlen($mobileNumber) > 8) {
            $mobileNumber = substr($mobileNumber, 0, 8);
            $mobile = '+357' . $mobileNumber;
        }
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
        if ($httpCode >= 200 && $httpCode < 300 && isset($responseData['status']) && $responseData['status'] === 'approved') {
            $user = \App\Models\User::where('mobile', $mobile)->first();
            if ($user) {
                \Auth::login($user);
                \Log::info('verifyLoginOtp: User logged in', ['user_id' => $user->id, 'role_id' => $user->role_id]);
                $this->redirectUrl = $user->role_id == 4 ? route('seller.home') : route('home');
                $this->dispatch('registration-success', message: 'Registration successful!', redirectUrl: $this->redirectUrl);
                return;
            }
            $supplier = DB::table('suppliers')->where('mobile_number', $mobile)->first();
            if ($supplier && $supplier->user_id) {
                $user = \App\Models\User::find($supplier->user_id);
                if ($user) {
                    \Auth::login($user);
                    \Log::info('verifyLoginOtp: Supplier user logged in', ['user_id' => $user->id, 'role_id' => $user->role_id]);
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
}