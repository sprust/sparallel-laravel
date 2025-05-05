<?php

declare(strict_types=1);

namespace SParallelLaravel\Implementation;

use SParallel\Contracts\ProcessCommandResolverInterface;
use SParallelLaravel\Workers\WorkerCommandFactory;

class ProcessCommandResolver implements ProcessCommandResolverInterface
{
    public function __construct(protected WorkerCommandFactory $commandFactory)
    {
    }

    public function get(): string
    {
        return $this->commandFactory->make('sparallel-process');
    }
}
