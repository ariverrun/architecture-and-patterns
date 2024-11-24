<?php

declare(strict_types=1);

namespace App\Command;

use App\DependencyInjection\DependencyResolver\DependencyResolverInterface;
use Closure;

class UpdateIocDependencyResolverCommand implements CommandInterface
{
    public function __construct(
        private readonly DependencyResolverInterface $dependencyResolver,
        private readonly Closure $updateIocResolverClosure,
    ) {
    }

    public function execute(): void
    {
        ($this->updateIocResolverClosure)($this->dependencyResolver);
    }
}
