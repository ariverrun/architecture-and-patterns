<?php

declare(strict_types=1);

namespace App\Command;

use Webmozart\Assert\Assert;

class CreateUnionCommand implements CommandInterface, GameObjectOperationCommandInterface
{
    private readonly string $unionName;
    /**
     * @param array{
     *  unionName: string,
     * } $args
     */
    public function __construct(array $args)
    {
        Assert::keyExists($args, 'unionName');
        $unionName = trim($args['unionName']);
        Assert::stringNotEmpty($unionName);
        $this->unionName = $unionName;
    }

    public function execute(): void
    {
        /*
         * @todo Create new Game Union with name provided in constructor
         */
    }
}
