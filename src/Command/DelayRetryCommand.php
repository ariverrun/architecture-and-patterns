<?php

declare(strict_types=1);

namespace App\Command;

use App\CommandQueue\CommandQueueInterface;

class DelayRetryCommand implements CommandInterface
{
    public function __construct(
        private readonly CommandInterface $command,
        private readonly CommandQueueInterface $commandQueue,
    ) {
    }

    public function execute(): void
    {
        $this->commandQueue->enqueue(
            new RetryCommand($this->command)
        );
    }
}
