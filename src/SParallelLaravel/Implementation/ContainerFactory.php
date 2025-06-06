<?php

declare(strict_types=1);

namespace SParallelLaravel\Implementation;

use Closure;
use Psr\Container\ContainerInterface;
use SParallel\Contracts\ContainerFactoryInterface;

readonly class ContainerFactory implements ContainerFactoryInterface
{
    public function __construct(private Closure $bootstrapResolver)
    {
    }

    public function create(): ContainerInterface
    {
        return call_user_func($this->bootstrapResolver);
    }
}
