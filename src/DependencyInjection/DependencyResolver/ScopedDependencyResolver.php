<?php

declare(strict_types=1);

namespace App\DependencyInjection\DependencyResolver;

use App\AutoGeneration\AdapterClassMaker;
use App\Command\CreateNewScopeCommand;
use App\Command\SetCurrentScopeCommand;
use App\DependencyInjection\Exception\DependencyNotFoundException;
use App\DependencyInjection\Exception\ScopeAlreadyExistsException;
use App\DependencyInjection\Exception\ScopeNotFoundException;
use App\GameObject\ObjectWithPropertiesContainerInterface;
use Closure;

class ScopedDependencyResolver implements DependencyResolverInterface, ScopesSupportingDependencyResolver
{
    private const ROOT_SCOPE_ID = '';

    private array $scopes = [];
    private ?string $currentScopeId = self::ROOT_SCOPE_ID;

    public function __construct()
    {
        $this->fillScopesWithDefaultDependencies();
    }

    public function resolve(string $dependencyKey, mixed ...$args): mixed
    {
        if (isset($this->scopes[$this->currentScopeId][$dependencyKey])) {
            return ($this->scopes[$this->currentScopeId][$dependencyKey])(...$args);
        }

        if (isset($this->scopes[self::ROOT_SCOPE_ID][$dependencyKey])) {
            return ($this->scopes[self::ROOT_SCOPE_ID][$dependencyKey])(...$args);
        }

        throw new DependencyNotFoundException("Dependency '{$dependencyKey}' not found");
    }

    public function getCurrentScopeId(): string
    {
        return $this->currentScopeId;
    }

    public function setCurrentScopeId(string $scopeId): void
    {
        if (false === array_key_exists($scopeId, $this->scopes)) {
            throw new ScopeNotFoundException();
        }

        $this->currentScopeId = $scopeId;
    }

    public function createNewScope(string $scopeId): void
    {
        if (true === array_key_exists($scopeId, $this->scopes)) {
            throw new ScopeAlreadyExistsException();
        }
        $this->scopes[$scopeId] = [];
    }

    private function fillScopesWithDefaultDependencies(): void
    {
        $this->scopes[self::ROOT_SCOPE_ID]['Ioc.Register'] = function (string $dependencyKey, Closure $dependencyBuildingClosure): Closure {
            return function () use ($dependencyKey, $dependencyBuildingClosure): void {
                $this->scopes[$this->currentScopeId][$dependencyKey] = $dependencyBuildingClosure;
            };
        };

        $this->scopes[self::ROOT_SCOPE_ID]['Ioc.Scope.SetCurrent'] = function (string $scopeId): SetCurrentScopeCommand {
            return new SetCurrentScopeCommand($this, $scopeId);
        };

        $this->scopes[self::ROOT_SCOPE_ID]['Ioc.Scope.New'] = function (string $scopeId): CreateNewScopeCommand {
            return new CreateNewScopeCommand($this, $scopeId);
        };

        $adapterClassMaker = new AdapterClassMaker();

        $this->scopes[self::ROOT_SCOPE_ID]['Adapter'] = static function (
            ObjectWithPropertiesContainerInterface $object,
            string ...$interfaces,
        ) use ($adapterClassMaker): object {
            $adapterClass = $adapterClassMaker->makeAdapterClass(...$interfaces);

            return new $adapterClass($object);
        };
    }
}
