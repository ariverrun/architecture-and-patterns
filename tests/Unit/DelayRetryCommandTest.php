<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Command\CommandInterface;
use App\Command\DelayRetryCommand;
use App\Command\RetryCommand;
use App\CommandQueue\CommandQueueInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class DelayRetryCommandTest extends TestCase
{
    public function testEnqueueingRetriedCommand(): void
    {
        $commandQueueMock = $this->createMock(CommandQueueInterface::class);

        $commandMock = $this->createMock(CommandInterface::class);

        $commandQueueMock->expects($this->once())
                    ->method('enqueue')
                    ->with(
                        $this->callback(function ($callbackArg) use ($commandMock): bool {
                            $this->assertInstanceOf(RetryCommand::class, $callbackArg);

                            $reflector = new ReflectionClass(RetryCommand::class);
                            $exceptionProperty = $reflector->getProperty('command');

                            $commandValue = $exceptionProperty->getValue($callbackArg);

                            $this->assertEqualsCanonicalizing($commandMock, $commandValue);

                            return true;
                        })
                    );

        (new DelayRetryCommand($commandMock, $commandQueueMock))->execute();
    }
}
