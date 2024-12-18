<?php

declare(strict_types=1);

namespace App\AMQP\Callback;

use PhpAmqpLib\Message\AMQPMessage;

interface AMQPCallbackInterface
{
    public function __invoke(AMQPMessage $message): void;
}
