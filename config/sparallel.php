<?php

return [
    'async'                   => (bool) env('SPARALLEL_ASYNC', true),
    'use_fork_inside_process' => (bool) env('SPARALLEL_USE_FORK_INSIDE_PROCESS', true),
];
