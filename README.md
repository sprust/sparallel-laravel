# Parallel PHP via processes for Laravel

```bash
php artisan vendor:publish --tag=sparallel-laravel
```

```dotenv
# sparallel
SPARALLEL_MODE=sync
SPARALLEL_SERVER_HOST=localhost
SPARALLEL_SERVER_PORT=18077
```

## example ##

Init
```php
$service = app(\SParallel\SParallelWorkers::class);

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
 * @var \SParallel\SParallelWorkers $service 
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
 * @var \SParallel\SParallelWorkers $service 
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

