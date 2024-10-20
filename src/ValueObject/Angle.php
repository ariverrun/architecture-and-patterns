<?php

declare(strict_types=1);

namespace App\ValueObject;

class Angle
{
    public function __construct(
        private readonly int $value,
    ) {
    }

    public static function plus(Angle $angleA, Angle $angleB): self
    {
        return new Angle(
            $angleA->value + $angleB->value
        );
    }

    public function toFloat(): float
    {
        return (float)$this->value;
    }
}
