<?php

declare(strict_types=1);

namespace SParallelLaravel\Implementation;

use SParallel\Contracts\HybridProcessCommandResolverInterface;
use Symfony\Component\Process\PhpExecutableFinder;

class HybridProcessCommandResolver implements HybridProcessCommandResolverInterface
{
    public function get(): string
    {
        $memoryLimitMb = (int) config('sparallel.task_memory_limit_mb');

        if ($memoryLimitMb <= 0) {
            $memoryLimitMb = 128;
        }

        return sprintf(
            '%s -d memory_limit=%dM %s',
            (new PhpExecutableFinder())->find(false),
            $memoryLimitMb,
            'vendor/bin/sparallel-hybrid-process',
        );
    }
}
