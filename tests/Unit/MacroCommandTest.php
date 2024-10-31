<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Command\CommandInterface;
use App\Command\MacroCommand;
use App\Exception\CommandException;
use PHPUnit\Framework\TestCase;

class MacroCommandTest extends TestCase
{
    public function testSuccessfulExecution(): void
    {
        $commands = [];

        foreach (range(0, 2) as $i) {
            $command = $this->createMock(CommandInterface::class);

            $command->expects($this->once())
                    ->method('execute');

            $commands[] = $command;
        }

        (new MacroCommand($commands))->execute();
    }

    public function testOneSubscommandFailed(): void
    {
        $commands = [];

        $failingCommandIndex = 2;

        foreach (range(0, 4) as $i) {
            $command = $this->createMock(CommandInterface::class);

            if ($failingCommandIndex === $i) {
                $command->expects($this->once())
                    ->method('execute')
                    ->will(
                        $this->throwException(new CommandException())
                    );
            } elseif ($failingCommandIndex > $i) {
                $command->expects($this->once())
                    ->method('execute');
            } else {
                $command->expects($this->never())
                    ->method('execute');
            }

            $commands[] = $command;
        }

        $this->expectException(CommandException::class);

        (new MacroCommand($commands))->execute();
    }
}
