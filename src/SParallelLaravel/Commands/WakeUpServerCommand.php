<?php

declare(strict_types=1);

namespace SParallelLaravel\Commands;

use Illuminate\Console\Command;
use SParallel\Server\ManagerRpcClient;
use Throwable;

class WakeUpServerCommand extends Command
{
    protected $signature = 'sparallel:server:wake-up';

    protected $description = 'Sleep server';

    /**
     * @throws Throwable
     */
    public function handle(ManagerRpcClient $client): void
    {
        $this->components->info('Wake up server...');

        try {
            $client->wakeUp();
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return;
        }

        $this->components->info('Server is woke up');
    }
}
