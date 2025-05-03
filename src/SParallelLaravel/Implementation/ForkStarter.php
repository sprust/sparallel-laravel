<?php

declare(strict_types=1);

namespace SParallelLaravel\Implementation;

use Closure;
use Illuminate\Contracts\Container\BindingResolutionException;
use SParallel\Contracts\ForkStarterInterface;
use SParallel\Services\Context;
use SParallel\Services\Fork\ForkHandler;
use SParallelLaravel\Application\ApplicationFactory;

readonly class ForkStarter implements ForkStarterInterface
{
    /**
     * @throws BindingResolutionException
     */
    public function start(
        Context $context,
        string $driverName,
        string $socketPath,
        mixed $taskKey,
        Closure $callback
    ): void {
        ApplicationFactory::create()->make(ForkHandler::class)
            ->handle(
                context: $context,
                driverName: $driverName,
                socketPath: $socketPath,
                taskKey: $taskKey,
                callback: $callback
            );
    }
}
