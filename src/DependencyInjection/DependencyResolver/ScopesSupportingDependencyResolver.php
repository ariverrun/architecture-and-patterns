<?php

declare(strict_types=1);

namespace App\DependencyInjection\DependencyResolver;

interface ScopesSupportingDependencyResolver
{
    public function getCurrentScopeId(): string;

    public function setCurrentScopeId(string $scopeId): void;

    public function createNewScope(string $scopeId): void;
}
