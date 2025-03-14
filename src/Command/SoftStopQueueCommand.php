<?php

declare(strict_types=1);

namespace App\Command;

use App\CommandQueue\CommandQueueCoroutine;
use App\CommandQueue\State\RunningUntilEmptyQueueState;
use App\DependencyInjection\IoC;

class SoftStopQueueCommand implements CommandInterface
{
    public function __construct(
        private readonly CommandQueueCoroutine $commandQueueCoroutine,
    ) {
    }

    public function execute(): void
    {
        $this->commandQueueCoroutine->updateState(
            new RunningUntilEmptyQueueState(
                $this->commandQueueCoroutine->getExceptionHandler(),
                IoC::resolve('Logger'),
            )
        );
    }
}
