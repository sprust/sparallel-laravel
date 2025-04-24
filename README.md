# Parallel PHP via processes for Laravel

```bash
php artisan vendor:publish --tag=sparallel-laravel
```

```dotenv
# sparallel
SPARALLEL_ASYNC=true
SPARALLEL_USE_FORK_INSIDE_PROCESS=true
```

Wait all tasks to finish and get results
```php
try {
    $results = app(\SParallel\Services\SParallelService::class)->wait(
        callbacks: [
            'first'  => static fn() => 'first',
            'second' => static fn() => throw new RuntimeException('second'),
        ],
        timeoutSeconds: 2,
    );
} catch (\SParallel\Exceptions\SParallelTimeoutException) {
    throw new RuntimeException('Timeout');
}

if ($results->hasFailed()) {
    foreach ($results->getFailed() as $key => $failedResult) {
        echo "$taskKey: ERROR: " . ($result->error->message ?: 'unknown error') . "\n";
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
try {
    $results = app(\SParallel\Services\SParallelService::class)->run(
        callbacks: [
            'first'  => static fn() => 'first',
            'second' => static fn() => throw new RuntimeException('second'),
        ],
        timeoutSeconds: 2,
    );

    foreach ($results as $taskKey => $result) {
        if ($result->error) {
            echo "$taskKey: ERROR: " . ($result->error->message ?: 'unknown error') . "\n";
            
            continue;
        }
    
        echo "$taskKey: SUCCESS: " . $result->result . "\n";
    }
} catch (\SParallel\Exceptions\SParallelTimeoutException) {
    throw new RuntimeException('Timeout');
}
```
