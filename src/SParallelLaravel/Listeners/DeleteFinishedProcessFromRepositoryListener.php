<?php

declare(strict_types=1);

namespace SParallelLaravel\Listeners;

use SParallelLaravel\Events\ProcessFinishedEvent;
use SParallelLaravel\Workers\Repositories\WorkersRepositoryInterface;

readonly class DeleteFinishedProcessFromRepositoryListener
{
    public function __construct(
        protected WorkersRepositoryInterface $workersRepository
    ) {
    }

    public function handle(ProcessFinishedEvent $event): void
    {
        $this->workersRepository->delete($event->pid);
    }
}
