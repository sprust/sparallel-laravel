<?php

declare(strict_types=1);

namespace SParallelLaravel\Events;

use SParallel\Entities\Context;
use SParallel\Exceptions\RpcCallException;

readonly class ServerGoneEvent
{
    public function __construct(
        public Context $context,
        public RpcCallException $exception
    ) {
    }
}
