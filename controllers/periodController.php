<?php
// controllers/periodController.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/login.php");
    exit;
}

require_once __DIR__ . '/../db_connection.php';
require_once __DIR__ . '/../models/Period.php';

$periodModel = new Period($conn);
$user_id = $_SESSION['user_id'];

// Get current month and year for calendar display
$current_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$current_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Handle new form submissions for daily symptoms
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add-period') {
        $start_date = trim($_POST['start_date']);
        $end_date = trim($_POST['end_date']);
        if ($periodModel->addPeriod($user_id, $start_date, $end_date)) {
            $_SESSION['message'] = "Period entry added successfully!";
        } else {
            $_SESSION['message'] = "Failed to add period entry.";
        }
    } elseif ($action === 'log-symptom') {
        $date = trim($_POST['date']);
        $cramps = isset($_POST['cramps']) ? trim($_POST['cramps']) : null;
        $flow_level = isset($_POST['flow_level']) ? trim($_POST['flow_level']) : null;
        $mood = isset($_POST['mood']) ? trim($_POST['mood']) : null;
        $notes = trim($_POST['notes']);

        if ($periodModel->addSymptom($user_id, $date, $cramps, $flow_level, $mood, $notes)) {
            $_SESSION['message'] = "Symptom logged successfully!";
        } else {
            $_SESSION['message'] = "Failed to log symptom.";
        }
    } elseif ($action === 'delete-period') {
        $id = trim($_POST['id']);
        if ($periodModel->deletePeriod($id, $user_id)) {
            $_SESSION['message'] = "Period entry deleted successfully!";
        } else {
            $_SESSION['message'] = "Failed to delete period entry.";
        }
    }
    
    // Redirect back to the calendar view after a successful action
    header("Location: periodController.php?month=" . $current_month . "&year=" . $current_year);
    exit;
}

// Fetch all periods for the calendar month and all symptoms
$periods = $periodModel->getPeriods($user_id, $current_month, $current_year);
$all_periods = $periodModel->getAllPeriods($user_id);
$symptoms = $periodModel->getSymptoms($user_id, $current_month, $current_year);

// Calculate cycle details based on past periods
$average_cycle_length = $periodModel->getAverageCycleLength($user_id);
$last_period_start = null;
$next_period_start = null;
$fertile_window_start = null;
$fertile_window_end = null;
$ovulation_day = null;

if (count($all_periods) >= 1) {
    $last_period = $all_periods[0];
    $last_period_start = $last_period['start_date'];

    // Use a fixed 28-day cycle if no average is available
    $cycle_length = $average_cycle_length ?? 28;
    
    // Prediction for the next period
    $next_period_start = date('Y-m-d', strtotime($last_period_start . " +$cycle_length days"));
    
    // Calculate fertility window and ovulation day based on next period start
    $ovulation_day = date('Y-m-d', strtotime($next_period_start . " -14 days"));
    $fertile_window_start = date('Y-m-d', strtotime($ovulation_day . " -5 days"));
    $fertile_window_end = date('Y-m-d', strtotime($ovulation_day . " +1 days"));
}

// Map periods and symptoms to a calendar grid
$calendar_data = [];
$period_days = [];
foreach ($periods as $period) {
    $start_date = new DateTime($period['start_date']);
    $end_date = new DateTime($period['end_date']);
    $interval = new DateInterval('P1D');
    $date_range = new DatePeriod($start_date, $interval, $end_date->modify('+1 day'));
    foreach ($date_range as $date) {
        $period_days[] = $date->format('Y-m-d');
    }
}
$symptoms_by_date = [];
foreach ($symptoms as $symptom) {
    $symptoms_by_date[$symptom['date']] = $symptom;
}

// Pass variables to the view
require_once __DIR__ . '/../views/periodView.php';
?>
