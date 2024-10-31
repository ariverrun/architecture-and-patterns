<?php

declare(strict_types=1);

namespace App\Command;

use App\GameObject\HavingFuelObjectInterface;

class BurnFuelCommand implements CommandInterface
{
    public function __construct(
        private readonly HavingFuelObjectInterface $obj,
    ) {
    }

    public function execute(): void
    {
        $this->obj->setFuelAmount(
            $this->obj->getFuelAmount() - $this->obj->getFuelConsumptionVelocity()
        );
    }
}
