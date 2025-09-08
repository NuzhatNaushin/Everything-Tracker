<?php
// controllers/expenseController.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/login.php");
    exit;
}

require_once __DIR__ . '/../db_connection.php';
require_once __DIR__ . '/../models/Expense.php';

$expenseModel = new Expense($conn);
$user_id = $_SESSION['user_id'];

// REPORT GENERATION 
if (isset($_GET['report']) && $_GET['report'] === 'true') {
    // Set headers to return a JSON response
    header('Content-Type: application/json');

    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

    if ($start_date && $end_date) {
        // Call the model to get the report data
        $report_data = $expenseModel->getReportData($user_id, $start_date, $end_date);
        echo json_encode($report_data);
    } else {
        echo json_encode([]);
    }
    exit; 
}

//  display calendar for the current month
$current_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$current_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add') {
        $amount = trim($_POST['amount']);
        $category = trim($_POST['category']);
        $description = trim($_POST['description']);
        $date = trim($_POST['date']);

        if ($expenseModel->addExpense($user_id, $amount, $category, $description, $date)) {
            $_SESSION['message'] = "Expense added successfully!";
        } else {
            $_SESSION['message'] = "Failed to add expense.";
        }
    } elseif ($action === 'update') {
        $id = trim($_POST['id']);
        $amount = trim($_POST['amount']);
        $category = trim($_POST['category']);
        $description = trim($_POST['description']);
        $date = trim($_POST['date']);

        if ($expenseModel->updateExpense($id, $user_id, $amount, $category, $description, $date)) {
            $_SESSION['message'] = "Expense updated successfully!";
        } else {
            $_SESSION['message'] = "Failed to update expense.";
        }
    } elseif ($action === 'delete') {
        $id = trim($_POST['id']);
        if ($expenseModel->deleteExpense($id, $user_id)) {
            $_SESSION['message'] = "Expense deleted successfully!";
        } else {
            $_SESSION['message'] = "Failed to delete expense.";
        }
    }
    
    // Redirect back to the controller after POST action
    header("Location: expenseController.php?month=" . $current_month . "&year=" . $current_year);
    exit;
}

// Fetch all expenses for the current month and year
$expenses = $expenseModel->getMonthlyExpenses($user_id, $current_month, $current_year);

$monthly_summary = $expenseModel->getMonthlySummary($user_id, $current_month, $current_year);

$categories = array_unique(array_column($expenses, 'category'));

// Check for category filter
$filtered_category = isset($_GET['category']) ? $_GET['category'] : '';
if (!empty($filtered_category)) {
    $expenses = array_filter($expenses, function($expense) use ($filtered_category) {
        return $expense['category'] === $filtered_category;
    });
}

require_once __DIR__ . '/../views/expenseView.php';
?>