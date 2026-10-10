<?php

class TestSuiteGuard
{
    public static function manifestPath($root)
    {
        return $root . '/tests/test-manifest.php';
    }

    public static function loadManifest($root)
    {
        $path = self::manifestPath($root);
        if (!is_file($path)) {
            throw new RuntimeException('Missing test manifest: ' . $path);
        }

        return require $path;
    }

    public static function resolveFiles($root, $manifest)
    {
        $files = array();
        foreach ($manifest['files'] as $relative) {
            $files[] = $root . '/' . ltrim(str_replace('./', '', $relative), '/');
        }

        return $files;
    }

    public static function countTestMethodsInFiles($files)
    {
        $count = 0;
        foreach ($files as $file) {
            if (!is_file($file)) {
                throw new RuntimeException('Manifest test file missing: ' . $file);
            }
            $source = file_get_contents($file);
            if (preg_match_all('/function\s+(test[A-Za-z0-9_]+)\s*\(/', $source, $matches)) {
                $count += count($matches[1]);
            }
        }

        return $count;
    }

    public static function assertManifest($root, $manifest)
    {
        if (!isset($manifest['files'], $manifest['expectedFileCount'], $manifest['expectedTestCount'])) {
            throw new RuntimeException('Test manifest is incomplete');
        }

        if (count($manifest['files']) !== (int) $manifest['expectedFileCount']) {
            throw new RuntimeException(
                'Manifest file list count ' . count($manifest['files'])
                . ' does not match expectedFileCount ' . $manifest['expectedFileCount']
            );
        }

        $files = self::resolveFiles($root, $manifest);
        if (count($files) !== (int) $manifest['expectedFileCount']) {
            throw new RuntimeException('Resolved manifest file count mismatch');
        }

        $listed = array();
        foreach ($manifest['files'] as $relative) {
            $listed[basename($relative)] = true;
        }

        $unitDir = $root . '/tests/unit';
        if (is_dir($unitDir)) {
            foreach (glob($unitDir . '/*Test.php') as $path) {
                $base = basename($path);
                if (!isset($listed[$base])) {
                    throw new RuntimeException('Unlisted test file in tests/unit: ' . $base);
                }
            }
        }

        $testCount = self::countTestMethodsInFiles($files);
        if ($testCount !== (int) $manifest['expectedTestCount']) {
            throw new RuntimeException(
                'Test method count ' . $testCount
                . ' does not match expectedTestCount ' . $manifest['expectedTestCount']
            );
        }

        return $files;
    }
}

class PhpDiagnosticGuard
{
    public static function containsPhpDiagnostic($stdout, $stderr)
    {
        $combined = $stdout . "\n" . $stderr;
        $patterns = array(
            'Notice:',
            'Warning:',
            'Deprecated:',
            'Strict Standards:',
            'Fatal error',
            'Parse error',
        );
        foreach ($patterns as $pattern) {
            if (stripos($combined, $pattern) !== false) {
                return $pattern;
            }
        }

        return false;
    }

    public static function failIfPhpDiagnostic($stdout, $stderr, $context)
    {
        $match = self::containsPhpDiagnostic($stdout, $stderr);
        if ($match !== false) {
            PHPUnit_Framework_Assert::fail(
                $context . ': PHP diagnostic matched ' . $match
                . ' in child output'
            );
        }
    }
}
