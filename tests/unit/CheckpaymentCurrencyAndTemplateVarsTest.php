<?php

class CheckpaymentCurrencyAndTemplateVarsTest extends ModuleTestCase
{
    private function baseModule()
    {
        Configuration::$store = array(
            'CHEQUE_NAME' => 'Ada',
            'CHEQUE_ADDRESS' => 'Paris',
        );
        Currency::$checkPaymentCurrenciesRows = array(array('id_currency' => 3));
        $ctx = Context::getContext();
        $ctx->cart->id_currency = 3;
        $ctx->cart->orderTotal = 12.34;
        $ctx->country->display_tax_label = false;

        return new Ps_Checkpayment();
    }

    public function testCheckCurrencyMatchesSecondRow()
    {
        $module = $this->baseModule();
        PaymentModule::$getCurrencyReturn = array(
            array('id_currency' => 1),
            array('id_currency' => 3),
        );
        $cart = new Cart();
        $cart->id_currency = 3;
        $this->assertTrue($module->checkCurrency($cart));
        $this->assertSame(array(3), PaymentModule::$getCurrencyCalls);
    }

    public function testCheckCurrencyRejectsOtherId()
    {
        $module = $this->baseModule();
        PaymentModule::$getCurrencyReturn = array(array('id_currency' => 4));
        $cart = new Cart();
        $cart->id_currency = 3;
        $this->assertFalse($module->checkCurrency($cart));
    }

    public function testCheckCurrencyRejectsFalse()
    {
        $module = $this->baseModule();
        PaymentModule::$getCurrencyReturn = false;
        $cart = new Cart();
        $cart->id_currency = 3;
        $this->assertFalse($module->checkCurrency($cart));
    }

    public function testCheckCurrencyRejectsEmptyArray()
    {
        $module = $this->baseModule();
        PaymentModule::$getCurrencyReturn = array();
        $cart = new Cart();
        $cart->id_currency = 3;
        $this->assertFalse($module->checkCurrency($cart));
    }

    public function testCheckCurrencyRejectsNonArray()
    {
        $module = $this->baseModule();
        PaymentModule::$getCurrencyReturn = new Currency(3);
        $cart = new Cart();
        $cart->id_currency = 3;
        $this->assertFalse($module->checkCurrency($cart));
    }

    public function testTemplateVarsFormatTotalWithIso()
    {
        $module = $this->baseModule();
        $vars = $module->getTemplateVars();
        $this->assertSame('12.34 EUR', $vars['checkTotal']);
        $this->assertSame(array(12.34, 'EUR'), LocaleStub::$lastFormatPriceArgs);
        $this->assertSame(array(true, Cart::BOTH), Cart::$lastGetOrderTotalArgs);
        $this->assertInternalType('string', $vars['checkTaxLabel']);
        $this->assertSame('', $vars['checkTaxLabel']);
        $taxCalls = array_filter(Module::$transCalls, function ($c) {
            return $c['id'] === '(tax incl.)';
        });
        $this->assertCount(0, $taxCalls);
    }

    public function testTemplateVarsTaxLabel()
    {
        $module = $this->baseModule();
        Context::getContext()->country->display_tax_label = true;
        $vars = $module->getTemplateVars();
        $this->assertSame('(tax incl.)', $vars['checkTaxLabel']);
        $last = end(Module::$transCalls);
        $this->assertSame('Modules.Checkpayment.Admin', $last['domain']);
    }

    public function testTemplateVarsPlaceholderNameAndAddress()
    {
        Configuration::$store = array();
        Currency::$checkPaymentCurrenciesRows = array(array('id_currency' => 3));
        $module = new Ps_Checkpayment();
        $vars = $module->getTemplateVars();
        $this->assertSame('___________', $vars['checkOrder']);
        $this->assertSame('___________', $vars['checkAddress']);
    }

    public function testTemplateVarsNewlineAddress()
    {
        Configuration::$store = array(
            'CHEQUE_NAME' => 'Ada',
            'CHEQUE_ADDRESS' => "10 Main\r\nParis",
        );
        Currency::$checkPaymentCurrenciesRows = array(array('id_currency' => 3));
        Context::getContext()->cart->orderTotal = 1.0;
        $module = new Ps_Checkpayment();
        $vars = $module->getTemplateVars();
        $this->assertSame('10 Main<br />Paris', $vars['checkAddress']);
    }

    public function testTemplateVarsCarriageReturnAddress()
    {
        Configuration::$store = array(
            'CHEQUE_NAME' => 'Ada',
            'CHEQUE_ADDRESS' => "10 Main\rParis",
        );
        Currency::$checkPaymentCurrenciesRows = array(array('id_currency' => 3));
        Context::getContext()->cart->orderTotal = 1.0;
        $module = new Ps_Checkpayment();
        $vars = $module->getTemplateVars();
        $this->assertSame('10 Main<br />Paris', $vars['checkAddress']);
    }
}
