<?php

declare(strict_types=1);

namespace App\Service;

interface AuthServiceInterface
{
    /**
     * @param int[] $userIds
     */
    public function createGameSession(int $gameId, array $userIds): int;
}
