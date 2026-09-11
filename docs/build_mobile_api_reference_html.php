<?php
/**
 * Generates docs/mobile-api-reference.html (clean light theme).
 * Delegates to Python generator — run: python3 docs/generate_api_html.py
 */

$py = __DIR__ . '/generate_api_html.py';
$cmd = 'python3 ' . escapeshellarg($py) . ' 2>&1';
passthru($cmd, $code);
exit($code ?: 0);
