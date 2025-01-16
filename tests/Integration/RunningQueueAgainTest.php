<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Async\Runtime;
use App\Command\CommandInterface;
use App\Command\RunCommand;
use App\CommandQueue\CommandQueue;
use App\CommandQueue\CommandQueueInterface;
use App\CommandQueue\CommandQueueCoroutine;
use App\CommandQueue\StatefulQueueStrategy;
use App\CommandQueue\State\CommandQueueStateInterface;
use App\CommandExceptionHandler\CommandExceptionHandlerInterface;
use App\DependencyInjection\IoC;
use Tests\Traits\IocSetupTrait;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RunningQueueAgainTest extends TestCase
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

            $queue = new CommandQueue('1');

            $exceptionHandler = $this->createMock(CommandExceptionHandlerInterface::class);

            $handlerStrategy = new StatefulQueueStrategy($exceptionHandler);

            $coroutine = new CommandQueueCoroutine('1', $queue, $handlerStrategy, $exceptionHandler);

            $skippingCommandsState = $this->createMock(CommandQueueStateInterface::class);

            $skippingCommandsState->expects($this->any())
                                ->method('handle')
                                ->willReturnCallback(function (CommandQueueInterface $queue): bool {
                                    $command = $queue->dequeue();

                                    if (null === $command) {
                                        return false;
                                    }

                                    if (RunCommand::class === $command::class) {
                                        $command->execute();
                                    }

                                    return true;
                                });

            $coroutine->updateState($skippingCommandsState);

            for ($i = 0; $i < 5; ++$i) {
                $command = $this->createMock(CommandInterface::class);
                $command->expects($this->never())
                        ->method('execute');
                $queue->enqueue($command);
            }

            $queue->enqueue(new RunCommand($coroutine));

            for ($i = 0; $i < 5; ++$i) {
                $command = $this->createMock(CommandInterface::class);
                $command->expects($this->once())
                        ->method('execute');
                $queue->enqueue($command);
            }

            $stoppingCommand = $this->createMock(CommandInterface::class);
            $stoppingCommand->expects($this->once())
                    ->method('execute')
                    ->willReturnCallback(function () use ($coroutine) {
                        $coroutine->updateState(null);
                    });

            $queue->enqueue($stoppingCommand);

            ($coroutine)();
        }))();
    }
}
