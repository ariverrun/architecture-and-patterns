<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use App\DependencyInjection\Exception\DependencyNotFoundException;

interface IocInterface
{
    /**
     * @throws DependencyNotFoundException
     */
    public static function resolve(string $dependencyKey, mixed ...$args): mixed;
}
