<?php

declare(strict_types=1);

namespace App\GameObject;

use App\ValueObject\Angle;

interface RotatingObjectInterface
{
    public function getAngle(): Angle;
    public function setAngle(Angle $angle): void;
    public function getAngularVelocity(): Angle;
}
