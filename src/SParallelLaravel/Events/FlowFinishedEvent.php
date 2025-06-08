<?php

declare(strict_types=1);

namespace SParallelLaravel\Events;

use SParallel\Entities\Context;

readonly class FlowFinishedEvent
{
    public function __construct(
        public Context $context,
    ) {
    }
}
