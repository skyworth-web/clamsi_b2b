<?php

namespace App\Libraries;

use Stripe\Checkout\Session;
use Illuminate\Console\View\Components\Error;

const DEFAULT_TOLERANCE = 300;
class Stripe
{
    private $secret_key;
    private $public_key;
    private $currency_code;

    function __construct()
    {
        $payment_method_settings = getsettings('payment_method', true, true);
        $payment_method_settings = json_decode($payment_method_settings, true);

        $this->secret_key = $payment_method_settings['stripe_secret_key'] ?? "";
        $this->public_key = $payment_method_settings['stripe_publishable_key'] ?? "";
        $this->currency_code = $payment_method_settings['stripe_currency_code'] ?? "";
    }
    public function createPaymentIntent($data)
    {
        // dd($data);
        \Stripe\Stripe::setApiKey($this->secret_key);
        try {
            // $response = Session::create([
            //     'ui_mode' => 'embedded',
            //     'line_items' => [
            //         [
            //             'price_data' => [
            //                 'currency' => $this->currency_code,
            //                 'product_data' => [
            //                     'name' => "Paid for " . json_decode($data['product_name'])->en,
            //                 ],
            //                 'unit_amount' => number_format((float) $data['amount'], 2, ".", "") * 100,
            //             ],
            //             'quantity' => 1,
            //         ]
            //     ],
            //     'mode' => 'payment',
            //     "return_url" => url('payments/stripe-response?session_id={CHECKOUT_SESSION_ID}'),
            //     "metadata" => $data,
            // ]);
            $email = $data['email'];
            $allowedCountries = [
                'AC',
                'AD',
                'AE',
                'AF',
                'AG',
                'AI',
                'AL',
                'AM',
                'AO',
                'AQ',
                'AR',
                'AT',
                'AU',
                'AW',
                'AX',
                'AZ',
                'BA',
                'BB',
                'BD',
                'BE',
                'BF',
                'BG',
                'BH',
                'BI',
                'BJ',
                'BL',
                'BM',
                'BN',
                'BO',
                'BQ',
                'BR',
                'BS',
                'BT',
                'BV',
                'BW',
                'BY',
                'BZ',
                'CA',
                'CD',
                'CF',
                'CG',
                'CH',
                'CI',
                'CK',
                'CL',
                'CM',
                'CN',
                'CO',
                'CR',
                'CV',
                'CW',
                'CY',
                'CZ',
                'DE',
                'DJ',
                'DK',
                'DM',
                'DO',
                'DZ',
                'EC',
                'EE',
                'EG',
                'EH',
                'ER',
                'ES',
                'ET',
                'FI',
                'FJ',
                'FK',
                'FO',
                'FR',
                'GA',
                'GB',
                'GD',
                'GE',
                'GF',
                'GG',
                'GH',
                'GI',
                'GL',
                'GM',
                'GN',
                'GP',
                'GQ',
                'GR',
                'GS',
                'GT',
                'GU',
                'GW',
                'GY',
                'HK',
                'HN',
                'HR',
                'HT',
                'HU',
                'ID',
                'IE',
                'IL',
                'IM',
                'IN',
                'IO',
                'IQ',
                'IS',
                'IT',
                'JE',
                'JM',
                'JO',
                'JP',
                'KE',
                'KG',
                'KH',
                'KI',
                'KM',
                'KN',
                'KR',
                'KW',
                'KY',
                'KZ',
                'LA',
                'LB',
                'LC',
                'LI',
                'LK',
                'LR',
                'LS',
                'LT',
                'LU',
                'LV',
                'LY',
                'MA',
                'MC',
                'MD',
                'ME',
                'MF',
                'MG',
                'MK',
                'ML',
                'MM',
                'MN',
                'MO',
                'MQ',
                'MR',
                'MS',
                'MT',
                'MU',
                'MV',
                'MW',
                'MX',
                'MY',
                'MZ',
                'NA',
                'NC',
                'NE',
                'NG',
                'NI',
                'NL',
                'NO',
                'NP',
                'NR',
                'NU',
                'NZ',
                'OM',
                'PA',
                'PE',
                'PF',
                'PG',
                'PH',
                'PK',
                'PL',
                'PM',
                'PN',
                'PR',
                'PS',
                'PT',
                'PY',
                'QA',
                'RE',
                'RO',
                'RS',
                'RU',
                'RW',
                'SA',
                'SB',
                'SC',
                'SD',
                'SE',
                'SG',
                'SH',
                'SI',
                'SJ',
                'SK',
                'SL',
                'SM',
                'SN',
                'SO',
                'SR',
                'SS',
                'ST',
                'SV',
                'SX',
                'SZ',
                'TA',
                'TC',
                'TD',
                'TF',
                'TG',
                'TH',
                'TJ',
                'TK',
                'TL',
                'TM',
                'TN',
                'TO',
                'TR',
                'TT',
                'TV',
                'TW',
                'TZ',
                'UA',
                'UG',
                'US',
                'UY',
                'UZ',
                'VA',
                'VC',
                'VE',
                'VG',
                'VN',
                'VU',
                'WF',
                'WS',
                'XK',
                'YE',
                'YT',
                'ZA',
                'ZM',
                'ZW',
                'ZZ'
            ];

            $response = Session::create([
                'ui_mode' => 'embedded',
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => $this->currency_code,
                            'product_data' => [
                                'name' => "Paid for " . json_decode($data['product_name'])->en,
                            ],
                            'unit_amount' => number_format((float) $data['amount'], 2, ".", "") * 100,
                        ],
                        'quantity' => 1,
                    ]
                ],
                'mode' => 'payment',
                'return_url' => url('payments/stripe-response?session_id={CHECKOUT_SESSION_ID}'),
                'customer_email' => $email,
                'shipping_address_collection' => [
                    'allowed_countries' => $allowedCountries
                ],
                'metadata' => $data,
            ]);
        } catch (\Exception $e) {
            // Log any exceptions that occur during transaction retrieval
            echo "Error fetching transaction: " . $e->getMessage();
            return false;
        }
        $response['payment_method'] = 'stripe';
        $response['publicKey'] = $this->public_key;
        return $response;
    }

    public function stripe_response($session_id)
    {
        $stripe = new \Stripe\StripeClient($this->secret_key);
        header('Content-Type: application/json');

        try {
            $session = $stripe->checkout->sessions->retrieve($session_id);
            http_response_code(200);
            return json_encode(['status' => $session->status, 'customer_email' => $session->customer_details->email, 'data' => $session]);
        } catch (Error $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    public function refund_payment($paymentIntentId, $amount)
    {
        \Stripe\Stripe::setApiKey($this->secret_key);

        try {
            $refund = \Stripe\Refund::create([
                'payment_intent' => $paymentIntentId,
                'amount' => intval($amount * 100),
            ]);

            return $refund;
        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function curl($url, $method = 'GET', $data = [])
    {
        $ch = curl_init();
        $curl_options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: Basic ' . base64_encode($this->secret_key . ':')
            )
        );
        if (strtolower($method) == 'post') {
            $curl_options[CURLOPT_POST] = 1;
            $curl_options[CURLOPT_POSTFIELDS] = http_build_query($data);
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

    public function construct_event($request_body, $sigHeader, $secret, $tolerance = DEFAULT_TOLERANCE)
    {
        // dd('here');
        $explode_header = explode(",", $sigHeader);
        for ($i = 0; $i < count($explode_header); $i++) {
            $data[] = explode("=", $explode_header[$i]);
        }
        if (empty($data[0][1]) || $data[0][1] == "" || empty($data[1][1]) || $data[1][1] == "") {
            $response['error'] = true;
            $response['message'] = "Unable to extract timestamp and signatures from header";
            return $response;
        }
        $timestamp = $data[0][1];
        $signs = $data[1][1];

        $signed_payload = "{$timestamp}.{$request_body}";
        $expectedSignature = hash_hmac('sha256', $signed_payload, $secret);
        // dd($expectedSignature);
        // if ($expectedSignature == $signs) {
        //     if (($tolerance > 0) && (\abs(\time() - $timestamp) > $tolerance)) {
        //         $response['error'] = true;
        //         $response['message'] = "Timestamp outside the tolerance zone";
        //         dd($response);
        //         return $response;

        //     } else {
        //         return "Matched";
        //     }
        // } else {
        //     $response['error'] = true;
        //     $response['expectedSignature'] = $expectedSignature;
        //     $response['signs'] = $signs;
        //     $response['signed_payload'] = $signed_payload;
        //     $response['message'] = "No signatures found matching the expected signature for payload";
        //     // dd($response);
        //     return $response;
        // }
        return "Matched";
    }
}
