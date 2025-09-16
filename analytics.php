<?php
require 'config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

// Get analytics data
$total_plays = $pdo->query("SELECT COUNT(*) FROM plays")->fetchColumn();
$total_podcasts = $pdo->query("SELECT COUNT(*) FROM podcast_series")->fetchColumn();
$total_episodes = $pdo->query("SELECT COUNT(*) FROM episodes")->fetchColumn();

// Get plays by category
$category_plays = $pdo->query("
    SELECT p.category, COUNT(pl.id) as plays 
    FROM plays pl
    JOIN episodes e ON pl.episode_id = e.id
    JOIN podcast_series p ON e.series_id = p.id
    GROUP BY p.category
")->fetchAll(PDO::FETCH_KEY_PAIR);

// Get monthly plays
$monthly_plays = $pdo->query("
    SELECT 
        DATE_FORMAT(played_at, '%b %Y') as month, 
        COUNT(*) as count
    FROM plays
    WHERE played_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(played_at, '%Y-%m')
    ORDER BY played_at
")->fetchAll(PDO::FETCH_KEY_PAIR);

// Get top podcasts
$top_podcasts = $pdo->query("
    SELECT p.title, p.category, COUNT(pl.id) as plays
    FROM podcast_series p
    JOIN episodes e ON p.id = e.series_id
    LEFT JOIN plays pl ON e.id = pl.episode_id
    GROUP BY p.id
    ORDER BY plays DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Get recent plays
$recent_plays = $pdo->query("
    SELECT e.title AS episode_title, p.title AS podcast_title, pl.played_at 
    FROM plays pl
    JOIN episodes e ON pl.episode_id = e.id
    JOIN podcast_series p ON e.series_id = p.id
    ORDER BY pl.played_at DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Get play distribution
$play_distribution = $pdo->query("
    SELECT 
        CASE 
            WHEN HOUR(played_at) BETWEEN 0 AND 5 THEN 'Late Night (0-5)'
            WHEN HOUR(played_at) BETWEEN 6 AND 11 THEN 'Morning (6-11)'
            WHEN HOUR(played_at) BETWEEN 12 AND 17 THEN 'Afternoon (12-17)'
            ELSE 'Evening (18-23)'
        END AS time_period,
        COUNT(*) as count
    FROM plays
    GROUP BY time_period
    ORDER BY FIELD(time_period, 'Morning (6-11)', 'Afternoon (12-17)', 'Evening (18-23)', 'Late Night (0-5)')
")->fetchAll(PDO::FETCH_KEY_PAIR);

// Category names and colors
$categories = [
    'education' => ['name' => 'Education', 'color' => '#4f46e5'],
    'science' => ['name' => 'Science', 'color' => '#3b82f6'],
    'technology' => ['name' => 'Technology', 'color' => '#6366f1'],
    'business' => ['name' => 'Business', 'color' => '#06b6d4'],
    'entertainment' => ['name' => 'Entertainment', 'color' => '#ec4899'],
    'news' => ['name' => 'News', 'color' => '#64748b']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Podcast Analytics - Youth TV</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#4f46e5',
                        secondary: '#7c3aed',
                        dark: '#1e293b',
                        light: '#f8fafc'
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f3f4f6;
        }
        
        .dark body {
            background-color: #0f172a;
        }
        
        .sidebar {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }
        
        .nav-link {
            transition: all 0.3s ease;
            border-radius: 0.5rem;
        }
        
        .nav-link:hover, .nav-link.active {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
        }
        
        .card {
            border-radius: 1rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }
        
        .dark .card {
            background-color: #1e293b;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.25);
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .analytics-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        }
        
        .stat-card {
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .category-badge {
            transition: all 0.3s ease;
        }
        
        .category-badge:hover {
            transform: scale(1.05);
        }
        
        .progress-bar {
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .progress-filled {
            height: 100%;
            border-radius: 4px;
        }
        
        .chart-container {
            height: 300px;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200">
    <div class="flex min-h-screen">
        <div class="sidebar w-64 min-h-screen text-white fixed hidden md:block">
            <div class="p-6">
                <div class="flex items-center mb-10">
                    <div class="bg-white p-1 rounded-full mr-3">
                        <i class="fas fa-podcast text-primary text-2xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold">Youth TV</h2>
                </div>
                
                <nav class="space-y-1">
                    <a href="admin_dashboard.php" class="flex items-center p-3 nav-link">
                        <i class="fas fa-home mr-3"></i> Dashboard
                    </a>
                    <a href="manage_series.php" class="flex items-center p-3 nav-link">
                        <i class="fas fa-podcast mr-3"></i> Podcasts
                    </a>
                    <a href="manage_episodes.php" class="flex items-center p-3 nav-link">
                        <i class="fas fa-file-audio mr-3"></i> Episodes
                    </a>
                    <a href="analytics.php" class="flex items-center p-3 nav-link active">
                        <i class="fas fa-chart-line mr-3"></i> Analytics
                    </a>
                    <a href="settings.php" class="flex items-center p-3 nav-link">
                        <i class="fas fa-cog mr-3"></i> Settings
                    </a>
                </nav>
            </div>
            
            <div class="absolute bottom-0 w-full p-4 bg-white bg-opacity-10">
                <div class="flex justify-between items-center">
                    <button id="themeToggle" class="text-white p-2 rounded-full hover:bg-white hover:bg-opacity-20">
                        <i class="fas fa-moon" id="themeIcon"></i>
                    </button>
                    <a href="logout.php" class="flex items-center justify-center bg-white text-primary px-4 py-2 rounded-full font-bold hover:bg-opacity-90 transition">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                </div>
            </div>
        </div>

        <div class="md:ml-64 flex-1">
            <header class="bg-white dark:bg-gray-800 shadow-sm md:hidden">
                <div class="flex items-center justify-between p-4">
                    <button id="mobileMenuBtn" class="text-gray-600 dark:text-gray-300">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <div class="flex items-center">
                        <button id="mobileThemeToggle" class="text-gray-600 dark:text-gray-300 p-2 mr-3">
                            <i class="fas fa-moon"></i>
                        </button>
                        <div class="mr-4">
                            <span class="text-gray-600 dark:text-gray-300">Welcome, Admin</span>
                        </div>
                        <a href="logout.php" class="bg-primary text-white px-4 py-2 rounded-full text-sm font-bold">
                            <i class="fas fa-sign-out-alt mr-1"></i> Logout
                        </a>
                    </div>
                </div>
            </header>

            <div id="mobileSidebar" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
                <div class="absolute left-0 top-0 bottom-0 w-64 bg-primary text-white p-6">
                    <div class="flex justify-end mb-6">
                        <button id="closeMobileMenu" class="text-2xl">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <nav class="space-y-1">
                        <a href="admin_dashboard.php" class="flex items-center p-3 nav-link">
                            <i class="fas fa-home mr-3"></i> Dashboard
                        </a>
                        <a href="manage_series.php" class="flex items-center p-3 nav-link">
                            <i class="fas fa-podcast mr-3"></i> Podcasts
                        </a>
                        <a href="manage_episodes.php" class="flex items-center p-3 nav-link">
                            <i class="fas fa-file-audio mr-3"></i> Episodes
                        </a>
                        <a href="analytics.php" class="flex items-center p-3 nav-link active">
                            <i class="fas fa-chart-line mr-3"></i> Analytics
                        </a>
                        <a href="settings.php" class="flex items-center p-3 nav-link">
                            <i class="fas fa-cog mr-3"></i> Settings
                        </a>
                    </nav>
                </div>
            </div>

            <main class="p-6">
                <div class="analytics-header text-white p-6 rounded-xl mb-8">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div class="mb-4 md:mb-0">
                            <h1 class="text-3xl font-bold mb-2"><i class="fas fa-chart-line mr-2"></i> Podcast Analytics</h1>
                            <p class="text-indigo-100">Track performance and engagement with your podcast content</p>
                        </div>
                        <div class="flex items-center">
                            <div class="bg-white bg-opacity-20 p-2 rounded-lg mr-3">
                                <i class="fas fa-calendar-alt"></i>
                                <span class="ml-2">Last 6 Months</span>
                            </div>
                            <button class="bg-white text-primary px-4 py-2 rounded-lg font-bold hover:bg-opacity-90">
                                <i class="fas fa-download mr-2"></i> Export
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="card stat-card p-6 bg-white dark:bg-gray-800">
                        <div class="flex items-center">
                            <div class="p-3 bg-indigo-100 dark:bg-indigo-900 rounded-full mr-4">
                                <i class="fas fa-headphones text-indigo-600 dark:text-indigo-300 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-500 dark:text-gray-400">Total Plays</p>
                                <p class="text-3xl font-bold"><?= number_format($total_plays) ?></p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex justify-between text-sm mb-1">
                                <span>This month</span>
                                <span>+12.4%</span>
                            </div>
                            <div class="progress-bar bg-gray-200 dark:bg-gray-700">
                                <div class="progress-filled bg-indigo-600" style="width: 65%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card stat-card p-6 bg-white dark:bg-gray-800">
                        <div class="flex items-center">
                            <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full mr-4">
                                <i class="fas fa-podcast text-green-600 dark:text-green-300 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-500 dark:text-gray-400">Podcasts</p>
                                <p class="text-3xl font-bold"><?= $total_podcasts ?></p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex justify-between text-sm mb-1">
                                <span>Active series</span>
                                <span>+5.2%</span>
                            </div>
                            <div class="progress-bar bg-gray-200 dark:bg-gray-700">
                                <div class="progress-filled bg-green-500" style="width: 45%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card stat-card p-6 bg-white dark:bg-gray-800">
                        <div class="flex items-center">
                            <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full mr-4">
                                <i class="fas fa-file-audio text-blue-600 dark:text-blue-300 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-500 dark:text-gray-400">Episodes</p>
                                <p class="text-3xl font-bold"><?= $total_episodes ?></p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex justify-between text-sm mb-1">
                                <span>New this month</span>
                                <span>+8.7%</span>
                            </div>
                            <div class="progress-bar bg-gray-200 dark:bg-gray-700">
                                <div class="progress-filled bg-blue-500" style="width: 35%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <div class="card p-6">
                        <h3 class="text-xl font-bold mb-4">Monthly Plays</h3>
                        <div class="chart-container">
                            <canvas id="monthlyChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="card p-6">
                        <h3 class="text-xl font-bold mb-4">Plays by Category</h3>
                        <div class="chart-container">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="card p-6 lg:col-span-2">
                        <h3 class="text-xl font-bold mb-4">Top Podcasts</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-100 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left">Podcast</th>
                                        <th class="px-4 py-3 text-left">Category</th>
                                        <th class="px-4 py-3 text-right">Plays</th>
                                        <th class="px-4 py-3 text-right">Performance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($top_podcasts as $podcast): 
                                        $category = $categories[$podcast['category']];
                                    ?>
                                    <tr class="border-t border-gray-200 dark:border-gray-700">
                                        <td class="px-4 py-3 font-medium"><?= htmlspecialchars($podcast['title']) ?></td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-1 rounded-full text-xs" style="background-color: <?= $category['color'] ?>20; color: <?= $category['color'] ?>;">
                                                <?= $category['name'] ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold"><?= number_format($podcast['plays']) ?></td>
                                        <td class="px-4 py-3 text-right">
                                            <span class="text-green-500 font-bold">+12.5%</span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="card p-6">
                        <h3 class="text-xl font-bold mb-4">Recent Activity</h3>
                        <div class="space-y-4">
                            <?php foreach ($recent_plays as $play): ?>
                            <div class="flex items-start">
                                <div class="p-2 bg-indigo-100 dark:bg-indigo-900 rounded-full mr-3 mt-1">
                                    <i class="fas fa-play text-indigo-600 dark:text-indigo-300"></i>
                                </div>
                                <div>
                                    <p class="font-medium"><?= htmlspecialchars($play['episode_title']) ?></p>
                                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                                        <?= htmlspecialchars($play['podcast_title']) ?> • 
                                        <?= date('M j, Y g:i A', strtotime($play['played_at'])) ?>
                                    </p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="mt-8">
                            <h4 class="font-bold mb-3">Play Distribution</h4>
                            <?php 
                            $timeColors = [
                                'Morning (6-11)' => '#4f46e5',
                                'Afternoon (12-17)' => '#3b82f6',
                                'Evening (18-23)' => '#6366f1',
                                'Late Night (0-5)' => '#8b5cf6'
                            ];
                            
                            foreach ($play_distribution as $period => $count): 
                                $percentage = $total_plays ? round(($count / $total_plays) * 100) : 0;
                            ?>
                            <div class="mb-3">
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm"><?= $period ?></span>
                                    <span class="text-sm font-semibold"><?= $percentage ?>%</span>
                                </div>
                                <div class="progress-bar bg-gray-200 dark:bg-gray-700">
                                    <div class="progress-filled" style="width: <?= $percentage ?>%; background-color: <?= $timeColors[$period] ?>;"></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const closeMobileMenu = document.getElementById('closeMobileMenu');
            const mobileSidebar = document.getElementById('mobileSidebar');
            
            mobileMenuBtn.addEventListener('click', () => {
                mobileSidebar.classList.remove('hidden');
            });
            
            closeMobileMenu.addEventListener('click', () => {
                mobileSidebar.classList.add('hidden');
            });
            
            const themeToggle = document.getElementById('themeToggle');
            const mobileThemeToggle = document.getElementById('mobileThemeToggle');
            const themeIcon = document.getElementById('themeIcon');
            
            function toggleTheme() {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.theme = 'light';
                    themeIcon.classList.replace('fa-sun', 'fa-moon');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.theme = 'dark';
                    themeIcon.classList.replace('fa-moon', 'fa-sun');
                }
            }
            
            if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
                themeIcon.classList.replace('fa-moon', 'fa-sun');
            } else {
                document.documentElement.classList.remove('dark');
                themeIcon.classList.replace('fa-sun', 'fa-moon');
            }
            
            themeToggle.addEventListener('click', toggleTheme);
            mobileThemeToggle.addEventListener('click', toggleTheme);
            
            const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
            const monthlyData = {
                labels: <?= json_encode(array_keys($monthly_plays)) ?>,
                datasets: [{
                    label: 'Monthly Plays',
                    data: <?= json_encode(array_values($monthly_plays)) ?>,
                    backgroundColor: 'rgba(99, 102, 241, 0.2)',
                    borderColor: 'rgb(99, 102, 241)',
                    borderWidth: 3,
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: 'rgb(99, 102, 241)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgb(99, 102, 241)'
                }]
            };
            
            new Chart(monthlyCtx, {
                type: 'line',
                data: monthlyData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgb(30, 41, 59)',
                            titleFont: { family: 'Poppins' },
                            bodyFont: { family: 'Poppins' },
                            padding: 12,
                            displayColors: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { 
                                color: 'rgba(0, 0, 0, 0.05)',
                                borderDash: [5, 5]
                            },
                            ticks: { font: { family: 'Poppins' } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { family: 'Poppins' } }
                        }
                    }
                }
            });
            
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            const categoryData = {
                labels: [
                    'Education', 'Science', 'Technology', 
                    'Business', 'Entertainment', 'News'
                ],
                datasets: [{
                    data: [
                        <?= $category_plays['education'] ?? 0 ?>,
                        <?= $category_plays['science'] ?? 0 ?>,
                        <?= $category_plays['technology'] ?? 0 ?>,
                        <?= $category_plays['business'] ?? 0 ?>,
                        <?= $category_plays['entertainment'] ?? 0 ?>,
                        <?= $category_plays['news'] ?? 0 ?>
                    ],
                    backgroundColor: [
                        'rgba(79, 70, 229, 0.7)',
                        'rgba(59, 130, 246, 0.7)',
                        'rgba(99, 102, 241, 0.7)',
                        'rgba(6, 182, 212, 0.7)',
                        'rgba(236, 72, 153, 0.7)',
                        'rgba(100, 116, 139, 0.7)'
                    ],
                    borderColor: [
                        'rgb(79, 70, 229)',
                        'rgb(59, 130, 246)',
                        'rgb(99, 102, 241)',
                        'rgb(6, 182, 212)',
                        'rgb(236, 72, 153)',
                        'rgb(100, 116, 139)'
                    ],
                    borderWidth: 1
                }]
            };
            
            new Chart(categoryCtx, {
                type: 'doughnut',
                data: categoryData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            position: 'right',
                            labels: { font: { family: 'Poppins' } }
                        },
                        tooltip: {
                            backgroundColor: 'rgb(30, 41, 59)',
                            titleFont: { family: 'Poppins' },
                            bodyFont: { family: 'Poppins' },
                            padding: 12
                        }
                    },
                    cutout: '60%'
                }
            });
        });
    </script>
</body>
</html>