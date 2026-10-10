<?php

class CheckpaymentConstructTest extends ModuleTestCase
{
    public function testMetadata()
    {
        Currency::$checkPaymentCurrenciesRows = array(array('id_currency' => 3));
        Configuration::$store = array(
            'CHEQUE_NAME' => 'Ada',
            'CHEQUE_ADDRESS' => 'Paris',
        );
        $module = new Ps_Checkpayment();
        $this->assertSame('ps_checkpayment', $module->name);
        $this->assertSame('payments_gateways', $module->tab);
        $this->assertSame('2.1.0', $module->version);
        $this->assertSame('PrestaShop', $module->author);
        $this->assertSame(array('payment', 'validation'), $module->controllers);
        $this->assertTrue($module->currencies);
        $this->assertSame('checkbox', $module->currencies_mode);
        $this->assertTrue($module->bootstrap);
        $this->assertSame(array('min' => '1.7.6.0', 'max' => _PS_VERSION_), $module->ps_versions_compliancy);
        $this->assertSame('Payments by check', $module->displayName);
        $this->assertSame(
            'Display contact details blocks to make it easy for customers to pay by check on your store.',
            $module->description
        );
        $this->assertSame('Are you sure you want to delete these details?', $module->confirmUninstall);
    }

    public function testPropertiesComeFromGetMultiple()
    {
        Configuration::$store = array(
            'CHEQUE_NAME' => 'Ada',
            'CHEQUE_ADDRESS' => '10 Main',
        );
        Currency::$checkPaymentCurrenciesRows = array(array('id_currency' => 3));
        $module = new Ps_Checkpayment();
        $this->assertSame('Ada', $module->checkName);
        $this->assertSame('10 Main', $module->address);
    }

    public function testExtraMailVarsUseGetAndNl2br()
    {
        Configuration::$store = array(
            'CHEQUE_NAME' => 'Ada',
            'CHEQUE_ADDRESS' => "10 Main\r\nParis",
        );
        Currency::$checkPaymentCurrenciesRows = array(array('id_currency' => 3));
        $module = new Ps_Checkpayment();
        $this->assertSame('Ada', $module->extra_mail_vars['{check_name}']);
        $this->assertSame("10 Main\r\nParis", $module->extra_mail_vars['{check_address}']);
        $this->assertSame('10 Main<br />Paris', $module->extra_mail_vars['{check_address_html}']);
    }

    public function testWarningWhenPayeeMissing()
    {
        Module::$activeOnConstruct = 1;
        Configuration::$store = array('CHEQUE_ADDRESS' => '10 Main');
        Currency::$checkPaymentCurrenciesRows = array(array('id_currency' => 3));
        $module = new Ps_Checkpayment();
        $this->assertSame(
            'The "Payee" and "Address" fields must be configured before using this module.',
            $module->warning
        );
    }

    public function testWarningWhenAddressMissing()
    {
        Module::$activeOnConstruct = 1;
        Configuration::$store = array('CHEQUE_NAME' => 'Ada', 'CHEQUE_ADDRESS' => '');
        Currency::$checkPaymentCurrenciesRows = array(array('id_currency' => 3));
        $module = new Ps_Checkpayment();
        $this->assertSame(
            'The "Payee" and "Address" fields must be configured before using this module.',
            $module->warning
        );
    }

    public function testNoConfigWarningWhenInactive()
    {
        Module::$activeOnConstruct = 0;
        Configuration::$store = array();
        Currency::$checkPaymentCurrenciesRows = array();
        $module = new Ps_Checkpayment();
        $this->assertFalse(isset($module->warning));
    }

    public function testCurrencyWarningWhenConfigured()
    {
        Module::$activeOnConstruct = 1;
        Configuration::$store = array(
            'CHEQUE_NAME' => 'Ada',
            'CHEQUE_ADDRESS' => 'Paris',
        );
        Currency::$checkPaymentCurrenciesRows = array();
        $module = new Ps_Checkpayment();
        $this->assertSame('No currency has been set for this module.', $module->warning);
    }

    public function testNoCurrencyWarningWhenCurrenciesExist()
    {
        Module::$activeOnConstruct = 1;
        Configuration::$store = array(
            'CHEQUE_NAME' => 'Ada',
            'CHEQUE_ADDRESS' => 'Paris',
        );
        Currency::$checkPaymentCurrenciesRows = array(array('id_currency' => 3));
        $module = new Ps_Checkpayment();
        $this->assertFalse(isset($module->warning));
    }
}
