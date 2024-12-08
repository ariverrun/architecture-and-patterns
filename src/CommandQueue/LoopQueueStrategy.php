<?php

declare(strict_types=1);

namespace App\CommandQueue;

use App\CommandExceptionHandler\CommandExceptionHandlerInterface;
use Throwable;

class LoopQueueStrategy implements CommandQueueHandlerStrategyInterface
{
    public function __construct(
        private readonly CommandExceptionHandlerInterface $exceptionHandler,
    ) {
    }

    public function doHandle(CommandQueueInterface $queue): bool
    {
        $command = $queue->dequeue();

        if (null !== $command) {
            try {

                $command->execute();

            } catch (Throwable $e) {
                $this->exceptionHandler->handle($command, $e)->execute();
            }
        }

        return true;
    }
}
