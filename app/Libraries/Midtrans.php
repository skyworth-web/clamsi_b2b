<?php

namespace App\Libraries;

use Illuminate\Support\Facades\Http;

class Midtrans
{
    public function create_transaction($order_id, $amount)
    {

        $payment_method_settings = getsettings('payment_method', true);
        $payment_method_settings = json_decode($payment_method_settings, true);


        $url = (isset($payment_method_settings['midtrans_payment_mode']) && $payment_method_settings['midtrans_payment_mode'] == "sandbox") ? 'https://app.sandbox.midtrans.com/' : 'https://app.midtrans.com/';

        $data = array(
            'order_id' => $order_id,
            'gross_amount' => intval($amount),
        );
        $final_data['transaction_details'] = $data;
        $url = $url . 'snap/v1/transactions';
        $method = 'POST';
        $response = $this->curl($url, $method, $final_data);
        return $response;
    }
    public function curl($url, $method = 'GET', $data = [])
    {

        $payment_method_settings = getsettings('payment_method', true);
        $payment_method_settings = json_decode($payment_method_settings, true);

        $server_key = (isset($payment_method_settings['midtrans_server_key'])) ? $payment_method_settings['midtrans_server_key'] : "";
        $ch = curl_init();
        $curl_options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 0,
            // Add header to the request, including Authorization generated from server key
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode($server_key . ':')
            )
        );
        if (strtolower($method) == 'post') {
            $curl_options[CURLOPT_POST] = 1;
            $curl_options[CURLOPT_POSTFIELDS] = json_encode($data);
        } else {
            $curl_options[CURLOPT_CUSTOMREQUEST] = 'GET';
        }
        curl_setopt_array($ch, $curl_options);
        $result = array(
            'body' => curl_exec($ch),
            'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
        );
        return $result;
    }
}
