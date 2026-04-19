<?php

namespace Core;

class Router {
    private $routes = [];

    public function add($method, $path, $callback) {
        $path = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[a-zA-Z0-9_-]+)', $path);
        
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => '#^' . $path . '$#',
            'callback' => $callback
        ];
    }

    public function dispatch($method, $uri) {
        $uri = parse_url($uri, PHP_URL_PATH);
        
        // Remove trailing slash if not root
        if ($uri !== '/' && substr($uri, -1) === '/') {
            $uri = substr($uri, 0, -1);
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === strtoupper($method) && preg_match($route['path'], $uri, $matches)) {
                // Filter string keys (named groups)
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                call_user_func_array($route['callback'], [$params]);
                return;
            }
        }

        // Not found
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Route not found"]);
    }
}
