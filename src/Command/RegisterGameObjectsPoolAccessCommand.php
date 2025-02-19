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
        $objectsPoolsByGameIdAndUserId = [];

        IoC::resolve('Ioc.Register', 'Game.ObjectsPool.Get', static function (int $gameId, int $userId) use (&$objectsPoolsByGameIdAndUserId): ObjectsPool {
            if (!isset($objectsPoolsByGameIdAndUserId[$gameId][$userId])) {
                throw new RuntimeException("Objects pool for a game with id {$gameId} and user with id {$userId} is not found");
            }

            return $objectsPoolsByGameIdAndUserId[$gameId][$userId];
        })();

        IoC::resolve('Ioc.Register', 'Game.ObjectsPool.Set', static function (int $gameId, int $userId) use (&$objectsPoolsByGameIdAndUserId): Closure {
            return function (ObjectsPool $objectsPool) use (&$objectsPoolsByGameIdAndUserId, $gameId, $userId): void {
                $objectsPoolsByGameIdAndUserId[$gameId][$userId] = $objectsPool;
            };
        })();

        IoC::resolve('Ioc.Register', 'Game.Object.Get', static function (string $objectId, int $gameId, int $userId): ObjectWithPropertiesContainerInterface {
            /** @var ObjectsPool $objectsPool */
            $objectsPool = IoC::resolve('Game.ObjectsPool.Get', $gameId, $userId);

            return $objectsPool->get($objectId);
        })();

        IoC::resolve('Ioc.Register', 'Game.Object.Set', static function (string $objectId, int $gameId, int $userId): Closure {
            return function (ObjectWithPropertiesContainerInterface $object) use ($objectId, $gameId, $userId): void {
                /** @var ObjectsPool $objectsPool */
                $objectsPool = IoC::resolve('Game.ObjectsPool.Get', $gameId, $userId);
                $objectsPool->set($objectId, $object);
            };
        })();
    }
}
