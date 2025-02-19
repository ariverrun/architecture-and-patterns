<?php

declare(strict_types=1);

namespace App\AMQP\Firewall;

use App\AMQP\Consumer\ConsumingContext;
use PhpAmqpLib\Message\AMQPMessage;

interface FirewallInterface
{
    public function isAccessGranted(AMQPMessage $aMQPMessage, mixed $consumerArg, ConsumingContext $context): bool;
}
