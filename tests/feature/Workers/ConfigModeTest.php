<?php

namespace SParallelLaravel\Tests\Workers;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use SParallel\Contracts\DriverFactoryInterface;
use SParallel\Contracts\DriverInterface;
use SParallel\Drivers\Server\ServerDriver;
use SParallel\Drivers\Sync\SyncDriver;
use SParallelLaravel\Tests\BaseTestCase;

class ConfigModeTest extends BaseTestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUnknownMode(): void
    {
        $this->setMode(uniqid());

        $this->assertDriver(SyncDriver::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testSyncMode(): void
    {
        $this->setMode('sync');

        $this->assertDriver(SyncDriver::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testServerMode(): void
    {
        $this->setMode('server');

        $this->assertDriver(ServerDriver::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testServerForce(): void
    {
        $this->app->get(DriverFactoryInterface::class)->forceDriver(
            $this->app->make(ServerDriver::class)
        );

        $this->assertDriver(ServerDriver::class);
    }

    private function setMode(string $mode): void
    {
        $this->app['config']->set('sparallel.mode', $mode);
    }

    /**
     * @param class-string<DriverInterface> $driverClass
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function assertDriver(string $driverClass): void
    {
        self::assertEquals(
            $driverClass,
            $this->app->get(DriverFactoryInterface::class)->get()::class
        );
    }
}
