<?php

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List - Everything Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            color: #333;
        }
        .container {
            max-width: 900px;
        }
        .task-item {
            background-color: white;
            border-left: 5px solid #4a90e2;
            transition: all 0.2s ease-in-out;
        }
        .task-item.completed {
            border-left: 5px solid #2ecc71;
            opacity: 0.7;
        }
        .task-item.completed .task-text {
            text-decoration: line-through;
            color: #888;
        }
        .btn-action {
            transition: all 0.2s ease-in-out;
        }
        .btn-action:hover {
            transform: scale(1.1);
        }
        .modal-overlay {
            background-color: rgba(0, 0, 0, 0.5);
            visibility: hidden;
        }
        .modal-overlay.open {
            visibility: visible;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <div class="container mx-auto bg-white rounded-xl shadow-2xl p-8 my-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-4xl font-extrabold text-gray-800">Hello, <?= htmlspecialchars($username) ?>!</h1>
            <div class="flex items-center space-x-4">
                <a href="../views/dashboard.php" class="text-gray-500 hover:text-gray-700 transition duration-300">
                    <i class="fas fa-arrow-left mr-2"></i>Dashboard
                </a>
                <a href="../views/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg shadow-lg transition duration-300 transform hover:scale-105">
                    Logout
                </a>
            </div>
        </div>

        <!-- Add New Task Form -->
        <form id="addTaskForm" action="../controllers/todoController.php" method="POST" class="bg-gray-100 p-6 rounded-xl shadow-inner mb-8">
            <input type="hidden" name="action" value="create">
            <h3 class="text-xl font-bold mb-4 text-gray-700">Add a New Task</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <input type="text" name="task" placeholder="Task description..." class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                </div>
                <div>
                    <input type="date" name="due_date" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <select name="priority" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                        <option value="Low">Low Priority</option>
                        <option value="Medium" selected>Medium Priority</option>
                        <option value="High">High Priority</option>
                    </select>
                </div>
                <div>
                    <input type="text" name="label" placeholder="Label (e.g., Work, Home)" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                </div>
                <div class="flex items-center space-x-2">
                    <input type="checkbox" name="recurring" id="recurring-checkbox" class="h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="recurring-checkbox" class="text-gray-700">Recurring</label>
                </div>
                <div id="recurring-options" class="hidden">
                    <select name="recurring_frequency" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                        <option value="Daily">Daily</option>
                        <option value="Weekly">Weekly</option>
                        <option value="Monthly">Monthly</option>
                    </select>
                </div>
                <div class="col-span-full">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg shadow-lg transition duration-300 transform hover:scale-105">
                        Add Task
                    </button>
                </div>
            </div>
        </form>

        <!-- Filters and Sorting -->
        <div class="flex flex-wrap items-center justify-between mb-6 p-4 bg-gray-100 rounded-xl shadow-inner">
            <h3 class="text-lg font-semibold mb-2 md:mb-0">Filter & Sort:</h3>
            <form id="filterSortForm" action="../controllers/todoController.php" method="GET" class="flex flex-wrap items-center space-x-2 space-y-2 md:space-y-0">
                <!-- Status Filter -->
                <select name="status" onchange="this.form.submit()" class="p-2 border-2 border-gray-300 rounded-lg">
                    <option value="">All Status</option>
                    <option value="incomplete" <?= ($_GET['status'] ?? '') === 'incomplete' ? 'selected' : '' ?>>Incomplete</option>
                    <option value="completed" <?= ($_GET['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                </select>

                <!-- Priority Filter -->
                <select name="priority" onchange="this.form.submit()" class="p-2 border-2 border-gray-300 rounded-lg">
                    <option value="">All Priorities</option>
                    <option value="High" <?= ($_GET['priority'] ?? '') === 'High' ? 'selected' : '' ?>>High</option>
                    <option value="Medium" <?= ($_GET['priority'] ?? '') === 'Medium' ? 'selected' : '' ?>>Medium</option>
                    <option value="Low" <?= ($_GET['priority'] ?? '') === 'Low' ? 'selected' : '' ?>>Low</option>
                </select>

                <!-- Sort By -->
                <select name="sort" onchange="this.form.submit()" class="p-2 border-2 border-gray-300 rounded-lg">
                    <option value="created_at DESC" <?= ($_GET['sort'] ?? '') === 'created_at DESC' ? 'selected' : '' ?>>Newest First</option>
                    <option value="created_at ASC" <?= ($_GET['sort'] ?? '') === 'created_at ASC' ? 'selected' : '' ?>>Oldest First</option>
                    <option value="due_date ASC" <?= ($_GET['sort'] ?? '') === 'due_date ASC' ? 'selected' : '' ?>>Due Date (Soonest)</option>
                    <option value="priority DESC" <?= ($_GET['sort'] ?? '') === 'priority DESC' ? 'selected' : '' ?>>Priority (High to Low)</option>
                </select>

                <!-- Search Input -->
                <input type="text" name="search" placeholder="Search tasks..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" class="p-2 border-2 border-gray-300 rounded-lg">
                <button type="submit" class="p-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition duration-300">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>


        <!-- Task List -->
        <div class="space-y-4">
            <?php if (empty($todos)): ?>
                <div class="bg-gray-100 p-6 rounded-lg text-center text-gray-500 shadow-md">
                    <p>No tasks found. Time to add one!</p>
                </div>
            <?php else: ?>
                <?php foreach ($todos as $todo): ?>
                    <div class="task-item flex flex-col md:flex-row items-start md:items-center justify-between p-4 rounded-xl shadow-md transition-all duration-300
                                <?= $todo['completed'] ? 'completed' : '' ?>">
                        <div class="flex-1 min-w-0 mb-2 md:mb-0">
                            <h4 class="text-xl font-bold task-text break-words"><?= htmlspecialchars($todo['task']) ?></h4>
                            <div class="text-sm text-gray-500 mt-1 flex flex-wrap items-center space-x-2">
                                <?php if ($todo['due_date']): ?>
                                    <span><i class="far fa-calendar-alt"></i> Due: <?= htmlspecialchars($todo['due_date']) ?></span>
                                <?php endif; ?>
                                <?php
                                    $priority_colors = ['High' => 'bg-red-500', 'Medium' => 'bg-yellow-500', 'Low' => 'bg-green-500'];
                                    $priority_text = $todo['priority'];
                                    $priority_class = $priority_colors[$priority_text] ?? 'bg-gray-500';
                                ?>
                                <span class="px-2 py-1 text-xs font-semibold text-white rounded-full <?= $priority_class ?>"><?= htmlspecialchars($priority_text) ?></span>
                                <?php if ($todo['label']): ?>
                                    <span class="px-2 py-1 text-xs font-semibold text-white bg-indigo-500 rounded-full"><i class="fas fa-tag"></i> <?= htmlspecialchars($todo['label']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2 mt-2 md:mt-0">
                            <!-- Toggle Completion Form -->
                            <form action="../controllers/todoController.php" method="POST">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?= $todo['id'] ?>">
                                <input type="hidden" name="completed" value="<?= $todo['completed'] ? '0' : '1' ?>">
                                <button type="submit" class="btn-action w-8 h-8 rounded-full flex items-center justify-center 
                                    <?= $todo['completed'] ? 'bg-green-500 hover:bg-green-600 text-white' : 'bg-gray-200 hover:bg-gray-300 text-gray-600' ?>">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>

                            <!-- Edit Button -->
                            <button onclick="openEditModal(<?= htmlspecialchars(json_encode($todo)) ?>)" class="btn-action w-8 h-8 rounded-full flex items-center justify-center bg-blue-500 hover:bg-blue-600 text-white">
                                <i class="fas fa-edit"></i>
                            </button>
                            
                            <!-- Delete Form -->
                            <form action="../controllers/todoController.php" method="POST">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $todo['id'] ?>">
                                <button type="submit" class="btn-action w-8 h-8 rounded-full flex items-center justify-center bg-red-500 hover:bg-red-600 text-white">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit Task Modal -->
    <div id="editModal" class="modal-overlay fixed inset-0 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-md mx-4">
            <h2 class="text-2xl font-bold mb-4">Edit Task</h2>
            <form id="editTaskForm" action="../controllers/todoController.php" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit-id">
                <div class="space-y-4">
                    <div>
                        <label for="edit-task" class="block text-gray-700">Task</label>
                        <input type="text" name="task" id="edit-task" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                    </div>
                    <div>
                        <label for="edit-due_date" class="block text-gray-700">Due Date</label>
                        <input type="date" name="due_date" id="edit-due_date" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label for="edit-priority" class="block text-gray-700">Priority</label>
                        <select name="priority" id="edit-priority" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                    <div>
                        <label for="edit-label" class="block text-gray-700">Label</label>
                        <input type="text" name="label" id="edit-label" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                    </div>
                    <div class="flex items-center space-x-2">
                        <input type="checkbox" name="completed" id="edit-completed" value="1" class="h-5 w-5 text-green-500 border-gray-300 rounded focus:ring-green-500">
                        <label for="edit-completed" class="text-gray-700">Completed</label>
                    </div>
                    <div class="flex items-center space-x-2">
                        <input type="checkbox" name="recurring" id="edit-recurring" value="1" class="h-5 w-5 text-blue-500 border-gray-300 rounded focus:ring-blue-500">
                        <label for="edit-recurring" class="text-gray-700">Recurring</label>
                    </div>
                    <div id="edit-recurring-options" class="hidden">
                        <label for="edit-recurring-frequency" class="block text-gray-700">Recurrence</label>
                        <select name="recurring_frequency" id="edit-recurring-frequency" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                            <option value="Daily">Daily</option>
                            <option value="Weekly">Weekly</option>
                            <option value="Monthly">Monthly</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end space-x-4 mt-6">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold rounded-lg transition duration-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition duration-300">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // JavaScript for modal functionality
        const editModal = document.getElementById('editModal');
        const editTaskForm = document.getElementById('editTaskForm');
        const recurringCheckbox = document.getElementById('recurring-checkbox');
        const recurringOptions = document.getElementById('recurring-options');
        const editRecurringCheckbox = document.getElementById('edit-recurring');
        const editRecurringOptions = document.getElementById('edit-recurring-options');

        function openEditModal(task) {
            document.getElementById('edit-id').value = task.id;
            document.getElementById('edit-task').value = task.task;
            document.getElementById('edit-due_date').value = task.due_date;
            document.getElementById('edit-priority').value = task.priority;
            document.getElementById('edit-label').value = task.label;
            document.getElementById('edit-completed').checked = task.completed == 1;
            document.getElementById('edit-recurring').checked = task.recurring == 1;
            document.getElementById('edit-recurring-frequency').value = task.recurring_frequency;

            if (task.recurring == 1) {
                editRecurringOptions.classList.remove('hidden');
            } else {
                editRecurringOptions.classList.add('hidden');
            }

            editModal.classList.add('open');
        }

        function closeEditModal() {
            editModal.classList.remove('open');
        }

        // Show/hide recurring options on checkbox change
        recurringCheckbox.addEventListener('change', () => {
            if (recurringCheckbox.checked) {
                recurringOptions.classList.remove('hidden');
            } else {
                recurringOptions.classList.add('hidden');
            }
        });

        editRecurringCheckbox.addEventListener('change', () => {
            if (editRecurringCheckbox.checked) {
                editRecurringOptions.classList.remove('hidden');
            } else {
                editRecurringOptions.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
