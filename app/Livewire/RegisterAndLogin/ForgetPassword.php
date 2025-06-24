<?php

namespace App\Livewire\RegisterAndLogin;

use App\Models\User;
use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ForgetPassword extends Component
{
    public function render()
    {
        $system_settings = getSettings('system_settings', true, true);
        $system_settings = json_decode($system_settings);
        $authentication_method = $system_settings->authentication_method ?? "";
        return view('livewire.' . config('constants.theme') . '.register-and-login.forget-password', [
            'authentication_method' => $authentication_method
        ])->title("Password Recovery |");
    }

    public function check_number(Request $request)
    {
        $mobile = $request->input('mobile');
        $user = User::where('mobile', $mobile)->first();
        if ($user) {
            return response()->json(['error' => false, 'message' => 'Mobile Number Registered']);
        } else {
            return response()->json(['error' => true, 'message' => 'Mobile Number is Not Registered']);
        }
    }

    public function new_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required',
            'new_password' => 'required',
            'verify_password' => 'required_with:new_password|same:new_password|min:8',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $response['error'] = true;
            $response['message'] = $errors;
            return $response;
        }

        $user_data = User::where('mobile', $request['mobile'])->first();
        $password = bcrypt($request['verify_password']);
        $user_data->update([
            'password' => $password,
        ]);
        if ($user_data) {
            try {
                sendMailTemplate(to: $user_data['email'], template_key: "forget_password", data: [
                    "username" => $user_data['username']
                ]);
            } catch (\Throwable $th) {
            }
            $response = [
                'error' => false,
                'message' => 'Password Updated successfully!'
            ];
            return $response;
        }
        $response = [
            'error' => true,
            'message' => 'Something Went Wrong Please Try Again Later!!'
        ];
        return $response;
    }
}
