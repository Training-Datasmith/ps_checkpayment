<?php

class TemplateAndManifestTest extends ModuleTestCase
{
    public function testConfigXmlMatchesModule()
    {
        Currency::$checkPaymentCurrenciesRows = array(array('id_currency' => 3));
        $module = new Ps_Checkpayment();
        $xml = simplexml_load_file(dirname(dirname(__DIR__)) . '/config.xml');
        $this->assertSame('ps_checkpayment', (string) $xml->name);
        $this->assertSame($module->version, (string) $xml->version);
    }

    public function testPaymentInfosTemplateUsesAssignedVars()
    {
        $tpl = file_get_contents(dirname(dirname(__DIR__)) . '/views/templates/front/payment_infos.tpl');
        $this->assertContains('{$checkTotal}', $tpl);
        $this->assertContains('{$checkTaxLabel}', $tpl);
        $this->assertContains('{$checkOrder}', $tpl);
        $this->assertContains('{$checkAddress nofilter}', $tpl);
    }

    public function testPaymentReturnTemplateUsesAssignedVars()
    {
        $tpl = file_get_contents(dirname(dirname(__DIR__)) . '/views/templates/hook/payment_return.tpl');
        $this->assertContains('{$total_to_pay}', $tpl);
        $this->assertContains('$shop_name', $tpl);
        $this->assertContains('{$checkName}', $tpl);
        $this->assertContains('{$checkAddress nofilter}', $tpl);
        $this->assertContains('$reference', $tpl);
    }

    public function testInfosTemplateFileExists()
    {
        $path = dirname(dirname(__DIR__)) . '/views/templates/hook/infos.tpl';
        $this->assertNotEmpty($path);
        $this->assertTrue(is_file($path));
    }
}
