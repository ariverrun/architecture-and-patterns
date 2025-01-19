<?php

declare(strict_types=1);

namespace App\ValueObject;

use Stringable;

class GameObjectIdentifier implements Stringable
{
    public function __construct(
        private readonly int $value,
    ) {
    }

    public function __toString(): string
    {
        return (string)$this->value;
    }

    public function equals(GameObjectIdentifier $gameIdToCompare): bool
    {
        return $this->value === $gameIdToCompare->value;
    }
}
