<?php

declare(strict_types=1);

namespace App\CommandQueue;

use App\Command\CommandInterface;

interface CommandQueueInterface
{
    public function dequeue(): ?CommandInterface;

    public function enqueue(CommandInterface $value): void;

    public function getId(): string;
}
