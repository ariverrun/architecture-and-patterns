<?php

declare(strict_types=1);

namespace App\Command;

use App\CommandQueue\CommandQueueInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class DelayLoggingExceptionCommand implements CommandInterface
{
    public function __construct(
        private readonly Throwable $exception,
        private readonly CommandQueueInterface $commandQueue,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function execute(): void
    {
        $this->commandQueue->enqueue(
            new LoggingExceptionCommand($this->exception, $this->logger)
        );
    }
}
