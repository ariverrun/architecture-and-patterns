<?php

declare(strict_types=1);

namespace App\Command;

use App\DependencyInjection\IoC;
use App\GameObject\HavingFuelObjectInterface;
use App\GameObject\MovingObjectInterface;
use App\GameObject\ObjectWithPropertiesContainerInterface;
use App\ValueObject\Vector;

class RegisterGameObjectOperationsCommand implements CommandInterface
{
    public function execute(): void
    {
        IoC::resolve(
            'Ioc.Register',
            'Spaceship.Operations.' . HavingFuelObjectInterface::class . '.FuelAmount.get',
            static function (ObjectWithPropertiesContainerInterface $object): int {
                return $object->getProperty('fuelAmount');
            }
        )();

        IoC::resolve(
            'Ioc.Register',
            'Spaceship.Operations.' . HavingFuelObjectInterface::class . '.FuelConsumptionVelocity.get',
            static function (ObjectWithPropertiesContainerInterface $object): int {
                return $object->getProperty('fuelConsumptionVelocity');
            }
        )();

        IoC::resolve(
            'Ioc.Register',
            'Spaceship.Operations.' . MovingObjectInterface::class . '.Location.get',
            static function (ObjectWithPropertiesContainerInterface $object): Vector {
                return $object->getProperty('location');
            }
        )();

        IoC::resolve(
            'Ioc.Register',
            'Spaceship.Operations.' . MovingObjectInterface::class . '.Velocity.get',
            static function (ObjectWithPropertiesContainerInterface $object): Vector {
                return $object->getProperty('velocity');
            }
        )();

        IoC::resolve(
            'Ioc.Register',
            'Spaceship.Operations.' . MovingObjectInterface::class . '.Location.set',
            static function (ObjectWithPropertiesContainerInterface $object, Vector $location): SetObjectPropertyCommand {
                return new SetObjectPropertyCommand($object, 'location', $location);
            }
        )();

        IoC::resolve(
            'Ioc.Register',
            'Spaceship.Operations.' . HavingFuelObjectInterface::class . '.FuelAmount.set',
            static function (ObjectWithPropertiesContainerInterface $object, int $fuelAmount): SetObjectPropertyCommand {
                return new SetObjectPropertyCommand($object, 'fuelAmount', $fuelAmount);
            }
        )();
    }
}
