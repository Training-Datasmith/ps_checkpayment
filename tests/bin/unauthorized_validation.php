<?php

$root = dirname(dirname(__DIR__));
require_once $root . '/tests/bootstrap-core.php';

TestDoubles::$markerFile = sys_get_temp_dir() . '/ps_checkpayment_validate_marker';
file_put_contents(TestDoubles::$markerFile, 'not-called');

Configuration::$store = array(
    'CHEQUE_NAME' => 'Ada',
    'CHEQUE_ADDRESS' => 'Paris',
    'PS_OS_CHEQUE' => 12,
);
Currency::$checkPaymentCurrenciesRows = array(array('id_currency' => 3));
Module::$paymentModules = array(array('name' => 'ps_wirepayment'));
Customer::$fixtures = array(5 => array('secure_key' => 'sec-key'));
$ctx = Context::getContext();
$ctx->cart->id = 99;
$ctx->cart->id_customer = 5;
$ctx->cart->id_address_delivery = 8;
$ctx->cart->id_address_invoice = 8;
$ctx->cart->orderTotal = 19.5;
$ctx->currency = new Currency(3);

class Ps_CheckpaymentMarker extends Ps_Checkpayment
{
    public function validateOrder($cartId, $state, $amount, $method, $message, $extraVars, $currencyId, $secure, $secureKey)
    {
        if (TestDoubles::$markerFile) {
            file_put_contents(TestDoubles::$markerFile, 'called');
        }
        parent::validateOrder($cartId, $state, $amount, $method, $message, $extraVars, $currencyId, $secure, $secureKey);
    }
}

$module = new Ps_CheckpaymentMarker();
$module->active = 1;
$module->id = 7;

$controller = new Ps_CheckpaymentValidationModuleFrontController();
$controller->module = $module;
$controller->postProcess();
