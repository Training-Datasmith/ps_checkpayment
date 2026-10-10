<?php

class Upgrade206Test extends ModuleTestCase
{
    public function testUpgradeSwitchesHook()
    {
        $spy = new PaymentModule();
        $this->assertTrue(upgrade_module_2_0_6($spy));
        $this->assertSame(array('paymentReturn'), Module::$unregisterHookCalls);
        $this->assertContains('displayPaymentReturn', Module::$registeredHooks);
    }
}
