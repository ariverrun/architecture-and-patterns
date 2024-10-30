<?php

declare(strict_types=1);

namespace App\Command;

class RetryCommand implements CommandInterface
{
    public function __construct(
        private readonly CommandInterface $command,
    ) {
    }

    public function execute(): void
    {
        $this->command->execute();
    }
}
