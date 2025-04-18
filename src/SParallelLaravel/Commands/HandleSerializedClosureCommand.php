<?php

declare(strict_types=1);

namespace SParallelLaravel\Commands;

use Illuminate\Console\Command;
use RuntimeException;
use SParallel\Contracts\EventsBusInterface;
use SParallel\Drivers\Process\ProcessDriver;
use SParallel\Transport\CallbackTransport;
use SParallel\Transport\ContextTransport;
use SParallel\Transport\ResultTransport;
use Throwable;

class HandleSerializedClosureCommand extends Command
{
    protected $signature = 'sparallel:handle-serialized-closure';

    protected $description = 'Handle serialized closure';

    public function handle(
        EventsBusInterface $eventsBus,
        ContextTransport $contextTransport,
        ResultTransport $resultTransport,
        CallbackTransport $callbackTransport
    ): void {
        $context = $contextTransport->unserialize(
            $_SERVER[ProcessDriver::SERIALIZED_CONTEXT_VARIABLE_NAME] ?? null
        );

        $driverName = ProcessDriver::DRIVER_NAME;

        $eventsBus->taskStarting(
            driverName: $driverName,
            context: $context
        );

        if (!array_key_exists(ProcessDriver::SERIALIZED_CLOSURE_VARIABLE_NAME, $_SERVER)) {
            $exception = new RuntimeException(
                message: 'No closure found in $_SERVER variable.'
            );

            $eventsBus->taskFailed(
                driverName: $driverName,
                context: $context,
                exception: $exception
            );

            fwrite(STDERR, $resultTransport->serialize(exception: $exception));
        } else {
            try {
                $closure = $callbackTransport->unserialize(
                    $_SERVER[ProcessDriver::SERIALIZED_CLOSURE_VARIABLE_NAME]
                );

                fwrite(STDOUT, $resultTransport->serialize(result: $closure()));
            } catch (Throwable $exception) {
                fwrite(STDERR, $resultTransport->serialize(exception: $exception));

                $eventsBus->taskFailed(
                    driverName: $driverName,
                    context: $context,
                    exception: $exception
                );
            }
        }

        $eventsBus->taskFinished(
            driverName: $driverName,
            context: $context
        );
    }
}
