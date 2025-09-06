<!-- views/periodView.php -->

<?php
// This view expects variables from the controller like $current_month, $current_year,
// $period_days, $symptoms_by_date, $next_period_start, etc.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$username = $_SESSION['username'];
$month_name = date('F', mktime(0, 0, 0, $current_month, 10));

// Calculate calendar details
$first_day_of_month = mktime(0, 0, 0, $current_month, 1, $current_year);
$number_of_days = date('t', $first_day_of_month);
$date_components = getdate($first_day_of_month);
$day_of_week = $date_components['wday']; // 0 for Sunday, 6 for Saturday

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
    <title>Period Tracker - Everything Tracker</title>
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
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.5rem;
        }
        .calendar-day {
            min-height: 100px;
            @media (min-width: 768px) {
                min-height: 120px;
            }
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
        .flow-level-icon.low { color: #fecaca; }
        .flow-level-icon.medium { color: #ef4444; }
        .flow-level-icon.heavy { color: #b91c1c; }
        .symptom-icon {
            font-size: 1.2rem;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <div class="container mx-auto bg-white rounded-xl shadow-2xl p-8 my-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-4xl font-extrabold text-gray-800">Period Tracker</h1>
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
                <button onclick="openAddPeriodModal()" class="bg-pink-600 hover:bg-pink-700 text-white font-bold py-2 px-4 rounded-lg shadow-lg transition duration-300 transform hover:scale-105">
                    <i class="fas fa-plus mr-2"></i>Log Period
                </button>
            </div>
        </div>

        <!-- Predictions & Key Info -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-gray-50 p-6 rounded-lg shadow-inner text-center">
                <h3 class="text-xl font-bold mb-2 text-pink-700">Next Period</h3>
                <p class="text-3xl font-extrabold text-pink-600">
                    <?= $next_period_start ? date('F j, Y', strtotime($next_period_start)) : 'N/A' ?>
                </p>
                <p class="text-gray-500 text-sm mt-1">Based on avg. cycle length of <?= $average_cycle_length ?? '28' ?> days</p>
            </div>
            <div class="bg-gray-50 p-6 rounded-lg shadow-inner text-center">
                <h3 class="text-xl font-bold mb-2 text-green-700">Ovulation Day</h3>
                <p class="text-3xl font-extrabold text-green-600">
                    <?= $ovulation_day ? date('F j, Y', strtotime($ovulation_day)) : 'N/A' ?>
                </p>
                <p class="text-gray-500 text-sm mt-1">Fertile window starts 5 days before</p>
            </div>
            <div class="bg-gray-50 p-6 rounded-lg shadow-inner text-center">
                <h3 class="text-xl font-bold mb-2 text-blue-700">Fertile Window</h3>
                <p class="text-2xl font-extrabold text-blue-600">
                    <?php if ($fertile_window_start && $fertile_window_end): ?>
                        <?= date('M j', strtotime($fertile_window_start)) ?> - <?= date('M j, Y', strtotime($fertile_window_end)) ?>
                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <!-- Calendar -->
        <div class="bg-white p-6 rounded-lg shadow-2xl">
            <div class="calendar-grid text-center font-bold text-gray-500 mb-4">
                <span class="text-red-500">Sun</span>
                <span>Mon</span>
                <span>Tue</span>
                <span>Wed</span>
                <span>Thu</span>
                <span>Fri</span>
                <span class="text-blue-500">Sat</span>
            </div>
            <div class="calendar-grid">
                <?php for ($i = 0; $i < $day_of_week; $i++): ?>
                    <div class="calendar-day"></div>
                <?php endfor; ?>
                <?php for ($day = 1; $day <= $number_of_days; $day++):
                    $current_date = date('Y-m-d', mktime(0, 0, 0, $current_month, $day, $current_year));
                    $is_period_day = in_array($current_date, $period_days);
                    $is_fertile_day = $fertile_window_start && $fertile_window_end && ($current_date >= $fertile_window_start && $current_date <= $fertile_window_end);
                    $is_ovulation_day = $ovulation_day && ($current_date === $ovulation_day);
                    $has_symptom = isset($symptoms_by_date[$current_date]);
                    
                    $bgColor = '';
                    if ($is_ovulation_day) {
                        $bgColor = 'bg-green-300';
                    } elseif ($is_fertile_day) {
                        $bgColor = 'bg-blue-300';
                    } elseif ($is_period_day) {
                        $bgColor = 'bg-pink-300';
                    } else {
                        $bgColor = 'bg-gray-100 hover:bg-gray-200';
                    }
                    ?>
                    <div onclick="openSymptomModal('<?= $current_date ?>')" class="calendar-day cursor-pointer rounded-lg p-2 shadow-md transition-all transform hover:scale-105 <?= $bgColor ?>">
                        <div class="text-sm font-bold text-right <?= $is_ovulation_day ? 'text-green-800' : ($is_fertile_day ? 'text-blue-800' : ($is_period_day ? 'text-pink-800' : 'text-gray-800')) ?>">
                            <?= $day ?>
                        </div>
                        <div class="mt-2 flex flex-col items-center space-y-1">
                            <?php if ($has_symptom):
                                $symptom = $symptoms_by_date[$current_date]; ?>
                                <i class="fas fa-droplet symptom-icon flow-level-icon <?= strtolower($symptom['flow_level']) ?>"></i>
                                <?php if ($symptom['cramps']): ?>
                                    <i class="fas fa-heart-crack symptom-icon text-gray-700"></i>
                                <?php endif; ?>
                                <?php if ($symptom['mood']): ?>
                                    <i class="fas fa-face-smile symptom-icon text-gray-700"></i>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <!-- Add Period Modal -->
    <div id="addPeriodModal" class="modal-overlay fixed inset-0 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-md mx-4">
            <h2 class="text-2xl font-bold mb-4 text-pink-700">Log New Period</h2>
            <form action="periodController.php" method="POST">
                <input type="hidden" name="action" value="add-period">
                <div class="space-y-4">
                    <div>
                        <label for="start_date" class="block text-gray-700">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-pink-500" required>
                    </div>
                    <div>
                        <label for="end_date" class="block text-gray-700">End Date</label>
                        <input type="date" name="end_date" id="end_date" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-pink-500" required>
                    </div>
                </div>
                <div class="flex justify-end space-x-4 mt-6">
                    <button type="button" onclick="closeAddPeriodModal()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold rounded-lg transition duration-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white font-bold rounded-lg transition duration-300">Log Period</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Symptom Logging Modal -->
    <div id="symptomModal" class="modal-overlay fixed inset-0 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-md mx-4">
            <h2 class="text-2xl font-bold mb-4 text-pink-700">Log Symptoms for <span id="symptomDateDisplay"></span></h2>
            <form action="periodController.php" method="POST">
                <input type="hidden" name="action" value="log-symptom">
                <input type="hidden" name="date" id="symptomDateInput">
                <div class="space-y-4">
                    <div class="flex items-center space-x-4">
                        <label class="font-semibold text-gray-700">Cramps</label>
                        <input type="checkbox" name="cramps" value="yes" class="form-checkbox text-pink-500 w-5 h-5">
                    </div>
                    <div>
                        <label class="font-semibold text-gray-700">Flow Level</label>
                        <div class="mt-2 flex space-x-4">
                            <label class="flex items-center">
                                <input type="radio" name="flow_level" value="low" class="form-radio text-pink-500 w-4 h-4" checked>
                                <span class="ml-2 text-sm">Low</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="flow_level" value="medium" class="form-radio text-pink-500 w-4 h-4">
                                <span class="ml-2 text-sm">Medium</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="flow_level" value="heavy" class="form-radio text-pink-500 w-4 h-4">
                                <span class="ml-2 text-sm">Heavy</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label for="mood" class="font-semibold text-gray-700">Mood</label>
                        <input type="text" name="mood" id="mood" placeholder="e.g., Happy, Tired, Crampy" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-pink-500">
                    </div>
                    <div>
                        <label for="notes" class="font-semibold text-gray-700">Notes</label>
                        <textarea name="notes" id="notes" rows="3" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-pink-500" placeholder="Add any additional notes about your day..."></textarea>
                    </div>
                </div>
                <div class="flex justify-end space-x-4 mt-6">
                    <button type="button" onclick="closeSymptomModal()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold rounded-lg transition duration-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white font-bold rounded-lg transition duration-300">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const addPeriodModal = document.getElementById('addPeriodModal');
        const symptomModal = document.getElementById('symptomModal');

        function openAddPeriodModal() {
            addPeriodModal.classList.add('open');
        }

        function closeAddPeriodModal() {
            addPeriodModal.classList.remove('open');
        }

        function openSymptomModal(date) {
            document.getElementById('symptomDateDisplay').innerText = date;
            document.getElementById('symptomDateInput').value = date;
            
            // Here you could fetch existing symptom data for the day
            // and pre-fill the form, but for now we'll just open the modal.
            symptomModal.classList.add('open');
        }

        function closeSymptomModal() {
            symptomModal.classList.remove('open');
        }
    </script>
</body>
</html>
