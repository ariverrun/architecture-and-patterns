<?php

declare(strict_types=1);

namespace App\CommandQueue;

use App\Command\CommandInterface;
use RuntimeException;

class ChangeableCommandQueue implements ChangeableCommandQueueInterface
{
    /**
     * @var array<int,CommandInterface>
     */
    private array $commands = [];

    private int $nextCommandKey = 0;

    /**
     * @var array<string,int>
     */
    private array $commandQueueKeysByCommandId = [];

    public function __construct(
        private readonly string $qeueuId,
    ) {
    }

    public function dequeue(): ?CommandInterface
    {
        $command = array_shift($this->commands);

        unset($this->commandQueueKeysByCommandId[$this->getCommandIdentifier($command)]);

        if (empty($this->commands)) {
            $this->nextCommandKey = 0;
        }

        return $command;
    }

    public function enqueue(CommandInterface $command): void
    {
        $this->commands[$this->nextCommandKey] = $command;
        $this->commandQueueKeysByCommandId[$this->getCommandIdentifier($command)] = $this->nextCommandKey;
        ++$this->nextCommandKey;
    }

    public function getId(): string
    {
        return $this->qeueuId;
    }

    public function isEnqueued(CommandInterface $command): bool
    {
        return isset($this->commandQueueKeysByCommandId[$this->getCommandIdentifier($command)]);
    }

    public function replace(CommandInterface $replaceWhat, CommandInterface $replaceWith): void
    {
        if (!isset($this->commandQueueKeysByCommandId[$this->getCommandIdentifier($replaceWhat)])) {
            throw new RuntimeException('Can not replace command that is not enqueued');
        }

        $commandKey = $this->commandQueueKeysByCommandId[$this->getCommandIdentifier($replaceWhat)];

        $this->commands[$commandKey] = $replaceWith;
    }

    public function delete(CommandInterface $command): void
    {
        if (!isset($this->commandQueueKeysByCommandId[$this->getCommandIdentifier($command)])) {
            throw new RuntimeException('Can not delete command that is not enqueued');
        }

        $commandKey = $this->commandQueueKeysByCommandId[$this->getCommandIdentifier($command)];

        unset($this->commands[$commandKey]);
    }

    private function getCommandIdentifier(CommandInterface $command): string
    {
        return (string)spl_object_id($command);
    }
}
