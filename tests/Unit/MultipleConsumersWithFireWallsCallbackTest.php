<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\AMQP\Callback\MultipleConsumersWithFireWallsCallback;
use App\DependencyInjection\IoC;
use App\AMQP\Firewall\FirewallInterface;
use PHPUnit\Framework\TestCase;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Tests\Mock\Consumer\AConsumer;
use Tests\Mock\Consumer\BConsumer;
use Tests\Mock\Consumer\MixedDto;
use Tests\Traits\IocSetupTrait;

class MultipleConsumersWithFireWallsCallbackTest extends TestCase
{
    use IocSetupTrait;

    public function setUp(): void
    {
        $this->setUpIocDependencyResolver();

        $logger = $this->createMock(LoggerInterface::class);

        IoC::resolve('Ioc.Register', 'Logger', static function (?string $loggerKey = null) use ($logger): LoggerInterface {
            return $logger;
        })();
    }

    public function tearDown(): void
    {
        $this->setUpIocDependencyResolver();
    }

    public function testSuccessfulConsuming(): void
    {
        $consumerA = $this->createMock(AConsumer::class);
        $consumerA->expects($this->once())
                    ->method('consume');
        $consumerB = $this->createMock(BConsumer::class);
        $consumerB->expects($this->once())
                    ->method('consume');
        $consumers = [
            $consumerA,
            $consumerB,
        ];

        $fireWallA = $this->createMock(FirewallInterface::class);
        $fireWallA->expects($this->once())
                    ->method('isAccessGranted')
                    ->willReturn(true);

        $fireWallB = $this->createMock(FirewallInterface::class);
        $fireWallB->expects($this->once())
                    ->method('isAccessGranted')
                    ->willReturn(true);

        $fireWallsByConsumer = [
            $consumerA::class => [
                $fireWallA,
            ],
            $consumerB::class => [
                $fireWallB,
            ],
        ];

        $serializerMock = $this->createMock(SerializerInterface::class);
        $serializerMock->expects($this->any())
                        ->method('deserialize')
                        ->willReturn(new MixedDto(['foo' => 'bar']));

        $callback = new MultipleConsumersWithFireWallsCallback(
            'queue1',
            $consumers,
            $fireWallsByConsumer,
            $serializerMock,
        );

        $amqpMessageMock = $this->createMock(AMQPMessage::class);

        ($callback)($amqpMessageMock);
    }

    public function testOneFireWallRestrictionIsEnough(): void
    {
        $consumer = $this->createMock(AConsumer::class);
        $consumer->expects($this->never())
                    ->method('consume');
        $consumers = [
            $consumer,
        ];

        $fireWallA = $this->createMock(FirewallInterface::class);
        $fireWallA->expects($this->once())
                    ->method('isAccessGranted')
                    ->willReturn(false);

        $fireWallB = $this->createMock(FirewallInterface::class);
        $fireWallB->expects($this->never())
                    ->method('isAccessGranted');

        $fireWallsByConsumer = [
            $consumer::class => [
                $fireWallA,
                $fireWallB,
            ],
        ];

        $serializerMock = $this->createMock(SerializerInterface::class);
        $serializerMock->expects($this->any())
                        ->method('deserialize')
                        ->willReturn(new MixedDto(['foo' => 'bar']));

        $callback = new MultipleConsumersWithFireWallsCallback(
            'queue1',
            $consumers,
            $fireWallsByConsumer,
            $serializerMock,
        );

        $amqpMessageMock = $this->createMock(AMQPMessage::class);

        ($callback)($amqpMessageMock);
    }

    public function testThatConsumersWorkIndependently(): void
    {
        $consumerA = $this->createMock(AConsumer::class);
        $consumerA->expects($this->never())
                    ->method('consume');
        $consumerB = $this->createMock(BConsumer::class);
        $consumerB->expects($this->once())
                    ->method('consume');
        $consumers = [
            $consumerA,
            $consumerB,
        ];

        $fireWallA = $this->createMock(FirewallInterface::class);
        $fireWallA->expects($this->once())
                    ->method('isAccessGranted')
                    ->willReturn(false);

        $fireWallB = $this->createMock(FirewallInterface::class);
        $fireWallB->expects($this->once())
                    ->method('isAccessGranted')
                    ->willReturn(true);

        $fireWallsByConsumer = [
            $consumerA::class => [
                $fireWallA,
            ],
            $consumerB::class => [
                $fireWallB,
            ],
        ];

        $serializerMock = $this->createMock(SerializerInterface::class);
        $serializerMock->expects($this->any())
                        ->method('deserialize')
                        ->willReturn(new MixedDto(['foo' => 'bar']));

        $callback = new MultipleConsumersWithFireWallsCallback(
            'queue1',
            $consumers,
            $fireWallsByConsumer,
            $serializerMock,
        );

        $amqpMessageMock = $this->createMock(AMQPMessage::class);

        ($callback)($amqpMessageMock);
    }
}
