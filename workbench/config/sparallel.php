<?php

use SParallelLaravel\Events\FlowFailedEvent;
use SParallelLaravel\Events\FlowFinishedEvent;
use SParallelLaravel\Events\FlowStartingEvent;
use SParallelLaravel\Events\ServerGoneEvent;
use SParallelLaravel\Events\TaskFailedEvent;
use SParallelLaravel\Events\TaskFinishedEvent;
use SParallelLaravel\Events\TaskStartingEvent;
use SParallelLaravel\Listeners\ReportFlowFailedListener;
use SParallelLaravel\Listeners\ReportTaskFailedListener;
use SParallelLaravel\Listeners\ReportServerGoneListener;

return [
    /**
     * sync - Use Sync driver
     * server - Use Server driver
     */
    'mode' => env('SPARALLEL_MODE', 'sync'),

    'server'    => [
        'host'     => env('SPARALLEL_SERVER_HOST', '127.0.0.1'),
        'port'     => (int) env('SPARALLEL_SERVER_PORT', 9000),
        'bin-path' => env('SPARALLEL_SERVER_BIN_PATH'),
    ],

    /**
     * key - event class
     * value - listener classes
     */
    'listeners' => [
        FlowStartingEvent::class => [],
        FlowFailedEvent::class   => [
            ReportFlowFailedListener::class,
        ],
        FlowFinishedEvent::class => [],
        TaskStartingEvent::class => [],
        TaskFailedEvent::class   => [
            ReportTaskFailedListener::class,
        ],
        TaskFinishedEvent::class => [],
        ServerGoneEvent::class   => [
            ReportServerGoneListener::class,
        ],
    ],
];
