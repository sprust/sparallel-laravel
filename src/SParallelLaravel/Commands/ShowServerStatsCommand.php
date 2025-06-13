<?php

declare(strict_types=1);

namespace SParallelLaravel\Commands;

use Illuminate\Console\Command;
use SParallel\Server\ManagerRpcClient;
use Throwable;

class ShowServerStatsCommand extends Command
{
    protected $signature = 'sparallel:server:stats';

    protected $description = 'Show server stats';

    public function handle(ManagerRpcClient $client): void
    {
        /** @phpstan-ignore-next-line while.alwaysTrue */
        while (true) {
            try {
                $stats = $client->stats();
            } catch (Throwable $exception) {
                system('clear');

                echo $exception->getMessage() . PHP_EOL;

                sleep(1);

                continue;
            }

            system('clear');

            dump($stats);

            sleep(1);
        }
    }
}
