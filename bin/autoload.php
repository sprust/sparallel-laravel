<?php

declare(strict_types=1);

$autoloadPaths = [
    __DIR__ . '/../../../autoload.php',
    __DIR__ . '/../vendor/autoload.php',
];

$autoloadPath = null;

foreach ($autoloadPaths as $filePath) {
    if (is_file($filePath)) {
        $autoloadPath = $filePath;

        break;
    }
}

if (!$autoloadPath) {
    throw new RuntimeException('Autoload file not found.');
}

require_once $autoloadPath;
