<?php
// Database connection configuration
$host = 'localhost';
$dbname = 'umak_esports';
$username = 'root';  // Change this to your database username
$password = '';      // Change this to your database password

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // echo "Database connected successfully!";
    
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    die();
}

// Function to get all upcoming events
function getUpcomingEvents($pdo) {
    $sql = "SELECT * FROM events WHERE status = 'upcoming' AND date >= CURDATE() ORDER BY date ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Function to get all events ordered by priority (ongoing, upcoming, completed)
function getAllEventsOrdered($pdo) {
    $sql = "SELECT * FROM events ORDER BY 
            CASE 
                WHEN status = 'ongoing' THEN 1
                WHEN status = 'upcoming' THEN 2
                WHEN status = 'completed' THEN 3
            END,
            date ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Function to get carousel configuration
function getCarouselConfig($pdo) {
    $sql = "SELECT setting_name, setting_value FROM carousel_config";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    $config = [];
    while ($row = $stmt->fetch()) {
        $config[$row['setting_name']] = $row['setting_value'];
    }
    return $config;
}

// Function to add new event with image upload
function addEvent($pdo, $title, $date, $location, $description, $status = 'upcoming', $image_url = null,) {
    $sql = "INSERT INTO events (title, date, location, description, status, image_url) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$title, $date, $location, $description, $status, $image_url]);
}

// Function to update event with image upload
function updateEvent($pdo, $id, $title, $date, $location, $description, $status, $image_data = null,) {
    if ($image_data !== null) {
        // Update with new image
        $sql = "UPDATE events SET title = ?, date = ?, location = ?, description = ?, status = ?, image_url = ?, WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$title, $date, $location, $description, $image_data, $status, $id]);
    } else {
        // Update without changing image
        $sql = "UPDATE events SET title = ?, date = ?, location = ?, description = ?, status = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$title, $date, $location, $description, $status, $id]);
    }
}

// Function to delete event
function deleteEvent($pdo, $id) {
    $sql = "DELETE FROM events WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$id]);
}

// Function to get event image
function getEventImage($pdo, $event_id) {
    $sql = "SELECT image_url FROM events WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$event_id]);
    $result = $stmt->fetch();
    return $result ? $result['image_url'] : null;
}

// Function to handle image upload
function handleImageUpload($file) {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return null;
    }
    
    // Check if file was uploaded successfully
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload failed with error code: ' . $file['error']);
    }
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $file_type = mime_content_type($file['tmp_name']);
    
    if (!in_array($file_type, $allowed_types)) {
        throw new Exception('Invalid file type. Only JPG, PNG, and GIF are allowed.');
    }
    
    // Check file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('File size too large. Maximum size is 5MB.');
    }
    
    // Compress and resize image to reduce database size
    $image_data = compressImage($file['tmp_name'], $file_type);
    if ($image_data === false) {
        throw new Exception('Failed to process uploaded image.');
    }
    
    return $image_data;
}

// Function to compress and resize images
function compressImage($file_path, $mime_type) {
    // Get image info
    $image_info = getimagesize($file_path);
    if ($image_info === false) {
        return false;
    }
    
    $width = $image_info[0];
    $height = $image_info[1];
    
    // Calculate new dimensions (max 800x600)
    $max_width = 800;
    $max_height = 600;
    
    if ($width > $max_width || $height > $max_height) {
        $ratio = min($max_width / $width, $max_height / $height);
        $new_width = round($width * $ratio);
        $new_height = round($height * $ratio);
    } else {
        $new_width = $width;
        $new_height = $height;
    }
    
    // Create image resource based on type
    switch ($mime_type) {
        case 'image/jpeg':
        case 'image/jpg':
            $source = imagecreatefromjpeg($file_path);
            break;
        case 'image/png':
            $source = imagecreatefrompng($file_path);
            break;
        case 'image/gif':
            $source = imagecreatefromgif($file_path);
            break;
        default:
            return false;
    }
    
    if (!$source) {
        return false;
    }
    
    // Create new image with new dimensions
    $destination = imagecreatetruecolor($new_width, $new_height);
    
    // Preserve transparency for PNG and GIF
    if ($mime_type === 'image/png' || $mime_type === 'image/gif') {
        imagealphablending($destination, false);
        imagesavealpha($destination, true);
        $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
        imagefilledrectangle($destination, 0, 0, $new_width, $new_height, $transparent);
    }
    
    // Resize image
    imagecopyresampled($destination, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    
    // Output to buffer
    ob_start();
    switch ($mime_type) {
        case 'image/jpeg':
        case 'image/jpg':
            imagejpeg($destination, null, 85); // 85% quality
            break;
        case 'image/png':
            imagepng($destination, null, 6); // Compression level 6
            break;
        case 'image/gif':
            imagegif($destination);
            break;
    }
    $image_data = ob_get_contents();
    ob_end_clean();
    
    // Clean up
    imagedestroy($source);
    imagedestroy($destination);
    
    return $image_data;
}

// Function to display image
function displayImage($image_data, $alt = 'Event Image', $class = 'event-image') {
    if ($image_data) {
        $base64 = base64_encode($image_data);
        $mime_type = 'image/jpeg'; // Default, you might want to detect this
        
        // Try to detect MIME type from image data
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_buffer($finfo, $image_data);
        finfo_close($finfo);
        
        return "<img src='data:$mime_type;base64,$base64' alt='$alt' class='$class'>";
    }
    return '';
}

// // Example usage:
// echo "<h2>Sample Database Operations</h2>";

// // Get upcoming events
// $events = getUpcomingEvents($pdo);
// echo "<h3>Upcoming Events:</h3>";
// foreach ($events as $event) {
//     echo "<p><strong>{$event['title']}</strong> - {$event['date']} at {$event['location']}</p>";
// }

// // Get carousel configuration
// $config = getCarouselConfig($pdo);
// echo "<h3>Carousel Configuration:</h3>";
// foreach ($config as $setting => $value) {
//     echo "<p><strong>{$setting}:</strong> {$value}</p>";
// }

// Example: Add a new event
// addEvent($pdo, 'New Gaming Event', '2025-12-25', 'Makati', 'A new exciting gaming event!', 'imgs/new-event.jpg', 'upcoming');

// Example: Update an event
// updateEvent($pdo, 1, 'Updated Tournament Title', '2025-08-20', 'Cebu', 'Updated description', 'imgs/updated-event.jpg', 'upcoming');

// Example: Delete an event
// deleteEvent($pdo, 1);
?> 