<?php


function parseSmsString($string, $data = [])
{

    foreach ($data as $key => $val) {

        if ($val != null) {
            $string = str_replace("{" . $key . "}", $val, $string);
        } else {
            $string = str_replace("{" . $key . "}", "NULL", $string);
        }
    }
    return $string;
}

/**
 *
 ** This function sends verifies the modules and sends sms for email from config saved in database.
 *@param array $emails = [
 *    "customer" => [],
 *    "admin" => [],
 *    "seller" => [],
 *    "delivery_boy" => []
 *]
 * @param array $phone = [
 *    "customer" => [],
 *    "admin" => [],
 *    "seller" => [],
 *    "delivery_boy" => []
 *]
 * @param string $event
 * This the the event like place_order, update_order_status, etc...
 * @return array [
 *   "error" => bool,
 *   "message" => string,
 *   "data" => mixed
 *]
 */

function send_sms($phone, $msg, $country_code = "+91")
{
    $data = getSettings('sms_gateway_settings', true);
    $data = json_decode($data, true);
    // dd($data);
    $data["body"] = [];
    if ($data["body_key"] != null) {
        for ($i = 0; $i < count($data["body_key"]); $i++) {
            $key = $data["body_key"][$i];
            $value = parse_sms($data["body_value"][$i], json_encode($phone), $msg, $country_code);

            $data["body"][$key] = $value;
        }
    }
    $data["header"] = [];
    if ($data["header_key"] != null) {

        for ($i = 0; $i < count($data["header_key"]); $i++) {
            $key = $data["header_key"][$i];
            $value = parse_sms($data["header_value"][$i], json_encode($phone), $msg, $country_code);

            $data["header"][] = $key . ": " . $value;
        }
    }
    $data["params"] = [];
    if ($data["params_key"] != null) {
        for ($i = 0; $i < count($data["params_key"]); $i++) {
            $key = $data["params_key"][$i];
            $value = parse_sms($data["params_value"][$i], json_encode($phone), $msg, $country_code);

            $data["params"][$key] = $value;
        }
    }
    return curl_sms($data["base_url"], $data["sms_gateway_method"], $data["body"], $data["header"]);
    // dd($data);
}

function curl_sms($url, $method = 'GET', $data = [], $headers = [])
{

    $ch = curl_init();
    $curl_options = array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded',
        )
    );

    if (count($headers) != 0) {

        $curl_options[CURLOPT_HTTPHEADER] = $headers;
    }

    if (strtolower($method) == 'post') {
        $curl_options[CURLOPT_POST] = 1;
        $curl_options[CURLOPT_POSTFIELDS] = http_build_query($data);
    } else {
        $curl_options[CURLOPT_CUSTOMREQUEST] = 'GET';
    }
    curl_setopt_array($ch, $curl_options);

    $result = array(
        'body' => json_decode(curl_exec($ch), true),
        'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
    );

    return $result;
}
