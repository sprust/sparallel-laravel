<?php

declare(strict_types=1);

namespace SParallelLaravel;

use SParallel\Contracts\EventsBusInterface;
use SParallel\Objects\Context;
use SParallelLaravel\Events\SParallelFlowFailedEvent;
use SParallelLaravel\Events\SParallelFlowFinishedEvent;
use SParallelLaravel\Events\SParallelFlowStartingEvent;
use SParallelLaravel\Events\SParallelTaskFailedEvent;
use SParallelLaravel\Events\SParallelTaskFinishedEvent;
use SParallelLaravel\Events\SParallelTaskStartingEvent;
use Throwable;

class EventsBus implements EventsBusInterface
{
    public function flowStarting(): void
    {
        event(new SParallelFlowStartingEvent());
    }

    public function flowFailed(Throwable $exception): void
    {
        event(new SParallelFlowFailedEvent(exception: $exception));
    }

    public function flowFinished(): void
    {
        event(new SParallelFlowFinishedEvent());
    }

    public function taskStarting(string $driverName, ?Context $context): void
    {
        event(
            new SParallelTaskStartingEvent(
                driverName: $driverName,
                context: $context
            )
        );
    }

    public function taskFailed(string $driverName, ?Context $context, Throwable $exception): void
    {
        event(
            new SParallelTaskFailedEvent(
                driverName: $driverName,
                context: $context,
                exception: $exception
            )
        );
    }

    public function taskFinished(string $driverName, ?Context $context): void
    {
        event(
            new SParallelTaskFinishedEvent(
                driverName: $driverName,
                context: $context
            )
        );
    }
}
