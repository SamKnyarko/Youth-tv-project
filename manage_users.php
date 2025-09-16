<?php
require 'config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

$users = $pdo->query("SELECT * FROM admin_users")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Youth TV Admin</title>
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
        
        .modal {
            transition: opacity 0.3s ease, transform 0.3s ease;
            transform: translateY(-20px);
            opacity: 0;
            pointer-events: none;
        }
        
        .modal.active {
            transform: translateY(0);
            opacity: 1;
            pointer-events: auto;
        }
        
        .modal-content {
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }
        
        .modal.active .modal-content {
            transform: scale(1);
        }
        
        .action-btn {
            transition: all 0.2s ease;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
        }
        
        .user-item {
            transition: all 0.3s ease;
        }
        
        .user-item:hover {
            background-color: #f8fafc;
            transform: translateY(-2px);
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
                    <h1 class="text-3xl font-bold text-gray-800">Manage Admin Users</h1>
                    <p class="text-gray-600">Create, edit, and manage administrator accounts</p>
                </div>

                <div class="grid grid-cols-1 gap-8">
                    <!-- Add User Card -->
                    <div class="card bg-white">
                        <div class="p-6">
                            <div class="flex items-center mb-6">
                                <div class="bg-primary p-3 rounded-full mr-3">
                                    <i class="fas fa-user-plus text-white text-xl"></i>
                                </div>
                                <h3 class="text-xl font-bold">Add New Admin User</h3>
                            </div>
                            
                            <form action="create_user.php" method="POST">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block mb-2 font-medium">Username</label>
                                        <input type="text" name="username" required 
                                            class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                    
                                    <div>
                                        <label class="block mb-2 font-medium">Password</label>
                                        <input type="password" name="password" required 
                                            class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                </div>
                                
                                <button type="submit" 
                                    class="mt-6 w-full bg-primary hover:bg-secondary text-white font-bold py-3 px-4 rounded-lg transition">
                                    <i class="fas fa-user-plus mr-2"></i> Create User
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Users List -->
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold">Administrator Accounts</h3>
                            <span class="bg-gray-200 text-gray-700 px-3 py-1 rounded-full font-medium">
                                <?= count($users) ?> users
                            </span>
                        </div>
                        
                        <div class="space-y-4">
                            <?php if (count($users) > 0): ?>
                                <?php foreach ($users as $user): ?>
                                <div class="card bg-white user-item">
                                    <div class="p-5">
                                        <div class="flex justify-between items-center">
                                            <div class="flex items-center">
                                                <div class="bg-gray-200 border-2 border-dashed rounded-xl w-16 h-16 flex items-center justify-center mr-4">
                                                    <i class="fas fa-user text-gray-500 text-2xl"></i>
                                                </div>
                                                <div>
                                                    <h4 class="font-bold text-lg"><?= htmlspecialchars($user['username']) ?></h4>
                                                    <p class="text-gray-600">Admin ID: <?= $user['id'] ?></p>
                                                </div>
                                            </div>
                                            
                                            <div class="flex space-x-2">
                                                <a href="edit_user.php?id=<?= $user['id'] ?>" 
                                                    class="bg-indigo-100 text-indigo-700 px-4 py-2 rounded-lg font-medium action-btn"
                                                    title="Edit User">
                                                    <i class="fas fa-edit mr-2"></i> Edit
                                                </a>
                                                
                                                <button class="bg-red-100 text-red-700 px-4 py-2 rounded-lg font-medium action-btn delete-btn"
                                                    data-id="<?= $user['id'] ?>" data-username="<?= htmlspecialchars($user['username']) ?>"
                                                    title="Delete User">
                                                    <i class="fas fa-trash mr-2"></i> Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="card bg-white p-8 text-center">
                                    <i class="fas fa-users-slash text-gray-300 text-5xl mb-4"></i>
                                    <h4 class="text-xl font-bold text-gray-600 mb-2">No Users Found</h4>
                                    <p class="text-gray-500">Add admin users to get started</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 modal">
        <div class="modal-content bg-white rounded-xl max-w-md w-full overflow-hidden">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="bg-red-100 p-3 rounded-full mr-3">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold">Confirm Deletion</h3>
                </div>
                
                <p class="text-gray-700 mb-6">
                    Are you sure you want to delete the admin account "<span id="username" class="font-semibold"></span>"? 
                    This action cannot be undone.
                </p>
                
                <div class="flex justify-end space-x-3">
                    <button id="cancelDelete" class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">
                        Cancel
                    </button>
                    <a id="confirmDelete" href="#" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium">
                        <i class="fas fa-trash mr-2"></i> Delete User
                    </a>
                </div>
            </div>
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
        
        // Delete confirmation modal
        const deleteModal = document.getElementById('deleteModal');
        const deleteButtons = document.querySelectorAll('.delete-btn');
        const usernameEl = document.getElementById('username');
        const confirmDeleteBtn = document.getElementById('confirmDelete');
        const cancelDeleteBtn = document.getElementById('cancelDelete');
        
        let currentUserId = null;
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', () => {
                currentUserId = button.getAttribute('data-id');
                const username = button.getAttribute('data-username');
                
                usernameEl.textContent = username;
                confirmDeleteBtn.href = `delete_user.php?id=${currentUserId}`;
                
                deleteModal.classList.add('active');
            });
        });
        
        cancelDeleteBtn.addEventListener('click', () => {
            deleteModal.classList.remove('active');
        });
        
        // Close modal when clicking outside
        deleteModal.addEventListener('click', (e) => {
            if (e.target === deleteModal) {
                deleteModal.classList.remove('active');
            }
        });
        
        // Close modal with ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && deleteModal.classList.contains('active')) {
                deleteModal.classList.remove('active');
            }
        });
    </script>
</body>
</html>