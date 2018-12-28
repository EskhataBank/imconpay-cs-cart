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

class ImconPay
{
    private $paymentUrl = 'https://pay.imcon.tj/pay/gateway/';
    private $clientCode;
    private $clientApiKey;
    private $rootApiKey;
    private $itemUrl;
    private $serviceOrderId;

    public function __construct($productUrl)
    {
        $this->itemUrl = $productUrl;
        $this->serviceOrderId = $this->generateServiceOrder();
        $this->clientCode = Registry::get('addons.imconpay.client_code');
        $this->clientApiKey = Registry::get('addons.imconpay.client_api_key');
        $this->rootApiKey = Registry::get('addons.imconpay.root_api_key');
    }

    public function getSignature($orderId, $amount)
    {
        $str = $this->serviceOrderId . $amount . $this->clientApiKey;
        $hash = hash('sha256', $str);
        $signature = $this->getPaymentSignature($this->serviceOrderId, $amount, $hash);
        if (strlen($signature) > 0) {
            $this->saveSignature($orderId, $this->serviceOrderId, $signature);
            return $signature;
        } else {
            $this->saveSignature($orderId, $this->serviceOrderId, $signature);
            return "Error create signature.";
        }
    }

    private function generateServiceOrder()
    {

        $db = db_query('SELECT max(service_order_id) as service_order FROM ?:orders_payments_signature');

        if (($db->num_rows) > 0) {
            while ($row = $db->fetch_assoc()) {
                $lastNumber = $row['service_order'];
                if (((int)$lastNumber) > 0) {
                    $lastNumber++;
                    return $lastNumber;
                } else return 1000;
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

    private function getPaymentSignature($serviceOrderId, $amount, $hash)
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

        $response = json_decode($response, true);
        if ($response['success'] === true) {
            return $response['signature'];
        } else return false;

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
}

?>
