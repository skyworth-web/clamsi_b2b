<?php
/*
    1. get_credentials()
*/
namespace App\Libraries;

class Paypal
{
    private $submit_btn = "";
    protected $paypal_mode;
    protected $paypal_business_email;
    protected $paypal_client_id;
    protected $currency_code;
    protected $paypal_url;
    protected $lastError;
    protected $ipnResponse;
    protected $ipnLogFile;
    protected $ipnLog;
    protected $fields = [];

    function __construct()
    {
        $settings = getSettings('payment_method', true, true);
        $settings = json_decode($settings);
        $this->paypal_mode = $settings->paypal_mode ?? "";
        $this->paypal_business_email = $settings->paypal_business_email ?? "";
        $this->paypal_client_id = $settings->paypal_client_id ?? "";
        $this->currency_code = $settings->currency_code ?? "";
        $this->paypal_url = ($this->paypal_mode == 'sandbox') ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';


        $this->lastError = '';
        $this->ipnResponse = '';
        $this->ipnLogFile = storage_path('logs/paypal_ipn.log');
        $this->ipnLog = true;

        $businessEmail = $settings->paypal_business_email;
        $this->addField('business', $this->paypal_business_email);
        $this->addField('rm', '2');
        $this->addField('cmd', '_xclick');
        $this->addField('currency_code', $this->currency_code);
        $this->addField('quantity', '1');
        $this->button('Pay Now!');
    }

    public function addField($name, $value)
    {
        $this->fields[$name] = $value;
    }

    protected function button($value)
    {
        $this->submit_btn = $value;
    }
    public function get_credentials()
    {
        $data['paypal_mode'] = $this->paypal_mode;
        $data['paypal_business_email'] = $this->paypal_business_email;
        $data['paypal_client_id'] = $this->paypal_client_id;
        $data['currency_code'] = $this->currency_code;
        return $data;
    }

    function paypal_auto_form()
    {
        // dd('here');
        // form with hidden elements which is submitted to paypal
        $this->button('Click here if you\'re not automatically redirected...');

        echo '<html>' . "\n";
        echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><title>Processing Payment.. Please wait.. |' . 'sajd' . '</title>
        </head>' . "\n";
        echo '<body style="text-align:center; font-size:2em;" onLoad="document.forms[\'paypal_auto_form\'].submit();">' . "\n";
        echo '<p style="text-align:center;">Please wait, your order is being processed and you will be redirected to the paypal website.</p>' . "\n";
        echo $this->paypal_form('paypal_auto_form');
        echo '</body></html>';
    }

    public function paypal_form($formName = 'paypal_form')
    {
        $url = $this->paypal_url;

        $form = '<form method="POST" action="' . $url . '" name="' . $formName . '">';
        $form .= '<input type="hidden" name="payer_email" value="testing@infinitietech.com" />';

        foreach ($this->fields as $name => $value) {
            $form .= '<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" />';
        }

        // $form .= '<p><img src="' . URL::asset('assets/old-pre-loader.gif') . '" alt="Please wait.. Loading" title="Please wait.. Loading.." width="140px" /></p>';
        $form .= '<p>' . htmlspecialchars($this->submit_btn) . '</p>';
        $form .= '</form>';
// dd($form);
        return $form;
    }


    function validate_ipn($paypalReturn)
    {
        $ipn_response = $this->curlPost($this->paypal_url, $paypalReturn);

        if (preg_match("/VERIFIED/i", $ipn_response)) {
            // Valid IPN transaction.
            return true;
        } else {
            return false;
        }
    }


    function dump()
    {
        // Used for debugging, this function will output all the field/value pairs
        ksort($this->fields);
        echo '<h2>ppal->dump() Output:</h2>' . "\n";
        echo '<code style="font: 12px Monaco, \'Courier New\', Verdana, Sans-serif;  background: #f9f9f9; border: 1px solid #D0D0D0; color: #002166; display: block; margin: 14px 0; padding: 12px 10px;">' . "\n";
        foreach ($this->fields as $key => $value)
            echo '<strong>' . $key . '</strong>:    ' . urldecode($value) . '<br/>';
        echo "</code>\n";
    }

    function curlPost($paypal_url, $paypal_return_arr)
    {
        $req = 'cmd=_notify-validate';
        foreach ($paypal_return_arr as $key => $value) {
            $value = urlencode(stripslashes($value));
            $req .= "&$key=$value";
        }

        $ipn_site_url = $paypal_url;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $ipn_site_url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}
