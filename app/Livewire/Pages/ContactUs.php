<?php

namespace App\Livewire\Pages;

use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class ContactUs extends Component
{
    public $name = "";
    public $email = "";
    public $subject = "";
    public $message = "";
    public function render()
    {
        $web_settings = getSettings('web_settings', true, true);
        $contact_us = getSettings('contact_us', true, true);

        return view('livewire.' . config('constants.theme') . '.pages.contact-us', [
            "web_settings" => json_decode($web_settings, true),
            'contact_us' => json_decode($contact_us, true)
        ])->title("Contact us |");
    }

    public function send_contact_us_email()
    {
        $validated = Validator::make(
            [
                'name' => $this->name,
                'email' => $this->email,
                'message' => $this->message,
                'subject' => $this->subject,
            ],
            [
                'name' => 'required',
                'email' => 'required|email',
                'message' => 'required',
                'subject' => 'required',
            ]
        );
        if ($validated->fails()) {
            $errors = $validated->errors();
            $this->dispatch('validationErrorshow', ['data' => $errors]);
            $response['error'] = true;
            $response['message'] = $errors;
            return $response;
        }
        $from = $this->email;
        $subject = $this->subject;
        $emailMessage = $this->message;
        try {
            $mail = sendContactUsMail($from, $subject, $emailMessage, "");
        } catch (\Throwable $th) {
            $this->dispatch('showError', 'Something Went Wrong, Please Try Again Later.');
            return $this->addError('mailError', 'Something Went Wrong, Please Try Again Later.');
        }
        if ($mail['error'] == true) {
            $response['error'] = true;
            $response['message'] = "Cannot send mail. You can try to send mail manually.";
            $response['data'] = $mail['message'];
            session()->flash('message', $response['message']);
            return $this->redirect('/contact-us', navigate: true);
        } else {
            $response['error'] = false;
            $response['message'] = 'Mail sent successfully.';
            $response['data'] = array();
            session()->flash('message', $response['message']);
            return $this->redirect('/contact-us', navigate: true);
        }
    }
}
