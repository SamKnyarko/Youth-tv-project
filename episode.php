<?php
require 'config.php';

if (isset($_GET['id'])) {
    $episodeId = $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM podcasts WHERE id = ?");
        $stmt->execute([$episodeId]);
        $episode = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$episode) die("Episode not found");
        
        $relatedStmt = $pdo->prepare("SELECT * FROM podcasts WHERE podcast_id = ? AND id != ? ORDER BY created_at DESC LIMIT 6");
        $relatedStmt->execute([$episode['podcast_id'], $episodeId]);
        $relatedEpisodes = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        die("Error loading episode: " . $e->getMessage());
    }
} else {
    header("Location: podcast.php");
    exit();
}

$categories = [
    'education' => ['title' => 'Educational Podcasts', 'icon' => 'fa-graduation-cap'],
    'science' => ['title' => 'Science Podcasts', 'icon' => 'fa-atom'],
    'technology' => ['title' => 'Tech Podcasts', 'icon' => 'fa-code'],
    'business' => ['title' => 'Business Podcasts', 'icon' => 'fa-briefcase'],
    'entertainment' => ['title' => 'Entertainment Podcasts', 'icon' => 'fa-film'],
    'news' => ['title' => 'News Podcasts', 'icon' => 'fa-newspaper']
];

function formatDuration($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $seconds = $seconds % 60;
    return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($episode['title']) ?> - Youth TV</title>
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
                    <img src="logo.png" alt="Logo" class="h-8 mr-3">
                    <h1 class="text-2xl font-bold">Youth TV</h1>
                </div>
                <nav class="hidden md:flex gap-8">
                    <a href="podcast.php" class="hover:text-gray-200">All Podcasts</a>
                    <a href="podcast.php?category=<?= $episode['category'] ?>" class="hover:text-gray-200">
                        <?= $categories[$episode['category']]['title'] ?>
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
            <a href="podcast.php?category=<?= $episode['category'] ?>" class="hover:text-gray-300">
                <?= $categories[$episode['category']]['title'] ?>
            </a>
            <span class="mx-2">/</span>
            <span class="text-white"><?= htmlspecialchars($episode['title']) ?></span>
        </div>
        <div id="mobile-menu" class="mobile-menu md:hidden bg-indigo-800 rounded-lg">
            <div class="px-2 pt-2 pb-4 space-y-2">
                <a href="podcast.php" class="block px-3 py-2 rounded-md hover:bg-indigo-700">All Podcasts</a>
                <a href="podcast.php?category=<?= $episode['category'] ?>" class="block px-3 py-2 rounded-md hover:bg-indigo-700">
                    <?= $categories[$episode['category']]['title'] ?>
                </a>
            </div>
        </div>
    </header>

    <main class="py-12">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto mb-12">
                <div class="episode-card">
                    <div class="relative h-64 overflow-hidden">
                        <img src="<?= $image_web_path . $episode['cover_image'] ?>" class="w-full h-full object-cover">
                        <div class="absolute top-2 left-2 category-badge <?= $episode['category'] ?>-badge text-white">
                            <i class="fas <?= $categories[$episode['category']]['icon'] ?> mr-1"></i> <?= $episode['category'] ?>
                        </div>
                    </div>
                    <div class="p-6">
                        <h1 class="text-3xl font-bold mb-2"><?= htmlspecialchars($episode['title']) ?></h1>
                        <p class="text-gray-600 mb-4"><?= date('M d, Y', strtotime($episode['created_at'])) ?></p>
                        <p class="text-gray-800 mb-6"><?= nl2br(htmlspecialchars($episode['description'])) ?></p>
                        <div class="education-gradient text-white p-4 rounded-lg">
                            <audio controls class="w-full">
                                <source src="<?= $audio_web_path . $episode['audio_file'] ?>" type="audio/mpeg">
                            </audio>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($relatedEpisodes)): ?>
            <section class="mb-16">
                <div class="podcast-gradient text-white p-4 rounded-lg mb-6">
                    <h2 class="text-2xl font-bold">More Episodes in This Podcast</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($relatedEpisodes as $related): ?>
                        <a href="episode.php?id=<?= $related['id'] ?>" class="episode-card">
                            <div class="relative h-48 overflow-hidden">
                                <img src="<?= $image_web_path . $related['cover_image'] ?>" class="w-full h-full object-cover">
                                <div class="absolute top-2 left-2 category-badge <?= $related['category'] ?>-badge text-white text-xs px-2 py-1 rounded">
                                    <i class="fas <?= $categories[$related['category']]['icon'] ?> mr-1"></i> <?= $related['category'] ?>
                                </div>
                            </div>
                            <div class="p-4">
                                <h3 class="font-bold text-lg mb-2"><?= htmlspecialchars($related['title']) ?></h3>
                                <div class="flex justify-between text-sm text-gray-500">
                                    <span><?= date('M d, Y', strtotime($related['created_at'])) ?></span>
                                    <span><?= formatDuration($related['duration']) ?></span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
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