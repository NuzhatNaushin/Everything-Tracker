<!-- views/expenseView.php -->

<?php
// This view expects the $expenses, $monthly_summary, $current_month, $current_year, and $categories variables to be set by the controller.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$username = $_SESSION['username'];
$month_name = date('F', mktime(0, 0, 0, $current_month, 10));

$prev_month = $current_month == 1 ? 12 : $current_month - 1;
$prev_year = $current_month == 1 ? $current_year - 1 : $current_year;
$next_month = $current_month == 12 ? 1 : $current_month + 1;
$next_year = $current_month == 12 ? $current_year + 1 : $current_year;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker - Everything Tracker</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0f4f8 0%, #d9e2ec 100%);
            color: #333;
        }
        .container {
            max-width: 1000px;
        }
        .modal-overlay {
            background-color: rgba(0, 0, 0, 0.5);
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }
        .modal-overlay.open {
            visibility: visible;
            opacity: 1;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <div class="container mx-auto bg-white rounded-xl shadow-2xl p-8 my-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-4xl font-extrabold text-gray-800">Expense Tracker</h1>
            <div class="flex items-center space-x-4">
                <a href="../views/dashboard.php" class="text-gray-500 hover:text-gray-700 transition duration-300">
                    <i class="fas fa-arrow-left mr-2"></i>Dashboard
                </a>
                <a href="../views/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg shadow-lg transition duration-300 transform hover:scale-105">
                    Logout
                </a>
            </div>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">
                <p><?= htmlspecialchars($_SESSION['message']) ?></p>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <!-- Monthly Navigation and Summary -->
        <div class="flex flex-col md:flex-row justify-between items-center bg-gray-100 p-4 rounded-t-xl mb-6">
            <div class="flex items-center space-x-4">
                <a href="?month=<?= $prev_month ?>&year=<?= $prev_year ?>" class="text-gray-600 hover:text-gray-800"><i class="fas fa-chevron-left"></i></a>
                <h2 class="text-2xl font-bold"><?= $month_name . ' ' . $current_year ?></h2>
                <a href="?month=<?= $next_month ?>&year=<?= $next_year ?>" class="text-gray-600 hover:text-gray-800"><i class="fas fa-chevron-right"></i></a>
            </div>
            <div class="mt-4 md:mt-0">
                <span class="text-lg font-bold">Total Expenses:</span>
                <span class="text-2xl text-red-600 font-extrabold">
                    $<?= number_format(array_sum(array_column($expenses, 'amount')), 2) ?>
                </span>
            </div>
            <button onclick="openAddModal()" class="mt-4 md:mt-0 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-lg transition duration-300 transform hover:scale-105">
                <i class="fas fa-plus mr-2"></i>Add Expense
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Filter by Category -->
            <div class="md:col-span-2 bg-gray-50 p-6 rounded-lg shadow-inner">
                <h3 class="text-xl font-bold mb-3">Expenses</h3>
                <form action="expenseController.php" method="GET" class="mb-4">
                    <input type="hidden" name="month" value="<?= $current_month ?>">
                    <input type="hidden" name="year" value="<?= $current_year ?>">
                    <label for="category-filter" class="block text-gray-700 mb-1">Filter by Category:</label>
                    <div class="flex items-center space-x-2">
                        <select name="category" id="category-filter" class="w-full p-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= htmlspecialchars($category) ?>" <?= (isset($_GET['category']) && $_GET['category'] === $category) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold rounded-lg transition duration-300">Filter</button>
                    </div>
                </form>

                <div class="bg-white p-4 rounded-lg shadow-lg max-h-96 overflow-y-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="px-4 py-2 text-left text-sm font-semibold">Date</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold">Amount</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold">Category</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold">Description</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (!empty($expenses)): ?>
                                <?php foreach ($expenses as $expense): ?>
                                    <tr>
                                        <td class="px-4 py-2 text-sm"><?= htmlspecialchars($expense['date']) ?></td>
                                        <td class="px-4 py-2 text-sm">$<?= number_format($expense['amount'], 2) ?></td>
                                        <td class="px-4 py-2 text-sm"><?= htmlspecialchars($expense['category']) ?></td>
                                        <td class="px-4 py-2 text-sm"><?= htmlspecialchars($expense['description']) ?></td>
                                        <td class="px-4 py-2 text-sm">
                                            <div class="flex space-x-2">
                                                <button onclick="openEditModal(<?= htmlspecialchars(json_encode($expense)) ?>)" class="text-blue-500 hover:text-blue-700"><i class="fas fa-edit"></i></button>
                                                <form action="expenseController.php" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this expense?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $expense['id'] ?>">
                                                    <button type="submit" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-4 py-4 text-center text-gray-500">No expenses for this month.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Monthly Summary & Reports -->
            <div class="md:col-span-1 flex flex-col space-y-6">
                <!-- Monthly Summary -->
                <div class="bg-gray-50 p-6 rounded-lg shadow-inner">
                    <h3 class="text-xl font-bold mb-3">Monthly Summary</h3>
                    <ul class="space-y-2">
                        <?php if (!empty($monthly_summary)): ?>
                            <?php foreach ($monthly_summary as $summary): ?>
                                <li class="flex justify-between items-center text-sm bg-white p-3 rounded-lg shadow-md">
                                    <span class="font-semibold"><?= htmlspecialchars($summary['category']) ?></span>
                                    <span class="text-gray-700">$<?= number_format($summary['total_amount'], 2) ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-center text-gray-500">No summary data available.</p>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Report Generator -->
                <div class="bg-gray-50 p-6 rounded-lg shadow-inner">
                    <h3 class="text-xl font-bold mb-3">Generate Report</h3>
                    <form action="expenseController.php" method="GET" onsubmit="generateReport(event)">
                        <div class="space-y-4">
                            <div>
                                <label for="report-start" class="block text-gray-700">Start Date</label>
                                <input type="date" id="report-start" name="start_date" class="w-full p-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                            </div>
                            <div>
                                <label for="report-end" class="block text-gray-700">End Date</label>
                                <input type="date" id="report-end" name="end_date" class="w-full p-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                            </div>
                        </div>
                        <button type="submit" class="w-full mt-6 bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg shadow-lg transition duration-300 transform hover:scale-105">
                            Generate Report
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Expense Modal -->
    <div id="expenseModal" class="modal-overlay fixed inset-0 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-md mx-4">
            <h2 id="modal-title" class="text-2xl font-bold mb-4">Add Expense</h2>
            <form id="expenseForm" action="expenseController.php" method="POST">
                <input type="hidden" name="action" id="modal-action" value="add">
                <input type="hidden" name="id" id="modal-id">
                <div class="space-y-4">
                    <div>
                        <label for="amount" class="block text-gray-700">Amount ($)</label>
                        <input type="number" step="0.01" name="amount" id="amount" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                    </div>
                    <div>
                        <label for="category" class="block text-gray-700">Category</label>
                        <select name="category" id="category" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                            <option value="Food">Food</option>
                            <option value="Transportation">Transportation</option>
                            <option value="Utilities">Utilities</option>
                            <option value="Entertainment">Entertainment</option>
                            <option value="Groceries">Groceries</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label for="description" class="block text-gray-700">Description</label>
                        <textarea name="description" id="description" rows="2" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"></textarea>
                    </div>
                    <div>
                        <label for="date" class="block text-gray-700">Date</label>
                        <input type="date" name="date" id="date" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                    </div>
                </div>
                <div class="flex justify-end space-x-4 mt-6">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold rounded-lg transition duration-300">Cancel</button>
                    <button type="submit" id="modal-submit-btn" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition duration-300">Add Expense</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Report Modal -->
    <div id="reportModal" class="modal-overlay fixed inset-0 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-2xl mx-4">
            <h2 class="text-2xl font-bold mb-4">Expense Report</h2>
            <div id="reportContent" class="space-y-4 max-h-96 overflow-y-auto">
                <!-- Report content will be loaded here by JavaScript -->
            </div>
            <div class="flex justify-end space-x-4 mt-6">
                <button type="button" onclick="closeReportModal()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold rounded-lg transition duration-300">Close</button>
            </div>
        </div>
    </div>

    <script>
        const expenseModal = document.getElementById('expenseModal');
        const reportModal = document.getElementById('reportModal');
        const expenseForm = document.getElementById('expenseForm');
        
        function openAddModal() {
            document.getElementById('modal-title').innerText = 'Add Expense';
            document.getElementById('modal-action').value = 'add';
            document.getElementById('modal-submit-btn').innerText = 'Add Expense';
            expenseForm.reset();
            document.getElementById('date').valueAsDate = new Date();
            expenseModal.classList.add('open');
        }

        function openEditModal(expense) {
            document.getElementById('modal-title').innerText = 'Edit Expense';
            document.getElementById('modal-action').value = 'update';
            document.getElementById('modal-submit-btn').innerText = 'Save Changes';
            document.getElementById('modal-id').value = expense.id;
            document.getElementById('amount').value = expense.amount;
            document.getElementById('category').value = expense.category;
            document.getElementById('description').value = expense.description;
            document.getElementById('date').value = expense.date;
            expenseModal.classList.add('open');
        }

        function closeModal() {
            expenseModal.classList.remove('open');
        }

        function closeReportModal() {
            reportModal.classList.remove('open');
        }

        async function generateReport(event) {
            event.preventDefault();
            const startDate = document.getElementById('report-start').value;
            const endDate = document.getElementById('report-end').value;
            const reportContent = document.getElementById('reportContent');

            reportContent.innerHTML = '<p class="text-center text-gray-500">Loading report...</p>';
            reportModal.classList.add('open');

            try {
                // Fetch data from the controller via AJAX
                const response = await fetch(`../controllers/expenseController.php?report=true&start_date=${startDate}&end_date=${endDate}`);
                const reportData = await response.json();

                if (reportData.length > 0) {
                    let html = '<table class="min-w-full divide-y divide-gray-200"><thead><tr><th class="px-4 py-2 text-left text-sm font-semibold">Date</th><th class="px-4 py-2 text-left text-sm font-semibold">Category</th><th class="px-4 py-2 text-left text-sm font-semibold">Amount</th></tr></thead><tbody>';
                    let total = 0;
                    reportData.forEach(item => {
                        html += `<tr><td class="px-4 py-2 text-sm">${item.date}</td><td class="px-4 py-2 text-sm">${item.category}</td><td class="px-4 py-2 text-sm">$${parseFloat(item.amount).toFixed(2)}</td></tr>`;
                        total += parseFloat(item.amount);
                    });
                    html += `</tbody></table><div class="mt-4 text-right font-bold text-lg">Total Expenses: $${total.toFixed(2)}</div>`;
                    reportContent.innerHTML = html;
                } else {
                    reportContent.innerHTML = '<p class="text-center text-gray-500">No expenses found for this date range.</p>';
                }

            } catch (error) {
                console.error("Error generating report:", error);
                reportContent.innerHTML = '<p class="text-center text-red-500">Failed to generate report. Please try again.</p>';
            }
        }
    </script>
</body>
</html>
