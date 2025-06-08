<?php

declare(strict_types=1);

namespace SParallelLaravel\Listeners;

use SParallelLaravel\Events\FlowFailedEvent;

class ReportFlowFailedListener
{
    public function handle(FlowFailedEvent $event): void
    {
        report($event->exception);
    }
}
