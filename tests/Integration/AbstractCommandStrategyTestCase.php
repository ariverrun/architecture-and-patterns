<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Command\CommandInterface;
use App\CommandQueue\CommandQueueInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

abstract class AbstractCommandStrategyTestCase extends TestCase
{
    final public function testStrategy(): void
    {
        $logger = $this->createMock(LoggerInterface::class);

        $queue = $this->createMock(CommandQueueInterface::class);

        $queue->commands = [];

        $enqueuedCommands = $dequeuedCommands = [];

        $queue->method('enqueue')
            ->willReturnCallback(function (CommandInterface $command) use ($queue, &$enqueuedCommands): void {
                $queue->commands[] = $command;
                $enqueuedCommands[] = $command;
            });

        $queue->method('dequeue')
            ->willReturnCallback(function () use ($queue, &$dequeuedCommands): ?CommandInterface {
                $command = array_shift($queue->commands);

                if (null !== $command) {
                    $dequeuedCommands[] = $command;
                }

                return $command;
            });

        $strategyClass = $this->getStrategyClass();

        (new $strategyClass($queue, $logger))();

        $expectedCommandClasses = $this->getExpectedCommandClasses();

        $this->assertEquals(count($expectedCommandClasses), count($enqueuedCommands));
        $this->assertEquals(count($expectedCommandClasses), count($dequeuedCommands));

        foreach ($expectedCommandClasses as $i => $expectedCommandClass) {
            $this->assertInstanceOf($expectedCommandClass, $enqueuedCommands[$i]);
            $this->assertInstanceOf($expectedCommandClass, $dequeuedCommands[$i]);
        }
    }

    abstract protected function getStrategyClass(): string;

    /**
     * @return string[]
     */
    abstract protected function getExpectedCommandClasses(): array;
}
