<?php

declare(strict_types=1);

namespace SParallelLaravel\Commands;

use Illuminate\Console\Command;
use SParallel\Drivers\Process\ProcessHandler;
use SParallel\Exceptions\SParallelTimeoutException;

class HandleProcessTaskCommand extends Command
{
    protected $signature = 'sparallel:handle-process-task';

    protected $description = 'Handle task of process driver';

    /**
     * @throws SParallelTimeoutException
     */
    public function handle(ProcessHandler $processHandler): void
    {
        $processHandler->handle();
    }
}
