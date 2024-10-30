<?php

declare(strict_types=1);

namespace App\CommandExceptionHandler;

use App\Command\CommandInterface;
use Throwable;

interface CommandExceptionHandlerInterface
{
    public function handle(CommandInterface $command, Throwable $exception): CommandInterface;

    public function registerResolver(string $commandClass, string $exceptionClass, CommandExceptionResolverInterface $handler): void;
}
