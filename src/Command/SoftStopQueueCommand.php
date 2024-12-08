<?php

declare(strict_types=1);

namespace App\Command;

use App\CommandQueue\CommandQueueCoroutine;
use App\CommandQueue\SoftStopQueueStrategy;

class SoftStopQueueCommand implements CommandInterface
{
    public function __construct(
        private readonly CommandQueueCoroutine $commandQueueCoroutine,
    ) {
    }

    public function execute(): void
    {
        $this->commandQueueCoroutine->overrideHandlerStrategy(
            new SoftStopQueueStrategy(
                $this->commandQueueCoroutine->getExceptionHandler()
            ),
        );

        $this->commandQueueCoroutine->gracefullyStop();
    }
}
