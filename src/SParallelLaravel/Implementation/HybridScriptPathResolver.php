<?php

declare(strict_types=1);

namespace SParallelLaravel\Implementation;

use SParallel\Contracts\HybridScriptPathResolverInterface;
use SParallelLaravel\Commands\HandleHybridProcessTaskCommand;

class HybridScriptPathResolver implements HybridScriptPathResolverInterface
{
    public function get(): string
    {
        return base_path('artisan') . ' ' . app(HandleHybridProcessTaskCommand::class)->getName();
    }
}
