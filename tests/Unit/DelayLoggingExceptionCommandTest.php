<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Command\DelayLoggingExceptionCommand;
use App\Command\LoggingExceptionCommand;
use App\CommandQueue\CommandQueueInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Exception;
use ReflectionClass;

class DelayLoggingExceptionCommandTest extends TestCase
{
    public function testEnqueueingLoggingCommand(): void
    {
        $commandQueueMock = $this->createMock(CommandQueueInterface::class);

        $loggerMock = $this->createMock(LoggerInterface::class);

        $exceptionMessage = 'Exception thrown in ' . __METHOD__;

        $exception = new Exception($exceptionMessage);

        $commandQueueMock->expects($this->once())
                    ->method('enqueue')
                    ->with(
                        $this->callback(function ($callbackArg) use ($exception): bool {
                            $this->assertInstanceOf(LoggingExceptionCommand::class, $callbackArg);

                            $reflector = new ReflectionClass(LoggingExceptionCommand::class);
                            $exceptionProperty = $reflector->getProperty('exception');

                            $exceptionValue = $exceptionProperty->getValue($callbackArg);
                            $this->assertEqualsCanonicalizing($exception, $exceptionValue);

                            return true;
                        })
                    );

        (new DelayLoggingExceptionCommand($exception, $commandQueueMock, $loggerMock))->execute();
    }
}
