<?php

declare(strict_types=1);

namespace SParallelLaravel\Events;

readonly class SParallelTaskFinishedEvent
{
    public function __construct(
        public string $driverName
    ) {
    }
}
