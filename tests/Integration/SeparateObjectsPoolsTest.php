<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Command\RegisterGameObjectsPoolAccessCommand;
use App\DependencyInjection\IoC;
use App\GameObject\ObjectWithPropertiesContainerInterface;
use App\GameObject\ObjectsPool;
use Tests\Traits\IocSetupTrait;
use PHPUnit\Framework\TestCase;

class SeparateObjectsPoolsTest extends TestCase
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

    public function testPoolsIsolationForDifferentUsers(): void
    {
        $objectsByTheirIdAndUserId = [
            1 => [
                'a' => $this->createMock(ObjectWithPropertiesContainerInterface::class),
                'b' => $this->createMock(ObjectWithPropertiesContainerInterface::class),
                'c' => $this->createMock(ObjectWithPropertiesContainerInterface::class),
                'd' => $this->createMock(ObjectWithPropertiesContainerInterface::class),
            ],
            2 => [
                'a' => $this->createMock(ObjectWithPropertiesContainerInterface::class),
                'd' => $this->createMock(ObjectWithPropertiesContainerInterface::class),
                'e' => $this->createMock(ObjectWithPropertiesContainerInterface::class),
            ],
        ];

        (new RegisterGameObjectsPoolAccessCommand())->execute();

        foreach ($objectsByTheirIdAndUserId as $userId => $objectsByTheirId) {
            $objectsPool = new ObjectsPool();

            IoC::resolve('Game.ObjectsPool.Set', 1, $userId)($objectsPool);

            foreach ($objectsByTheirId as $objectId => $object) {
                IoC::resolve('Game.Object.Set', $objectId, 1, $userId)($object);
            }
        }

        foreach ($objectsByTheirIdAndUserId as $userId => $objectsByTheirId) {
            foreach ($objectsByTheirId as $objectId => $object) {
                $objectFromIoC = IoC::resolve('Game.Object.Get', $objectId, 1, $userId);
                $this->assertEqualsCanonicalizing($object, $objectFromIoC);
            }
        }
    }
}
