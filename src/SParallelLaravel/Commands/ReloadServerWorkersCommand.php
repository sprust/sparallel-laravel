<?php

declare(strict_types=1);

namespace SParallelLaravel\Commands;

use Illuminate\Console\Command;
use SParallel\Server\Workers\WorkersRpcClient;
use Throwable;

class ReloadServerWorkersCommand extends Command
{
    protected $signature = 'sparallel:server:workers:reload';

    protected $description = 'Reload server workers';

    /**
     * @throws Throwable
     */
    public function handle(WorkersRpcClient $client): void
    {
        $client->reload();
    }
}
