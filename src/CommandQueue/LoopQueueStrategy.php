<?php

declare(strict_types=1);

namespace App\CommandQueue;

use App\CommandExceptionHandler\CommandExceptionHandlerInterface;
use App\DependencyInjection\IoC;
use Psr\Log\LoggerInterface;
use Throwable;

class LoopQueueStrategy implements CommandQueueHandlerStrategyInterface
{
    private readonly LoggerInterface $logger;
    public function __construct(
        private readonly CommandExceptionHandlerInterface $exceptionHandler,
    ) {
        $this->logger = IoC::resolve('Logger');
    }

    public function doHandle(CommandQueueInterface $queue): bool
    {
        $command = $queue->dequeue();

        if (null !== $command) {

            $this->logger->info('Run command from queue', ['command' => $command::class, 'queueId' => $queue->getId()]);

            try {

                $command->execute();

            } catch (Throwable $e) {

                $this->logger->error('Failed command from queue', ['command' => $command::class, 'queueId' => $queue->getId(), 'exception' => $e]);

                $this->exceptionHandler->handle($command, $e)->execute();
            }
        }

        return true;
    }
}
