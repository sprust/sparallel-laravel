# Parallel PHP via processes for Laravel

```bash
php artisan vendor:publish --tag=sparallel-laravel
```

```dotenv
# sparallel
SPARALLEL_ASYNC=true
SPARALLEL_USE_FORK_INSIDE_PROCESS=true
```

```php
try {
    /** @var \SParallel\Objects\ResultsObject $results */
    $results = app(\SParallel\Services\SParallelService::class)->wait(
        callbacks: [
            'first'  => static fn() => 'first',
            'second' => static fn() => 'second',
        ],
        waitMicroseconds: 2_000_000, // 2 seconds
    );
} catch (\SParallel\Exceptions\SParallelTimeoutException) {
    throw new RuntimeException('Timeout');
}

if ($results->hasFailed()) {
    foreach ($results->getFailed() as $key => $failedResult) {
        echo sprintf(
            'Failed task: %s\n%s\n',
            $key, $failedResult->error?->message ?? 'unknown error'
        );
    }
}

foreach ($results->getResults() as $result) {
    if ($failedResult->error) {
        continue;
    }

    echo $result->result . "\n";
}
```
