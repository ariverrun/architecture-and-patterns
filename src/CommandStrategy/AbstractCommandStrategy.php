<?php

declare(strict_types=1);

namespace App\CommandStrategy;

use App\CommandQueue\CommandQueue;
use App\CommandQueue\CommandQueueInterface;
use App\CommandQueue\CommandQueueHandler;
use App\CommandExceptionHandler\CommandExceptionHandler;
use App\CommandExceptionHandler\CommandExceptionHandlerInterface;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;

abstract class AbstractCommandStrategy
{
    protected CommandExceptionHandlerInterface $commandExceptionHandler;

    final public function __construct(
        protected readonly CommandQueueInterface $commandQueue,
        protected readonly LoggerInterface $logger,
    ) {
        $this->commandExceptionHandler = new CommandExceptionHandler();
    }

    final public function __invoke(): void
    {
        $this->enqueueCommands();

        $this->registerExceptionResolvers();

        $queueHandler = new CommandQueueHandler($this->commandExceptionHandler);

        $queueHandler->handle($this->commandQueue);        
    }

    abstract protected function enqueueCommands(): void;
    abstract protected function registerExceptionResolvers(): void;
}