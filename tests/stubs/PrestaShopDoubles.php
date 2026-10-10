<?php

class RedirectException extends Exception
{
    public $url;

    public function __construct($url)
    {
        $this->url = $url;
        parent::__construct('Redirect to ' . $url);
    }
}

class Configuration
{
    public static $store = array();
    public static $deleteResults = array();

    public static function getMultiple($keys)
    {
        $out = array();
        foreach ($keys as $key) {
            if (array_key_exists($key, self::$store)) {
                $out[$key] = self::$store[$key];
            }
        }

        return $out;
    }

    public static function get($key)
    {
        if (array_key_exists($key, self::$store)) {
            return self::$store[$key];
        }

        return false;
    }

    public static function updateValue($key, $value)
    {
        self::$store[$key] = $value;

        return true;
    }

    public static function deleteByName($key)
    {
        if (isset(self::$deleteResults[$key])) {
            $ok = self::$deleteResults[$key];
            if ($ok) {
                unset(self::$store[$key]);
            }

            return $ok;
        }
        unset(self::$store[$key]);

        return true;
    }
}

class Tools
{
    public static function getValue($key, $default = false)
    {
        if (array_key_exists($key, $_POST)) {
            return $_POST[$key];
        }
        if (array_key_exists($key, $_GET)) {
            return $_GET[$key];
        }

        return $default;
    }

    public static function isSubmit($key)
    {
        if (isset($_POST[$key]) || isset($_GET[$key])) {
            return true;
        }
        if ($key === 'btnSubmit') {
            if (isset($_POST['btnSubmit_x']) || isset($_POST['btnSubmit_y'])) {
                return true;
            }
        }

        return false;
    }

    public static function nl2br($str)
    {
        if ($str === false || $str === null) {
            $str = '';
        }

        return str_replace(array("\r\n", "\r", "\n"), '<br />', (string) $str);
    }

    public static function redirect($url)
    {
        throw new RedirectException($url);
    }

    public static function getShopDomainSsl($http, $both)
    {
        return 'https://shop.test';
    }

    public static function getAdminTokenLite($controller)
    {
        return 'admintoken-' . $controller;
    }
}

class Cart
{
    const BOTH = 3;

    public $id = 0;
    public $id_currency = 3;
    public $id_customer = 0;
    public $id_address_delivery = 0;
    public $id_address_invoice = 0;
    public $nbProductsCount = 0;
    public $orderTotal = 0.0;
    public static $lastGetOrderTotalArgs = null;

    public function nbProducts()
    {
        return $this->nbProductsCount;
    }

    public function getOrderTotal($tax, $type)
    {
        self::$lastGetOrderTotalArgs = array($tax, $type);

        return $this->orderTotal;
    }
}

class Currency
{
    public $id;
    public $iso_code;
    public static $checkPaymentCurrenciesRows = array();
    public static $isoById = array(3 => 'EUR');

    public function __construct($id)
    {
        $this->id = (int) $id;
        if (isset(self::$isoById[$this->id])) {
            $this->iso_code = self::$isoById[$this->id];
        } else {
            $this->iso_code = 'XXX';
        }
    }

    public static function checkPaymentCurrencies($idModule)
    {
        return self::$checkPaymentCurrenciesRows;
    }
}

class LocaleStub
{
    public static $lastFormatPriceArgs = null;

    public function formatPrice($amount, $iso)
    {
        self::$lastFormatPriceArgs = array($amount, $iso);

        return sprintf('%.2f %s', $amount, $iso);
    }
}

class Link
{
    public function getModuleLink($module, $controller, $params, $ssl)
    {
        $url = 'https://shop.test/module/' . $module . '/' . $controller;
        if (!$ssl) {
            $url .= '?ssl=0';
        }

        return $url;
    }

    public function getAdminLink($controller, $withToken)
    {
        return 'https://shop.test/admin/' . $controller;
    }
}

class ShopStub
{
    public $name = 'Demo Shop';
}

class LanguageStub
{
    public $iso_code = 'en';
}

class CountryStub
{
    public $display_tax_label = false;
}

class SmartyStub
{
    public static $assigned = array();

    public function assign($vars)
    {
        if (is_array($vars)) {
            foreach ($vars as $k => $v) {
                self::$assigned[$k] = $v;
            }
        }
    }
}

class Context
{
    private static $instance;
    public $cart;
    public $link;
    public $currency;
    public $language;
    public $country;
    public $shop;
    public $smarty;
    private $locale;

    public function __construct()
    {
        $this->cart = new Cart();
        $this->smarty = new SmartyStub();
        $this->link = new Link();
        $this->currency = new Currency(3);
        $this->language = new LanguageStub();
        $this->country = new CountryStub();
        $this->shop = new ShopStub();
        $this->locale = new LocaleStub();
    }

    public static function getContext()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function resetInstance()
    {
        self::$instance = new self();
    }

    public function getCurrentLocale()
    {
        return $this->locale;
    }
}

class Validate
{
    public static function isLoadedObject($object)
    {
        return is_object($object) && !empty($object->id);
    }
}

class Customer
{
    public $id;
    public $secure_key;
    public static $fixtures = array();

    public function __construct($id)
    {
        $this->id = (int) $id;
        if (isset(self::$fixtures[$this->id])) {
            $this->secure_key = self::$fixtures[$this->id]['secure_key'];
        } else {
            $this->id = 0;
        }
    }
}

class HelperForm
{
    public $show_toolbar;
    public $id;
    public $identifier;
    public $submit_action;
    public $currentIndex;
    public $token;
    public $tpl_vars;
    public static $lastForms = null;
    public static $lastHelper = null;

    public function generateForm($forms)
    {
        self::$lastForms = $forms;
        self::$lastHelper = array(
            'show_toolbar' => $this->show_toolbar,
            'id' => $this->id,
            'submit_action' => $this->submit_action,
            'token' => $this->token,
            'currentIndex' => $this->currentIndex,
            'tpl_vars' => $this->tpl_vars,
        );

        return 'FORM:generated';
    }
}

class Module
{
    public $name;
    public $active = 1;
    public $id = 7;
    public $context;
    public $identifier = 'ps_checkpayment';
    public $currentOrder;
    public $displayName = 'Payments by check';
    public static $activeOnConstruct = 1;
    public static $idOnConstruct = 7;
    public static $paymentModules = array();
    public static $transCalls = array();
    public static $parentInstallResult = true;
    public static $registerHookResult = true;
    public static $registerHookResults = array();
    public static $registeredHooks = array();
    public static $unregisterHookCalls = array();
    public static $parentUninstallResult = true;
    public static $parentUninstallCalled = 0;
    public static $validateOrderCalls = array();

    public function __construct()
    {
        $this->context = Context::getContext();
        $this->active = self::$activeOnConstruct;
        $this->id = self::$idOnConstruct;
    }

    public function trans($id, $params, $domain)
    {
        self::$transCalls[] = array('id' => $id, 'params' => $params, 'domain' => $domain);

        return $id;
    }

    public function install()
    {
        return self::$parentInstallResult;
    }

    public function uninstall()
    {
        self::$parentUninstallCalled++;

        return self::$parentUninstallResult;
    }

    public function registerHook($name)
    {
        if (isset(self::$registerHookResults[$name])) {
            $ok = self::$registerHookResults[$name];
        } else {
            $ok = self::$registerHookResult;
        }
        if ($ok) {
            self::$registeredHooks[] = $name;
        }

        return $ok;
    }

    public function unregisterHook($name)
    {
        self::$unregisterHookCalls[] = $name;

        return true;
    }

    public static function getPaymentModules()
    {
        return self::$paymentModules;
    }

    public function display($file, $template)
    {
        ModuleFrontController::$displayCalls[] = array('file' => $file, 'template' => $template);

        return 'INFOS:' . $template;
    }

    public function displayError($msg)
    {
        ModuleFrontController::$displayErrorCalls[] = $msg;

        return 'ERROR:' . $msg;
    }

    public function displayConfirmation($msg)
    {
        ModuleFrontController::$displayConfirmationCalls[] = $msg;

        return 'OK:' . $msg;
    }

    public function fetch($template)
    {
        ModuleFrontController::$fetchCalls[] = $template;

        return 'FETCH:' . $template;
    }

    public function getPathUri()
    {
        return '/modules/ps_checkpayment/';
    }

    public function validateOrder($cartId, $state, $amount, $method, $message, $extraVars, $currencyId, $secure, $secureKey)
    {
        self::$validateOrderCalls[] = array(
            'cartId' => $cartId,
            'state' => $state,
            'amount' => $amount,
            'method' => $method,
            'message' => $message,
            'extraVars' => $extraVars,
            'currencyId' => $currencyId,
            'secure' => $secure,
            'secureKey' => $secureKey,
        );
        $this->currentOrder = 4242;
    }
}

class PaymentModule extends Module
{
    public $smarty;
    public static $getCurrencyReturn = array();
    public static $getCurrencyCalls = array();

    public function __construct()
    {
        parent::__construct();
        $this->smarty = new SmartyStub();
    }

    public function getCurrency($idCurrency)
    {
        self::$getCurrencyCalls[] = $idCurrency;

        return self::$getCurrencyReturn;
    }
}

class ModuleFrontController
{
    public $module;
    public $context;
    public static $setTemplateCalls = array();
    public static $displayCalls = array();
    public static $displayErrorCalls = array();
    public static $displayConfirmationCalls = array();
    public static $fetchCalls = array();

    public function __construct()
    {
        $this->context = Context::getContext();
    }

    public function initContent()
    {
    }

    public function setTemplate($template)
    {
        self::$setTemplateCalls[] = $template;
    }

    public function trans($id, $params, $domain)
    {
        Module::$transCalls[] = array('id' => $id, 'params' => $params, 'domain' => $domain);

        return $id;
    }
}

class OrderStub
{
    public $id = 50;
    public $reference = 'ABCDEF';
    public $id_currency = 3;
    public $ordersTotalPaid = 100;
    public $totalPaid = 40;

    public function getOrdersTotalPaid()
    {
        return $this->ordersTotalPaid;
    }

    public function getTotalPaid()
    {
        return $this->totalPaid;
    }
}

class TestDoubles
{
    public static $markerFile;

    public static function reset()
    {
        $_GET = array();
        $_POST = array();
        $_REQUEST = array();
        Configuration::$store = array();
        Configuration::$deleteResults = array();
        Currency::$checkPaymentCurrenciesRows = array();
        Currency::$isoById = array(3 => 'EUR');
        Module::$activeOnConstruct = 1;
        Module::$idOnConstruct = 7;
        Module::$paymentModules = array();
        Module::$transCalls = array();
        Module::$parentInstallResult = true;
        Module::$registerHookResult = true;
        Module::$registerHookResults = array();
        Module::$registeredHooks = array();
        Module::$unregisterHookCalls = array();
        Module::$parentUninstallResult = true;
        Module::$parentUninstallCalled = 0;
        Module::$validateOrderCalls = array();
        PaymentModule::$getCurrencyReturn = array();
        PaymentModule::$getCurrencyCalls = array();
        Customer::$fixtures = array();
        Context::resetInstance();
        SmartyStub::$assigned = array();
        LocaleStub::$lastFormatPriceArgs = null;
        Cart::$lastGetOrderTotalArgs = null;
        HelperForm::$lastForms = null;
        HelperForm::$lastHelper = null;
        ModuleFrontController::$setTemplateCalls = array();
        ModuleFrontController::$displayCalls = array();
        ModuleFrontController::$displayErrorCalls = array();
        ModuleFrontController::$displayConfirmationCalls = array();
        ModuleFrontController::$fetchCalls = array();
        if (self::$markerFile && file_exists(self::$markerFile)) {
            unlink(self::$markerFile);
        }
    }
}
