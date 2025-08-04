<?php
// controllers/todoController.php

session_start();

require_once __DIR__ . '/../db_connection.php';
require_once __DIR__ . '/../models/todo.php';

// Check if the user is logged in.
if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$todoModel = new Todo($conn);

// Handle form submissions (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? null;

    switch ($action) {
        case 'create':
            $task_data = [
                'user_id' => $user_id,
                'task' => trim($_POST['task'] ?? ''),
                'due_date' => $_POST['due_date'] ?? null,
                'priority' => $_POST['priority'] ?? 'Medium',
                'label' => trim($_POST['label'] ?? null),
                'recurring' => isset($_POST['recurring']) ? 1 : 0,
                'recurring_frequency' => $_POST['recurring_frequency'] ?? null,
            ];
            if (!empty($task_data['task'])) {
                $todoModel->create($task_data);
            }
            break;

        case 'update':
            // Check if this is a simple completion toggle or a full form update.
            if (isset($_POST['completed']) && !isset($_POST['task'])) {
                $completed = $_POST['completed'];
                
                // If marking as complete, check for recurrence
                if ($completed == 1) {
                    $original_task = $todoModel->getTodoById($id);
                    if ($original_task && $original_task['recurring'] == 1) {
                        // Calculate next due date
                        $next_due_date = null;
                        $due_date = $original_task['due_date'] ? new DateTime($original_task['due_date']) : new DateTime();
                        switch ($original_task['recurring_frequency']) {
                            case 'Daily':
                                $due_date->modify('+1 day');
                                break;
                            case 'Weekly':
                                $due_date->modify('+1 week');
                                break;
                            case 'Monthly':
                                $due_date->modify('+1 month');
                                break;
                        }
                        $next_due_date = $due_date->format('Y-m-d');

                        // Create a new task for the next recurrence
                        $new_task_data = [
                            'user_id' => $user_id,
                            'task' => $original_task['task'],
                            'due_date' => $next_due_date,
                            'priority' => $original_task['priority'],
                            'label' => $original_task['label'],
                            'recurring' => 1,
                            'recurring_frequency' => $original_task['recurring_frequency'],
                        ];
                        $todoModel->create($new_task_data);
                    }
                }
                $todoModel->toggleCompletion($id, $user_id, $completed);
            } elseif ($id !== null && !empty($_POST['task'])) {
                 $task_data = [
                    'user_id' => $user_id,
                    'task' => trim($_POST['task'] ?? ''),
                    'due_date' => $_POST['due_date'] ?? null,
                    'priority' => $_POST['priority'] ?? 'Medium',
                    'label' => trim($_POST['label'] ?? null),
                    'completed' => isset($_POST['completed']) ? 1 : 0,
                    'recurring' => isset($_POST['recurring']) ? 1 : 0,
                    'recurring_frequency' => $_POST['recurring_frequency'] ?? null,
                ];
                $todoModel->update($id, $task_data);
            }
            break;

        case 'delete':
            if ($id !== null) {
                $todoModel->delete($id, $user_id);
            }
            break;
    }
    // Redirect back to the To-Do list view to prevent form resubmission and show updated data.
    header("Location: ../controllers/todoController.php");
    exit;
}

// Handle filtering and sorting (GET)
$filters = [
    'status' => $_GET['status'] ?? null,
    'priority' => $_GET['priority'] ?? null,
    'label' => $_GET['label'] ?? null,
    'search' => $_GET['search'] ?? null,
];
$sort_by = $_GET['sort'] ?? 'created_at DESC';

// Fetch all todos for the current user based on filters and sorting
$todos = $todoModel->getTodosByUserId($user_id, $filters, $sort_by);

// Include the view to display the to-do list
require_once __DIR__ . '/../views/todoView.php';
?>