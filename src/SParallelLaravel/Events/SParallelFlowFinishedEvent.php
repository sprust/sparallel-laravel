<?php

declare(strict_types=1);

namespace SParallelLaravel\Events;

use SParallel\Services\Context;

readonly class SParallelFlowFinishedEvent
{
    public function __construct(
        public Context $context,
    ) {
    }
}
