<?php
// models/users.php


class User {
    private $conn;
    private $table = 'users';

    /**
     * Constructor 
     * @param PDO $db 
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     *
     * @param string $username 
     * @param string $email 
     * @param string $password 
     * @return bool 
     */
    public function registerUser($username, $email, $password) {

        $username = trim($username);
        $email = trim($email);
        $password = trim($password);

        $query = "INSERT INTO " . $this->table . " (username, password, email) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        if ($stmt->execute([$username, $password, $email])) {
            return true;
        } else {
            error_log("Registration failed: " . implode(" ", $stmt->errorInfo()));
            return false;
        }
    }

    /**
     * login
     *
     * @param string $username 
     * @param string $password 
     * @return array|false 
     */
    public function loginUser($username, $password) {
        $username = trim($username);
        $password = trim($password);
        
        $query = "SELECT id, username, password, email FROM " . $this->table . " WHERE username = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['password'] === $password) {
            return $user; 
        }

        return false; 
    }

    public function getUserById($user_id) {
        $query = "SELECT id, username, email, created_at FROM " . $this->table . " WHERE id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            return $user;
        }

        return false;
    }
}
?>
