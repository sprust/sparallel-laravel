<?php

declare(strict_types=1);

namespace SParallelLaravel\Implementation;

use SParallel\Contracts\ProcessScriptPathResolverInterface;
use SParallelLaravel\Commands\HandleProcessTaskCommand;

class ProcessScriptPathResolver implements ProcessScriptPathResolverInterface
{
    public function get(): string
    {
        return base_path('artisan') . ' ' . app(HandleProcessTaskCommand::class)->getName();
    }
}
