<?php

declare(strict_types=1);

namespace App\GameField;

interface AreaInterface
{
    /**
     * @return AreaInterface[]
     */
    public function getAdjacentAreas(): array;
}
