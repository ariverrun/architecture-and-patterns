<?php

declare(strict_types=1);

namespace App\ValueObject;

class Vector
{
    public function __construct(
        protected readonly float $x,
        protected readonly float $y,
    ) {
    }

    public static function plus(Vector $vectorA, Vector $vectorB): self
    {
        return new Vector(
            $vectorA->x + $vectorB->x,
            $vectorA->y + $vectorB->y
        );
    }

    public function toInt(): int
    {
        return (int)($this->x / $this->y);
    }
}
