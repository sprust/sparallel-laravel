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
    public function starting(?Context $context): void
    {
        event(
            new SParallelTaskStartingEvent(
                $this->getDriverNameByContext($context),
                $context?->all()
            )
        );
    }

    public function failed(?Context $context, Throwable $exception): void
    {
        event(
            new SParallelTaskFailedEvent(
                $this->getDriverNameByContext($context),
                $context?->all(),
                $exception
            )
        );
    }

    public function finished(?Context $context): void
    {
        event(
            new SParallelTaskFinishedEvent(
                $this->getDriverNameByContext($context),
                $context?->all()
            )
        );
    }

    private function getDriverNameByContext(?Context $context): string
    {
        return $context?->get(Constants::CONTEXT_DRIVER_KEY) ?? 'unknown';
    }
}
