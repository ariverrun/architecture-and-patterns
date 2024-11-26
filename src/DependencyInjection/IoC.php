<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use App\DependencyInjection\DependencyResolver\DependencyResolverInterface;
use App\DependencyInjection\DependencyResolver\InitialDependencyResolver;

final class IoC implements IocInterface
{
    private static DependencyResolverInterface $dependencyResolver;

    public static function resolve(string $dependencyKey, mixed ...$args): mixed
    {
        return self::getDependencyResolver()->resolve($dependencyKey, ...$args);
    }

    private static function getDependencyResolver(): DependencyResolverInterface
    {
        if (!isset(self::$dependencyResolver)) {

            $dependencyResolverUpdateFunc = static function (DependencyResolverInterface $dependencyResolver): void {
                self::$dependencyResolver = $dependencyResolver;
            };

            self::$dependencyResolver = new InitialDependencyResolver($dependencyResolverUpdateFunc);
        }

        return self::$dependencyResolver;
    }
}
