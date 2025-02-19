<?php

declare(strict_types=1);

namespace App\AMQP\Consumer;

class ConsumingContext
{
    private int $userId;

    public function getUserId(): ?int
    {
        return $this->userId ?? null;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }
}
