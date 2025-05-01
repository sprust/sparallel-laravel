<?php

namespace SParallelLaravel\Tests;

use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;
use SParallel\Contracts\DriverInterface;
use SParallel\Contracts\EventsBusInterface;
use SParallel\Drivers\Fork\ForkDriver;
use SParallel\Drivers\Hybrid\HybridDriver;
use SParallel\Drivers\Process\ProcessDriver;
use SParallel\Drivers\Sync\SyncDriver;
use SParallel\Exceptions\ContextCheckerException;
use SParallel\Services\Context;
use SParallel\Services\SParallelService;
use SParallel\TestCases\SParallelServiceTestCasesTrait;
use SParallelLaravel\Events\SParallelFlowFailedEvent;
use SParallelLaravel\Events\SParallelFlowStartingEvent;

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
            service: $this->makeService($driverClass),
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ContextCheckerException
     */
    #[Test]
    #[DataProvider('driversDataProvider')]
    public function waitFirstOnlySuccess(string $driverClass): void
    {
        $this->onWaitFirstOnlySuccess(
            service: $this->makeService($driverClass),
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ContextCheckerException
     */
    #[Test]
    #[DataProvider('driversDataProvider')]
    public function waitFirstNotOnlySuccess(string $driverClass): void
    {
        $this->onWaitFirstNotOnlySuccess(
            service: $this->makeService($driverClass),
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ContextCheckerException
     */
    #[Test]
    #[DataProvider('driversDataProvider')]
    public function workersLimit(string $driverClass): void
    {
        $this->onWorkersLimit(
            service: $this->makeService($driverClass),
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
            service: $this->makeService($driverClass),
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
            service: $this->makeService($driverClass),
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
            service: $this->makeService($driverClass),
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
            service: $this->makeService($driverClass),
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
            service: $this->makeService($driverClass),
        );
    }


    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ContextCheckerException
     */
    #[Test]
    #[DataProvider('driversDataProvider')]
    public function eventsSuccess(string $driverClass): void
    {
        Event::fake();

        $callbacks = [
            'first'  => static fn(Context $context) => uniqid(),
            'second' => static fn(Context $context) => uniqid(),
        ];

        $callbacksCount = count($callbacks);

        $service = $this->makeService($driverClass);

        $results = $service->wait(
            callbacks: $callbacks,
            timeoutSeconds: 1,
        );

        self::assertTrue($results->isFinished());
        self::assertFalse($results->hasFailed());
        self::assertTrue($results->count() === $callbacksCount);

        /**
         * @var array<string, int> $eventsCounts
         */
        $eventsCounts = array_map(
            static fn($dispatchedEvent) => count($dispatchedEvent),
            Event::dispatchedEvents()
        );

        $this->assertEventsCount($eventsCounts, SParallelFlowStartingEvent::class, 1);
        $this->assertEventsCount($eventsCounts, SParallelFlowFailedEvent::class, 0);
        $this->assertEventsCount($eventsCounts, SParallelFlowStartingEvent::class, 1);
        // TODO: background tasks
        //$this->assertEventsCount($eventsCounts, SParallelTaskFailedEvent::class, 0);
        //$this->assertEventsCount($eventsCounts, SParallelTaskFinishedEvent::class, 2);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ContextCheckerException
     */
    #[Test]
    #[DataProvider('driversDataProvider')]
    public function eventsFailed(string $driverClass): void
    {
        Event::fake();

        $callbacks = [
            'first'  => static fn(Context $context) => throw new RuntimeException('first'),
            'second' => static fn(Context $context) => throw new RuntimeException('second'),
        ];

        $callbacksCount = count($callbacks);

        $service = $this->makeService($driverClass);

        $results = $service->wait(
            callbacks: $callbacks,
            timeoutSeconds: 1,
        );

        self::assertTrue($results->isFinished());
        self::assertTrue($results->hasFailed());
        self::assertTrue($results->count() === $callbacksCount);

        /**
         * @var array<string, int> $eventsCounts
         */
        $eventsCounts = array_map(
            static fn($dispatchedEvent) => count($dispatchedEvent),
            Event::dispatchedEvents()
        );

        $this->assertEventsCount($eventsCounts, SParallelFlowStartingEvent::class, 1);
        $this->assertEventsCount($eventsCounts, SParallelFlowFailedEvent::class, 0);
        $this->assertEventsCount($eventsCounts, SParallelFlowStartingEvent::class, 1);
        // TODO: background tasks
        //$this->assertEventsCount($eventsCounts, SParallelTaskFailedEvent::class, 0);
        //$this->assertEventsCount($eventsCounts, SParallelTaskFinishedEvent::class, 2);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function makeService(string $driverClass): SParallelService
    {
        return new SParallelService(
            driver: $this->app->get($driverClass),
            eventsBus: $this->app->get(EventsBusInterface::class)
        );
    }

    /**
     * @param array<class-string<object>, int> $eventsCounts
     */
    private function assertEventsCount(array $eventsCounts, string $eventClass, int $expectedCount): void
    {
        $currentCount = $eventsCounts[$eventClass] ?? 0;

        self::assertEquals(
            $expectedCount,
            $currentCount,
            "Expected [$eventClass] events count: $expectedCount, got: $currentCount"
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
     * @return array{driverClass: class-string<DriverInterface>}[]
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
     * @return array{driverClass: class-string<DriverInterface>}
     */
    private static function makeDriverCase(string $driverClass): array
    {
        return [
            'driverClass' => $driverClass,
        ];
    }
}
