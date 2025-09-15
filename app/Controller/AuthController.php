<?php

namespace App\Controller;

use App\Model\User;

class AuthController
{
    public function login(array $data): void
    {
        try {
            header('Content-Type: application/json; charset=utf-8');

            $email    = $data['email']    ?? '';
            $password = $data['password'] ?? '';
            if ($email === '' || $password === '') {
                http_response_code(400);
                echo json_encode(['error' => 'missing_fields']);
                return;
            }

            $user = User::checkCredentials($email, $password);

            if (!$user) {
                http_response_code(401);
                echo json_encode(['error' => 'Credenciales inválidas']);
                return;
            }

            echo json_encode([
                'user_id'     => $user['id'],
                'name'        => $user['name'],
                'role'        => $user['role'],
                'permissions' => $this->loadPermissionsForRole($user['role'] ?? ''),
                'layout_pref' => ($user['role'] ?? '') === 'admin' ? 'admin_dashboard' : 'viewer_home',
            ]);
            return;
        } catch (\Throwable $e) {
            if ((getenv('APP_ENV') ?: 'prod') !== 'prod') {
                error_log('[core-login-ex] ' . $e->getMessage() . ' @' . $e->getFile() . ':' . $e->getLine());
            }
            http_response_code(500);
            echo json_encode(['error' => 'internal_error', 'message' => 'Ups, algo salió mal']);
            return;
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
