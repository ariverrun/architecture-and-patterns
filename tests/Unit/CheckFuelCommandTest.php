<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Command\CheckFuelCommand;
use App\Exception\CommandException;
use App\GameObject\HavingFuelObjectInterface;
use PHPUnit\Framework\TestCase;

class CheckFuelCommandTest extends TestCase
{
    public function testWhenHasEnoughFuel(): void
    {
        $havingFuelObjectMock = $this->createMock(HavingFuelObjectInterface::class);

        $havingFuelObjectMock->method('getFuelAmount')
                        ->willReturn(5);

        $havingFuelObjectMock->method('getFuelConsumptionVelocity')
            ->willReturn(1);

        $havingFuelObjectMock->expects($this->once())
                            ->method('getFuelAmount');

        $havingFuelObjectMock->expects($this->once())
                            ->method('getFuelConsumptionVelocity');

        (new CheckFuelCommand($havingFuelObjectMock))->execute();
    }

    public function testWhenHasFuelOnlyForOneMove(): void
    {
        $havingFuelObjectMock = $this->createMock(HavingFuelObjectInterface::class);

        $havingFuelObjectMock->method('getFuelAmount')
                        ->willReturn(1);

        $havingFuelObjectMock->method('getFuelConsumptionVelocity')
            ->willReturn(1);

        $havingFuelObjectMock->expects($this->once())
                            ->method('getFuelAmount');

        $havingFuelObjectMock->expects($this->once())
                            ->method('getFuelConsumptionVelocity');

        (new CheckFuelCommand($havingFuelObjectMock))->execute();
    }

    public function testWhenHasNotEnoghtFuel(): void
    {
        $havingFuelObjectMock = $this->createMock(HavingFuelObjectInterface::class);

        $havingFuelObjectMock->method('getFuelAmount')
                        ->willReturn(1);

        $havingFuelObjectMock->method('getFuelConsumptionVelocity')
            ->willReturn(2);

        $havingFuelObjectMock->expects($this->once())
                            ->method('getFuelAmount');

        $havingFuelObjectMock->expects($this->once())
                            ->method('getFuelConsumptionVelocity');

        $this->expectException(CommandException::class);

        (new CheckFuelCommand($havingFuelObjectMock))->execute();
    }
}
