<?php

declare(strict_types=1);

namespace SParallelLaravel\Drivers\ProcessWithForkInside;

use Closure;
use RuntimeException;
use SParallel\Contracts\WaitGroupInterface;
use SParallel\Objects\ResultObject;
use SParallel\Objects\ResultsObject;

class ProcessWithForkInsideWaitGroup implements WaitGroupInterface
{
    /**
     * @param array<mixed, Closure> $callbacks
     */
    public function __construct(
        protected WaitGroupInterface $waitGroup,
        protected array $callbacks
    ) {
    }

    public function current(): ResultsObject
    {
        $current = $this->waitGroup->current();

        if (!$current->count()) {
            return $current;
        }

        if ($current->hasFailed()) {
            return $current;
        }

        /** @var array<ResultObject>|mixed $currentResults */
        $currentResults = $current->getResults()[0] ?? null;

        $results = new ResultsObject();

        if (!is_array($currentResults)) {
            $results->addResult(
                key: 'error',
                result: new ResultObject(
                    exception: new RuntimeException(
                        "Expected array, got: " . gettype($currentResults)
                    )
                )
            );

            return $results;
        }

        $invalidTypeResults = array_filter(
            $currentResults,
            static fn($result) => !($result instanceof ResultObject)
        );

        if (count($invalidTypeResults)) {
            $results->addResult(
                key: 'error',
                result: new ResultObject(
                    exception: new RuntimeException(
                        "Expected array of ResultObject, got: " . gettype($invalidTypeResults[0])
                    )
                )
            );

            return $results;
        }

        foreach ($currentResults as $key => $result) {
            $results->addResult($key, $result);
        }

        return $results;
    }

    public function break(): void
    {
        $this->waitGroup->break();
    }
}
