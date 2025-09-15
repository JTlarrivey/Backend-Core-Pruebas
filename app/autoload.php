<?php

declare(strict_types=1);

if (!defined('APP_ROOT')) {
    // /var/www/html
    define('APP_ROOT', dirname(__DIR__));
}

spl_autoload_register(function (string $class): void {
    $prefix  = 'App\\';
    $baseDir = APP_ROOT . '/app/'; // app/Controller, app/Model, app/Router, etc.

    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));              // p.ej. Controller\AuthController
    $file     = $baseDir . str_replace('\\', '/', $relative) . '.php';

    if (is_file($file)) {
        require $file;
    }
});
