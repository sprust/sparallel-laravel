<?php

declare(strict_types=1);

namespace SParallelLaravel\Commands;

use Illuminate\Console\Command;
use SParallel\Server\Workers\WorkersRpcClient;
use Throwable;

class StopServerCommand extends Command
{
    protected $signature = 'sparallel:server:stop';

    protected $description = 'Reload server workers';

    /**
     * @throws Throwable
     */
    public function handle(WorkersRpcClient $client): void
    {
        // TODO: move method to 'manage' server
        $client->stop();
    }
}
