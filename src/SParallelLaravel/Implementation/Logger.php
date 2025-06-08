<?php

declare(strict_types=1);

namespace SParallelLaravel\Implementation;

use Psr\Log\LoggerInterface;
use SParallel\Contracts\SParallelLoggerInterface;

readonly class Logger implements SParallelLoggerInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function emergency(\Stringable|string $message, array $context = []): void
    {
        $this->logger->log(__FUNCTION__, $message, $context);;
    }

    public function alert(\Stringable|string $message, array $context = []): void
    {
        $this->logger->log(__FUNCTION__, $message, $context);;
    }

    public function critical(\Stringable|string $message, array $context = []): void
    {
        $this->logger->log(__FUNCTION__, $message, $context);;
    }

    public function error(\Stringable|string $message, array $context = []): void
    {
        $this->logger->log(__FUNCTION__, $message, $context);;
    }

    public function warning(\Stringable|string $message, array $context = []): void
    {
        $this->logger->log(__FUNCTION__, $message, $context);;
    }

    public function notice(\Stringable|string $message, array $context = []): void
    {
        $this->logger->log(__FUNCTION__, $message, $context);;
    }

    public function info(\Stringable|string $message, array $context = []): void
    {
        $this->logger->log(__FUNCTION__, $message, $context);;
    }

    public function debug(\Stringable|string $message, array $context = []): void
    {
        $this->logger->log(__FUNCTION__, $message, $context);;
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }
}
