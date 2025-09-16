<?php
require 'config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

$user_id = $_GET['id'] ?? null;
$current_user = null;

if ($user_id) {
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
    $stmt->execute([$user_id]);
    $current_user = $stmt->fetch();
}

if (!$current_user) {
    header('Location: manage_users.php');
    exit;
}

// Handle user update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
    
    if ($password) {
        $stmt = $pdo->prepare("UPDATE admin_users SET username = ?, password = ? WHERE id = ?");
        $stmt->execute([$username, $password, $user_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE admin_users SET username = ? WHERE id = ?");
        $stmt->execute([$username, $user_id]);
    }
    
    header("Location: manage_users.php?success=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Youth TV Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
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
        /* Same styles as manage_episodes.php */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f3f4f6;
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
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
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
                        <i class="fas fa-stream mr-3"></i> Podcast Series
                    </a>
                    <a href="manage_episodes.php" class="flex items-center p-3 nav-link">
                        <i class="fas fa-file-audio mr-3"></i> Episodes
                    </a>
                    <a href="manage_users.php" class="flex items-center p-3 nav-link active">
                        <i class="fas fa-users mr-3"></i> Users
                    </a>
                    <a href="analytics.php" class="flex items-center p-3 nav-link">
                        <i class="fas fa-chart-line mr-3"></i> Analytics
                    </a>
                    <a href="settings.php" class="flex items-center p-3 nav-link">
                        <i class="fas fa-cog mr-3"></i> Settings
                    </a>
                </nav>
            </div>
            
            <div class="absolute bottom-0 w-full p-4 bg-white bg-opacity-10">
                <a href="logout.php" class="flex items-center justify-center bg-white text-primary px-4 py-2 rounded-full font-bold hover:bg-opacity-90 transition">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="md:ml-64 flex-1">
            <!-- Mobile Header -->
            <header class="bg-white shadow-sm md:hidden">
                <div class="flex items-center justify-between p-4">
                    <button id="mobileMenuBtn" class="text-gray-600">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <div class="flex items-center">
                        <div class="mr-4">
                            <span class="text-gray-600">Welcome, Admin</span>
                        </div>
                        <a href="logout.php" class="bg-primary text-white px-4 py-2 rounded-full text-sm font-bold">
                            <i class="fas fa-sign-out-alt mr-1"></i> Logout
                        </a>
                    </div>
                </div>
            </header>

            <!-- Mobile Sidebar -->
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
                            <i class="fas fa-stream mr-3"></i> Podcast Series
                        </a>
                        <a href="manage_episodes.php" class="flex items-center p-3 nav-link">
                            <i class="fas fa-file-audio mr-3"></i> Episodes
                        </a>
                        <a href="manage_users.php" class="flex items-center p-3 nav-link active">
                            <i class="fas fa-users mr-3"></i> Users
                        </a>
                        <a href="analytics.php" class="flex items-center p-3 nav-link">
                            <i class="fas fa-chart-line mr-3"></i> Analytics
                        </a>
                        <a href="settings.php" class="flex items-center p-3 nav-link">
                            <i class="fas fa-cog mr-3"></i> Settings
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content Area -->
            <main class="p-6">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-800">Edit Admin User</h1>
                    <p class="text-gray-600">Update user credentials</p>
                </div>

                <div class="grid grid-cols-1 gap-8">
                    <!-- User Form -->
                    <div class="card bg-white">
                        <div class="p-6">
                            <div class="flex items-center mb-6">
                                <div class="bg-primary p-3 rounded-full mr-3">
                                    <i class="fas fa-user-edit text-white text-xl"></i>
                                </div>
                                <h3 class="text-xl font-bold">Edit <?= htmlspecialchars($current_user['username']) ?></h3>
                            </div>
                            
                            <form method="POST">
                                <div class="grid grid-cols-1 gap-6">
                                    <div>
                                        <label class="block mb-2 font-medium">Username</label>
                                        <input type="text" name="username" required 
                                            value="<?= htmlspecialchars($current_user['username']) ?>"
                                            class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                    
                                    <div>
                                        <label class="block mb-2 font-medium">New Password</label>
                                        <input type="password" name="password" 
                                            class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                            placeholder="Leave blank to keep current password">
                                    </div>
                                    
                                    <div class="flex space-x-4">
                                        <button type="submit" 
                                            class="flex-1 bg-primary hover:bg-secondary text-white font-bold py-3 px-4 rounded-lg transition">
                                            <i class="fas fa-save mr-2"></i> Save Changes
                                        </button>
                                        <a href="manage_users.php" 
                                            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-4 rounded-lg text-center transition">
                                            <i class="fas fa-times mr-2"></i> Cancel
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const closeMobileMenu = document.getElementById('closeMobileMenu');
        const mobileSidebar = document.getElementById('mobileSidebar');
        
        mobileMenuBtn.addEventListener('click', () => {
            mobileSidebar.classList.remove('hidden');
        });
        
        closeMobileMenu.addEventListener('click', () => {
            mobileSidebar.classList.add('hidden');
        });
    </script>
</body>
</html>
