<?php

declare(strict_types=1);

namespace App\Application;

interface ApplicationInterface
{
    public function bootstrap(): self;
    public function run(): self;
    public function getQueueCoroutinesAmount(): ?int;
}
