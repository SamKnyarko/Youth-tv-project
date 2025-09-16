<?php
require 'config.php';

$stmt = $pdo->query("
    SELECT ps.*, COUNT(e.id) AS episode_count 
    FROM podcast_series ps
    LEFT JOIN episodes e ON ps.id = e.series_id
    GROUP BY ps.id
    ORDER BY ps.created_at DESC
");
$all_series = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categorizedSeries = [];
foreach ($all_series as $series) {
    $categorizedSeries[$series['category']][] = $series;
}

$categories = [
    'education' => ['title' => 'Educational Podcasts', 'icon' => 'fa-graduation-cap'],
    'science' => ['title' => 'Science Podcasts', 'icon' => 'fa-atom'],
    'technology' => ['title' => 'Tech Podcasts', 'icon' => 'fa-code'],
    'business' => ['title' => 'Business Podcasts', 'icon' => 'fa-briefcase'],
    'entertainment' => ['title' => 'Entertainment Podcasts', 'icon' => 'fa-film'],
    'news' => ['title' => 'News Podcasts', 'icon' => 'fa-newspaper']
];

$latestEpisode = null;
$stmt = $pdo->query("
    SELECT e.*, ps.title AS series_title, ps.cover_image AS series_cover
    FROM episodes e
    JOIN podcast_series ps ON e.series_id = ps.id
    ORDER BY e.created_at DESC 
    LIMIT 1
");
$latestEpisode = $stmt->fetch(PDO::FETCH_ASSOC);

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
    <title>Youth TV - Podcast Series</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .podcast-player { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); }
        .episode-card { background: white; border-radius: 0.75rem; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); transition: all 0.3s ease; }
        .episode-card:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        .play-icon { background: rgba(255, 255, 255, 0.9); border-radius: 9999px; width: 3rem; height: 3rem; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; }
        .play-icon:hover { transform: scale(1.1); background: white; }
        .progress-bar { height: 6px; background: rgba(255, 255, 255, 0.2); border-radius: 3px; overflow: hidden; }
        .progress-filled { height: 100%; background: white; border-radius: 3px; width: 0%; }
        .category-btn { transition: all 0.3s ease; }
        .category-btn:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .platform-btn { transition: all 0.3s ease; }
        .platform-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .host-card { transition: all 0.3s ease; }
        .host-card:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
        .mobile-menu { max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; }
        .mobile-menu.open { max-height: 500px; }
        .logo-container { height: 50px; display: flex; align-items: center; }
        .audio-wave { display: flex; align-items: center; justify-content: space-between; width: 50px; height: 30px; }
        .audio-wave span { width: 3px; height: 10px; background-color: white; border-radius: 3px; animation: audio-wave 1.5s infinite ease-in-out; }
        .audio-wave span:nth-child(2) { animation-delay: 0.2s; }
        .audio-wave span:nth-child(3) { animation-delay: 0.4s; }
        .audio-wave span:nth-child(4) { animation-delay: 0.6s; }
        .audio-wave span:nth-child(5) { animation-delay: 0.8s; }
        @keyframes audio-wave { 0% { height: 10px; } 50% { height: 20px; } 100% { height: 10px; } }
        .education-gradient { background: linear-gradient(135deg, #4f46e5 0%, #10b981 100%); }
        .science-gradient { background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%); }
        .tech-gradient { background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); }
        .business-gradient { background: linear-gradient(135deg, #06b6d4 0%, #0ea5e9 100%); }
        .entertainment-gradient { background: linear-gradient(135deg, #ec4899 0%, #f43f5e 100%); }
        .news-gradient { background: linear-gradient(135deg, #64748b 0%, #475569 100%); }
        .education-badge { background-color: #10b981; color: white; }
        .science-badge { background-color: #8b5cf6; color: white; }
        .tech-badge { background-color: #a855f7; color: white; }
        .business-badge { background-color: #0ea5e9; color: white; }
        .entertainment-badge { background-color: #f43f5e; color: white; }
        .news-badge { background-color: #475569; color: white; }
        .category-tab.active { border-bottom: 3px solid white; font-weight: 600; }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <header class="gradient-bg text-white shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
            <div class="flex items-center">
                        <a href="index.php" class="logo-container mr-3" style="height: 75px;" >
                            <img src="logo.png" 
                                 alt="Youth TV Logo" 
                                 class="h-full hover:scale-105 transition-transform">
                        </a>
                    </div>
                <nav class="hidden md:flex gap-8">
                    <a href="index.php" class="hover:text-gray-200 font-medium">Home</a>
                    <a href="news.html" class="hover:text-gray-200 font-medium">News</a>
                    <a href="videos.html" class="hover:text-gray-200 font-medium">Videos</a>
                    <a href="podcast.php" class="hover:text-gray-200 font-medium text-white font-semibold">Podcasts</a>
                    <a href="blog.html" class="hover:text-gray-200 font-medium">Blog</a>
                    <a href="events.php" class="hover:text-gray-200 font-medium">Events</a>
                </nav>
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-button" class="text-white focus:outline-none">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
                <div class="hidden md:block">
                    
                </div>
            </div>
            <div id="mobile-menu" class="mobile-menu md:hidden bg-indigo-800 mt-2 rounded-lg">
                <div class="px-2 pt-2 pb-4 space-y-2">
                    <a href="index.php" class="block px-3 py-2 rounded-md hover:bg-indigo-700">Home</a>
                    <a href="news.html" class="block px-3 py-2 rounded-md hover:bg-indigo-700">News</a>
                    <a href="videos.html" class="block px-3 py-2 rounded-md hover:bg-indigo-700">Videos</a>
                    <a href="podcast.php" class="block px-3 py-2 rounded-md hover:bg-indigo-700 bg-indigo-700">Podcasts</a>
                    <a href="blog.html" class="block px-3 py-2 rounded-md hover:bg-indigo-700">Blog</a>
                    <a href="events.php" class="block px-3 py-2 rounded-md hover:bg-indigo-700">Events</a>
                    <button class="w-full bg-white text-indigo-700 px-4 py-2 rounded-full font-semibold hover:bg-gray-100 transition mt-2">Subscribe</button>
                </div>
            </div>
        </div>
    </header>

    <main class="py-12">
        <?php if ($latestEpisode): ?>
        <div class="education-gradient text-white p-6 mb-12 rounded-xl shadow-xl mx-4">
            <div class="max-w-4xl mx-auto">
                <div class="flex items-center mb-4">
                    <img id="currentPodcastImage" src="<?= $image_web_path . $latestEpisode['series_cover'] ?>" class="w-24 h-24 rounded-lg mr-4 object-cover">
                    <div>
                        <h2 class="text-2xl font-bold" id="nowPlayingTitle"><?= htmlspecialchars($latestEpisode['title']) ?></h2>
                        <p class="text-gray-200" id="nowPlayingAuthor"><?= htmlspecialchars($latestEpisode['series_title']) ?></p>
                    </div>
                </div>
                <div class="progress-bar mb-2">
                    <div class="progress-filled" id="progressBar"></div>
                </div>
                <div class="flex justify-between text-sm text-gray-300 mb-4">
                    <span id="currentTime">0:00</span>
                    <span id="duration"><?= formatDuration($latestEpisode['duration']) ?></span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-6">
                        <button class="text-2xl hover:text-gray-300" id="skipBackward"><i class="fas fa-step-backward"></i></button>
                        <button class="text-3xl play-pause" id="playPauseBtn"><i class="fas fa-play"></i></button>
                        <button class="text-2xl hover:text-gray-300" id="skipForward"><i class="fas fa-step-forward"></i></button>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button class="hover:text-gray-300" id="muteBtn"><i class="fas fa-volume-up"></i></button>
                        <input type="range" min="0" max="1" step="0.01" value="1" class="w-24" id="volumeControl">
                        <div id="playingAnimation" class="audio-wave hidden">
                            <span></span><span></span><span></span><span></span><span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="container mx-auto px-4">
            <section class="mb-8">
                <div class="flex overflow-x-auto pb-2 space-x-4">
                    <?php foreach ($categories as $key => $category): ?>
                        <button class="category-tab px-4 py-2 text-lg font-medium <?= $key === 'education' ? 'active text-indigo-700' : 'text-gray-600' ?> whitespace-nowrap" data-category="<?= $key ?>">
                            <i class="fas <?= $category['icon'] ?> mr-2"></i> <?= $category['title'] ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </section>

            <?php foreach ($categories as $key => $category): ?>
                <section class="mb-16 category-section <?= $key !== 'education' ? 'hidden' : '' ?>" id="<?= $key ?>-series">
                    <div class="flex items-center justify-between mb-8">
                        <h2 class="text-3xl font-bold"><?= $category['title'] ?></h2>
                        <div class="<?= $key ?>-badge px-3 py-1 rounded-full text-sm font-semibold">
                            <i class="fas <?= $category['icon'] ?> mr-1"></i> <?= explode(' ', $category['title'])[0] ?>
                        </div>
                    </div>
                    <?php if (!empty($categorizedSeries[$key])): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            <?php foreach ($categorizedSeries[$key] as $series): ?>
                                <a href="series_detail.php?id=<?= $series['id'] ?>" class="episode-card">
                                    <div class="relative h-48 overflow-hidden">
                                        <img src="<?= $image_web_path . $series['cover_image'] ?>" class="w-full h-full object-cover">
                                        <div class="absolute top-2 left-2 <?= $key ?>-badge text-white text-xs px-2 py-1 rounded">
                                            <i class="fas <?= $category['icon'] ?> mr-1"></i> <?= $key ?>
                                        </div>
                                        <div class="absolute bottom-2 right-2 bg-black bg-opacity-60 text-white text-xs px-2 py-1 rounded">
                                            <?= $series['episode_count'] ?> episodes
                                        </div>
                                    </div>
                                    <div class="p-4">
                                        <h3 class="font-bold text-xl mb-2"><?= htmlspecialchars($series['title']) ?></h3>
                                        <p class="text-gray-600"><?= htmlspecialchars(substr($series['description'], 0, 100)) ?>...</p>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <i class="fas fa-podcast text-4xl text-gray-400 mb-4"></i>
                            <p class="text-gray-600">No <?= $key ?> podcast series available</p>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>
        </div>
    </main>

    <footer class="bg-gray-900 text-white py-12">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- About -->
            <div>
                <h3 class="text-xl font-bold mb-4">Youth TV</h3>
                <p class="text-gray-400 mb-4">Empowering the next generation through engaging content, education, and community.</p>
                <div class="flex space-x-4">
                    <a href="https://www.facebook.com/youthtvgh/?locale=gn_PY" class="text-gray-400 hover:text-white">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://www.youtube.com/@youthtvghana" class="text-gray-400 hover:text-white">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div>
                <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                <ul class="space-y-2">
                    <li><a href="About.html" class="text-gray-400 hover:text-white">About Us</a></li>
                    <li><a href="About.html#team" class="text-gray-400 hover:text-white">Our Team</a></li>
                    <li><a href="career.html" class="text-gray-400 hover:text-white">Careers</a></li>
                    <li><a href="contact.html" class="text-gray-400 hover:text-white">Contact</a></li>
                </ul>
            </div>
            
            <!-- Categories - Updated with category links -->
            <div>
                <h3 class="text-lg font-semibold mb-4">Categories</h3>
                <ul class="space-y-2">
                    <li><a href="news.html?category=general" class="text-gray-400 hover:text-white">General</a></li>
                    <li><a href="news.html?category=sports" class="text-gray-400 hover:text-white">Sports</a></li>
                    <li><a href="news.html?category=technology" class="text-gray-400 hover:text-white">Technology</a></li>
                    <li><a href="news.html?category=business" class="text-gray-400 hover:text-white">Business</a></li>
                    <li><a href="news.html?category=entertainment" class="text-gray-400 hover:text-white">Entertainment</a></li>
                    <li><a href="news.html?category=health" class="text-gray-400 hover:text-white">Health</a></li>
                    <li><a href="news.html?category=science" class="text-gray-400 hover:text-white">Science</a></li>
                </ul>
            </div>
            
            <!-- Contact -->
            <div>
                <h3 class="text-lg font-semibold mb-4">Contact Us</h3>
                <ul class="space-y-2 text-gray-400">
                    <li class="flex items-center">
                        <i class="fas fa-map-marker-alt mr-3"></i> Block 205, 21 Jordan Street, Madina Estates.
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-phone mr-3"></i> +233 30 291 8276
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-envelope mr-3"></i> info@youthtvonline.com
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="border-t border-gray-800 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center">
            <p class="text-gray-500 mb-4 md:mb-0">© 2025 Youth TV. All rights reserved.</p>
            <div class="flex space-x-6">
                <p class="text-gray-500 mb-4 md:mb-0">Powered By YouthTV</p>
            </div>
        </div>
    </div>
</footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('open');
            const icon = mobileMenuButton.querySelector('i');
            icon.classList.toggle('fa-bars');
            icon.classList.toggle('fa-times');
        });

        <?php if ($latestEpisode): ?>
        const audioPlayer = new Audio();
        audioPlayer.src = '<?= $audio_web_path . $latestEpisode["audio_file"] ?>';
        const playPauseBtn = document.getElementById('playPauseBtn');
        const progressBar = document.getElementById('progressBar');
        const currentTimeDisplay = document.getElementById('currentTime');
        const durationDisplay = document.getElementById('duration');
        const volumeControl = document.getElementById('volumeControl');
        const muteBtn = document.getElementById('muteBtn');
        const playingAnimation = document.getElementById('playingAnimation');
        const nowPlayingTitle = document.getElementById('nowPlayingTitle');
        const nowPlayingAuthor = document.getElementById('nowPlayingAuthor');
        const currentPodcastImage = document.getElementById('currentPodcastImage');
        const skipForwardBtn = document.getElementById('skipForward');
        const skipBackwardBtn = document.getElementById('skipBackward');

        audioPlayer.addEventListener('play', function() {
            fetch(`track_play.php?id=<?= $latestEpisode['id'] ?>`).catch(error => console.error('Error tracking play:', error));
        });

        function formatTime(seconds) {
            if (!seconds || isNaN(seconds)) return "0:00";
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = Math.floor(seconds % 60);
            return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
        }

        audioPlayer.addEventListener('timeupdate', function() {
            const progress = (audioPlayer.currentTime / audioPlayer.duration) * 100;
            progressBar.style.width = `${progress}%`;
            currentTimeDisplay.textContent = formatTime(audioPlayer.currentTime);
            if (audioPlayer.duration && !isNaN(audioPlayer.duration)) {
                durationDisplay.textContent = formatTime(audioPlayer.duration);
            }
        });

        audioPlayer.volume = volumeControl.value;
        volumeControl.addEventListener('input', function(e) {
            audioPlayer.volume = e.target.value;
            audioPlayer.muted = false;
            muteBtn.innerHTML = '<i class="fas fa-volume-up"></i>';
        });

        muteBtn.addEventListener('click', function() {
            audioPlayer.muted = !audioPlayer.muted;
            muteBtn.innerHTML = audioPlayer.muted ? '<i class="fas fa-volume-mute"></i>' : '<i class="fas fa-volume-up"></i>';
            volumeControl.value = audioPlayer.muted ? 0 : audioPlayer.volume;
        });

        playPauseBtn.addEventListener('click', function() {
            if (audioPlayer.paused) {
                audioPlayer.play();
                playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';
                playingAnimation.classList.remove('hidden');
            } else {
                audioPlayer.pause();
                playPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
                playingAnimation.classList.add('hidden');
            }
        });

        skipForwardBtn.addEventListener('click', function() {
            audioPlayer.currentTime = Math.min(audioPlayer.currentTime + 15, audioPlayer.duration);
        });

        skipBackwardBtn.addEventListener('click', function() {
            audioPlayer.currentTime = Math.max(audioPlayer.currentTime - 15, 0);
        });
        <?php endif; ?>

        function changeCategory(category) {
            document.querySelectorAll('.category-section').forEach(el => el.classList.add('hidden'));
            document.getElementById(`${category}-series`).classList.remove('hidden');
            document.querySelectorAll('.category-tab').forEach(tab => {
                const isActive = tab.dataset.category === category;
                tab.classList.toggle('active', isActive);
                tab.classList.toggle('text-indigo-700', isActive);
                tab.classList.toggle('text-gray-600', !isActive);
            });
        }

        document.querySelectorAll('.category-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                changeCategory(this.dataset.category);
            });
        });

        changeCategory('education');
    });
    </script>
</body>
</html>