<?php

declare(strict_types=1);

namespace App\AMQP\Callback;

use App\AMQP\Consumer\AMQPConsumerInterface;
use App\DependencyInjection\IoC;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use ReflectionMethod;
use RuntimeException;
use Throwable;

class MultipleConsumersCallback implements AMQPCallbackInterface
{
    private readonly array $consumersExpectedArgClasses;
    private readonly LoggerInterface $logger;

    /**
     * @param AMQPConsumerInterface[] $consumers
     */
    public function __construct(
        private readonly string $queueName,
        private readonly array $consumers,
        private readonly SerializerInterface $serializer,
    ) {
        $consumersExpectedArgClasses = [];

        foreach ($consumers as $consumer) {
            $consumerClass = $consumer::class;

            $consumerClass = $consumer::class;
            $consumeMethodReflector = new ReflectionMethod($consumerClass, 'consume');

            $parameters = $consumeMethodReflector->getParameters();

            $consumersExpectedArgClasses[$consumerClass] = $parameters[0]->getType()->getName();
        }

        $this->consumersExpectedArgClasses = $consumersExpectedArgClasses;

        $this->logger = IoC::resolve('Logger');
    }

    public function __invoke(AMQPMessage $message): void
    {
        foreach ($this->consumers as $consumer) {
            $consumerClass = $consumer::class;
            $expectedArgClass = $this->consumersExpectedArgClasses[$consumerClass] ?? null;

            if (null === $expectedArgClass) {
                throw new RuntimeException("Arg type for '{$consumerClass}' not found");
            }

            try {
                $argDto = $this->serializer->deserialize($message->getBody(), $expectedArgClass, 'json');
                $consumer->consume($argDto);
            } catch (Throwable $e) {
                $this->logger->error('AMQP message consuming failed', ['queue' => $this->queueName, 'message' => $message->getBody(), 'exception' => $e]);
            }
        }
    }
}
