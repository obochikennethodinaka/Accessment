<?php

namespace Models;

use PDO;
use Exception;

class User
{
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $email;
    public $password_hash;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function emailExists()
    {
        $query = "SELECT id, username, password_hash FROM " . $this->table_name . " WHERE email = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->password_hash = $row['password_hash'];
            return true;
        }

        return false;
    }

    public function usernameExists()
    {
        $query = "SELECT id FROM " . $this->table_name . " WHERE username = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->username);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public function createUser()
    {
        if ($this->emailExists()) {
            throw new Exception("Email already exists");
        }
        if ($this->usernameExists()) {
            throw new Exception("Username already exists");
        }

        $query = "INSERT INTO " . $this->table_name . " SET username=:username, email=:email, password_hash=:password_hash";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password_hash", $this->password_hash);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }
}
