<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Command\ExceptionThrowingCommand;
use App\Command\DoubleRetryCommand;
use App\Command\RetryCommand;
use App\Command\LoggingExceptionCommand;
use App\CommandStrategy\DoubleRetryOnExceptionThenLogExceptionStrategy;

class DoubleRetryOnExceptionThenLogExceptionStrategyTest extends AbstractCommandStrategyTestCase
{
    protected function getStrategyClass(): string
    {
        return DoubleRetryOnExceptionThenLogExceptionStrategy::class;
    }

    protected function getExpectedCommandClasses(): array
    {
        return [
            ExceptionThrowingCommand::class,
            DoubleRetryCommand::class,
            RetryCommand::class,
            LoggingExceptionCommand::class,
        ];
    }
}