<?php

declare(strict_types=1);

namespace SParallelLaravel\Events;

use SParallel\Services\Context;

readonly class TaskStartingEvent
{
    public function __construct(
        public string $driverName,
        public Context $context,
    ) {
    }
}
