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
        
        // Get all events from database
        $events = getAllEventsOrdered($pdo);
        
    } else {
        throw new Exception('Database connection file not found');
    }
} catch (Exception $e) {
    // Don't expose error details to users
    $events = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Events - UMAK Fortem Ardeas Esports</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Poppins:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <link href="style.css" rel="stylesheet" />
    <style>
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            width: 100%;
            max-width: 100%;
        }
        
        .event-card {
            background-color: #243f63;
            border-radius: 0.5rem;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .event-image-container {
            width: 100%;
            height: 200px;
            overflow: hidden;
            position: relative;
        }
        
        .event-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .event-content {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .event-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #fff;
        }
        
        .event-date {
            color: #60a5fa;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .event-location {
            color: #9ca3af;
            margin-bottom: 1rem;
        }
        
        .event-description {
            color: #d1d5db;
            line-height: 1.6;
            flex-grow: 1;
        }
        
        .event-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }
        
        .status-ongoing {
            background-color: #059669;
            color: #fff;
        }
        
        .status-upcoming {
            background-color: #d97706;
            color: #fff;
        }
        
        .status-completed {
            background-color: #6b7280;
            color: #fff;
        }
        
        .no-events {
            text-align: center;
            padding: 3rem;
            color: #9ca3af;
        }
        
        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 2rem;
            color: #fff;
        }
        
        .events-count {
            text-align: center;
            color: #60a5fa;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }
        
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
                <li><a href="admin_events.php" class="nav-link">Login</a></li>
            </ul>
        </nav>
    </header>

    <main class="p-8">
        <h1 class="page-title">All Events</h1>
        
        <?php if (!empty($events)): ?>
            <div class="events-count">
                Showing <?php echo count($events); ?> event<?php echo count($events) !== 1 ? 's' : ''; ?>
            </div>
            
            <div class="events-grid">
                <?php foreach ($events as $event): ?>
                    <div class="event-card">
                        <?php if (!empty($event['image_url'])): ?>
                            <div class="event-image-container">
                                <?php echo displayImage($event['image_url'], htmlspecialchars($event['title']), 'event-image'); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="event-content">
                            <span class="event-status status-<?php echo $event['status']; ?>">
                                <?php echo ucfirst($event['status']); ?>
                            </span>
                            
                            <h3 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                            
                            <p class="event-date">
                                📅 <?php echo htmlspecialchars($event['date']); ?>
                            </p>
                            
                            <p class="event-location">
                                📍 <?php echo htmlspecialchars($event['location']); ?>
                            </p>
                            
                            <p class="event-description">
                                <?php echo htmlspecialchars($event['description']); ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-events">
                <h2 class="text-2xl font-bold mb-4">No Events Found</h2>
                <p class="mb-4">There are no events available at the moment.</p>
                <div class="space-x-4">
                    <a href="admin_events.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                        Add Events
                    </a>
                    <a href="check_events.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">
                        Check Database
                    </a>
                </div>
            </div>
        <?php endif; ?>
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