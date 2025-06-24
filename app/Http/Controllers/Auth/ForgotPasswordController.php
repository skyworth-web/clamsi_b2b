<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;


use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{

    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        if (!isExist(['mobile' => $request->mobile], 'users')) {
            return response()->json(['error' => true, 'error_message' => 'Mobile number does not exist']);
        }
        if (isEmailConfigured()) {
            if ($request['mobile'] == null) {
                return response()->json(['error_message' => 'Please enter mobile number']);
            }

            $email = fetchDetails('users', ['mobile' => $request['mobile']], 'email')[0]->email;

            // Determine whether it's a user or client based on the input data
            $provider = $this->determineProvider($email);

            $subject = "test mail";
            $emailMessage = "this is test mail for forgot password";
            $attachment = "";


            // Send the password reset link
            try {

                $response = Password::broker()->sendResetLink(
                    ['email' => $email]
                );

                if ($response == Password::RESET_LINK_SENT) {
                    return response()->json(['error' => false, 'message' => __($response)]);
                } else {

                    return response()->json(['error' => true, 'error_message' => __($response)]);
                }
            } catch (\Exception $e) {
                // Handle the exception here
                return response()->json(['error' => true, 'error_message' => 'Password reset link couldn\'t sent, please check email settings.']);
            }
        } else {
            return response()->json(['error' => true, 'error_message' => 'Password reset link couldn\'t sent, please configure email settings.']);
        }
    }

    public function showResetPasswordForm($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function ResetPassword(Request $request)
    {
        $request->validate([]);
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|numeric',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'error_message' => $validator->errors()->all(),
            ]);
        } else {
            $status = Password::broker()->reset(
                $request->only('mobile', 'password', 'password_confirmation', 'token'),
                function (User $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->setRememberToken(Str::random(60));

                    $user->save();
                    if (isEmailConfigured()) {
                        event(new PasswordReset($user));
                    }
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return response()->json(['error' => false, 'message' => __($status)]);
            } else {
                return response()->json(['error' => true, 'error_message' => __($status)]);
            }
        }
    }

    protected function determineProvider($email)
    {
        // Determine whether the email belongs to a user or a client
        // You can customize this logic based on your application's requirements
        return User::where('email', $email)->exists() ? 'users' : '';
    }
}
