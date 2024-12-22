<?php

declare(strict_types=1);

namespace App\Command;

use App\DependencyInjection\IoC;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use RuntimeException;

class RegisterLoggersCommand implements CommandInterface
{
    public function execute(): void
    {
        $defaultLogger = new Logger('logger');

        foreach (Level::cases() as $level) {
            $defaultLogger->pushHandler(
                new StreamHandler('php://stdout', $level)
            );
        }

        $loggers = [
            '' => $defaultLogger,
        ];

        IoC::resolve('Ioc.Register', 'Logger', static function (?string $loggerKey = null) use ($loggers): LoggerInterface {
            $loggerKey = (string)$loggerKey;

            if (!isset($loggers[$loggerKey])) {
                throw new RuntimeException("Logger '{$loggerKey}' not found");
            }

            return $loggers[$loggerKey];
        })();
    }
}
