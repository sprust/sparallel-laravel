<?php

declare(strict_types=1);

namespace SParallelLaravel\Implementation;

use Psr\Container\ContainerInterface;
use SParallel\Contracts\ContainerFactoryInterface;
use SParallelLaravel\Application\ApplicationFactory;

readonly class ContainerFactory implements ContainerFactoryInterface
{
    private ContainerInterface $container;

    public function __construct()
    {
        $this->container = ApplicationFactory::create();
    }

    public function create(): ContainerInterface
    {
        return clone $this->container;
    }
}
