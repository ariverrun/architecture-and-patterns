<?php

declare(strict_types=1);

namespace App\Command;

use App\DependencyInjection\IoC;
use App\GameObject\ObjectsPool;
use App\GameObject\ObjectWithPropertiesContainerInterface;
use App\ValueObject\Vector;

class InitExampleGamesCommand implements CommandInterface
{
    private const GAMES_AMOUNT = 5;
    private const OBJECTS_PER_GAME_AMOUNT = 10;
    public function execute(): void
    {
        for ($i = 0; $i < self::GAMES_AMOUNT; ++$i) {
            $gameId = $i;

            $objectsPool = new ObjectsPool();

            for ($y = 0; $y < self::OBJECTS_PER_GAME_AMOUNT; ++$y) {
                $objectId = (string)$y;

                $object = new class () implements ObjectWithPropertiesContainerInterface {
                    private array $properties = [];
                    public function __construct()
                    {
                        $this->properties = [
                            'fuelAmount' => random_int(0, 100),
                            'fuelConsumptionVelocity' => random_int(1, 5),
                            'location' => new Vector(random_int(0, 1000), random_int(0, 1000)),
                            'velocity' => new Vector(random_int(0, 1000), random_int(0, 1000)),
                        ];
                    }
                    public function getProperty(string $id): mixed
                    {
                        return $this->properties[$id];
                    }
                    public function setProperty(string $id, mixed $value): void
                    {
                        $this->properties[$id] = $value;
                    }
                };

                $objectsPool->set($objectId, $object);
            }

            IoC::resolve('Game.ObjectsPool.Set', $gameId, 0)($objectsPool);
        }
    }
}
