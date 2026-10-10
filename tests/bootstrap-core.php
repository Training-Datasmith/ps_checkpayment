<?php

error_reporting(-1);

if (!defined('_PS_VERSION_')) {
    define('_PS_VERSION_', '1.7.6.0');
}
if (!defined('__PS_BASE_URI__')) {
    define('__PS_BASE_URI__', '/');
}

$root = dirname(__DIR__);

require_once $root . '/tests/test-suite-guard.php';
$manifest = TestSuiteGuard::loadManifest($root);
TestSuiteGuard::assertManifest($root, $manifest);

require_once $root . '/tests/stubs/PaymentOption.php';
require_once $root . '/tests/stubs/PrestaShopDoubles.php';

require_once $root . '/ps_checkpayment.php';
require_once $root . '/controllers/front/payment.php';
require_once $root . '/controllers/front/validation.php';
require_once $root . '/upgrade/upgrade-2.0.6.php';

spl_autoload_register(function ($class) {
    if ($class === 'ModuleTestCase') {
        require_once dirname(__FILE__) . '/ModuleTestCase.php';
    }
});
