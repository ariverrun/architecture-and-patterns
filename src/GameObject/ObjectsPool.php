<?php

declare(strict_types=1);

namespace App\GameObject;

use RuntimeException;

class ObjectsPool
{
    /**
     * @var array<string,ObjectWithPropertiesContainerInterface>
     */
    private array $objects = [];

    public function get(string $id): ObjectWithPropertiesContainerInterface
    {
        if (!isset($this->objects[$id])) {
            throw new RuntimeException("Object with id '{$id}' is not found");
        }

        return $this->objects[$id];
    }

    public function set(string $id, ObjectWithPropertiesContainerInterface $object): void
    {
        $this->objects[$id] = $object;
    }
}
