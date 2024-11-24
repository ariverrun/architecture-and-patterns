<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Command\SetCurrentScopeCommand;
use App\DependencyInjection\DependencyResolver\ScopesSupportingDependencyResolver;
use PHPUnit\Framework\TestCase;

class SetCurrentScopeCommandTest extends TestCase
{
    public function testScopeSetting(): void
    {
        $dependencyResolver = $this->createMock(ScopesSupportingDependencyResolver::class);

        $scopeId = '1';

        $dependencyResolver->expects($this->once())
                            ->method('setCurrentScopeId')
                            ->with($scopeId);

        (new SetCurrentScopeCommand($dependencyResolver, $scopeId))->execute();
    }
}
