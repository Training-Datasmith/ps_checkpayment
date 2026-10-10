<?php

class CheckpaymentConfigTest extends ModuleTestCase
{
    public function testGetContentWithoutSubmitDoesNotSave()
    {
        Configuration::$store = array('CHEQUE_NAME' => 'Grace');
        Currency::$checkPaymentCurrenciesRows = array(array('id_currency' => 3));
        $module = new Ps_Checkpayment();
        $html = $module->getContent();
        $this->assertSame('Grace', Configuration::$store['CHEQUE_NAME']);
        $this->assertContains('INFOS:./views/templates/hook/infos.tpl', $html);
        $this->assertContains('FORM:generated', $html);
        $this->assertCount(0, ModuleFrontController::$displayConfirmationCalls);
    }

    public function testGetConfigFieldsValuesUsesStoredConfig()
    {
        Configuration::$store = array(
            'CHEQUE_NAME' => 'Grace',
            'CHEQUE_ADDRESS' => 'Paris',
        );
        Currency::$checkPaymentCurrenciesRows = array(array('id_currency' => 3));
        $module = new Ps_Checkpayment();
        $values = $module->getConfigFieldsValues();
        $this->assertSame('Grace', $values['CHEQUE_NAME']);
        $this->assertSame('Paris', $values['CHEQUE_ADDRESS']);
    }

    public function testGetConfigFieldsValuesPrefersPostIncludingEmptyString()
    {
        Configuration::$store = array('CHEQUE_NAME' => 'Grace');
        Currency::$checkPaymentCurrenciesRows = array(array('id_currency' => 3));
        $_POST['CHEQUE_NAME'] = '';
        $module = new Ps_Checkpayment();
        $values = $module->getConfigFieldsValues();
        $this->assertInternalType('string', $values['CHEQUE_NAME']);
        $this->assertSame('', $values['CHEQUE_NAME']);
    }

    public function testRenderFormShape()
    {
        Configuration::$store = array();
        Currency::$checkPaymentCurrenciesRows = array(array('id_currency' => 3));
        $module = new Ps_Checkpayment();
        $module->renderForm();
        $forms = HelperForm::$lastForms;
        $helperProps = HelperForm::$lastHelper;
        $this->assertNotNull($forms);
        $this->assertFalse($helperProps['show_toolbar']);
        $this->assertSame(0, $helperProps['id']);
        $this->assertSame('btnSubmit', $helperProps['submit_action']);
        $this->assertSame('admintoken-AdminModules', $helperProps['token']);
        $expectedIndex = 'https://shop.test/admin/AdminModules&configure=ps_checkpayment&tab_module=payments_gateways&module_name=ps_checkpayment';
        $this->assertSame($expectedIndex, $helperProps['currentIndex']);
        $this->assertSame($module->getConfigFieldsValues(), $helperProps['tpl_vars']['fields_value']);
        $form = $forms[0]['form'];
        $this->assertSame('Contact details', $form['legend']['title']);
        $this->assertSame('CHEQUE_NAME', $form['input'][0]['name']);
        $this->assertSame('Payee (name)', $form['input'][0]['label']);
        $this->assertSame('CHEQUE_ADDRESS', $form['input'][1]['name']);
        $this->assertSame('Address', $form['input'][1]['label']);
        $this->assertSame('Address where the check should be sent to.', $form['input'][1]['desc']);
        $this->assertSame('Save', $form['submit']['title']);
    }

    public function testSubmitMissingPayeeShowsOnlyPayeeError()
    {
        Configuration::$store = array('CHEQUE_ADDRESS' => 'Paris');
        Currency::$checkPaymentCurrenciesRows = array(array('id_currency' => 3));
        $_POST['btnSubmit'] = '1';
        $_POST['CHEQUE_NAME'] = '';
        $_POST['CHEQUE_ADDRESS'] = 'Paris';
        $module = new Ps_Checkpayment();
        $html = $module->getContent();
        $this->assertContains('ERROR:The "Payee" field is required.', $html);
        $this->assertNotContains('The "Address" field is required.', $html);
        $this->assertFalse(array_key_exists('CHEQUE_NAME', Configuration::$store));
        $this->assertContains('FORM:generated', $html);
    }

    public function testSubmitMissingAddressShowsAddressError()
    {
        Configuration::$store = array();
        Currency::$checkPaymentCurrenciesRows = array(array('id_currency' => 3));
        $_POST['btnSubmit'] = '1';
        $_POST['CHEQUE_NAME'] = 'Ada';
        $_POST['CHEQUE_ADDRESS'] = '';
        $module = new Ps_Checkpayment();
        $html = $module->getContent();
        $this->assertContains('ERROR:The "Address" field is required.', $html);
        $this->assertFalse(array_key_exists('CHEQUE_ADDRESS', Configuration::$store));
    }

    public function testSubmitSavesRawValuesAndConfirms()
    {
        Configuration::$store = array();
        Currency::$checkPaymentCurrenciesRows = array(array('id_currency' => 3));
        $_POST['btnSubmit'] = '1';
        $_POST['CHEQUE_NAME'] = 'Ada';
        $_POST['CHEQUE_ADDRESS'] = "10 Main\nParis";
        $module = new Ps_Checkpayment();
        $html = $module->getContent();
        $this->assertSame('Ada', Configuration::$store['CHEQUE_NAME']);
        $this->assertSame("10 Main\nParis", Configuration::$store['CHEQUE_ADDRESS']);
        $this->assertContains('OK:Settings updated', $html);
        $this->assertContains('INFOS:./views/templates/hook/infos.tpl', $html);
        $this->assertContains('FORM:generated', $html);
    }
}
