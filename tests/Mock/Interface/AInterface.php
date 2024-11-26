<?php

declare(strict_types=1);

namespace Tests\Mock\Interface;

interface AInterface
{
    public function voidAMethodWithArgs(int $a1, string $a2): void;
    public function conflictMethod(): void;
}
