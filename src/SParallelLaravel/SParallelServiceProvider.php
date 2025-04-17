<?php

declare(strict_types=1);

namespace SParallelLaravel;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use SParallel\Contracts\DriverInterface;
use SParallel\Contracts\EventsBusInterface;
use SParallel\Contracts\SerializerInterface;
use SParallel\Drivers\Fork\ForkDriver;
use SParallel\Drivers\Process\ProcessDriver;
use SParallel\Drivers\Sync\SyncDriver;
use SParallel\Objects\Context;
use SParallel\Services\SParallelService;
use SParallel\Transport\CallbackTransport;
use SParallel\Transport\ContextTransport;
use SParallel\Transport\ResultTransport;
use SParallelLaravel\Commands\HandleSerializedClosureCommand;
use SParallelLaravel\Drivers\ProcessWithForkInside\ProcessWithForkInsideDriver;

class SParallelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Context::class);
        $this->app->singleton(EventsBusInterface::class, EventsBus::class);
        $this->app->singleton(SerializerInterface::class, Serializer::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function boot(): void
    {
        $this->registerTransports();
        $this->registerDrivers();

        $runningInConsole = $this->app->runningInConsole();

        $this->app->singleton(
            SParallelService::class,
            fn(): SParallelService => new SParallelService(
                driver: $this->detectDriver($runningInConsole),
                eventsBus: $this->app->get(EventsBusInterface::class),
            )
        );

        $events = $this->app->get(Dispatcher::class);

        foreach (config('sparallel.listeners', []) as $eventClass => $listenerClasses) {
            foreach ($listenerClasses as $listenerClass) {
                $events->listen($eventClass, $listenerClass);
            }
        }

        if ($runningInConsole) {
            $this->commands([
                HandleSerializedClosureCommand::class,
            ]);

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

    private function registerTransports(): void
    {
        $this->app->singleton(CallbackTransport::class);
        $this->app->singleton(ResultTransport::class);
        $this->app->singleton(ContextTransport::class);
    }

    private function registerDrivers(): void
    {
        $this->app->singleton(SyncDriver::class);
        $this->app->singleton(ForkDriver::class);
        $this->app->singleton(
            ProcessDriver::class,
            static fn(): ProcessDriver => new ProcessDriver(
                callbackTransport: app(CallbackTransport::class),
                resultTransport: app(ResultTransport::class),
                contextTransport: app(ContextTransport::class),
                scriptPath: sprintf(
                    '%s %s',
                    base_path('artisan'),
                    app(HandleSerializedClosureCommand::class)->getName()
                ),
                context: app(Context::class)
            )
        );
        $this->app->singleton(ProcessWithForkInsideDriver::class);
    }

    private function detectDriver(bool $runningInConsole): DriverInterface
    {
        if (!config('sparallel.async')) {
            return app(SyncDriver::class);
        }

        if ($runningInConsole) {
            return app(ForkDriver::class);
        }

        if (config('sparallel.use_fork_inside_process')) {
            return app(ProcessWithForkInsideDriver::class);
        }

        return app(ProcessDriver::class);
    }
}
