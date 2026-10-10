<?php

class CheckpaymentHooksTest extends ModuleTestCase
{
    private function configuredModule()
    {
        Configuration::$store = array(
            'CHEQUE_NAME' => 'Ada',
            'CHEQUE_ADDRESS' => "10 Main\nParis",
        );
        Currency::$checkPaymentCurrenciesRows = array(array('id_currency' => 3));
        $ctx = Context::getContext();
        $ctx->cart->id_currency = 3;
        $ctx->cart->orderTotal = 12.34;
        $module = new Ps_Checkpayment();
        $module->active = 1;
        $module->checkName = 'Ada';
        $module->address = "10 Main\nParis";

        return $module;
    }

    public function testPaymentOptionsInactive()
    {
        $module = $this->configuredModule();
        $module->active = 0;
        PaymentModule::$getCurrencyReturn = array(array('id_currency' => 3));
        $result = $module->hookPaymentOptions(array('cart' => Context::getContext()->cart));
        $this->assertNull($result);
        $this->assertCount(0, ModuleFrontController::$fetchCalls);
    }

    public function testPaymentOptionsCurrencyRejected()
    {
        $module = $this->configuredModule();
        PaymentModule::$getCurrencyReturn = array(array('id_currency' => 4));
        $result = $module->hookPaymentOptions(array('cart' => Context::getContext()->cart));
        $this->assertNull($result);
        $this->assertCount(0, ModuleFrontController::$fetchCalls);
    }

    public function testPaymentOptionsBuildsOneOption()
    {
        $module = $this->configuredModule();
        PaymentModule::$getCurrencyReturn = array(array('id_currency' => 3));
        $result = $module->hookPaymentOptions(array('cart' => Context::getContext()->cart));
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $option = $result[0];
        $this->assertSame('ps_checkpayment', $option->moduleName);
        $this->assertSame('Pay by Check', $option->callToActionText);
        $this->assertSame('https://shop.test/module/ps_checkpayment/validation', $option->action);
        $this->assertSame(
            'FETCH:module:ps_checkpayment/views/templates/front/payment_infos.tpl',
            $option->additionalInformation
        );
        $this->assertSame('12.34 EUR', SmartyStub::$assigned['checkTotal']);
        $this->assertSame('Ada', SmartyStub::$assigned['checkOrder']);
        $this->assertSame('10 Main<br />Paris', SmartyStub::$assigned['checkAddress']);
    }

    public function testDisplayPaymentReturnInactive()
    {
        $module = $this->configuredModule();
        $module->active = 0;
        $result = $module->hookDisplayPaymentReturn(array('order' => new OrderStub()));
        $this->assertNull($result);
        $this->assertSame(array(), SmartyStub::$assigned);
    }

    public function testDisplayPaymentReturnAssignsRemainder()
    {
        $module = $this->configuredModule();
        $order = new OrderStub();
        $result = $module->hookDisplayPaymentReturn(array('order' => $order));
        $this->assertSame('FETCH:module:ps_checkpayment/views/templates/hook/payment_return.tpl', $result);
        $this->assertSame('60.00 EUR', SmartyStub::$assigned['total_to_pay']);
        $this->assertSame(array(60, 'EUR'), LocaleStub::$lastFormatPriceArgs);
        $this->assertSame('Demo Shop', SmartyStub::$assigned['shop_name']);
        $this->assertSame('Ada', SmartyStub::$assigned['checkName']);
        $this->assertSame('10 Main<br />Paris', SmartyStub::$assigned['checkAddress']);
        $this->assertSame('ok', SmartyStub::$assigned['status']);
        $this->assertSame(50, SmartyStub::$assigned['id_order']);
        $this->assertSame('ABCDEF', SmartyStub::$assigned['reference']);
    }
}
