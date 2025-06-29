<?php
// Security headers to prevent inspection and attacks
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; connect-src 'self'");

// Disable error reporting in production
error_reporting(0);
ini_set('display_errors', 0);

// Include database connection and functions
try {
    if (file_exists('database_connection.php')) {
        include 'database_connection.php';
        
        // Get events from database ordered by priority
        $events = getAllEventsOrdered($pdo);
        
        // Get carousel configuration from database
        $carousel_config = getCarouselConfig($pdo);
        
    } else {
        throw new Exception('Database connection file not found');
    }
} catch (Exception $e) {
    // Don't expose error details to users
    $events = [];
    $carousel_config = [];
}

// Convert config array to usable format
$config = [];
foreach ($carousel_config as $setting => $value) {
    $config[$setting] = $value;
}

// Set default values if database config is empty
$auto_play = isset($config['auto_play']) ? $config['auto_play'] : 'true';
$interval = isset($config['interval']) ? (int)$config['interval'] : 5000;
$pause_on_hover = isset($config['pause_on_hover']) ? $config['pause_on_hover'] : 'true';
$show_indicators = isset($config['show_indicators']) ? $config['show_indicators'] : 'true';
$show_controls = isset($config['show_controls']) ? $config['show_controls'] : 'true';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>UMAK Fortem Ardeas Esports</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Poppins:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <link href="style.css" rel="stylesheet" />
    <style>
        /* Disable text selection and right-click */
        body {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        
        /* Hide elements from inspect */
        .security-hidden {
            display: none !important;
        }
    </style>
</head>
<body class="bg-gray-900 font-poppins" oncontextmenu="return false;" onselectstart="return false;" oncopy="return false;">
    <header class="navbar justify-center">
        <nav class="flex items-center space-x-8">
            <ul class="flex space-x-4">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="aboutpage.php" class="nav-link">About</a></li>
            </ul>
    
            <a href="index.php" class="logo-link">
                <img src="imgs/logoumakesports.png" alt="Logo" class="logoheader" />
            </a>
    
            <ul class="flex space-x-4">
                <li><a href="events.php" class="nav-link">Events</a></li>
                <li><a href="login.php" class="nav-link">Login</a></li>
            </ul>
        </nav>
    </header>
    <main class="p-8">
        <!-- Side-by-side layout: Cover photo on left, Carousel on right -->
        <div class="flex flex-col lg:flex-row gap-8 items-end">
            <!-- Left side: Cover photo with hover text -->
            <div class="lg:w-1/2">
                <section class="hero-section flex items-center justify-center">
                    <h2 class="bgtext">Join the Guild</h2>
                </section>
            </div>
            
            <!-- Right side: Carousel -->
            <div class="lg:w-1/2">
                <?php if (!empty($events)): ?>
                <div class="carousel-container">
                    <div class="carousel-wrapper">
                        <?php foreach ($events as $index => $event): ?>
                        <div class="carousel-slide <?php echo $index === 0 ? 'active' : ''; ?>">
                            <div class="card shadow-sm carousel-event-card">
                                <?php if (!empty($event['image_url'])): ?>
                                    <div class="card-background">
                                        <?php echo displayImage($event['image_url'], htmlspecialchars($event['title']), 'card-bg-image'); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="card-content">
                                    <div class="card-overlay">
                                        <div class="mb-2">
                                            <span class="px-2 py-1 rounded text-xs <?php 
                                                echo $event['status'] === 'ongoing' ? 'bg-green-600' : 
                                                    ($event['status'] === 'upcoming' ? 'bg-yellow-600' : 'bg-gray-600'); 
                                            ?>">
                                                <?php echo ucfirst($event['status']); ?>
                                            </span>
                                        </div>
                                        <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                                        <p class="card-text">Date: <?php echo htmlspecialchars($event['date']); ?></p>
                                        <p class="card-text">Location: <?php echo htmlspecialchars($event['location']); ?></p>
                                        <p class="card-text"><?php echo htmlspecialchars($event['description']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if ($show_controls === 'true'): ?>
                    <!-- Carousel Controls -->
                    <button class="carousel-control prev" onclick="changeSlide(-1)">❮</button>
                    <button class="carousel-control next" onclick="changeSlide(1)">❯</button>
                    <?php endif; ?>
                    
                    <?php if ($show_indicators === 'true' && count($events) > 1): ?>
                    <!-- Carousel Indicators -->
                    <div class="carousel-indicators">
                        <?php foreach ($events as $index => $event): ?>
                        <span class="indicator <?php echo $index === 0 ? 'active' : ''; ?>" onclick="currentSlide(<?php echo $index + 1; ?>)"></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-8">
                    <p class="text-gray-400">No events at the moment. Check back soon!</p>
                    <div class="mt-4">
                        <a href="admin_events.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Add Events</a>
                        <a href="check_events.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded ml-2">Check Database</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <section class="mt-8">
            <h3 class="text-2xl font-bold">Quick Stats</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <div class="card">
                    <h4 class="text-xl font-bold">Users</h4>
                    <p>1,234</p>
                </div>
                <div class="card">
                    <h4 class="text-xl font-bold">Events Hosted</h4>
                    <p><?php echo count($events); ?></p>
                </div>
                <div class="card">
                    <h4 class="text-xl font-bold">Org Partners</h4>
                    <p>12</p>
                </div>
            </div>
        </section>
    </main>
    <footer class="p-4 bg-gray-800 mt-8">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <p>2025 We strive. We fall. We rise.</p>
            <div class="flex space-x-4">
                <a href="#" class="nav-link">Privacy Policy</a>
                <a href="https://www.facebook.com/umakfortem" class="nav-link">Contact</a>
                <a href="#" class="nav-link">Terms</a>
            </div>
        </div>
    </footer>
    
    <script>
        let currentSlideIndex = 0;
        const slides = document.querySelectorAll('.carousel-slide');
        const indicators = document.querySelectorAll('.indicator');
        
        function showSlide(index) {
            // Hide all slides
            slides.forEach(slide => slide.classList.remove('active'));
            indicators.forEach(indicator => indicator.classList.remove('active'));
            
            // Show current slide
            if (slides[index]) {
                slides[index].classList.add('active');
                if (indicators[index]) {
                    indicators[index].classList.add('active');
                }
            }
        }
        
        function changeSlide(direction) {
            currentSlideIndex += direction;
            
            if (currentSlideIndex >= slides.length) {
                currentSlideIndex = 0;
            } else if (currentSlideIndex < 0) {
                currentSlideIndex = slides.length - 1;
            }
            
            showSlide(currentSlideIndex);
        }
        
        function currentSlide(index) {
            currentSlideIndex = index - 1;
            showSlide(currentSlideIndex);
        }
        
        // Auto-play functionality
        function autoPlay() {
            if (slides.length > 1) {
                changeSlide(1);
            }
        }
        
        // Start auto-play if enabled
        <?php if ($auto_play === 'true'): ?>
        let autoPlayInterval = setInterval(autoPlay, <?php echo $interval; ?>);
        <?php endif; ?>
        
        // Pause on hover if enabled
        <?php if ($pause_on_hover === 'true'): ?>
        const carouselContainer = document.querySelector('.carousel-container');
        
        if (carouselContainer) {
            carouselContainer.addEventListener('mouseenter', () => {
                <?php if ($auto_play === 'true'): ?>
                clearInterval(autoPlayInterval);
                <?php endif; ?>
            });
            
            carouselContainer.addEventListener('mouseleave', () => {
                <?php if ($auto_play === 'true'): ?>
                autoPlayInterval = setInterval(autoPlay, <?php echo $interval; ?>);
                <?php endif; ?>
            });
        }
        <?php endif; ?>
    </script>
    
    <script>
        // Security measures to prevent inspection
        (function() {
            'use strict';
            
            // Disable F12, Ctrl+Shift+I, Ctrl+U, Ctrl+Shift+C
            document.addEventListener('keydown', function(e) {
                if (e.key === 'F12' || 
                    (e.ctrlKey && e.shiftKey && e.key === 'I') ||
                    (e.ctrlKey && e.key === 'u') ||
                    (e.ctrlKey && e.shiftKey && e.key === 'C')) {
                    e.preventDefault();
                    return false;
                }
            });
            
            // Disable right-click context menu
            document.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                return false;
            });
            
            // Disable text selection
            document.addEventListener('selectstart', function(e) {
                e.preventDefault();
                return false;
            });
            
            // Disable copy
            document.addEventListener('copy', function(e) {
                e.preventDefault();
                return false;
            });
            
            // Disable drag and drop
            document.addEventListener('dragstart', function(e) {
                e.preventDefault();
                return false;
            });
            
            // Clear console on page load
            console.clear();
            
            // Override console methods
            console.log = function() {};
            console.info = function() {};
            console.warn = function() {};
            console.error = function() {};
            console.debug = function() {};
            
            // Disable developer tools detection
            setInterval(function() {
                const devtools = {
                    open: false,
                    orientation: null
                };
                
                const threshold = 160;
                
                if (window.outerHeight - window.innerHeight > threshold || 
                    window.outerWidth - window.innerWidth > threshold) {
                    devtools.open = true;
                    document.body.innerHTML = '<div style="text-align:center;padding:50px;color:white;">Access Denied</div>';
                }
            }, 1000);
            
        })();
    </script>
</body>
</html> 