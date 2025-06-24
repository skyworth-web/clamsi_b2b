<?php

namespace App\Http\Controllers;

use App\Models\Otps;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function send_otp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            $response = [
                'error' => true,
                'message' => $validator->errors()->all(),
                'code' => 102,
            ];
            return response()->json($response);
        }
        $otp = random_int(100000, 999999);
        $data = [
            'mobile' => $request['mobile'],
            'otp' => $otp,
            'varified' => 0
        ];

        $res = send_sms($request['mobile'], "please don't share with anyone $otp");
        if ($res['http_code'] == 201) {
            if (!isExist(['mobile' => $request['mobile']], 'otps')) {
                Otps::create($data);
            }
            $otp_details = Otps::where('mobile',$request['mobile']);
            $otp_details->update($data);
            return [
                "error" => false,
                "message" => "OTP send successfully.",
                "data" => $data
            ];
        }
        return [
            "error" => true,
            "message" => "Something went wrong."
        ];
    }

    public function verify_otp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|numeric',
            'verification_code' => 'required'
        ]);
        if ($validator->fails()) {
            $response = [
                'error' => true,
                'message' => $validator->errors()->all(),
                'code' => 102,
            ];
            return response()->json($response);
        }
        $otp_details = Otps::where('mobile', $request['mobile'])->first();
        if ($otp_details != null) {
            if ($otp_details['otp'] == $request['verification_code']) {
                $data = [
                    'varified' => 1
                ];
                $otp_details->update($data);
                return [
                    "error" => false,
                    "message" => "Verification Code Varified Successfully."
                ];
            }
            return [
                "error" => true,
                "message" => "Verification Failed, Code Incorrect!"
            ];
        }
        return [
            "error" => true,
            "message" => "Something Went Wrong Please Try Again."
        ];
    }
}
