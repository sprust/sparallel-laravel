<?php

namespace SParallelLaravel\Tests;

use Illuminate\Support\Env;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use SParallel\Contracts\DriverInterface;
use SParallel\Drivers\Fork\ForkDriver;
use SParallel\Drivers\Hybrid\HybridDriver;
use SParallel\Drivers\Process\ProcessDriver;
use SParallel\Drivers\Sync\SyncDriver;

class ConfigModeTest extends BaseTestCase
{
    protected function tearDown(): void
    {
        $this->setRunningInConsole(true);

        parent::tearDown();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUnknown(): void
    {
        $this->setMode(uniqid());

        $this->assertDriver(SyncDriver::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testSync(): void
    {
        $this->setMode('sync');

        $this->assertDriver(SyncDriver::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testProcess(): void
    {
        $this->setMode('process');

        $this->assertDriver(ProcessDriver::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testHybrid(): void
    {
        $this->setMode('hybrid');

        $this->assertDriver(HybridDriver::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testProcessForkInConsole(): void
    {
        $this->setRunningInConsole(true);

        $this->setMode('process_fork');

        $this->assertDriver(ForkDriver::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testProcessForkNotInConsole(): void
    {
        $this->setRunningInConsole(false);

        $this->setMode('process_fork');

        $this->assertDriver(ProcessDriver::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testHybridForkInConsole(): void
    {
        $this->setRunningInConsole(true);

        $this->setMode('hybrid_fork');

        $this->assertDriver(ForkDriver::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testHybridForkNotInConsole(): void
    {
        $this->setRunningInConsole(false);

        $this->setMode('hybrid_fork');

        $this->assertDriver(HybridDriver::class);
    }

    private function setMode(string $mode): void
    {
        $this->app['config']->set('sparallel.mode', $mode);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function assertDriver(string $driverClass): void
    {
        self::assertEquals(
            $driverClass,
            $this->app->get(DriverInterface::class)::class
        );
    }

    private function setRunningInConsole(bool $isRunningInConsole): void
    {
        Env::getRepository()->set('APP_RUNNING_IN_CONSOLE', $isRunningInConsole ? 'true' : 'false');

        $this->refreshApplication();
    }
}
