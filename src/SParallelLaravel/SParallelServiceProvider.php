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
        $context = $this->app->get(Context::class);

        if (!config('sparallel.async')) {
            $context->add(Constants::CONTEXT_DRIVER_KEY, Constants::DRIVER_SYNC);

            return new SyncDriver(
                context: $context,
            );
        }

        if ($runningInConsole) {
            $context->add(Constants::CONTEXT_DRIVER_KEY, Constants::DRIVER_FORK);

            return new ForkDriver(
                context: $context,
            );
        }

        $context->add(Constants::CONTEXT_DRIVER_KEY, Constants::DRIVER_PROCESS);

        return new ProcessDriver(
            scriptPath: sprintf(
                '%s %s',
                base_path('artisan'),
                app(HandleSerializedClosureCommand::class)->getName()
            ),
            context: $context,
        );
    }
}
