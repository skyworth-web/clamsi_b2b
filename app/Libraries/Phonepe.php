<?php
/*
    1. get_credentials()
    2. create_order($amount,$receipt='')
    3. fetch_payments($id ='')
    4. capture_payment($amount, $id, $currency = "INR")
    5. verify_payment($order_id, $razorpay_payment_id, $razorpay_signature)

    0. curl($url, $method = 'GET', $data = [])
*/
namespace App\Libraries;

class Phonepe
{
    private $salt_index = "";
    private $salt_key = "";
    private $merchant_id = "";
    private $url = "";
    private $environment = "";
    private $app_id = "";

    function __construct()
    {
        $settings = getSettings('payment_method', true,true);
        $settings = json_decode($settings);
        $this->salt_index = $settings->phonepe_salt_index ?? " ";
        $this->salt_key = $settings->phonepe_salt_key ?? " ";
        $this->merchant_id = $settings->phonepe_marchant_id ?? " ";
        $this->url = (isset($settings->phonepe_payment_mode) && $settings->phonepe_payment_mode == "PRODUCTION") ? "https://api.phonepe.com/apis/hermes" : "https://api-preprod.phonepe.com/apis/pg-sandbox";

        if(isset($settings->phonepe_payment_mode)){
            if ($settings->phonepe_payment_mode == 'PRODUCTION') {
                $this->environment = 'PRODUCTION';
            } elseif($settings->phonepe_payment_mode == 'UAT'){
                $this->environment = 'UAT';
            } else {
                $this->environment = 'SANDBOX';
            }
        }
    }

    public function get_credentials()
    {
        $data['salt_index'] = $this->salt_index;
        $data['salt_key'] = $this->salt_key;
        $data['merchant_id'] = $this->merchant_id;
        $data['url'] = $this->url;
        return $data;
    }

    public function pay($data)
    {
        $data['merchantId'] = $this->merchant_id;
        $data['paymentInstrument'] = array(
            'type' => 'PAY_PAGE',
        );
        $url = $this->url . '/pg/v1/pay';
        $method = 'POST';
        /** generating a X-VERIFY header */
        $encode = base64_encode(json_encode($data));
        $saltKey = $this->salt_key;
        $saltIndex = $this->salt_index;
        $string = $encode . '/pg/v1/pay' . $saltKey;
        $sha256 = hash('sha256', $string);
        $finalXHeader = $sha256 . '###' . $saltIndex;

        $header = [
            "Content-Type: application/json",
            "accept: application/json",
            "X-VERIFY: $finalXHeader"
        ];
        $response = $this->curl($url, $method, json_encode(['request' => $encode]), $header);
        $res = json_decode($response['body'], true);
        return $res;
    }

    public function phonepe_checksum($data)
    {
        $phonePeMerId = $this->merchant_id;
        $phonePeEndPointUrl = url("admin/webhook/phonepe_webhook");
        // $phonePeEndPointUrl = "	https://webhook.site/55e4ad5a-cf46-4d85-bb93-3f3ced4c0917";
        $phonePeSaltKey = $this->salt_key;
        $phonePeSaltIndex = $this->salt_index;
        $totalPrice = $data['final_total'];
        $userMobileNumber = $data['mobile'];

        $amt = (int)round($totalPrice * 100);
        $jsonData = [
            "merchantId" => $phonePeMerId,
            "merchantUserId"=> $phonePeMerId,
            "merchantTransactionId" => $data['order_id'],
            "amount" => $amt,
            "redirectUrl" => $phonePeEndPointUrl,
            "redirectMode" => "REDIRECT",
            "callbackUrl" => $phonePeEndPointUrl,
            "mobileNumber" => $userMobileNumber,
            "paymentInstrument" => ["type" => "PAY_PAGE"]
        ];
        $base64Data = base64_encode(json_encode($jsonData,JSON_UNESCAPED_SLASHES));
        $apiEndPoint = "/pg/v1/pay";
        $dataToHash = $base64Data . $apiEndPoint . $phonePeSaltKey;
        $sHA256 = hash('sha256', $dataToHash);
        $checksum = $sHA256 . '###' . $phonePeSaltIndex;
        $data =  [
            "payload" => $jsonData,
            "checksum" => $checksum
        ];
        return $data;
    }

    public function check_status($id = '')
    {
        $data['merchantId'] = $this->merchant_id;
        $data['paymentInstrument'] = array(
            'type' => 'PAY_PAGE',
        );
        $endpoint = "/pg/v1/status/$this->merchant_id/$id";
        $url = $this->url . $endpoint;
        $method = 'GET';

        /** generating a X-VERIFY header */
        $saltKey = $this->salt_key;
        $saltIndex = $this->salt_index;
        $string = $endpoint . "" . $saltKey;
        $sha256 = hash('sha256', $string);
        $finalXHeader = $sha256 . '###' . $saltIndex;

        $header = [
            "Content-Type: application/json",
            "X-VERIFY: $finalXHeader",
            "X-MERCHANT-ID: $this->merchant_id",
        ];
        $response = $this->curl($url, $method, [], $header);
        $res = json_decode($response['body'], true);
        return $res;
    }
    public function curl($url, $method = 'POST', $data = [], $header = [])
    {
        $ch = curl_init();
        $curl_options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_HTTPHEADER => $header
        );
        if (strtolower($method) == 'post') {
            $curl_options[CURLOPT_POST] = 1;
            if (!empty($data)) {
                $curl_options[CURLOPT_POSTFIELDS] = $data;
            }
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
