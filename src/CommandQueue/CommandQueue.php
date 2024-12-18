<?php

declare(strict_types=1);

namespace App\CommandQueue;

use App\Command\CommandInterface;

class CommandQueue implements CommandQueueInterface
{
    /**
     * @var CommandInterface[]
     */
    private array $commands = [];
    public function __construct(
        private readonly string $qeueuId,
    ) {
    }

    public function dequeue(): ?CommandInterface
    {
        return array_shift($this->commands);
    }

    public function enqueue(CommandInterface $command): void
    {
        $this->commands[] = $command;
    }

    public function getId(): string
    {
        return $this->qeueuId;
    }
}
