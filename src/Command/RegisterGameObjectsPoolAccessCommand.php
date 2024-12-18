<?php

declare(strict_types=1);

namespace App\Command;

use App\DependencyInjection\IoC;
use App\GameObject\ObjectsPool;
use App\GameObject\ObjectWithPropertiesContainerInterface;
use Closure;
use RuntimeException;

class RegisterGameObjectsPoolAccessCommand implements CommandInterface
{
    public function execute(): void
    {
        $objectsPoolsByGameId = [];

        IoC::resolve('Ioc.Register', 'Game.ObjectsPool.Get', static function (int $gameId) use (&$objectsPoolsByGameId): ObjectsPool {
            if (!isset($objectsPoolsByGameId[$gameId])) {
                throw new RuntimeException("Objects pool for a game with id {$gameId} is not found");
            }

            return $objectsPoolsByGameId[$gameId];
        })();

        IoC::resolve('Ioc.Register', 'Game.ObjectsPool.Set', static function (int $gameId) use (&$objectsPoolsByGameId): Closure {
            return function (ObjectsPool $objectsPool) use (&$objectsPoolsByGameId, $gameId): void {
                $objectsPoolsByGameId[$gameId] = $objectsPool;
            };
        })();

        IoC::resolve('Ioc.Register', 'Game.Object.Get', static function (string $objectId, int $gameId): ObjectWithPropertiesContainerInterface {
            /** @var ObjectsPool $objectsPool */
            $objectsPool = IoC::resolve('Game.ObjectsPool.Get', $gameId);

            return $objectsPool->get($objectId);
        })();

        IoC::resolve('Ioc.Register', 'Game.Object.Set', static function (string $objectId, int $gameId): Closure {
            return function (ObjectWithPropertiesContainerInterface $object) use ($objectId, $gameId): void {
                /** @var ObjectsPool $objectsPool */
                $objectsPool = IoC::resolve('Game.ObjectsPool.Get', $gameId);
                $objectsPool->set($objectId, $object);
            };
        })();
    }
}
