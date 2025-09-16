<?php
require 'config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $stmt = $pdo->prepare("INSERT INTO events (title, description, full_description, event_date, event_time, location, image_url, price, max_attendees, registration_link, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['title'],
                    $_POST['description'],
                    $_POST['full_description'],
                    $_POST['event_date'],
                    $_POST['event_time'],
                    $_POST['location'],
                    $_POST['image_url'],
                    $_POST['price'],
                    $_POST['max_attendees'],
                    $_POST['registration_link'],
                    isset($_POST['featured']) ? 1 : 0
                ]);
                break;
            
            case 'edit':
                $stmt = $pdo->prepare("UPDATE events SET title = ?, description = ?, full_description = ?, event_date = ?, event_time = ?, location = ?, image_url = ?, price = ?, max_attendees = ?, registration_link = ?, featured = ?, status = ? WHERE id = ?");
                $stmt->execute([
                    $_POST['title'],
                    $_POST['description'],
                    $_POST['full_description'],
                    $_POST['event_date'],
                    $_POST['event_time'],
                    $_POST['location'],
                    $_POST['image_url'],
                    $_POST['price'],
                    $_POST['max_attendees'],
                    $_POST['registration_link'],
                    isset($_POST['featured']) ? 1 : 0,
                    $_POST['status'],
                    $_POST['event_id']
                ]);
                break;
            
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
                $stmt->execute([$_POST['event_id']]);
                break;
        }
        header('Location: manage_events.php');
        exit;
    }
}

$events = $pdo->query("SELECT * FROM events ORDER BY event_date DESC, created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Events - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard-sidebar { width: 260px; transition: all 0.3s; }
        .dashboard-main { margin-left: 260px; transition: all 0.3s; }
        @media (max-width: 768px) {
            .dashboard-sidebar { margin-left: -260px; }
            .dashboard-main { margin-left: 0; }
            .sidebar-active .dashboard-sidebar { margin-left: 0; }
        }
        .modal { transition: opacity 0.3s ease; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <aside class="dashboard-sidebar bg-gray-800 text-white fixed h-full overflow-y-auto">
            <div class="p-4">
                <h2 class="text-2xl font-bold mb-6 flex items-center">
                    <i class="fas fa-podcast mr-2"></i> Youth TV Admin
                </h2>
                <nav class="space-y-2">
                    <a href="admin_dashboard.php" class="flex items-center p-3 hover:bg-gray-700 rounded">
                        <i class="fas fa-home mr-3"></i> Dashboard
                    </a>
                    <a href="manage_series.php" class="flex items-center p-3 hover:bg-gray-700 rounded">
                        <i class="fas fa-stream mr-3"></i> Podcast Series
                    </a>
                    <a href="manage_episodes.php" class="flex items-center p-3 hover:bg-gray-700 rounded">
                        <i class="fas fa-file-audio mr-3"></i> Episodes
                    </a>
                    <a href="manage_events.php" class="flex items-center p-3 bg-gray-700 rounded">
                        <i class="fas fa-calendar-alt mr-3"></i> Events
                    </a>
                    <a href="manage_users.php" class="flex items-center p-3 hover:bg-gray-700 rounded">
                        <i class="fas fa-users mr-3"></i> Users
                    </a>
                    <a href="analytics.php" class="flex items-center p-3 hover:bg-gray-700 rounded">
                        <i class="fas fa-chart-line mr-3"></i> Analytics
                    </a>
                    <a href="settings.php" class="flex items-center p-3 hover:bg-gray-700 rounded">
                        <i class="fas fa-cog mr-3"></i> Settings
                    </a>
                </nav>
            </div>
        </aside>

        <main class="dashboard-main flex-1">
            <header class="bg-white shadow-sm">
                <div class="flex items-center justify-between p-4">
                    <button id="sidebarToggle" class="md:hidden text-gray-600">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-gray-800">Manage Events</h1>
                        <div class="ml-auto flex items-center">
                            <span class="text-gray-600 mr-4">Welcome, Admin</span>
                            <a href="logout.php" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <div class="p-6">
                <div class="bg-white rounded-lg shadow mb-6">
                    <div class="p-6 border-b flex justify-between items-center">
                        <h2 class="text-xl font-bold">Events Management</h2>
                        <button onclick="openModal('eventModal')" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                            <i class="fas fa-plus mr-2"></i> Add New Event
                        </button>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date & Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Attendees</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($events as $event): ?>
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <?php if ($event['image_url']): ?>
                                            <img src="<?= htmlspecialchars($event['image_url']) ?>" alt="" class="w-10 h-10 rounded mr-3 object-cover">
                                            <?php endif; ?>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($event['title']) ?></div>
                                                <div class="text-sm text-gray-500"><?= htmlspecialchars(substr($event['description'], 0, 50)) ?>...</div>
                                                <?php if ($event['featured']): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <i class="fas fa-star mr-1"></i> Featured
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <div><?= date('M d, Y', strtotime($event['event_date'])) ?></div>
                                        <div class="text-gray-500"><?= date('h:i A', strtotime($event['event_time'])) ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($event['location']) ?></td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            <?php
                                            switch($event['status']) {
                                                case 'upcoming': echo 'bg-blue-100 text-blue-800'; break;
                                                case 'ongoing': echo 'bg-green-100 text-green-800'; break;
                                                case 'completed': echo 'bg-gray-100 text-gray-800'; break;
                                                case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                            }
                                            ?>">
                                            <?= ucfirst($event['status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?= $event['current_attendees'] ?><?= $event['max_attendees'] > 0 ? '/' . $event['max_attendees'] : '' ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium space-x-2">
                                        <button onclick="editEvent(<?= htmlspecialchars(json_encode($event)) ?>)" class="text-indigo-600 hover:text-indigo-900">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteEvent(<?= $event['id'] ?>)" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div id="eventModal" class="modal fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-screen overflow-y-auto">
            <div class="p-6 border-b">
                <h3 class="text-lg font-medium" id="modalTitle">Add New Event</h3>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="event_id" id="eventId">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Event Title</label>
                        <input type="text" name="title" id="eventTitle" required class="w-full p-2 border rounded-md">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Short Description</label>
                        <textarea name="description" id="eventDescription" required class="w-full p-2 border rounded-md h-20"></textarea>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Description</label>
                        <textarea name="full_description" id="eventFullDescription" required class="w-full p-2 border rounded-md h-32"></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Event Date</label>
                        <input type="date" name="event_date" id="eventDate" required class="w-full p-2 border rounded-md">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Event Time</label>
                        <input type="time" name="event_time" id="eventTime" required class="w-full p-2 border rounded-md">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                        <input type="text" name="location" id="eventLocation" required class="w-full p-2 border rounded-md">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Image URL</label>
                        <input type="url" name="image_url" id="eventImageUrl" class="w-full p-2 border rounded-md">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                        <input type="text" name="price" id="eventPrice" value="Free" class="w-full p-2 border rounded-md">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Attendees (0 = unlimited)</label>
                        <input type="number" name="max_attendees" id="eventMaxAttendees" value="0" min="0" class="w-full p-2 border rounded-md">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Registration Link</label>
                        <input type="url" name="registration_link" id="eventRegistrationLink" class="w-full p-2 border rounded-md">
                    </div>
                    
                    <div id="statusField" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" id="eventStatus" class="w-full p-2 border rounded-md">
                            <option value="upcoming">Upcoming</option>
                            <option value="ongoing">Ongoing</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="featured" id="eventFeatured" class="mr-2">
                            <span class="text-sm font-medium text-gray-700">Featured Event</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-6 border-t">
                    <button type="button" onclick="closeModal('eventModal')" class="px-4 py-2 text-gray-700 bg-gray-200 rounded hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                        Save Event
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteModal" class="modal fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <h3 class="text-lg font-medium mb-4">Delete Event</h3>
                <p class="text-gray-600 mb-6">Are you sure you want to delete this event? This action cannot be undone.</p>
                <form method="POST" class="flex justify-end space-x-3">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="event_id" id="deleteEventId">
                    <button type="button" onclick="closeModal('deleteModal')" class="px-4 py-2 text-gray-700 bg-gray-200 rounded hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('sidebarToggle').addEventListener('click', () => {
        document.documentElement.classList.toggle('sidebar-active');
    });

    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
        document.getElementById(modalId).classList.add('flex');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        document.getElementById(modalId).classList.remove('flex');
        if (modalId === 'eventModal') {
            resetForm();
        }
    }

    function resetForm() {
        document.getElementById('formAction').value = 'add';
        document.getElementById('eventId').value = '';
        document.getElementById('modalTitle').textContent = 'Add New Event';
        document.getElementById('statusField').classList.add('hidden');
        document.querySelector('#eventModal form').reset();
        document.getElementById('eventPrice').value = 'Free';
        document.getElementById('eventMaxAttendees').value = '0';
    }

    function editEvent(event) {
        document.getElementById('formAction').value = 'edit';
        document.getElementById('eventId').value = event.id;
        document.getElementById('modalTitle').textContent = 'Edit Event';
        document.getElementById('statusField').classList.remove('hidden');
        
        document.getElementById('eventTitle').value = event.title;
        document.getElementById('eventDescription').value = event.description;
        document.getElementById('eventFullDescription').value = event.full_description;
        document.getElementById('eventDate').value = event.event_date;
        document.getElementById('eventTime').value = event.event_time;
        document.getElementById('eventLocation').value = event.location;
        document.getElementById('eventImageUrl').value = event.image_url || '';
        document.getElementById('eventPrice').value = event.price;
        document.getElementById('eventMaxAttendees').value = event.max_attendees;
        document.getElementById('eventRegistrationLink').value = event.registration_link || '';
        document.getElementById('eventStatus').value = event.status;
        document.getElementById('eventFeatured').checked = event.featured == 1;
        
        openModal('eventModal');
    }

    function deleteEvent(eventId) {
        document.getElementById('deleteEventId').value = eventId;
        openModal('deleteModal');
    }

    window.onclick = function(event) {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (event.target === modal) {
                closeModal(modal.id);
            }
        });
    }
    </script>
</body>
</html>