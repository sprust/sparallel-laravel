<?php

declare(strict_types=1);

namespace SParallelLaravel\Listeners;

use SParallelLaravel\Events\SParallelFlowFailedEvent;

class LogSParallelFlowFailedListener
{
    public function handle(SParallelFlowFailedEvent $event): void
    {
        report($event->exception);
    }
}
