<?php

declare(strict_types=1);

namespace SParallelLaravel\Events;

use SParallel\Objects\Context;

readonly class SParallelTaskFinishedEvent
{
    public function __construct(
        public string $driverName,
        public ?Context $context,
    ) {
    }
}
