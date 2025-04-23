<?php

namespace SParallelLaravel\Implementation;

use SParallel\Contracts\ContextSetterInterface;
use SParallel\Objects\Context;

class ContextSetter implements ContextSetterInterface
{
    public function set(Context $context): void
    {
        app()->bind(Context::class, static fn() => $context);
    }
}
