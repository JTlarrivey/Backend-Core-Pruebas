<?php

declare(strict_types=1);

// --- Boot básico ---
define('APP_ROOT', dirname(__DIR__));                 // /var/www/html
require APP_ROOT . '/app/autoload.php';               // tu autoloader (sin Composer)

// Si estás detrás de proxy/Render, marcá HTTPS correctamente
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

// Health check directo (para Render)
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if ($path === '/health') {
    header('Content-Type: text/plain; charset=utf-8');
    echo 'ok';
    exit;
}

if ((getenv('APP_ENV') ?: 'prod') !== 'prod') {
    ini_set('display_errors', '1');    // mostrar errores en dev
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

// --- Carga de variables ---
// Recomendado: setear todo por variables de entorno en Render.
// Fallback opcional a .env si no hay APP_ENV (útil en dev)
if (!getenv('APP_ENV') && is_file(APP_ROOT . '/.env')) {
    foreach (file(APP_ROOT . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if ($line[0] === '#') continue;
        [$k, $v] = array_pad(explode('=', $line, 2), 2, '');
        $k = trim($k);
        $v = trim($v, " \t\n\r\0\x0B\"'");
        if ($k !== '') {
            putenv("$k=$v");
            $_ENV[$k] = $v;
        }
    }
}

// --- Router ---
use App\Router\Router;   // ← asegurate que la clase exista en app/Router/Router.php con namespace App\Router

try {
    (new Router())->dispatch();
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'internal_error',
        'message' => 'Ups, algo salió mal',
    ]);
}
