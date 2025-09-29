<?php
header('Content-Type: application/json; charset=utf-8');

$envs = [
    'APP_ENV' => getenv('APP_ENV') ?: null,
    'DB_HOST' => getenv('DB_HOST') ? 'SET' : 'MISSING',
    'DB_NAME' => getenv('DB_NAME') ? 'SET' : 'MISSING',
    'DB_USER' => getenv('DB_USER') ? 'SET' : 'MISSING',
    'DB_PASS' => getenv('DB_PASS') ? 'SET' : 'MISSING',
];

$exts = [
    'pdo_mysql' => extension_loaded('pdo_mysql'),
    'mysqli'    => extension_loaded('mysqli'),
    'json'      => extension_loaded('json'),
    'mbstring'  => extension_loaded('mbstring'),
];

$diag = ['ok' => true, 'envs' => $envs, 'exts' => $exts];

// (Opcional) prueba rápida de conexión PDO si están los envs
try {
    if (getenv('DB_HOST') && getenv('DB_NAME') && getenv('DB_USER')) {
        $dsn = 'mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_NAME') . ';charset=utf8mb4';
        $pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS') ?: '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->query('SELECT 1');
        $diag['db'] = 'OK';
    } else {
        $diag['db'] = 'SKIPPED';
    }
} catch (Throwable $e) {
    $diag['db'] = 'FAIL';
    $diag['db_error'] = $e->getMessage();
}

echo json_encode($diag);
