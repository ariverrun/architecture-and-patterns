<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Command\CheckGameObjectsCollisionCommand;
use App\Command\EnqueueCheckingPossibleGameObjectsCollisionsCommand;
use App\Command\MacroCommand;
use App\DependencyInjection\IoC;
use App\GameObject\IdentifiableObjectInterface;
use App\CommandQueue\ChangeableCommandQueueInterface;
use App\GameObject\LocatedObjectInterface;
use App\Service\GameObjectsCollisionCheckerInterface;
use App\Service\GameObjectsLocationRegistryInterface;
use App\ValueObject\GameObjectIdentifier;
use Tests\Mock\Interface\CallableInterface;
use Tests\Traits\IocSetupTrait;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;

class GameObjectsCollisionsCheckingTest extends TestCase
{
    use IocSetupTrait;

    public function setUp(): void
    {
        $this->setUpIocDependencyResolver();
    }

    public function tearDown(): void
    {
        $this->setUpIocDependencyResolver();
    }

    #[DataProvider('getTestCases')]
    public function testEnqueueingCheckingCommand(int $objectsCount, bool $prevCommandExists, bool $prevCommandIsEnqueued): void
    {
        $collisionsChecker = $this->createMock(GameObjectsCollisionCheckerInterface::class);

        IoC::resolve('Ioc.Register', 'GameObjects.Collision.Checker.Service', static function () use ($collisionsChecker): GameObjectsCollisionCheckerInterface {
            return $collisionsChecker;
        })();

        $otherObjects = [];

        for ($i = 0; $i < $objectsCount; ++$i) {
            $otherObjects[] = $this->createGameObjectMock();
        }

        $gameObjectsLocationRegistry = $this->createMock(GameObjectsLocationRegistryInterface::class);

        $gameObjectsLocationRegistry->expects($this->once())
                ->method('getObjectsLocatedInArea')
                ->willReturn($otherObjects);

        IoC::resolve('Ioc.Register', 'GameObjects.Location.Registry', static function () use ($gameObjectsLocationRegistry): GameObjectsLocationRegistryInterface {
            return $gameObjectsLocationRegistry;
        })();

        $queue = $this->createMock(ChangeableCommandQueueInterface::class);

        if ($objectsCount > 0 && true === $prevCommandExists) {
            $queue->expects($this->once())
                ->method('isEnqueued')
                ->willReturn($prevCommandIsEnqueued);
        } else {
            $queue->expects($this->never())
                ->method('isEnqueued');
        }

        IoC::resolve('Ioc.Register', 'Check.GameObjects.Collision.MacroCommand', static function (): ?MacroCommand {
            return null;
        })();

        if (true === $prevCommandExists) {
            $prevMacroCommand = $this->createMock(MacroCommand::class);

            IoC::resolve('Ioc.Register', 'Check.GameObjects.Collision.MacroCommand', static function () use ($prevMacroCommand): ?MacroCommand {
                return $prevMacroCommand;
            })();
        }

        $enqueuedCommand = null;

        if (true === $prevCommandIsEnqueued) {
            if ($objectsCount > 0) {
                $queue->expects($this->once())
                    ->method('replace')
                    ->with(
                        $this->equalToCanonicalizing($prevMacroCommand),
                        $this->isInstanceOf(MacroCommand::class),
                    )
                    ->willReturnCallback(function (MacroCommand $prevCommand, MacroCommand $newCommand) use (&$enqueuedCommand): void {
                        $enqueuedCommand = $newCommand;
                    });
            } else {
                $queue->expects($this->once())
                    ->method('delete')
                    ->with(
                        $this->equalToCanonicalizing($prevMacroCommand),
                    );
            }
        } else {
            $queue->expects($this->never())
                    ->method('replace');
            $queue->expects($this->never())
                    ->method('delete');
            $queue->expects($this->once())
                    ->method('enqueue')
                    ->with(
                        $this->isInstanceOf(MacroCommand::class),
                    )->willReturnCallback(function (MacroCommand $command) use (&$enqueuedCommand): void {
                        $enqueuedCommand = $command;
                    });
        }

        $object = $this->createGameObjectMock();

        $prevMacroCommandSetter = $this->createMock(CallableInterface::class);

        $prevMacroCommandSetter->expects($this->once())
            ->method('__invoke')
            ->willReturnCallback(function (GameObjectIdentifier $gameObjectId, ?MacroCommand $command) use ($objectsCount, $object): void {
                $this->assertEqualsCanonicalizing($gameObjectId, $object->getId());

                if ($objectsCount > 0) {
                    $this->assertInstanceOf(MacroCommand::class, $command);
                } else {
                    $this->assertNull($command);
                }
            });

        IoC::resolve('Ioc.Register', 'Previous.GameObjects.Collision.MacroCommand.Setter', static function () use ($prevMacroCommandSetter): callable {
            return $prevMacroCommandSetter;
        })();

        (new EnqueueCheckingPossibleGameObjectsCollisionsCommand($object, $queue))->execute();

        if ($objectsCount > 0) {
            $this->assertInstanceOf(MacroCommand::class, $enqueuedCommand);

            $macroCommandReflector = new ReflectionClass($enqueuedCommand);
            $commandsProperty = $macroCommandReflector->getProperty('commands');
            $commandsProperty->setAccessible(true);
            $subCommands = $commandsProperty->getValue($enqueuedCommand);

            $this->assertCount($objectsCount, $subCommands);

            $subCommandReflector = new ReflectionClass(CheckGameObjectsCollisionCommand::class);
            $objectAProperty = $subCommandReflector->getProperty('objectA');
            $objectAProperty->setAccessible(true);
            $objectBProperty = $subCommandReflector->getProperty('objectB');
            $objectBProperty->setAccessible(true);

            for ($i = 0; $i < $objectsCount; ++$i) {
                $subCommand = $subCommands[$i];

                $this->assertInstanceOf(CheckGameObjectsCollisionCommand::class, $subCommand);

                $subCommandObjectA = $objectAProperty->getValue($subCommand);
                $this->assertEqualsCanonicalizing($object, $subCommandObjectA);
                $subCommandObjectB = $objectBProperty->getValue($subCommand);
                $this->assertEqualsCanonicalizing($otherObjects[$i], $subCommandObjectB);
            }

        } else {
            $this->assertNull($enqueuedCommand);
        }
    }

    public static function getTestCases(): array
    {
        return [
            [
                'objectsCount' => 3,
                'prevCommandExists' => true,
                'prevCommandIsEnqueued' => true,
            ],
            [
                'objectsCount' => 0,
                'prevCommandExists' => true,
                'prevCommandIsEnqueued' => true,
            ],
            [
                'objectsCount' => 2,
                'prevCommandExists' => true,
                'prevCommandIsEnqueued' => false,
            ],
            [
                'objectsCount' => 4,
                'prevCommandExists' => false,
                'prevCommandIsEnqueued' => false,
            ],
        ];
    }

    private function createGameObjectMock(): LocatedObjectInterface & IdentifiableObjectInterface
    {
        $objectMock = $this->createMockForIntersectionOfInterfaces([IdentifiableObjectInterface::class, LocatedObjectInterface::class]);

        $objectMock->expects($this->any())
                ->method('getId')
                ->willReturn(new GameObjectIdentifier(spl_object_id($objectMock)));

        return $objectMock;
    }
}
