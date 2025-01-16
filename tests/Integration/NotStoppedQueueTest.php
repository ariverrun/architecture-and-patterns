<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Async\Runtime;
use App\Command\CommandInterface;
use App\CommandQueue\CommandQueue;
use App\CommandQueue\CommandQueueCoroutine;
use App\CommandQueue\StatefulQueueStrategy;
use App\CommandExceptionHandler\CommandExceptionHandlerInterface;
use App\DependencyInjection\IoC;
use Tests\Traits\IocSetupTrait;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class NotStoppedQueueTest extends TestCase
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
    public function testQueueHardStopping(): void
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
                        $coroutine->updateState(null);
                    });

            $queue->enqueue($command);
        }))();
    }
}
