<?php

namespace Controllers;

use Config\Database;
use Models\User;
use Core\Response;
use Utils\Validator;
use Utils\JwtHelper;
use Utils\Logger;
use Exception;

class AuthController
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function signup()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $data = Validator::sanitize($data);

        $errors = Validator::validateRequired($data, ['username', 'email', 'password']);
        if (!empty($errors)) {
            Response::error(implode(', ', $errors), 400);
        }

        if (!Validator::validateEmail($data['email'])) {
            Response::error("Invalid email format", 400);
        }

        $user = new User($this->db);
        $user->username = $data['username'];
        $user->email = $data['email'];
        $user->password_hash = password_hash($data['password'], PASSWORD_BCRYPT);

        try {
            if ($user->createUser()) {
                Logger::info("User signed up: " . $user->username);
                Response::json(["message" => "User properly registered."], 201);
            } else {
                Response::error("Unable to register user.", 500);
            }
        } catch (Exception $e) {
            Logger::error("Signup failed: " . $e->getMessage());
            Response::error($e->getMessage(), 400);
        }
    }

    public function login()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $data = Validator::sanitize($data);

        $errors = Validator::validateRequired($data, ['email', 'password']);
        if (!empty($errors)) {
            Response::error(implode(', ', $errors), 400);
        }

        $user = new User($this->db);
        $user->email = $data['email'];

        if ($user->emailExists() && password_verify($data['password'], $user->password_hash)) {
            $tokenPayload = [
                "id" => $user->id,
                "username" => $user->username,
                "email" => $user->email,
                "exp" => time() + (60 * 60 * 24) // 1 day expiration
            ];

            $token = JwtHelper::generateToken($tokenPayload);
            Logger::info("User logged in: " . $user->username);
            Response::json(["message" => "Login successful", "token" => $token], 200);
        } else {
            Logger::info("Failed login attempt for email: " . $user->email);
            Response::error("Invalid email or password", 401);
        }
    }
}
