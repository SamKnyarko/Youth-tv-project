<?php
require 'config.php';

// Get filter parameters
$filter_status = $_GET['status'] ?? 'upcoming';
$search = $_GET['search'] ?? '';

// Build query based on filters
$query = "SELECT * FROM events WHERE 1=1";
$params = [];

if ($filter_status !== 'all') {
    $query .= " AND status = ?";
    $params[] = $filter_status;
}

if (!empty($search)) {
    $query .= " AND (title LIKE ? OR description LIKE ? OR location LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY featured DESC, event_date ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$events = $stmt->fetchAll();

// Get featured events for hero section
$featured_events = $pdo->query("SELECT * FROM events WHERE featured = 1 AND status = 'upcoming' ORDER BY event_date ASC LIMIT 3")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - Youth TV</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Custom CSS from index.html */
        .sticky {
            position: sticky;
            top: 0;
        }
        .z-50 {
            z-index: 50;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .video-card:hover .play-icon {
            transform: scale(1.1);
            opacity: 0.9;
        }
        .news-card {
            transition: all 0.3s ease;
        }
        .news-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .category-tab.active {
            border-bottom: 3px solid #667eea;
            color: #667eea;
            font-weight: 600;
        }
        .marquee {
            white-space: nowrap;
            overflow: hidden;
            box-sizing: border-box;
        }
        .marquee span {
            display: inline-block;
            padding-left: 100%;
            animation: marquee 15s linear infinite;
        }
        @keyframes marquee {
            0%   { transform: translate(0, 0); }
            100% { transform: translate(-100%, 0); }
        }
        /* Mobile menu styles */
        .mobile-menu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }
        .mobile-menu.open {
            max-height: 500px;
        }
        /* Logo container */
        .logo-container {
            height: 50px;
            display: flex;
            align-items: center;
        }
        #subscribeBtn, #unsubscribeBtn {
            transition: all 0.3s ease;
        }
        #subscriptionMsg {
            min-height: 1.5rem;
        }
        /* Added smooth scrolling */
        html {
            scroll-behavior: smooth;
        }
        /* Highlight for newsletter section */
        #newsletter:target {
            animation: highlight 2s ease;
        }
        @keyframes highlight {
            0% { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
            50% { background: linear-gradient(135deg, #8ea1f0 0%, #9a6bc5 100%); }
            100% { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        }
        
        
        /* Event specific styles */
        .hero-gradient { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .event-status { animation: pulse 2s infinite; }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <!-- Header - Same as index.html -->
    <header class="sticky top-0 z-50 bg-white shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <!-- Logo and brand -->
                <div class="flex items-center">
                    <a href="index.php" class="logo-container mr-3" style="height: 75px;">
                        <img src="logo.png" 
                             alt="Youth TV Logo" 
                             class="h-full hover:scale-105 transition-transform">
                    </a>
                </div>
    
                <!-- Desktop Navigation -->
                <nav class="hidden md:flex gap-8">
                    <a href="index.php" class="text-gray-700 hover:text-indigo-600 font-medium relative group">
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
                    <a href="events.php" class="text-indigo-600 font-medium relative group">
                        Events
                        <span class="absolute bottom-0 left-0 w-full h-0.5 bg-indigo-600"></span>
                    </a>
                </nav>
    
                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-button" class="text-gray-700 focus:outline-none">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
    
                <!-- Subscribe button - Updated to link to newsletter section -->
                <div class="hidden md:block">
                    <a href="#newsletter" class="bg-indigo-600 text-white px-6 py-2 rounded-full font-semibold hover:bg-indigo-700 transition-all shadow-md inline-block">
                        Subscribe
                    </a>
                </div>
            </div>
    
            <!-- Mobile menu -->
            <div id="mobile-menu" class="mobile-menu md:hidden bg-white mt-2 rounded-lg shadow-xl">
                <div class="px-2 pt-2 pb-4 space-y-2">
                    <a href="index.php" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">Home</a>
                    <a href="news.html" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">News</a>
                    <a href="videos.html" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">Videos</a>
                    <a href="podcast.php" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">Podcasts</a>
                    <a href="blog.html" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">Blog</a>
                    <a href="events.php" class="block px-3 py-2 rounded-md text-indigo-600 font-medium hover:bg-gray-100">Events</a>
                    <!-- Updated to link to newsletter section -->
                    <a href="#newsletter" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-full font-semibold hover:bg-indigo-700 transition mt-2 text-center block">
                        Subscribe
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Breaking News Marquee -->
    <div class="bg-blue-600 text-white py-2 px-4">
        <div class="container mx-auto flex items-center">
            <span class="font-bold mr-4 whitespace-nowrap">BREAKING:</span>
            <div class="marquee">
                <span>Youth TV launches new mentorship program • Tech summit registration now open • Interview with young entrepreneur airing tomorrow at 8PM</span>
            </div>
        </div>
    </div>

    <!-- Hero Section -->
    <?php if (!empty($featured_events)): ?>
    <section class="gradient-bg text-white">
        <div class="container mx-auto px-4 py-16">
            <div class="text-center mb-12">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">Featured Events</h1>
                <p class="text-xl opacity-90 max-w-3xl mx-auto">Join us for exciting events, workshops, and community gatherings designed for young voices and fresh perspectives.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-<?= min(count($featured_events), 3) ?> gap-8">
                <?php foreach ($featured_events as $event): ?>
                <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-lg p-6 card-hover">
                    <?php if ($event['image_url']): ?>
                    <img src="<?= htmlspecialchars($event['image_url']) ?>" alt="<?= htmlspecialchars($event['title']) ?>" class="w-full h-48 object-cover rounded-lg mb-4">
                    <?php endif; ?>
                    <h3 class="text-xl font-bold mb-2"><?= htmlspecialchars($event['title']) ?></h3>
                    <p class="text-sm opacity-90 mb-4"><?= htmlspecialchars($event['description']) ?></p>
                    <div class="flex items-center text-sm mb-2">
                        <i class="fas fa-calendar mr-2"></i>
                        <?= date('M d, Y', strtotime($event['event_date'])) ?>
                        <i class="fas fa-clock ml-4 mr-2"></i>
                        <?= date('h:i A', strtotime($event['event_time'])) ?>
                    </div>
                    <div class="flex items-center text-sm mb-4">
                        <i class="fas fa-map-marker-alt mr-2"></i>
                        <?= htmlspecialchars($event['location']) ?>
                    </div>
                    <button onclick="openEventModal(<?= htmlspecialchars(json_encode($event)) ?>)" class="w-full bg-white text-indigo-600 px-4 py-2 rounded-lg font-medium hover:bg-gray-100 transition-colors">
                        View Details
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Events Section -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <!-- Search and Filter -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                <form method="GET" class="flex flex-wrap gap-4 items-center">
                    <div class="flex-1 min-w-64">
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search events..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    <div>
                        <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="all" <?= $filter_status === 'all' ? 'selected' : '' ?>>All Events</option>
                            <option value="upcoming" <?= $filter_status === 'upcoming' ? 'selected' : '' ?>>Upcoming</option>
                            <option value="ongoing" <?= $filter_status === 'ongoing' ? 'selected' : '' ?>>Ongoing</option>
                            <option value="completed" <?= $filter_status === 'completed' ? 'selected' : '' ?>>Completed</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Events Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($events as $event): ?>
                <div class="bg-white rounded-lg shadow-sm card-hover overflow-hidden">
                    <?php if ($event['image_url']): ?>
                    <img src="<?= htmlspecialchars($event['image_url']) ?>" alt="<?= htmlspecialchars($event['title']) ?>" class="w-full h-48 object-cover">
                    <?php endif; ?>
                    
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                <?php
                                switch($event['status']) {
                                    case 'upcoming': echo 'bg-blue-100 text-blue-800'; break;
                                    case 'ongoing': echo 'bg-green-100 text-green-800 event-status'; break;
                                    case 'completed': echo 'bg-gray-100 text-gray-800'; break;
                                    case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                }
                                ?>">
                                <?= ucfirst($event['status']) ?>
                            </span>
                            <?php if ($event['featured']): ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <i class="fas fa-star mr-1"></i> Featured
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <h3 class="text-xl font-bold text-gray-900 mb-2"><?= htmlspecialchars($event['title']) ?></h3>
                        <p class="text-gray-600 mb-4"><?= htmlspecialchars(substr($event['description'], 0, 100)) ?>...</p>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fas fa-calendar mr-2"></i>
                                <?= date('M d, Y', strtotime($event['event_date'])) ?>
                                <i class="fas fa-clock ml-4 mr-2"></i>
                                <?= date('h:i A', strtotime($event['event_time'])) ?>
                            </div>
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fas fa-map-marker-alt mr-2"></i>
                                <?= htmlspecialchars($event['location']) ?>
                            </div>
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fas fa-dollar-sign mr-2"></i>
                                <?= htmlspecialchars($event['price']) ?>
                            </div>
                            <?php if ($event['max_attendees'] > 0): ?>
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fas fa-users mr-2"></i>
                                <?= $event['current_attendees'] ?>/<?= $event['max_attendees'] ?> attendees
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <button onclick="openEventModal(<?= htmlspecialchars(json_encode($event)) ?>)" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                            View Details
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (empty($events)): ?>
            <div class="text-center py-16">
                <i class="fas fa-calendar-times text-6xl text-gray-400 mb-4"></i>
                <h3 class="text-xl font-medium text-gray-600 mb-2">No events found</h3>
                <p class="text-gray-500">Try adjusting your search criteria or check back later for new events.</p>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Newsletter Section -->
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
    
    <!-- Footer - Same as index.html -->
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

    <!-- Event Details Modal -->
    <div id="eventModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-screen overflow-y-auto">
            <div id="eventModalContent">
                <!-- Content will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            
            mobileMenuButton.addEventListener('click', function() {
                mobileMenu.classList.toggle('open');
            });
            
            // Close mobile menu when clicking on links
            document.querySelectorAll('#mobile-menu a').forEach(link => {
                link.addEventListener('click', function() {
                    mobileMenu.classList.remove('open');
                });
            });
        });

        // Event modal functions
        function openEventModal(event) {
            const modal = document.getElementById('eventModal');
            const content = document.getElementById('eventModalContent');
            
            const statusClass = {
                'upcoming': 'bg-blue-100 text-blue-800',
                'ongoing': 'bg-green-100 text-green-800',
                'completed': 'bg-gray-100 text-gray-800',
                'cancelled': 'bg-red-100 text-red-800'
            };
            
            const eventDate = new Date(event.event_date + 'T' + event.event_time);
            const formattedDate = eventDate.toLocaleDateString('en-US', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            const formattedTime = eventDate.toLocaleTimeString('en-US', { 
                hour: 'numeric', 
                minute: '2-digit',
                hour12: true 
            });
            
            content.innerHTML = `
                <div class="relative">
                    ${event.image_url ? `<img src="${event.image_url}" alt="${event.title}" class="w-full h-64 object-cover">` : ''}
                    <button onclick="closeEventModal()" class="absolute top-4 right-4 bg-white bg-opacity-90 text-gray-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-opacity-100">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${statusClass[event.status]}">
                            ${event.status.charAt(0).toUpperCase() + event.status.slice(1)}
                        </span>
                        ${event.featured ? '<span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800"><i class="fas fa-star mr-1"></i> Featured</span>' : ''}
                    </div>
                    
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">${event.title}</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div class="space-y-3">
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-calendar mr-3 text-indigo-600"></i>
                                <span>${formattedDate}</span>
                            </div>
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-clock mr-3 text-indigo-600"></i>
                                <span>${formattedTime}</span>
                            </div>
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-map-marker-alt mr-3 text-indigo-600"></i>
                                <span>${event.location}</span>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-dollar-sign mr-3 text-indigo-600"></i>
                                <span>${event.price}</span>
                            </div>
                            ${event.max_attendees > 0 ? `
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-users mr-3 text-indigo-600"></i>
                                <span>${event.current_attendees}/${event.max_attendees} attendees</span>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">Description</h3>
                        <p class="text-gray-600 leading-relaxed">${event.description}</p>
                    </div>
                    
                    ${event.registration_link && event.status === 'upcoming' ? `
                    <div class="flex gap-3">
                        <a href="${event.registration_link}" target="_blank" class="flex-1 bg-indigo-600 text-white text-center px-6 py-3 rounded-lg hover:bg-indigo-700 transition-colors">
                            <i class="fas fa-external-link-alt mr-2"></i>Register Now
                        </a>
                        <button onclick="shareEvent('${event.title}', '${event.registration_link}')" class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-share-alt"></i>
                        </button>
                    </div>
                    ` : ''}
                </div>
            `;
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeEventModal() {
            const modal = document.getElementById('eventModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }

        function shareEvent(title, link) {
            if (navigator.share) {
                navigator.share({
                    title: title,
                    text: 'Check out this event!',
                    url: link
                });
            } else {
                // Fallback to copying link
                navigator.clipboard.writeText(link).then(() => {
                    alert('Event link copied to clipboard!');
                });
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('eventModal');
            if (event.target === modal) {
                closeEventModal();
            }
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeEventModal();
            }
        });

        // Newsletter subscription functionality
        const scriptURL = 'https://script.google.com/macros/s/AKfycbyxWduebhGeQz8zYmT72ZCiuhA2aHNQBQBgZLwQ9jQU1NR4cIibVwpTNMIQ0lIJQaYI/exec';
        let isSubscribing = true;

        // Toggle between subscribe/unsubscribe
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

        // Handle form submission
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
    </script>
</body>
</html>