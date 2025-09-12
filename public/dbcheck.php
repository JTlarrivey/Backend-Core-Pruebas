<?php
header('Content-Type: text/plain; charset=utf-8');
try {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        getenv('DB_HOST'),
        getenv('DB_PORT') ?: '3306',
        getenv('DB_NAME')
    );
    new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "db ok";
} catch (Throwable $e) {
    http_response_code(500);
    echo "db error: " . $e->getMessage();
}
