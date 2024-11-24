<?php

declare(strict_types=1);

namespace App\Command;

use App\DependencyInjection\DependencyResolver\ScopesSupportingDependencyResolver;

class CreateNewScopeCommand implements CommandInterface
{
    public function __construct(
        private readonly ScopesSupportingDependencyResolver $dependencyResolver,
        private readonly string $scopeId,
    ) {
    }

    public function execute(): void
    {
        $this->dependencyResolver->createNewScope($this->scopeId);
    }
}