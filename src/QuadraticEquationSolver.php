<?php

declare(strict_types=1);

namespace App;

use InvalidArgumentException;

class QuadraticEquationSolver
{
    private const FLOAT_EPSILON = PHP_FLOAT_EPSILON * 10;

    /**
     * @return float[]
     */
    public function solve(float $a, float $b, float $c): array
    {
        if ($this->isZero($a)) {
            throw new InvalidArgumentException('A coefficient can not be a zero');
        }

        foreach (func_get_args() as $coeff) {
            if (is_infinite($coeff)) {
                throw new InvalidArgumentException('Coefficient can not be infinite');
            }

            if (is_nan($coeff)) {
                throw new InvalidArgumentException('Coefficient can not be not a number value');
            }
        }

        $d = $this->computeDiscriminant($a, $b, $c);

        if ($this->isZero($d)) {
            return [
                0 - ($b / (2*$a))
            ];
        } elseif ($d > 0) {
            return [
                (0 - $b - sqrt($d)) / (2 * $a),
                (0 - $b + sqrt($d)) / (2 * $a)
            ];
        }

        return [];
    }

    private function computeDiscriminant(float $a, float $b, float $c): float
    {
        return pow($b,2) - 4 * $a * $c;
    }

    private function isZero(float $val): bool
    {
        return abs($val) <= self::FLOAT_EPSILON;
    }
}