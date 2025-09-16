<?php
require 'config.php';

// Authentication check
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

// Handle file uploads
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $duration = $_POST['duration'];
    
    // File upload handling
    $audio_file = basename($_FILES['audio_file']['name']);
    $cover_image = basename($_FILES['cover_image']['name']);
    
    move_uploaded_file($_FILES['audio_file']['tmp_name'], $upload_dir.'audio/'.$audio_file);
    move_uploaded_file($_FILES['cover_image']['tmp_name'], $upload_dir.'images/'.$cover_image);
    
    // Insert into database
    $stmt = $pdo->prepare("INSERT INTO podcasts (title, description, category, duration, audio_file, cover_image) 
                          VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $description, $category, $duration, $audio_file, $cover_image]);
    
    header('Location: admin.php?success=1');
    exit;
}

// Get existing podcasts
$podcasts = $pdo->query("SELECT * FROM podcasts ORDER BY created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">Podcast Management</h1>
        
        <!-- Upload Form -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <form method="POST" enctype="multipart/form-data">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block mb-2">Title</label>
                        <input type="text" name="title" required class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label class="block mb-2">Category</label>
                        <select name="category" required class="w-full p-2 border rounded">
                            <option value="education">Education</option>
                            <option value="science">Science</option>
                            <option value="technology">Technology</option>
                            <option value="business">Business</option>
                            <option value="entertainment">Entertainment</option>
                            <option value="news">News</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2">Description</label>
                        <textarea name="description" required class="w-full p-2 border rounded" rows="3"></textarea>
                    </div>
                    <div>
                        <label class="block mb-2">Duration (seconds)</label>
                        <input type="number" name="duration" required class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label class="block mb-2">Audio File</label>
                        <input type="file" name="audio_file" accept="audio/*" required class="w-full">
                    </div>
                    <div>
                        <label class="block mb-2">Cover Image</label>
                        <input type="file" name="cover_image" accept="image/*" required class="w-full">
                    </div>
                </div>
                <button type="submit" class="mt-6 bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">
                    Upload Podcast
                </button>
            </form>
        </div>

        <!-- Existing Podcasts -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-4">Existing Podcasts</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($podcasts as $podcast): ?>
                <div class="border rounded-lg p-4">
                    <img src="<?= $image_web_path.$podcast['cover_image'] ?>" class="w-full h-48 object-cover mb-4">
                    <h3 class="font-bold mb-2"><?= htmlspecialchars($podcast['title']) ?></h3>
                    <p class="text-sm text-gray-600 mb-2"><?= htmlspecialchars($podcast['description']) ?></p>
                    <div class="text-sm text-gray-500">
                        <span><?= floor($podcast['duration']/60) ?> mins</span> | 
                        <span><?= $podcast['category'] ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>