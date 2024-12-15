<?php

declare(strict_types=1);

namespace App\Command;

use App\DependencyInjection\IoC;
use App\AMQP\Callback\AMQPCallbackInterface;

class RegisterAMQPCallback implements CommandInterface
{
    public function __construct(
       private readonly AMQPCallbackInterface $callback,
    ) {
    }

    public function execute(): void
    {
        $callback = $this->callback;

        Ioc::resolve('Ioc.Register', 'Amqp.MessageCallback.Get', static function() use($callback): callable {
            return function($message) use($callback) {
                return ($callback)($message);
            };
        })();
    }
}