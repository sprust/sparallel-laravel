<?php

declare(strict_types=1);

namespace SParallelLaravel\Implementation;

use SParallel\Contracts\ContextSetterInterface;
use SParallel\Services\Context;

class ContextSetter implements ContextSetterInterface
{
    public function set(Context $context): void
    {
        app()->bind(Context::class, static fn() => $context);
    }
}
