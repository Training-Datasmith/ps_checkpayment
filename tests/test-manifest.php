<?php

/**
 * Canonical unit test file list. phpunit.xml.dist and run-shuffled.php must stay in sync.
 */
return array(
    'files' => array(
        './tests/unit/CheckpaymentConfigTest.php',
        './tests/unit/CheckpaymentConstructTest.php',
        './tests/unit/CheckpaymentCurrencyAndTemplateVarsTest.php',
        './tests/unit/CheckpaymentHooksTest.php',
        './tests/unit/CheckpaymentInstallTest.php',
        './tests/unit/PaymentControllerTest.php',
        './tests/unit/TemplateAndManifestTest.php',
        './tests/unit/Upgrade206Test.php',
        './tests/unit/ValidationControllerTest.php',
        './tests/unit/ValidationUnauthorizedTest.php',
    ),
    'expectedFileCount' => 10,
    'expectedTestCount' => 53,
);
