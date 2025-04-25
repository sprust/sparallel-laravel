<?php

namespace SParallelLaravel\Tests;

use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase;
use SParallelLaravel\SParallelServiceProvider;

abstract class BaseTestCase extends TestCase
{
    use WithWorkbench;

    protected function getPackageProviders($app): array
    {
        return [
            SParallelServiceProvider::class,
            ...parent::getPackageProviders($app),
        ];
    }
}
