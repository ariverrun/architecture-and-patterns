<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Command\ChangeVelocityCommand;
use App\GameObject\MovingObjectInterface;
use App\ValueObject\Vector;
use PHPUnit\Framework\TestCase;

class ChangeVelocityCommandTest extends TestCase
{
    public function testChangingVelocity(): void
    {
        $movingObjectMock = $this->createMock(MovingObjectInterface::class);

        $movingObjectMock->expects($this->any())
                        ->method('getVelocity')
                        ->willReturn(new Vector(1, 1));

        $expected = new Vector(3, 3);

        $movingObjectMock->expects($this->once())
                        ->method('setVelocity')
                        ->with($this->equalTo($expected));

        (new ChangeVelocityCommand($movingObjectMock, $expected))->execute();
    }
}
