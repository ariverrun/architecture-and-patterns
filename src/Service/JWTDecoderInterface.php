<?php

declare(strict_types=1);

namespace App\Service;

use Exception;

interface JWTDecoderInterface
{
    /**
     * @throws Exception
     */
    public function decode(string $token): array;
}
