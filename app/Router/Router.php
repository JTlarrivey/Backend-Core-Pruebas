<?php

declare(strict_types=1);

namespace App\Router;

use App\Controller\AuthController;
use App\Controller\UserController;
use App\Controller\ApiController;
use App\Middleware\AuthenticationMiddleware;

final class Router
{
    public function dispatch(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        // Tomamos el path real que llegó (htaccess reescribe a index.php)
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        $path = ltrim($path, '/'); // '' si es '/'

        // Preflight CORS opcional (si manejás CORS acá)
        if ($method === 'OPTIONS') {
            http_response_code(204);
            return;
        }

        header('Content-Type: application/json; charset=utf-8');

        $routeKey = "{$method} {$path}";

        switch ($routeKey) {
            case 'POST login':
                $data = json_decode(file_get_contents('php://input'), true) ?? [];
                (new AuthController())->login($data);
                return;

            case 'POST logout':
                (new AuthController())->logout();
                return;


            case 'GET ':
            case 'GET /':
                echo json_encode(['ok' => true]);
                return;

            case 'GET verify_session':
                (new AuthController())->verifySession();
                return;

            case 'GET users':
                AuthenticationMiddleware::requireLogin();
                (new UserController())->list();
                return;

            case 'GET user':
                AuthenticationMiddleware::requireLogin();
                (new UserController())->get($_GET ?? []);
                return;

            case 'POST user':
                AuthenticationMiddleware::requireLogin();
                (new UserController())->create($_POST ?? []);
                return;

            case 'PUT user':
                AuthenticationMiddleware::requireLogin();
                $data = json_decode(file_get_contents('php://input'), true) ?? [];
                (new UserController())->update($data);
                return;

            case 'DELETE user':
                AuthenticationMiddleware::requireLogin();
                $data = json_decode(file_get_contents('php://input'), true) ?? [];
                (new UserController())->delete($data);
                return;

            case 'GET metrics':
                (new ApiController())->getRawMetrics();
                return;

            default:
                http_response_code(404);
                echo json_encode(['error' => 'Ruta no encontrada', 'path' => $path]);
                return;
        }
    }
}
