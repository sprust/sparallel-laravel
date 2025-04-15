<?php

declare(strict_types=1);

namespace SParallelLaravel\Drivers\ProcessWithForkInside;

use Illuminate\Contracts\Foundation\Application;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use SParallel\Contracts\DriverInterface;
use SParallel\Contracts\WaitGroupInterface;
use SParallel\Drivers\Fork\ForkDriver;
use SParallel\Drivers\Process\ProcessDriver;
use SParallel\Exceptions\SParallelTimeoutException;
use SParallel\Objects\ResultsObject;
use SParallel\Services\SParallelService;
use SParallelLaravel\Drivers\DriverFactory;

class ProcessWithForkInsideDriver implements DriverInterface
{
    public function __construct(protected Application $app)
    {
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws SParallelTimeoutException
     * @throws ContainerExceptionInterface
     */
    public function wait(array $callbacks): WaitGroupInterface
    {
        $callback = static function () use ($callbacks): ResultsObject {
            $forkDriver = app(SParallelService::class, [
                'driver' => app(DriverFactory::class)->get(ForkDriver::class),
            ]);

            return $forkDriver->wait($callbacks);
        };

        $processDriver = $this->app->get(DriverFactory::class)
            ->get(ProcessDriver::class);

        $waitGroup = $processDriver->wait([$callback]);

        return new ProcessWithForkInsideWaitGroup(
            waitGroup: $waitGroup,
            callbacks: $callbacks
        );
    }
}
