<?php

declare(strict_types=1);

namespace App\Repository;

interface GameRepositoryInterface
{
    public function getSessionIdByGameId(int $gameId): int;
}
