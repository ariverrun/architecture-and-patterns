<?php

declare(strict_types=1);

namespace App\GameObject;

use App\ValueObject\Vector;

interface MovingObjectInterface
{
    public function getLocation(): Vector;
    public function setLocation(Vector $location): void;
    public function getVelocity(): Vector;
    public function setVelocity(Vector $velocity): void;
}
