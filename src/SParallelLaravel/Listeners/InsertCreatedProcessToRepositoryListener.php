<?php

namespace SParallelLaravel\Listeners;

use SParallelLaravel\Events\ProcessCreatedEvent;
use SParallelLaravel\Workers\Repositories\WorkersRepositoryInterface;

readonly class InsertCreatedProcessToRepositoryListener
{
    public function __construct(
        protected WorkersRepositoryInterface $workersRepository
    ) {
    }

    public function handle(ProcessCreatedEvent $event): void
    {
        $this->workersRepository->insert($event->pid);
    }
}
