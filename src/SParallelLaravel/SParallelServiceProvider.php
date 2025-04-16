<?php

declare(strict_types=1);

namespace SParallelLaravel;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use SParallel\Contracts\DriverInterface;
use SParallel\Contracts\TaskEventsBusInterface;
use SParallel\Drivers\Fork\ForkDriver;
use SParallel\Drivers\Process\ProcessDriver;
use SParallel\Drivers\Sync\SyncDriver;
use SParallel\Objects\Context;
use SParallel\Services\SParallelService;
use SParallelLaravel\Commands\HandleSerializedClosureCommand;
use SParallelLaravel\Drivers\DriverFactory;
use SParallelLaravel\Drivers\ProcessWithForkInside\ProcessWithForkInsideDriver;

class SParallelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Context::class);
        $this->app->singleton(TaskEventsBusInterface::class, TaskEventsBus::class);
        $this->app->singleton(DriverFactory::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function boot(): void
    {
        $this->commands([
            HandleSerializedClosureCommand::class,
        ]);

        $runningInConsole = $this->app->runningInConsole();

        $this->app->singleton(
            SParallelService::class,
            fn(): SParallelService => new SParallelService(
                driver: $this->detectDriver($runningInConsole),
            )
        );

        $events = $this->app->get(Dispatcher::class);

        foreach (config('sparallel.listeners', []) as $eventClass => $listenerClasses) {
            foreach ($listenerClasses as $listenerClass) {
                $events->listen($eventClass, $listenerClass);
            }
        }

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

        if (config('sparallel.use_fork_inside_process')) {
            return $this->app->get(ProcessWithForkInsideDriver::class);
        }

        return $factory->get(ProcessDriver::class);
    }
}
