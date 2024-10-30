<?php

declare(strict_types=1);

namespace App\CommandQueue;

use App\CommandExceptionHandler\CommandExceptionHandlerInterface;
use Throwable;

class CommandQueueHandler implements CommandQueueHandlerInterface
{
    public function __construct(
        private readonly CommandExceptionHandlerInterface $commandExceptionHandler,
    ) {
    }

    public function handle(CommandQueueInterface $queue): void
    {
        $queueIsNotEmpty = null;

        while (false !== $queueIsNotEmpty) {

            $command = $queue->dequeue();

            $queueIsNotEmpty = null !== $command ? true : false;

            if (null !== $command) {
                try {

                    $command->execute();

                } catch (Throwable $e) {
                    $this->commandExceptionHandler->handle($command, $e)->execute();
                }
            }
        }
    }
}
