<?php

declare(strict_types=1);

namespace SParallelLaravel\Drivers;

use Illuminate\Contracts\Foundation\Application;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;
use SParallel\Contracts\DriverInterface;
use SParallel\Contracts\EventsBusInterface;
use SParallel\Drivers\Fork\ForkDriver;
use SParallel\Drivers\Process\ProcessDriver;
use SParallel\Drivers\Sync\SyncDriver;
use SParallel\Objects\Context;
use SParallelLaravel\Commands\HandleSerializedClosureCommand;

class DriverFactory
{
    public function __construct(
        protected Application $app,
        protected Context $context,
        protected EventsBusInterface $eventsBus,
    ) {
    }

    /**
     * @param class-string<DriverInterface> $driverClass
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function get(string $driverClass): DriverInterface
    {
        return match ($driverClass) {
            SyncDriver::class => new SyncDriver(
                context: $this->context,
                eventsBus: $this->eventsBus
            ),
            ForkDriver::class => new ForkDriver(
                context: $this->context,
                eventsBus: $this->eventsBus
            ),
            ProcessDriver::class => new ProcessDriver(
                scriptPath: sprintf(
                    '%s %s',
                    base_path('artisan'),
                    $this->app->get(HandleSerializedClosureCommand::class)->getName()
                ),
                context: $this->context,
            ),
            default => throw new RuntimeException(
                message: sprintf(
                    'Driver %s not found',
                    $driverClass
                )
            ),
        };
    }
}
