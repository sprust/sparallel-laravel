<?php

declare(strict_types=1);

namespace SParallelLaravel\Workers\Repositories;

use Redis;
use RuntimeException;
use SParallelLaravel\Workers\Worker;

class RedisWorkersRepository implements WorkersRepositoryInterface
{
    protected int $version = 1;

    protected string $cacheKeyPrefix = 'sparallel:workers:';

    protected int $redisPrefixLength;

    protected int $uptime;

    public function __construct(protected Redis $redis)
    {
        $this->uptime = $this->getSystemStartTimeSeconds();

        $this->redisPrefixLength = mb_strlen((string) $this->redis->getOption(Redis::OPT_PREFIX));
    }

    public function insert(int $pid): void
    {
        $this->redis->set(
            key: $this->makeCacheKey($pid),
            value: json_encode([
                'v'   => $this->version,
                'pid' => $pid,
                'cat' => time(),
                'sut' => $this->uptime,
            ])
        );
    }

    public function delete(int $pid): void
    {
        $this->redis->del(
            $this->makeCacheKey($pid),
        );
    }

    public function deleteMany(array $pids): void
    {
        $this->redis->del(
            array_map(
                fn(int $pid) => $this->makeCacheKey($pid),
                $pids,
            ),
        );
    }

    public function getAll(): array
    {
        $keys = $this->getKeys();

        $rawWorkers = $this->redis->mget($keys);

        if (!$rawWorkers) {
            return [];
        }

        $workers = [];

        foreach ($rawWorkers as $index => $rawWorker) {
            if (!is_string($rawWorker)) {
                continue;
            }

            $workerData = json_decode($rawWorker, true);

            if (!is_array($workerData)) {
                continue;
            }

            $version = $workerData['v'] ?? null;

            if (!is_numeric($version)) {
                continue;
            }

            $version = (int) $version;

            if ($version !== $this->version) {
                continue;
            }

            $pid       = (int) $workerData['pid'];
            $createdAt = (int) $workerData['cat'];
            $uptime    = (int) $workerData['sut'];

            $isActive             = posix_kill($pid, 0);
            $isFromPreviousUptime = $uptime < $this->uptime;

            $workers[] = new Worker(
                key: $keys[$index],
                pid: $pid,
                createdAt: $createdAt,
                systemUptime: $uptime,
                isActive: $isActive,
                isFromPreviousUptime: $isFromPreviousUptime,
            );
        }

        return $workers;
    }

    public function flush(): void
    {
        $keys = $this->getKeys();

        if (!count($keys)) {
            return;
        }

        $this->redis->unlink($keys);
    }

    /**
     * @return array<string>
     */
    protected function getKeys(): array
    {
        $redisPrefixLength = $this->redisPrefixLength;

        return array_map(
            static fn(string $key) => substr($key, $redisPrefixLength),
            $this->redis->keys("$this->cacheKeyPrefix*"),
        );
    }

    protected function makeCacheKey(int $pid): string
    {
        return $this->cacheKeyPrefix . $pid;
    }

    protected function getSystemStartTimeSeconds(): int
    {
        $uptime = file_get_contents('/proc/uptime');

        if ($uptime === false) {
            throw new RuntimeException('Failed to read /proc/uptime');
        }

        return time() - ((int) explode(' ', $uptime)[0]);
    }
}
