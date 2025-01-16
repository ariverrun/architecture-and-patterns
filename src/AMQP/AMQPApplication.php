<?php

declare(strict_types=1);

namespace App\AMQP;

use App\AMQP\Callback\MultipleConsumersWithFireWallsCallback;
use App\AMQP\Consumer\AMQPConsumerInterface;
use App\AMQP\Firewall\FirewallInterface;
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

    /**
     * @var array<string, array{class: string, consumers: string[]}>
     */
    private readonly array $firewallsConfig;

    private readonly int $queueCoroutinesAmount;
    private readonly LoggerInterface $logger;

    /**
     * @var array<string, FirewallInterface>
     */
    private array $fireWalls = [];

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

        $this->queuesConfig = $appConfig['firewalls'];

        if (!array_key_exists('firewalls', $appConfig)) {
            throw new InvalidConfigurationException("Missing required configuration parameter 'queuefirewallss'");
        }

        Assert::allIsArray($appConfig['firewalls']);

        $this->firewallsConfig = $appConfig['firewalls'];

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

            $fireWallsByConsumer = [];

            foreach ($queueConfig['consumers'] as $consumerClass) {

                $consumerFileWalls = $this->getFireWallsByConsumer($consumerClass);
                $fireWallsByConsumer[$consumerClass] = $consumerFileWalls;

                Assert::subclassOf($consumerClass, AMQPConsumerInterface::class);
                $consumers[] = new $consumerClass();
            }

            $callback = new MultipleConsumersWithFireWallsCallback(
                $queueName,
                $consumers,
                $fireWallsByConsumer,
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

    /**
     * @return FirewallInterface[]
     */
    private function getFireWallsByConsumer(string $consumerClass): array
    {
        $firewalls = [];

        foreach ($this->firewallsConfig as $fireWallConfig) {
            if (in_array($consumerClass, $fireWallConfig['consumers'])) {
                $fireWallClass = $fireWallConfig['class'];

                if (!isset($this->fireWalls[$fireWallClass])) {
                    $this->fireWalls[$fireWallClass] = new $fireWallClass();
                }

                $firewalls[] = $this->fireWalls[$fireWallClass];
            }
        }

        return $firewalls;
    }
}
