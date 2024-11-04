<?php

declare(strict_types=1);

namespace App\Command;

use App\GameObject\MovingObjectInterface;
use App\ValueObject\Vector;

class ChangeVelocityCommand implements CommandInterface
{
    public function __construct(
        private readonly MovingObjectInterface $obj,
        private readonly Vector $newVelocity,
    ) {
    }

    public function execute(): void
    {
        $this->obj->setVelocity($this->newVelocity);
    }
}
