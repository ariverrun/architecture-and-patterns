<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Command\BurnFuelCommand;
use App\GameObject\HavingFuelObjectInterface;
use PHPUnit\Framework\TestCase;

class BurnFuelCommandTest extends TestCase
{
    public function testBurningFuel(): void
    {
        $havingFuelObjectMock = $this->createMock(HavingFuelObjectInterface::class);

        $initialFuelAmount = 5;

        $havingFuelObjectMock->method('getFuelAmount')
                        ->willReturn($initialFuelAmount);

        $fuelConsumptionVelocity = 1;

        $havingFuelObjectMock->method('getFuelConsumptionVelocity')
            ->willReturn($fuelConsumptionVelocity);


        $havingFuelObjectMock->expects($this->once())
                            ->method('setFuelAmount')
                            ->with(
                                $this->equalTo($initialFuelAmount - $fuelConsumptionVelocity)
                            );

        (new BurnFuelCommand($havingFuelObjectMock))->execute();
    }
}
