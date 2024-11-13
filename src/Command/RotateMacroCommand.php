<?php

declare(strict_types=1);

namespace App\Command;

use App\GameObject\MovingObjectInterface;
use App\GameObject\RotatingObjectInterface;
use App\ValueObject\Vector;

class RotateMacroCommand extends MacroCommand
{
    /**
     * @var CommandInterface[]
     */
    protected readonly array $commands;

    public function __construct(RotatingObjectInterface $obj)
    {
        $commands = [
            new RotateCommand($obj),
        ];

        if ($obj instanceof MovingObjectInterface) {
            $commands[] = new ChangeVelocityCommand(
                $obj,
                Vector::rotate($obj->getVelocity(), $obj->getAngle()),
            );
        }

        $this->commands = $commands;
    }
}
