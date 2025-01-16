<?php

declare(strict_types=1);

namespace App\CommandQueue;

enum CommandQueueCoroutineStatus: int
{
    case NEW = 0;
    case RUNNING = 1;
    case COMPLETED = 2;
}
