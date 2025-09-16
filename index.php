<?php
require 'config.php';

// Fetch featured events for index page
$featured_events = $pdo->query("SELECT * FROM events WHERE featured = 1 AND status = 'upcoming' ORDER BY event_date ASC LIMIT 3")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Youth TV - Engaging Content for the Next Generation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sticky { position: sticky; top: 0; }
        .z-50 { z-index: 50; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .video-card:hover .play-icon { transform: scale(1.1); opacity: 0.9; }
        .news-card { transition: all 0.3s ease; }
        .news-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .category-tab.active { border-bottom: 3px solid #667eea; color: #667eea; font-weight: 600; }
        .marquee { white-space: nowrap; overflow: hidden; box-sizing: border-box; }
        .marquee span { display: inline-block; padding-left: 100%; animation: marquee 15s linear infinite; }
        @keyframes marquee { 0% { transform: translate(0, 0); } 100% { transform: translate(-100%, 0); } }
        .mobile-menu { max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; }
        .mobile-menu.open { max-height: 500px; }
        .logo-container { height: 50px; display: flex; align-items: center; }
        #subscribeBtn, #unsubscribeBtn { transition: all 0.3s ease; }
        #subscriptionMsg { min-height: 1.5rem; }
        html { scroll-behavior: smooth; }
        #newsletter:target { animation: highlight 2s ease; }
        @keyframes highlight { 
            0% { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); } 
            50% { background: linear-gradient(135deg, #8ea1f0 0%, #9a6bc5 100%); } 
            100% { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); } 
        }
        .podcast-player { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); }
        .audio-wave { display: flex; align-items: center; justify-content: space-between; width: 50px; height: 30px; }
        .audio-wave span { width: 3px; height: 10px; background-color: white; border-radius: 3px; animation: audio-wave 1.5s infinite ease-in-out; }
        .audio-wave span:nth-child(2) { animation-delay: 0.2s; }
        .audio-wave span:nth-child(3) { animation-delay: 0.4s; }
        .audio-wave span:nth-child(4) { animation-delay: 0.6s; }
        .audio-wave span:nth-child(5) { animation-delay: 0.8s; }
        @keyframes audio-wave { 0% { height: 10px; } 50% { height: 20px; } 100% { height: 10px; } }
        .education-badge { background-color: #10b981; color: white; }
        .science-badge { background-color: #8b5cf6; color: white; }
        .tech-badge { background-color: #a855f7; color: white; }
        .business-badge { background-color: #0ea5e9; color: white; }
        .entertainment-badge { background-color: #f43f5e; color: white; }
        .news-badge { background-color: #475569; color: white; }
        .blog-card { transition: all 0.3s ease; height: 100%; cursor: pointer; }
        .blog-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .blog-image { height: 200px; object-fit: cover; width: 100%; }
        .category-tag { transition: all 0.3s ease; cursor: pointer; }
        .category-tag:hover { transform: scale(1.05); }
        .post-modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.8); display: flex; justify-content: center; align-items: center; z-index: 100; opacity: 0; visibility: hidden; transition: all 0.3s ease; overflow-y: auto; }
        .post-modal.active { opacity: 1; visibility: visible; }
        .modal-content { background: white; border-radius: 12px; width: 90%; max-width: 800px; max-height: 90vh; overflow-y: auto; position: relative; transform: translateY(-20px); transition: transform 0.3s ease; }
        .post-modal.active .modal-content { transform: translateY(0); }
        .close-modal { position: absolute; top: 15px; right: 15px; background: rgba(0,0,0,0.2); width: 40px; height: 40px; border-radius: 50%; display: flex; justify-content: center; align-items: center; color: white; font-size: 1.5rem; cursor: pointer; z-index: 10; transition: all 0.3s ease; }
        .close-modal:hover { background: rgba(0,0,0,0.4); transform: rotate(90deg); }
        .modal-header { padding: 40px 40px 20px; position: relative; }
        .modal-image { height: 300px; width: 100%; object-fit: cover; border-radius: 8px; }
        .modal-body { padding: 0 40px 40px; }
        .post-content { line-height: 1.8; color: #333; font-size: 1.1rem; }
        .post-content h2, .post-content h3 { margin-top: 1.5em; margin-bottom: 0.8em; color: #2d3748; }
        .post-content p { margin-bottom: 1.5em; }
        .post-content ul, .post-content ol { margin-left: 1.5em; margin-bottom: 1.5em; }
        .post-content li { margin-bottom: 0.5em; }
        .post-content blockquote { border-left: 4px solid #667eea; padding-left: 1.5em; margin: 1.5em 0; font-style: italic; color: #4a5568; }
        .post-content pre { background: #2d3748; color: #e2e8f0; padding: 1em; border-radius: 8px; overflow-x: auto; margin: 1.5em 0; }
        .post-content code { background: #edf2f7; padding: 0.2em 0.4em; border-radius: 4px; font-family: monospace; }
        .modal-footer { padding: 20px 40px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
        .social-share { display: flex; gap: 10px; }
        .social-share a { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.2rem; transition: all 0.3s ease; cursor: pointer; }
        .social-share a:hover { transform: translateY(-3px); }
        .share-facebook { background-color: #3b5998; }
        .share-twitter { background-color: #1da1f2; }
        .share-linkedin { background-color: #0077b5; }
        .share-link { background-color: #667eea; }
        .modal-loading { min-height: 300px; display: flex; justify-content: center; align-items: center; flex-direction: column; }
        .loader { width: 48px; height: 48px; border: 5px solid rgba(102, 126, 234, 0.2); border-bottom-color: #667eea; border-radius: 50%; display: inline-block; animation: rotation 1s linear infinite; }
        @keyframes rotation { 0% { transform: rotate(0deg) } 100% { transform: rotate(360deg) } }
        .author-image { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <header class="sticky top-0 z-50 bg-white shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <a href="index.php" class="logo-container mr-3" style="height: 75px;">
                        <img src="logo.png" alt="Youth TV Logo" class="h-full hover:scale-105 transition-transform">
                    </a>
                </div>
                <nav class="hidden md:flex gap-8">
                    <a href="#home" class="text-gray-700 hover:text-indigo-600 font-medium relative group">
                        Home
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-indigo-600 transition-all group-hover:w-full"></span>
                    </a>
                    <a href="news.html" class="text-gray-700 hover:text-indigo-600 font-medium relative group">
                        News
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-indigo-600 transition-all group-hover:w-full"></span>
                    </a>
                    <a href="videos.html" class="text-gray-700 hover:text-indigo-600 font-medium relative group">
                        Videos
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-indigo-600 transition-all group-hover:w-full"></span>
                    </a>
                    <a href="podcast.php" class="text-gray-700 hover:text-indigo-600 font-medium relative group">
                        Podcasts
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-indigo-600 transition-all group-hover:w-full"></span>
                    </a>
                    <a href="blog.html" class="text-gray-700 hover:text-indigo-600 font-medium relative group">
                        Blog
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-indigo-600 transition-all group-hover:w-full"></span>
                    </a>
                    <a href="events.php" class="text-gray-700 hover:text-indigo-600 font-medium relative group">
                        Events
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-indigo-600 transition-all group-hover:w-full"></span>
                    </a>
                </nav>
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-button" class="text-gray-700 focus:outline-none">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
                <div class="hidden md:block">
                    <a href="#newsletter" class="bg-indigo-600 text-white px-6 py-2 rounded-full font-semibold hover:bg-indigo-700 transition-all shadow-md inline-block">
                        Subscribe
                    </a>
                </div>
            </div>
            <div id="mobile-menu" class="mobile-menu md:hidden bg-white mt-2 rounded-lg shadow-xl">
                <div class="px-2 pt-2 pb-4 space-y-2">
                    <a href="#home" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">Home</a>
                    <a href="news.html" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">News</a>
                    <a href="videos.html" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">Videos</a>
                    <a href="podcast.php" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">Podcasts</a>
                    <a href="blog.html" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">Blog</a>
                    <a href="events.php" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">Events</a>
                    <a href="#newsletter" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-full font-semibold hover:bg-indigo-700 transition mt-2 text-center block">
                        Subscribe
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="bg-blue-600 text-white py-2 px-4">
        <div class="container mx-auto flex items-center">
            <span class="font-bold mr-4 whitespace-nowrap">BREAKING:</span>
            <div class="marquee">
                <span>Youth TV launches new mentorship program • Tech summit registration now open • Interview with young entrepreneur airing tomorrow at 8PM</span>
            </div>
        </div>
    </div>

    <section id="home" class="gradient-bg text-white py-16">
        <div class="container mx-auto px-4 flex flex-col md:flex-row items-center">
            <div class="md:w-1/2 mb-8 md:mb-0">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">Empowering the Next Generation</h2>
                <p class="text-xl mb-6">Your premier destination for youth-focused news, entertainment, and inspiration.</p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="#featured-video-grid">
                    <button class="bg-white text-indigo-700 px-6 py-3 rounded-full font-semibold hover:bg-gray-100 transition" >
                        Watch Latest Episode
                    </button></a>
                    <a href="https://www.facebook.com/youthtvgh/?locale=gn_PY">
                    <button class="border-2 border-white text-white px-6 py-3 rounded-full font-semibold hover:bg-white hover:text-indigo-700 transition">
                        Join Our Community
                    </button></a>
                </div>
            </div>
            <div class="md:w-1/2 relative">
                <div class="relative aspect-video bg-black rounded-xl overflow-hidden shadow-2xl">
                    <img src="https://images.unsplash.com/photo-1579389083078-4e7018379f7e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" 
                         alt="Youth TV Show" class="w-full h-full object-cover">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="play-icon bg-white bg-opacity-80 rounded-full w-16 h-16 flex items-center justify-center transition duration-300">
                            <i class="fas fa-play text-indigo-700 text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="news" class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold mb-8 text-center">Top Headlines</h2>
            <div class="flex justify-center mb-8 border-b border-gray-200">
                <button class="category-tab active px-6 py-2" data-category="general">All</button>
                <button class="category-tab px-6 py-2" data-category="business">Business</button>
                <button class="category-tab px-6 py-2" data-category="entertainment">Entertainment</button>
                <button class="category-tab px-6 py-2" data-category="health">Health</button>
                <button class="category-tab px-6 py-2" data-category="science">Science</button>
                <button class="category-tab px-6 py-2" data-category="sports">Sports</button>
                <button class="category-tab px-6 py-2" data-category="technology">Tech</button>
            </div>
            <div id="news-loading" class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-2xl text-indigo-600"></i>
                <p class="mt-2">Loading news...</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="index-news-grid"></div>
            <div class="text-center mt-8">
                <a href="news.html" class="border-2 border-indigo-600 text-indigo-600 px-6 py-2 rounded-full font-semibold hover:bg-indigo-600 hover:text-white transition">
                    View All News
                </a>
            </div>
        </div>
    </section>

    <section id="videos" class="py-12 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold mb-8 text-center">Featured Videos</h2>
            <div id="featured-video-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div id="video-loading" class="col-span-full text-center py-8">
                    <i class="fas fa-spinner fa-spin text-2xl text-indigo-600"></i>
                    <p class="mt-2">Loading featured videos...</p>
                </div>
            </div>
            <div class="text-center mt-8">
                <a href="https://www.youtube.com/@youthtvghana" target="_blank" 
                   class="bg-red-600 text-white px-6 py-3 rounded-full font-semibold hover:bg-red-700 transition inline-flex items-center">
                    <i class="fab fa-youtube mr-2"></i> Visit Our YouTube Channel
                </a>
            </div>
        </div>
    </section>

    <section id="blog" class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold mb-8 text-center">From Our Blog</h2>
            <div id="blog-loading" class="col-span-full text-center py-8">
                <i class="fas fa-spinner fa-spin text-2xl text-indigo-600"></i>
                <p class="mt-2">Loading blog posts...</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="index-blog-grid"></div>
            <div class="text-center mt-8">
                <a href="blog.html" class="border-2 border-indigo-600 text-indigo-600 px-6 py-2 rounded-full font-semibold hover:bg-indigo-600 hover:text-white transition">
                    View All Blog Posts
                </a>
            </div>
        </div>
    </section>

    <section id="podcasts" class="py-12 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold mb-8 text-center">Latest Podcasts</h2>
            <div id="podcast-player" class="podcast-player text-white p-6 mb-12 rounded-xl shadow-xl">
                <div class="max-w-4xl mx-auto">
                    <div class="flex items-center mb-4">
                        <img id="currentPodcastImage" src="https://images.unsplash.com/photo-1571330735066-03aaa9429d89?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" class="w-24 h-24 rounded-lg mr-4 object-cover">
                        <div>
                            <h2 class="text-2xl font-bold" id="nowPlayingTitle">Loading latest episode...</h2>
                            <p class="text-gray-200" id="nowPlayingAuthor">Youth TV Podcast</p>
                        </div>
                    </div>
                    <div class="progress-bar mb-2 bg-gray-200 rounded-full h-2">
                        <div class="progress-filled bg-white rounded-full h-full w-0" id="progressBar"></div>
                    </div>
                    <div class="flex justify-between text-sm text-gray-300 mb-4">
                        <span id="currentTime">0:00</span>
                        <span id="duration">0:00</span>
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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="podcast-grid"></div>
            <div class="text-center mt-8">
                <a href="podcast.php" class="border-2 border-indigo-600 text-indigo-600 px-6 py-2 rounded-full font-semibold hover:bg-indigo-600 hover:text-white transition">
                    View All Podcasts
                </a>
            </div>
        </div>
    </section>

    <!-- Events Section -->
    <section id="events" class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold mb-8 text-center">Upcoming Events</h2>
            
            <?php if (!empty($featured_events)): ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <?php foreach ($featured_events as $event): ?>
                <?php 
                    $event_date = new DateTime($event['event_date']);
                    $formatted_date = $event_date->format('M d');
                ?>
                <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition">
                    <div class="relative h-48 bg-gray-900">
                        <?php if ($event['image_url']): ?>
                        <img src="<?= htmlspecialchars($event['image_url']) ?>" 
                             alt="<?= htmlspecialchars($event['title']) ?>" 
                             class="w-full h-full object-cover opacity-90">
                        <?php else: ?>
                        <div class="bg-gray-200 border-2 border-dashed w-full h-full"></div>
                        <?php endif; ?>
                        <div class="absolute top-4 left-4 bg-white text-indigo-700 px-3 py-1 rounded-full font-bold text-sm">
                            <?= $formatted_date ?>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center text-sm text-gray-500 mb-2">
                            <i class="fas fa-map-marker-alt mr-2"></i> <?= htmlspecialchars($event['location']) ?>
                        </div>
                        <h3 class="text-xl font-bold mb-3"><?= htmlspecialchars($event['title']) ?></h3>
                        <p class="text-gray-600 mb-4"><?= htmlspecialchars(substr($event['description'], 0, 100)) ?>...</p>
                        <?php if ($event['registration_link'] && $event['status'] === 'upcoming'): ?>
                        <a href="<?= htmlspecialchars($event['registration_link']) ?>" target="_blank" class="block w-full bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700 transition text-center">
                            Register Now
                        </a>
                        <?php else: ?>
                        <button class="w-full bg-gray-300 text-gray-600 py-2 rounded cursor-not-allowed">
                            Registration Closed
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-12">
                <i class="fas fa-calendar-times text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-600">No upcoming events at this time. Check back soon!</p>
            </div>
            <?php endif; ?>
            
            <div class="text-center mt-8">
                <a href="events.php" class="border-2 border-indigo-600 text-indigo-600 px-6 py-2 rounded-full font-semibold hover:bg-indigo-600 hover:text-white transition">
                    View All Events
                </a>
            </div>
        </div>
    </section>

    <section id="newsletter" class="py-12 gradient-bg text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-4">Stay Updated</h2>
            <p class="text-xl mb-8 max-w-2xl mx-auto">
                Subscribe to our newsletter for the latest content, events, and opportunities delivered to your inbox.
            </p>
            <div class="max-w-md mx-auto">
                <div class="flex justify-center gap-2 mb-4">
                    <button id="subscribeBtn" class="px-4 py-2 rounded-full bg-white text-indigo-600 font-semibold">
                        Subscribe
                    </button>
                    <button id="unsubscribeBtn" class="px-4 py-2 rounded-full border border-white text-white font-semibold">
                        Unsubscribe
                    </button>
                </div>
                <form id="subscriptionForm" class="flex gap-2">
                    <input 
                        type="email" 
                        name="Email"
                        placeholder="Your email address"
                        required
                        class="flex-grow px-4 py-3 border-2 border-white rounded-l bg-white/20 backdrop-blur-sm
                               focus:outline-none focus:ring-2 focus:ring-white
                               placeholder-indigo-200 placeholder-opacity-100
                               text-indigo-100 focus:text-white transition-colors"
                        autocomplete="email"
                    >
                    <button 
                        type="submit" 
                        class="bg-white text-indigo-700 px-6 py-3 font-semibold hover:bg-gray-100 transition rounded-r"
                    >
                        Subscribe
                    </button>
                </form>
                <p class="text-sm mt-4 opacity-80">We respect your privacy. Unsubscribe at any time.</p>
                <span id="subscriptionMsg" class="block mt-4 text-sm"></span>
            </div>
        </div>
    </section>
    
    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
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
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="About.html" class="text-gray-400 hover:text-white">About Us</a></li>
                        <li><a href="About.html#team" class="text-gray-400 hover:text-white">Our Team</a></li>
                        <li><a href="career.html" class="text-gray-400 hover:text-white">Careers</a></li>
                        <li><a href="contact.html" class="text-gray-400 hover:text-white">Contact</a></li>
                    </ul>
                </div>
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

    <div class="post-modal" id="post-modal">
        <div class="close-modal" id="close-modal">
            <i class="fas fa-times"></i>
        </div>
        <div class="modal-content">
            <div class="modal-loading" id="modal-loading">
                <div class="loader mx-auto mb-4"></div>
                <p class="text-indigo-600 font-medium">Loading post...</p>
            </div>
            <div id="modal-loaded-content" class="hidden">
                <div class="modal-header">
                    <img id="modal-image" src="" alt="Post Image" class="modal-image mb-6">
                    <div class="flex flex-wrap gap-3 mb-4">
                        <span id="modal-category" class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-sm font-medium"></span>
                        <span id="modal-date" class="text-gray-500"></span>
                    </div>
                    <h1 id="modal-title" class="text-3xl font-bold text-gray-900 mb-4"></h1>
                    <div class="flex items-center mb-6">
                        <img id="modal-author-image" src="" alt="Author" class="author-image mr-3">
                        <div>
                            <div id="modal-author" class="font-medium"></div>
                            <div class="flex items-center text-sm text-gray-500">
                                <span id="modal-read-time" class="mr-4"></span>
                                <span id="modal-likes" class="flex items-center">
                                    <i class="fas fa-heart text-red-500 mr-1"></i>
                                    <span id="like-count"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-body">
                    <div id="modal-content" class="post-content"></div>
                </div>
                <div class="modal-footer">
                    <div class="social-share">
                        <a href="#" class="share-facebook" title="Share on Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="share-twitter" title="Share on Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="share-linkedin" title="Share on LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="#" class="share-link" title="Copy link" id="copy-link">
                            <i class="fas fa-link"></i>
                        </a>
                    </div>
                    <button id="save-post" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition flex items-center">
                        <i class="far fa-bookmark mr-2"></i> Save
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const NEWS_API_KEY = 'de0e69ec1e2645f98ebdf97e89fff832';
        const INDEX_PAGE_SIZE = 3;

        async function fetchIndexNews(category = 'general') {
            try {
                const apiUrl = `https://newsapi.org/v2/top-headlines?country=us&pageSize=${INDEX_PAGE_SIZE}${category !== 'general' ? `&category=${category}` : ''}&apiKey=${NEWS_API_KEY}`;
                const proxyUrl = 'https://api.allorigins.win/get?url=';
                const response = await fetch(proxyUrl + encodeURIComponent(apiUrl));
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const data = await response.json();
                return JSON.parse(data.contents);
            } catch (error) {
                console.error('Error fetching news:', error);
                throw error;
            }
        }

        function createIndexNewsCard(article) {
            return `
                <div class="news-card bg-white rounded-lg overflow-hidden shadow-md transition duration-300">
                    <div class="relative h-48 overflow-hidden">
                        <img src="${article.urlToImage || 'https://via.placeholder.com/400x225'}" 
                             alt="${article.title}" 
                             class="w-full h-full object-cover">
                        <span class="absolute top-2 left-2 ${getCategoryColor(article.category)} text-white text-xs px-2 py-1 rounded">
                            ${article.source.name}
                        </span>
                    </div>
                    <div class="p-6">
                        <span class="${getCategoryTextColor(article.category)} text-sm font-medium">
                            ${article.category || 'General'}
                        </span>
                        <h3 class="text-xl font-bold mt-2 mb-3">${article.title}</h3>
                        <p class="text-gray-600 mb-4">${article.description || ''}</p>
                        <div class="flex items-center justify-between">
                            <a href="${article.url}" target="_blank" 
                               class="text-indigo-600 hover:text-indigo-800">
                                Read More
                            </a>
                            <div class="flex items-center text-sm text-gray-500">
                                <span>${new Date(article.publishedAt).toLocaleDateString()}</span>
                                <span class="mx-2">•</span>
                                <span>${Math.ceil((article.content?.split(' ').length || 0) / 200)} min read</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function getCategoryColor(category) {
            const colors = {
                sports: 'bg-blue-600',
                technology: 'bg-indigo-600',
                business: 'bg-purple-600',
                entertainment: 'bg-pink-600',
                default: 'bg-gray-600'
            };
            return colors[category?.toLowerCase()] || colors.default;
        }

        function getCategoryTextColor(category) {
            const colors = {
                sports: 'text-blue-600',
                technology: 'text-indigo-600',
                business: 'text-purple-600',
                entertainment: 'text-pink-600',
                default: 'text-gray-600'
            };
            return colors[category?.toLowerCase()] || colors.default;
        }

        document.addEventListener('DOMContentLoaded', async () => {
            const newsGrid = document.getElementById('index-news-grid');
            const loading = document.getElementById('news-loading');
            try {
                loading.classList.remove('hidden');
                const newsData = await fetchIndexNews();
                if (newsData.articles.length > 0) {
                    newsGrid.innerHTML = newsData.articles.map(createIndexNewsCard).join('');
                } else {
                    newsGrid.innerHTML = `
                        <div class="col-span-full text-center">
                            <p class="text-gray-600">No recent news found</p>
                        </div>
                    `;
                }
            } catch (error) {
                newsGrid.innerHTML = `
                    <div class="col-span-full text-center">
                        <p class="text-red-600">Error loading news</p>
                    </div>
                `;
            } finally {
                loading.classList.add('hidden');
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.category-tab').forEach(tab => {
                tab.addEventListener('click', async () => {
                    document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');
                    const category = tab.dataset.category;
                    const newsGrid = document.getElementById('index-news-grid');
                    const loading = document.getElementById('news-loading');
                    try {
                        loading.classList.remove('hidden');
                        const newsData = await fetchIndexNews(category);
                        if (newsData.articles.length > 0) {
                            newsGrid.innerHTML = newsData.articles.map(createIndexNewsCard).join('');
                        } else {
                            newsGrid.innerHTML = `
                                <div class="col-span-full text-center">
                                    <p class="text-gray-600">No news found in this category</p>
                                </div>
                            `;
                        }
                    } catch (error) {
                        newsGrid.innerHTML = `
                            <div class="col-span-full text-center">
                                <p class="text-red-600">Error loading news</p>
                            </div>
                        `;
                    } finally {
                        loading.classList.add('hidden');
                    }
                });
            });
        });

        const scriptURL = 'https://script.google.com/macros/s/AKfycbyxWduebhGeQz8zYmT72ZCiuhA2aHNQBQBgZLwQ9jQU1NR4cIibVwpTNMIQ0lIJQaYI/exec';
        let isSubscribing = true;

        document.getElementById('subscribeBtn').addEventListener('click', () => {
            isSubscribing = true;
            document.getElementById('subscribeBtn').classList.add('bg-white', 'text-indigo-600');
            document.getElementById('unsubscribeBtn').classList.remove('bg-white', 'text-indigo-600');
            document.getElementById('subscriptionForm').querySelector('button[type="submit"]').textContent = 'Subscribe';
        });

        document.getElementById('unsubscribeBtn').addEventListener('click', () => {
            isSubscribing = false;
            document.getElementById('unsubscribeBtn').classList.add('bg-white', 'text-indigo-600');
            document.getElementById('subscribeBtn').classList.remove('bg-white', 'text-indigo-600');
            document.getElementById('subscriptionForm').querySelector('button[type="submit"]').textContent = 'Unsubscribe';
        });

        document.getElementById('subscriptionForm').addEventListener('submit', e => {
            e.preventDefault();
            const formData = new FormData(e.target);
            formData.append('action', isSubscribing ? 'subscribe' : 'unsubscribe');
            const msgElement = document.getElementById('subscriptionMsg');
            fetch(scriptURL, { 
                method: 'POST', 
                body: formData
            })
            .then(response => response.text())
            .then(message => {
                msgElement.textContent = message;
                msgElement.className = 'block mt-4 text-sm text-green-300';
                e.target.reset();
            })
            .catch(error => {
                msgElement.textContent = "Error processing request";
                msgElement.className = 'block mt-4 text-sm text-red-300';
                console.error('Error!', error.message);
            })
            .finally(() => {
                setTimeout(() => {
                    msgElement.textContent = '';
                    msgElement.className = 'block mt-4 text-sm';
                }, 4000);
            });
        });      

        const YOUTUBE_CHANNEL_ID = 'UCUzkF-8dKS07whUZKJHQylg';
        const API_KEY = 'AIzaSyCDddiG686BvKtJNc7bTcyWMVF7RhU6nJY';

        function parseDuration(duration) {
            const match = duration.match(/PT(\d+H)?(\d+M)?(\d+S)?/);
            const hours = parseInt(match[1]) || 0;
            const minutes = parseInt(match[2]) || 0;
            const seconds = parseInt(match[3]) || 0;
            if (hours > 0) {
                return `${hours}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            }
            return `${minutes}:${String(seconds).padStart(2, '0')}`;
        }

        async function fetchFeaturedVideos() {
            try {
                const channelRes = await fetch(
                    `https://www.googleapis.com/youtube/v3/channels?part=contentDetails&id=${YOUTUBE_CHANNEL_ID}&key=${API_KEY}`
                );
                const channelData = await channelRes.json();
                const uploadsPlaylistId = channelData.items[0].contentDetails.relatedPlaylists.uploads;
                const videosRes = await fetch(
                    `https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&maxResults=3&playlistId=${uploadsPlaylistId}&key=${API_KEY}`
                );
                const videosData = await videosRes.json();
                const videoIds = videosData.items.map(v => v.snippet.resourceId.videoId);
                const statsRes = await fetch(
                    `https://www.googleapis.com/youtube/v3/videos?part=contentDetails,statistics&id=${videoIds.join(',')}&key=${API_KEY}`
                );
                const statsData = await statsRes.json();
                return videosData.items.map((item, index) => ({
                    id: item.snippet.resourceId.videoId,
                    title: item.snippet.title,
                    thumbnail: item.snippet.thumbnails.high?.url || 'https://via.placeholder.com/400x225',
                    publishedAt: new Date(item.snippet.publishedAt).toLocaleDateString(),
                    views: statsData.items[index].statistics.viewCount || 0,
                    duration: parseDuration(statsData.items[index].contentDetails.duration)
                }));
            } catch (error) {
                console.error('Error fetching featured videos:', error);
                return [];
            }
        }

        function createFeaturedVideoCard(video) {
            return `
                <div class="video-card bg-white rounded-lg overflow-hidden shadow-md transition duration-300">
                    <div class="relative aspect-video bg-gray-900">
                        <a href="https://youtu.be/${video.id}" target="_blank" class="block h-full">
                            <img src="${video.thumbnail}" alt="${video.title}" 
                                 class="w-full h-full object-cover">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="play-icon bg-white bg-opacity-80 rounded-full w-16 h-16 flex items-center justify-center transition duration-300">
                                    <i class="fas fa-play text-indigo-700 text-2xl"></i>
                                </div>
                            </div>
                        </a>
                        <span class="absolute bottom-2 right-2 bg-black bg-opacity-70 text-white text-xs px-2 py-1 rounded">
                            ${video.duration}
                        </span>
                    </div>
                    <div class="p-4">
                        <h3 class="font-bold mb-2">${video.title}</h3>
                        <div class="flex items-center text-sm text-gray-600">
                            <span>${Number(video.views).toLocaleString()} views</span>
                            <span class="mx-2">•</span>
                            <span>${video.publishedAt}</span>
                        </div>
                    </div>
                </div>
            `;
        }

        document.addEventListener('DOMContentLoaded', async () => {
            const videoGrid = document.getElementById('featured-video-grid');
            const loading = document.getElementById('video-loading');
            try {
                loading.classList.remove('hidden');
                const videos = await fetchFeaturedVideos();
                if (videos.length > 0) {
                    videoGrid.innerHTML = videos.map(createFeaturedVideoCard).join('');
                } else {
                    videoGrid.innerHTML = `
                        <div class="col-span-full text-center">
                            <p class="text-gray-600">No featured videos available</p>
                        </div>
                    `;
                }
            } catch (error) {
                videoGrid.innerHTML = `
                    <div class="col-span-full text-center">
                        <p class="text-red-600">Error loading featured videos</p>
                    </div>
                `;
            } finally {
                loading.classList.add('hidden');
            }
        });

        document.addEventListener('DOMContentLoaded', async () => {
            const audioPlayer = new Audio();
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
            const podcastGrid = document.getElementById('podcast-grid');
            try {
                const response = await fetch('get_podcasts.php?limit=3');
                const podcasts = await response.json();
                if (podcasts.length > 0) {
                    const latestPodcast = podcasts[0];
                    audioPlayer.src = latestPodcast.audio_file;
                    nowPlayingTitle.textContent = latestPodcast.title;
                    nowPlayingAuthor.textContent = latestPodcast.series_title;
                    currentPodcastImage.src = latestPodcast.series_cover;
                    durationDisplay.textContent = formatTime(latestPodcast.duration);
                    podcastGrid.innerHTML = podcasts.map(podcast => `
                        <div class="bg-white rounded-lg overflow-hidden shadow-md transition duration-300">
                            <div class="relative h-48 overflow-hidden">
                                <img src="${podcast.series_cover}" alt="${podcast.title}" class="w-full h-full object-cover">
                                <div class="absolute bottom-2 right-2 bg-black bg-opacity-60 text-white text-xs px-2 py-1 rounded">
                                    ${formatTime(podcast.duration)}
                                </div>
                            </div>
                            <div class="p-4">
                                <h3 class="font-bold text-xl mb-2">${podcast.title}</h3>
                                <p class="text-gray-600 mb-4">${podcast.description.substring(0, 100)}...</p>
                                <button class="w-full bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700 transition play-podcast" data-audio="${podcast.audio_file}" data-title="${podcast.title}" data-author="${podcast.series_title}" data-image="${podcast.series_cover}" data-duration="${podcast.duration}">
                                    Play Episode
                                </button>
                            </div>
                        </div>
                    `).join('');
                    document.querySelectorAll('.play-podcast').forEach(btn => {
                        btn.addEventListener('click', function() {
                            audioPlayer.src = this.dataset.audio;
                            nowPlayingTitle.textContent = this.dataset.title;
                            nowPlayingAuthor.textContent = this.dataset.author;
                            currentPodcastImage.src = this.dataset.image;
                            durationDisplay.textContent = formatTime(parseInt(this.dataset.duration));
                            audioPlayer.play();
                            playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';
                            playingAnimation.classList.remove('hidden');
                        });
                    });
                } else {
                    podcastGrid.innerHTML = `
                        <div class="col-span-full text-center">
                            <p class="text-gray-600">No podcasts available</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading podcasts:', error);
                podcastGrid.innerHTML = `
                    <div class="col-span-full text-center">
                        <p class="text-red-600">Error loading podcasts</p>
                    </div>
                `;
            }

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
        });

        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenuButton.addEventListener('click', function() {
                mobileMenu.classList.toggle('open');
            });
            document.querySelectorAll('#mobile-menu a').forEach(link => {
                link.addEventListener('click', function() {
                    mobileMenu.classList.remove('open');
                });
            });
            const tabs = document.querySelectorAll('.category-tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    tabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                });
            });
            const videoCards = document.querySelectorAll('.video-card');
            videoCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.querySelector('.play-icon').classList.add('scale-110', 'opacity-90');
                });
                card.addEventListener('mouseleave', function() {
                    this.querySelector('.play-icon').classList.remove('scale-110', 'opacity-90');
                });
            });
        });
        
        const DEV_TO_API_URL = 'https://dev.to/api/articles';
        const INDEX_BLOG_POSTS_COUNT = 3;
        
        async function fetchBlogPostsForIndex() {
            try {
                document.getElementById('blog-loading').classList.remove('hidden');
                const response = await fetch(`${DEV_TO_API_URL}?top=30&per_page=${INDEX_BLOG_POSTS_COUNT}`);
                if (!response.ok) throw new Error('Failed to fetch blog posts');
                const data = await response.json();
                const posts = data.map(post => ({
                    id: post.id,
                    title: post.title,
                    excerpt: { rendered: post.description || "Read this insightful article..." },
                    content: { rendered: post.body_html || "" },
                    date: post.published_at,
                    author: post.user?.name || 'Dev.to Author',
                    positive_reactions_count: post.positive_reactions_count || 0,
                    featured_image: post.cover_image || post.social_image || 
                                 'https://images.unsplash.com/photo-1541462608143-67571c6738dd?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&h=400&q=80',
                    category: 'technology'
                }));
                document.getElementById('blog-loading').classList.add('hidden');
                return posts;
            } catch (error) {
                console.error('Error fetching blog posts for index:', error);
                document.getElementById('blog-loading').innerHTML = `
                    <p class="text-red-600">Error loading blog posts</p>
                `;
                return [];
            }
        }

        function createIndexBlogCard(post) {
            const wordCount = post.excerpt.rendered ? post.excerpt.rendered.split(' ').length : 0;
            const readTime = Math.max(1, Math.ceil(wordCount / 200));
            return `
                <div class="bg-white rounded-lg overflow-hidden shadow-md blog-card" data-id="${post.id}">
                    <div class="h-48 overflow-hidden">
                        <img src="${post.featured_image}" 
                             alt="${post.title}" 
                             class="blog-image w-full h-full object-cover">
                    </div>
                    <div class="p-6">
                        <div class="flex items-center text-sm text-gray-500 mb-2">
                            <span>${new Date(post.date).toLocaleDateString()}</span>
                            <span class="mx-2">•</span>
                            <span>By ${post.author}</span>
                        </div>
                        <h3 class="text-xl font-bold mb-3">${post.title}</h3>
                        <p class="text-gray-600 mb-4">${post.excerpt.rendered}</p>
                        <a class="text-indigo-600 font-medium hover:text-indigo-800 inline-flex items-center">
                            Read More <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>
            `;
        }

        async function openPostModal(post) {
            const modal = document.getElementById('post-modal');
            const modalContent = document.getElementById('modal-loaded-content');
            const modalLoading = document.getElementById('modal-loading');
            modal.classList.add('active');
            modalLoading.style.display = 'flex';
            modalContent.classList.add('hidden');
            const wordCount = post.content.rendered ? post.content.rendered.split(' ').length : 0;
            const readTime = Math.max(1, Math.ceil(wordCount / 200));
            document.getElementById('modal-image').src = post.featured_image;
            document.getElementById('modal-category').textContent = post.category.charAt(0).toUpperCase() + post.category.slice(1);
            document.getElementById('modal-date').textContent = new Date(post.date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            document.getElementById('modal-title').textContent = post.title;
            document.getElementById('modal-author-image').src = `https://i.pravatar.cc/150?u=${post.author}`;
            document.getElementById('modal-author').textContent = post.author;
            document.getElementById('modal-read-time').textContent = `${readTime} min read`;
            document.getElementById('modal-content').innerHTML = post.content.rendered || "<p>Content not available</p>";
            document.getElementById('like-count').textContent = post.positive_reactions_count;
            modalLoading.style.display = 'none';
            modalContent.classList.remove('hidden');
        }

        document.addEventListener('DOMContentLoaded', async () => {
            const blogGrid = document.getElementById('index-blog-grid');
            try {
                const posts = await fetchBlogPostsForIndex();
                if (posts.length > 0) {
                    blogGrid.innerHTML = posts.map(createIndexBlogCard).join('');
                    document.querySelectorAll('.blog-card').forEach(card => {
                        card.addEventListener('click', () => {
                            const id = card.dataset.id;
                            const post = posts.find(p => p.id.toString() === id);
                            if (post) {
                                openPostModal(post);
                            }
                        });
                    });
                } else {
                    blogGrid.innerHTML = `
                        <div class="col-span-full text-center">
                            <p class="text-gray-600">No blog posts available</p>
                        </div>
                    `;
                }
            } catch (error) {
                blogGrid.innerHTML = `
                    <div class="col-span-full text-center">
                        <p class="text-red-600">Error loading blog posts</p>
                    </div>
                `;
            }
            document.getElementById('close-modal').addEventListener('click', () => {
                document.getElementById('post-modal').classList.remove('active');
            });
            document.getElementById('post-modal').addEventListener('click', (e) => {
                if (e.target === document.getElementById('post-modal')) {
                    document.getElementById('post-modal').classList.remove('active');
                }
            });
            document.getElementById('save-post').addEventListener('click', function() {
                this.innerHTML = '<i class="fas fa-bookmark mr-2"></i> Saved';
                this.classList.add('bg-indigo-800');
                setTimeout(() => {
                    this.innerHTML = '<i class="far fa-bookmark mr-2"></i> Save';
                    this.classList.remove('bg-indigo-800');
                }, 2000);
            });
            document.getElementById('copy-link').addEventListener('click', (e) => {
                e.preventDefault();
                const tempInput = document.createElement('input');
                tempInput.value = window.location.href;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
                const tooltip = document.createElement('div');
                tooltip.textContent = 'Link copied!';
                tooltip.classList.add('bg-indigo-600', 'text-white', 'px-3', 'py-1', 'rounded', 'text-sm', 'absolute', 'top-0', 'right-0', 'mt-12', 'mr-4');
                document.getElementById('copy-link').appendChild(tooltip);
                setTimeout(() => {
                    tooltip.remove();
                }, 2000);
            });
        });
    </script>
</body>
</html>