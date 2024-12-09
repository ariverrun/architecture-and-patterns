<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\QuadraticEquationSolver;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class QuadraticEquationSolverTest extends TestCase
{
    private QuadraticEquationSolver $solver;

    public function setUp(): void
    {
        $this->solver = new QuadraticEquationSolver();
    }

    /**
     * @dataProvider getTestCases
     * @param float[] $expected
     */
    public function testSolver(float $a, float $b, float $c, ?array $expected, ?string $exception): void
    {
        if (null !== $exception) {
            $this->expectException($exception);
        }

        $roots = $this->solver->solve($a, $b, $c);

        $this->assertEqualsCanonicalizing($expected, $roots);
    }

    /**
     * @return array[{"a":float,"b":float,"c":float,"expected":float[]|null,"exception":string|null}]
     */
    public static function getTestCases(): array
    {
        return [          
            /**
             * x^2+1 = 0 has no roots
             */
            [
                'a' => 1.0,
                'b' => 0.0,
                'c' => 1.0,
                'expected' => [],
                'exception' => null,
            ],
            /**
             * x^2-1 = 0 has two roots
             */
            [
                'a' => 1.0,
                'b' => 0.0,
                'c' => -1.0,
                'expected' => [1.0, -1.0],
                'exception' => null,
            ],
            /**
             * x^2+2x+1 = 0 has one roots
             */
            [
                'a' => 1.0,
                'b' => 2.0,
                'c' => 1.0,
                'expected' => [-1.0],
                'exception' => null,
            ],
            /**
             * Zero A coefficient throws exception
             */
            [
                'a' => PHP_FLOAT_EPSILON,
                'b' => 1.0,
                'c' => 1.0,
                'expected' => null,
                'exception' => InvalidArgumentException::class,
            ],
            /**
             * Discriminant with abs value less than epsilon is considered as 0 
             */              
            [
                'a' => 2.5E-9,
                'b' => 1.5E-8,
                'c' => 2.0E-9,
                'expected' => [-2.9999999999999996],
                'exception' => null,
            ],
            /**
             * Infinite coefficient throws exception
             */            
            [
                'a' => 1.0,
                'b' => INF,
                'c' => 1.0,
                'expected' => null,
                'exception' => InvalidArgumentException::class,
            ],
            /**
             * Negative infinite coefficient throws exception
             */            
            [
                'a' => -INF,
                'b' => 2.0,
                'c' => 1.0,
                'expected' => null,
                'exception' => InvalidArgumentException::class,
            ],
            /**
             * Not a number coefficient throws exception
             */                      
            [
                'a' => 1.0,
                'b' => 1.0,
                'c' => NAN,
                'expected' => null,
                'exception' => InvalidArgumentException::class,
            ],               
        ];
    }
}