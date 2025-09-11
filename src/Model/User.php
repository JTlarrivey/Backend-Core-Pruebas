<?php

namespace App\Model;

use App\Database\Connection;
use PDO;

class User
{
    public static function findAll(): array
    {
        $db = Connection::get();
        return $db->query("SELECT id,name,email,role,created_at FROM users")->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(int $id): ?array
    {
        $stmt = Connection::get()->prepare("SELECT id,name,email,role,created_at FROM users WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function create(string $name, string $email, string $passHash, string $role): int
    {
        $stmt = Connection::get()->prepare(
            "INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,?)"
        );
        $stmt->execute([$name, $email, $passHash, $role]);
        return (int) Connection::get()->lastInsertId();
    }

    public static function update(int $id, string $name, string $email, string $role): bool
    {
        $stmt = Connection::get()->prepare(
            "UPDATE users SET name=?,email=?,role=? WHERE id=?"
        );
        return $stmt->execute([$name, $email, $role, $id]);
    }

    public static function delete(int $id): bool
    {
        $stmt = Connection::get()->prepare("DELETE FROM users WHERE id=?");
        return $stmt->execute([$id]);
    }

    public static function checkCredentials(string $email, string $plain): ?array
    {
        $stmt = Connection::get()->prepare(
            "SELECT id,name,email,password_hash,role FROM users WHERE email=?"
        );
        $stmt->execute([$email]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$u || !password_verify($plain, $u['password_hash'])) {
            return null;
        }
        unset($u['password_hash']);
        return $u;
    }
}
