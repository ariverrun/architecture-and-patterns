<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\AMQP\AMQPApplication;
use App\Kernel\Kernel;

(new Kernel(new AMQPApplication()))
    ->bootstrap()
    ->run()
    ->terminate();
