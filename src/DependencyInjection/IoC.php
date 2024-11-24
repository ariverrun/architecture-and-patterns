<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use App\DependencyInjection\Exception\DependencyNotFoundException;
use App\DependencyInjection\DependencyResolver\DependencyResolverInterface;
use App\DependencyInjection\DependencyResolver\InitialDependencyResolver;

final class IoC
{
    private static DependencyResolverInterface $dependencyResolver;

    /**
     * @throws DependencyNotFoundException
     */
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
