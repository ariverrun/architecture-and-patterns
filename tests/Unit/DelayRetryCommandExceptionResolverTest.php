<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Command\CommandInterface;
use App\Command\DelayRetryCommand;
use App\CommandExceptionHandler\DelayRetryCommandExceptionResolver;
use App\CommandQueue\CommandQueueInterface;
use PHPUnit\Framework\TestCase;
use Exception;

class DelayRetryCommandExceptionResolverTest extends TestCase
{
    public function testResolving(): void
    {
        $commandQueueMock = $this->createMock(CommandQueueInterface::class);

        $resolver = new DelayRetryCommandExceptionResolver($commandQueueMock);

        $exceptionMessage = 'Exception thrown in ' . __METHOD__;

        $exception = new Exception($exceptionMessage);

        $commandMock = $this->createMock(CommandInterface::class);

        $resolvedToCommand  = $resolver->resolve($commandMock, $exception);

        $this->assertInstanceOf(DelayRetryCommand::class, $resolvedToCommand);
    }
}
