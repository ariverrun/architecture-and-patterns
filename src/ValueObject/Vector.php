<?php

declare(strict_types=1);

namespace App\ValueObject;

class Vector
{
    private const FLOAT_EPSILON = 0.0000000001;
    public function __construct(
        private readonly float $x,
        private readonly float $y,
    ) {
    }

    public static function plus(Vector $vectorA, Vector $vectorB): self
    {
        return new Vector(
            $vectorA->x + $vectorB->x,
            $vectorA->y + $vectorB->y
        );
    }

    public static function rotate(Vector $vector, Angle $angle): self
    {
        $angleValue = $angle->toFloat();

        $angleValueCos = cos($angleValue);
        $angleValueSin = sin($angleValue);

        return new Vector(
            $vector->x * $angleValueCos - $vector->y * $angleValueSin,
            $vector->x * $angleValueSin + $vector->y * $angleValueCos,
        );
    }

    public function equals(Vector $vector): bool
    {
        return abs($this->x - $vector->x) < self::FLOAT_EPSILON && abs($this->y - $vector->y) < self::FLOAT_EPSILON;
    }
}
