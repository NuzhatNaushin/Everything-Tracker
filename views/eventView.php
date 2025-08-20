<!-- views/eventView.php -->

<?php
// This view expects the $events, $current_month, and $current_year variables to be set by the controller.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$username = $_SESSION['username'];

// Calculate calendar details
$first_day_of_month = mktime(0, 0, 0, $current_month, 1, $current_year);
$number_of_days = date('t', $first_day_of_month);
$date_components = getdate($first_day_of_month);
$month_name = $date_components['month'];
$day_of_week = $date_components['wday']; // 0 for Sunday, 6 for Saturday

$prev_month = $current_month == 1 ? 12 : $current_month - 1;
$prev_year = $current_month == 1 ? $current_year - 1 : $current_year;
$next_month = $current_month == 12 ? 1 : $current_month + 1;
$next_year = $current_month == 12 ? $current_year + 1 : $current_year;

// Group events by date
$events_by_date = [];
foreach ($events as $event) {
    $event_date = date('j', strtotime($event['start_date']));
    if (!isset($events_by_date[$event_date])) {
        $events_by_date[$event_date] = [];
    }
    $events_by_date[$event_date][] = $event;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Calendar - Everything Tracker</title>
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
            max-width: 900px;
        }
        .day-cell {
            min-height: 120px;
            background-color: white;
            border: 1px solid #e2e8f0;
        }
        .day-cell.inactive {
            background-color: #f8fafc;
            color: #cbd5e1;
        }
        .day-cell.today {
            background-color: #e3f2fd;
        }
        .event-dot {
            height: 8px;
            width: 8px;
            border-radius: 9999px;
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
            <h1 class="text-4xl font-extrabold text-gray-800">Event Calendar</h1>
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

        <!-- Calendar Navigation -->
        <div class="flex justify-between items-center bg-gray-100 p-4 rounded-t-xl">
            <a href="?month=<?= $prev_month ?>&year=<?= $prev_year ?>" class="text-gray-600 hover:text-gray-800"><i class="fas fa-chevron-left"></i></a>
            <h2 class="text-2xl font-bold"><?= $month_name . ' ' . $current_year ?></h2>
            <a href="?month=<?= $next_month ?>&year=<?= $next_year ?>" class="text-gray-600 hover:text-gray-800"><i class="fas fa-chevron-right"></i></a>
        </div>

        <!-- Calendar Grid -->
        <div class="grid grid-cols-7 text-center font-bold text-gray-700 bg-gray-200">
            <div class="p-2">Sun</div>
            <div class="p-2">Mon</div>
            <div class="p-2">Tue</div>
            <div class="p-2">Wed</div>
            <div class="p-2">Thu</div>
            <div class="p-2">Fri</div>
            <div class="p-2">Sat</div>
        </div>
        <div class="grid grid-cols-7">
            <?php
            // Fill initial blank days
            for ($i = 0; $i < $day_of_week; $i++):
                echo '<div class="day-cell inactive p-2"></div>';
            endfor;

            // Fill days with events
            for ($day = 1; $day <= $number_of_days; $day++):
                $is_today = ($day == date('j') && $current_month == date('n') && $current_year == date('Y')) ? 'today' : '';
                ?>
                <div class="day-cell p-2 <?= $is_today ?> relative cursor-pointer" onclick="openDailyModal(<?= $day ?>)">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-lg font-bold"><?= $day ?></span>
                        <button onclick="event.stopPropagation(); openAddModal(<?= $day ?>)" class="text-gray-400 hover:text-blue-500"><i class="fas fa-plus-circle"></i></button>
                    </div>
                    <?php if (isset($events_by_date[$day])): ?>
                        <div class="event-list space-y-1">
                            <?php foreach ($events_by_date[$day] as $event): ?>
                                <button onclick="event.stopPropagation(); openEditModal(<?= htmlspecialchars(json_encode($event)) ?>)" class="w-full text-left truncate px-2 py-1 rounded-md text-xs text-white" style="background-color: <?= htmlspecialchars($event['color_code']) ?>;">
                                    <?= htmlspecialchars($event['title']) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>
    </div>

    <!-- Daily Events Modal -->
    <div id="dailyModal" class="modal-overlay fixed inset-0 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-lg mx-4">
            <h2 id="dailyModalTitle" class="text-2xl font-bold mb-4">Events for </h2>
            <div id="dailyEventsList" class="space-y-4 max-h-96 overflow-y-auto">
                <!-- Daily events will be loaded here by JavaScript -->
            </div>
            <div class="flex justify-end space-x-4 mt-6">
                <button type="button" onclick="closeDailyModal()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold rounded-lg transition duration-300">Close</button>
            </div>
        </div>
    </div>

    <!-- Add Event Modal -->
    <div id="addModal" class="modal-overlay fixed inset-0 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-md mx-4">
            <h2 class="text-2xl font-bold mb-4">Add Event</h2>
            <form id="addEventForm" action="../controllers/eventController.php" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="space-y-4">
                    <div>
                        <label for="add-title" class="block text-gray-700">Title</label>
                        <input type="text" name="title" id="add-title" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                    </div>
                    <div>
                        <label for="add-description" class="block text-gray-700">Description</label>
                        <textarea name="description" id="add-description" rows="2" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"></textarea>
                    </div>
                    <div>
                        <label for="add-start_date" class="block text-gray-700">Start Date</label>
                        <input type="date" name="start_date" id="add-start_date" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                    </div>
                    <div>
                        <label for="add-end_date" class="block text-gray-700">End Date</label>
                        <input type="date" name="end_date" id="add-end_date" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                    </div>
                    <div>
                        <label for="add-category" class="block text-gray-700">Category</label>
                        <select name="category" id="add-category" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                            <option value="Personal">Personal</option>
                            <option value="Work">Work</option>
                            <option value="Social">Social</option>
                            <option value="Health">Health</option>
                        </select>
                    </div>
                    <div>
                        <label for="add-color_code" class="block text-gray-700">Color</label>
                        <input type="color" name="color_code" id="add-color_code" class="w-full h-12 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                    </div>
                </div>
                <div class="flex justify-end space-x-4 mt-6">
                    <button type="button" onclick="closeAddModal()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold rounded-lg transition duration-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition duration-300">Add Event</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Event Modal -->
    <div id="editModal" class="modal-overlay fixed inset-0 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-md mx-4">
            <h2 class="text-2xl font-bold mb-4">Edit Event</h2>
            <form id="editEventForm" action="../controllers/eventController.php" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit-id">
                <div class="space-y-4">
                    <div>
                        <label for="edit-title" class="block text-gray-700">Title</label>
                        <input type="text" name="title" id="edit-title" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                    </div>
                    <div>
                        <label for="edit-description" class="block text-gray-700">Description</label>
                        <textarea name="description" id="edit-description" rows="2" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"></textarea>
                    </div>
                    <div>
                        <label for="edit-start_date" class="block text-gray-700">Start Date</label>
                        <input type="date" name="start_date" id="edit-start_date" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                    </div>
                    <div>
                        <label for="edit-end_date" class="block text-gray-700">End Date</label>
                        <input type="date" name="end_date" id="edit-end_date" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                    </div>
                    <div>
                        <label for="edit-category" class="block text-gray-700">Category</label>
                        <select name="category" id="edit-category" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                            <option value="Personal">Personal</option>
                            <option value="Work">Work</option>
                            <option value="Social">Social</option>
                            <option value="Health">Health</option>
                        </select>
                    </div>
                    <div>
                        <label for="edit-color_code" class="block text-gray-700">Color</label>
                        <input type="color" name="color_code" id="edit-color_code" class="w-full h-12 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                    </div>
                </div>
                <div class="flex justify-between items-center mt-6">
                    <button type="button" onclick="openShareModal(document.getElementById('edit-id').value); closeEditModal();" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-lg transition duration-300">
                        Share
                    </button>
                    <div class="flex space-x-4">
                        <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold rounded-lg transition duration-300">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition duration-300">Save Changes</button>
                    </div>
                </div>
            </form>
            <form action="../controllers/eventController.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this event?');" class="mt-4">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="delete-id">
                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg transition duration-300">
                        Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Share Event Modal -->
    <div id="shareModal" class="modal-overlay fixed inset-0 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-md mx-4">
            <h2 class="text-2xl font-bold mb-4">Share Event</h2>
            <p class="text-gray-600 mb-4">Invite others to this event by email.</p>
            <form id="shareEventForm" action="../controllers/eventController.php" method="POST">
                <input type="hidden" name="action" value="share">
                <input type="hidden" name="event_id" id="share-event-id">
                <div class="space-y-4">
                    <div>
                        <label for="invitee-email" class="block text-gray-700">Invitee's Email</label>
                        <input type="email" name="email" id="invitee-email" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                    </div>
                </div>
                <div class="flex justify-end space-x-4 mt-6">
                    <button type="button" onclick="closeShareModal()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold rounded-lg transition duration-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-lg transition duration-300">Send Invite</button>
                </div>
            </form>
        </div>
    </div>


    <script>
        const addModal = document.getElementById('addModal');
        const editModal = document.getElementById('editModal');
        const dailyModal = document.getElementById('dailyModal');
        const shareModal = document.getElementById('shareModal');
        const eventsByDate = <?= json_encode($events_by_date) ?>;
        const monthName = '<?= $month_name ?>';
        const currentYear = '<?= $current_year ?>';

        function openAddModal(day) {
            const currentMonth = <?= $current_month ?>;
            const currentYear = <?= $current_year ?>;
            const formattedDate = `${currentYear}-${String(currentMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            document.getElementById('add-start_date').value = formattedDate;
            document.getElementById('add-end_date').value = formattedDate;
            addModal.classList.add('open');
        }

        function closeAddModal() {
            addModal.classList.remove('open');
        }
        
        function openEditModal(event) {
            document.getElementById('edit-id').value = event.id;
            document.getElementById('delete-id').value = event.id; // Pass ID to the delete form
            document.getElementById('edit-title').value = event.title;
            document.getElementById('edit-description').value = event.description;
            document.getElementById('edit-start_date').value = event.start_date;
            document.getElementById('edit-end_date').value = event.end_date;
            document.getElementById('edit-category').value = event.category;
            document.getElementById('edit-color_code').value = event.color_code;
            editModal.classList.add('open');
        }

        function closeEditModal() {
            editModal.classList.remove('open');
        }

        function openDailyModal(day) {
            document.getElementById('dailyModalTitle').innerText = `Events for ${monthName} ${day}, ${currentYear}`;
            const eventsForDay = eventsByDate[day] || [];
            const dailyEventsList = document.getElementById('dailyEventsList');
            dailyEventsList.innerHTML = '';

            if (eventsForDay.length > 0) {
                eventsForDay.forEach(event => {
                    const eventElement = document.createElement('div');
                    eventElement.className = 'bg-gray-100 p-3 rounded-lg border-l-4'
                    eventElement.style.borderLeftColor = event.color_code;
                    eventElement.innerHTML = `
                        <div class="flex justify-between items-center">
                            <h4 class="font-semibold text-lg">${event.title}</h4>
                            <div class="space-x-2">
                                <button onclick="event.stopPropagation(); openEditModal(${JSON.stringify(event).replace(/"/g, "'")}); closeDailyModal();" class="text-blue-500 hover:text-blue-700"><i class="fas fa-edit"></i></button>
                                <form action="../controllers/eventController.php" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this event?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="${event.id}">
                                    <button type="submit" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </div>
                        <p class="text-gray-600 text-sm mt-1">${event.description || 'No description'}</p>
                        <p class="text-gray-500 text-xs mt-1">Start: ${event.start_date} | End: ${event.end_date}</p>
                    `;
                    dailyEventsList.appendChild(eventElement);
                });
            } else {
                dailyEventsList.innerHTML = '<p class="text-center text-gray-500">No events for this day.</p>';
            }

            dailyModal.classList.add('open');
        }

        function closeDailyModal() {
            dailyModal.classList.remove('open');
        }

        function openShareModal(eventId) {
            document.getElementById('share-event-id').value = eventId;
            shareModal.classList.add('open');
        }

        function closeShareModal() {
            shareModal.classList.remove('open');
        }
    </script>
</body>
</html>
