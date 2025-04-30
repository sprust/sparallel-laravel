<?php

declare(strict_types=1);

namespace SParallelLaravel\Events;

use SParallel\Services\Context;
use Throwable;

readonly class SParallelFlowFailedEvent
{
    public function __construct(
        public Context $context,
        public Throwable $exception
    ) {
    }
}
