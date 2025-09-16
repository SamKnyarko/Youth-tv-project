<?php
require 'config.php';

if (!isset($_GET['id'])) {
    header("Location: podcast.php");
    exit;
}

$series_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM podcast_series WHERE id = ?");
$stmt->execute([$series_id]);
$series = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$series) {
    header("Location: podcast.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM episodes WHERE series_id = ? ORDER BY created_at DESC");
$stmt->execute([$series_id]);
$episodes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = [
    'education' => ['title' => 'Educational Podcasts', 'icon' => 'fa-graduation-cap'],
    'science' => ['title' => 'Science Podcasts', 'icon' => 'fa-atom'],
    'technology' => ['title' => 'Tech Podcasts', 'icon' => 'fa-code'],
    'business' => ['title' => 'Business Podcasts', 'icon' => 'fa-briefcase'],
    'entertainment' => ['title' => 'Entertainment Podcasts', 'icon' => 'fa-film'],
    'news' => ['title' => 'News Podcasts', 'icon' => 'fa-newspaper']
];

function formatDuration($seconds) {
    $minutes = floor($seconds / 60);
    $seconds = $seconds % 60;
    return sprintf("%02d:%02d", $minutes, $seconds);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($series['title']) ?> - Youth TV</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .gradient-bg {background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);}
        .episode-card {background: white;border-radius: 0.75rem;overflow: hidden;box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);}
        .play-icon {background: rgba(255, 255, 255, 0.9);border-radius: 9999px;width: 3rem;height: 3rem;display: flex;align-items: center;justify-content: center;}
        .progress-bar {height: 6px;background: rgba(255, 255, 255, 0.2);border-radius: 3px;overflow: hidden;}
        .progress-filled {height: 100%;background: white;border-radius: 3px;width: 0%;}
        .education-gradient {background: linear-gradient(135deg, #4f46e5 0%, #10b981 100%);}
        .category-badge {padding: 0.25rem 0.5rem;border-radius: 9999px;font-size: 0.75rem;line-height: 1rem;}
        .podcast-gradient {background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);}
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <header class="gradient-bg text-white shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
            <div class="flex items-center">
                        <a href="index.html" class="logo-container mr-3" style="height: 75px;" >
                            <img src="logo.png" 
                                 alt="Youth TV Logo" 
                                 class="h-full hover:scale-105 transition-transform">
                        </a>
                    </div>
                <nav class="hidden md:flex gap-8">
                    <a href="podcast.php" class="hover:text-gray-200">All Podcasts</a>
                    <a href="podcast.php?category=<?= $series['category'] ?>" class="hover:text-gray-200">
                        <?= $categories[$series['category']]['title'] ?>
                    </a>
                </nav>
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-button" class="text-white focus:outline-none">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="container mx-auto px-4 py-2 text-sm">
            <a href="podcast.php" class="hover:text-gray-300">Podcasts</a>
            <span class="mx-2">/</span>
            <a href="podcast.php?category=<?= $series['category'] ?>" class="hover:text-gray-300">
                <?= $categories[$series['category']]['title'] ?>
            </a>
            <span class="mx-2">/</span>
            <span class="text-white"><?= htmlspecialchars($series['title']) ?></span>
        </div>
        <div id="mobile-menu" class="mobile-menu md:hidden bg-indigo-800 rounded-lg">
            <div class="px-2 pt-2 pb-4 space-y-2">
                <a href="podcast.php" class="block px-3 py-2 rounded-md hover:bg-indigo-700">All Podcasts</a>
                <a href="podcast.php?category=<?= $series['category'] ?>" class="block px-3 py-2 rounded-md hover:bg-indigo-700">
                    <?= $categories[$series['category']]['title'] ?>
                </a>
            </div>
        </div>
    </header>

    <main class="py-12">
        <div class="container mx-auto px-4 mb-12">
            <div class="flex flex-col md:flex-row items-center">
                <img src="<?= $image_web_path . $series['cover_image'] ?>" 
                     class="w-48 h-48 rounded-xl object-cover shadow-lg mb-6 md:mb-0 md:mr-8">
                <div>
                    <h1 class="text-4xl font-bold mb-4"><?= htmlspecialchars($series['title']) ?></h1>
                    <div class="flex items-center mb-4">
                        <div class="<?= $series['category'] ?>-badge text-white px-3 py-1 rounded-full mr-4">
                            <?= ucfirst($series['category']) ?>
                        </div>
                        <span class="text-gray-600">
                            <?= count($episodes) ?> episodes
                        </span>
                    </div>
                    <p class="text-gray-700 max-w-3xl"><?= nl2br(htmlspecialchars($series['description'])) ?></p>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4">
            <div class="podcast-gradient text-white p-4 rounded-lg mb-8">
                <h2 class="text-2xl font-bold">Episodes</h2>
            </div>
            
            <?php if (!empty($episodes)): ?>
            <div class="space-y-6">
                <?php foreach ($episodes as $episode): ?>
                <div class="episode-card bg-white rounded-xl p-6 flex flex-col md:flex-row">
                    <div class="mb-4 md:mb-0 md:mr-6">
                        <img src="<?= $image_web_path . $series['cover_image'] ?>" 
                             class="w-32 h-32 rounded-lg object-cover">
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xl font-bold mb-2"><?= htmlspecialchars($episode['title']) ?></h3>
                        <div class="flex text-gray-600 mb-4">
                            <span class="mr-4"><?= date('M d, Y', strtotime($episode['created_at'])) ?></span>
                            <span><?= floor($episode['duration']/60) ?> min</span>
                        </div>
                        <p class="text-gray-700 mb-4"><?= htmlspecialchars(substr($episode['description'], 0, 200)) ?>...</p>
                        <audio controls class="w-full mt-4">
                            <source src="<?= $audio_web_path . $episode['audio_file'] ?>" type="audio/mpeg">
                        </audio>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-12">
                <i class="fas fa-podcast text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-600">No episodes available for this series yet</p>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-4 text-center">
            <p>© 2023 Youth TV. All rights reserved.</p>
        </div>
    </footer>

    <script>
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('open');
        });
    </script>
</body>
</html>