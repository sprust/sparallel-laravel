<?php

declare(strict_types=1);

namespace SParallelLaravel\Commands;

use Illuminate\Console\Command;
use SParallelLaravel\Workers\Repositories\WorkersRepositoryInterface;
use SParallelLaravel\Workers\Worker;

class MonitorWorkersCommand extends Command
{
    protected $signature = 'sparallel:monitor-workers {repository?}';

    protected $description = 'Monitor processes';

    public function handle(): void
    {
        $repositoryType = $this->argument('repository');

        if ($repositoryType) {
            config(['sparallel.workers_repository' => $repositoryType]);
        }

        $repository = app(WorkersRepositoryInterface::class);

        /** @phpstan-ignore-next-line while.alwaysTrue */
        while (true) {
            $table = array_map(
                static fn(Worker $process) => [
                    'key'                  => $process->key,
                    'pid'                  => $process->pid,
                    'createdAt'            => $process->createdAt,
                    'systemUptime'         => $process->systemUptime,
                    'isActive'             => $process->isActive ? '+' : '',
                    'isFromPreviousUptime' => $process->isFromPreviousUptime ? '+' : '',
                ],
                $repository->getAll()
            );

            usort($table, static fn(array $a, array $b) => $a['pid'] <=> $b['pid']);

            system('clear');

            $this->warn('repository: ' . $repository::class);

            $this->table(
                headers: [
                    'key',
                    'pid',
                    'createdAt',
                    'systemUptime',
                    'isActive',
                    'isFromPreviousUptime',
                ],
                rows: $table
            );

            sleep(1);
        }
    }
}
