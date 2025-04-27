<?php

declare(strict_types=1);

namespace SParallelLaravel\Commands;

use Illuminate\Console\Command;
use SParallel\Drivers\Hybrid\HybridProcessHandler;
use SParallel\Exceptions\CancelerException;

class HandleHybridProcessTaskCommand extends Command
{
    protected $signature = 'sparallel:handle-hybrid-process-task';

    protected $description = 'Handle task of hybrid driver';

    /**
     * @throws CancelerException
     */
    public function handle(HybridProcessHandler $hybridProcessHandler): void
    {
        $hybridProcessHandler->handle();
    }
}
