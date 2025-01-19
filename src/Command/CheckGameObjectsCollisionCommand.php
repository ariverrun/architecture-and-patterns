<?php

declare(strict_types=1);

namespace App\Command;

use App\Exception\GameObjectsCollisionException;
use App\GameObject\LocatedObjectInterface;
use App\Service\GameObjectsCollisionCheckerInterface;

class CheckGameObjectsCollisionCommand implements CommandInterface
{
    public function __construct(
        private readonly LocatedObjectInterface $objectA,
        private readonly LocatedObjectInterface $objectB,
        private readonly GameObjectsCollisionCheckerInterface $gameObjectsCollisionChecker,
    ) {
    }

    /**
     * @throws GameObjectsCollisionException
     */
    public function execute(): void
    {
        if (true === $this->gameObjectsCollisionChecker->haveCollision($this->objectA, $this->objectB)) {
            throw new GameObjectsCollisionException();
        }
    }
}
