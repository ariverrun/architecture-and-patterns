<?php

declare(strict_types=1);

namespace App\CommandQueue;

class HardStopQueueStrategy implements CommandQueueHandlerStrategyInterface
{
    public function doHandle(CommandQueueInterface $queue): bool
    {
        return false;
    }
}
