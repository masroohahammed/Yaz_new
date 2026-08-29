<?php

declare(strict_types=1);

error_reporting(E_ALL);

$paths = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../app/Helpers/fm_helper.php',
];

foreach ($paths as $path) {
    if (is_file($path)) {
        require_once $path;
    }
}
