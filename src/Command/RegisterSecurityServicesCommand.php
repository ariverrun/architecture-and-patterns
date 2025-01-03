<?php

declare(strict_types=1);

namespace App\Command;

use App\DependencyInjection\IoC;
use App\Service\JWTDecoderInterface;
use App\Service\LcobucciJWTAdapter;

class RegisterSecurityServicesCommand implements CommandInterface
{
    public function execute(): void
    {
        $publicKeyPath = IoC::resolve('Env.JWT_PUBLIC_KEY_PATH');
        $passPhrase = IoC::resolve('Env.JWT_PASSPHRASE');

        $jwtService = new LcobucciJWTAdapter($publicKeyPath, $passPhrase);

        IoC::resolve('Ioc.Register', 'Jwt.Decoder', static function () use ($jwtService): JWTDecoderInterface {
            return $jwtService;
        })();
    }
}
