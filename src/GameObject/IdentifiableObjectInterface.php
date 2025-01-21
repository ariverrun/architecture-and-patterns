<?php

declare(strict_types=1);

namespace App\GameObject;

use App\ValueObject\GameObjectIdentifier;

interface IdentifiableObjectInterface
{
    public function getId(): GameObjectIdentifier;
}
