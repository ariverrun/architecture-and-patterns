<?php

declare(strict_types=1);

namespace App\Command;

use App\DependencyInjection\IoC;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class RegisterSerializationServicesCommand implements CommandInterface
{
    public function execute(): void
    {
        $serializer = new Serializer(
            [
                new ArrayDenormalizer(),
                new JsonSerializableNormalizer(),
                new ObjectNormalizer(),
            ],
            [
                new JsonEncoder(),
            ],
        );

        IoC::resolve('Ioc.Register', 'Serializer', static function () use ($serializer): SerializerInterface {
            return $serializer;
        })();
    }
}
