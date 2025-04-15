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
        $currentResults = ($current->getResults()[0] ?? null)?->result;

        if ($currentResults instanceof ResultsObject) {
            return $currentResults;
        }

        $failedResults = new ResultsObject();

        $failedResults->addResult(
            key: 'error',
            result: new ResultObject(
                exception: new RuntimeException(
                    "Expected ResultsObject, got: " . (is_null($currentResults) ? 'null' : gettype($currentResults))
                )
            )
        );

        return $failedResults;
    }

    public function break(): void
    {
        $this->waitGroup->break();
    }
}
