<?php

declare(strict_types=1);

namespace SParallelLaravel\Events;

use SParallel\Entities\Context;
use Throwable;

readonly class TaskFailedEvent
{
    public function __construct(
        public string $driverName,
        public Context $context,
        public Throwable $exception
    ) {
    }
}
