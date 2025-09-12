<?php

namespace App\Controller;

use App\Model\User;

class AuthController
{
    /**
     * Endpoint de login: valida credenciales y devuelve contexto de usuario.
     *
     * @param array $data  Datos recibidos (POST): ['email', 'password']
     * @return void
     */
    public function login(array $data): void
    {
        header('Content-Type: application/json');

        $email    = $data['email']    ?? '';
        $password = $data['password'] ?? '';

        // 1) Verificar credenciales en la tabla users
        $user = User::checkCredentials($email, $password);

        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Credenciales inválidas']);
            return;
        }

        // 2) Construir contexto de usuario
        $permissions = $this->loadPermissionsForRole($user['role']);

        // 3) Devolver JSON con todo lo necesario para poblar la sesión
        echo json_encode([
            'valid' => true,
            'user'  => [
                'user_id'     => $user['id'],
                'name'        => $user['name'],
                'role'        => $user['role'],
                'permissions' => $permissions,
                'layout_pref' => $user['role'] === 'admin' ? 'admin_dashboard' : 'viewer_home',
            ],
        ]);
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
