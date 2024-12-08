<?php

declare(strict_types=1);

namespace App\CommandQueue;

use App\CommandExceptionHandler\CommandExceptionHandlerInterface;
use Throwable;

class SoftStopQueueStrategy implements CommandQueueHandlerStrategyInterface
{
    public function __construct(
        private readonly CommandExceptionHandlerInterface $exceptionHandler,
    ) {
    }

    public function doHandle(CommandQueueInterface $queue): bool
    {
        $command = $queue->dequeue();

        $queueIsNotEmpty = null !== $command ? true : false;

        if (null !== $command) {
            try {

                $command->execute();

            } catch (Throwable $e) {
                $this->exceptionHandler->handle($command, $e)->execute();
            }
        }

        return $queueIsNotEmpty;
    }
}
