<?php

declare(strict_types=1);

namespace SParallelLaravel\Commands;

use Illuminate\Console\Command;
use SParallel\Server\StatsRpcClient;
use Throwable;

class ServerStatsCommand extends Command
{
    protected $signature = 'sparallel:server:stats';

    protected $description = 'Monitor processes';

    public function handle(StatsRpcClient $client): void
    {
        /** @phpstan-ignore-next-line while.alwaysTrue */
        while (true) {
            try {
                $json = $client->get();
            } catch (Throwable $exception) {
                system('clear');

                echo $exception->getMessage() . PHP_EOL;

                sleep(1);

                continue;
            }

            system('clear');

            dump(json_decode($json, true));

            sleep(1);
        }
    }
}
