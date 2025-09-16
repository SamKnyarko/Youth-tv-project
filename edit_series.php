<?php
require 'config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

$series_id = $_GET['id'] ?? null;
$series = null;

if ($series_id) {
    $stmt = $pdo->prepare("SELECT * FROM podcast_series WHERE id = ?");
    $stmt->execute([$series_id]);
    $series = $stmt->fetch();
    
    if (!$series) {
        header('Location: manage_series.php');
        exit;
    }
} else {
    header('Location: manage_series.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    
    // Handle file upload if new image was provided
    if (!empty($_FILES['cover_image']['name'])) {
        // Delete old image
        if (file_exists($upload_dir.'images/'.$series['cover_image'])) {
            unlink($upload_dir.'images/'.$series['cover_image']);
        }
        
        $cover_image = basename($_FILES['cover_image']['name']);
        move_uploaded_file($_FILES['cover_image']['tmp_name'], $upload_dir.'images/'.$cover_image);
    } else {
        $cover_image = $series['cover_image'];
    }
    
    // Update database
    $stmt = $pdo->prepare("UPDATE podcast_series SET title = ?, description = ?, category = ?, cover_image = ? WHERE id = ?");
    $stmt->execute([$title, $description, $category, $cover_image, $series_id]);
    
    header('Location: manage_series.php?success=2');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Series - Youth TV Admin</title>
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
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
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
        
        .preview-image {
            transition: all 0.3s ease;
        }
        
        .preview-image:hover {
            transform: scale(1.02);
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
                    <h1 class="text-3xl font-bold text-gray-800">Edit Podcast Series</h1>
                    <p class="text-gray-600">Update the details of your podcast series</p>
                </div>
                
                <!-- Success Message -->
                <?php if (isset($_GET['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span>Series updated successfully!</span>
                </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 gap-8">
                    <!-- Edit Series Form -->
                    <div class="card bg-white">
                        <div class="p-6">
                            <div class="flex items-center mb-6">
                                <div class="bg-primary p-3 rounded-full mr-3">
                                    <i class="fas fa-edit text-white text-xl"></i>
                                </div>
                                <h3 class="text-xl font-bold">Edit "<?= htmlspecialchars($series['title']) ?>"</h3>
                            </div>
                            
                            <form method="POST" enctype="multipart/form-data">
                                <div class="grid grid-cols-1 gap-6">
                                    <div>
                                        <label class="block mb-2 font-medium">Series Title</label>
                                        <input type="text" name="title" required 
                                            value="<?= htmlspecialchars($series['title']) ?>"
                                            class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                    
                                    <div>
                                        <label class="block mb-2 font-medium">Category</label>
                                        <select name="category" required 
                                            class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <option value="education" <?= $series['category'] == 'education' ? 'selected' : '' ?>>Education</option>
                                            <option value="science" <?= $series['category'] == 'science' ? 'selected' : '' ?>>Science</option>
                                            <option value="technology" <?= $series['category'] == 'technology' ? 'selected' : '' ?>>Technology</option>
                                            <option value="business" <?= $series['category'] == 'business' ? 'selected' : '' ?>>Business</option>
                                            <option value="entertainment" <?= $series['category'] == 'entertainment' ? 'selected' : '' ?>>Entertainment</option>
                                            <option value="news" <?= $series['category'] == 'news' ? 'selected' : '' ?>>News</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block mb-2 font-medium">Description</label>
                                        <textarea name="description" required rows="4"
                                            class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"><?= htmlspecialchars($series['description']) ?></textarea>
                                    </div>
                                    
                                    <div>
                                        <label class="block mb-2 font-medium">Cover Image</label>
                                        
                                        <!-- Current Image Preview -->
                                        <div class="mb-4">
                                            <p class="text-sm text-gray-500 mb-2">Current Cover Image:</p>
                                            <img src="<?= $image_web_path.$series['cover_image'] ?>" 
                                                class="w-32 h-32 object-cover rounded-lg preview-image border">
                                        </div>
                                        
                                        <!-- New Image Upload -->
                                        <div class="upload-area cursor-pointer">
                                            <div class="flex flex-col items-center justify-center py-6 px-4">
                                                <i class="fas fa-cloud-upload-alt text-gray-400 text-3xl mb-2"></i>
                                                <p class="text-center text-gray-500 mb-1">Click to upload new cover image</p>
                                                <p class="text-xs text-gray-400">Leave empty to keep current image</p>
                                            </div>
                                            <input type="file" name="cover_image" accept="image/*" class="hidden" />
                                        </div>
                                    </div>
                                    
                                    <div class="flex justify-end space-x-3 pt-4">
                                        <a href="manage_series.php" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50">
                                            Cancel
                                        </a>
                                        <button type="submit" 
                                            class="px-4 py-2 bg-primary hover:bg-secondary text-white rounded-lg font-medium">
                                            <i class="fas fa-save mr-2"></i> Save Changes
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Series Stats -->
                    <div class="card bg-white">
                        <div class="p-6">
                            <div class="flex items-center mb-6">
                                <div class="bg-primary p-3 rounded-full mr-3">
                                    <i class="fas fa-chart-bar text-white text-xl"></i>
                                </div>
                                <h3 class="text-xl font-bold">Series Statistics</h3>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <?php
                                $episode_count = $pdo->query("SELECT COUNT(*) FROM episodes WHERE series_id = {$series['id']}")->fetchColumn();
                                $total_plays = $pdo->query("SELECT SUM(play_count) FROM episodes WHERE series_id = {$series['id']}")->fetchColumn();
                                $total_plays = $total_plays ? $total_plays : 0;
                                ?>
                                
                                <div class="bg-indigo-50 p-4 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="bg-indigo-100 p-3 rounded-full mr-3">
                                            <i class="fas fa-file-audio text-indigo-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Total Episodes</p>
                                            <p class="text-2xl font-bold"><?= $episode_count ?></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="bg-blue-100 p-3 rounded-full mr-3">
                                            <i class="fas fa-headphones text-blue-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Total Plays</p>
                                            <p class="text-2xl font-bold"><?= $total_plays ?></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-purple-50 p-4 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="bg-purple-100 p-3 rounded-full mr-3">
                                            <i class="fas fa-calendar text-purple-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Created On</p>
                                            <p class="text-lg font-medium"><?= date('M d, Y', strtotime($series['created_at'])) ?></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="bg-green-100 p-3 rounded-full mr-3">
                                            <i class="fas fa-tag text-green-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Category</p>
                                            <p class="text-lg font-medium"><?= ucfirst($series['category']) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-6">
                                <a href="manage_episodes.php?series_id=<?= $series['id'] ?>" 
                                    class="w-full bg-primary hover:bg-secondary text-white font-bold py-3 px-4 rounded-lg transition flex items-center justify-center">
                                    <i class="fas fa-file-audio mr-2"></i> Manage Episodes
                                </a>
                            </div>
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
        
        // File upload area styling
        const uploadArea = document.querySelector('.upload-area');
        const fileInput = document.querySelector('input[name="cover_image"]');
        
        uploadArea.addEventListener('click', () => {
            fileInput.click();
        });
        
        fileInput.addEventListener('change', () => {
            if (fileInput.files.length > 0) {
                uploadArea.classList.add('border-primary', 'bg-blue-50');
                uploadArea.querySelector('p').textContent = fileInput.files[0].name;
                
                // Preview new image
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.querySelector('.preview-image').src = e.target.result;
                }
                reader.readAsDataURL(fileInput.files[0]);
            }
        });
    </script>
</body>
</html>