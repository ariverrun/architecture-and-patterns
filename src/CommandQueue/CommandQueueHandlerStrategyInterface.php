<?php

declare(strict_types=1);

namespace App\CommandQueue;

interface CommandQueueHandlerStrategyInterface
{
    public function doHandle(CommandQueueInterface $queue): bool;
}
