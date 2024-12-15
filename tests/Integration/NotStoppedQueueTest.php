<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Async\Runtime;
use App\Command\CommandInterface;
use App\CommandQueue\CommandQueue;
use App\CommandQueue\CommandQueueCoroutine;
use App\CommandQueue\LoopQueueStrategy;
use App\CommandExceptionHandler\CommandExceptionHandlerInterface;
use PHPUnit\Framework\TestCase;

class NotStoppedQueueTest extends TestCase
{
    public function testQueueHardStopping(): void
    {
        (new Runtime(function (): void {

            $queue = new CommandQueue();

            $exceptionHandler = $this->createMock(CommandExceptionHandlerInterface::class);

            $handlerStrategy = new LoopQueueStrategy($exceptionHandler);

            $coroutine = new CommandQueueCoroutine('1', $queue, $handlerStrategy, $exceptionHandler);

            for ($i = 0; $i < 4; ++$i) {
                $command = $this->createMock(CommandInterface::class);
                $command->expects($this->once())
                        ->method('execute');
                $queue->enqueue($command);
            }

            for ($i = 0; $i < 3; ++$i) {
                $command = $this->createMock(CommandInterface::class);
                $command->expects($this->once())
                        ->method('execute');
                $queue->enqueue($command);
            }

            ($coroutine)();

            for ($i = 0; $i < 2; ++$i) {
                $command = $this->createMock(CommandInterface::class);
                $command->expects($this->once())
                        ->method('execute');
                $queue->enqueue($command);
            }

            $command = $this->createMock(CommandInterface::class);
            $command->expects($this->once())
                    ->method('execute')
                    ->willReturnCallback(function () use ($coroutine) {
                        $coroutine->gracefullyStop();
                    });

            $queue->enqueue($command);
        }))();
    }
}
