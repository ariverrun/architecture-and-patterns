<?php

declare(strict_types=1);

namespace App\Command;

use App\Exception\CommandException;
use Webmozart\Assert\Assert;
use Throwable;

class MacroCommand implements CommandInterface
{
    /**
     * @param CommandInterface[] $commands
     */
    public function __construct(
        protected readonly array $commands,
    ) {
        Assert::allIsInstanceOf($commands, CommandInterface::class);
    }

    final public function execute(): void
    {
        foreach ($this->commands as $command) {
            try {
                $command->execute();
            } catch (Throwable $e) {
                throw new CommandException('Command execution failed');
            }
        }
    }
}
