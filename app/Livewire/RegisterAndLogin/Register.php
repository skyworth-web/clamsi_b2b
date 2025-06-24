<?php

namespace App\Livewire\RegisterAndLogin;

use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Validator;

class Register extends Component
{

    public $showWizard = false;
    public $username = "";
    public $otp = "";
    
    public $mobile = "";
    public $password = "";
    public $password_confirmation = "";

    public function render()
    {
        $system_settings = getSettings('system_settings', true, true);
        $system_settings = json_decode($system_settings);
        $authentication_method = $system_settings->authentication_method ?? "";
        return view('livewire.' . config('constants.theme') . '.register-and-login.register', [
            'authentication_method' => $authentication_method
        ])->title("Sign Up |");
    }

    public function store(Request $request)
    {
        if ((config('constants.ALLOW_MODIFICATION') == 0)) {
            $response['error'] = true;
            $response['message'] = "Register is Not Allowed In Demo Mode";
            return $response;
        }

        $validator = Validator::make(
            $request->all(),
            [
                'username' => 'required',
                'mobile' => 'required|numeric',
                'email' => ['required', 'email', Rule::unique('users', 'email')],
                'password' => 'required|confirmed|min:8'
            ]
        );
        if ($validator->fails()) {
            $errors = $validator->errors();
            $response['error'] = true;
            $response['message'] = $errors;
            return $response;
        }
        $data['username'] = $request['username'];
        $data['mobile'] = $request['mobile'];
        $data['email'] = $request['email'];
        $data['password'] = bcrypt($request['password']);
        $data['role_id'] = "2";
        $user = User::create($data);

        auth()->login($user);
        $response = [
            'error' => false,
            'message' => "Welcome " . $request['username'],
        ];
        try {
            sendMailTemplate(to: $data['email'], template_key: "welcome", data: [
                "username" => $data['username']
            ]);
        } catch (\Throwable $th) {
        }
        return $response;
    }

    public function check_mobile_number(Request $request)
    {
        if ((config('constants.ALLOW_MODIFICATION') == 0)) {
            $response['error'] = true;
            $response['allow_modification_error'] = true;
            $response['message'] = "Register is Not Allowed In Demo Mode";
            return $response;
        }

        $validator = Validator::make(
            $request->all(),
            [
                'mobile' => ['required', Rule::unique('users', 'mobile')],
            ]
        );
        if ($validator->fails()) {
            $errors = $validator->errors();
            $response['error'] = true;
            $response['message'] = $errors;
            return $response;
        }
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        $user = Socialite::driver('google')->user();
        $finduser = User::where('email', $user->email)->first();
        if ($finduser) {
            Auth::login($finduser);
            return redirect('/')->with('message', 'Logged In Successfully');;
        } else {
            $newUser = User::create([
                'username' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'image' => $user->avatar,
                'role_id' => "2",
                'active' => "1",
                'type' => "google",
            ]);
            Auth::login($newUser);
            redirect("/")->with('message', 'Registered Successfully');
            try {
                sendMailTemplate(to: $newUser['email'], template_key: "welcome", data: [
                    "username" => $newUser['username']
                ]);
            } catch (\Throwable $th) {
            }
            return;
        }
    }
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function handleFacebookCallback()
    {
        $user = Socialite::driver('facebook')->user();
        $finduser = User::where('email', $user->email)->first();
        if ($finduser) {
            Auth::login($finduser);
            return redirect('/')->with('message', 'Logged In Successfully');;
        } else {
            $newUser = User::create([
                'username' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'image' => $user->avatar,
                'active' => "1",
                'role_id' => "2",
                'type' => "facebook",
            ]);
            Auth::login($newUser);
            redirect("/")->with('message', 'Registered Successfully');
            try {
                sendMailTemplate(to: $newUser['email'], template_key: "welcome", data: [
                    "username" => $newUser['username']
                ]);
            } catch (\Throwable $th) {
            }
            return;
        }
    }
}
