<?php

declare(strict_types=1);

namespace SParallelLaravel\Listeners;

use SParallelLaravel\Events\SParallelTaskFailedEvent;

class LogSParallelTaskFailedListener
{
    public function handle(SParallelTaskFailedEvent $event): void
    {
        report($event->exception);
    }
}
