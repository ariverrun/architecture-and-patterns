<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Async\Runtime;
use App\Async\Async;
use App\Command\AsyncQueueHandlingCommand;
use App\CommandQueue\CommandQueue;
use App\CommandQueue\CommandQueueCoroutine;
use App\CommandQueue\CommandQueueHandlerStrategyInterface;
use App\CommandExceptionHandler\CommandExceptionHandlerInterface;
use PHPUnit\Framework\TestCase;

class AsyncQueueHandlingTest extends TestCase
{
    public function testAsyncQueueHandlingByCommand(): void
    {
        (new Runtime(function () {
            $queue = new CommandQueue('1');

            $exceptionHandler = $this->createMock(CommandExceptionHandlerInterface::class);

            $coroutine = new CommandQueueCoroutine('1', $queue, $this->createMock(CommandQueueHandlerStrategyInterface::class), $exceptionHandler);

            $asyncronouslySetFlag = false;


            $handlerStrategy = $this->createMock(CommandQueueHandlerStrategyInterface::class);
            $handlerStrategy->expects($this->once())
                            ->method('doHandle')
                            ->willReturnCallback(function () use ($coroutine, &$asyncronouslySetFlag): bool {
                                $coroutine->gracefullyStop();
                                Async::sleep(0.001);
                                $asyncronouslySetFlag = true;

                                return false;
                            });
            $coroutine->overrideHandlerStrategy($handlerStrategy);

            (new AsyncQueueHandlingCommand($coroutine))->execute();

            $this->assertFalse($asyncronouslySetFlag);

            Async::sleep(0.002);

            $this->assertTrue($asyncronouslySetFlag);
        }))();
    }
}
