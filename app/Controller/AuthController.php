<?php

namespace App\Controller;

use App\Model\User;

class AuthController
{
    public function login(array $data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $email    = trim($data['email'] ?? '');
            $password = (string)($data['password'] ?? '');
            if ($email === '' || $password === '') {
                http_response_code(400);
                echo json_encode(['error' => 'missing_fields']);
                return;
            }

            $user = \App\Model\User::checkCredentials($email, $password);
            if (!$user) {
                http_response_code(401);
                echo json_encode(['error' => 'Credenciales inválidas']);
                return;
            }

            $permissions = $this->loadPermissionsForRole($user['role']);

            echo json_encode([
                'user_id'     => $user['id'],
                'name'        => $user['name'],
                'role'        => $user['role'],
                'permissions' => $permissions,
                'layout_pref' => $user['role'] === 'admin' ? 'admin_dashboard' : 'viewer_home',
            ]);
        } catch (\Throwable $e) {
            $isProd = (getenv('APP_ENV') ?: 'prod') === 'prod';
            error_log('[core-login] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(
                $isProd
                    ? ['error' => 'internal_error', 'message' => 'Ups, algo salió mal']
                    : ['error' => 'internal_error', 'detail' => $e->getMessage()]
            );
        }
    }

    public function logout(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if (session_status() !== \PHP_SESSION_ACTIVE) {
            session_start();
        }
        session_unset();
        session_destroy();
        echo json_encode(['status' => 'logged_out']);
        return;
    }

    public function verifySession(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if (session_status() !== \PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (empty($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado']);
            return;
        }
        echo json_encode($_SESSION['user']);
        return;
    }

    private function loadPermissionsForRole(string $role): array
    {
        return $role === 'admin' ? ['view', 'edit', 'delete', 'manage_users']
            : ($role === 'viewer' ? ['view'] : []);
    }
}
