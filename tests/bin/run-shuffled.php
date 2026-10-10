<?php

error_reporting(-1);

$root = dirname(dirname(__DIR__));

require_once $root . '/tests/test-suite-guard.php';
$manifest = TestSuiteGuard::loadManifest($root);
$files = TestSuiteGuard::assertManifest($root, $manifest);

$phpunit = getenv('PHPUNIT_PHAR');
if (!$phpunit) {
    $phpunit = '/usr/local/bin/phpunit.phar';
}
if (!is_file($phpunit)) {
    fwrite(STDERR, "PHPUnit PHAR not found: {$phpunit}\n");
    exit(1);
}

$seed = getenv('RANDOM_SEED');
if ($seed === false || $seed === '') {
    $seed = 20261008;
}
$seed = (int) $seed;
if (version_compare(PHP_VERSION, '7.1.0', '<')) {
    srand($seed);
    mt_srand($seed);
} else {
    mt_srand($seed);
}

require_once $root . '/tests/bootstrap-core.php';

if (!class_exists('PHPUnit_Framework_TestSuite', false)) {
    $phar = realpath($phpunit);
    if ($phar === false) {
        fwrite(STDERR, "Cannot resolve PHPUnit PHAR path\n");
        exit(1);
    }
    include $phar;
}

$methods = array();
foreach ($files as $file) {
    require_once $file;
    $class = basename($file, '.php');
    $ref = new ReflectionClass($class);
    foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if (strpos($method->getName(), 'test') === 0) {
            $methods[] = array($class, $method->getName());
        }
    }
}

if (count($methods) !== (int) $manifest['expectedTestCount']) {
    fwrite(
        STDERR,
        'Shuffled method count ' . count($methods)
        . ' does not match expectedTestCount ' . $manifest['expectedTestCount'] . "\n"
    );
    exit(1);
}

for ($i = count($methods) - 1; $i > 0; $i--) {
    $j = mt_rand(0, $i);
    $tmp = $methods[$i];
    $methods[$i] = $methods[$j];
    $methods[$j] = $tmp;
}

echo "RANDOM_SEED={$seed}\n";
echo 'METHOD_COUNT=' . count($methods) . "\n";

$suite = new PHPUnit_Framework_TestSuite('Shuffled ps_checkpayment');
foreach ($methods as $pair) {
    $suite->addTest(
        PHPUnit_Framework_TestSuite::createTest(new ReflectionClass($pair[0]), $pair[1])
    );
}

$runner = new PHPUnit_TextUI_TestRunner();
$result = $runner->run($suite, array(), false);

exit($result->wasSuccessful() ? 0 : 1);
