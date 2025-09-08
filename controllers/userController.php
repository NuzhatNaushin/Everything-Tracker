<?php
// controllers/userController.php

session_start();

require_once __DIR__ . '/../db_connection.php';
require_once __DIR__ . '/../models/users.php';

$userModel = new User($conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // user Registration 
    if (isset($_POST['action']) && $_POST['action'] == 'register') {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        if (empty($username) || empty($email) || empty($password)) {
            $_SESSION['message'] = "All fields are required.";
            header("Location: ../views/register.php");
            exit;
        }

        // register the new user.
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

    // User Login
    if (isset($_POST['action']) && $_POST['action'] == 'login') {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        if (empty($username) || empty($password)) {
            $_SESSION['message'] = "Username and password are required.";
            header("Location: ../views/login.php");
            exit;
        }

        $user = $userModel->loginUser($username, $password);

        if ($user) {
 
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            header("Location: ../views/dashboard.php");
            exit;
        } else {

            $_SESSION['message'] = "Invalid username or password.";
            header("Location: ../views/login.php");
            exit;
        }
    }
}
?>