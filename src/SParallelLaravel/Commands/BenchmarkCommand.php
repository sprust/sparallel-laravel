<?php

declare(strict_types=1);

namespace SParallelLaravel\Commands;

use Illuminate\Console\Command;
use Psr\Container\ContainerInterface;
use SParallel\Drivers\Server\ServerDriver;
use SParallel\TestCases\Benchmark;
use Throwable;

class BenchmarkCommand extends Command
{
    protected $signature = 'sparallel:workers:benchmark
        {--timeoutSeconds=5}
        {--workersLimit=10}
        {--uniqueCount=5}
        {--bigResponseCount=5}
        {--sleepCount=5}
        {--sleepSec=1}
        {--memoryLimitCount=5}
        {--throwCount=5}';

    protected $description = 'Benchmark server workers';

    /**
     * @throws Throwable
     */
    public function handle(ContainerInterface $container): void
    {
        ini_set('memory_limit', '1G');

        $benchmark = new Benchmark(
            uniqueCount: (int) $this->option('uniqueCount'),
            bigResponseCount: (int) $this->option('bigResponseCount'),
            sleepCount: (int) $this->option('sleepCount'),
            sleepSec: (int) $this->option('sleepSec'),
            memoryLimitCount: (int) $this->option('memoryLimitCount'),
            throwCount: (int) $this->option('throwCount'),
        );

        $driverClasses = [
            ServerDriver::class,
        ];

        $benchmark->start(
            container: $container,
            driverClasses: $driverClasses,
            timeoutSeconds: (int) $this->option('timeoutSeconds'),
            workersLimit: (int) $this->option('workersLimit'),
        );
    }
}
