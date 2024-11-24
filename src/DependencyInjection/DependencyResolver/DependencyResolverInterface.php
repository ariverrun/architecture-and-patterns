<?php

declare(strict_types=1);

namespace App\DependencyInjection\DependencyResolver;

use App\DependencyInjection\Exception\DependencyNotFoundException;

interface DependencyResolverInterface
{
    /**
     * @throws DependencyNotFoundException
     */
    public function resolve(string $dependencyKey, mixed ...$args): mixed;
}
