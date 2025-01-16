<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Command\CommandInterface;
use App\Command\InterpretCommand;
use App\CommandQueue\CommandQueueInterface;
use App\DependencyInjection\IoC;
use App\GameObject\ObjectWithPropertiesContainerInterface;
use Tests\Traits\IocSetupTrait;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class InterpretCommandTest extends TestCase
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

    public function testSuccessfulInterpretation(): void
    {
        $operationId = '1';

        $objectMock = $this->createMock(ObjectWithPropertiesContainerInterface::class);

        $commandMock = $this->createMock(CommandInterface::class);

        IoC::resolve('Ioc.Register', 'Game.Operation.Get', static function (string $operationId, ObjectWithPropertiesContainerInterface $object, mixed $args) use ($commandMock): CommandInterface {
            return $commandMock;
        })();

        $queueMock = $this->createMock(CommandQueueInterface::class);

        $queueMock->expects($this->once())
                    ->method('enqueue')
                    ->with($this->equalToCanonicalizing($commandMock));

        (new InterpretCommand(
            $operationId,
            $objectMock,
            null,
            $queueMock,
        ))->execute();
    }

    public function testOperationNotFound(): void
    {
        $operationId = '1';

        $objectMock = $this->createMock(ObjectWithPropertiesContainerInterface::class);

        IoC::resolve('Ioc.Register', 'Game.Operation.Get', static function (string $operationId, ObjectWithPropertiesContainerInterface $object, mixed $args): null {
            return null;
        })();

        $queueMock = $this->createMock(CommandQueueInterface::class);

        $queueMock->expects($this->never())
                    ->method('enqueue');

        $this->expectException(InvalidArgumentException::class);

        (new InterpretCommand(
            $operationId,
            $objectMock,
            null,
            $queueMock,
        ))->execute();
    }
}
