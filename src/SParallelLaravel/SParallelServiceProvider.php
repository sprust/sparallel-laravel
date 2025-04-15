<?php

declare(strict_types=1);

namespace SParallelLaravel;

use Illuminate\Support\ServiceProvider;
use SParallel\Contracts\DriverInterface;
use SParallel\Drivers\Fork\ForkDriver;
use SParallel\Drivers\Process\ProcessDriver;
use SParallel\Drivers\Sync\SyncDriver;
use SParallel\Services\ParallelService;
use SParallelLaravel\Commands\HandleSerializedClosureCommand;
use SParallelLaravel\Events\SParallelTaskFailedEvent;
use SParallelLaravel\Events\SParallelTaskFinishedEvent;
use SParallelLaravel\Events\SParallelTaskStartingEvent;
use Throwable;

class SParallelServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([
            HandleSerializedClosureCommand::class,
        ]);

        $runningInConsole = $this->app->runningInConsole();

        $this->app->singleton(
            ParallelService::class,
            static fn(): ParallelService => new ParallelService(
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

    private function detectDriver(bool $runningInConsole): DriverInterface
    {
        if (!config('sparallel.async')) {
            return new SyncDriver(
                beforeTask: static fn() => event(new SParallelTaskStartingEvent(Constants::DRIVER_SYNC)),
                afterTask: static fn() => event(new SParallelTaskFinishedEvent(Constants::DRIVER_SYNC)),
                failedTask: static fn(Throwable $exception) => event(
                    new SParallelTaskFailedEvent(Constants::DRIVER_SYNC, $exception)
                ),
            );
        }

        if ($runningInConsole) {
            return new ForkDriver(
                beforeTask: static fn() => event(new SParallelTaskStartingEvent(Constants::DRIVER_FORK)),
                afterTask: static fn() => event(new SParallelTaskFinishedEvent(Constants::DRIVER_FORK)),
                failedTask: static fn(Throwable $exception) => event(
                    new SParallelTaskFailedEvent(Constants::DRIVER_FORK, $exception)
                ),
            );
        }

        return new ProcessDriver(
            scriptPath: base_path('artisan') . ' ' . app(HandleSerializedClosureCommand::class)->getName(),
        );
    }
}
