<?php

declare(strict_types=1);

namespace App\Command;

use App\CommandQueue\CommandQueueCoroutine;
use App\CommandQueue\CommandQueueInterface;
use App\CommandQueue\State\MoveToQueueState;

class MoveToCommand implements CommandInterface
{
    public function __construct(
        private readonly CommandQueueCoroutine $commandQueueCoroutine,
        private readonly CommandQueueInterface $acceptingQueue,
    ) {
    }

    public function execute(): void
    {
        $this->commandQueueCoroutine->updateState(
            new MoveToQueueState(
                $this->acceptingQueue,
            )
        );
    }
}
