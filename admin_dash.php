<?php
require 'config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_podcast'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $category = $_POST['category'];
        $author = $_POST['author'];
        $coverImage = $_FILES['cover_image'];
        $coverImageName = uniqid() . '_' . $coverImage['name'];
        move_uploaded_file($coverImage['tmp_name'], $image_upload_path . $coverImageName);
        $stmt = $pdo->prepare("INSERT INTO podcasts (title, description, category, author, cover_image) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $category, $author, $coverImageName]);
        header('Location: admin_dashboard.php?success=podcast');
        exit;
    }

    if (isset($_POST['create_episode'])) {
        $podcast_id = $_POST['podcast_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $duration = $_POST['duration'];
        $audioFile = $_FILES['audio_file'];
        $audioFileName = uniqid() . '_' . $audioFile['name'];
        move_uploaded_file($audioFile['tmp_name'], $audio_upload_path . $audioFileName);
        $stmt = $pdo->prepare("INSERT INTO episodes (podcast_id, title, description, audio_file, duration) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$podcast_id, $title, $description, $audioFileName, $duration]);
        header('Location: admin_dashboard.php?success=episode');
        exit;
    }
}

$podcasts = $pdo->query("SELECT * FROM podcasts ORDER BY created_at DESC")->fetchAll();
$episodes = $pdo->query("SELECT e.*, p.title AS podcast_title, p.cover_image FROM episodes e JOIN podcasts p ON e.podcast_id = p.id ORDER BY e.created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard-sidebar { width: 260px; transition: all 0.3s; }
        .dashboard-main { margin-left: 260px; transition: all 0.3s; }
        @media (max-width: 768px) {
            .dashboard-sidebar { margin-left: -260px; }
            .dashboard-main { margin-left: 0; }
            .sidebar-active .dashboard-sidebar { margin-left: 0; }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <aside class="dashboard-sidebar bg-gray-800 text-white fixed h-full overflow-y-auto">
            <div class="p-4">
                <h2 class="text-2xl font-bold mb-6 flex items-center"><i class="fas fa-podcast mr-2"></i> Youth TV Admin</h2>
                <nav class="space-y-2">
                    <a href="admin_dashboard.php" class="flex items-center p-3 bg-gray-700 rounded"><i class="fas fa-home mr-3"></i> Dashboard</a>
                    <a href="manage_podcasts.php" class="flex items-center p-3 hover:bg-gray-700 rounded"><i class="fas fa-podcast mr-3"></i> Podcasts</a>
                    <a href="analytics.php" class="flex items-center p-3 hover:bg-gray-700 rounded"><i class="fas fa-chart-line mr-3"></i> Analytics</a>
                    <a href="manage_users.php" class="flex items-center p-3 hover:bg-gray-700 rounded">
                        <i class="fas fa-users mr-3"></i> Users
                    </a>
                    <a href="settings.php" class="flex items-center p-3 hover:bg-gray-700 rounded">
                        <i class="fas fa-cog mr-3"></i> Settings
                    </a>
                </nav>
            </div>
        </aside>

        <main class="dashboard-main flex-1">
            <header class="bg-white shadow-sm">
                <div class="flex items-center justify-between p-4">
                    <button id="sidebarToggle" class="md:hidden text-gray-600"><i class="fas fa-bars text-xl"></i></button>
                    <div class="flex items-center">
                        <div class="mr-4"><span class="text-gray-600">Welcome, Admin</span></div>
                        <a href="logout.php" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700"><i class="fas fa-sign-out-alt mr-2"></i> Logout</a>
                    </div>
                </div>
            </header>

            <div class="p-6">
                <?php if(isset($_GET['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <?= $_GET['success'] === 'podcast' ? 'Podcast series created!' : 'Episode added!' ?>
                </div>
                <?php endif; ?>

                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h3 class="text-xl font-bold mb-4">Create New Podcast Series</h3>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div><input type="text" name="title" placeholder="Series Title" class="w-full p-2 border rounded" required></div>
                            <div>
                                <select name="category" class="w-full p-2 border rounded" required>
                                    <option value="education">Education</option>
                                    <option value="science">Science</option>
                                    <option value="technology">Technology</option>
                                    <option value="business">Business</option>
                                    <option value="entertainment">Entertainment</option>
                                    <option value="news">News</option>
                                </select>
                            </div>
                            <div class="md:col-span-2"><textarea name="description" placeholder="Series Description" class="w-full p-2 border rounded" rows="3" required></textarea></div>
                            <div><input type="text" name="author" value="Youth TV" class="w-full p-2 border rounded" required></div>
                            <div><input type="file" name="cover_image" accept="image/*" class="w-full" required></div>
                        </div>
                        <button type="submit" name="create_podcast" class="mt-6 bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">Create Series</button>
                    </form>
                </div>

                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h3 class="text-xl font-bold mb-4">Add New Episode</h3>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <select name="podcast_id" class="w-full p-2 border rounded" required>
                                    <?php foreach ($podcasts as $podcast): ?>
                                    <option value="<?= $podcast['id'] ?>"><?= htmlspecialchars($podcast['title']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div><input type="text" name="title" placeholder="Episode Title" class="w-full p-2 border rounded" required></div>
                            <div class="md:col-span-2"><textarea name="description" placeholder="Episode Description" class="w-full p-2 border rounded" rows="3" required></textarea></div>
                            <div><input type="number" name="duration" placeholder="Duration (seconds)" class="w-full p-2 border rounded" required></div>
                            <div><input type="file" name="audio_file" accept="audio/*" class="w-full" required></div>
                        </div>
                        <button type="submit" name="create_episode" class="mt-6 bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">Add Episode</button>
                    </form>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-xl font-bold mb-4">Manage Episodes</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left">Cover</th>
                                    <th class="px-6 py-3 text-left">Series</th>
                                    <th class="px-6 py-3 text-left">Episode</th>
                                    <th class="px-6 py-3 text-left">Duration</th>
                                    <th class="px-6 py-3 text-left">Plays</th>
                                    <th class="px-6 py-3 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($episodes as $episode): ?>
                                <tr>
                                    <td class="px-6 py-4"><img src="<?= $image_web_path.$episode['cover_image'] ?>" class="w-16 h-16 object-cover rounded"></td>
                                    <td class="px-6 py-4"><?= htmlspecialchars($episode['podcast_title']) ?></td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium"><?= htmlspecialchars($episode['title']) ?></div>
                                        <div class="text-sm text-gray-500"><?= date('M d, Y', strtotime($episode['created_at'])) ?></div>
                                    </td>
                                    <td class="px-6 py-4"><?= floor($episode['duration']/60) ?> mins</td>
                                    <td class="px-6 py-4"><?= $episode['play_count'] ?></td>
                                    <td class="px-6 py-4">
                                        <div class="flex space-x-2">
                                            <a href="edit_episode.php?id=<?= $episode['id'] ?>" class="text-indigo-600 hover:text-indigo-900"><i class="fas fa-edit"></i></a>
                                            <a href="delete_episode.php?id=<?= $episode['id'] ?>" class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
    document.getElementById('sidebarToggle').addEventListener('click', () => {
        document.documentElement.classList.toggle('sidebar-active');
    });
    document.addEventListener('click', (e) => {
        if (window.innerWidth < 768 && !document.getElementById('sidebarToggle').contains(e.target) && !document.querySelector('.dashboard-sidebar').contains(e.target)) {
            document.documentElement.classList.remove('sidebar-active');
        }
    });
    </script>
</body>
</html>