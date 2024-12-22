<?php

declare(strict_types=1);

namespace App\Command;

use App\GameObject\ObjectWithPropertiesContainerInterface;

class SetObjectPropertyCommand implements CommandInterface
{
    public function __construct(
        private readonly ObjectWithPropertiesContainerInterface $obj,
        private readonly string $propertyId,
        private readonly mixed $propertyValue,
    ) {
    }

    public function execute(): void
    {
        $this->obj->setProperty($this->propertyId, $this->propertyValue);
    }
}
