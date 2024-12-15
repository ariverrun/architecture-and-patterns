<?php

declare(strict_types=1);

namespace App\Kernel;

use App\CommandQueue\CommandQueue;
use App\CommandQueue\CommandQueueInterface;
use App\DependencyInjection\IoC;
use App\DependencyInjection\DependencyResolver\ScopedDependencyResolver;
use App\GameObject\ObjectWithPropertiesContainerInterface;
use Dotenv\Dotenv;

abstract class AbstractKernel
{
    public function bootstrap(): self
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
        $dotenv->load();

        (Ioc::resolve('Ioc.DependencyResolver.Update', new ScopedDependencyResolver()))->execute();

        $queue = new CommandQueue();

        Ioc::resolve('Ioc.Register', 'Game.Queue.Get', static function(int $gameId) use($queue): CommandQueueInterface {
            return $queue;
        })();

        $object = new class() implements ObjectWithPropertiesContainerInterface {
            private array $properties = [];
            public function getProperty(string $id): mixed
            {
                return $this->properties[$id];
            }
            public function setProperty(string $id, mixed $value): void
            {
                $this->properties[$id] = $value;
            }
        };

        Ioc::resolve('Ioc.Register', 'Game.Object.Get', static function(string $objectId) use($object): ObjectWithPropertiesContainerInterface {
            return $object;
        })();        

        return $this;
    }

    abstract public function run(): void;
}