<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Command\LoggingExceptionCommand;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Exception;

class LoggingExceptionCommandTest extends TestCase
{
    public function testExceptionLogging(): void
    {
        $loggerMock = $this->createMock(LoggerInterface::class);

        $exceptionMessage = 'Exception thrown in ' . __METHOD__;

        $exception = new Exception($exceptionMessage);

        $loggerMock->expects($this->once())
                    ->method('error')
                    ->with(
                        $this->anything(),
                        $this->equalTo([
                            'exception' => $exception,
                        ]),
                    );

        (new LoggingExceptionCommand($exception, $loggerMock))->execute();
    }
}
