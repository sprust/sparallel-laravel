# Parallel PHP via processes for Laravel

## APP

```dotenv
# sparallel
## one of: sync, server 
SPARALLEL_MODE=sync
SPARALLEL_SERVER_HOST=localhost
SPARALLEL_SERVER_PORT=18077
SPARALLEL_LOG_CHANNEL=null
SPARALLEL_SERVER_BIN_PATH=sparallel-server
```

```bash
php artisan vendor:publish --tag=sparallel-laravel
```

## SERVER

### load the bin file
```bash
php artisan sparallel:server:load
```

### .env.sparallel
```dotenv
# PID
SERVER_PID_FILE_PATH=storage/sparallel/sparallel-pid

# RPC
RPC_PORT=18077

# logging
LOG_DIR=storage/sparallel/logs
# any,debug,info,warn,error
LOG_LEVELS=any
LOG_KEEP_DAYS=3

SERVE_PROXY=false
SERVE_WORKERS=true

WORKER_COMMAND="php -d memory_limit=512M vendor/bin/sparallel-worker-e104f"
MIN_WORKERS_NUMBER=5
MAX_WORKERS_NUMBER=10
WORKERS_NUMBER_SCALE_UP=5
WORKERS_NUMBER_PERCENT_SCALE_UP=80
WORKERS_NUMBER_PERCENT_SCALE_DOWN=50
```

### .gitignore
```gitignore
.env.sparallel
storage/sparallel/*
sparallel-server
```

### start server
```bash
sparallel-server --env=.env.sparallel start
```

## example ##

Init
```php
$workers = app(\SParallel\SParallelWorkers::class);

$callbacks = [
    'first'  => static fn() => 'first',
    'second' => static fn() => throw new RuntimeException('second'),
    'third'  => static function(
        \SParallel\Entities\Context $context,
        \Illuminate\Contracts\Events\Dispatcher $dispatcher // DI support
    ) {
        $context->check();
        
        return 'third';
    },
];
```

Wait all tasks to finish and get results
```php
/** 
 * @var \SParallel\SParallelWorkers $workers 
 * @var array<string, Closure> $callbacks 
 */

$results = $workers->wait(
    callbacks: $callbacks,
    timeoutSeconds: 2,
);

if ($results->hasFailed()) {
    foreach ($results->getFailed() as $key => $failedResult) {
        echo "$taskKey: ERROR: " . ($failedResult->error?->message ?: 'unknown error') . "\n";
    }
}

foreach ($results->getResults() as $result) {
    if ($result->error) {
        continue;
    }

    echo "$taskKey: SUCCESS: " . $result->result . "\n";
}
```

Run tasks and get results at any task completion
```php
/** 
 * @var \SParallel\SParallelWorkers $workers 
 * @var array<string, Closure> $callbacks 
 */

$results = $workers->run(
    callbacks: $callbacks,
    timeoutSeconds: 2,
);

foreach ($results as $taskKey => $result) {
    if ($result->error) {
        echo "$taskKey: ERROR: " . ($result->error->message ?: 'unknown error') . "\n";
        
        continue;
    }

    echo "$taskKey: SUCCESS: " . $result->result . "\n";
}
```

