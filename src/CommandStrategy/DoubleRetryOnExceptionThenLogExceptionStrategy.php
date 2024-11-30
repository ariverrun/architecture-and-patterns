<?php

declare(strict_types=1);

namespace App\CommandStrategy;

use App\Command\ExceptionThrowingCommand;
use App\Command\DoubleRetryCommand;
use App\Command\RetryCommand;
use App\CommandExceptionHandler\DelayRetryCommandExceptionResolver;
use App\CommandExceptionHandler\DelayLoggingCommandExceptionResolver;
use App\CommandExceptionHandler\DelayDoubleRetryCommandExceptionResolver;
use RuntimeException;

class DoubleRetryOnExceptionThenLogExceptionStrategy extends AbstractCommandStrategy
{
    protected function enqueueCommands(): void
    {
        $this->commandQueue->enqueue(new ExceptionThrowingCommand());
    }

    protected function registerExceptionResolvers(): void
    {
        $this->commandExceptionHandler->registerResolver(
            ExceptionThrowingCommand::class,
            RuntimeException::class,
            new DelayDoubleRetryCommandExceptionResolver($this->commandQueue)
        );

        $this->commandExceptionHandler->registerResolver(
            DoubleRetryCommand::class,
            RuntimeException::class,
            new DelayRetryCommandExceptionResolver($this->commandQueue)
        );

        $this->commandExceptionHandler->registerResolver(
            RetryCommand::class,
            RuntimeException::class,
            new DelayLoggingCommandExceptionResolver($this->commandQueue, $this->logger)
        );
    }
}