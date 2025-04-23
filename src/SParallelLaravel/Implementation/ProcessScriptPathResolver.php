<?php

namespace SParallelLaravel\Implementation;

use SParallel\Contracts\ProcessScriptPathResolverInterface;
use SParallelLaravel\Commands\HandleProcessTaskCommand;

class ProcessScriptPathResolver implements ProcessScriptPathResolverInterface
{
    public function get(): string
    {
        return app(HandleProcessTaskCommand::class)->getName();
    }
}
