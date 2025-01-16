<?php

declare(strict_types=1);

namespace App\CommandQueue\State;

use App\CommandQueue\CommandQueueInterface;

interface CommandQueueStateInterface
{
    public function handle(CommandQueueInterface $queue): bool;
}
