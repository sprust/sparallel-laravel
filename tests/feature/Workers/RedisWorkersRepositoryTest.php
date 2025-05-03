<?php

declare(strict_types=1);

namespace SParallelLaravel\Tests\Workers;

use Illuminate\Contracts\Container\BindingResolutionException;
use SParallelLaravel\Tests\BaseTestCase;
use SParallelLaravel\Workers\Repositories\WorkersRepositoryInterface;

class RedisWorkersRepositoryTest extends BaseTestCase
{
    private WorkersRepositoryInterface $repository;

    /**
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('sparallel.workers_repository', 'redis');

        $this->repository = $this->app->make(WorkersRepositoryInterface::class);

        $this->repository->flush();
    }

    public function test(): void
    {
        $this->repository->insert(mt_rand(777777777, 7777777777));
        $this->repository->insert(mt_rand(7777777778, 8777777778));
        $this->repository->insert(mt_rand(8777777779, 9777777779));

        $workers = $this->repository->getAll();

        self::assertCount(
            3,
            $workers
        );

        $worker1 = $workers[0];
        $worker2 = $workers[1];

        foreach ($workers as $index => $worker) {
            self::assertFalse(
                $worker->isActive,
                "Worker [$index] should not be active"
            );

            self::assertFalse(
                $worker->isFromPreviousUptime,
                "Worker [$index] should not be from previous uptime"
            );
        }

        $this->repository->delete($worker1->pid);

        self::assertCount(
            2,
            $this->repository->getAll()
        );

        $this->repository->deleteMany([$worker2->pid]);

        self::assertCount(
            1,
            $this->repository->getAll()
        );

        $this->repository->flush();

        self::assertCount(
            0,
            $this->repository->getAll()
        );
    }
}
