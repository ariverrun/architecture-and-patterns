<?php

declare(strict_types=1);

namespace App\Kernel;

use App\AMQP\Callback\MultipleConsumersCallback;
use App\AMQP\Consumer\AMQPConsumerInterface;
use App\Kernel\Exception\InvalidConfigurationException;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Webmozart\Assert\Assert;
use RuntimeException;
use Throwable;

class AMQPKernel extends AbstractKernel
{
    private readonly array $queuesConfig;
    public function bootstrap(): self
    {
        parent::bootstrap();

        $amqpConfig = Yaml::parseFile(__DIR__ . '/../../config/amqp.yml');

        if (empty($amqpConfig['queues']) || !is_array($amqpConfig['queues'])) {
            throw new InvalidConfigurationException("Config has to have 'queues' array declaration");
        }

        $queuesConfig = $amqpConfig['queues'];
        
        foreach ($queuesConfig as $queueName => $queueConfig) {
            if (empty($queueConfig['consumers']) || !is_array($queueConfig['consumers'])) {
                throw new InvalidConfigurationException("Queue config has to have 'consumers' array declaration");
            }

            $consumers = [];

            foreach ($queueConfig['consumers'] as $consumerClass) {
                Assert::subclassOf($consumerClass, AMQPConsumerInterface::class);
                $consumers[] = new $consumerClass();
            }

            $callback = new MultipleConsumersCallback(
                $consumers, 
                new Serializer([
                    new ArrayDenormalizer(),
                    new JsonSerializableNormalizer(),
                    new ObjectNormalizer(),
                ], [
                    new JsonEncoder()
                ]),
            );

            $queuesConfig[$queueName]['callback'] = $callback;
        }

        $this->queuesConfig = $queuesConfig;

        return $this;
    }

    public function run(): void
    {
        $connection = new AMQPStreamConnection(
            $_ENV['RABBITMQ_HOST'], 
            $_ENV['RABBITMQ_PORT'], 
            $_ENV['RABBITMQ_USER'], 
            $_ENV['RABBITMQ_PASSWORD'],
        );

        $channel = $connection->channel();

        foreach ($this->queuesConfig as $queueName => $queueConfig) {
            $channel->queue_declare($queueName, false, false, false, false);

            $callback = $queueConfig['callback'] ?? null;

            if (null === $callback) {
                throw new RuntimeException("Callback for '{$queueName}' queue not found");
            }

            $channel->basic_consume($queueName, '', false, true, false, false, $callback);
        }

        try {
            $channel->consume();
        } catch (Throwable $exception) {
            dump($exception);
        }
    }
}