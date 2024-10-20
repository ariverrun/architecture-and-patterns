<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Command\MoveCommand;
use App\GameObject\MovingObjectInterface;
use App\ValueObject\Vector;
use PHPUnit\Framework\TestCase;
use Exception;

class MoveCommandTest extends TestCase
{
    public function testSuccessfulMovement(): void
    {
        $movingObjectMock = $this->createMock(MovingObjectInterface::class);

        $initialLocation = new Vector(12, 5);

        $movingObjectMock->method('getLocation')
                        ->willReturn($initialLocation);

        $velocity = new Vector(-7, 3);

        $movingObjectMock->method('getVelocity')
                        ->willReturn($velocity);

        $expectedNewLocation = new Vector(5, 8);

        $movingObjectMock->expects($this->once())
                ->method('setLocation')
                ->with($this->equalTo($expectedNewLocation));

        (new MoveCommand($movingObjectMock))->execute();
    }

    public function testMovementForObjectWithUnreadableLocation(): void
    {
        $movingObjectMock = $this->createMock(MovingObjectInterface::class);

        $exception = new Exception('Location can not be read');

        $movingObjectMock->method('getLocation')
                        ->willThrowException($exception);

        $velocity = new Vector(1, 1);

        $movingObjectMock->method('getVelocity')
                        ->willReturn($velocity);

        $this->expectException(get_class($exception));

        (new MoveCommand($movingObjectMock))->execute();
    }

    public function testMovementForObjectWithUnreadableVelocity(): void
    {
        $movingObjectMock = $this->createMock(MovingObjectInterface::class);

        $initialLocation = new Vector(12, 5);

        $movingObjectMock->method('getLocation')
                        ->willReturn($initialLocation);

        $exception = new Exception(message: 'Velocity can not be read');

        $movingObjectMock->method('getVelocity')
                        ->willThrowException($exception);

        $this->expectException(get_class($exception));

        (new MoveCommand($movingObjectMock))->execute();
    }

    public function testMovementForObjectThatDontAllowToSetLocation(): void
    {
        $initialLocation = new Vector(1, 5);

        $velocity = new Vector(5, 8);

        $movingObjectMock = $this->createMock(MovingObjectInterface::class);

        $movingObjectMock->method('getLocation')
                        ->willReturn($initialLocation);

        $movingObjectMock->method('getVelocity')
                        ->willReturn($velocity);

        $exception = new Exception('Location can not be set');

        $movingObjectMock->method('setLocation')
                        ->willThrowException($exception);

        $this->expectException(get_class($exception));

        (new MoveCommand($movingObjectMock))->execute();
    }
}
