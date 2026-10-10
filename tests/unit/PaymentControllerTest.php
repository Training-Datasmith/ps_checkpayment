<?php

class PaymentControllerTest extends ModuleTestCase
{
    public function testSslFlag()
    {
        $controller = new Ps_CheckpaymentPaymentModuleFrontController();
        $this->assertTrue($controller->ssl);
    }

    public function testRedirectsWhenModuleTypeIsWrong()
    {
        $controller = new Ps_CheckpaymentPaymentModuleFrontController();
        $controller->module = new stdClass();
        try {
            $controller->initContent();
            $this->fail('Expected RedirectException');
        } catch (RedirectException $e) {
            $this->assertSame('index.php?controller=order', $e->url);
        }
        $this->assertCount(0, ModuleFrontController::$setTemplateCalls);
    }

    public function testRedirectsWhenCurrencyMismatches()
    {
        Configuration::$store = array(
            'CHEQUE_NAME' => 'Ada',
            'CHEQUE_ADDRESS' => 'Paris',
        );
        Currency::$checkPaymentCurrenciesRows = array(array('id_currency' => 3));
        $module = new Ps_Checkpayment();
        PaymentModule::$getCurrencyReturn = array(array('id_currency' => 4));
        $controller = new Ps_CheckpaymentPaymentModuleFrontController();
        $controller->module = $module;
        try {
            $controller->initContent();
            $this->fail('Expected RedirectException');
        } catch (RedirectException $e) {
            $this->assertSame('index.php?controller=order', $e->url);
        }
    }

    public function testAssignsExecutionData()
    {
        Configuration::$store = array(
            'CHEQUE_NAME' => 'Ada',
            'CHEQUE_ADDRESS' => "10 Main\nParis",
        );
        Currency::$checkPaymentCurrenciesRows = array(array('id_currency' => 3));
        $module = new Ps_Checkpayment();
        $module->checkName = 'Ada';
        $module->address = "10 Main\nParis";
        PaymentModule::$getCurrencyReturn = array(array('id_currency' => 3));
        $ctx = Context::getContext();
        $ctx->cart->nbProductsCount = 2;
        $ctx->cart->id_currency = 3;
        $ctx->cart->orderTotal = 12.34;
        $ctx->language->iso_code = 'fr';
        $controller = new Ps_CheckpaymentPaymentModuleFrontController();
        $controller->module = $module;
        $controller->initContent();
        $assigned = SmartyStub::$assigned;
        $this->assertSame(2, $assigned['nbProducts']);
        $this->assertSame(3, $assigned['cust_currency']);
        $this->assertSame(PaymentModule::$getCurrencyReturn, $assigned['currencies']);
        $this->assertSame(12.34, $assigned['total']);
        $this->assertSame('fr', $assigned['isoCode']);
        $this->assertSame('Ada', $assigned['checkName']);
        $this->assertSame('10 Main<br />Paris', $assigned['checkAddress']);
        $this->assertSame('/modules/ps_checkpayment/', $assigned['this_path']);
        $this->assertSame('/modules/ps_checkpayment/', $assigned['this_path_check']);
        $this->assertSame('https://shop.test/modules/ps_checkpayment/', $assigned['this_path_ssl']);
        $this->assertCount(1, ModuleFrontController::$setTemplateCalls);
        $this->assertNotEmpty(ModuleFrontController::$setTemplateCalls[0]);
    }
}
