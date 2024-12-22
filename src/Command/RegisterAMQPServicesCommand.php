<?php

declare(strict_types=1);

namespace App\Command;

use App\AMQP\Callback\MultipleConsumersCallback;

class RegisterAMQPServicesCommand extends MacroCommand
{
    /**
     * @var CommandInterface[]
     */
    protected readonly array $commands;

    public function __construct()
    {
        $commands = [
            new RegisterAMQPCallback(
                new MultipleConsumersCallback(),
            ),
        ];

        $this->commands = $commands;
    }
}
