<?php

declare(strict_types=1);

namespace App\CommandExceptionHandler;

use App\Command\CommandInterface;
use Throwable;

interface CommandExceptionResolverInterface
{
    public function resolve(CommandInterface $command, Throwable $exception): CommandInterface;
}
