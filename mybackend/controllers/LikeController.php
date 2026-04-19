<?php

namespace Controllers;

use Config\Database;
use Models\Like;
use Core\Response;
use Utils\Validator;

class LikeController
{
    private $db;
    private $user;

    public function __construct($userPayload)
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = $userPayload; // Passed from AuthMiddleware
    }

    public function toggleLike()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $data = Validator::sanitize($data);

        $errors = Validator::validateRequired($data, ['post_id']);
        if (!empty($errors)) {
            Response::error(implode(', ', $errors), 400);
        }

        $like = new Like($this->db);
        $like->user_id = $this->user['id'];
        $like->post_id = $data['post_id'];

        $result = $like->toggleLike();

        if ($result) {
            $msg = $result['status'] === 'liked' ? 'Post liked successfully' : 'Post unliked successfully';
            Response::json(["message" => $msg, "action" => $result['status']], 200);
        } else {
            Response::error("Unable to process like toggle.", 500);
        }
    }
}
