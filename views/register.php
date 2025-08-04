<?php
// views/register.php

session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Everything Tracker</title>
    <style>
        /* General Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Form Container */
        .form-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .form-container h2 {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            color: #333;
        }

        .form-group {
            margin-bottom: 1rem;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #555;
        }

        .form-group input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
        }

        .btn {
            width: 100%;
            padding: 0.8rem;
            border: none;
            border-radius: 8px;
            background-color: #ff6b6b;
            color: white;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #ff5252;
        }
        
        .message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            color: #d8000c;
            background-color: #ffbaba;
            border: 1px solid #d8000c;
        }
        
        .success {
            color: #005a00;
            background-color: #d4edda;
            border: 1px solid #005a00;
        }
        
        .link {
            display: block;
            margin-top: 1rem;
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Register for Everything Tracker</h2>
        <?php
        if (isset($_SESSION['message'])) {
            $is_success = (strpos($_SESSION['message'], 'successful') !== false);
            $message_class = $is_success ? 'success' : '';
            echo '<div class="message ' . $message_class . '">' . htmlspecialchars($_SESSION['message']) . '</div>';
            unset($_SESSION['message']);
        }
        ?>
        <form action="../controllers/userController.php" method="POST">
            <input type="hidden" name="action" value="register">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Register</button>
        </form>
        <a href="login.php" class="link">Already have an account? Log in here.</a>
    </div>
</body>
</html>
