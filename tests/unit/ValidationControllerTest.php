<?php

class ValidationControllerTest extends ModuleTestCase
{
    private function validationSetup()
    {
        Configuration::$store = array(
            'CHEQUE_NAME' => 'Ada',
            'CHEQUE_ADDRESS' => "10 Main\nParis",
            'PS_OS_CHEQUE' => 12,
        );
        Currency::$checkPaymentCurrenciesRows = array(array('id_currency' => 3));
        Module::$paymentModules = array(array('name' => 'ps_checkpayment'));
        Customer::$fixtures = array(5 => array('secure_key' => 'sec-key'));
        $ctx = Context::getContext();
        $ctx->cart->id = 99;
        $ctx->cart->id_customer = 5;
        $ctx->cart->id_address_delivery = 8;
        $ctx->cart->id_address_invoice = 8;
        $ctx->cart->orderTotal = 19.5;
        $ctx->currency = new Currency(3);
        $module = new Ps_Checkpayment();
        $module->active = 1;
        $module->id = 7;
        $module->displayName = 'Payments by check';

        return $module;
    }

    public function testRedirectsWhenModuleTypeIsWrong()
    {
        $controller = new Ps_CheckpaymentValidationModuleFrontController();
        $controller->module = new stdClass();
        try {
            $controller->postProcess();
            $this->fail('Expected RedirectException');
        } catch (RedirectException $e) {
            $this->assertSame('index.php?controller=order&step=1', $e->url);
        }
        $this->assertCount(0, Module::$validateOrderCalls);
    }

    public function testRedirectsWhenCustomerMissing()
    {
        $module = $this->validationSetup();
        Context::getContext()->cart->id_customer = 0;
        $controller = new Ps_CheckpaymentValidationModuleFrontController();
        $controller->module = $module;
        try {
            $controller->postProcess();
            $this->fail('Expected RedirectException');
        } catch (RedirectException $e) {
            $this->assertSame('index.php?controller=order&step=1', $e->url);
        }
        $this->assertCount(0, Module::$validateOrderCalls);
    }

    public function testRedirectsWhenDeliveryAddressMissing()
    {
        $module = $this->validationSetup();
        Context::getContext()->cart->id_address_delivery = 0;
        $controller = new Ps_CheckpaymentValidationModuleFrontController();
        $controller->module = $module;
        try {
            $controller->postProcess();
            $this->fail('Expected RedirectException');
        } catch (RedirectException $e) {
            $this->assertSame('index.php?controller=order&step=1', $e->url);
        }
    }

    public function testRedirectsWhenInvoiceAddressMissing()
    {
        $module = $this->validationSetup();
        Context::getContext()->cart->id_address_invoice = 0;
        $controller = new Ps_CheckpaymentValidationModuleFrontController();
        $controller->module = $module;
        try {
            $controller->postProcess();
            $this->fail('Expected RedirectException');
        } catch (RedirectException $e) {
            $this->assertSame('index.php?controller=order&step=1', $e->url);
        }
    }

    public function testRedirectsWhenModuleInactive()
    {
        $module = $this->validationSetup();
        $module->active = 0;
        $controller = new Ps_CheckpaymentValidationModuleFrontController();
        $controller->module = $module;
        try {
            $controller->postProcess();
            $this->fail('Expected RedirectException');
        } catch (RedirectException $e) {
            $this->assertSame('index.php?controller=order&step=1', $e->url);
        }
        $this->assertCount(0, Module::$validateOrderCalls);
    }

    public function testRedirectsWhenCustomerNotLoaded()
    {
        $module = $this->validationSetup();
        Customer::$fixtures = array();
        $controller = new Ps_CheckpaymentValidationModuleFrontController();
        $controller->module = $module;
        try {
            $controller->postProcess();
            $this->fail('Expected RedirectException');
        } catch (RedirectException $e) {
            $this->assertSame('index.php?controller=order&step=1', $e->url);
        }
        $this->assertCount(0, Module::$validateOrderCalls);
    }

    public function testValidateOrderAndConfirmationRedirect()
    {
        $module = $this->validationSetup();
        $controller = new Ps_CheckpaymentValidationModuleFrontController();
        $controller->module = $module;
        try {
            $controller->postProcess();
            $this->fail('Expected RedirectException');
        } catch (RedirectException $e) {
            $expected = 'index.php?controller=order-confirmation&id_cart=99&id_module=7&id_order=4242&key=sec-key';
            $this->assertSame($expected, $e->url);
        }
        $this->assertCount(1, Module::$validateOrderCalls);
        $call = Module::$validateOrderCalls[0];
        $this->assertSame(99, $call['cartId']);
        $this->assertSame(12, $call['state']);
        $this->assertInternalType('float', $call['amount']);
        $this->assertSame(19.5, $call['amount']);
        $this->assertSame('Payments by check', $call['method']);
        $this->assertNull($call['message']);
        $this->assertSame('Ada', $call['extraVars']['{check_name}']);
        $this->assertSame("10 Main\nParis", $call['extraVars']['{check_address}']);
        $this->assertSame('10 Main<br />Paris', $call['extraVars']['{check_address_html}']);
        $this->assertSame(3, $call['currencyId']);
        $this->assertFalse($call['secure']);
        $this->assertSame('sec-key', $call['secureKey']);
    }
}
