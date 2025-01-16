<?php

declare(strict_types=1);

namespace App\Service;

use Lcobucci\Clock\FrozenClock;
use Lcobucci\JWT\JwtFacade;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Validation\Constraint;
use DateTimeImmutable;

class LcobucciJWTAdapter implements JWTDecoderInterface
{
    private readonly Key $key;
    private readonly JwtFacade $jwtFacade;

    public function __construct(
        string $publicKeyPath,
        string $passPhrase,
    ) {
        $this->key = InMemory::file($publicKeyPath, $passPhrase);

        $this->jwtFacade = new JwtFacade();
    }

    public function decode(string $token): array
    {
        $token = $this->jwtFacade->parse(
            $token,
            new Constraint\SignedWith(new Sha256(), $this->key),
            new Constraint\LooseValidAt(
                new FrozenClock(new DateTimeImmutable('now'))
            )
        );

        return $token->claims()->all();
    }
}
