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
        return sprintf(
            'cd %s && %s %s %s',
            base_path(),
            (new PhpExecutableFinder())->find(false),
            artisan_binary(),
            app(HandleHybridProcessTaskCommand::class)->getName()
        );
    }
}
