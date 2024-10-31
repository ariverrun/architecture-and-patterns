<?php

declare(strict_types=1);

namespace App\Command;

use App\Exception\CommandException;
use App\GameObject\HavingFuelObjectInterface;

class CheckFuelCommand implements CommandInterface
{
    public function __construct(
        private readonly HavingFuelObjectInterface $obj,
    ) {
    }

    public function execute(): void
    {
        if (0 > ($this->obj->getFuelAmount() - $this->obj->getFuelConsumptionVelocity())) {
            throw new CommandException('Not enough fuel');
        }
    }
}
