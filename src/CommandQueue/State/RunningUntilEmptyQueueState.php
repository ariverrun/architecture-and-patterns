<?php

declare(strict_types=1);

namespace App\CommandQueue\State;

use App\CommandExceptionHandler\CommandExceptionHandlerInterface;
use App\CommandQueue\CommandQueueInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class RunningUntilEmptyQueueState implements CommandQueueStateInterface
{
    public function __construct(
        private readonly CommandExceptionHandlerInterface $exceptionHandler,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(CommandQueueInterface $queue): bool
    {
        $command = $queue->dequeue();

        if (null !== $command) {

            $this->logger->info('Run command from queue', ['command' => $command::class, 'queueId' => $queue->getId()]);

            try {

                $command->execute();

                return true;

            } catch (Throwable $e) {

                $this->logger->error('Failed command from queue', ['command' => $command::class, 'queueId' => $queue->getId(), 'exception' => $e]);

                $this->exceptionHandler->handle($command, $e)->execute();
            }
        }

        return false;
    }
}
