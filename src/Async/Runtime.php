<?php

declare(strict_types=1);

namespace App\Async;

use Swoole\Coroutine;
use Closure;

class Runtime
{
    public function __construct(
        private readonly Closure $closureToExecute,
    ) {
    }

    public function __invoke(): void
    {
        $closureToExecute = $this->closureToExecute;

        Coroutine\run(static function () use ($closureToExecute): void {
            $closureToExecute();
        });
    }
}
