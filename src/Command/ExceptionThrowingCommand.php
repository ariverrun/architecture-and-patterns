<?php

declare(strict_types=1);

namespace App\Command;

use RuntimeException;

class ExceptionThrowingCommand implements CommandInterface
{
    public function execute(): void
    {
        throw new RuntimeException('Exception throw in ' . __METHOD__);
    }
}
