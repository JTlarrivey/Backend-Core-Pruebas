<?php

namespace App\Controller;

use App\Model\User;
use App\Auth\AuthenticationMiddleware;
use Exception;

class UserController
{
    /**
     * Lista todos los usuarios.
     */
    public function list(): void
    {
        header('Content-Type: application/json');
        AuthenticationMiddleware::requireLogin();

        try {
            $users = User::findAll();
            echo json_encode($users);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo obtener la lista de usuarios']);
        }
    }

    /**
     * Devuelve un solo usuario por ID.
     *
     * @param array $req Espera ['id' => int]
     */
    public function get(array $req): void
    {
        header('Content-Type: application/json');
        AuthenticationMiddleware::requireLogin();

        $id = isset($req['id']) ? (int)$req['id'] : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de usuario inválido']);
            return;
        }

        try {
            $user = User::find($id);
            if (!$user) {
                http_response_code(404);
                echo json_encode(['error' => 'Usuario no encontrado']);
                return;
            }
            echo json_encode($user);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener el usuario']);
        }
    }

    /**
     * Crea un nuevo usuario.
     *
     * @param array $req Espera ['name','email','password','role']
     */
    public function create(array $req): void
    {
        header('Content-Type: application/json');
        AuthenticationMiddleware::requireLogin();

        // Validación básica
        foreach (['name', 'email', 'password', 'role'] as $field) {
            if (empty($req[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "Falta el campo $field"]);
                return;
            }
        }

        try {
            $id = User::create(
                $req['name'],
                $req['email'],
                password_hash($req['password'], PASSWORD_DEFAULT),
                $req['role']
            );
            http_response_code(201);
            echo json_encode(['status' => 'created', 'id' => $id]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo crear el usuario']);
        }
    }

    /**
     * Actualiza un usuario existente.
     *
     * @param array $req Espera ['id','name','email','role']
     */
    public function update(array $req): void
    {
        header('Content-Type: application/json');
        AuthenticationMiddleware::requireLogin();

        $id = isset($req['id']) ? (int)$req['id'] : 0;
        if ($id <= 0 || empty($req['name']) || empty($req['email']) || empty($req['role'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos inválidos para actualización']);
            return;
        }

        try {
            $ok = User::update($id, $req['name'], $req['email'], $req['role']);
            if ($ok) {
                echo json_encode(['status' => 'updated']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Usuario no encontrado']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar el usuario']);
        }
    }

    /**
     * Elimina un usuario.
     *
     * @param array $req Espera ['id']
     */
    public function delete(array $req): void
    {
        header('Content-Type: application/json');
        AuthenticationMiddleware::requireLogin();

        $id = isset($req['id']) ? (int)$req['id'] : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de usuario inválido']);
            return;
        }

        try {
            $ok = User::delete($id);
            if ($ok) {
                echo json_encode(['status' => 'deleted']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Usuario no encontrado']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar el usuario']);
        }
    }
}
