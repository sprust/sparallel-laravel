<?php

declare(strict_types=1);

namespace SParallelLaravel\Workers\Repositories;

use SParallelLaravel\Workers\Worker;

interface WorkersRepositoryInterface
{
    public function insert(int $pid): void;

    public function delete(int $pid): void;

    /**
     * @param array<int> $pids
     */
    public function deleteMany(array $pids): void;

    /**
     * @return array<Worker>
     */
    public function getAll(): array;

    public function flush(): void;
}
