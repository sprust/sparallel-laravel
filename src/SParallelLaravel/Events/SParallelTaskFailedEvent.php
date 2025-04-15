<?php

declare(strict_types=1);

namespace SParallelLaravel\Events;

use Throwable;

readonly class SParallelTaskFailedEvent
{
    /**
     * @param array<string, mixed>|null $context
     */
    public function __construct(
        public string $driverName,
        public ?array $context,
        public Throwable $exception
    ) {
    }
}
