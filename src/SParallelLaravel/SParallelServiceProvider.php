<?php

declare(strict_types=1);

namespace SParallelLaravel;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\ServiceProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use SParallel\Contracts\CallbackCallerInterface;
use SParallel\Contracts\DriverInterface;
use SParallel\Contracts\EventsBusInterface;
use SParallel\Contracts\ForkStarterInterface;
use SParallel\Contracts\HybridProcessCommandResolverInterface;
use SParallel\Contracts\ProcessCommandResolverInterface;
use SParallel\Contracts\SerializerInterface;
use SParallel\Drivers\Fork\ForkDriver;
use SParallel\Drivers\Hybrid\HybridDriver;
use SParallel\Drivers\Process\ProcessDriver;
use SParallel\Drivers\Sync\SyncDriver;
use SParallel\Services\Callback\CallbackCaller;
use SParallel\Services\Fork\Forker;
use SParallel\Services\Fork\ForkHandler;
use SParallel\Services\Fork\ForkService;
use SParallel\Services\Process\ProcessService;
use SParallel\Services\Socket\SocketService;
use SParallel\Services\SParallelService;
use SParallel\Transport\CallbackTransport;
use SParallel\Transport\ContextTransport;
use SParallel\Transport\ProcessMessagesTransport;
use SParallel\Transport\ResultTransport;
use SParallelLaravel\Commands\MonitorWorkersCommand;
use SParallelLaravel\Implementation\EventsBus;
use SParallelLaravel\Implementation\ForkStarter;
use SParallelLaravel\Implementation\HybridProcessCommandResolver;
use SParallelLaravel\Implementation\ProcessCommandResolver;
use SParallelLaravel\Implementation\Serializer;
use SParallelLaravel\Workers\Repositories\RedisWorkersRepository;
use SParallelLaravel\Workers\Repositories\StubWorkersRepository;
use SParallelLaravel\Workers\Repositories\WorkersRepositoryInterface;
use SParallelLaravel\Workers\WorkerCommandFactory;

class SParallelServiceProvider extends ServiceProvider
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function boot(): void
    {
        // workers
        $this->app->singleton(
            WorkerCommandFactory::class,
            static fn(): WorkerCommandFactory => new WorkerCommandFactory(
                memoryLimitMb: (int) config('sparallel.task_memory_limit_mb')
            )
        );
        $this->app->singleton(
            WorkersRepositoryInterface::class,
            static function () {
                // TODO: factory

                $repositoryType = strtolower(config('sparallel.workers_repository', 'none'));

                if ($repositoryType === 'redis') {
                    return new RedisWorkersRepository(
                        redis: Redis::connection()->client(),
                    );
                }

                return new StubWorkersRepository();
            }
        );

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
        $this->app->singleton(ForkStarterInterface::class, ForkStarter::class);

        // services
        $this->app->singleton(ForkHandler::class);
        $this->app->singleton(ForkService::class);
        $this->app->singleton(Forker::class);
        $this->app->singleton(ProcessService::class);
        $this->app->singleton(SocketService::class);
        $this->app->singleton(SParallelService::class);

        // drivers
        $this->app->singleton(
            DriverInterface::class,
            static function (): DriverInterface {
                // TODO: factory

                $mode = strtolower(config('sparallel.mode', 'sync'));

                if ($mode === 'sync') {
                    return app(SyncDriver::class);
                }

                if ($mode === 'process') {
                    return app(ProcessDriver::class);
                }

                if ($mode === 'hybrid') {
                    return app(HybridDriver::class);
                }

                if ($mode === 'process_fork') {
                    return app()->runningInConsole()
                        ? app(ForkDriver::class)
                        : app(ProcessDriver::class);
                }

                if ($mode === 'hybrid_fork') {
                    return app()->runningInConsole()
                        ? app(ForkDriver::class)
                        : app(HybridDriver::class);
                }

                logger()->warning("Unknown sparallel mode: $mode. Use 'sync' mode.");

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
                MonitorWorkersCommand::class,
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
