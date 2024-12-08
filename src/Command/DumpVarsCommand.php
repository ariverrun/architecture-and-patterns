<?php

declare(strict_types=1);

namespace App\Command;

class DumpVarsCommand implements CommandInterface
{
    public function __construct(
        private readonly array $vars,
    ) {
    }

    public function execute(): void
    {
        foreach ($this->vars as $var) {
            var_dump($var);
        }
    }
}
