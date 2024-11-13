<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Command\RotateMacroCommand;
use App\GameObject\MovingObjectInterface;
use App\GameObject\RotatingObjectInterface;
use App\ValueObject\Angle;
use App\ValueObject\Vector;
use PHPUnit\Framework\TestCase;

class RotateMacroCommandTest extends TestCase
{
    public function testRotateMovingObject(): void
    {
        $objectMock = $this->createMockForIntersectionOfInterfaces([
            RotatingObjectInterface::class,
            MovingObjectInterface::class,
        ]);

        $objectMock->method('getAngle')
                    ->willReturn(new Angle(30));

        $objectMock->method('getAngularVelocity')
                ->willReturn(new Angle(15));

        $initialVelocity = new Vector(4, 7);

        $objectMock->method('getVelocity')
            ->willReturn($initialVelocity);

        $expectedVelocity = new Vector(7.5332271682004, -2.8723663471584);

        $objectMock->method('setVelocity')
                    ->willReturnCallback(function (Vector $callbackArg) use ($expectedVelocity): void {
                        $this->assertTrue($expectedVelocity->equals($callbackArg));
                    });

        (new RotateMacroCommand($objectMock))->execute();
    }
}
