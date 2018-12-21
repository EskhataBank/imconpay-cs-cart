<?php
/**
 * Created by PhpStorm.
 * User: K_Hayoev
 * Date: 21.12.2018
 * Time: 14:33
 */

use Tygh\Storage;

class ImconPayCls
{
    const ORDER_APPROVED = 'approved';
    const ORDER_DECLINED = 'declined';
    const ORDER_SEPARATOR = '#';
    const SIGNATURE_SEPARATOR = '';
    const URL = "https://pay.imcon.tj/api/pay/createQuote";

    public static function getSignature($data, $password, $encoded = true)
    {
        $data = array_filter($data, function ($var) {
            return $var !== '' && $var !== null;
        });
        ksort($data);
        $str = $password;
        foreach ($data as $k => $v) {
            $str .= self::SIGNATURE_SEPARATOR . $v;
        }
        if ($encoded) {
            //return sha1($str);
            return hash('sha256', $str);
        } else {
            return $str;
        }
    }

    public static function isPaymentValid($imconpaySettings, $response)
    {
        if ($imconpaySettings['merchant_id'] != $response['merchant_id']) {
            return 'An error has occurred during payment. Merchant data is incorrect.';
        }
        if ($response['order_status'] == self::ORDER_DECLINED) {
            return 'An error has occurred during payment. Order is declined.';
        }

        $responseSignature = $response['signature'];
        if (isset($response['response_signature_string'])) {
            unset($response['response_signature_string']);
        }
        if (isset($response['signature'])) {
            unset($response['signature']);
        }
        if (self::getSignature($response, $imconpaySettings['secret_key']) != $responseSignature) {
            return 'An error has occurred during payment. Signature is not valid.';
        }

        return true;
    }
}

?>
