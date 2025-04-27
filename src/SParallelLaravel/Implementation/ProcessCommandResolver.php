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
        return sprintf(
            'cd %s && %s %s %s',
            base_path(),
            (new PhpExecutableFinder())->find(false),
            artisan_binary(),
            app(HandleProcessTaskCommand::class)->getName()
        );
    }
}
