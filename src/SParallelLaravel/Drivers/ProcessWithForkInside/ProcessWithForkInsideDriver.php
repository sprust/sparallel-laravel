<?php

declare(strict_types=1);

namespace SParallelLaravel\Drivers\ProcessWithForkInside;

use SParallel\Contracts\DriverInterface;
use SParallel\Contracts\WaitGroupInterface;
use SParallel\Drivers\Fork\ForkDriver;
use SParallel\Drivers\Process\ProcessDriver;
use SParallel\Exceptions\SParallelTimeoutException;
use SParallel\Objects\ResultsObject;
use SParallel\Services\SParallelService;

class ProcessWithForkInsideDriver implements DriverInterface
{
    /**
     * @throws SParallelTimeoutException
     */
    public function wait(array $callbacks): WaitGroupInterface
    {
        $callback = static function () use ($callbacks): ResultsObject {
            $forkDriver = app(SParallelService::class, [
                'driver' => app(ForkDriver::class),
            ]);

            return $forkDriver->wait($callbacks);
        };

        $waitGroup = app(ProcessDriver::class)->wait([$callback]);

        return new ProcessWithForkInsideWaitGroup(
            waitGroup: $waitGroup,
            callbacks: $callbacks
        );
    }
}
