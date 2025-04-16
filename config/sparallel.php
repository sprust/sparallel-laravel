<?php

use SParallelLaravel\Events\SParallelTaskStartingEvent;
use SParallelLaravel\Events\SParallelTaskFailedEvent;
use SParallelLaravel\Listeners\LogSParallelTaskFailedListener;
use SParallelLaravel\Events\SParallelTaskFinishedEvent;

return [
    /**
     * false - Use sync driver
     * true - Use async driver via Fork or/and Process
     */
    'async'                   => (bool) env('SPARALLEL_ASYNC', true),

    /**
     * false - Use Process driver if running not in console
     * true - Use many forks inside one process instead many processes
     */
    'use_fork_inside_process' => (bool) env('SPARALLEL_USE_FORK_INSIDE_PROCESS', true),

    /**
     * key - event class
     * value - listeners classes
     */
    'listeners'               => [
        SParallelTaskStartingEvent::class => [],
        SParallelTaskFailedEvent::class   => [
            LogSParallelTaskFailedListener::class,
        ],
        SParallelTaskFinishedEvent::class => [],
    ],
];
