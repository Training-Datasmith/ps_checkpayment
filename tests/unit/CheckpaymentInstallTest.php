<?php

class CheckpaymentInstallTest extends ModuleTestCase
{
    public function testInstallRegistersBothHooks()
    {
        Module::$parentInstallResult = true;
        Module::$registerHookResult = true;
        $module = new Ps_Checkpayment();
        $this->assertTrue($module->install());
        $this->assertSame(array('paymentOptions', 'displayPaymentReturn'), Module::$registeredHooks);
    }

    public function testInstallStopsWhenParentFails()
    {
        Module::$parentInstallResult = false;
        Module::$registerHookResult = true;
        $module = new Ps_Checkpayment();
        $this->assertFalse($module->install());
        $this->assertCount(0, Module::$registeredHooks);
    }

    public function testInstallStopsWhenFirstHookFails()
    {
        Module::$parentInstallResult = true;
        Module::$registerHookResults = array('paymentOptions' => false);
        $module = new Ps_Checkpayment();
        $this->assertFalse($module->install());
        $this->assertNotContains('displayPaymentReturn', Module::$registeredHooks);
    }

    public function testUninstallDeletesBothKeysThenParent()
    {
        Configuration::$store = array(
            'CHEQUE_NAME' => 'Ada',
            'CHEQUE_ADDRESS' => 'Paris',
        );
        Module::$parentUninstallResult = true;
        $module = new Ps_Checkpayment();
        $this->assertTrue($module->uninstall());
        $this->assertFalse(array_key_exists('CHEQUE_NAME', Configuration::$store));
        $this->assertFalse(array_key_exists('CHEQUE_ADDRESS', Configuration::$store));
    }

    public function testUninstallStopsWhenNameDeleteFails()
    {
        Configuration::$store = array(
            'CHEQUE_NAME' => 'Ada',
            'CHEQUE_ADDRESS' => 'Paris',
        );
        Configuration::$deleteResults = array('CHEQUE_NAME' => false);
        Module::$parentUninstallResult = true;
        $module = new Ps_Checkpayment();
        $this->assertFalse($module->uninstall());
        $this->assertTrue(array_key_exists('CHEQUE_ADDRESS', Configuration::$store));
        $this->assertSame(0, Module::$parentUninstallCalled);
    }

    public function testUninstallStopsWhenAddressDeleteFails()
    {
        Configuration::$store = array(
            'CHEQUE_NAME' => 'Ada',
            'CHEQUE_ADDRESS' => 'Paris',
        );
        Configuration::$deleteResults = array('CHEQUE_ADDRESS' => false);
        Module::$parentUninstallResult = true;
        $module = new Ps_Checkpayment();
        $this->assertFalse($module->uninstall());
        $this->assertSame(0, Module::$parentUninstallCalled);
    }
}
