<?php

namespace SParallelLaravel\Tests;

use Illuminate\Support\Facades\Event;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase;
use SParallelLaravel\SParallelServiceProvider;

abstract class BaseTestCase extends TestCase
{
    use WithWorkbench;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();
    }

    protected function getPackageProviders($app): array
    {
        return [
            SParallelServiceProvider::class,
            ...parent::getPackageProviders($app),
        ];
    }
}
