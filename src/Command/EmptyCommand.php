<?php

declare(strict_types=1);

namespace App\Command;

class EmptyCommand implements CommandInterface
{
    public function execute(): void
    {
        var_dump(__METHOD__);
    }
}
