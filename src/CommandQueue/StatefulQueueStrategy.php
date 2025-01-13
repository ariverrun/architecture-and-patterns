<?php

declare(strict_types=1);

namespace App\CommandQueue;

use App\CommandExceptionHandler\CommandExceptionHandlerInterface;
use App\CommandQueue\State\CommandQueueStateInterface;
use App\CommandQueue\State\RunningQueueState;
use App\DependencyInjection\IoC;
use Psr\Log\LoggerInterface;

class StatefulQueueStrategy implements StatefulCommandQueueHandlerStrategyInterface
{
    private readonly LoggerInterface $logger;
    private ?CommandQueueStateInterface $state;
    public function __construct(
        private readonly CommandExceptionHandlerInterface $exceptionHandler,
    ) {
        $this->logger = IoC::resolve('Logger');
        $this->state = new RunningQueueState(
            $this->exceptionHandler,
            $this->logger,
        );
    }

    public function doHandle(CommandQueueInterface $queue): ?CommandQueueStateInterface
    {
        if (
            null !== $this->state
            && $this->state->handle($queue)
        ) {
            return $this->state;
        }

        return null;
    }

    public function setState(?CommandQueueStateInterface $state): void
    {
        $this->state = $state;
    }
}
