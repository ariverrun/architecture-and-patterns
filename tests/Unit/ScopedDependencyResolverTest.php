<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DependencyInjection\DependencyResolver\ScopedDependencyResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

class ScopedDependencyResolverTest extends TestCase
{
    public function testCreateAndSetScope(): void
    {
        $dependencyResolver = new ScopedDependencyResolver();

        $scopeId = '1';

        $dependencyResolver->resolve('Ioc.Scope.New', $scopeId)->execute();
        $dependencyResolver->resolve('Ioc.Scope.SetCurrent', $scopeId)->execute();

        $this->assertEquals($scopeId, $dependencyResolver->getCurrentScopeId());
    }

    #[DataProvider('getTestDependencies')]
    public function testDependencyRegistering(string $key, mixed $value): void
    {
        $dependencyResolver = new ScopedDependencyResolver();

        $dependencyResolver->resolve('Ioc.Register', $key, static function () use ($value): mixed {
            return $value;
        })();

        $this->assertEqualsCanonicalizing($value, $dependencyResolver->resolve($key));
    }

    /**
     * @return array{key: string, value: mixed}[]
     */
    public static function getTestDependencies(): array
    {
        return [
            [
                'key' => 'dep1',
                'value' => 'a',
            ],
            [
                'key' => 'dep2',
                'value' => 1,
            ],
            [
                'key' => 'dep3',
                'value' => new stdClass(),
            ],
            [
                'key' => 'dep4',
                'value' => static function (): bool {
                    return true;
                },
            ],
        ];
    }
}
