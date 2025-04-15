<?php

declare(strict_types=1);

namespace SParallelLaravel\Events;

use Throwable;

readonly class SParallelTaskFailedEvent
{
    public function __construct(
        public string $driverName,
        public Throwable $exception
    ) {
    }
}
