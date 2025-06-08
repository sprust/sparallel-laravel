<?php

declare(strict_types=1);

namespace SParallelLaravel\Listeners;

use SParallelLaravel\Events\ServerGoneEvent;

class ReportServerGoneListener
{
    public function handle(ServerGoneEvent $event): void
    {
        report($event->exception);
    }
}
