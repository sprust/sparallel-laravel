<?php

declare(strict_types=1);

namespace SParallelLaravel\Events;

readonly class ProcessFinishedEvent
{
    public function __construct(
        public int $pid,
    ) {
    }
}
