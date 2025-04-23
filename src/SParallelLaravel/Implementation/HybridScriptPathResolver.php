<?php

namespace SParallelLaravel\Implementation;

use SParallel\Contracts\HybridScriptPathResolverInterface;
use SParallelLaravel\Commands\HandleHybridProcessTaskCommand;

class HybridScriptPathResolver implements HybridScriptPathResolverInterface
{
    public function get(): string
    {
        return app(HandleHybridProcessTaskCommand::class)->getName();
    }
}
