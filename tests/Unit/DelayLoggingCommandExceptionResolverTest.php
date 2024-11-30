<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Command\CommandInterface;
use App\Command\DelayLoggingExceptionCommand;
use App\CommandExceptionHandler\DelayLoggingCommandExceptionResolver;
use App\CommandQueue\CommandQueueInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Exception;

class DelayLoggingCommandExceptionResolverTest extends TestCase
{
    public function testResolving(): void
    {
        $commandQueueMock = $this->createMock(CommandQueueInterface::class);

        $loggerMock = $this->createMock(LoggerInterface::class);

        $resolver = new DelayLoggingCommandExceptionResolver($commandQueueMock, $loggerMock);

        $exceptionMessage = 'Exception thrown in ' . __METHOD__;

        $exception = new Exception($exceptionMessage);

        $commandMock = $this->createMock(CommandInterface::class);

        $resolvedToCommand  = $resolver->resolve($commandMock, $exception);

        $this->assertInstanceOf(DelayLoggingExceptionCommand::class, $resolvedToCommand);
    }
}
