<?php

declare(strict_types=1);

namespace SParallelLaravel;

use SParallel\Contracts\TaskEventsBusInterface;
use SParallel\Objects\Context;
use SParallelLaravel\Events\SParallelTaskFailedEvent;
use SParallelLaravel\Events\SParallelTaskFinishedEvent;
use SParallelLaravel\Events\SParallelTaskStartingEvent;
use Throwable;

class TaskEventsBus implements TaskEventsBusInterface
{
    public function starting(string $driverName, ?Context $context): void
    {
        event(
            new SParallelTaskStartingEvent(
                driverName: $driverName,
                context: $context?->all()
            )
        );
    }

    public function failed(string $driverName, ?Context $context, Throwable $exception): void
    {
        event(
            new SParallelTaskFailedEvent(
                driverName: $driverName,
                context: $context?->all(),
                exception: $exception
            )
        );
    }

    public function finished(string $driverName, ?Context $context): void
    {
        event(
            new SParallelTaskFinishedEvent(
                driverName: $driverName,
                context: $context?->all()
            )
        );
    }
}
