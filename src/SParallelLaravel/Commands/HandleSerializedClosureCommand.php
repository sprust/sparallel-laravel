<?php

declare(strict_types=1);

namespace SParallelLaravel\Commands;

use Illuminate\Console\Command;
use RuntimeException;
use SParallel\Drivers\Process\ProcessDriver;
use SParallel\Transport\Serializer;
use SParallel\Transport\TaskResultTransport;
use SParallelLaravel\Constants;
use SParallelLaravel\Events\SParallelTaskFailedEvent;
use SParallelLaravel\Events\SParallelTaskFinishedEvent;
use SParallelLaravel\Events\SParallelTaskStartingEvent;
use Throwable;

class HandleSerializedClosureCommand extends Command
{
    protected $signature = 'sparallel:handle-serialized-closure';

    protected $description = 'Handle serialized closure';

    public function handle(): void
    {
        event(new SParallelTaskStartingEvent(Constants::DRIVER_PROCESS));

        if (!array_key_exists(ProcessDriver::VARIABLE_NAME, $_SERVER)) {
            $exception = new RuntimeException(
                message: 'No closure found in $_SERVER variable.'
            );

            fwrite(STDERR, TaskResultTransport::serialize(exception: $exception));

            event(new SParallelTaskFailedEvent(Constants::DRIVER_PROCESS, $exception));
        } else {
            try {
                $closure = Serializer::unSerialize(
                    $_SERVER[ProcessDriver::VARIABLE_NAME]
                );

                fwrite(STDOUT, TaskResultTransport::serialize(result: $closure()));
            } catch (Throwable $exception) {
                fwrite(STDERR, TaskResultTransport::serialize(exception: $exception));

                event(new SParallelTaskFailedEvent(Constants::DRIVER_PROCESS, $exception));
            }
        }

        event(new SParallelTaskFinishedEvent(Constants::DRIVER_PROCESS));
    }
}
