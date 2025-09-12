<?php

declare(strict_types=1);

namespace App\Router;

use App\Controller\AuthController;       // ← OJO: singular, como tu AuthController
use App\Controller\UserController;       // ← idem
use App\Controller\ApiController;        // ← idem
use App\Middleware\AuthenticationMiddleware;

final class Router
{
    public function dispatch(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        // Tomar el path real (htaccess reescribe todo a public/index.php)
        $uri      = $_SERVER['REQUEST_URI'] ?? '/';
        $pathPart = parse_url($uri, PHP_URL_PATH) ?? '/';
        $rawPath  = trim($pathPart, '/');                  // p.ej. 'login' o '' si es raíz
        $routeKey = $method . ' ' . ($rawPath === '' ? '/' : $rawPath);

        // Preflight CORS (si aplica)
        if ($method === 'OPTIONS') {
            http_response_code(204);
            return;
        }

        header('Content-Type: application/json; charset=utf-8');

        // Log en dev
        if ((getenv('APP_ENV') ?: 'prod') !== 'prod') {
            error_log('[core-router] ' . $routeKey);
        }

        switch ($routeKey) {
            case 'POST login':
                $data = json_decode(file_get_contents('php://input'), true) ?? [];
                (new AuthController())->login($data);
                return;

            case 'POST logout':
                (new AuthController())->logout();
                return;

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
                (new UserController())->create($_POST ?? []); // si posteás JSON, cambialo como en login
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
                echo json_encode(['error' => 'Ruta no encontrada', 'path' => ($rawPath === '' ? '/' : $rawPath)]);
                return;
        }
    }
}
