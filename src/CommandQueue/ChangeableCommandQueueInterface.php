<?php

declare(strict_types=1);

namespace App\CommandQueue;

use App\Command\CommandInterface;

interface ChangeableCommandQueueInterface extends CommandQueueInterface
{
    public function isEnqueued(CommandInterface $command): bool;

    public function replace(CommandInterface $replaceWhat, CommandInterface $replaceWith): void;

    public function delete(CommandInterface $command): void;
}
