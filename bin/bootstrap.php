<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

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

/** @var Application $app */
$app = require $bootstrapFilePath;

$app->make(Kernel::class)->bootstrap();

return $app;
