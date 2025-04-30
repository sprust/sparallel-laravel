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
use SParallel\Contracts\HybridProcessCommandResolverInterface;
use SParallel\Contracts\ProcessCommandResolverInterface;
use SParallel\Contracts\SerializerInterface;
use SParallel\Drivers\Fork\ForkDriver;
use SParallel\Drivers\Hybrid\HybridDriver;
use SParallel\Drivers\Process\ProcessDriver;
use SParallel\Drivers\Sync\SyncDriver;
use SParallel\Services\Callback\CallbackCaller;
use SParallel\Services\Fork\ForkHandler;
use SParallel\Services\Fork\ForkService;
use SParallel\Services\Process\ProcessService;
use SParallel\Services\Socket\SocketService;
use SParallel\Services\SParallelService;
use SParallel\Transport\CallbackTransport;
use SParallel\Transport\ContextTransport;
use SParallel\Transport\ProcessMessagesTransport;
use SParallel\Transport\ResultTransport;
use SParallelLaravel\Commands\HandleHybridProcessTaskCommand;
use SParallelLaravel\Commands\HandleProcessTaskCommand;
use SParallelLaravel\Implementation\EventsBus;
use SParallelLaravel\Implementation\HybridProcessCommandResolver;
use SParallelLaravel\Implementation\ProcessCommandResolver;
use SParallelLaravel\Implementation\Serializer;

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
        $this->app->singleton(ResultTransport::class);
        $this->app->singleton(ContextTransport::class);
        $this->app->singleton(ProcessMessagesTransport::class);

        // implementations
        $this->app->singleton(EventsBusInterface::class, EventsBus::class);
        $this->app->singleton(SerializerInterface::class, Serializer::class);
        $this->app->singleton(CallbackCallerInterface::class, CallbackCaller::class);
        $this->app->singleton(ProcessCommandResolverInterface::class, ProcessCommandResolver::class);
        $this->app->singleton(HybridProcessCommandResolverInterface::class, HybridProcessCommandResolver::class);

        // services
        $this->app->singleton(ForkHandler::class);
        $this->app->singleton(ForkService::class);
        $this->app->singleton(ProcessService::class);
        $this->app->singleton(SocketService::class);
        $this->app->singleton(SParallelService::class);

        // drivers
        $this->app->singleton(
            DriverInterface::class,
            static function (): DriverInterface {
                if (!config('sparallel.async')) {
                    return app(SyncDriver::class);
                }

                if (app()->runningInConsole()) {
                    return app(ForkDriver::class);
                }

                if (config('sparallel.use_fork_inside_process')) {
                    return app(HybridDriver::class);
                }

                return app(ProcessDriver::class);
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
                HandleProcessTaskCommand::class,
                HandleHybridProcessTaskCommand::class,
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
}
