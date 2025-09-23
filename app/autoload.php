<?php

declare(strict_types=1);

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}


// Cargar .env si existe (y no pisar variables ya existentes del entorno
$envFile = APP_ROOT . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) continue;
        [$k, $v] = array_map('trim', explode('=', $line, 2));
        $cur = getenv($k);
        if ($cur === false || $cur === '') {
            putenv("$k=$v");
            $_ENV[$k] = $v;
            $_SERVER[$k] = $v;
        }
    }
}

spl_autoload_register(function (string $class): void {
    $prefix  = 'App\\';
    $baseDir = APP_ROOT . '/app/';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) return;

    $relative = substr($class, strlen($prefix));
    $file     = $baseDir . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) require $file;
});

if (!function_exists('cfg')) {
    /**
     * Devuelve el valor de una variable según APP_ENV.
     * - Si APP_ENV=prod => usa $prodKey (fallback a $localKey)
     * - Si APP_ENV!=prod => usa $localKey (fallback a $prodKey)
     */
    function cfg(string $prodKey, string $localKey, ?string $default = null): ?string
    {
        $env  = getenv('APP_ENV') ?: 'prod';
        $keys = ($env === 'prod') ? [$prodKey, $localKey] : [$localKey, $prodKey];
        foreach ($keys as $k) {
            $v = getenv($k);
            if ($v !== false && $v !== '') return $v;
        }
        return $default;
    }
}
