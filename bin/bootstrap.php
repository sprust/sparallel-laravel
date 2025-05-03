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

$bootstrapFilePaths = [
    __DIR__ . '/../../../../bootstrap/app.php',
    __DIR__ . '/../vendor/orchestra/testbench-core/laravel/bootstrap/app.php',
];

$bootstrapFilePath = null;

foreach ($bootstrapFilePaths as $filePath) {
    if (is_file($filePath)) {
        $bootstrapFilePath = $filePath;

        break;
    }
}

if (!$bootstrapFilePath) {
    throw new RuntimeException('Application bootstrap file not found.');
}

return require_once $bootstrapFilePath;
