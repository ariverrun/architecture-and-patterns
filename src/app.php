<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\CommandQueue\CommandQueue;
use App\CommandStrategy\DoubleRetryOnExceptionThenLogExceptionStrategy;
use App\CommandStrategy\RetryOnExceptionThenLogExceptionStrategy;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$queue = new CommandQueue();

$logger = new Logger('default');
$logger->pushHandler(new StreamHandler(__DIR__ . '/../var/log/error.log', Level::Error));

$strategies = [
    RetryOnExceptionThenLogExceptionStrategy::class,
    DoubleRetryOnExceptionThenLogExceptionStrategy::class,
];

foreach ($strategies as $strategyClass) {
    (new $strategyClass($queue, $logger))();
}
