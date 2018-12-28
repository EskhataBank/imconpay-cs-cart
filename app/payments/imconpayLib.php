<?php
/**
 * Created by PhpStorm.
 * User: K_Hayoev
 * Date: 21.12.2018
 * Time: 14:33
 */

use Tygh\Storage;
use Tygh\Registry;
use Tygh\Http;
use Tygh\Session;

class ImconPayCls
{
    const ORDER_APPROVED = 'approved';
    const ORDER_DECLINED = 'declined';
    const ORDER_SEPARATOR = '#';
    const SIGNATURE_SEPARATOR = '';
    const URL = "https://pay.imcon.tj/pay/gateway/";
    const CLIENT_API_KEY = 'e7b1137f72d554cdca99d78d620ca9c1d80294e967b27ce9e5b1b71f82fabdb2';
    const ROOT_API_KEY = '03d67f433966c96667cc71e09e74950e5f3eb3fdb7e763cc819ca2b5f45c1329';
    const ITEM_URL = 'http://localhost:8012/cscart';
    const CLIENT_CODE = '62db901e';

    private $clientCode;
    private $clientApiKey;
    private $rootApiKey;
    private $itemUrl;
    private $paymentUrl ='https://pay.imcon.tj/pay/gateway/';
    private $serviceOrderId;

    public function __construct($productUrl)
    {
        $this->itemUrl = $productUrl;
        $this->serviceOrderId = $this->generateServiceOrder();
    }


    private function generateServiceOrder()
    {

        $a = db_query('SELECT max(service_order_id) as service_order FROM ?:orders_payments_signature');

        fn_print_r($a);

        if (($a->num_rows) > 0) {

            /* fetch associative array */
            while ($row = $a->fetch_assoc()) {
                fn_print_r($row);

                $last = $row['service_order'];
                if (((int)$last) > 0) {
                    $last++;
                    return $last;
                } else return 100;


            }
        }
    }

    private function saveSignature($orderId, $serviceOrderId, $signature)
    {
        $data = array(
            'order_id' => $orderId,
            'service_order_id' => $serviceOrderId,
            'signature' => $signature,
            'created_on' => date("Y-m-d H:i:s")
        );

        db_query('INSERT INTO ?:orders_payments_signature ?e', $data);
    }

    private function validateSignature($serviceOrderId, $amount, $hash)
    {
        $url = 'https://pay.imcon.tj/api/pay/createQuote';
        $data = array(
            'client_code' => $this->clientCode,
            'order_id' => $serviceOrderId,
            'amount' => $amount,
            'item_url' => $this->itemUrl,
            'hash' => $hash,

        );

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);

        echo "</br> Response is: " . $response;
        echo "</br> Item irl is: " . $this->itemUrl;
        echo "</br> Client_code: " . $this->clientCode;

        $arr = json_decode($response, true);


        fn_print_r($arr);
        fn_print_r($serviceOrderId);


        echo 'SERVER signature is ' . $arr["signature"] . "</br>";
        echo 'MY signature is ' . $hash;

        if ($arr['success'] === true) {

            return $arr['signature'];

        } elseif ($arr['msg'] == 'Order Exist') {

            return true;

        } else return false;

    }

    public function getSignature($orderId, $amount)
    {


        $this->clientCode = Registry::get('addons.imconpay.client_code');
        $this->clientApiKey = Registry::get('addons.imconpay.client_api_key');
        $this->rootApiKey = Registry::get('addons.imconpay.root_api_key');

        echo 'OrderId is ' . $orderId . '</br>';
        echo 'ServiceOrderId is ' . $this->serviceOrderId . '</br>';

        echo 'Amount is ' . $amount . '</br>';


        $str = $this->serviceOrderId . $amount . $this->clientApiKey;

        echo 'String is ' . $str . "</br>";

        $hash = hash('sha256', $str);

        echo 'Hash is ' . $hash;

        $serverSignature = $this->validateSignature($this->serviceOrderId, $amount, $hash);
        if (strlen($serverSignature) > 0) {

            $this->saveSignature($orderId, $this->serviceOrderId, $serverSignature);

            return $serverSignature;
        } else {
            $this->saveSignature($orderId, $this->serviceOrderId, $serverSignature);
            return "Error create signature. Different signature.";
        }
    }


    public function getServiceOrder()
    {
        return $this->serviceOrderId;
    }

    public function getPaymentUrl()
    {
        return $this->paymentUrl;
    }

    public function getClientCode()
    {
        return $this->clientCode;
    }

    /* public static function getSignature($data, $password, $encoded = true)
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
     }*/
}

?>
