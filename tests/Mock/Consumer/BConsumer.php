<?php

declare(strict_types=1);

namespace Tests\Mock\Consumer;

use App\AMQP\Consumer\AMQPConsumerInterface;

class BConsumer implements AMQPConsumerInterface
{
    public function consume(MixedDto $dto): void
    {
    }
}
