<?php

declare(strict_types=1);

namespace SParallelLaravel\Implementation;

use SParallel\Contracts\EventsBusInterface;
use SParallel\Entities\Context;
use SParallel\Exceptions\RpcCallException;
use SParallelLaravel\Events\FlowFailedEvent;
use SParallelLaravel\Events\FlowFinishedEvent;
use SParallelLaravel\Events\FlowStartingEvent;
use SParallelLaravel\Events\ServerGoneEvent;
use SParallelLaravel\Events\TaskFailedEvent;
use SParallelLaravel\Events\TaskFinishedEvent;
use SParallelLaravel\Events\TaskStartingEvent;
use Throwable;

class EventsBus implements EventsBusInterface
{
    public function flowStarting(Context $context): void
    {
        event(new FlowStartingEvent(context: $context));
    }

    public function flowFailed(Context $context, Throwable $exception): void
    {
        event(new FlowFailedEvent(context: $context, exception: $exception));
    }

    public function flowFinished(Context $context): void
    {
        event(new FlowFinishedEvent(context: $context));
    }

    public function taskStarting(string $driverName, Context $context): void
    {
        event(
            new TaskStartingEvent(
                driverName: $driverName,
                context: $context
            )
        );
    }

    public function taskFailed(string $driverName, Context $context, Throwable $exception): void
    {
        event(
            new TaskFailedEvent(
                driverName: $driverName,
                context: $context,
                exception: $exception
            )
        );
    }

    public function taskFinished(string $driverName, Context $context): void
    {
        event(
            new TaskFinishedEvent(
                driverName: $driverName,
                context: $context
            )
        );
    }

    public function onServerGone(Context $context, RpcCallException $exception): void
    {
        event(
            new ServerGoneEvent(
                context: $context,
                exception: $exception
            )
        );
    }
}
