<?php

namespace Controllers;

use Config\Database;
use Models\Post;
use Core\Response;
use Utils\Validator;

class PostController
{
    private $db;
    private $user;

    public function __construct($userPayload)
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = $userPayload; // Passed from AuthMiddleware
    }

    public function create()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $data = Validator::sanitize($data);

        $errors = Validator::validateRequired($data, ['content']);
        if (!empty($errors)) {
            Response::error(implode(', ', $errors), 400);
        }

        $post = new Post($this->db);
        $post->user_id = $this->user['id'];
        $post->content = $data['content'];
        $post->location = isset($data['location']) ? $data['location'] : null;

        if ($post->create()) {
            Response::json([
                "message" => "Post created successfuly.",
                "post_id" => $post->id
            ], 201);
        } else {
            Response::error("Unable to create post.", 500);
        }
    }

    // This method does not necessarily require authentication to view posts (optional)
    public function getPosts()
    {
        // Query parameters
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
        $location = isset($_GET['location']) ? $_GET['location'] : null;

        // Sanitize filter
        $location = $location ? Validator::sanitize($location) : null;

        $post = new Post($this->db);
        $stmt = $post->getPosts($page, $limit, $location);
        $total = $post->countPosts($location);

        $posts_arr = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $posts_arr[] = $row;
        }

        Response::json([
            "message" => "Posts retrieved successfully.",
            "data" => $posts_arr,
            "meta" => [
                "current_page" => $page,
                "per_page" => $limit,
                "total_items" => $total,
                "total_pages" => ceil($total / $limit)
            ]
        ], 200);
    }
}
