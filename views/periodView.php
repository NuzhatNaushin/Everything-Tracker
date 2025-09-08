<!-- views/periodView.php -->

<?php

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$username = $_SESSION['username'];
$month_name = date('F', mktime(0, 0, 0, $current_month, 10));

// calendar details
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
        .tooltip {
            position: absolute;
            background-color: #333;
            color: white;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.875rem;
            z-index: 100;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease;
            white-space: pre-wrap;
            pointer-events: none;
            max-width: 250px;
            left: 50%;
            transform: translateX(-50%);
            bottom: 120%;
        }
        .calendar-day:hover .tooltip {
            opacity: 1;
            visibility: visible;
        }
        .tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #333 transparent transparent transparent;
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
                <button onclick="openManagePeriodsModal()" class="bg-pink-600 hover:bg-pink-700 text-white font-bold py-2 px-4 rounded-lg shadow-lg transition duration-300 transform hover:scale-105">
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
                    <div onclick="openSymptomModal('<?= $current_date ?>', '<?= htmlspecialchars(json_encode($has_symptom ? $symptoms_by_date[$current_date] : null), ENT_QUOTES, 'UTF-8') ?>')" class="calendar-day relative cursor-pointer rounded-lg p-2 shadow-md transition-all transform hover:scale-105 <?= $bgColor ?>">
                        <div class="text-sm font-bold text-right <?= $is_ovulation_day ? 'text-green-800' : ($is_fertile_day ? 'text-blue-800' : ($is_period_day ? 'text-pink-800' : 'text-gray-800')) ?>">
                            <?= $day ?>
                        </div>
                        <div class="mt-2 flex flex-col items-center space-y-1">
                            <?php if ($has_symptom):
                                $symptom = $symptoms_by_date[$current_date]; ?>
                                <?php if ($symptom['flow_level']): ?>
                                    <i class="fas fa-droplet symptom-icon flow-level-icon <?= strtolower($symptom['flow_level']) ?>"></i>
                                <?php endif; ?>
                                <?php if ($symptom['cramps'] === 'yes'): ?>
                                    <i class="fas fa-heart-crack symptom-icon text-gray-700"></i>
                                <?php endif; ?>
                                <?php if ($symptom['mood']): ?>
                                    <i class="fas fa-face-smile symptom-icon text-gray-700"></i>
                                <?php endif; ?>
                                <?php if ($symptom['notes']): ?>
                                    <i class="fas fa-info-circle text-gray-700 symptom-icon"></i>
                                <?php endif; ?>
                                <div class="tooltip z-50">
                                    <div class="font-bold text-base mb-1"><?= date('F j, Y', strtotime($current_date)) ?></div>
                                    <?php if ($symptom['cramps'] === 'yes'): ?>
                                        <div class="flex items-center"><i class="fas fa-heart-crack mr-2"></i>Cramps</div>
                                    <?php endif; ?>
                                    <?php if ($symptom['flow_level']): ?>
                                        <div class="flex items-center"><i class="fas fa-droplet mr-2"></i>Flow: <?= ucfirst($symptom['flow_level']) ?></div>
                                    <?php endif; ?>
                                    <?php if ($symptom['mood']): ?>
                                        <div class="flex items-center"><i class="fas fa-face-smile mr-2"></i>Mood: <?= htmlspecialchars($symptom['mood']) ?></div>
                                    <?php endif; ?>
                                    <?php if ($symptom['notes']): ?>
                                        <div class="flex items-center mt-2"><i class="fas fa-sticky-note mr-2"></i>Notes: <br><?= nl2br(htmlspecialchars($symptom['notes'])) ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <!-- Manage Periods Modal -->
    <div id="managePeriodsModal" class="modal-overlay fixed inset-0 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-lg mx-4">
            <h2 class="text-2xl font-bold mb-4 text-pink-700">Manage Periods</h2>
            <div id="period-list" class="space-y-4 max-h-96 overflow-y-auto pr-2">
                <!-- Period entries will be populated here by JS -->
            </div>
            <button onclick="openAddPeriodModal()" class="mt-6 w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-3 px-4 rounded-lg shadow-lg transition duration-300 transform hover:scale-105">
                <i class="fas fa-plus mr-2"></i>Add New Period
            </button>
            <div class="flex justify-end space-x-4 mt-6">
                <button type="button" onclick="closeManagePeriodsModal()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold rounded-lg transition duration-300">Close</button>
            </div>
        </div>
    </div>

    <!-- Add/Edit Period Modal -->
    <div id="addEditPeriodModal" class="modal-overlay fixed inset-0 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-md mx-4">
            <h2 id="periodModalTitle" class="text-2xl font-bold mb-4 text-pink-700">Log New Period</h2>
            <form id="periodForm" action="periodController.php" method="POST">
                <input type="hidden" name="action" id="periodAction" value="add-period">
                <input type="hidden" name="period_id" id="periodId">
                <div class="space-y-4">
                    <div>
                        <label for="start_date" class="block text-gray-700">Start Date</label>
                        <input type="date" name="start_date" id="periodStartDate" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-pink-500" required>
                    </div>
                    <div>
                        <label for="end_date" class="block text-gray-700">End Date</label>
                        <input type="date" name="end_date" id="periodEndDate" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-pink-500" required>
                    </div>
                </div>
                <div class="flex justify-end space-x-4 mt-6">
                    <button type="button" onclick="closeAddEditPeriodModal()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold rounded-lg transition duration-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white font-bold rounded-lg shadow-lg transition duration-300">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Symptom Logging Modal -->
    <div id="symptomModal" class="modal-overlay fixed inset-0 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-md mx-4">
            <h2 class="text-2xl font-bold mb-4 text-pink-700">Log Symptoms for <span id="symptomDateDisplay"></span></h2>
            <form id="symptomForm" action="periodController.php" method="POST">
                <input type="hidden" name="action" id="symptomAction" value="save-symptom">
                <input type="hidden" name="date" id="symptomDateInput">
                <input type="hidden" name="symptom_id" id="symptomId">
                <div class="space-y-4">
                    <div class="flex items-center space-x-4">
                        <label class="font-semibold text-gray-700">Cramps</label>
                        <input type="checkbox" name="cramps" id="crampsCheckbox" value="yes" class="form-checkbox text-pink-500 w-5 h-5">
                    </div>
                    <div>
                        <label class="font-semibold text-gray-700">Flow Level</label>
                        <div class="mt-2 flex space-x-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="flow_level" value="low" class="flow-checkbox form-checkbox text-pink-500 w-4 h-4" id="lowFlow">
                                <span class="ml-2 text-sm">Low</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="flow_level" value="medium" class="flow-checkbox form-checkbox text-pink-500 w-4 h-4" id="mediumFlow">
                                <span class="ml-2 text-sm">Medium</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="flow_level" value="heavy" class="flow-checkbox form-checkbox text-pink-500 w-4 h-4" id="heavyFlow">
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
                <div class="flex justify-between space-x-4 mt-6">
                    <button type="button" onclick="confirmSymptomDeletion()" id="deleteSymptomBtn" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-bold rounded-lg transition duration-300 hidden">Delete</button>
                    <div class="flex-grow"></div>
                    <button type="button" onclick="closeSymptomModal()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold rounded-lg transition duration-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white font-bold rounded-lg shadow-lg transition duration-300">Save</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Custom Confirmation Modal for Periods -->
    <div id="deletePeriodConfirmationModal" class="modal-overlay fixed inset-0 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-sm mx-4 text-center">
            <p class="text-lg font-semibold mb-4 text-gray-700">Are you sure you want to delete this period?</p>
            <div class="flex justify-center space-x-4">
                <button type="button" onclick="closeDeletePeriodConfirmationModal()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold rounded-lg transition duration-300">Cancel</button>
                <form id="deletePeriodForm" action="periodController.php" method="POST" class="inline">
                    <input type="hidden" name="action" value="delete-period">
                    <input type="hidden" name="period_id" id="deletePeriodId">
                    <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-bold rounded-lg transition duration-300">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Custom Confirmation Modal for Symptoms -->
    <div id="deleteSymptomConfirmationModal" class="modal-overlay fixed inset-0 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-sm mx-4 text-center">
            <p class="text-lg font-semibold mb-4 text-gray-700">Are you sure you want to delete this symptom log?</p>
            <div class="flex justify-center space-x-4">
                <button type="button" onclick="closeDeleteSymptomConfirmationModal()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold rounded-lg transition duration-300">Cancel</button>
                <form id="deleteSymptomForm" action="periodController.php" method="POST" class="inline">
                    <input type="hidden" name="action" value="delete-symptom">
                    <input type="hidden" name="symptom_id" id="deleteSymptomId">
                    <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-bold rounded-lg transition duration-300">Delete</button>
                </form>
            </div>
        </div>
    </div>


    <script>
        const managePeriodsModal = document.getElementById('managePeriodsModal');
        const addEditPeriodModal = document.getElementById('addEditPeriodModal');
        const symptomModal = document.getElementById('symptomModal');
        const deletePeriodConfirmationModal = document.getElementById('deletePeriodConfirmationModal');
        const deleteSymptomConfirmationModal = document.getElementById('deleteSymptomConfirmationModal');

        const allPeriods = <?= json_encode($all_periods) ?>;
        const symptomsByDate = <?= json_encode($symptoms_by_date) ?>;
        
        function openManagePeriodsModal() {
            populatePeriodList();
            managePeriodsModal.classList.add('open');
        }

        function closeManagePeriodsModal() {
            managePeriodsModal.classList.remove('open');
        }
        
        function openAddPeriodModal() {
            document.getElementById('periodModalTitle').innerText = 'Log New Period';
            document.getElementById('periodAction').value = 'add-period';
            document.getElementById('periodId').value = '';
            document.getElementById('periodStartDate').value = '';
            document.getElementById('periodEndDate').value = '';
            addEditPeriodModal.classList.add('open');
            closeManagePeriodsModal();
        }

        function openEditPeriodModal(periodId, startDate, endDate) {
            document.getElementById('periodModalTitle').innerText = 'Edit Period';
            document.getElementById('periodAction').value = 'update-period';
            document.getElementById('periodId').value = periodId;
            document.getElementById('periodStartDate').value = startDate;
            document.getElementById('periodEndDate').value = endDate;
            addEditPeriodModal.classList.add('open');
            closeManagePeriodsModal();
        }

        function closeAddEditPeriodModal() {
            addEditPeriodModal.classList.remove('open');
        }

        function populatePeriodList() {
            const periodList = document.getElementById('period-list');
            periodList.innerHTML = '';
            if (allPeriods.length === 0) {
                periodList.innerHTML = '<p class="text-center text-gray-500">No periods logged yet. Add one to get started!</p>';
                return;
            }
            allPeriods.forEach(period => {
                const periodItem = document.createElement('div');
                periodItem.className = 'bg-gray-100 p-4 rounded-lg flex items-center justify-between';
                const startDate = new Date(period.start_date);
                const endDate = new Date(period.end_date);
                const formatter = new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                periodItem.innerHTML = `
                    <div>
                        <p class="font-semibold">
                            <i class="fas fa-calendar-alt mr-2 text-pink-500"></i>
                            ${formatter.format(startDate)} - ${formatter.format(endDate)}
                        </p>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="openEditPeriodModal(${period.id}, '${period.start_date}', '${period.end_date}')" class="text-blue-500 hover:text-blue-700 transition duration-300">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="confirmPeriodDeletion(${period.id})" class="text-red-500 hover:text-red-700 transition duration-300">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
                periodList.appendChild(periodItem);
            });
        }

        function openSymptomModal(date, symptomDataString) {
            const symptomData = JSON.parse(symptomDataString);
            
            document.getElementById('symptomDateDisplay').innerText = date;
            document.getElementById('symptomDateInput').value = date;
            
            document.getElementById('crampsCheckbox').checked = false;
            document.getElementById('mood').value = '';
            document.getElementById('notes').value = '';
            document.getElementById('symptomId').value = '';
            document.querySelectorAll('.flow-checkbox').forEach(cb => cb.checked = false);

            if (symptomData) {
                document.getElementById('symptomId').value = symptomData.id;
                document.getElementById('symptomAction').value = 'save-symptom'; // Will be handled as an update
                document.getElementById('crampsCheckbox').checked = symptomData.cramps === 'yes';
                document.getElementById('mood').value = symptomData.mood || '';
                document.getElementById('notes').value = symptomData.notes || '';
                if (symptomData.flow_level) {
                    const flowCheckbox = document.getElementById(symptomData.flow_level + 'Flow');
                    if (flowCheckbox) {
                        flowCheckbox.checked = true;
                    }
                }
                document.getElementById('deleteSymptomBtn').classList.remove('hidden');
            } else {
                document.getElementById('symptomAction').value = 'save-symptom'; // New symptom
                document.getElementById('deleteSymptomBtn').classList.add('hidden');
            }
            
            symptomModal.classList.add('open');
        }

        function closeSymptomModal() {
            symptomModal.classList.remove('open');
        }

        // Handle mutually exclusive flow level checkboxes
        document.querySelectorAll('.flow-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    document.querySelectorAll('.flow-checkbox').forEach(otherCheckbox => {
                        if (otherCheckbox !== this) {
                            otherCheckbox.checked = false;
                        }
                    });
                }
            });
        });

        // Confirmation handlers
        function confirmPeriodDeletion(periodId) {
            document.getElementById('deletePeriodId').value = periodId;
            deletePeriodConfirmationModal.classList.add('open');
        }

        function closeDeletePeriodConfirmationModal() {
            deletePeriodConfirmationModal.classList.remove('open');
        }

        function confirmSymptomDeletion() {
            const symptomId = document.getElementById('symptomId').value;
            if (symptomId) {
                document.getElementById('deleteSymptomId').value = symptomId;
                deleteSymptomConfirmationModal.classList.add('open');
            }
        }
        
        function closeDeleteSymptomConfirmationModal() {
            deleteSymptomConfirmationModal.classList.remove('open');
        }
    </script>
</body>
</html>
