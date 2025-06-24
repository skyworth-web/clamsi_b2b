<?php

namespace App\Libraries;

use Illuminate\Support\Facades\Http;

class Paystack
{

    private $secret_key;
    private $public_key;

    function __construct()
    {
        $payment_method_settings = getsettings('payment_method', true, true);
        $payment_method_settings = json_decode($payment_method_settings, true);

        $this->secret_key = $payment_method_settings['paystack_secret_key'] ?? "";
        $this->public_key = $payment_method_settings['paystack_key_id'] ?? "";
    }

    public function get_credentials()
    {
        $data['public_key'] = $this->public_key;
        return response()->json($data);
    }
    public function initialize_payment($data)
    {
        $amount = $data['amount'] * 100;
        $reference = 'PSK_' . uniqid();

        $fields = [
            'email' => $data['email'],
            'amount' => $amount,
            'reference' => $reference,
            'callback_url' => url('/api/paystack_payment/callback')
        ];

        $end_point = "https://api.paystack.co/transaction/initialize";
        $method = "POST";

        $response = $this->curl_request($end_point, $method, http_build_query($fields));
        $result = json_decode($response, true);
        // dd($result);
        if ($result && $result['status']) {
            return [
                'error' => false,
                'data' => [
                    'status' => $result['status'],
                    'authorization_url' => $result['data']['authorization_url'],
                    'access_code' => $result['data']['access_code'],
                    'reference' => $result['data']['reference'],
                    'callback_url' => $fields['callback_url']
                ]
            ];
        } else {
            return [
                'error' => true,
                'message' => 'Payment initialization failed.'
            ];
        }
    }


    public function verify_transaction($reference = '')
    {
        $url = "https://api.paystack.co/";

        $end_point = $url . "transaction/verify";
        $end_point .= "/" . $reference ?? "";
        $method = "get";
        $transfer = $this->curl_request($end_point, $method);
        // dd($transfer);
        return $transfer;
    }

    private function curl_request($end_point, $method, $data = array())
    {
        $curl = curl_init();
        $secret_key = $this->secret_key;

        curl_setopt_array($curl, array(
            CURLOPT_URL => $end_point,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_POSTFIELDS => $data,   /* array('test_key' => 'test_value_1') */
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $secret_key
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
}
