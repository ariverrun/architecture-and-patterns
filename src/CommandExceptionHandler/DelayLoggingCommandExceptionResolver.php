<?php

declare(strict_types=1);

namespace App\CommandExceptionHandler;

use App\Command\CommandInterface;
use App\Command\DelayLoggingExceptionCommand;
use App\CommandQueue\CommandQueueInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class DelayLoggingCommandExceptionResolver implements CommandExceptionResolverInterface
{
    public function __construct(
        private readonly CommandQueueInterface $commandQueue,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function resolve(CommandInterface $command, Throwable $exception): CommandInterface
    {
        return new DelayLoggingExceptionCommand($exception, $this->commandQueue, $this->logger);
    }
}
