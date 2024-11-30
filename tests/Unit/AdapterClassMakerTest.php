<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\AutoGeneration\AdapterClassMaker;
use App\AutoGeneration\Exception\MethodsConflictException;
use App\Command\CommandInterface;
use App\DependencyInjection\IocInterface;
use App\DependencyInjection\Exception\DependencyNotFoundException;
use App\GameObject\MovingObjectInterface;
use App\GameObject\HavingFuelObjectInterface;
use App\GameObject\ObjectWithPropertiesContainerInterface;
use App\GameObject\RotatingObjectInterface;
use App\ValueObject\Angle;
use App\ValueObject\Vector;
use Tests\Mock\Interface\AInterface;
use Tests\Mock\Interface\BInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Closure;

class AdapterClassMakerTest extends TestCase
{
    private IocInterface $iocMock;
    private string $iocMockAlias = IocInterface::class . '_' . __CLASS__ . '_Mock';

    public function setUp(): void
    {
        $this->iocMock = new class () implements IocInterface {
            public static array $dependencies = [];

            public static function resolve(string $dependencyKey, mixed ...$args): mixed
            {
                if (isset(self::$dependencies[$dependencyKey])) {
                    return (self::$dependencies[$dependencyKey])(...$args);
                }
                throw new DependencyNotFoundException();
            }
        };

        $this->iocMock::$dependencies = [];

        $this->iocMock::$dependencies['Ioc.Register'] = function (string $dependencyKey, Closure $dependencyBuildingClosure): Closure {
            return function () use ($dependencyKey, $dependencyBuildingClosure): void {
                $this->iocMock::$dependencies[$dependencyKey] = $dependencyBuildingClosure;
            };
        };

        class_alias($this->iocMock::class, $this->iocMockAlias);
    }

    #[DataProvider('getTestCases')]
    public function testAdapterMaking(
        array $interfaces,
        ?string $generationException,
        array $dependenicesToSet,
        array $calls,
    ): void {

        $this->assertTrue(true);

        $adapterClassMaker = new AdapterClassMaker($this->iocMockAlias);

        if (null !== $generationException) {
            $this->expectException($generationException);
        }

        foreach ($dependenicesToSet as $dependency) {
            $this->iocMockAlias::resolve('Ioc.Register', $dependency['key'], $dependency['accessor'])();
        }

        $adapterClass = $adapterClassMaker->makeAdapterClass(... $interfaces);

        $adapteeMock = $this->createMock(ObjectWithPropertiesContainerInterface::class);

        $adapteeMock->properties = [];

        $adapteeMock->method('getProperty')
                    ->willReturnCallback(static function (string $id) use ($adapteeMock): mixed {
                        return $adapteeMock->properties[$id];
                    });

        $adapteeMock->method('setProperty')
                    ->willReturnCallback(static function (string $id, mixed $value) use ($adapteeMock): void {
                        $adapteeMock->properties[$id] = $value;
                    });


        $adapter = new $adapterClass($adapteeMock);

        if (null === $generationException) {
            foreach ($interfaces as $interface) {
                $this->assertInstanceOf($interface, $adapter);
            }

            foreach ($calls as $call) {
                if (null !== $call['exception']) {
                    $this->expectException($call['exception']);
                }

                $method = $call['method'];

                $result = $adapter->$method(... $call['args']);

                if (null === $call['exception']) {
                    $this->assertEqualsCanonicalizing($call['expectedResult'], $result);
                }
            }
        }
    }

    /**
     * @return array{
     *  interfaces: array,
     *  generationException: string|null,
     *  dependenicesToSet: array{key: string, accessor: Closure},
     *  calls: array{
     *      method: string,
     *      args: mixed[],
     *      expectedResult: mixed,
     *      exception: string|null
     *  }[]
     * }[]
     */
    public static function getTestCases(): array
    {
        return [
            [
                'interfaces' => [
                    RotatingObjectInterface::class,
                ],
                'generationException' => null,
                'dependenicesToSet' => [
                    [
                        'key' => 'Spaceship.Operations.' . RotatingObjectInterface::class . '.Angle.get',
                        'accessor' => static function (): Angle {
                            return new Angle(5);
                        },
                    ],
                ],
                'calls' => [
                    [
                        'method' => 'getAngle',
                        'args' => [],
                        'expectedResult' => new Angle(5),
                        'exception' => null,
                    ],
                ],
            ],
            [
                'interfaces' => [
                    RotatingObjectInterface::class,
                ],
                'generationException' => null,
                'dependenicesToSet' => [
                    [
                        'key' => 'Spaceship.Operations.' . RotatingObjectInterface::class . '.Angle.set',
                        'accessor' => function (ObjectWithPropertiesContainerInterface $object, Angle $angle): CommandInterface {

                            $setPropertyMockCommand = new class ($object, $angle) implements CommandInterface {
                                public function __construct(
                                    private readonly ObjectWithPropertiesContainerInterface $object,
                                    private readonly Angle $angle,
                                ) {
                                }

                                public function execute(): void
                                {
                                    $this->object->setProperty('angle', $this->angle);
                                }
                            };

                            return $setPropertyMockCommand;
                        },
                    ],
                    [
                        'key' => 'Spaceship.Operations.' . RotatingObjectInterface::class . '.Angle.get',
                        'accessor' => static function (ObjectWithPropertiesContainerInterface $object): Angle {
                            return $object->getProperty('angle');
                        },
                    ],
                ],
                'calls' => [
                    [
                        'method' => 'setAngle',
                        'args' => [new Angle(5)],
                        'expectedResult' => null,
                        'exception' => null,
                    ],
                    [
                        'method' => 'getAngle',
                        'args' => [],
                        'expectedResult' => new Angle(5),
                        'exception' => null,
                    ],
                ],
            ],
            [
                'interfaces' => [
                    MovingObjectInterface::class,
                    RotatingObjectInterface::class,
                ],
                'generationException' => null,
                'dependenicesToSet' => [
                    [
                        'key' => 'Spaceship.Operations.' . MovingObjectInterface::class . '.Location.get',
                        'accessor' => static function (): Vector {
                            return new Vector(2, 4);
                        },
                    ],
                    [
                        'key' => 'Spaceship.Operations.' . RotatingObjectInterface::class . '.AngularVelocity.get',
                        'accessor' => static function (): Angle {
                            return new Angle(8);
                        },
                    ],
                ],
                'calls' => [
                    [
                        'method' => 'getLocation',
                        'args' => [],
                        'expectedResult' => new Vector(2, 4),
                        'exception' => null,
                    ],
                    [
                        'method' => 'getAngularVelocity',
                        'args' => [],
                        'expectedResult' => new Angle(8),
                        'exception' => null,
                    ],
                ],
            ],
            [
                'interfaces' => [
                    HavingFuelObjectInterface::class,
                    AInterface::class,
                ],
                'generationException' => null,
                'dependenicesToSet' => [
                    [
                        'key' => 'Spaceship.Operations.' . HavingFuelObjectInterface::class . '.FuelAmount.get',
                        'accessor' => static function (): int {
                            return 100;
                        },
                    ],
                    [
                        'key' => 'Spaceship.Operations.' . AInterface::class . '.voidAMethodWithArgs',
                        'accessor' => function (ObjectWithPropertiesContainerInterface $object, int $a1, string $a2): CommandInterface {
                            return new class () implements CommandInterface {
                                public function execute(): void
                                {
                                }
                            };
                        },
                    ],
                ],
                'calls' => [
                    [
                        'method' => 'getFuelAmount',
                        'args' => [],
                        'expectedResult' => 100,
                        'exception' => null,
                    ],
                    [
                        'method' => 'voidAMethodWithArgs',
                        'args' => [1, 'a'],
                        'expectedResult' => null,
                        'exception' => null,
                    ],
                ],
            ],
            [
                'interfaces' => [
                    AInterface::class,
                    BInterface::class,
                ],
                'generationException' => MethodsConflictException::class,
                'dependenicesToSet' => [],
                'calls' => [],
            ],
            [
                'interfaces' => [
                    RotatingObjectInterface::class,
                ],
                'generationException' => null,
                'dependenicesToSet' => [],
                'calls' => [
                    [
                        'method' => 'getAngle',
                        'args' => [],
                        'expectedResult' => null,
                        'exception' => DependencyNotFoundException::class,
                    ],
                ],
            ],
        ];
    }
}
