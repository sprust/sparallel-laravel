<?php

declare(strict_types=1);

namespace SParallelLaravel\Commands;

use Illuminate\Console\Command;
use SParallel\Server\ManagerRpcClient;
use Throwable;

class StopServerCommand extends Command
{
    protected $signature = 'sparallel:server:stop';

    protected $description = 'Reload server workers';

    /**
     * @throws Throwable
     */
    public function handle(ManagerRpcClient $client): void
    {
        $this->components->info('Stopping server...');

        try {
            $client->stop();
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return;
        }

        $this->components->info('Server stopped');
    }
}
