<?php

namespace App\Model;

use PDO;

final class User
{
    private static function pdo(): PDO
    {
        $host = getenv('DB_HOST') ?: 'localhost';
        $port = (int)(getenv('DB_PORT') ?: 3306);
        $db   = getenv('DB_NAME') ?: '';
        $usr  = getenv('DB_USER') ?: '';
        $pwd  = getenv('DB_PASS') ?: '';
        $dsn  = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

        $pdo = new PDO($dsn, $usr, $pwd, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    }

    public static function checkCredentials(string $email, string $password): ?array
    {
        try {
            $pdo = self::pdo();
            $st  = $pdo->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = ? LIMIT 1');
            $st->execute([$email]);
            $row = $st->fetch();
            if (!$row) return null;

            // Si tus hashes son bcrypt de PHP:
            if (!password_verify($password, $row['password_hash'])) {
                return null;
            }

            return [
                'id'   => (int)$row['id'],
                'name' => $row['name'],
                'role' => $row['role'] ?: 'viewer',
            ];
        } catch (\Throwable $e) {
            if ((getenv('APP_ENV') ?: 'prod') !== 'prod') {
                error_log('[core-db] ' . $e->getMessage());
            }
            throw $e; // lo captura AuthController y devuelve 500 JSON
        }
    }
}
