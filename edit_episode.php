<?php
require 'config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

$episode_id = $_GET['id'] ?? null;
$current_episode = null;
$series_info = null;

if ($episode_id) {
    // Fetch episode details
    $stmt = $pdo->prepare("SELECT * FROM episodes WHERE id = ?");
    $stmt->execute([$episode_id]);
    $current_episode = $stmt->fetch();
    
    if ($current_episode) {
        // Fetch series information
        $stmt = $pdo->prepare("SELECT * FROM podcast_series WHERE id = ?");
        $stmt->execute([$current_episode['series_id']]);
        $series_info = $stmt->fetch();
    }
}

if (!$current_episode || !$series_info) {
    header('Location: manage_episodes.php');
    exit;
}

// Handle episode update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $duration = $_POST['duration'];
    
    // Check if a new audio file was uploaded
    if (!empty($_FILES['audio_file']['name'])) {
        // Delete old audio file
        $old_file = $upload_dir.'audio/'.$current_episode['audio_file'];
        if (file_exists($old_file)) {
            unlink($old_file);
        }
        
        // Upload new file
        $audio_file = basename($_FILES['audio_file']['name']);
        move_uploaded_file($_FILES['audio_file']['tmp_name'], $upload_dir.'audio/'.$audio_file);
    } else {
        $audio_file = $current_episode['audio_file'];
    }
    
    // Update database
    $stmt = $pdo->prepare("UPDATE episodes SET title = ?, description = ?, audio_file = ?, duration = ? WHERE id = ?");
    $stmt->execute([$title, $description, $audio_file, $duration, $episode_id]);
    
    header("Location: manage_episodes.php?series_id={$series_info['id']}&success=1");
    exit;
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
    <title>Edit Episode - Youth TV Admin</title>
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
        
        .action-btn {
            transition: all 0.2s ease;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
        }
        
        .audio-preview {
            border: 2px dashed #cbd5e1;
            border-radius: 0.75rem;
            transition: all 0.3s ease;
        }
        
        .audio-preview:hover {
            border-color: #4f46e5;
            background-color: #f8fafc;
        }
        
        .duration-input {
            display: flex;
            align-items: center;
        }
        
        .duration-input input {
            width: 60px;
            text-align: center;
            margin: 0 5px;
        }
        
        .duration-input span {
            color: #6b7280;
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
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-800">Edit Podcast Episode</h1>
                            <p class="text-gray-600">Update details for "<?= htmlspecialchars($current_episode['title']) ?>"</p>
                        </div>
                        <a href="manage_episodes.php?series_id=<?= $series_info['id'] ?>" 
                            class="bg-indigo-100 text-indigo-700 px-4 py-2 rounded-lg font-medium">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Episodes
                        </a>
                    </div>
                </div>
                
                <!-- Success Message -->
                <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span>Episode updated successfully!</span>
                </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Edit Episode Form -->
                    <div class="card bg-white">
                        <div class="p-6">
                            <div class="flex items-center mb-6">
                                <div class="bg-primary p-3 rounded-full mr-3">
                                    <i class="fas fa-edit text-white text-xl"></i>
                                </div>
                                <h3 class="text-xl font-bold">Edit Episode Details</h3>
                            </div>
                            
                            <form method="POST" enctype="multipart/form-data">
                                <div class="space-y-6">
                                    <!-- Series Information -->
                                    <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                        <img src="<?= $image_web_path.$series_info['cover_image'] ?>" 
                                            class="w-16 h-16 object-cover rounded-lg mr-4">
                                        <div>
                                            <h4 class="font-bold"><?= htmlspecialchars($series_info['title']) ?></h4>
                                            <p class="text-gray-600 text-sm"><?= ucfirst($series_info['category']) ?> series</p>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block mb-2 font-medium">Episode Title</label>
                                        <input type="text" name="title" required 
                                            value="<?= htmlspecialchars($current_episode['title']) ?>"
                                            class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                    
                                    <div>
                                        <label class="block mb-2 font-medium">Description</label>
                                        <textarea name="description" required rows="5"
                                            class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"><?= htmlspecialchars($current_episode['description']) ?></textarea>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block mb-2 font-medium">Audio File</label>
                                            <p class="text-sm text-gray-500 mb-3">Current file: <?= $current_episode['audio_file'] ?></p>
                                            
                                            <div class="audio-preview cursor-pointer">
                                                <div class="flex flex-col items-center justify-center py-6 px-4">
                                                    <i class="fas fa-file-audio text-gray-400 text-3xl mb-2"></i>
                                                    <p class="text-center text-gray-500 mb-1">Click to upload new audio file</p>
                                                    <p class="text-xs text-gray-400">MP3, WAV, AAC formats</p>
                                                </div>
                                                <input type="file" name="audio_file" accept="audio/*" class="hidden" id="audioFileInput" />
                                            </div>
                                            <p class="text-xs text-gray-500 mt-2">Leave blank to keep current file</p>
                                        </div>
                                        
                                        <div>
                                            <label class="block mb-2 font-medium">Duration</label>
                                            <div class="duration-input">
                                                <input type="number" name="minutes" min="0" max="59" 
                                                    value="<?= floor($current_episode['duration'] / 60) ?>"
                                                    class="p-3 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                                <span>:</span>
                                                <input type="number" name="seconds" min="0" max="59" 
                                                    value="<?= $current_episode['duration'] % 60 ?>"
                                                    class="p-3 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                                <input type="hidden" name="duration" id="durationInput" value="<?= $current_episode['duration'] ?>">
                                            </div>
                                            <p class="text-sm text-gray-500 mt-2">Total: <span id="durationTotal"><?= format_duration($current_episode['duration']) ?></span> (MM:SS)</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex space-x-4">
                                        <button type="submit" 
                                            class="flex-1 bg-primary hover:bg-secondary text-white font-bold py-3 px-4 rounded-lg transition">
                                            <i class="fas fa-save mr-2"></i> Save Changes
                                        </button>
                                        <a href="manage_episodes.php?series_id=<?= $series_info['id'] ?>" 
                                            class="flex-1 bg-gray-200 text-gray-700 px-4 py-3 rounded-lg text-center font-medium hover:bg-gray-300 transition">
                                            <i class="fas fa-times mr-2"></i> Cancel
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Episode Stats -->
                    <div>
                        <div class="card bg-white mb-6">
                            <div class="p-6">
                                <div class="flex items-center mb-4">
                                    <div class="bg-indigo-100 p-3 rounded-full mr-3">
                                        <i class="fas fa-chart-bar text-indigo-600 text-xl"></i>
                                    </div>
                                    <h3 class="text-xl font-bold">Episode Statistics</h3>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-blue-50 p-4 rounded-lg">
                                        <div class="text-3xl font-bold text-blue-700"><?= $current_episode['play_count'] ?></div>
                                        <div class="text-gray-600">Total Plays</div>
                                    </div>
                                    
                                    <div class="bg-purple-50 p-4 rounded-lg">
                                        <div class="text-3xl font-bold text-purple-700"><?= format_duration($current_episode['duration']) ?></div>
                                        <div class="text-gray-600">Duration</div>
                                    </div>
                                    
                                    <div class="bg-indigo-50 p-4 rounded-lg">
                                        <div class="text-3xl font-bold text-indigo-700">
                                            <?= date('M d, Y', strtotime($current_episode['created_at'])) ?>
                                        </div>
                                        <div class="text-gray-600">Created Date</div>
                                    </div>
                                    
                                    <div class="bg-teal-50 p-4 rounded-lg">
                                        <div class="text-3xl font-bold text-teal-700">
                                            <?= date('M d, Y', strtotime($current_episode['updated_at'] ?? $current_episode['created_at'])) ?>
                                        </div>
                                        <div class="text-gray-600">Last Updated</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card bg-white">
                            <div class="p-6">
                                <div class="flex items-center mb-4">
                                    <div class="bg-red-100 p-3 rounded-full mr-3">
                                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                                    </div>
                                    <h3 class="text-xl font-bold text-red-700">Danger Zone</h3>
                                </div>
                                
                                <p class="text-gray-700 mb-4">
                                    Deleting this episode will permanently remove it from the platform. 
                                    This action cannot be undone.
                                </p>
                                
                                <button id="deleteBtn" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition">
                                    <i class="fas fa-trash mr-2"></i> Delete Episode
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-xl max-w-md w-full overflow-hidden">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="bg-red-100 p-3 rounded-full mr-3">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold">Confirm Episode Deletion</h3>
                </div>
                
                <p class="text-gray-700 mb-6">
                    Are you sure you want to permanently delete the episode 
                    "<span class="font-semibold"><?= htmlspecialchars($current_episode['title']) ?></span>"?
                </p>
                
                <div class="flex justify-end space-x-3">
                    <button id="cancelDelete" class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">
                        Cancel
                    </button>
                    <a href="delete_episode.php?id=<?= $episode_id ?>&series_id=<?= $series_info['id'] ?>" 
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium">
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
        
        // Audio file upload interaction
        const audioPreview = document.querySelector('.audio-preview');
        const audioInput = document.getElementById('audioFileInput');
        
        audioPreview.addEventListener('click', () => {
            audioInput.click();
        });
        
        audioInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                audioPreview.classList.add('border-primary', 'bg-blue-50');
                audioPreview.querySelector('p').textContent = this.files[0].name;
            }
        });
        
        // Duration calculation
        const minutesInput = document.querySelector('input[name="minutes"]');
        const secondsInput = document.querySelector('input[name="seconds"]');
        const durationInput = document.getElementById('durationInput');
        const durationTotal = document.getElementById('durationTotal');
        
        function updateDuration() {
            const minutes = parseInt(minutesInput.value) || 0;
            const seconds = parseInt(secondsInput.value) || 0;
            const totalSeconds = (minutes * 60) + seconds;
            
            durationInput.value = totalSeconds;
            
            // Format for display
            const formattedMinutes = minutes.toString().padStart(2, '0');
            const formattedSeconds = seconds.toString().padStart(2, '0');
            durationTotal.textContent = `${formattedMinutes}:${formattedSeconds}`;
        }
        
        minutesInput.addEventListener('input', updateDuration);
        secondsInput.addEventListener('input', updateDuration);
        
        // Delete modal handling
        const deleteModal = document.getElementById('deleteModal');
        const deleteBtn = document.getElementById('deleteBtn');
        const cancelDeleteBtn = document.getElementById('cancelDelete');
        
        // Show modal when delete button is clicked
        deleteBtn.addEventListener('click', () => {
            deleteModal.classList.remove('hidden');
        });
        
        // Hide modal when cancel is clicked
        cancelDeleteBtn.addEventListener('click', () => {
            deleteModal.classList.add('hidden');
        });
        
        // Hide modal when clicking outside
        deleteModal.addEventListener('click', (e) => {
            if (e.target === deleteModal) {
                deleteModal.classList.add('hidden');
            }
        });
        
        // Hide modal with ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                deleteModal.classList.add('hidden');
            }
        });
    </script>
</body>
</html>