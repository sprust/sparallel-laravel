<?php

declare(strict_types=1);

namespace SParallelLaravel;

use Illuminate\Support\ServiceProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use SParallel\Contracts\DriverInterface;
use SParallel\Contracts\TaskEventsBusInterface;
use SParallel\Drivers\Fork\ForkDriver;
use SParallel\Drivers\Process\ProcessDriver;
use SParallel\Drivers\Sync\SyncDriver;
use SParallel\Objects\Context;
use SParallel\Services\ParallelService;
use SParallelLaravel\Commands\HandleSerializedClosureCommand;

class SParallelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Context::class);
        $this->app->singleton(TaskEventsBusInterface::class, TaskEventsBus::class);
        $this->app->singleton(DriverFactory::class);
    }

    public function boot(): void
    {
        $this->commands([
            HandleSerializedClosureCommand::class,
        ]);

        $runningInConsole = $this->app->runningInConsole();

        $this->app->singleton(
            ParallelService::class,
            fn(): ParallelService => new ParallelService(
                driver: $this->detectDriver($runningInConsole),
            )
        );

        if ($runningInConsole) {
            $this->publishes(
                paths: [
                    __DIR__ . '/../../config/sparallel.php' => config_path('sparallel.php'),
                ],
                groups: [
                    'sparallel-laravel',
                ]
            );
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function detectDriver(bool $runningInConsole): DriverInterface
    {
        $factory = $this->app->get(DriverFactory::class);

        if (!config('sparallel.async')) {
            return $factory->get(SyncDriver::class);
        }

        if ($runningInConsole) {
            return $factory->get(ForkDriver::class);
        }

        return $factory->get(ProcessDriver::class);
    }
}
