<?php

namespace App\Database;

use PDO;

class Connection
{
    private static ?PDO $instance = null;

    public static function get(): PDO
    {
        if (!self::$instance) {
            $dsn  = $_ENV['DB_DSN'] ?? '';
            $user = $_ENV['DB_USER'] ?? '';
            $pass = $_ENV['DB_PASS'] ?? '';

            self::$instance = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
        }

        return self::$instance;
    }
}
