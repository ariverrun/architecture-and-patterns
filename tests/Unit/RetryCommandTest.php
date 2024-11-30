<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Command\CommandInterface;
use App\Command\RetryCommand;
use PHPUnit\Framework\TestCase;

class RetryCommandTest extends TestCase
{
    public function testRetry(): void
    {
        $commandMock = $this->createMock(CommandInterface::class);

        $commandMock->expects($this->once())
                    ->method('execute');

        (new RetryCommand($commandMock))->execute();
    }
}
