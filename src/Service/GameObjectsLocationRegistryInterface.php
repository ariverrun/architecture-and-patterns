<?php

declare(strict_types=1);

namespace App\Service;

use App\GameField\AreaInterface;
use App\GameObject\IdentifiableObjectInterface;
use App\GameObject\LocatedObjectInterface;

interface GameObjectsLocationRegistryInterface
{
    public function getObjectArea(LocatedObjectInterface & IdentifiableObjectInterface $object): AreaInterface;

    /**
     * @return LocatedObjectInterface&IdentifiableObjectInterface[]
     */
    public function getObjectsLocatedInArea(AreaInterface $area): array;
}
