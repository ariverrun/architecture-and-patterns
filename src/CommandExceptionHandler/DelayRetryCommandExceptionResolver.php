<?php

declare(strict_types=1);

namespace App\CommandExceptionHandler;

use App\Command\CommandInterface;
use App\Command\DelayRetryCommand;
use App\CommandQueue\CommandQueueInterface;
use Throwable;

class DelayRetryCommandExceptionResolver implements CommandExceptionResolverInterface
{
    public function __construct(
        private readonly CommandQueueInterface $commandQueue,
    ) {
    }

    public function resolve(CommandInterface $command, Throwable $exception): CommandInterface
    {
        return new DelayRetryCommand($command, $this->commandQueue);
    }
}
