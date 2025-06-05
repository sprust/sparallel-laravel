<?php

declare(strict_types=1);

namespace SParallelLaravel\Events;

use SParallel\Entities\Context;
use Throwable;

readonly class FlowFailedEvent
{
    public function __construct(
        public Context $context,
        public Throwable $exception
    ) {
    }
}
