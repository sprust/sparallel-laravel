<?php

declare(strict_types=1);

namespace SParallelLaravel\Workers;

use RuntimeException;
use Symfony\Component\Process\PhpExecutableFinder;

class WorkerCommandFactory
{
    protected string $binDirectoryPath;
    protected string $commandPrefix;

    public function __construct(int $memoryLimitMb)
    {
        $this->init($memoryLimitMb);
    }

    public function make(string $fileName): string
    {
        $filePath = sprintf(
            '%s/%s',
            $this->binDirectoryPath,
            $fileName,
        );

        if (!file_exists($filePath)) {
            throw new RuntimeException(
                sprintf('File [%s] not found.', $filePath),
            );
        }

        return sprintf(
            '%s %s',
            $this->commandPrefix,
            $filePath,
        );
    }

    protected function init(int $memoryLimitMb): void
    {
        $this->commandPrefix = sprintf(
            '%s -d memory_limit=%dM',
            (new PhpExecutableFinder())->find(false),
            $memoryLimitMb,
        );

        $paths = [
            base_path('vendor/bin'),
            'vendor/bin',
        ];

        foreach ($paths as $path) {
            if (!is_dir($path)) {
                continue;
            }

            $this->binDirectoryPath = $path;

            return;
        }

        throw new RuntimeException(
            'Bin directory path not found.',
        );
    }
}
