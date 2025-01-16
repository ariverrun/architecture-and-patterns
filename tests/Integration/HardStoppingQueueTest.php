<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Async\Runtime;
use App\Command\CommandInterface;
use App\Command\HardStopQueueCommand;
use App\CommandQueue\CommandQueue;
use App\CommandQueue\CommandQueueCoroutine;
use App\CommandQueue\LoopQueueStrategy;
use App\CommandExceptionHandler\CommandExceptionHandlerInterface;
use App\DependencyInjection\IoC;
use Tests\Traits\IocSetupTrait;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class HardStoppingQueueTest extends TestCase
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

            $handlerStrategy = new LoopQueueStrategy($exceptionHandler);

            $coroutine = new CommandQueueCoroutine('1', $queue, $handlerStrategy, $exceptionHandler);

            for ($i = 0; $i < 5; ++$i) {
                $command = $this->createMock(CommandInterface::class);
                $command->expects($this->once())
                        ->method('execute');
                $queue->enqueue($command);
            }

            $queue->enqueue(new HardStopQueueCommand($coroutine));

            for ($i = 0; $i < 5; ++$i) {
                $command = $this->createMock(CommandInterface::class);
                $command->expects($this->never())
                        ->method('execute');
                $queue->enqueue($command);
            }

            ($coroutine)();
        }))();
    }
}
