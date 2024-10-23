<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Command\RotateCommand;
use App\GameObject\RotatingObjectInterface;
use App\ValueObject\Angle;
use PHPUnit\Framework\TestCase;
use Exception;

class RotateCommandTest extends TestCase
{
    public function testSuccessfulRotation(): void
    {
        $rotatingObjectMock = $this->createMock(RotatingObjectInterface::class);

        $initialAngle = new Angle(0);

        $rotatingObjectMock->method('getAngle')
                        ->willReturn($initialAngle);

        $angularVelocity = new Angle(10);

        $rotatingObjectMock->method('getAngularVelocity')
                        ->willReturn($angularVelocity);

        $expectedNewAngle = new Angle(10);

        $rotatingObjectMock->expects($this->once())
                ->method(constraint: 'setAngle')
                ->with($this->equalTo($expectedNewAngle));

        (new RotateCommand($rotatingObjectMock))->execute();
    }

    public function testRotationForObjectWithUnreadableAngle(): void
    {
        $rotatingObjectMock = $this->createMock(RotatingObjectInterface::class);

        $exception = new Exception('Angle can not be read');

        $rotatingObjectMock->method('getAngle')
                        ->willThrowException($exception);

        $angularVelocity = new Angle(10);

        $rotatingObjectMock->method('getAngularVelocity')
                        ->willReturn($angularVelocity);

        $this->expectException(get_class($exception));

        (new RotateCommand($rotatingObjectMock))->execute();
    }

    public function testRotationForObjectWithUnreadableAngularVelocity(): void
    {
        $rotatingObjectMock = $this->createMock(RotatingObjectInterface::class);

        $initialAngle = new Angle(0);

        $rotatingObjectMock->method('getAngle')
                        ->willReturn($initialAngle);

        $exception = new Exception('Angular velocity can not be read');

        $rotatingObjectMock->method('getAngularVelocity')
                        ->willThrowException($exception);

        $this->expectException(get_class($exception));

        (new RotateCommand($rotatingObjectMock))->execute();
    }

    public function testRotationForObjectThatDontAllowToSetAngle(): void
    {
        $rotatingObjectMock = $this->createMock(RotatingObjectInterface::class);

        $initialAngle = new Angle(0);

        $rotatingObjectMock->method('getAngle')
                        ->willReturn($initialAngle);

        $angularVelocity = new Angle(10);

        $rotatingObjectMock->method('getAngularVelocity')
                        ->willReturn($angularVelocity);

        $exception = new Exception('Angle can not be set');

        $rotatingObjectMock->method('setAngle')
                        ->willThrowException($exception);

        $this->expectException(get_class($exception));

        (new RotateCommand($rotatingObjectMock))->execute();
    }
}
