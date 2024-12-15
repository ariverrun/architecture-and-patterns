<?php

declare(strict_types=1);

namespace App\Async;

use Swoole\Coroutine;
use Closure;

class Async
{
    public function __construct(
        private readonly Closure $closureToExecute,
    ) {
    }

    public function __invoke(): void
    {
        Coroutine::create($this->closureToExecute);
    }

    public static function sleep(float $seconds): void
    {
        Coroutine::sleep($seconds);
    }
}
