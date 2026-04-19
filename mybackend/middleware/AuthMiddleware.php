<?php

namespace Middleware;

use Core\Response;
use Utils\JwtHelper;

class AuthMiddleware
{
    public static function handle()
    {
        $headers = getallheaders();
        $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            Response::error("Unauthorized: Missing or invalid Authorization header", 401);
        }

        $token = $matches[1];
        $payload = JwtHelper::validateToken($token);

        if (!$payload) {
            Response::error("Unauthorized: Invalid or expired token", 401);
        }

        // Return user data from payload
        return $payload;
    }
}
