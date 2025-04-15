<?php

declare(strict_types=1);

namespace SParallelLaravel;

class Constants
{
    public const CONTEXT_DRIVER_KEY = 'driver';

    public const DRIVER_SYNC    = 'sync';
    public const DRIVER_PROCESS = 'process';
    public const DRIVER_FORK    = 'fork';
}
