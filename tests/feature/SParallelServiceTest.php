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
use SParallel\Exceptions\CancelerException;
use SParallel\Services\SParallelService;
use SParallel\TestCases\SParallelServiceTestCasesTrait;

class SParallelServiceTest extends BaseTestCase
{
    use SParallelServiceTestCasesTrait;

    protected function defineEnvironment($app): void
    {
        $app['config']->set('sparallel.async', true);
    }

    /**
     * @param class-string<DriverInterface> $driverClass
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws CancelerException
     */
    #[Test]
    #[DataProvider('driversDataProvider')]
    public function success(string $driverClass): void
    {
        $service = new SParallelService(
            driver: $this->app->get($driverClass),
            eventsBus: $this->app->get(EventsBusInterface::class),
        );

        $this->onSuccess($service);
    }

    /**
     * @param class-string<DriverInterface> $driverClass
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws CancelerException
     */
    #[Test]
    #[DataProvider('driversDataProvider')]
    public function failure(string $driverClass): void
    {
        $service = new SParallelService(
            driver: $this->app->get($driverClass),
            eventsBus: $this->app->get(EventsBusInterface::class),
        );

        $this->onFailure($service);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Test]
    #[DataProvider('driversDataProvider')]
    public function timeout(string $driverClass): void
    {
        $service = new SParallelService(
            driver: $this->app->get($driverClass),
            eventsBus: $this->app->get(EventsBusInterface::class),
        );

        $this->onTimeout($service);
    }

    /**
     * @throws CancelerException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Test]
    #[DataProvider('driversDataProvider')]
    public function breakAtFirstError(string $driverClass): void
    {
        $service = new SParallelService(
            driver: $this->app->get($driverClass),
            eventsBus: $this->app->get(EventsBusInterface::class),
        );

        $this->onBreakAtFirstError($service);
    }

    /**
     * @throws CancelerException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Test]
    #[DataProvider('driversDataProvider')]
    public function bigPayload(string $driverClass): void
    {
        $service = new SParallelService(
            driver: $this->app->get($driverClass),
            eventsBus: $this->app->get(EventsBusInterface::class),
        );

        $this->onBigPayload($service);
    }

    /**
     * @throws CancelerException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Test]
    #[DataProvider('driversMemoryLeakDataProvider')]
    public function memoryLeak(string $driverClass): void
    {
        $service = new SParallelService(
            driver: $this->app->get($driverClass),
            eventsBus: $this->app->get(EventsBusInterface::class),
        );

        $this->onMemoryLeak($service);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws CancelerException
     */
    //#[Test] // TODO
    #[DataProvider('driversDataProvider')]
    public function events(string $driverClass): void
    {
        $service = new SParallelService(
            driver: $this->app->get($driverClass),
            eventsBus: $this->app->get(EventsBusInterface::class)
        );

        $this->onEvents($service, static fn() => app());
    }

    /**
     * @return array{driver: DriverInterface}[]
     */
    public static function driversDataProvider(): array
    {
        return [
            'sync'    => self::makeDriverCase(
                driverClass: SyncDriver::class
            ),
            'process' => self::makeDriverCase(
                driverClass: ProcessDriver::class
            ),
            'fork'    => self::makeDriverCase(
                driverClass: ForkDriver::class
            ),
            'hybrid'  => self::makeDriverCase(
                driverClass: HybridDriver::class
            ),
        ];
    }

    /**
     * @return array{driver: DriverInterface}[]
     */
    public static function driversMemoryLeakDataProvider(): array
    {
        return [
            'process' => self::makeDriverCase(
                driverClass: ProcessDriver::class
            ),
            'fork'    => self::makeDriverCase(
                driverClass: ForkDriver::class
            ),
            'hybrid'  => self::makeDriverCase(
                driverClass: HybridDriver::class
            ),
        ];
    }

    /**
     * @return array{driver: DriverInterface}
     */
    private static function makeDriverCase(string $driverClass): array
    {
        return [
            'driverClass' => $driverClass,
        ];
    }
}
