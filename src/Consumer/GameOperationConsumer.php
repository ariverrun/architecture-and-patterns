<?php

declare(strict_types=1);

namespace App\Consumer;

use App\AMQP\Consumer\AMQPConsumerInterface;
use App\Command\InterpretCommand;
use App\CommandQueue\CommandQueueInterface;
use App\DependencyInjection\IoC;
use App\GameObject\ObjectWithPropertiesContainerInterface;

class GameOperationConsumer implements AMQPConsumerInterface
{
    public function consume(GameOperationMessageDto $message): void
    {
        /** @var CommandQueueInterface $queue */
        $queue = IoC::resolve('Game.Queue.Get', $message->gameId);

        /** @var ObjectWithPropertiesContainerInterface $object */
        $object = IoC::resolve('Game.Object.Get', $message->objectId, $message->gameId);

        $command = new InterpretCommand($message->operationId, $object, $message->args, $queue);

        $queue->enqueue($command);
    }
}
