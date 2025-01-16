<?php

declare(strict_types=1);

namespace Tests\Mock\Consumer;

class MixedDto
{
    public function __construct(
        public readonly mixed $data,
    ) {
    }
}
