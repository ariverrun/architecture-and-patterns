<?php

declare(strict_types=1);

namespace Tests\Mock\Interface;

interface CallableInterface
{
    public function __invoke(mixed ... $args): void;
}
