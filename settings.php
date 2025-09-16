<?php
require 'config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site_title = $_POST['site_title'];
    $site_description = $_POST['site_description'];
    
    // Update settings in database
    $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE name = ?");
    $stmt->execute([$site_title, 'site_title']);
    $stmt->execute([$site_description, 'site_description']);
}

// Get current settings
$settings = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <?php include 'admin_header.php'; ?>
    
    <div class="dashboard-main flex-1 p-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-xl font-bold mb-4"><i class="fas fa-cog mr-2"></i> Settings</h3>
            
            <form method="POST">
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block mb-2">Site Title</label>
                        <input type="text" name="site_title" value="<?= $settings['site_title'] ?? '' ?>" class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label class="block mb-2">Site Description</label>
                        <textarea name="site_description" class="w-full p-2 border rounded" rows="3"><?= $settings['site_description'] ?? '' ?></textarea>
                    </div>
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>