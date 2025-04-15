<?php

declare(strict_types=1);

namespace SParallelLaravel\Events;

readonly class SParallelTaskStartingEvent
{
    public function __construct(
        public string $driverName
    ) {
    }
}
