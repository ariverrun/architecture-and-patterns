<?php

declare(strict_types=1);

namespace App\Command;

use App\GameObject\MovingObjectInterface;
use App\ValueObject\Vector;

class MoveCommand implements CommandInterface
{
    public function __construct(
        private readonly MovingObjectInterface $obj,
    ) {
    }

    public function execute(): void
    {
        $this->obj->setLocation(
            Vector::plus(
                $this->obj->getLocation(),
                $this->obj->getVelocity()
            )
        );
    }
}
