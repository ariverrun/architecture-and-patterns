<?php

declare(strict_types=1);

namespace App\GameObject;

interface HavingFuelObjectInterface
{
    public function getFuelAmount(): int;

    public function setFuelAmount(int $fuelAmount): void;

    public function getFuelConsumptionVelocity(): int;
}
