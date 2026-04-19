<?php

// CORS Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Autoloader function
spl_autoload_register(function ($class) {
    // Convert namespace backslashes to directory separators
    $file = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

use Core\Router;
use Middleware\AuthMiddleware;
use Middleware\RateLimitMiddleware;
use Controllers\AuthController;
use Controllers\PostController;
use Controllers\LikeController;
use Core\Response;
use Utils\Logger;

try {
    // Basic Rate Limiting
    RateLimitMiddleware::handle();

    // Initialize Router
    $router = new Router();

    // Health check
    $router->add('GET', '/api/ping', function () {
        Response::json(["message" => "pong"]);
    });

    // Auth Routes
    $router->add('POST', '/api/auth/signup', function () {
        $controller = new AuthController();
        $controller->signup();
    });

    $router->add('POST', '/api/auth/login', function () {
        $controller = new AuthController();
        $controller->login();
    });

    // Post Routes (Requires Auth)
    $router->add('POST', '/api/posts', function () {
        $user = AuthMiddleware::handle();
        $controller = new PostController($user);
        $controller->create();
    });

    // Get Posts (Optional Auth, but for now open with pagination)
    $router->add('GET', '/api/posts', function () {
        // Here we just pass null for auth, as viewing posts doesn't require login
        $controller = new PostController(null);
        $controller->getPosts();
    });

    // Like Route
    $router->add('POST', '/api/likes/toggle', function () {
        $user = AuthMiddleware::handle();
        $controller = new LikeController($user);
        $controller->toggleLike();
    });

    // Dispatch request
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = $_SERVER['REQUEST_URI'];

    // Support app running from subdirectories gracefully
    // e.g. /mybackend/index.php becomes /api/...
    $basePath = '/mybackend';
    if (strpos($uri, $basePath) === 0) {
        $uri = substr($uri, strlen($basePath));
    }

    $router->dispatch($method, $uri);

} catch (Exception $e) {
    Logger::error("Unhandled Application Exception: " . $e->getMessage());
    Response::error("Internal Server Error", 500);
}
