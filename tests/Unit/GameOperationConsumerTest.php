<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Command\CommandInterface;
use App\Command\InterpretCommand;
use App\CommandQueue\CommandQueueInterface;
use App\Consumer\GameOperationConsumer;
use App\Consumer\GameOperationMessageDto;
use App\DependencyInjection\IoC;
use App\GameObject\ObjectWithPropertiesContainerInterface;
use Tests\Traits\IocSetupTrait;
use PHPUnit\Framework\TestCase;

class GameOperationConsumerTest extends TestCase
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
    
    public function testSuccessfulConsume(): void
    {
        $gameId = 1;
        $objectId = '2';
        $operationId = '3';

        $dto = new GameOperationMessageDto(
            $gameId,
            $objectId,
            $operationId,
            null,
        );

        $consumer = new GameOperationConsumer();

        $queueMock = $this->createMock(CommandQueueInterface::class);

        IoC::resolve('Ioc.Register', 'Game.Queue.Get', static function (int $gameId) use ($queueMock): CommandQueueInterface {
            return $queueMock;
        })();
        
        $objectMock = $this->createMock(ObjectWithPropertiesContainerInterface::class);

        IoC::resolve('Ioc.Register', 'Game.Object.Get', static function (string $objectId, int $gameId) use($objectMock): ObjectWithPropertiesContainerInterface {
            return $objectMock;
        })();        

        $queueMock->expects($this->once())
                    ->method('enqueue')
                    ->with(
                        $this->isInstanceOf(InterpretCommand::class)
                    );

        $consumer->consume($dto);
    }
}
