<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Command\CreateNewScopeCommand;
use App\DependencyInjection\DependencyResolver\ScopesSupportingDependencyResolver;
use PHPUnit\Framework\TestCase;

class CreateNewScopeCommandTest extends TestCase
{
    public function testScopeSetting(): void
    {
        $dependencyResolver = $this->createMock(ScopesSupportingDependencyResolver::class);

        $scopeId = '1';

        $dependencyResolver->expects($this->once())
                            ->method('createNewScope')
                            ->with($scopeId);

        (new CreateNewScopeCommand($dependencyResolver, $scopeId))->execute();
    }
}
