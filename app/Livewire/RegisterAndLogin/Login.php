<?php

namespace App\Livewire\RegisterAndLogin;

use App\Models\User;
use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class Login extends Component
{
    public $mobile = "";
    public $password = "";

    public function mount(){
        $this->mobile = (config('constants.ALLOW_MODIFICATION') == 0) ? "9876543210" : "";
        $this->password = (config('constants.ALLOW_MODIFICATION') == 0) ? "12345678" : "";
    }
    public function render()
    {
        return view('livewire.' . config('constants.theme') . '.register-and-login.login')->title("Sign In |");
    }

    // public function login(Request $request)
    // {
    //     $validator = Validator::make([
    //         'mobile' => $this->mobile,
    //         'password' => $this->password,
    //     ],[
    //         'mobile' => ['required', Rule::exists('users', 'mobile')],
    //         'password' => 'required'
    //     ],[
    //         'mobile.exists' => 'Mobile Number is Not Registered'
    //     ]);

    //     if ($validator->fails()) {
    //         $errors = $validator->errors();
    //         $this->dispatch('validationErrorshow',['data' => $errors]);
    //         return;
    //     }


    //     $user = User::where('mobile', $this->password)->first();
    //     $device = $request->header('sec-ch-ua-platform');
    //     $date = new \DateTime();
    //     $currentDateTime = $date->format('Y-m-d H:i:s');
    //     $timeZone = $date->getTimezone()->getName();
    //     $data = [
    //         'device' => $device,
    //         'currentDateTime' => $currentDateTime,
    //         'timeZone' => $timeZone
    //     ];
    //     $validate['mobile'] = $this->mobile;
    //     $validate['password'] = $this->password;
    //     if (Auth::attempt($validate)) {
    //         try {
    //             sendMailTemplate(to: $user['email'], template_key: "user_login", data: [
    //                 "username" => $user['username'],
    //                 "device" => $data['device'],
    //                 "currentDateTime" => $data['currentDateTime'],
    //                 "timeZone" => $data['timeZone']
    //             ]);
    //         } catch (\Throwable $th) {}
    //         $this->dispatch('showSuccess','User Loggedin Successfully');
    //         return redirect('/');
    //     }
    //     return $this->dispatch('showError','Invalid Credentials');
    // }
    public function login(Request $request)
{
    $validator = Validator::make([
        'mobile' => $this->mobile,
        'password' => $this->password,
    ], [
        'mobile' => ['required', Rule::exists('users', 'mobile')],
        'password' => 'required'
    ], [
        'mobile.exists' => 'Mobile Number is Not Registered'
    ]);

    if ($validator->fails()) {
        $errors = $validator->errors();
        $this->dispatch('validationErrorshow', ['data' => $errors]);
        return;
    }

    $user = User::where('mobile', $this->mobile)->first(); // <-- fixed: was $this->password

    if (!$user) {
        return $this->dispatch('showError', 'User not found.');
    }

    if ($user->active != 1) {
        return $this->dispatch('showError', 'Your account has been deactivated.');
    }

    $device = $request->header('sec-ch-ua-platform');
    $date = new \DateTime();
    $currentDateTime = $date->format('Y-m-d H:i:s');
    $timeZone = $date->getTimezone()->getName();
    $data = [
        'device' => $device,
        'currentDateTime' => $currentDateTime,
        'timeZone' => $timeZone
    ];

    $validate = [
        'mobile' => $this->mobile,
        'password' => $this->password
    ];

    if (Auth::attempt($validate)) {
        try {
            sendMailTemplate(to: $user['email'], template_key: "user_login", data: [
                "username" => $user['username'],
                "device" => $data['device'],
                "currentDateTime" => $data['currentDateTime'],
                "timeZone" => $data['timeZone']
            ]);
        } catch (\Throwable $th) {
            // optional: log the error
        }

        $this->dispatch('showSuccess', 'User Logged in Successfully');
        return redirect('/');
    }

    return $this->dispatch('showError', 'Invalid Credentials');
}

}
