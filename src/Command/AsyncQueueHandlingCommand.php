<?php

declare(strict_types=1);

namespace App\Command;

use App\CommandQueue\CommandQueueCoroutine;

class AsyncQueueHandlingCommand implements CommandInterface
{
    public function __construct(
        private readonly CommandQueueCoroutine $commandQueueCoroutine,
    ) {
    }

    public function execute(): void
    {
        ($this->commandQueueCoroutine)();
    }
}
