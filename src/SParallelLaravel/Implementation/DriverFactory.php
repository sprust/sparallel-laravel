<?php

declare(strict_types=1);

namespace SParallelLaravel\Implementation;

use SParallel\Contracts\DriverFactoryInterface;
use SParallel\Contracts\DriverInterface;
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

        $driverClass = config("sparallel.drivers.$mode");

        if ($driverClass) {
            $driver = app($driverClass);
        } else {
            $driver = app(SyncDriver::class);

            logger()->warning("Unknown sparallel mode: $mode. Using 'sync' mode.");
        }

        return $this->driver = $driver;
    }
}
