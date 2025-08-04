<?php
// views/dashboard.php

session_start();

// Check if the user is logged in. If not, redirect to the login page.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// User is logged in, so we can access their session data.
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Everything Tracker</title>
    <style>
        /* General Styles - Shared with homepage */
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
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
            text-decoration: none;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: white;
        }

        .user-info span {
            font-size: 1rem;
        }

        .btn {
            padding: 0.5rem 1.5rem;
            border: none;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: #ff6b6b;
            color: white;
        }

        .btn-primary:hover {
            background: #ff5252;
            transform: translateY(-2px);
        }

        /* Dashboard Content */
        .dashboard {
            text-align: center;
            padding: 4rem 0;
            color: white;
        }

        .dashboard h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .dashboard p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .module-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .module-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-decoration: none;
            color: #333;
        }

        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        }

        .module-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .module-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #333;
        }

        .module-card p {
            color: #666;
            line-height: 1.6;
        }

        /* Footer */
        footer {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            color: white;
            text-align: center;
            padding: 2rem 0;
            margin-top: 2rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard h1 {
                font-size: 2rem;
            }
            
            .module-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <a href="dashboard.php" class="logo">Everything Tracker</a>
                <div class="user-info">
                    <span>Hello, <?php echo htmlspecialchars($username); ?>!</span>
                    <a href="logout.php" class="btn btn-primary">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <main>
        <section class="dashboard">
            <div class="container">
                <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
                <p>Access your productivity modules below.</p>
                <div class="module-grid">
                    <a href="../controllers/todoController.php" class="module-card">
                        <div class="module-icon">✓</div>
                        <h3>To-Do List</h3>
                        <p>Manage your tasks and stay on top of your responsibilities.</p>
                    </a>
                    <a href="calendar.php" class="module-card">
                        <div class="module-icon">📅</div>
                        <h3>Event Calendar</h3>
                        <p>Schedule your events and never miss an important date again.</p>
                    </a>
                    <a href="expenses.php" class="module-card">
                        <div class="module-icon">💰</div>
                        <h3>Expense Tracker</h3>
                        <p>Track your spending and manage your budget effectively.</p>
                    </a>
                    <a href="period.php" class="module-card">
                        <div class="module-icon">🩺</div>
                        <h3>Period Tracker</h3>
                        <p>Monitor your cycle with a simple and intuitive tracker.</p>
                    </a>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 Everything Tracker. Your personal productivity companion.</p>
        </div>
    </footer>
</body>
</html>
