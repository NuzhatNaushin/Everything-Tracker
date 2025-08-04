<?php
// controllers/userController.php

// Start a PHP session. This is required to store user data after login.
session_start();

// Include the database connection and the User model.
require_once __DIR__ . '/../db_connection.php';
require_once __DIR__ . '/../models/users.php';

// Instantiate the User model class, passing the database connection object.
$userModel = new User($conn);

// Check if the request method is POST to handle form submissions.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // --- Handle User Registration ---
    if (isset($_POST['action']) && $_POST['action'] == 'register') {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        // Perform basic server-side validation.
        if (empty($username) || empty($email) || empty($password)) {
            $_SESSION['message'] = "All fields are required.";
            header("Location: ../views/register.php");
            exit;
        }

        // Attempt to register the new user.
        if ($userModel->registerUser($username, $email, $password)) {
            $_SESSION['message'] = "Registration successful! You can now log in.";
            header("Location: ../views/login.php");
            exit;
        } else {
            $_SESSION['message'] = "Registration failed. Please try a different username or email.";
            header("Location: ../views/register.php");
            exit;
        }
    }

    // --- Handle User Login ---
    if (isset($_POST['action']) && $_POST['action'] == 'login') {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        // Perform basic server-side validation.
        if (empty($username) || empty($password)) {
            $_SESSION['message'] = "Username and password are required.";
            header("Location: ../views/login.php");
            exit;
        }

        // Attempt to log the user in.
        $user = $userModel->loginUser($username, $password);

        if ($user) {
            // Login successful. Store user data in the session.
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // Redirect the user to the dashboard or home page.
            // We'll create this later. For now, we can redirect to a welcome page.
            header("Location: ../views/dashboard.php");
            exit;
        } else {
            // Login failed.
            $_SESSION['message'] = "Invalid username or password.";
            header("Location: ../views/login.php");
            exit;
        }
    }
}
?>