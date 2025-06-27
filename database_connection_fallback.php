<?php
// Fallback Database Connection with Automatic File Storage
// This automatically switches to file storage if BLOB upload fails

$host = 'localhost';
$dbname = 'umak_esports';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    die();
}

// Create uploads directory
$uploads_dir = 'uploads/';
if (!file_exists($uploads_dir)) {
    mkdir($uploads_dir, 0755, true);
}

// Smart image upload function that tries BLOB first, falls back to file storage
function handleImageUploadSmart($file) {
    global $uploads_dir;
    
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return ['type' => 'none', 'data' => null];
    }
    
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
    
    // Try to compress and store as BLOB first
    if (extension_loaded('gd')) {
        try {
            $compressed_data = compressImage($file['tmp_name'], $file_type);
            if ($compressed_data !== false) {
                return ['type' => 'blob', 'data' => $compressed_data];
            }
        } catch (Exception $e) {
            // Fall back to file storage
        }
    }
    
    // Fallback: Store as file
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $uploads_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['type' => 'file', 'data' => $filepath];
    } else {
        throw new Exception('Failed to save uploaded file.');
    }
}

// Function to add event with smart image handling
function addEventSmart($pdo, $title, $date, $location, $description, $status = 'upcoming', $image_data = null) {
    if ($image_data && $image_data['type'] === 'blob') {
        $sql = "INSERT INTO events (title, date, location, description, status, image_url) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$title, $date, $location, $description, $status, $image_data['data']]);
    } else {
        $image_path = $image_data ? $image_data['data'] : null;
        $sql = "INSERT INTO events (title, date, location, description, status, image_url) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$title, $date, $location, $description, $status, $image_path]);
    }
}

// Function to update event with smart image handling
function updateEventSmart($pdo, $id, $title, $date, $location, $description, $status, $image_data = null) {
    if ($image_data && $image_data['type'] === 'blob') {
        // Update with new BLOB image
        $sql = "UPDATE events SET title = ?, date = ?, location = ?, description = ?, status = ?, image_url = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$title, $date, $location, $description, $status, $image_data['data'], $id]);
    } else {
        // Update with file path or no image change
        $image_path = $image_data ? $image_data['data'] : null;
        
        if ($image_path !== null) {
            // Update with new image path
            $sql = "UPDATE events SET title = ?, date = ?, location = ?, description = ?, status = ?, image_url = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$title, $date, $location, $description, $status, $image_path, $id]);
        } else {
            // Update without changing image
            $sql = "UPDATE events SET title = ?, date = ?, location = ?, description = ?, status = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$title, $date, $location, $description, $status, $id]);
        }
    }
}

// Function to display image (handles both BLOB and file paths)
function displayImageSmart($image_data, $alt = 'Event Image', $class = 'event-image') {
    if (!$image_data) return '';
    
    // Check if it's a file path
    if (is_string($image_data) && file_exists($image_data)) {
        return "<img src='$image_data' alt='$alt' class='$class'>";
    }
    
    // Check if it's BLOB data
    if (is_string($image_data) && strlen($image_data) > 100) {
        // Likely BLOB data, try to display as base64
        try {
            $base64 = base64_encode($image_data);
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_buffer($finfo, $image_data);
            finfo_close($finfo);
            
            if (!$mime_type || !str_starts_with($mime_type, 'image/')) {
                $mime_type = 'image/jpeg'; // fallback
            }
            
            return "<img src='data:$mime_type;base64,$base64' alt='$alt' class='$class'>";
        } catch (Exception $e) {
            return '';
        }
    }
    
    return '';
}

// Image compression function (same as before)
function compressImage($file_path, $mime_type) {
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
            imagejpeg($destination, null, 85);
            break;
        case 'image/png':
            imagepng($destination, null, 6);
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

// Other functions remain the same
function getUpcomingEvents($pdo) {
    $sql = "SELECT * FROM events WHERE status = 'upcoming' AND date >= CURDATE() ORDER BY date ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

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

function deleteEvent($pdo, $id) {
    // Get image data before deleting
    $stmt = $pdo->query("SELECT image_url FROM events WHERE id = $id");
    $event = $stmt->fetch();
    
    // Delete image file if it exists
    if ($event && $event['image_url'] && file_exists($event['image_url'])) {
        unlink($event['image_url']);
    }
    
    $sql = "DELETE FROM events WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$id]);
}
?> 