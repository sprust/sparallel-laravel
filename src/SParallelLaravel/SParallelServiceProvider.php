<?php

declare(strict_types=1);

namespace SParallelLaravel;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use SParallel\Contracts\CallbackCallerInterface;
use SParallel\Contracts\DriverInterface;
use SParallel\Contracts\EventsBusInterface;
use SParallel\Contracts\RpcClientInterface;
use SParallel\Contracts\SerializerInterface;
use SParallel\Contracts\SParallelLoggerInterface;
use SParallel\Drivers\Server\ServerDriver;
use SParallel\Drivers\Sync\SyncDriver;
use SParallel\Implementation\CallbackCaller;
use SParallel\Implementation\RpcClient;
use SParallel\Transport\CallbackTransport;
use SParallel\Transport\ContextTransport;
use SParallel\Transport\ServerTaskTransport;
use SParallel\Transport\TaskResultTransport;
use SParallelLaravel\Commands\LoadServerBinCommand;
use SParallelLaravel\Commands\ReloadServerWorkersCommand;
use SParallelLaravel\Commands\ShowServerStatsCommand;
use SParallelLaravel\Commands\StopServerCommand;
use SParallelLaravel\Implementation\EventsBus;
use SParallelLaravel\Implementation\Serializer;
use SParallelLaravel\Implementation\Logger;
use Spiral\Goridge\Relay;
use Spiral\Goridge\RPC\RPC;

class SParallelServiceProvider extends ServiceProvider
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function boot(): void
    {
        // transports
        $this->app->singleton(CallbackTransport::class);
        $this->app->singleton(ContextTransport::class);
        $this->app->singleton(ServerTaskTransport::class);
        $this->app->singleton(TaskResultTransport::class);

        // implementations
        $this->app->singleton(EventsBusInterface::class, EventsBus::class);
        $this->app->singleton(SerializerInterface::class, Serializer::class);
        $this->app->singleton(CallbackCallerInterface::class, CallbackCaller::class);
        $this->app->singleton(
            RpcClientInterface::class,
            static function () {
                $host = config('sparallel.server.host');
                $port = config('sparallel.server.port');

                return new RpcClient(
                    new RPC(Relay::create("tcp://$host:$port"))
                );
            }
        );
        $this->app->singleton(SParallelLoggerInterface::class, Logger::class);

        // drivers
        $this->app->singleton(
            DriverInterface::class,
            static function (): DriverInterface {
                // TODO: factory

                $mode = strtolower(config('sparallel.mode', 'sync'));

                if ($mode === 'sync') {
                    return app(SyncDriver::class);
                }

                if ($mode === 'server') {
                    return app(ServerDriver::class);
                }

                logger()->warning("Unknown sparallel mode: $mode. Using 'sync' mode.");

                return app(SyncDriver::class);
            }
        );

        $events = $this->app->get(Dispatcher::class);

        foreach (config('sparallel.listeners', []) as $eventClass => $listenerClasses) {
            foreach ($listenerClasses as $listenerClass) {
                $events->listen($eventClass, $listenerClass);
            }
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                LoadServerBinCommand::class,
                ShowServerStatsCommand::class,
                ReloadServerWorkersCommand::class,
                StopServerCommand::class,
            ]);

            $this->publishes(
                paths: [
                    __DIR__ . '/../../workbench/config/sparallel.php' => config_path('sparallel.php'),
                ],
                groups: [
                    'sparallel-laravel',
                ]
            );
        }
    }
}
