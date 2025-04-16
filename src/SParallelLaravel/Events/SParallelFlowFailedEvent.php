<?php

declare(strict_types=1);

namespace SParallelLaravel\Events;

use Throwable;

readonly class SParallelFlowFailedEvent
{
    public function __construct(public Throwable $exception)
    {
    }
}
