<?php

declare(strict_types=1);

namespace SParallelLaravel\Workers;

readonly class Worker
{
    public function __construct(
        public string $key,
        public int $pid,
        public int $createdAt,
        public int $systemUptime,
        public bool $isActive,
        public bool $isFromPreviousUptime,
    ) {
    }
}
