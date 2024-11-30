<?php

declare(strict_types=1);

namespace App\CommandQueue;

interface CommandQueueHandlerInterface
{
    public function handle(CommandQueueInterface $queue): void;
}
