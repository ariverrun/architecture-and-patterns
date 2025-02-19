<?php

declare(strict_types=1);

namespace App\Consumer;

class GameOperationMessageDto
{
    public function __construct(
        public readonly int $gameId,
        public readonly string $operationId,
        public readonly mixed $args,
        public readonly ?string $objectId = null,
    ) {
    }
}
