<?php

declare(strict_types=1);

namespace SParallelLaravel\Implementation;

use SParallel\Contracts\HybridProcessCommandResolverInterface;
use SParallelLaravel\Workers\WorkerCommandFactory;

class HybridProcessCommandResolver implements HybridProcessCommandResolverInterface
{
    public function __construct(protected WorkerCommandFactory $commandFactory)
    {
    }

    public function get(): string
    {
        return $this->commandFactory->make('sparallel-hybrid-process');
    }
}
