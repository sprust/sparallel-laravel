<?php

declare(strict_types=1);

namespace SParallelLaravel\Events;

use SParallel\Objects\Context;
use Throwable;

readonly class SParallelTaskFailedEvent
{
    public function __construct(
        public string $driverName,
        public ?Context $context,
        public Throwable $exception
    ) {
    }
}
