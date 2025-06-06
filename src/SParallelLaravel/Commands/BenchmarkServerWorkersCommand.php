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

        while ($counter <= $total) {
            ++$counter;

            $callbacks[] = static fn() => uniqid();
        }

        $counter = 0;

        memory_reset_peak_usage();

        $start = microtime(true);

        $generator = $workers->run($callbacks, 30);

        foreach ($generator as $result) {
            ++$counter;

            if ($result->error) {
                echo sprintf(
                    "%f\t%s\tERROR\t%s\t%s\n",
                    microtime(true),
                    $result->taskKey,
                    $counter,
                    substr($result->error->message, 0, 50),
                );

                continue;
            }

            echo sprintf(
                "%f\t%s\tINFO\t%s\t%s\n",
                microtime(true),
                $result->taskKey,
                $counter,
                substr($result->result, 0, 50),
            );
        }

        echo sprintf(
                "\n\nmemPeak:%f\ttime:%f\tcount:%d/%d",
                round(memory_get_peak_usage() / 1024 / 1024, 4),
                microtime(true) - $start,
                $counter,
                $total,
            ) . PHP_EOL;
    }
}
