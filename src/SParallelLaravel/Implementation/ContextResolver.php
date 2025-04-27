<?php

declare(strict_types=1);

namespace SParallelLaravel\Implementation;

use SParallel\Contracts\ContextResolverInterface;
use SParallel\Services\Context;

class ContextResolver implements ContextResolverInterface
{
    public function set(Context $context): void
    {
        app()->singleton(Context::class, static fn() => $context);
    }

    public function get(): Context
    {
        return app(Context::class);
    }
}
