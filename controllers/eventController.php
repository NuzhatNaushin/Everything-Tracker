<?php
// controllers/eventController.php

// Ensure session is started only if it hasn't been already
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in. This check should redirect to login if no user is found.
if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/login.php");
    exit;
}

require_once __DIR__ . '/../db_connection.php';
require_once __DIR__ . '/../models/Event.php';

$eventModel = new Event($conn);
$user_id = $_SESSION['user_id'];

// Default action: display calendar for the current month
$current_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$current_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'create') {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $start_date = trim($_POST['start_date']);
        $end_date = trim($_POST['end_date']);
        $category = trim($_POST['category']);
        $color_code = trim($_POST['color_code']);

        if ($eventModel->create($user_id, $title, $description, $start_date, $end_date, $category, $color_code)) {
            $_SESSION['message'] = "Event created successfully!";
        } else {
            $_SESSION['message'] = "Failed to create event.";
        }
    } elseif ($action === 'update') {
        $id = trim($_POST['id']);
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $start_date = trim($_POST['start_date']);
        $end_date = trim($_POST['end_date']);
        $category = trim($_POST['category']);
        $color_code = trim($_POST['color_code']);
        
        // Pass the $user_id as the 8th argument to match the Event model's update function
        if ($eventModel->update($id, $user_id, $title, $description, $start_date, $end_date, $category, $color_code)) {
            $_SESSION['message'] = "Event updated successfully!";
        } else {
            $_SESSION['message'] = "Failed to update event.";
        }
    } elseif ($action === 'delete') {
        $id = trim($_POST['id']);
        if ($eventModel->delete($id, $user_id)) {
            $_SESSION['message'] = "Event deleted successfully!";
        } else {
            $_SESSION['message'] = "Failed to delete event.";
        }
    } elseif ($action === 'share') {
        $event_id = trim($_POST['event_id']);
        $invitee_email = trim($_POST['email']);
        
        if (empty($event_id) || empty($invitee_email)) {
            $_SESSION['message'] = "Event ID and email are required.";
        } else {
            try {
                if ($eventModel->shareEvent($event_id, $invitee_email)) {
                    $_SESSION['message'] = "Event shared successfully with $invitee_email!";
                } else {
                    $_SESSION['message'] = "Failed to share event. The invitee's email might not be registered or the event is already shared with this user.";
                }
            } catch (PDOException $e) {
                $_SESSION['message'] = "A database error occurred: " . $e->getMessage();
            }
        }
    }

    // Redirect back to the calendar view after a successful action
    header("Location: ../controllers/eventController.php?month=" . $current_month . "&year=" . $current_year);
    exit;
}

// Fetch events for the current month, including shared events
$events = $eventModel->getEventsByMonth($user_id, $current_month, $current_year);

// Include the view file to render the calendar
require_once __DIR__ . '/../views/eventView.php';
?>