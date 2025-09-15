<?php

declare(strict_types=1);

namespace App\Router;

use App\Controller\AuthController;
use App\Controller\UserController;
use App\Controller\ApiController;

final class Router
{
    public function dispatch(): void
    {
        $method   = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri      = $_SERVER['REQUEST_URI'] ?? '/';
        $pathPart = parse_url($uri, PHP_URL_PATH) ?? '/';
        $rawPath  = trim($pathPart, '/'); // '' si raíz
        $routeKey = $method . ' ' . ($rawPath === '' ? '/' : $rawPath);

        if ($method === 'OPTIONS') {
            http_response_code(204);
            return;
        }

        header('Content-Type: application/json; charset=utf-8');

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

            case 'GET verify_session':
                (new AuthController())->verifySession();
                return;

            case 'GET metrics':
                (new ApiController())->getRawMetrics();
                return;

            case 'GET users':
                (new UserController())->list();
                return;

            case 'GET user':
                (new UserController())->get($_GET ?? []);
                return;

            case 'POST user':
                (new UserController())->create($_POST ?? []);
                return;

            case 'PUT user':
                $d = json_decode(file_get_contents('php://input'), true) ?? [];
                (new UserController())->update($d);
                return;

            case 'DELETE user':
                $d = json_decode(file_get_contents('php://input'), true) ?? [];
                (new UserController())->delete($d);
                return;

            case 'GET /':
                echo json_encode(['ok' => true]);
                return;

            default:
                http_response_code(404);
                echo json_encode(['error' => 'Ruta no encontrada', 'path' => ($rawPath === '' ? '/' : $rawPath)]);
                return;
        }
    }
}
