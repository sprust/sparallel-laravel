<?php

declare(strict_types=1);

namespace SParallelLaravel\Commands;

use Illuminate\Console\Command;
use SParallel\SParallelWorkers;
use Throwable;

class BenchmarkServerWorkersCommand extends Command
{
    protected $signature = 'sparallel:server:workers:benchmark {count?}';

    protected $description = 'Benchmark server workers';

    /**
     * @throws Throwable
     */
    public function handle(SParallelWorkers $workers): void
    {
        $total = ((int) $this->argument('count')) ?: 10;

        $counter = 0;

        $callbacks = [];

        while ($counter < $total) {
            ++$counter;

            $callbacks[] = static fn() => uniqid();
        }

        $totalCounter   = 0;
        $successCounter = 0;
        $failedCounter  = 0;

        memory_reset_peak_usage();

        $start = microtime(true);

        $generator = $workers->run($callbacks, 30);

        foreach ($generator as $result) {
            ++$totalCounter;

            if ($result->error) {
                ++$failedCounter;

                echo sprintf(
                    "%f\t%s\tERROR\t%s\t%s\n",
                    microtime(true),
                    $result->taskKey,
                    $totalCounter,
                    substr($result->error->message, 0, 50),
                );

                continue;
            }

            ++$successCounter;

            echo sprintf(
                "%f\t%s\tINFO\t%s\t%s\n",
                microtime(true),
                $result->taskKey,
                $totalCounter,
                substr($result->result, 0, 50),
            );
        }

        echo sprintf(
                "\n\nmemPeak:%f\ttime:%f\tcount:%d/%d\tsuccess:%d/%d\tfailed:%d/%d",
                round(memory_get_peak_usage() / 1024 / 1024, 4),
                microtime(true) - $start,
                $totalCounter,
                $total,
                $successCounter,
                $total,
                $failedCounter,
                $total,
            ) . PHP_EOL;
    }
}
