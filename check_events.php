<?php
// Test file to check database events
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Events Check</h1>";

try {
    // Include database connection
    include 'database_connection.php';
    
    echo "<h2>Database Connection Status</h2>";
    echo "<p style='color: green;'>✅ Database connected successfully!</p>";
    
    // Check if events table exists
    echo "<h2>Events Table Check</h2>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'events'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Events table exists</p>";
    } else {
        echo "<p style='color: red;'>❌ Events table does not exist</p>";
        echo "<p>Please run the database_setup.sql file first.</p>";
        exit;
    }
    
    // Get all events (not just upcoming)
    echo "<h2>All Events in Database</h2>";
    $stmt = $pdo->query("SELECT * FROM events ORDER BY date ASC");
    $all_events = $stmt->fetchAll();
    
    if (empty($all_events)) {
        echo "<p style='color: orange;'>⚠️ No events found in database</p>";
        echo "<p>You need to add events first. You can:</p>";
        echo "<ul>";
        echo "<li>Use the admin panel: <a href='admin_events.php'>admin_events.php</a></li>";
        echo "<li>Or run the SQL commands from database_setup.sql</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: green;'>✅ Found " . count($all_events) . " events in database</p>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Title</th><th>Date</th><th>Location</th><th>Status</th></tr>";
        foreach ($all_events as $event) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($event['id']) . "</td>";
            echo "<td>" . htmlspecialchars($event['title']) . "</td>";
            echo "<td>" . htmlspecialchars($event['date']) . "</td>";
            echo "<td>" . htmlspecialchars($event['location']) . "</td>";
            echo "<td>" . htmlspecialchars($event['status']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test the getUpcomingEvents function
    echo "<h2>Upcoming Events (Function Test)</h2>";
    $upcoming_events = getUpcomingEvents($pdo);
    
    if (empty($upcoming_events)) {
        echo "<p style='color: orange;'>⚠️ No upcoming events found</p>";
        echo "<p>This might be because:</p>";
        echo "<ul>";
        echo "<li>All events are in the past</li>";
        echo "<li>All events have status other than 'upcoming'</li>";
        echo "<li>No events exist</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: green;'>✅ Found " . count($upcoming_events) . " upcoming events</p>";
        foreach ($upcoming_events as $event) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
            echo "<h3>" . htmlspecialchars($event['title']) . "</h3>";
            echo "<p><strong>Date:</strong> " . htmlspecialchars($event['date']) . "</p>";
            echo "<p><strong>Location:</strong> " . htmlspecialchars($event['location']) . "</p>";
            echo "<p><strong>Description:</strong> " . htmlspecialchars($event['description']) . "</p>";
            echo "<p><strong>Status:</strong> " . htmlspecialchars($event['status']) . "</p>";
            if (!empty($event['image_url'])) {
                echo "<p><strong>Image:</strong></p>";
                echo displayImage($event['image_url'], htmlspecialchars($event['title']), 'w-32 h-32 object-cover rounded');
            }
            echo "</div>";
        }
    }
    
    // Test carousel configuration
    echo "<h2>Carousel Configuration</h2>";
    $carousel_config = getCarouselConfig($pdo);
    
    if (empty($carousel_config)) {
        echo "<p style='color: orange;'>⚠️ No carousel configuration found</p>";
    } else {
        echo "<p style='color: green;'>✅ Carousel configuration loaded</p>";
        echo "<ul>";
        foreach ($carousel_config as $setting => $value) {
            echo "<li><strong>" . htmlspecialchars($setting) . ":</strong> " . htmlspecialchars($value) . "</li>";
        }
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<h2>Quick Actions</h2>";
echo "<p><a href='index.php'>View Main Page</a></p>";
echo "<p><a href='index_simple.php'>View Simplified Page</a></p>";
echo "<p><a href='admin_events.php'>Admin Panel</a></p>";
?> 