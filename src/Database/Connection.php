<?php

namespace App\Database;

use PDO;
use PDOException;

class Connection
{
    private static ?PDO $instance = null;

    public static function get(): PDO
    {
        if (!self::$instance) {
            // Intentamos leer primero el DSN completo (por compatibilidad con entornos previos)
            $dsn = $_ENV['DB_DSN'] ?? '';

            // Si no está definido, construimos uno con las variables separadas
            if (empty($dsn)) {
                $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
                $db   = $_ENV['DB_NAME'] ?? '';
                $port = $_ENV['DB_PORT'] ?? '3306';
                $charset = 'utf8mb4';

                if (empty($db)) {
                    throw new \RuntimeException('No se definió la variable DB_NAME en el entorno (.env).');
                }

                $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";
            }

            $user = $_ENV['DB_USER'] ?? 'root';
            $pass = $_ENV['DB_PASS'] ?? '';

            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                throw new \RuntimeException('Error al conectar a la base de datos: ' . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
