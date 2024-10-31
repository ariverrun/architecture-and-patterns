<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Command\StraightLineMoveMacroCommand;
use App\Exception\CommandException;
use App\GameObject\HavingFuelObjectInterface;
use App\GameObject\MovingObjectInterface;
use App\ValueObject\Vector;
use PHPUnit\Framework\TestCase;

class StraightLineMoveMacroCommandTest extends TestCase
{
    public function testSuccessfulMovement(): void
    {
        $objectMock = $this->createMockForIntersectionOfInterfaces([
            HavingFuelObjectInterface::class,
            MovingObjectInterface::class,
        ]);

        $initialFuelAmount = 5;

        $objectMock->method('getFuelAmount')
                        ->willReturn($initialFuelAmount);

        $fuelConsumptionVelocity = 1;

        $objectMock->method('getFuelConsumptionVelocity')
            ->willReturn($fuelConsumptionVelocity);

        $initialLocation = new Vector(12, 5);

        $objectMock->method('getLocation')
                        ->willReturn($initialLocation);

        $velocity = new Vector(-7, 3);

        $objectMock->method('getVelocity')
                        ->willReturn($velocity);

        $expectedNewLocation = new Vector(5, 8);

        $objectMock->expects($this->once())
                ->method('setLocation')
                ->with($this->equalTo($expectedNewLocation));

        $objectMock->expects($this->once())
                ->method('setFuelAmount')
                ->with(
                    $this->equalTo($initialFuelAmount - $fuelConsumptionVelocity)
                );

        (new StraightLineMoveMacroCommand($objectMock))->execute();
    }

    public function testNotEnoughFuel(): void
    {
        $objectMock = $this->createMockForIntersectionOfInterfaces([
            HavingFuelObjectInterface::class,
            MovingObjectInterface::class,
        ]);

        $initialFuelAmount = 1;

        $objectMock->method('getFuelAmount')
                        ->willReturn($initialFuelAmount);

        $fuelConsumptionVelocity = 2;

        $objectMock->method('getFuelConsumptionVelocity')
            ->willReturn($fuelConsumptionVelocity);

        $initialLocation = new Vector(12, 5);

        $objectMock->method('getLocation')
                        ->willReturn($initialLocation);

        $velocity = new Vector(-7, 3);

        $objectMock->method('getVelocity')
                        ->willReturn($velocity);

        $objectMock->expects($this->never())
                ->method('setLocation');

        $objectMock->expects($this->never())
                ->method('setFuelAmount');

        $this->expectException(CommandException::class);

        (new StraightLineMoveMacroCommand($objectMock))->execute();
    }
}
