<?php

class ValidationUnauthorizedTest extends ModuleTestCase
{
    public function testExitWhenPaymentModuleNotListed()
    {
        $root = dirname(dirname(__DIR__));
        $script = $root . '/tests/bin/unauthorized_validation.php';
        $marker = sys_get_temp_dir() . '/ps_checkpayment_validate_marker';
        if (file_exists($marker)) {
            unlink($marker);
        }
        $descriptors = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );
        $phpBin = getenv('PHP_BIN');
        if (!$phpBin) {
            $phpBin = 'php';
        }
        $cmd = escapeshellarg($phpBin) . ' ' . escapeshellarg($script);
        $proc = proc_open($cmd, $descriptors, $pipes, $root);
        $this->assertInternalType('resource', $proc);
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exitCode = proc_close($proc);
        PhpDiagnosticGuard::failIfPhpDiagnostic($stdout, $stderr, 'unauthorized_validation child');
        $this->assertSame(0, $exitCode);
        $this->assertSame('This payment method is not available.', $stdout);
        $this->assertTrue(file_exists($marker));
        $this->assertSame('not-called', trim(file_get_contents($marker)));
    }
}
