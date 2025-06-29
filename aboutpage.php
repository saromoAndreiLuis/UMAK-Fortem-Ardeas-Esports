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

try {
    if (file_exists('database_connection.php')) {
        include 'database_connection.php';
        
        // Get events from database ordered by priority
        $events = getAllEventsOrdered($pdo);
        
        // Get carousel configuration from database
        $carousel_config = getCarouselConfig($pdo);
        
        // Debug information (remove this in production)
        $debug_info = "Database connected. Found " . count($events) . " events total.";
        
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
?>

<!DOCTYPE html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>About UMAK Fortem Ardeas Esports</title>
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
                <li><a href="admin_events.php" class="nav-link">Admin</a></li>
            </ul>
        </nav>
    </header>

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