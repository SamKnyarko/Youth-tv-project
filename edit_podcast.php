<?php
require 'config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

$podcast = [];
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM podcasts WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $podcast = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $duration = $_POST['duration'];
    
    // Handle file updates
    $audio_file = $podcast['audio_file'];
    if (!empty($_FILES['audio_file']['name'])) {
        unlink($upload_dir.'audio/'.$audio_file);
        $audio_file = basename($_FILES['audio_file']['name']);
        move_uploaded_file($_FILES['audio_file']['tmp_name'], $upload_dir.'audio/'.$audio_file);
    }
    
    $cover_image = $podcast['cover_image'];
    if (!empty($_FILES['cover_image']['name'])) {
        unlink($upload_dir.'images/'.$cover_image);
        $cover_image = basename($_FILES['cover_image']['name']);
        move_uploaded_file($_FILES['cover_image']['tmp_name'], $upload_dir.'images/'.$cover_image);
    }
    
    $stmt = $pdo->prepare("UPDATE podcasts SET 
        title = ?, 
        description = ?, 
        category = ?, 
        duration = ?, 
        audio_file = ?, 
        cover_image = ? 
        WHERE id = ?");
        
    $stmt->execute([
        $title, 
        $description, 
        $category, 
        $duration, 
        $audio_file, 
        $cover_image, 
        $_GET['id']
    ]);
    
    header('Location: admin_dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Podcast</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <?php include 'admin_header.php'; ?>
    
    <div class="dashboard-main flex-1 p-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-xl font-bold mb-4"><i class="fas fa-edit mr-2"></i> Edit Podcast</h3>
            
            <form method="POST" enctype="multipart/form-data">
                <!-- Same form fields as upload form with existing values -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block mb-2">Title</label>
                        <input type="text" name="title" value="<?= $podcast['title'] ?? '' ?>" required class="w-full p-2 border rounded">
                    </div>
                    <!-- Add other fields with existing values -->
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
                    Update Podcast
                </button>
            </form>
        </div>
    </div>
</body>
</html>