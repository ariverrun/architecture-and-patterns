<?php

declare(strict_types=1);

namespace App\CommandQueue\State;

use App\CommandQueue\CommandQueueInterface;

class MoveToQueueState implements CommandQueueStateInterface
{
    public function __construct(
        private readonly CommandQueueInterface $acceptingQueue,
    ) {
    }

    public function handle(CommandQueueInterface $queue): bool
    {
        $command = $queue->dequeue();

        if (null !== $command) {
            $this->acceptingQueue->enqueue($command);

            return true;
        }

        return false;
    }
}
