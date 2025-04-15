<?php

declare(strict_types=1);

namespace SParallelLaravel\Events;

readonly class SParallelTaskStartingEvent
{
    /**
     * @param array<string, mixed>|null $context
     */
    public function __construct(
        public string $driverName,
        public ?array $context
    ) {
    }
}
