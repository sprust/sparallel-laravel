<?php

declare(strict_types=1);

namespace SParallelLaravel\Commands;

use Illuminate\Console\Command;
use SParallel\Server\ManagerRpcClient;
use Throwable;

class SleepServerCommand extends Command
{
    protected $signature = 'sparallel:server:sleep';

    protected $description = 'Sleep server';

    /**
     * @throws Throwable
     */
    public function handle(ManagerRpcClient $client): void
    {
        $this->components->info('Sleeping server...');

        try {
            $client->sleep();
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return;
        }

        $this->components->info('Server is sleeping');
    }
}
