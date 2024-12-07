<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Command\ExceptionThrowingCommand;
use App\Command\RetryCommand;
use App\Command\LoggingExceptionCommand;
use App\CommandStrategy\RetryOnExceptionThenLogExceptionStrategy;

class RetryOnExceptionThenLogExceptionStrategyTest extends AbstractCommandStrategyTestCase
{
    protected function getStrategyClass(): string
    {
        return RetryOnExceptionThenLogExceptionStrategy::class;
    }

    protected function getExpectedCommandClasses(): array
    {
        return [
            ExceptionThrowingCommand::class,
            RetryCommand::class,
            LoggingExceptionCommand::class,
        ];
    }
}
