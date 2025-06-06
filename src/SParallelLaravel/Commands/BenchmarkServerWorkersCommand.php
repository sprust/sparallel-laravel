<?php

declare(strict_types=1);

namespace SParallelLaravel\Commands;

use Illuminate\Console\Command;
use Psr\Container\ContainerInterface;
use SParallel\Drivers\Server\ServerDriver;
use SParallel\TestCases\Benchmark;
use Throwable;

class BenchmarkServerWorkersCommand extends Command
{
    protected $signature = 'sparallel:server:workers:benchmark {count?}';

    protected $description = 'Benchmark server workers';

    /**
     * @throws Throwable
     */
    public function handle(ContainerInterface $container): void
    {
        ini_set('memory_limit', '1G');

        $count = ((int) $this->argument('count')) ?: 5;

        $benchmark = new Benchmark(
            uniqueCount: $count,
            bigResponseCount: $count,
            sleepCount: $count,
            sleepSec: 1,
            memoryLimitCount: $count,
            throwCount: $count,
        );

        $driverClasses = [
            ServerDriver::class,
        ];

        $timeoutSeconds = 50;
        $workersLimit   = 10;

        $benchmark->start(
            container: $container,
            driverClasses: $driverClasses,
            timeoutSeconds: $timeoutSeconds,
            workersLimit: $workersLimit,
        );
    }
}
