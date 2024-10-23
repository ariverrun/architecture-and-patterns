<?php

declare(strict_types=1);

namespace App\ValueObject;

use InvalidArgumentException;

class Vector
{
    /**
     * @var float[]
     */
    private readonly array $values;

    public function __construct(float ...$values)
    {
        $this->values = $values;
    }

    public static function plus(Vector $vectorA, Vector $vectorB): self
    {
        if (count($vectorA->values) !== count($vectorB->values)) {
            throw new InvalidArgumentException('Addition can not be performed for vectors with different dimensions amount');
        }

        $newValues = [];

        foreach ($vectorA->values as $n => $aValue) {
            $bValue = $vectorB->values[$n];

            $newValues[] = $aValue + $bValue;
        }

        return new Vector(...$newValues);
    }
}
