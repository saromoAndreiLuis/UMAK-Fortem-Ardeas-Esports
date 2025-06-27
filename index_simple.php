<?php
// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
$events = [];
$carousel_config = [];
$error_message = '';

try {
    // Include database connection and functions
    if (file_exists('database_connection.php')) {
        include 'database_connection.php';
        
        // Get events from database
        $events = getUpcomingEvents($pdo);
        
        // Get carousel configuration from database
        $carousel_config = getCarouselConfig($pdo);
    } else {
        throw new Exception('Database connection file not found');
    }
} catch (Exception $e) {
    $error_message = "Database connection failed: " . $e->getMessage();
    
    // Fallback events data
    $events = [
        [
            'title' => 'Esports Tournament 2025',
            'date' => '2025-08-15',
            'location' => 'Cebu',
            'description' => 'Join us for an epic gaming showdown!',
            'image_url' => ''
        ],
        [
            'title' => 'Gaming Convention',
            'date' => '2025-09-10',
            'location' => 'Davao',
            'description' => 'Experience the latest in gaming technology!',
            'image_url' => ''
        ],
        [
            'title' => 'Pro League Finals',
            'date' => '2025-07-15',
            'location' => 'Manila',
            'description' => 'Witness the ultimate battle of champions!',
            'image_url' => ''
        ]
    ];
    
    // Fallback carousel configuration
    $carousel_config = [
        'auto_play' => 'true',
        'interval' => '5000',
        'pause_on_hover' => 'true',
        'show_indicators' => 'true',
        'show_controls' => 'true'
    ];
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
</head>
<body class="bg-gray-900 font-poppins">
    <?php if ($error_message): ?>
    <div class="bg-red-600 text-white p-4 text-center">
        <strong>Warning:</strong> <?php echo htmlspecialchars($error_message); ?>
        <br><small>Using fallback data. Please check your database connection.</small>
    </div>
    <?php endif; ?>

    <header class="navbar justify-center">
        <nav class="flex items-center space-x-8">
            <ul class="flex space-x-4">
                <li><a href="#" class="nav-link">Home</a></li>
                <li><a href="#" class="nav-link">About</a></li>
            </ul>
    
            <a href="#" class="logo-link">
                <img src="imgs/acadarena-logo.png" alt="Logo" class="logoheader" />
            </a>
    
            <ul class="flex space-x-4">
                <li><a href="#" class="nav-link">Events</a></li>
                <li><a href="admin_events.php" class="nav-link">Admin</a></li>
            </ul>
        </nav>
    </header>
    <main class="p-8">
        <section class="hero-section flex items-center justify-center">
            <h2 class="bgtext">Join the Guild</h2>
        </section>
        <section class="mt-8">
            <h3 class="text-2xl font-bold">Featured Events</h3>
            <div class="mt-4">
                <?php if (!empty($events)): ?>
                <div class="carousel-container">
                    <div class="carousel-wrapper">
                        <?php foreach ($events as $index => $event): ?>
                        <div class="carousel-slide <?php echo $index === 0 ? 'active' : ''; ?>">
                            <div class="card mb-4 shadow-sm">
                                <div class="card-body text-center">
                                    <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                                    <p class="card-text">Date: <?php echo htmlspecialchars($event['date']); ?></p>
                                    <p class="card-text">Location: <?php echo htmlspecialchars($event['location']); ?></p>
                                    <p class="card-text"><?php echo htmlspecialchars($event['description']); ?></p>
                                    <?php if (!empty($event['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($event['image_url']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="event-image">
                                    <?php endif; ?>
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
                    <p class="text-gray-400">No upcoming events at the moment. Check back soon!</p>
                </div>
                <?php endif; ?>
            </div>
        </section>
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
                <a href="#" class="nav-link">Contact</a>
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
</body>
</html> 