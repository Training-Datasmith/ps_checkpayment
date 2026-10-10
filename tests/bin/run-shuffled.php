<?php

$root = dirname(dirname(__DIR__));

$phpunit = getenv('PHPUNIT_PHAR');
if (!$phpunit) {
    $phpunit = '/usr/local/bin/phpunit.phar';
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

$testsDir = $root . '/tests/unit';
$methods = array();
foreach (glob($testsDir . '/*Test.php') as $file) {
    $class = basename($file, '.php');
    $source = file_get_contents($file);
    if (preg_match_all('/function\s+(test[A-Za-z0-9_]+)\s*\(/', $source, $matches)) {
        foreach ($matches[1] as $methodName) {
            $methods[] = $class . '::' . $methodName;
        }
    }
}

for ($i = count($methods) - 1; $i > 0; $i--) {
    $j = mt_rand(0, $i);
    $tmp = $methods[$i];
    $methods[$i] = $methods[$j];
    $methods[$j] = $tmp;
}

echo "RANDOM_SEED={$seed}\n";
$failures = 0;
$phpBin = defined('PHP_BINARY') ? PHP_BINARY : 'php';
foreach ($methods as $method) {
    $filter = '^' . $method . '$';
    $cmd = escapeshellarg($phpBin) . ' ' . escapeshellarg($phpunit)
        . ' -c ' . escapeshellarg($root . '/phpunit.xml.dist')
        . ' --filter ' . escapeshellarg($filter)
        . ' ' . escapeshellarg($testsDir);
    passthru($cmd, $code);
    if ($code !== 0) {
        $failures++;
        echo "FAILED: {$method}\n";
    }
}

if ($failures > 0) {
    exit(1);
}

exit(0);
