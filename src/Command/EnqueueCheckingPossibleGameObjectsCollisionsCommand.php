<?php

declare(strict_types=1);

namespace App\Command;

use App\DependencyInjection\IoC;
use App\GameObject\IdentifiableObjectInterface;
use App\CommandQueue\ChangeableCommandQueueInterface;
use App\GameObject\LocatedObjectInterface;
use App\ValueObject\GameObjectIdentifier;
use App\Service\GameObjectsCollisionCheckerInterface;
use App\Service\GameObjectsLocationRegistryInterface;

class EnqueueCheckingPossibleGameObjectsCollisionsCommand implements CommandInterface
{
    private readonly GameObjectsLocationRegistryInterface $gameObjectsLocationRegistry;
    private readonly GameObjectsCollisionCheckerInterface $gameObjectsCollisionChecker;
    public function __construct(
        private readonly LocatedObjectInterface & IdentifiableObjectInterface $object,
        private readonly ChangeableCommandQueueInterface $queue,
    ) {
        $this->gameObjectsLocationRegistry = IoC::resolve('GameObjects.Location.Registry');
        $this->gameObjectsCollisionChecker = IoC::resolve('GameObjects.Collision.Checker.Service');
    }

    public function execute(): void
    {
        /**
         * @var array<string,LocatedObjectInterface>
         */
        $objectsToCheckCollisionWithById = [];

        $objectArea = $this->gameObjectsLocationRegistry->getObjectArea($this->object);

        foreach ($this->gameObjectsLocationRegistry->getObjectsLocatedInArea($objectArea) as $object) {
            $objectsToCheckCollisionWithById[(string)$object->getId()] = $object;
        }

        foreach ($objectArea->getAdjacentAreas() as $adjacentArea) {
            foreach ($this->gameObjectsLocationRegistry->getObjectsLocatedInArea($adjacentArea) as $object) {
                $objectsToCheckCollisionWithById[(string)$object->getId()] = $object;
            }
        }

        if (array_key_exists((string)$this->object->getId(), $objectsToCheckCollisionWithById)) {
            unset($objectsToCheckCollisionWithById[(string)$this->object->getId()]);
        }

        $commands = [];

        foreach ($objectsToCheckCollisionWithById as $objectToCheckCollisionWith) {
            $commands[] = new CheckGameObjectsCollisionCommand(
                $this->object,
                $objectToCheckCollisionWith,
                $this->gameObjectsCollisionChecker,
            );
        }

        $prevMacroCommand = IoC::resolve('Check.GameObjects.Collision.MacroCommand', $this->object->getId());

        /**
         * @var $prevMacroCommandSetter callable(GameObjectIdentifier, MacroCommand): void
         */
        $prevMacroCommandSetter = IoC::resolve('Previous.GameObjects.Collision.MacroCommand.Setter');

        if (empty($commands)) {
            $this->queue->delete($prevMacroCommand);
            $prevMacroCommandSetter($this->object->getId(), null);

            return;
        }

        $macroCommand = new MacroCommand($commands);

        if ($prevMacroCommand && $this->queue->isEnqueued($prevMacroCommand)) {
            $this->queue->replace($prevMacroCommand, $macroCommand);
        } else {
            $this->queue->enqueue($macroCommand);
        }

        $prevMacroCommandSetter($this->object->getId(), $macroCommand);
    }
}
