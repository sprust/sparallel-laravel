<?php

namespace SParallelLaravel\Tests\Workers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use SParallel\Contracts\DriverFactoryInterface;
use SParallel\Contracts\DriverInterface;
use SParallel\Drivers\Server\ServerDriver;
use SParallel\Drivers\Sync\SyncDriver;
use SParallel\Entities\Context;
use SParallel\Exceptions\ContextCheckerException;
use SParallel\SParallelWorkers;
use SParallel\TestCases\SParallelWorkersTestCasesTrait;
use SParallelLaravel\Events\FlowFailedEvent;
use SParallelLaravel\Events\FlowStartingEvent;
use SParallelLaravel\Events\ServerGoneEvent;
use SParallelLaravel\Events\TaskFailedEvent;
use SParallelLaravel\Events\TaskFinishedEvent;
use SParallelLaravel\Tests\BaseTestCase;

class SParallelWorkersTest extends BaseTestCase
{
    use SParallelWorkersTestCasesTrait;

    /**
     * @param class-string<DriverInterface> $driverClass
     *
     * @throws ContextCheckerException
     * @throws BindingResolutionException
     */
    #[Test]
    #[DataProvider('allDriversDataProvider')]
    public function success(string $driverClass): void
    {
        $this->onSuccess(
            workers: $this->makeService($driverClass),
        );
    }

    /**
     * @param class-string<DriverInterface> $driverClass
     *
     * @throws ContextCheckerException
     * @throws BindingResolutionException
     */
    #[Test]
    #[DataProvider('allDriversDataProvider')]
    public function waitFirstOnlySuccess(string $driverClass): void
    {
        $this->onWaitFirstOnlySuccess(
            workers: $this->makeService($driverClass),
        );
    }

    /**
     * @param class-string<DriverInterface> $driverClass
     *
     * @throws ContextCheckerException
     * @throws BindingResolutionException
     */
    #[Test]
    #[DataProvider('allDriversDataProvider')]
    public function waitFirstNotOnlySuccess(string $driverClass): void
    {
        $this->onWaitFirstNotOnlySuccess(
            workers: $this->makeService($driverClass),
        );
    }

    /**
     * @param class-string<DriverInterface> $driverClass
     *
     * @throws ContextCheckerException
     * @throws BindingResolutionException
     */
    #[Test]
    #[DataProvider('allDriversDataProvider')]
    public function workersLimit(string $driverClass): void
    {
        $this->onWorkersLimit(
            workers: $this->makeService($driverClass),
        );
    }

    /**
     * @param class-string<DriverInterface> $driverClass
     *
     * @throws ContextCheckerException
     * @throws BindingResolutionException
     */
    #[Test]
    #[DataProvider('allDriversDataProvider')]
    public function failure(string $driverClass): void
    {
        $this->onFailure(
            workers: $this->makeService($driverClass),
        );
    }

    /**
     * @param class-string<DriverInterface> $driverClass
     *
     * @throws BindingResolutionException
     */
    #[Test]
    #[DataProvider('allDriversDataProvider')]
    public function timeout(string $driverClass): void
    {
        $this->onTimeout(
            workers: $this->makeService($driverClass),
        );
    }

    /**
     * @param class-string<DriverInterface> $driverClass
     *
     * @throws ContextCheckerException
     * @throws BindingResolutionException
     */
    #[Test]
    #[DataProvider('allDriversDataProvider')]
    public function breakAtFirstError(string $driverClass): void
    {
        $this->onBreakAtFirstError(
            workers: $this->makeService($driverClass),
        );
    }

    /**
     * @param class-string<DriverInterface> $driverClass
     *
     * @throws ContextCheckerException
     * @throws BindingResolutionException
     */
    #[Test]
    #[DataProvider('allDriversDataProvider')]
    public function bigPayload(string $driverClass): void
    {
        $this->onBigPayload(
            workers: $this->makeService($driverClass),
        );
    }

    /**
     * @param class-string<DriverInterface> $driverClass
     *
     * @throws ContextCheckerException
     * @throws BindingResolutionException
     */
    #[Test]
    #[DataProvider('asyncDriversDataProvider')]
    public function memoryLeak(string $driverClass): void
    {
        $this->onMemoryLeak(
            workers: $this->makeService($driverClass),
        );
    }


    /**
     * @param class-string<DriverInterface> $driverClass
     *
     * @throws ContextCheckerException
     * @throws BindingResolutionException
     */
    #[Test]
    #[DataProvider('allDriversDataProvider')]
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

        $this->assertEventsCount($eventsCounts, FlowStartingEvent::class, 1);
        $this->assertEventsCount($eventsCounts, FlowFailedEvent::class, 0);
        $this->assertEventsCount($eventsCounts, FlowStartingEvent::class, 1);

        if ($driverClass === SyncDriver::class) {
            $this->assertEventsCount($eventsCounts, TaskFailedEvent::class, 0);
            $this->assertEventsCount($eventsCounts, TaskFinishedEvent::class, 2);
        }
    }

    /**
     * @param class-string<DriverInterface> $driverClass
     *
     * @throws ContextCheckerException
     * @throws BindingResolutionException
     */
    #[Test]
    #[DataProvider('allDriversDataProvider')]
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

        $eventsCounts = $this->getEventCounts();

        $this->assertEventsCount($eventsCounts, FlowStartingEvent::class, 1);
        $this->assertEventsCount($eventsCounts, FlowFailedEvent::class, 0);
        $this->assertEventsCount($eventsCounts, FlowStartingEvent::class, 1);

        if ($driverClass === SyncDriver::class) {
            $this->assertEventsCount($eventsCounts, TaskFailedEvent::class, 2);
            $this->assertEventsCount($eventsCounts, TaskFinishedEvent::class, 2);
        }
    }

    /**
     * @throws BindingResolutionException
     */
    private function makeService(string $driverClass): SParallelWorkers
    {
        $app = $this->app;

        $app->make(DriverFactoryInterface::class)->forceDriver(
            $app->make($driverClass)
        );

        return $app->make(SParallelWorkers::class);
    }

    private function assertServerHaveNotGone(): void
    {
        $eventsCounts = $this->getEventCounts();

        $this->assertEventsCount($eventsCounts, ServerGoneEvent::class, 0);
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
     * @return array<class-string<object>, int>
     */
    private function getEventCounts(): array
    {
        return array_map(
            static fn($dispatchedEvent) => count($dispatchedEvent),
            Event::dispatchedEvents()
        );
    }

    /**
     * @return array{driverClass: class-string<DriverInterface>}[]
     */
    public static function allDriversDataProvider(): array
    {
        return [
            'sync'   => self::makeDriverCase(
                driverClass: SyncDriver::class
            ),
            'server' => self::makeDriverCase(
                driverClass: ServerDriver::class
            ),
        ];
    }

    /**
     * @return array{driverClass: class-string<DriverInterface>}[]
     */
    public static function asyncDriversDataProvider(): array
    {
        return [
            'server' => self::makeDriverCase(
                driverClass: ServerDriver::class
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

    protected function tearDown(): void
    {
        $this->assertServerHaveNotGone();

        parent::tearDown();
    }
}
