<?php

namespace Models;

use PDO;

class Like
{
    private $conn;
    private $table_name = "likes";

    public $id;
    public $user_id;
    public $post_id;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function toggleLike()
    {
        // Check if already liked
        $query = "SELECT id FROM " . $this->table_name . " WHERE user_id = :user_id AND post_id = :post_id LIMIT 1";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":post_id", $this->post_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Un-like
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $deleteQuery = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $delStmt = $this->conn->prepare($deleteQuery);
            $delStmt->bindParam(":id", $row['id']);
            $delStmt->execute();
            return ["status" => "unliked"];
        } else {
            // Like
            $insertQuery = "INSERT INTO " . $this->table_name . " SET user_id=:user_id, post_id=:post_id";
            $insStmt = $this->conn->prepare($insertQuery);
            $insStmt->bindParam(":user_id", $this->user_id);
            $insStmt->bindParam(":post_id", $this->post_id);
            if ($insStmt->execute()) {
                return ["status" => "liked"];
            }
            return false;
        }
    }
}
