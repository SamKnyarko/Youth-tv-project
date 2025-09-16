<?php
require 'config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

$total_series = $pdo->query("SELECT COUNT(*) FROM podcast_series")->fetchColumn();
$total_episodes = $pdo->query("SELECT COUNT(*) FROM episodes")->fetchColumn();
$total_events = $pdo->query("SELECT COUNT(*) FROM events WHERE status = 'upcoming'")->fetchColumn();
$latest_series = $pdo->query("SELECT title FROM podcast_series ORDER BY created_at DESC LIMIT 1")->fetchColumn();
$featured_events = $pdo->query("SELECT COUNT(*) FROM events WHERE featured = 1 AND status = 'upcoming'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
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
        #deleteModal { transition: opacity 0.3s ease; }
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
                    <a href="admin_dashboard.php" class="flex items-center p-3 bg-gray-700 rounded">
                        <i class="fas fa-home mr-3"></i> Dashboard
                    </a>
                    <a href="manage_series.php" class="flex items-center p-3 hover:bg-gray-700 rounded">
                        <i class="fas fa-stream mr-3"></i> Podcast Series
                    </a>
                    <a href="manage_episodes.php" class="flex items-center p-3 hover:bg-gray-700 rounded">
                        <i class="fas fa-file-audio mr-3"></i> Episodes
                    </a>
                    <a href="manage_events.php" class="flex items-center p-3 hover:bg-gray-700 rounded">
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
                        <div class="mr-4">
                            <span class="text-gray-600">Welcome, Admin</span>
                        </div>
                        <a href="logout.php" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </a>
                    </div>
                </div>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 p-6">
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="p-3 bg-indigo-100 rounded-full mr-4">
                            <i class="fas fa-stream text-indigo-600 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-gray-500">Total Series</p>
                            <p class="text-2xl font-bold"><?= $total_series ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-full mr-4">
                            <i class="fas fa-file-audio text-green-600 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-gray-500">Total Episodes</p>
                            <p class="text-2xl font-bold"><?= $total_episodes ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-full mr-4">
                            <i class="fas fa-calendar-alt text-purple-600 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-gray-500">Upcoming Events</p>
                            <p class="text-2xl font-bold"><?= $total_events ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-full mr-4">
                            <i class="fas fa-star text-yellow-600 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-gray-500">Featured Events</p>
                            <p class="text-2xl font-bold"><?= $featured_events ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <div>
                                <h3 class="text-xl font-bold mb-4"><i class="fas fa-stream mr-2"></i> Quick Series Access</h3>
                                <a href="manage_series.php" class="block bg-indigo-100 text-indigo-700 p-6 rounded-lg text-center hover:bg-indigo-200 transition">
                                    <i class="fas fa-stream text-4xl mb-4"></i>
                                    <h4 class="text-xl font-bold">Manage Podcast Series</h4>
                                    <p class="text-gray-600 mt-2">Create and edit podcast series</p>
                                </a>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold mb-4"><i class="fas fa-file-audio mr-2"></i> Quick Episode Access</h3>
                                <a href="manage_episodes.php" class="block bg-green-100 text-green-700 p-6 rounded-lg text-center hover:bg-green-200 transition">
                                    <i class="fas fa-file-audio text-4xl mb-4"></i>
                                    <h4 class="text-xl font-bold">Manage Episodes</h4>
                                    <p class="text-gray-600 mt-2">Add and edit podcast episodes</p>
                                </a>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold mb-4"><i class="fas fa-calendar-alt mr-2"></i> Quick Events Access</h3>
                                <a href="manage_events.php" class="block bg-purple-100 text-purple-700 p-6 rounded-lg text-center hover:bg-purple-200 transition">
                                    <i class="fas fa-calendar-alt text-4xl mb-4"></i>
                                    <h4 class="text-xl font-bold">Manage Events</h4>
                                    <p class="text-gray-600 mt-2">Create and manage upcoming events</p>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
    document.getElementById('sidebarToggle').addEventListener('click', () => {
        document.documentElement.classList.toggle('sidebar-active');
    });
    </script>
</body>
</html>