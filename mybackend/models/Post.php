<?php

namespace Models;

use PDO;

class Post
{
    private $conn;
    private $table_name = "posts";

    public $id;
    public $user_id;
    public $content;
    public $location;
    public $created_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " SET user_id=:user_id, content=:content, location=:location";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":location", $this->location);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    public function getPosts($page = 1, $limit = 10, $location = null)
    {
        $offset = ($page - 1) * $limit;

        $query = "SELECT p.id, p.content, p.location, p.created_at, u.username, 
                  (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) as likes_count
                  FROM " . $this->table_name . " p
                  JOIN users u ON p.user_id = u.id";

        if ($location) {
            $query .= " WHERE p.location = :location";
        }

        $query .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        if ($location) {
            $stmt->bindParam(":location", $location, PDO::PARAM_STR);
        }

        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt;
    }

    public function countPosts($location = null)
    {
        $query = "SELECT COUNT(id) as total FROM " . $this->table_name;
        if ($location) {
            $query .= " WHERE location = :location";
        }
        $stmt = $this->conn->prepare($query);

        if ($location) {
            $stmt->bindParam(":location", $location, PDO::PARAM_STR);
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
