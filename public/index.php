<?php

declare(strict_types=1);

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}
require APP_ROOT . '/app/autoload.php';

// En DEV mostramos errores; en PROD los mandamos al log
if ((getenv('APP_ENV') ?: 'prod') !== 'prod') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    error_reporting(E_ALL);
}

use App\Router\Router;

(new Router())->dispatch();
