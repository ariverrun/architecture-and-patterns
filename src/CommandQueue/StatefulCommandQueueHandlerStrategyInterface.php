<?php

declare(strict_types=1);

namespace App\CommandQueue;

use App\CommandQueue\State\CommandQueueStateInterface;

interface StatefulCommandQueueHandlerStrategyInterface
{
    public function doHandle(CommandQueueInterface $queue): ?CommandQueueStateInterface;
    public function setState(?CommandQueueStateInterface $state): void;
}
