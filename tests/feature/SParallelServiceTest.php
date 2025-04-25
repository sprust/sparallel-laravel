<?php

namespace SParallelLaravel\Tests;

class SParallelServiceTest extends BaseTestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('sparallel.async', true);
    }

    public function test(): void
    {
        $this->assertTrue(true);
    }
}
