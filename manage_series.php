<?php
require 'config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

// Handle series creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    
    // File upload handling
    $cover_image = basename($_FILES['cover_image']['name']);
    move_uploaded_file($_FILES['cover_image']['tmp_name'], $upload_dir.'images/'.$cover_image);
    
    // Insert into database
    $stmt = $pdo->prepare("INSERT INTO podcast_series (title, description, category, cover_image) 
                          VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $description, $category, $cover_image]);
    
    header('Location: manage_series.php?success=1');
    exit;
}

// Get existing series
$series = $pdo->query("SELECT * FROM podcast_series ORDER BY created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Podcast Series - Youth TV Admin</title>
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
        
        .category-badge {
            transition: all 0.3s ease;
        }
        
        .category-badge:hover {
            transform: scale(1.05);
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
        
        .series-cover {
            transition: transform 0.3s ease;
        }
        
        .series-item:hover .series-cover {
            transform: scale(1.05);
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
                    <a href="manage_series.php" class="flex items-center p-3 nav-link active">
                        <i class="fas fa-stream mr-3"></i> Podcast Series
                    </a>
                    <a href="manage_episodes.php" class="flex items-center p-3 nav-link">
                        <i class="fas fa-file-audio mr-3"></i> Episodes
                    </a>
                    <a href="manage_users.php" class="flex items-center p-3 nav-link">
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
                        <a href="manage_series.php" class="flex items-center p-3 nav-link active">
                            <i class="fas fa-stream mr-3"></i> Podcast Series
                        </a>
                        <a href="manage_episodes.php" class="flex items-center p-3 nav-link">
                            <i class="fas fa-file-audio mr-3"></i> Episodes
                        </a>
                        <a href="manage_users.php" class="flex items-center p-3 nav-link">
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
                    <h1 class="text-3xl font-bold text-gray-800">Manage Podcast Series</h1>
                    <p class="text-gray-600">Create, edit, and manage your podcast series</p>
                </div>
                
                <!-- Success Message -->
                <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span>Series created successfully!</span>
                </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Create Series Form -->
                    <div class="card bg-white">
                        <div class="p-6 border-b">
                            <div class="flex items-center mb-4">
                                <div class="bg-primary p-3 rounded-full mr-3">
                                    <i class="fas fa-plus-circle text-white text-xl"></i>
                                </div>
                                <h3 class="text-xl font-bold">Create New Series</h3>
                            </div>
                            
                            <form method="POST" enctype="multipart/form-data">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block mb-2 font-medium">Series Title</label>
                                        <input type="text" name="title" required 
                                            class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                    
                                    <div>
                                        <label class="block mb-2 font-medium">Category</label>
                                        <select name="category" required 
                                            class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <option value="education">Education</option>
                                            <option value="science">Science</option>
                                            <option value="technology">Technology</option>
                                            <option value="business">Business</option>
                                            <option value="entertainment">Entertainment</option>
                                            <option value="news">News</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block mb-2 font-medium">Description</label>
                                        <textarea name="description" required rows="3"
                                            class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                                    </div>
                                    
                                    <div>
                                        <label class="block mb-2 font-medium">Cover Image</label>
                                        <div class="flex items-center justify-center w-full">
                                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                    <i class="fas fa-cloud-upload-alt text-gray-400 text-2xl mb-2"></i>
                                                    <p class="text-sm text-gray-500">Click to upload cover image</p>
                                                </div>
                                                <input type="file" name="cover_image" accept="image/*" required class="hidden" />
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" 
                                        class="w-full bg-primary hover:bg-secondary text-white font-bold py-3 px-4 rounded-lg transition mt-2">
                                        <i class="fas fa-save mr-2"></i> Create Series
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Series List -->
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold">Existing Series</h3>
                            <span class="bg-gray-200 text-gray-700 px-3 py-1 rounded-full font-medium">
                                <?= count($series) ?> series
                            </span>
                        </div>
                        
                        <div class="space-y-5">
                            <?php if (count($series) > 0): ?>
                                <?php foreach ($series as $s): 
                                    $episode_count = $pdo->query("SELECT COUNT(*) FROM episodes WHERE series_id = {$s['id']}")->fetchColumn();
                                ?>
                                <div class="card bg-white series-item">
                                    <div class="flex">
                                        <div class="w-24 relative overflow-hidden">
                                            <img src="<?= $image_web_path.$s['cover_image'] ?>" 
                                                class="w-full h-full object-cover series-cover">
                                        </div>
                                        
                                        <div class="flex-1 p-4">
                                            <div class="flex justify-between">
                                                <h4 class="font-bold text-lg"><?= htmlspecialchars($s['title']) ?></h4>
                                                <span class="bg-indigo-100 text-indigo-800 px-2 py-1 rounded-full text-xs category-badge">
                                                    <?= ucfirst($s['category']) ?>
                                                </span>
                                            </div>
                                            
                                            <p class="text-gray-600 text-sm my-2">
                                                <?= htmlspecialchars(substr($s['description'], 0, 80)) ?>...
                                            </p>
                                            
                                            <div class="flex justify-between items-center mt-2">
                                                <span class="text-gray-500 text-sm">
                                                    <i class="fas fa-file-audio mr-1"></i> <?= $episode_count ?> episodes
                                                </span>
                                                
                                                <div class="flex space-x-2">
                                                    <a href="manage_episodes.php?series_id=<?= $s['id'] ?>" 
                                                        class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm action-btn"
                                                        title="Manage Episodes">
                                                        <i class="fas fa-file-audio"></i>
                                                    </a>
                                                    
                                                    <a href="edit_series.php?id=<?= $s['id'] ?>" 
                                                        class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-sm action-btn"
                                                        title="Edit Series">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    <button class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm action-btn delete-btn"
                                                        data-id="<?= $s['id'] ?>" data-title="<?= htmlspecialchars($s['title']) ?>"
                                                        title="Delete Series">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="card bg-white p-8 text-center">
                                    <i class="fas fa-podcast text-gray-300 text-5xl mb-4"></i>
                                    <h4 class="text-xl font-bold text-gray-600 mb-2">No Series Found</h4>
                                    <p class="text-gray-500">Create your first podcast series to get started</p>
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
                    Are you sure you want to delete the series "<span id="seriesTitle" class="font-semibold"></span>"? 
                    This action cannot be undone and will delete all associated episodes.
                </p>
                
                <div class="flex justify-end space-x-3">
                    <button id="cancelDelete" class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">
                        Cancel
                    </button>
                    <a id="confirmDelete" href="#" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium">
                        <i class="fas fa-trash mr-2"></i> Delete Series
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
        const seriesTitleEl = document.getElementById('seriesTitle');
        const confirmDeleteBtn = document.getElementById('confirmDelete');
        const cancelDeleteBtn = document.getElementById('cancelDelete');
        
        let currentSeriesId = null;
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', () => {
                currentSeriesId = button.getAttribute('data-id');
                const seriesTitle = button.getAttribute('data-title');
                
                seriesTitleEl.textContent = seriesTitle;
                confirmDeleteBtn.href = `delete_series.php?id=${currentSeriesId}`;
                
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