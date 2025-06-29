<?php


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
    $error_message = "Database connection failed: " . $e->getMessage();
    $events = [];
    $carousel_config = [];
    $debug_info = "Using fallback data due to database error.";
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
</head>
<body class="bg-gray-900 font-poppins">
    <?php if (isset($error_message)): ?>
    <div class="bg-red-600 text-white p-4 text-center">
        <strong>Warning:</strong> <?php echo htmlspecialchars($error_message); ?>
        <br><small>Using fallback data. Please check your database connection.</small>
    </div>
    <?php endif; ?>

    <!-- Debug information (remove this in production) -->
    <div class="bg-blue-600 text-white p-2 text-center text-sm">
        <?php echo htmlspecialchars($debug_info); ?> 
        <a href="check_events.php" class="underline">Check Events</a> | 
        <a href="admin_events.php" class="underline">Admin Panel</a>
    </div>

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
</html>