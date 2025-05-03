<?php

declare(strict_types=1);

namespace SParallelLaravel\Application;

use Illuminate\Foundation\Application;

class ApplicationFactory
{
    public static function create(): Application
    {
        return require __DIR__ . '/../../../bin/bootstrap.php';
    }
}
