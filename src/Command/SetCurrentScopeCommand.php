<?php

declare(strict_types=1);

namespace App\Command;

use App\DependencyInjection\DependencyResolver\ScopesSupportingDependencyResolver;

class SetCurrentScopeCommand implements CommandInterface
{
    public function __construct(
        private readonly ScopesSupportingDependencyResolver $dependencyResolver,
        private readonly string $scopeId,
    ) {
    }

    public function execute(): void
    {
        $this->dependencyResolver->setCurrentScopeId($this->scopeId);
    }
}