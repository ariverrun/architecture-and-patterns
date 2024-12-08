<?php

declare(strict_types=1);

namespace App\CommandQueue;

use App\Async\Async;
use App\CommandExceptionHandler\CommandExceptionHandlerInterface;

class CommandQueueCoroutine
{
    private const SLEEP_SECONDS_ON_WAITING = 0.1;
    private const MAX_CYCLES_IN_A_ROW = 10;

    private bool $isGracefullyStopped = false;
    private int $cyclesCounter = 0;

    public function __construct(
        private readonly string $coroutineId,
        private readonly CommandQueueInterface $queue,
        private CommandQueueHandlerStrategyInterface $handlerStrategy,
        private readonly CommandExceptionHandlerInterface $exceptionHandler,
    ) {
    }

    public function __invoke(): void
    {
        (new Async(
            function (): void {

                loopQueue:

                if (!$this->isGracefullyStopped) {
                    while (
                        $this->handlerStrategy->doHandle(
                            $this->queue
                        )
                    ) {
                        ++$this->cyclesCounter;

                        if ($this->cyclesCounter >= self::MAX_CYCLES_IN_A_ROW) {
                            $this->cyclesCounter = 0;
                            break;
                        }
                    }

                    Async::sleep(self::SLEEP_SECONDS_ON_WAITING);

                    goto loopQueue;
                }
            }
        ))();
    }

    public function overrideHandlerStrategy(CommandQueueHandlerStrategyInterface $strategy): void
    {
        $this->handlerStrategy = $strategy;
    }

    public function getExceptionHandler(): CommandExceptionHandlerInterface
    {
        return $this->exceptionHandler;
    }

    public function gracefullyStop(): void
    {
        $this->isGracefullyStopped = true;
    }
}
