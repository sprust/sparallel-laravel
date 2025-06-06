<?php

declare(strict_types=1);

namespace SParallelLaravel\Commands;

use Illuminate\Console\Command;
use SParallel\SParallelWorkers;
use Throwable;

class BenchmarkServerWorkersCommand extends Command
{
    protected $signature = 'sparallel:server:workers:benchmark';

    protected $description = 'Benchmark server workers';

    /**
     * @throws Throwable
     */
    public function handle(SParallelWorkers $workers): void
    {
        $start = microtime(true);

        $callbacks = [
            static fn() => uniqid(),
            static fn() => uniqid(),
            static fn() => uniqid(),
            static fn() => uniqid(),
            static fn() => uniqid(),
            static fn() => uniqid(),
            static fn() => uniqid(),
            static fn() => uniqid(),
            static fn() => uniqid(),
            static fn() => uniqid(),
        ];

        $res = $workers->run($callbacks, 3);

        foreach ($res as $item) {
            dump($item);
        }

        dump(microtime(true) - $start);
    }
}
