<?php

declare(strict_types=1);

namespace App\Command;

use Psr\Log\LoggerInterface;
use Throwable;

class LoggingExceptionCommand implements CommandInterface
{
    public function __construct(
        private readonly Throwable $exception,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function execute(): void
    {
        $this->logger->error('Caught command exception', [
            'exception' => $this->exception,
        ]);
    }
}
