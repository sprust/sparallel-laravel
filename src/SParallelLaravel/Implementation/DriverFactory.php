<?php

declare(strict_types=1);

namespace SParallelLaravel\Implementation;

use SParallel\Contracts\DriverFactoryInterface;
use SParallel\Contracts\DriverInterface;
use SParallel\Drivers\Server\ServerDriver;
use SParallel\Drivers\Sync\SyncDriver;

class DriverFactory implements DriverFactoryInterface
{
    public function __construct(
        protected ?DriverInterface $driver = null,
    ) {
    }

    public function forceDriver(?DriverInterface $driver): void
    {
        $this->driver = $driver;
    }

    public function get(): DriverInterface
    {
        if (!is_null($this->driver)) {
            return $this->driver;
        }

        $mode = strtolower(config('sparallel.mode', 'sync'));

        if ($mode === 'sync') {
            return app(SyncDriver::class);
        }

        if ($mode === 'server') {
            return app(ServerDriver::class);
        }

        logger()->warning("Unknown sparallel mode: $mode. Using 'sync' mode.");

        return app(SyncDriver::class);
    }
}
