<?php

declare(strict_types=1);

namespace App\Command;

use App\GameObject\MovingObjectInterface;
use App\ValueObject\Vector;

class StopMovingCommand implements CommandInterface, GameObjectOperationCommandInterface
{
    public function __construct(
        private readonly MovingObjectInterface $obj,
    ) {
    }

    public function execute(): void
    {
        $this->obj->setVelocity(
            new Vector(0, 0),
        );
    }
}
