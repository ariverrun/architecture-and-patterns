<?php

declare(strict_types=1);

namespace App\AMQP;

use App\AMQP\Callback\MultipleConsumersCallback;
use App\AMQP\Consumer\AMQPConsumerInterface;
use App\Application\ApplicationInterface;
use App\Application\Exception\InvalidConfigurationException;
use App\DependencyInjection\IoC;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Yaml\Yaml;
use Webmozart\Assert\Assert;
use Throwable;

class AMQPApplication implements ApplicationInterface
{
    /**
     * @var array<string, array{
     *  consumers: string[]
     * }>
     */
    private readonly array $queuesConfig;

    private readonly int $queueCoroutinesAmount;
    private readonly LoggerInterface $logger;

    public function bootstrap(): self
    {
        $appConfig = Yaml::parseFile(__DIR__ . '/../../config/application/amqp.yml');

        if (array_key_exists('queue_coroutines_amount', $appConfig)) {
            Assert::integerish($appConfig['queue_coroutines_amount']);
            $queueCoroutinesAmount = (int)$appConfig['queue_coroutines_amount'];
            Assert::greaterThan($queueCoroutinesAmount, 0);
            $this->queueCoroutinesAmount = $queueCoroutinesAmount;
        }

        if (!array_key_exists('queues', $appConfig)) {
            throw new InvalidConfigurationException("Missing required configuration parameter 'queues'");
        }

        Assert::allIsArray($appConfig['queues']);

        $this->queuesConfig = $appConfig['queues'];

        $this->logger = IoC::resolve('Logger');

        return $this;
    }

    public function run(): self
    {
        $connection = new AMQPStreamConnection(
            $_ENV['RABBITMQ_HOST'],
            $_ENV['RABBITMQ_PORT'],
            $_ENV['RABBITMQ_USER'],
            $_ENV['RABBITMQ_PASSWORD'],
            $_ENV['RABBITMQ_VHOST'],
        );

        $channel = $connection->channel();

        /** @var SerializerInterface $serializer */
        $serializer = IoC::resolve('Serializer');

        foreach ($this->queuesConfig as $queueName => $queueConfig) {

            $consumers = [];

            foreach ($queueConfig['consumers'] as $consumerClass) {
                Assert::subclassOf($consumerClass, AMQPConsumerInterface::class);
                $consumers[] = new $consumerClass();
            }

            $callback = new MultipleConsumersCallback(
                $queueName,
                $consumers,
                $serializer,
            );

            $channel->queue_declare($queueName, false, false, false, false);

            $channel->basic_consume($queueName, '', false, true, false, false, $callback);
        }

        try {
            $channel->consume();
        } catch (Throwable $e) {
            $this->logger->error('AMQP channel consuming failed', ['exception' => $e]);
        }

        return $this;
    }

    public function getQueueCoroutinesAmount(): ?int
    {
        return $this->queueCoroutinesAmount ?? null;
    }
}
