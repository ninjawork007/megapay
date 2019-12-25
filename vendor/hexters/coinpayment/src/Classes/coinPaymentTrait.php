<?php
namespace Hexters\CoinPayment\Classes;

trait coinPaymentTrait
{

    public function api_call($cmd, $req = array())
    {
        // echo $cmd;
        // dd(coinPaymentInfo()->public_key);
        // Fill these in from your API Keys page
        // $public_key  = config('coinpayment.public_key');
        // $private_key = config('coinpayment.private_key');

        $public_key = coinPaymentInfo()->public_key;

        $private_key = coinPaymentInfo()->private_key;

        // Set the API command and required fields
        $req['version'] = 1;
        $req['cmd']     = $cmd;
        $req['key']     = $public_key;

        // dd($req);

        if (!empty(auth()->user()->email))// hotfix - updated in paymoney_v2.3
        {
            $req['buyer_email'] = auth()->user()->email;
        }

        $req['format'] = 'json'; //supported values are json and xml

        // Generate the query string
        $post_data = http_build_query($req, '', '&');

        // Calculate the HMAC signature on the POST data
        $hmac = hash_hmac('sha512', $post_data, $private_key);

        // Create cURL handle and initialize (if needed)
        static $ch = null;
        if ($ch === null)
        {
            $ch = curl_init('https://www.coinpayments.net/api.php');
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('HMAC: ' . $hmac));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

        // Execute the call and close cURL handle
        $data = curl_exec($ch);
        // Parse and return data if successful.
        if ($data !== false)
        {
            if (PHP_INT_SIZE < 8 && version_compare(PHP_VERSION, '5.4.0') >= 0)
            {
                // We are on 32-bit PHP, so use the bigint as string option. If you are using any API calls with Satoshis it is highly NOT recommended to use 32-bit PHP
                $dec = json_decode($data, true, 512, JSON_BIGINT_AS_STRING);
            }
            else
            {
                $dec = json_decode($data, true);
            }

            if ($dec !== null && count($dec))
            {
                return $dec; //
            }
            else
            {
                // If you are using PHP 5.5.0 or higher you can use json_last_error_msg() for a better error message
                return array('error' => 'Unable to parse JSON result (' . json_last_error() . ')');
            }
        }
        else
        {
            return array('error' => 'cURL error: ' . curl_error($ch));
        }
    }
}
