<?php

declare(strict_types=1);

namespace App\CommandQueue;

use App\Async\Async;
use App\CommandExceptionHandler\CommandExceptionHandlerInterface;
use App\CommandQueue\State\CommandQueueStateInterface;

class CommandQueueCoroutine
{
    private const SLEEP_SECONDS_ON_WAITING = 0.1;
    private const MAX_CYCLES_IN_A_ROW = 10;

    private int $cyclesCounter = 0;
    private CommandQueueCoroutineStatus $status;

    public function __construct(
        private readonly string $coroutineId,
        private readonly CommandQueueInterface $queue,
        private StatefulCommandQueueHandlerStrategyInterface $handlerStrategy,
        private readonly CommandExceptionHandlerInterface $exceptionHandler,
    ) {
        $this->status = CommandQueueCoroutineStatus::NEW;
    }

    public function __invoke(): void
    {
        $this->status = CommandQueueCoroutineStatus::RUNNING;

        (new Async(
            function (): void {

                loopQueue:

                    while (true) {

                        $nextState = $this->handlerStrategy->doHandle($this->queue);

                        if (null === $nextState) {
                            $this->status = CommandQueueCoroutineStatus::COMPLETED;

                            return;
                        }

                        ++$this->cyclesCounter;

                        if ($this->cyclesCounter >= self::MAX_CYCLES_IN_A_ROW) {
                            $this->cyclesCounter = 0;
                            break;
                        }
                    }

                Async::sleep(self::SLEEP_SECONDS_ON_WAITING);

                goto loopQueue;
            }
        ))();
    }

    public function updateState(?CommandQueueStateInterface $state): void
    {
        $this->handlerStrategy->setState($state);
    }

    public function overrideHandlerStrategy(StatefulCommandQueueHandlerStrategyInterface $strategy): void
    {
        $this->handlerStrategy = $strategy;
    }

    public function getExceptionHandler(): CommandExceptionHandlerInterface
    {
        return $this->exceptionHandler;
    }

    public function getStatus(): CommandQueueCoroutineStatus
    {
        return $this->status;
    }
}
