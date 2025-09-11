<?php

use App\Config\Env;
use App\Router;


require __DIR__ . '/../vendor/autoload.php';

Env::load(__DIR__ . '/..');    // carga .env
(new Router())->dispatch();
