<?php

declare(strict_types=1);

namespace App\AMQP\Callback;

use App\AMQP\Consumer\AMQPConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Serializer\SerializerInterface;
use ReflectionMethod;
use RuntimeException;
use Throwable;

class MultipleConsumersCallback implements AMQPCallbackInterface
{
    private readonly array $consumersExpectedArgClasses;
    /**
     * @param AMQPConsumerInterface[] $consumers
     */
    public function __construct(
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
                dump($e);
            }
        }
    }
}