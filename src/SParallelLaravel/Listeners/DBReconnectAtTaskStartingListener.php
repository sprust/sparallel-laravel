<?php

declare(strict_types=1);

namespace SParallelLaravel\Listeners;

use SParallelLaravel\Events\TaskStartingEvent;

readonly class DBReconnectAtTaskStartingListener
{
    public function handle(TaskStartingEvent $event): void
    {
        foreach (app('db')->getConnections() as $connection) {
            $connection->flushQueryLog();
            $connection->forgetRecordModificationState();
            $connection->reconnect();
        }
    }
}
