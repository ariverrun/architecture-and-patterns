<?php

declare(strict_types=1);

namespace App\Command;

class DoubleDelayRetryCommand extends DelayRetryCommand
{
    public function execute(): void
    {
        $this->commandQueue->enqueue(
            new DoubleRetryCommand($this->command)
        );
    }    
}
