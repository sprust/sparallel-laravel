<?php

declare(strict_types=1);

namespace SParallelLaravel\Implementation;

use SParallel\Contracts\ProcessCommandResolverInterface;
use SParallelLaravel\Commands\HandleProcessTaskCommand;
use Symfony\Component\Process\PhpExecutableFinder;

use function Illuminate\Support\artisan_binary;

class ProcessCommandResolver implements ProcessCommandResolverInterface
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
            app(HandleProcessTaskCommand::class)->getName(),
        );
    }
}
