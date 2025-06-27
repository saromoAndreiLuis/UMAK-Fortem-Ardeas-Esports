<?php
// Alternative approach: Store images as files instead of BLOB
// This avoids MySQL max_allowed_packet issues

// Database connection configuration
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

// Create uploads directory if it doesn't exist
$uploads_dir = 'uploads/';
if (!file_exists($uploads_dir)) {
    mkdir($uploads_dir, 0755, true);
}

// Function to handle image upload (stores as file)
function handleImageUploadFile($file) {
    global $uploads_dir;
    
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return null;
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
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $uploads_dir . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to save uploaded file.');
    }
    
    return $filepath;
}

// Function to add event with file path
function addEventFile($pdo, $title, $date, $location, $description, $status = 'upcoming', $image_path = null) {
    $sql = "INSERT INTO events (title, date, location, description, status, image_url) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$title, $date, $location, $description, $status, $image_path]);
}

// Function to display image from file
function displayImageFile($image_path, $alt = 'Event Image', $class = 'event-image') {
    if ($image_path && file_exists($image_path)) {
        return "<img src='$image_path' alt='$alt' class='$class'>";
    }
    return '';
}

// Function to delete image file
function deleteImageFile($image_path) {
    if ($image_path && file_exists($image_path)) {
        unlink($image_path);
    }
}

// Usage example:
// $image_path = handleImageUploadFile($_FILES['event_image']);
// addEventFile($pdo, $title, $date, $location, $description, $status, $image_path);
?> 