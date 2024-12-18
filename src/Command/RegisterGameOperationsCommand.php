<?php

declare(strict_types=1);

namespace App\Command;

use App\DependencyInjection\IoC;
use App\GameObject\ObjectWithPropertiesContainerInterface;
use Webmozart\Assert\Assert;
use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;

class RegisterGameOperationsCommand implements CommandInterface
{
    /**
     * @param array<string,array{
     *  class: string|null,
     *  objectPath: string
     * }> $gameOperationsConfig
     */
    public function __construct(
        private readonly array $gameOperationsConfigList,
    ) {
    }

    public function execute(): void
    {
        $callbacksByOperationId = [];

        foreach ($this->gameOperationsConfigList as $operationId => $operationCfg) {
            $commandClass = $operationCfg['class'] ?? $operationId;
            Assert::stringNotEmpty($commandClass);
            Assert::subclassOf($commandClass, GameObjectOperationCommandInterface::class);

            $commandReflector = new ReflectionClass($commandClass);

            $callbacksByOperationId[$operationId] = static function (ObjectWithPropertiesContainerInterface $object, mixed $args) use ($commandClass, $commandReflector): CommandInterface {
                $objectConstructorParam = $commandReflector->getConstructor()->getParameters()[0];
                $hasArgsParam = isset($commandReflector->getConstructor()->getParameters()[1]);
                $interfacesObjectHasImplement = array_map(function (ReflectionNamedType $type): string {
                    return $type->getName();
                }, $objectConstructorParam->getType()->getTypes());
                $adapter = IoC::resolve('Adapter', $object, ...$interfacesObjectHasImplement);

                return $hasArgsParam ? new $commandClass($adapter, $args) : new $commandClass($adapter);
            };
        }

        IoC::resolve('Ioc.Register', 'Game.Operation.Get', static function (string $operationId, ObjectWithPropertiesContainerInterface $object, mixed $args) use ($callbacksByOperationId): CommandInterface {
            if (isset($callbacksByOperationId[$operationId])) {
                return ($callbacksByOperationId[$operationId])($object, $args);
            }
            throw new RuntimeException("Game operation '{$operationId}' is not found");
        })();
    }
}
