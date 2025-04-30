# Parallel PHP via processes for Laravel

```bash
php artisan vendor:publish --tag=sparallel-laravel
```

```dotenv
# sparallel
SPARALLEL_ASYNC=true
SPARALLEL_USE_HYBRID_ALWAYS=true
SPARALLEL_USE_FORK_INSIDE_PROCESS=true
```

## example ##

Init
```php
$service = app(\SParallel\Services\SParallelService::class);

$callbacks = [
    'first'  => static fn() => 'first',
    'second' => static fn() => throw new RuntimeException('second'),
    'third'  => static function(
        \SParallel\Services\Canceler $canceler,
        \Illuminate\Contracts\Events\Dispatcher $dispatcher // DI support
    ) {
        $canceler->check();
        
        return 'third';
    },
];
```

Wait all tasks to finish and get results
```php
/** 
 * @var \SParallel\Services\SParallelService $service 
 * @var array<string, Closure> $callbacks 
 */

$results = $service->wait(
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
 * @var \SParallel\Services\SParallelService $service 
 * @var array<string, Closure> $callbacks 
 */

$results = $service->run(
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

