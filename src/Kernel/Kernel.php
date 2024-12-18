<?php

declare(strict_types=1);

namespace App\Kernel;

use App\Application\ApplicationInterface;
use App\Async\Runtime;
use App\Async\Async;
use App\Command\AsyncQueueHandlingCommand;
use App\Command\CommandInterface;
use App\CommandQueue\CommandQueue;
use App\CommandQueue\CommandQueueCoroutine;
use App\CommandQueue\LoopQueueStrategy;
use App\CommandQueue\CommandQueueInterface;
use App\CommandExceptionHandler\CommandExceptionHandler;
use App\DependencyInjection\DependencyResolver\ScopedDependencyResolver;
use App\DependencyInjection\IoC;
use App\Kernel\Exception\InvalidConfigurationException;
use Dotenv\Dotenv;
use Symfony\Component\Yaml\Yaml;
use Webmozart\Assert\Assert;

class Kernel
{
    /**
     * @var CommandInterface[]
     */
    private array $commandsToExecuteOnRun = [];

    public function __construct(
        private readonly ApplicationInterface $application,
    ) {
    }

    public function bootstrap(): self
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
        $dotenv->load();

        IoC::resolve('Ioc.DependencyResolver.Update', new ScopedDependencyResolver())->execute();

        $kernelConfig = Yaml::parseFile(__DIR__ . '/../../config/kernel.yml');

        $queueCoroutinesAmount = $this->parseQueueCoroutinesAmount($kernelConfig);

        $this->executeBootCommands(
            $this->parseBootCommandsConfig($kernelConfig)
        );

        $this->application->bootstrap();

        if (null !== $this->application->getQueueCoroutinesAmount()) {
            $queueCoroutinesAmount = $this->application->getQueueCoroutinesAmount();
        }

        $this->bootQueueCoroutines($queueCoroutinesAmount);

        return $this;
    }

    public function run(): self
    {
        $commandsToExecuteOnRun = $this->commandsToExecuteOnRun;
        unset($this->commandsToExecuteOnRun);
        $application = $this->application;

        (new Runtime(
            static function () use ($commandsToExecuteOnRun, $application): void {
                foreach ($commandsToExecuteOnRun as $command) {
                    $command->execute();
                }

                (new Async(
                    static function () use ($application): void {
                        $application->run();
                    }
                ))();
            }
        ))();

        return $this;
    }

    public function terminate(): self
    {
        return $this;
    }

    /**
     * @param array<string,mixed> $kernelConfig
     */
    private function parseQueueCoroutinesAmount(array $kernelConfig): int
    {
        if (!array_key_exists('queue_coroutines_amount', $kernelConfig)) {
            throw new InvalidConfigurationException("Missing required configuration parameter 'queue_coroutines_amount'");
        }

        Assert::integerish($kernelConfig['queue_coroutines_amount']);
        $queueCoroutinesAmount = (int)$kernelConfig['queue_coroutines_amount'];
        Assert::greaterThan($queueCoroutinesAmount, 0);

        return $queueCoroutinesAmount;
    }

    /**
     * @param array<string,mixed> $kernelConfig
     *
     * @return array<string,mixed>
     */
    private function parseBootCommandsConfig(array $kernelConfig): array
    {
        $bootCommandsConfig = array_key_exists('boot_commands', $kernelConfig) ? $kernelConfig['boot_commands'] : [];
        Assert::isArray($bootCommandsConfig);
        Assert::allSubclassOf(array_keys($bootCommandsConfig), CommandInterface::class);

        return $bootCommandsConfig;
    }

    /**
     * @param array<string,mixed> $bootCommandsConfig
     */
    private function executeBootCommands(array $bootCommandsConfig): void
    {
        foreach ($bootCommandsConfig as $commandClass => $commandConfig) {
            /** @var CommandInterface $command */
            $command = new $commandClass();
            $command->execute();
        }
    }

    private function bootQueueCoroutines(int $coroutinesAmount): void
    {
        $exceptionHandler = new CommandExceptionHandler();

        $queuesByCoroutineId = [];

        for ($i = 0; $i < $coroutinesAmount; ++$i) {
            $coroutineId = (string)$i;

            $queue = new CommandQueue($coroutineId);

            $coroutine = new CommandQueueCoroutine(
                $coroutineId,
                $queue,
                new LoopQueueStrategy($exceptionHandler),
                $exceptionHandler
            );
            $this->commandsToExecuteOnRun[] = new AsyncQueueHandlingCommand($coroutine);
            $queuesByCoroutineId[$coroutineId] = $queue;
        }

        $gameIdsByCoroutineId = [];

        IoC::resolve('Ioc.Register', 'Game.Queue.Get', static function (int $gameId) use ($queuesByCoroutineId, $gameIdsByCoroutineId): CommandQueueInterface {
            foreach ($gameIdsByCoroutineId as $coroutineId => $gameIds) {
                if (in_array($gameId, $gameIds)) {
                    return $queuesByCoroutineId[$coroutineId];
                }
            }

            $coroutineId = array_rand($queuesByCoroutineId);
            $gameIdsByCoroutineId[$coroutineId][] = $gameId;

            $queue = $queuesByCoroutineId[$coroutineId];

            return $queue;
        })();
    }
}
