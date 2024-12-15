<?php

declare(strict_types=1);

namespace App\Command;

use App\CommandQueue\CommandQueueCoroutine;
use App\CommandQueue\HardStopQueueStrategy;

class HardStopQueueCommand implements CommandInterface
{
    public function __construct(
        private readonly CommandQueueCoroutine $commandQueueCoroutine,
    ) {
    }

    public function execute(): void
    {
        $this->commandQueueCoroutine->overrideHandlerStrategy(
            new HardStopQueueStrategy(),
        );

        $this->commandQueueCoroutine->gracefullyStop();
    }
}
