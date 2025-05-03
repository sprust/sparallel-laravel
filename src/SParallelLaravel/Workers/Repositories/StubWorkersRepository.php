<?php

declare(strict_types=1);

namespace SParallelLaravel\Workers\Repositories;

class StubWorkersRepository implements WorkersRepositoryInterface
{
    public function insert(int $pid): void
    {
        // STUB
    }

    public function delete(int $pid): void
    {
        // STUB
    }

    public function deleteMany(array $pids): void
    {
        // STUB
    }

    public function getAll(): array
    {
        return [];
    }

    public function flush(): void
    {
        // STUB
    }
}
