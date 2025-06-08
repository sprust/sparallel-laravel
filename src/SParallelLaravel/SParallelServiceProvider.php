<?php

declare(strict_types=1);

namespace SParallelLaravel;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use SParallel\Contracts\CallbackCallerInterface;
use SParallel\Contracts\DriverFactoryInterface;
use SParallel\Contracts\EventsBusInterface;
use SParallel\Contracts\RpcClientInterface;
use SParallel\Contracts\SerializerInterface;
use SParallel\Contracts\SParallelLoggerInterface;
use SParallel\Implementation\CallbackCaller;
use SParallel\Implementation\RpcClient;
use SParallel\Transport\CallbackTransport;
use SParallel\Transport\ContextTransport;
use SParallel\Transport\ServerTaskTransport;
use SParallel\Transport\TaskResultTransport;
use SParallelLaravel\Commands\BenchmarkCommand;
use SParallelLaravel\Commands\LoadServerBinCommand;
use SParallelLaravel\Commands\ReloadServerWorkersCommand;
use SParallelLaravel\Commands\ShowServerStatsCommand;
use SParallelLaravel\Commands\StopServerCommand;
use SParallelLaravel\Implementation\DriverFactory;
use SParallelLaravel\Implementation\EventsBus;
use SParallelLaravel\Implementation\Logger;
use SParallelLaravel\Implementation\Serializer;
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
        $this->app->singleton(DriverFactoryInterface::class, DriverFactory::class);

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
                BenchmarkCommand::class,
            ]);

            $this->publishes(
                paths: [
                    __DIR__ . '/../../workbench/config/sparallel.php' => config_path('sparallel.php'),
                    __DIR__ . '/../../workbench/config/.env.sparallel' => base_path('.env.sparallel.example'),
                ],
                groups: [
                    'sparallel-laravel',
                ]
            );
        }
    }
}
