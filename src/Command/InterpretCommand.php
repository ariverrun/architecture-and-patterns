<?php

declare(strict_types=1);

namespace App\Command;

use App\GameObject\ObjectWithPropertiesContainerInterface;

class InterpretCommand implements CommandInterface
{
    public function __construct(
        private readonly string $operationId,
        private readonly ObjectWithPropertiesContainerInterface $obj,
        private readonly mixed $args,
    ) {
    }

    public function execute(): void
    {

    }
}