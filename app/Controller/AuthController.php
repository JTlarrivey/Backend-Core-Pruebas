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

            $permissions = $this->loadPermissionsForRole($user['role'] ?? '');

            echo json_encode([
                'user_id'     => $user['id'],
                'name'        => $user['name'],
                'role'        => $user['role'],
                'permissions' => $permissions,
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

    /**
     * Endpoint de logout: destruye la sesión.
     *
     * @return void
     */
    public function logout(): void
    {
        header('Content-Type: application/json');
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        session_unset();
        session_destroy();

        echo json_encode([
            'status'  => 'logged_out',
            'message' => 'Sesión cerrada correctamente'
        ]);
    }

    /**
     * Endpoint de verificación de sesión: comprueba si hay usuario autenticado.
     *
     * @return void
     */
    public function verifySession(): void
    {
        header('Content-Type: application/json');
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode([
                'error'   => 'No autenticado',
                'message' => 'La sesión no está activa o expiró.'
            ]);
            return;
        }

        // Devolver contexto de usuario en sesión
        echo json_encode($_SESSION['user']);
    }

    /**
     * Carga permisos asociados a un rol.
     *
     * @param string $role
     * @return array
     */
    private function loadPermissionsForRole(string $role): array
    {
        switch ($role) {
            case 'admin':
                return ['view', 'edit', 'delete', 'manage_users'];
            case 'viewer':
                return ['view'];
            default:
                return [];
        }
    }
}
