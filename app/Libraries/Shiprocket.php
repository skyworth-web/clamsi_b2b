<?php

namespace App\Libraries;

use Illuminate\Http\Request;

class Shiprocket
{
    private $email = "";
    private $password = "";
    private $url = "https://apiv2.shiprocket.in/v1/external/";

    public function __construct()
    {
        $settings = getSettings('shipping_method', true);
        $settings = json_decode($settings, true);
        $this->url = "https://apiv2.shiprocket.in/v1/external/";
        $this->email = isset($settings['email']) ? $settings['email'] : '';
        $this->password = isset($settings['password']) ? $settings['password'] : '';
    }

    public function get_credentials()
    {
        $data['email'] = $this->email;
        $data['password'] = $this->password;
        return $data;
    }

    public function generate_token()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://apiv2.shiprocket.in/v1/external/auth/login',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
            "email":"' . $this->email . '",
            "password": "' . $this->password . '"
        }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));
        $result = curl_exec($curl);
        $response = (!empty($result)) ? json_decode($result, true) : "";

        curl_close($curl);
        $token = (isset($response['token'])) ? $response['token'] : "";
        return $token;
    }
    public function curl($url, $method = 'GET', $data = [])
    {
        // dd($data);
        $token = $this->generate_token();
        // dd($token);
        $ch = curl_init();
        $headers = array(
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        );
        $curl_options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_HTTPHEADER => $headers
        );
        if (strtolower($method) == 'post') {
            $curl_options[CURLOPT_POST] = 1;
            $curl_options[CURLOPT_POSTFIELDS] = $data;
        } else {
            $curl_options[CURLOPT_CUSTOMREQUEST] = 'GET';
        }
        curl_setopt_array($ch, $curl_options);

        $result = curl_exec($ch);
        // dd($result);
        $result = (!empty($result)) ? json_decode($result, 1) : $result;

        return $result;
    }

    public function check_serviceability($data)
    {
        $pickup_location = (isset($data['pickup_postcode']) && !empty($data['pickup_postcode'])) ? $data['pickup_postcode'] : "";
        $delivery_pincode = (isset($data['delivery_postcode']) && !empty($data['delivery_postcode'])) ? $data['delivery_postcode'] : "";
        $weight = (isset($data['weight']) && !empty($data['weight'])) ? $data['weight'] : "";
        $cod = (isset($data['cod']) && !empty($data['cod'])) ? $data['cod'] : 0;

        $query = array(
            "pickup_postcode" => $pickup_location,
            "delivery_postcode" => $delivery_pincode,
            "weight" => $weight,
            "cod" => $cod
        );

        $qry_str = http_build_query($query);

        $url = $this->url . 'courier/serviceability/?' . $qry_str;

        $result = $this->curl($url);
        return $result;
    }

    public function cancel_order($shipment_id)
    {
        $url = $this->url . 'orders/cancel';
        $data = array(
            'ids' => [$shipment_id]
        );
        $result = $this->curl($url, "POST", json_encode($data));
        return $result;
    }

    public function get_specific_order($order_id)
    {
        // firebase server url to send the curl request

        $url = $this->url . 'orders/show/' . $order_id;
        $result = $this->curl($url);

        //and return the result
        return $result;
    }

    public function create_order($data)
    {
        // firebase server url to send the curl request
        $url = $this->url . 'orders/create/adhoc';

        //building headers for the request

        $data = json_encode($data);
        $result = $this->curl($url, $method = 'POST', $data);
        return $result;
    }

    public function generate_awb($shipment_id)
    {
        $url = $this->url . 'courier/assign/awb';
        $data = array(
            'shipment_id' => $shipment_id,
        );
        $result = $this->curl($url, "POST", json_encode($data));

        return $result;
    }

    public function get_order($shipment_id)
    {
        // firebase server url to send the curl request

        $url = $this->url . 'shipments/' . $shipment_id;
        $result = $this->curl($url);

        //and return the result
        return $result;
    }
    public function request_for_pickup($shipment_id)
    {
        // firebase server url to send the curl request
        $url = $this->url . 'courier/generate/pickup';

        $shipment_id = array('shipment_id' => $shipment_id);
        $result = $this->curl($url, "POST", json_encode($shipment_id));

        //and return the result
        return $result;
    }
    public function generate_label($shipment_id)
    {
        $url = $this->url . 'courier/generate/label';
        $data = array(
            'shipment_id' => [$shipment_id]
        );
        $result = $this->curl($url, 'POST', json_encode($data));
        return $result;
    }
    public function generate_invoice($order_id)
    {
        $url = $this->url . 'orders/print/invoice';
        $data = array(
            'ids' => [$order_id]
        );
        $result = $this->curl($url, 'POST', json_encode($data));
        return $result;
    }

    public function add_pickup_location($data)
    {
        // firebase server url to send the curl request
        // dd($data);
        $url = $this->url . 'settings/company/addpickup';
        $result = $this->curl($url, "POST", json_encode($data));
        // dd($result);
        //and return the result

        return $result;
    }
    public function tracking_order($tracking_id)
    {

        $url = $this->url . 'courier/track?order_id=' . $tracking_id;
        //building headers for the request
        $result = $this->curl($url, 'GET');
        // dd($result);
        return $result;
    }
}
