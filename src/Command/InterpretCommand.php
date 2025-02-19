<?php

declare(strict_types=1);

namespace App\Command;

use App\CommandQueue\CommandQueueInterface;
use App\DependencyInjection\IoC;
use App\GameObject\ObjectWithPropertiesContainerInterface;
use Webmozart\Assert\Assert;

class InterpretCommand implements CommandInterface
{
    public function __construct(
        private readonly string $operationId,
        private readonly ?ObjectWithPropertiesContainerInterface $obj,
        private readonly mixed $args,
        private readonly CommandQueueInterface $queue,
    ) {
    }

    public function execute(): void
    {
        $command = IoC::resolve('Game.Operation.Get', $this->operationId, $this->obj, $this->args);

        Assert::isInstanceOf($command, CommandInterface::class);

        $this->queue->enqueue($command);
    }
}
