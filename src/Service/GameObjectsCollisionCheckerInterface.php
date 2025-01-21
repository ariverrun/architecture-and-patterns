<?php

declare(strict_types=1);

namespace App\Service;

use App\GameObject\LocatedObjectInterface;

interface GameObjectsCollisionCheckerInterface
{
    public function haveCollision(LocatedObjectInterface $objectA, LocatedObjectInterface $objectB): bool;
}
