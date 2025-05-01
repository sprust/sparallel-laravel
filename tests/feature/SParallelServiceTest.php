<?php

namespace SParallelLaravel\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use SParallel\Contracts\DriverInterface;
use SParallel\Contracts\EventsBusInterface;
use SParallel\Drivers\Fork\ForkDriver;
use SParallel\Drivers\Hybrid\HybridDriver;
use SParallel\Drivers\Process\ProcessDriver;
use SParallel\Drivers\Sync\SyncDriver;
use SParallel\Exceptions\ContextCheckerException;
use SParallel\Services\SParallelService;
use SParallel\TestCases\SParallelServiceTestCasesTrait;

class SParallelServiceTest extends BaseTestCase
{
    use SParallelServiceTestCasesTrait;

    /**
     * @param class-string<DriverInterface> $driverClass
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ContextCheckerException
     */
    #[Test]
    #[DataProvider('driversDataProvider')]
    public function success(string $driverClass): void
    {
        $this->onSuccess(
            service: $this->mekService($driverClass),
        );
    }

    /**
     * @param class-string<DriverInterface> $driverClass
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ContextCheckerException
     */
    #[Test]
    #[DataProvider('driversDataProvider')]
    public function failure(string $driverClass): void
    {
        $this->onFailure(
            service: $this->mekService($driverClass),
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Test]
    #[DataProvider('driversDataProvider')]
    public function timeout(string $driverClass): void
    {
        $this->onTimeout(
            service: $this->mekService($driverClass),
        );
    }

    /**
     * @throws ContextCheckerException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Test]
    #[DataProvider('driversDataProvider')]
    public function breakAtFirstError(string $driverClass): void
    {
        $this->onBreakAtFirstError(
            service: $this->mekService($driverClass),
        );
    }

    /**
     * @throws ContextCheckerException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Test]
    #[DataProvider('driversDataProvider')]
    public function bigPayload(string $driverClass): void
    {
        $this->onBigPayload(
            service: $this->mekService($driverClass),
        );
    }

    /**
     * @throws ContextCheckerException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Test]
    #[DataProvider('driversMemoryLeakDataProvider')]
    public function memoryLeak(string $driverClass): void
    {
        $this->onMemoryLeak(
            service: $this->mekService($driverClass),
        );
    }

    // TODO: events test

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function mekService(string $driverClass): SParallelService
    {
        return new SParallelService(
            driver: $this->app->get($driverClass),
            eventsBus: $this->app->get(EventsBusInterface::class)
        );
    }

    /**
     * @return array{driverClass: class-string<DriverInterface>}[]
     */
    public static function driversDataProvider(): array
    {
        return [
            'sync'    => self::makeDriverCase(
                driverClass: SyncDriver::class
            ),
            // TODO
            //'process' => self::makeDriverCase(
            //    driverClass: ProcessDriver::class
            //),
            'fork'    => self::makeDriverCase(
                driverClass: ForkDriver::class
            ),
            // TODO
            //'hybrid'  => self::makeDriverCase(
            //    driverClass: HybridDriver::class
            //),
        ];
    }

    /**
     * @return array{driverClass: class-string<DriverInterface>}[]
     */
    public static function driversMemoryLeakDataProvider(): array
    {
        return [
            // TODO
            //'process' => self::makeDriverCase(
            //    driverClass: ProcessDriver::class
            //),
            'fork'    => self::makeDriverCase(
                driverClass: ForkDriver::class
            ),
            // TODO
            //'hybrid'  => self::makeDriverCase(
            //    driverClass: HybridDriver::class
            //),
        ];
    }

    /**
     * @return array{driverClass: class-string<DriverInterface>}
     */
    private static function makeDriverCase(string $driverClass): array
    {
        return [
            'driverClass' => $driverClass,
        ];
    }
}
