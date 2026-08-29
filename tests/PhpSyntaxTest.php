<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PhpSyntaxTest extends TestCase
{
    public function testApplicationPhpFilesLint(): void
    {
        $root = dirname(__DIR__);
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root . '/app')
        );
        $failures = [];
        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $path = $file->getPathname();
            $cmd  = 'php -l ' . escapeshellarg($path) . ' 2>&1';
            $out  = [];
            $code = 0;
            exec($cmd, $out, $code);
            if ($code !== 0) {
                $failures[] = implode("\n", $out);
            }
        }
        $this->assertSame([], $failures, implode("\n\n", $failures));
    }
}
