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

    $pp_response = array();
    $pp_response['order_status'] = 'F';
    $pp_response['reason_text'] = __('text_transaction_declined');
    $order_id = !empty($_REQUEST['order_id']) ? (int)$_REQUEST['order_id'] : 0;

    if ($mode == 'response' && !empty($_REQUEST['order_id'])) {

        $order_info = fn_get_order_info($order_id);

        if (empty($processor_data)) {
            $processor_data = fn_get_processor_data($order_info['payment_id']);
        }
        $option = array('merchant_id' => $processor_data['processor_params']['imconpay_merchantid'],
            'secret_key' => $processor_data['processor_params']['imconpay_merchnatSecretKey']);
        $response = ImconPayCls::isPaymentValid($option, $_POST);

        if ($response === true && $order_info['status'] == 'N') {
            if ($_REQUEST['order_status'] == ImconPayCls::ORDER_APPROVED) {
                $pp_response['order_status'] = 'P';
                $pp_response['reason_text'] = __('transaction_approved');
                $pp_response['transaction_id'] = $_REQUEST['payment_id'];
                if (fn_check_payment_script('imconpay.php', $order_id)) {
                    fn_finish_payment($order_id, $pp_response);
                    fn_order_placement_routines('route', $order_id);
                }


            }
        }
    } elseif ($mode == 'sucsses' && !empty($_REQUEST['order_id'])) {
        if ($response == true && $order_info['status'] == 'N') {
            if ($_REQUEST['order_status'] == ImconPayCls::ORDER_APPROVED) {
                $pp_response['order_status'] = 'P';
                $pp_response['reason_text'] = __('transaction_approved');
                $pp_response['transaction_id'] = $_REQUEST['payment_id'];
                fn_finish_payment($order_id, $pp_response);
            }
        }
    }
    exit;

} else {

    $payment_url = ImconPayCls::URL;
    $currency_f = CART_SECONDARY_CURRENCY;
    if ($processor_data['processor_params']['currency'] == 'shop_cur') {
        $amount = fn_format_price_by_currency($order_info['total']);
    } else {
        $amount = fn_format_price($order_info['total'], $processor_data['processor_params']['currency']);
        $currency_f = $processor_data['processor_params']['currency'];
    }
    $confirm_url = fn_url("payment_notification.sucsses?payment=imconpay&order_id=$order_id", AREA, 'current');
    $response_url = fn_url("payment_notification.response?payment=imconpay&order_id=$order_id", AREA, 'current');

    $post_data = array(
        'merchant_id' => $processor_data['processor_params']['imconpay_merchantid'],
        'lang' => $processor_data['processor_params']['imconpay_lang'],
        'order_id' => time() . $order_id,
        'order_desc' => '#' . $order_id,
        'amount' => round($amount * 100),
        'currency' => $currency_f,
        'server_callback_url' => $confirm_url,
        'response_url' => $response_url
    );
    $post_data['signature'] = ImconPayCls::getSignature($post_data, $processor_data['processor_params']['imconpay_merchnatSecretKey']);


    fn_create_payment_form($payment_url, $post_data, 'ImconPay', false);
    exit;
}