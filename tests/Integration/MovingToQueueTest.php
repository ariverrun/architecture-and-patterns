<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Async\Async;
use App\Async\Runtime;
use App\Command\CommandInterface;
use App\Command\MoveToCommand;
use App\CommandQueue\CommandQueue;
use App\CommandQueue\CommandQueueCoroutine;
use App\CommandQueue\CommandQueueCoroutineStatus;
use App\CommandQueue\StatefulQueueStrategy;
use App\CommandExceptionHandler\CommandExceptionHandlerInterface;
use App\DependencyInjection\IoC;
use Tests\Traits\IocSetupTrait;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class MovingToQueueTest extends TestCase
{
    use IocSetupTrait;

    public function setUp(): void
    {
        $this->setUpIocDependencyResolver();
    }

    public function tearDown(): void
    {
        $this->setUpIocDependencyResolver();
    }

    public function testMovingCommandsToOtherQueue(): void
    {
        $logger = $this->createMock(LoggerInterface::class);

        IoC::resolve('Ioc.Register', 'Logger', static function (?string $loggerKey = null) use ($logger): LoggerInterface {
            return $logger;
        })();

        (new Runtime(function (): void {

            $queueA = new CommandQueue('1');

            $exceptionHandler = $this->createMock(CommandExceptionHandlerInterface::class);

            $handlerStrategy = new StatefulQueueStrategy($exceptionHandler);

            $coroutine = new CommandQueueCoroutine('1', $queueA, $handlerStrategy, $exceptionHandler);

            for ($i = 0; $i < 5; ++$i) {
                $command = $this->createMock(CommandInterface::class);
                $command->expects($this->once())
                        ->method('execute');
                $queueA->enqueue($command);
            }

            $queueB = new CommandQueue('2');

            $queueA->enqueue(new MoveToCommand($coroutine, $queueB));

            $commandsAfterMoveTo = [];

            for ($i = 0; $i < 5; ++$i) {
                $command = $this->createMock(CommandInterface::class);
                $command->expects($this->never())
                        ->method('execute');
                $queueA->enqueue($command);
                $commandsAfterMoveTo[] = $command;
            }

            ($coroutine)();

            (new Async(function () use ($coroutine, $commandsAfterMoveTo, $queueB): void {
                while (CommandQueueCoroutineStatus::COMPLETED !== $coroutine->getStatus()) {
                    Async::sleep(0.001);
                }

                $i = 0;

                while ($command = $queueB->dequeue()) {

                    $this->assertEqualsCanonicalizing($commandsAfterMoveTo[$i], $command);

                    ++$i;
                }

                $this->assertEquals(count($commandsAfterMoveTo), $i);
            }))();

        }))();
    }
}
