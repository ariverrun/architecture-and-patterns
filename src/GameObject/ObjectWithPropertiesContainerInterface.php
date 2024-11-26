<?php

declare(strict_types=1);

namespace App\GameObject;

interface ObjectWithPropertiesContainerInterface
{
    public function getProperty(string $id): mixed;
    public function setProperty(string $id, mixed $value): void;
}
