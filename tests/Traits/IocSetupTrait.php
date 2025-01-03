<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\DependencyInjection\DependencyResolver\InitialDependencyResolver;
use App\DependencyInjection\DependencyResolver\ScopedDependencyResolver;
use App\DependencyInjection\IoC;
use ReflectionClass;

trait IocSetupTrait
{
    protected function setUpIocDependencyResolver(): void
    {
        $iocReflector = new ReflectionClass(IoC::class);

        $dependencyResolverProperty = $iocReflector->getProperty('dependencyResolver');

        $dependencyResolverProperty->setAccessible(true);

        if (
            !$dependencyResolverProperty->isInitialized()
            || $dependencyResolverProperty->getValue() instanceof InitialDependencyResolver
        ) {
            IoC::resolve('Ioc.DependencyResolver.Update', new ScopedDependencyResolver())->execute();
        } else {
            $dependencyResolver = $dependencyResolverProperty->getValue();

            if ($dependencyResolver instanceof ScopedDependencyResolver) {
                $dependencyResolverReflector = new ReflectionClass($dependencyResolver::class);

                $scopesProperty = $dependencyResolverReflector->getProperty('scopes');

                $scopesProperty->setAccessible(true);

                $scopesProperty->setValue($dependencyResolver, []);

                $fillScopesWithDefaultDependenciesMethod = $dependencyResolverReflector->getMethod('fillScopesWithDefaultDependencies');

                $fillScopesWithDefaultDependenciesMethod->setAccessible(true);

                $fillScopesWithDefaultDependenciesMethod->invoke($dependencyResolver);

                $fillScopesWithDefaultDependenciesMethod->setAccessible(false);

                $scopesProperty->setAccessible(false);
            }
        }

        $dependencyResolverProperty->setAccessible(false);
    }
}
