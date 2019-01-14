<?php
/**
 * Created by PhpStorm.
 * User: K_Hayoev
 * Date: 21.12.2018
 * Time: 14:34
 */

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}
$ExternalLibPath = realpath(dirname(__FILE__)) . DS . 'imconpayLib.php';
require_once($ExternalLibPath);


if (defined('PAYMENT_NOTIFICATION')) {


    //echo 'Success';

    $pp_response = array();
    $pp_response['order_status'] = 'F';
    $pp_response['reason_text'] = __('text_transaction_declined');
    $order_id = !empty($_REQUEST['order_id']) ? (int)$_REQUEST['order_id'] : 0;

    if ($mode == 'response' && !empty($_REQUEST['order_id'])) {

        //echo ' Response Success';

        $imcon = new ImconPay("http://localhost:8012/httpexample/");
        $order_id = $imcon->getOrderId($order_id);
        if (strlen($order_id) == 0) {
            echo "Error: Cannot find orderId.";
        }
        //echo $order_id;
        if ($_REQUEST['success'] == true) {

            $order_info = fn_get_order_info($order_id);
            $option['amount'] = $amount = fn_format_price($order_info['total']);
            $option['order_id'] = $order_id;
            $request['order_id'] = $_REQUEST['order_id'];
            $request['signature'] = $_REQUEST['signature'];

            $response = $imcon->isPaymentValid($option, $request);

            echo "response:" . $response . "</br>";
           // if ($response === true && $order_info['status'] == 'N') {
            if ($response === true) {

                echo $response;

                echo $order_id;

                $pp_response['order_status'] = 'P';
                $pp_response['reason_text'] = __('transaction_approved');
                $pp_response['transaction_id'] = $_REQUEST['order_id'];
                if (fn_check_payment_script('imconpay.php', $order_id)) {
                    fn_finish_payment($order_id, $pp_response);
                    fn_order_placement_routines('route', $order_id);
                }


            }
        }


    } elseif ($mode == 'success' && !empty($_REQUEST['order_id'])) {

        echo ' success Success';

        if ($response == true && $order_info['status'] == 'N') {
                $pp_response['order_status'] = 'P';
                $pp_response['reason_text'] = __('transaction_approved');
                $pp_response['transaction_id'] = $_REQUEST['order_id'];
                fn_finish_payment($order_id, $pp_response);
        }
    }
    exit;

} else {

    $currency_f = CART_SECONDARY_CURRENCY;
    if ($processor_data['processor_params']['currency'] == 'shop_cur') {
        $amount = fn_format_price_by_currency($order_info['total']);
    } else {
        $amount = fn_format_price($order_info['total'], $processor_data['processor_params']['currency']);
        $currency_f = $processor_data['processor_params']['currency'];
    }
    $confirm_url = fn_url("payment_notification.success?payment=imconpay&order_id=$order_id", AREA, 'current');
    $response_url = fn_url("payment_notification.response?payment=imconpay&order_id=$order_id", AREA, 'current');

    echo "confirmURL is $confirm_url </br>";
    echo "responseURL is $response_url </br>";

    $imcon = new ImconPay("http://localhost:8012/httpexample/");

    $signature = $imcon->getSignature($order_id, $amount);
    $serviceOrderId = $imcon->getServiceOrder();
    $clientCode = $imcon->getClientCode();
    $payment_url = $imcon->getPaymentUrl();

    //$signature = ImconPayCls::generateSignature($order_id, $amount);
    fn_create_payment_form($payment_url . $signature . '/' . $serviceOrderId . '/' . $clientCode, array(), 'ImconPay', true, 'get');
    exit;
}