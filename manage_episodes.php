<?php
require 'config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

$series_id = $_GET['series_id'] ?? null;
$current_series = null;

if ($series_id) {
    $stmt = $pdo->prepare("SELECT * FROM podcast_series WHERE id = ?");
    $stmt->execute([$series_id]);
    $current_series = $stmt->fetch();
}

// Handle episode creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $current_series) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $duration = $_POST['duration'];  // Now in seconds
    
    // File upload handling
    $audio_file = basename($_FILES['audio_file']['name']);
    move_uploaded_file($_FILES['audio_file']['tmp_name'], $upload_dir.'audio/'.$audio_file);
    
    // Insert into database
    $stmt = $pdo->prepare("INSERT INTO episodes (series_id, title, description, audio_file, duration) 
                          VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$series_id, $title, $description, $audio_file, $duration]);
    
    header("Location: manage_episodes.php?series_id=$series_id&success=1");
    exit;
}

// Get all series for dropdown
$all_series = $pdo->query("SELECT * FROM podcast_series ORDER BY title")->fetchAll();

// Get episodes for current series
$episodes = [];
if ($current_series) {
    $stmt = $pdo->prepare("SELECT * FROM episodes WHERE series_id = ? ORDER BY created_at DESC");
    $stmt->execute([$series_id]);
    $episodes = $stmt->fetchAll();
}

// Function to format seconds to MM:SS
function format_duration($seconds) {
    $minutes = floor($seconds / 60);
    $seconds = $seconds % 60;
    return sprintf('%02d:%02d', $minutes, $seconds);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Episodes - Youth TV Admin</title>
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
        
        .upload-area {
            border: 2px dashed #cbd5e1;
            border-radius: 0.75rem;
            transition: all 0.3s ease;
        }
        
        .upload-area:hover {
            border-color: #4f46e5;
            background-color: #f8fafc;
        }
        
        .episode-item {
            transition: all 0.3s ease;
        }
        
        .episode-item:hover {
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
                    <a href="manage_episodes.php" class="flex items-center p-3 nav-link active">
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
                        <a href="manage_series.php" class="flex items-center p-3 nav-link">
                            <i class="fas fa-stream mr-3"></i> Podcast Series
                        </a>
                        <a href="manage_episodes.php" class="flex items-center p-3 nav-link active">
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
                    <h1 class="text-3xl font-bold text-gray-800">Manage Episodes</h1>
                    <p class="text-gray-600">Create, edit, and manage podcast episodes</p>
                </div>
                
                <!-- Success Message -->
                <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span>Episode created successfully!</span>
                </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 gap-8">
                    <!-- Series Selection -->
                    <div class="card bg-white p-6">
                        <div class="flex flex-col md:flex-row md:items-center">
                            <div class="mb-4 md:mb-0 md:mr-4">
                                <label class="block mb-2 font-medium text-gray-700">Select Podcast Series:</label>
                            </div>
                            <div class="flex flex-1">
                                <select id="seriesSelector" class="flex-1 p-3 border rounded-l-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <?php foreach ($all_series as $series): ?>
                                        <option value="<?= $series['id'] ?>" <?= $current_series && $series['id'] == $current_series['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($series['title']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button onclick="changeSeries()" class="bg-primary hover:bg-secondary text-white px-4 py-3 rounded-r-lg font-medium">
                                    Go
                                </button>
                            </div>
                        </div>
                        
                        <?php if ($current_series): ?>
                        <div class="flex items-center mt-6 p-4 bg-gray-50 rounded-xl">
                            <img src="<?= $image_web_path.$current_series['cover_image'] ?>" 
                                class="w-16 h-16 object-cover rounded-lg mr-4">
                            <div>
                                <h4 class="font-bold text-lg"><?= htmlspecialchars($current_series['title']) ?></h4>
                                <p class="text-gray-600"><?= ucfirst($current_series['category']) ?> series</p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($current_series): ?>
                    <!-- Episode Creation Form -->
                    <div class="card bg-white">
                        <div class="p-6">
                            <div class="flex items-center mb-6">
                                <div class="bg-primary p-3 rounded-full mr-3">
                                    <i class="fas fa-plus-circle text-white text-xl"></i>
                                </div>
                                <h3 class="text-xl font-bold">Add New Episode</h3>
                            </div>
                            
                            <form method="POST" enctype="multipart/form-data">
                                <div class="grid grid-cols-1 gap-6">
                                    <div>
                                        <label class="block mb-2 font-medium">Episode Title</label>
                                        <input type="text" name="title" required 
                                            class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                    
                                    <div>
                                        <label class="block mb-2 font-medium">Description</label>
                                        <textarea name="description" required rows="3"
                                            class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block mb-2 font-medium">Audio File</label>
                                            <div class="upload-area cursor-pointer">
                                                <div class="flex flex-col items-center justify-center py-6 px-4">
                                                    <i class="fas fa-file-audio text-gray-400 text-3xl mb-2"></i>
                                                    <p class="text-center text-gray-500 mb-1">Click to upload audio file</p>
                                                    <p class="text-xs text-gray-400">MP3, WAV, AAC formats</p>
                                                </div>
                                                <input type="file" name="audio_file" accept="audio/*" required class="hidden" />
                                            </div>
                                        </div>
                                        
                                        <div>
                                            <!-- CHANGED: Duration input to seconds -->
                                            <label class="block mb-2 font-medium">Duration (seconds)</label>
                                            <input type="number" name="duration" required min="1"
                                                class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                                placeholder="Enter seconds">
                                            <p class="text-sm text-gray-500 mt-1">Enter duration in seconds</p>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" 
                                        class="w-full bg-primary hover:bg-secondary text-white font-bold py-3 px-4 rounded-lg transition mt-2">
                                        <i class="fas fa-plus-circle mr-2"></i> Add Episode
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Episodes List -->
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold">Episodes in This Series</h3>
                            <span class="bg-gray-200 text-gray-700 px-3 py-1 rounded-full font-medium">
                                <?= count($episodes) ?> episodes
                            </span>
                        </div>
                        
                        <div class="space-y-4">
                            <?php if (count($episodes) > 0): ?>
                                <?php foreach ($episodes as $ep): ?>
                                <div class="card bg-white episode-item">
                                    <div class="p-5">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h4 class="font-bold text-lg mb-1"><?= htmlspecialchars($ep['title']) ?></h4>
                                                <div class="flex items-center text-gray-600 mb-3">
                                                    <span class="mr-4">
                                                        <i class="far fa-clock mr-1"></i> 
                                                        <!-- CHANGED: Format duration as MM:SS -->
                                                        <?= format_duration($ep['duration']) ?>
                                                    </span>
                                                    <span>
                                                        <i class="fas fa-headphones mr-1"></i> 
                                                        <?= $ep['play_count'] ?> plays
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="flex space-x-2">
                                                <a href="edit_episode.php?id=<?= $ep['id'] ?>" 
                                                    class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-sm action-btn"
                                                    title="Edit Episode">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <button class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm action-btn delete-btn"
                                                    data-id="<?= $ep['id'] ?>" data-title="<?= htmlspecialchars($ep['title']) ?>"
                                                    title="Delete Episode">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <p class="text-gray-700 mb-4">
                                            <?= htmlspecialchars(substr($ep['description'], 0, 120)) ?>...
                                        </p>
                                        
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-500 text-sm">
                                                <i class="far fa-calendar mr-1"></i> 
                                                <?= date('M d, Y', strtotime($ep['created_at'])) ?>
                                            </span>
                                            
                                            <div class="flex items-center">
                                                <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded-full text-xs mr-2">
                                                    <i class="fas fa-play-circle mr-1"></i> Play
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="card bg-white p-8 text-center">
                                    <i class="fas fa-file-audio text-gray-300 text-5xl mb-4"></i>
                                    <h4 class="text-xl font-bold text-gray-600 mb-2">No Episodes Found</h4>
                                    <p class="text-gray-500">Add episodes to this series to get started</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php else: ?>
                        <div class="card bg-white p-8 text-center">
                            <i class="fas fa-stream text-gray-300 text-5xl mb-4"></i>
                            <h4 class="text-xl font-bold text-gray-600 mb-2">Select a Podcast Series</h4>
                            <p class="text-gray-500">Choose a series from the dropdown to manage its episodes</p>
                        </div>
                    <?php endif; ?>
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
                    Are you sure you want to delete the episode "<span id="episodeTitle" class="font-semibold"></span>"? 
                    This action cannot be undone.
                </p>
                
                <div class="flex justify-end space-x-3">
                    <button id="cancelDelete" class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">
                        Cancel
                    </button>
                    <a id="confirmDelete" href="#" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium">
                        <i class="fas fa-trash mr-2"></i> Delete Episode
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
        const episodeTitleEl = document.getElementById('episodeTitle');
        const confirmDeleteBtn = document.getElementById('confirmDelete');
        const cancelDeleteBtn = document.getElementById('cancelDelete');
        
        let currentEpisodeId = null;
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', () => {
                currentEpisodeId = button.getAttribute('data-id');
                const episodeTitle = button.getAttribute('data-title');
                
                episodeTitleEl.textContent = episodeTitle;
                confirmDeleteBtn.href = `delete_episode.php?id=${currentEpisodeId}`;
                
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
        
        // Change series function
        function changeSeries() {
            const seriesId = document.getElementById('seriesSelector').value;
            window.location.href = `manage_episodes.php?series_id=${seriesId}`;
        }
        
        // File upload area styling
        const uploadArea = document.querySelector('.upload-area');
        const fileInput = document.querySelector('input[name="audio_file"]');
        
        uploadArea.addEventListener('click', () => {
            fileInput.click();
        });
        
        fileInput.addEventListener('change', () => {
            if (fileInput.files.length > 0) {
                uploadArea.classList.add('border-primary', 'bg-blue-50');
                uploadArea.querySelector('p').textContent = fileInput.files[0].name;
            }
        });
    </script>
</body>
</html>