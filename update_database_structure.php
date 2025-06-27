<?php
// Script to update database structure for image uploads
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Structure Update</h1>";

try {
    // Include database connection
    include 'database_connection.php';
    
    echo "<p style='color: green;'>✅ Database connected successfully!</p>";
    
    // Check current column type
    echo "<h2>Current Database Structure</h2>";
    $stmt = $pdo->query("DESCRIBE events");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check if image_url column is already LONGBLOB
    $image_url_column = null;
    foreach ($columns as $column) {
        if ($column['Field'] === 'image_url') {
            $image_url_column = $column;
            break;
        }
    }
    
    if ($image_url_column) {
        echo "<h2>Image URL Column Status</h2>";
        echo "<p>Current type: <strong>" . htmlspecialchars($image_url_column['Type']) . "</strong></p>";
        
        if (strpos(strtolower($image_url_column['Type']), 'longblob') !== false) {
            echo "<p style='color: green;'>✅ Image URL column is already LONGBLOB. No changes needed.</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Image URL column needs to be updated to LONGBLOB.</p>";
            echo "<p>You need to run this SQL command in phpMyAdmin:</p>";
            echo "<pre style='background: #f0f0f0; padding: 10px; border-radius: 5px;'>";
            echo "ALTER TABLE events MODIFY COLUMN image_url LONGBLOB;";
            echo "</pre>";
            echo "<p><strong>Warning:</strong> This will clear any existing image URLs in the database.</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Image URL column not found. Please check your database structure.</p>";
    }
    
    // Show sample events to check current data
    echo "<h2>Current Events Data</h2>";
    $stmt = $pdo->query("SELECT id, title, image_url FROM events LIMIT 5");
    $events = $stmt->fetchAll();
    
    if (empty($events)) {
        echo "<p>No events found in database.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Title</th><th>Image URL Type</th></tr>";
        foreach ($events as $event) {
            $image_type = is_null($event['image_url']) ? 'NULL' : 
                         (is_string($event['image_url']) ? 'String (' . strlen($event['image_url']) . ' chars)' : 'Binary data');
            echo "<tr>";
            echo "<td>" . htmlspecialchars($event['id']) . "</td>";
            echo "<td>" . htmlspecialchars($event['title']) . "</td>";
            echo "<td>" . htmlspecialchars($image_type) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>If the image_url column is not LONGBLOB, run the ALTER TABLE command in phpMyAdmin</li>";
echo "<li>Test the admin panel: <a href='admin_events.php'>admin_events.php</a></li>";
echo "<li>Try uploading an image to see if it works</li>";
echo "<li>Check the main page: <a href='index.php'>index.php</a></li>";
echo "</ol>";
?>