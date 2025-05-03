<?php

declare(strict_types=1);

namespace SParallelLaravel\Listeners;

use SParallelLaravel\Events\TaskFailedEvent;

class LogSParallelTaskFailedListener
{
    public function handle(TaskFailedEvent $event): void
    {
        report($event->exception);
    }
}
