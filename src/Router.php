<?php

namespace App;

use App\Controller\AuthController;
use App\Controller\UserController;
use App\Controller\ApiController;
use App\Auth\AuthenticationMiddleware;

class Router
{
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = ltrim($_GET['path'] ?? '', '/');
        $routeKey = "$method $path";
        header('Content-Type: application/json');

        // TEMPORAL: debug para confirmar
        // var_dump($routeKey); exit;

        switch ($routeKey) {
            case 'POST login':
                $data = json_decode(file_get_contents('php://input'), true);
                (new AuthController())->login($data);
                break;

            case 'POST logout':
                (new AuthController())->logout();
                break;

            case 'GET verify_session':
                (new AuthController())->verifySession();
                break;

            case 'GET users':
                AuthenticationMiddleware::requireLogin();
                (new UserController())->list();
                break;

            case 'GET user':
                AuthenticationMiddleware::requireLogin();
                (new UserController())->get($_GET ?? []);
                break;

            case 'POST user':
                AuthenticationMiddleware::requireLogin();
                (new UserController())->create($_POST ?? []);
                break;

            case 'PUT user':
                AuthenticationMiddleware::requireLogin();
                $data = json_decode(file_get_contents('php://input'), true);
                (new UserController())->update($data);
                break;

            case 'DELETE user':
                AuthenticationMiddleware::requireLogin();
                $data = json_decode(file_get_contents('php://input'), true);
                (new UserController())->delete($data);
                break;

            case 'GET metrics':

                (new ApiController())->getRawMetrics();
                break;


            default:
                http_response_code(404);
                echo json_encode(['error' => 'Ruta no encontrada']);
        }
    }
}
