<?php

declare(strict_types=1);

namespace SParallelLaravel\Drivers\ProcessWithForkInside;

use Closure;
use SParallel\Contracts\WaitGroupInterface;
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

        $results = new ResultsObject();

        foreach ($current->getResults() as $key => $result) {
            $results->addResult($key, $result);
        }

        return $results;
    }

    public function break(): void
    {
        $this->waitGroup->break();
    }
}
