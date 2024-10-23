<?php

declare(strict_types=1);

namespace App\Command;

use App\GameObject\RotatingObjectInterface;
use App\ValueObject\Angle;

class RotateCommand implements CommandInterface
{
    public function __construct(
        private readonly RotatingObjectInterface $obj,
    ) {
    }

    public function execute(): void
    {
        $this->obj->setAngle(
            Angle::plus(
                $this->obj->getAngle(),
                $this->obj->getAngularVelocity()
            )
        );
    }
}
