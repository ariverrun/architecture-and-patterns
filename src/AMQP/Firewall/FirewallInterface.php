<?php

declare(strict_types=1);

namespace App\AMQP\Firewall;

use PhpAmqpLib\Message\AMQPMessage;

interface FirewallInterface
{
    public function isAccessGranted(AMQPMessage $aMQPMessage, mixed $consumerArg): bool;
}
