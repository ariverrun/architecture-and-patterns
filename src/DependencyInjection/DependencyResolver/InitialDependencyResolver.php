<?php

declare(strict_types=1);

namespace App\DependencyInjection\DependencyResolver;

use App\Command\UpdateIocDependencyResolverCommand;
use App\DependencyInjection\Exception\DependencyNotFoundException;
use Closure;

class InitialDependencyResolver implements DependencyResolverInterface
{
    private const UPDATE_IOC_DEPENDENCY_RESOLVER_KEY = 'Ioc.DependencyResolver.Update';

    public function __construct(
        private readonly Closure $updateIocResolverClosure,
    ) {
    }

    public function resolve(string $dependencyKey, mixed ...$args): mixed
    {
        if (self::UPDATE_IOC_DEPENDENCY_RESOLVER_KEY === $dependencyKey) {
            return new UpdateIocDependencyResolverCommand(
                $args[0],
                $this->updateIocResolverClosure
            );
        }

        throw new DependencyNotFoundException();
    }
}
