<?php

declare(strict_types=1);

namespace SParallelLaravel\Implementation;

use SParallel\Contracts\HybridProcessCommandResolverInterface;
use SParallelLaravel\Commands\HandleHybridProcessTaskCommand;

use Symfony\Component\Process\PhpExecutableFinder;

use function Illuminate\Support\artisan_binary;

class HybridProcessCommandResolver implements HybridProcessCommandResolverInterface
{
    public function get(): string
    {
        $memoryLimitMb = (int) config('sparallel.task_memory_limit_mb');

        if ($memoryLimitMb <= 0) {
            $memoryLimitMb = 128;
        }

        return sprintf(
            'cd %s && %s -d memory_limit=%dM %s %s',
            base_path(),
            (new PhpExecutableFinder())->find(false),
            $memoryLimitMb,
            artisan_binary(),
            app(HandleHybridProcessTaskCommand::class)->getName()
        );
    }
}
