<?php

namespace App\Services;

class TwilioOtpService
{
    protected $accountSid;
    protected $authToken;
    protected $verifyServiceSid;

    public function __construct()
    {
        $this->accountSid = config('services.twilio.sid');
        $this->authToken = config('services.twilio.token');
        $this->verifyServiceSid = config('services.twilio.verify_sid');
    }

    /**
     * Send an OTP to the given mobile number using Twilio Verify API.
     * @param string $mobile E.164 format (e.g. +1234567890)
     * @return array [success, response, http_code, curl_error]
     */
    public function sendOtp($mobile)
    {
        // TEMPORARILY BYPASSED FOR TESTING - TWILIO API CALL COMMENTED OUT
        /*
        $url = "https://verify.twilio.com/v2/Services/{$this->verifyServiceSid}/Verifications";
        $data = [
            'To' => $mobile,
            'Channel' => 'sms',
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->accountSid}:{$this->authToken}");
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        */
        
        // TEMPORARY: Always return success for testing
        return [
            'success' => true,
            'response' => '{"status": "pending"}',
            'http_code' => 200,
            'curl_error' => null,
        ];
    }

    /**
     * Verify an OTP for the given mobile number using Twilio Verify API.
     * @param string $mobile E.164 format (e.g. +1234567890)
     * @param string $otp
     * @return array [success, response, http_code, curl_error]
     */
    public function verifyOtp($mobile, $otp)
    {
        // TEMPORARILY BYPASSED FOR TESTING - TWILIO API CALL COMMENTED OUT
        /*
        $url = "https://verify.twilio.com/v2/Services/{$this->verifyServiceSid}/VerificationCheck";
        $data = [
            'To' => $mobile,
            'Code' => $otp,
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->accountSid}:{$this->authToken}");
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        $responseData = json_decode($response, true);
        */
        
        // TEMPORARY: Always return success for testing (you can modify this to check specific OTPs)
        $responseData = ['status' => 'approved'];
        return [
            'success' => true,
            'response' => $responseData,
            'http_code' => 200,
            'curl_error' => null,
        ];
    }
} 