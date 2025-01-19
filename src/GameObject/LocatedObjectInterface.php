<?php

declare(strict_types=1);

namespace App\GameObject;

use App\ValueObject\Vector;

interface LocatedObjectInterface
{
    public function getLocation(): Vector;
}
