<?php
// models/users.php

/**
 * The User class is the model for handling all user-related database operations.
 * It provides methods for user registration, login, and data retrieval using PDO.
 */
class User {
    private $conn;
    private $table = 'users';

    /**
     * Constructor for the User class.
     * @param PDO $db The database connection object.
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Registers a new user by inserting their details into the `users` table.
     * The password is stored as plain text as requested, but this is not recommended.
     *
     * @param string $username The user's chosen username.
     * @param string $email The user's email address.
     * @param string $password The user's chosen password (stored as plain text).
     * @return bool True on successful registration, false on failure.
     */
    public function registerUser($username, $email, $password) {
        // Trim any leading/trailing whitespace from the inputs
        $username = trim($username);
        $email = trim($email);
        $password = trim($password);

        // Prepare a SQL statement to prevent SQL injection.
        $query = "INSERT INTO " . $this->table . " (username, password, email) VALUES (?, ?, ?)";

        // Use a prepared statement to bind parameters securely.
        $stmt = $this->conn->prepare($query);

        if ($stmt->execute([$username, $password, $email])) {
            return true;
        } else {
            // Log the error for debugging purposes.
            error_log("Registration failed: " . implode(" ", $stmt->errorInfo()));
            return false;
        }
    }

    /**
     * Authenticates a user by checking their username and password.
     *
     * @param string $username The user's username.
     * @param string $password The password entered by the user.
     * @return array|false An associative array with user data on success, or false on failure.
     */
    public function loginUser($username, $password) {
        // Trim any leading/trailing whitespace from the inputs
        $username = trim($username);
        $password = trim($password);
        
        // Prepare a SQL statement to select the user by username.
        $query = "SELECT id, username, password, email FROM " . $this->table . " WHERE username = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if a user with that username exists and the password matches.
        // We are comparing plain text passwords here as per your request.
        if ($user && $user['password'] === $password) {
            return $user; // Return the user data on successful login.
        }

        return false; // Return false for failed login.
    }

    /**
     * Retrieves a user's information from the database by their user ID.
     *
     * @param int $user_id The ID of the user.
     * @return array|false An associative array with user data on success, or false if the user is not found.
     */
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
