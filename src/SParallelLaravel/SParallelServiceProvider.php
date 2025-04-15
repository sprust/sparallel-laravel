<?php

declare(strict_types=1);

namespace SParallelLaravel;

use Illuminate\Support\ServiceProvider;
use SParallel\Drivers\Factory;
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
        if ($this->app->runningInConsole()) {
            $this->commands([
                HandleSerializedClosureCommand::class,
            ]);
        }

        $runningInConsole = app()->runningInConsole();

        $this->app->singleton(
            SyncDriver::class,
            static function (): SyncDriver {
                return new SyncDriver(
                    beforeTask: static fn() => event(new SParallelTaskStartingEvent(Constants::DRIVER_SYNC)),
                    afterTask: static fn() => event(new SParallelTaskFinishedEvent(Constants::DRIVER_SYNC)),
                    failedTask: static fn(Throwable $exception) => event(
                        new SParallelTaskFailedEvent(Constants::DRIVER_SYNC, $exception)
                    ),
                );
            }
        );

        $this->app->singleton(
            ProcessDriver::class,
            static function (): ProcessDriver {
                return new ProcessDriver(
                    scriptPath: base_path('artisan') . ' ' . app(HandleSerializedClosureCommand::class)->getName(),
                );
            }
        );

        if ($runningInConsole) {
            $this->app->singleton(
                ForkDriver::class,
                static function (): ForkDriver {
                    return new ForkDriver(
                        beforeTask: static fn() => event(new SParallelTaskStartingEvent(Constants::DRIVER_FORK)),
                        afterTask: static fn() => event(new SParallelTaskFinishedEvent(Constants::DRIVER_FORK)),
                        failedTask: static fn(Throwable $exception) => event(
                            new SParallelTaskFailedEvent(Constants::DRIVER_FORK, $exception)
                        ),
                    );
                }
            );
        }

        $this->app->singleton(
            ParallelService::class,
            static fn(): ParallelService => new ParallelService(
                driver: (new Factory(
                    container: app(),
                    isRunningInConsole: $runningInConsole
                ))->detect(),
            )
        );
    }
}
