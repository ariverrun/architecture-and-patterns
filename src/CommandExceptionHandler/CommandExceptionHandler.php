<?php

declare(strict_types=1);

namespace App\CommandExceptionHandler;

use App\Command\CommandInterface;
use RuntimeException;
use Throwable;

class CommandExceptionHandler implements CommandExceptionHandlerInterface
{
    /**
     * @var array<string,<string,CommandExceptionResolverInterface>>
     */
    private array $exceptionHandlersMap = [];

    public function handle(CommandInterface $command, Throwable $exception): CommandInterface
    {
        $resolver = $this->getResolver($command, $exception);

        return $resolver->resolve($command, $exception);
    }

    public function registerResolver(string $commandClass, string $exceptionClass, CommandExceptionResolverInterface $exceptionHandler): void
    {
        $this->exceptionHandlersMap[$commandClass][$exceptionClass] = $exceptionHandler;
    }

    private function getResolver(CommandInterface $command, Throwable $exception): CommandExceptionResolverInterface
    {
        $commandClass = get_class($command);
        $exceptionClass = get_class($exception);

        $exceptionHandler = $this->exceptionHandlersMap[$commandClass][$exceptionClass] ?? null;

        if (null === $exceptionHandler) {
            throw new RuntimeException('Exception handler for ' . $exceptionClass . ' thrown in command ' . $commandClass . ' is not found');
        }

        return $exceptionHandler;
    }
}
