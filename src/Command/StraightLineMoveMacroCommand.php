<?php

declare(strict_types=1);

namespace App\Command;

use App\GameObject\HavingFuelObjectInterface;
use App\GameObject\MovingObjectInterface;

class StraightLineMoveMacroCommand extends MacroCommand implements GameObjectOperationCommandInterface
{
    /**
     * @var CommandInterface[]
     */
    protected readonly array $commands;

    public function __construct(HavingFuelObjectInterface & MovingObjectInterface $obj)
    {
        $this->commands = [
            new CheckFuelCommand($obj),
            new MoveCommand($obj),
            new BurnFuelCommand($obj),
        ];
    }
}
