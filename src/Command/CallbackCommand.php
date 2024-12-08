<?php

declare(strict_types=1);

namespace App\Command;

use Closure;

class CallbackCommand implements CommandInterface
{
    public function __construct(
        private readonly Closure $callback,
        private readonly array $args,
    ) {
    }

    public function execute(): void
    {
        ($this->callback)(...$this->args);
    }
}
