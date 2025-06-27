<?php
// Include database connection and functions
include 'database_connection.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_event':
                $title = $_POST['title'] ?? '';
                $date = $_POST['date'] ?? '';
                $location = $_POST['location'] ?? '';
                $description = $_POST['description'] ?? '';
                $status = $_POST['status'] ?? 'upcoming';
                
                // Handle image upload with smart fallback
                $image_data = null;
                if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                    try {
                        $image_data = handleImageUploadSmart($_FILES['event_image']);
                    } catch (Exception $e) {
                        $error_message = "Image upload failed: " . $e->getMessage();
                        break;
                    }
                }
                
                if (addEventSmart($pdo, $title, $date, $location, $description, $image_data, $status)) {
                    $success_message = "Event added successfully!";
                } else {
                    $error_message = "Failed to add event.";
                }
                break;
                
            case 'update_event':
                $id = $_POST['event_id'] ?? 0;
                $title = $_POST['title'] ?? '';
                $date = $_POST['date'] ?? '';
                $location = $_POST['location'] ?? '';
                $description = $_POST['description'] ?? '';
                $status = $_POST['status'] ?? 'upcoming';
                
                // Handle image upload with smart fallback
                $image_data = null;
                if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                    try {
                        $image_data = handleImageUploadSmart($_FILES['event_image']);
                    } catch (Exception $e) {
                        $error_message = "Image upload failed: " . $e->getMessage();
                        break;
                    }
                }
                
                if (updateEventSmart($pdo, $id, $title, $date, $location, $description, $image_data, $status)) {
                    $success_message = "Event updated successfully!";
                } else {
                    $error_message = "Failed to update event.";
                }
                break;
                
            case 'delete_event':
                $id = $_POST['event_id'] ?? 0;
                if (deleteEvent($pdo, $id)) {
                    $success_message = "Event deleted successfully!";
                } else {
                    $error_message = "Failed to delete event.";
                }
                break;
        }
    }
}

// Get all events for display
$all_events = [];
try {
    $stmt = $pdo->query("SELECT * FROM events ORDER BY date ASC");
    $all_events = $stmt->fetchAll();
} catch (Exception $e) {
    $error_message = "Failed to load events: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management - Admin (Fallback)</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body class="bg-gray-900 font-poppins">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-white">Event Management (Smart Fallback)</h1>
            <a href="index.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Back to Home</a>
        </div>
        
        <?php if (isset($success_message)): ?>
        <div class="bg-green-600 text-white p-4 rounded mb-6">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
        <div class="bg-red-600 text-white p-4 rounded mb-6">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
        <?php endif; ?>
        
        <!-- Add New Event Form -->
        <div class="card mb-8">
            <div class="card-header">
                <h2 class="text-xl font-bold">Add New Event</h2>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="action" value="add_event">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Event Title</label>
                            <input type="text" name="title" class="w-full p-2 border rounded bg-gray-800 text-white" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Date</label>
                            <input type="date" name="date" class="w-full p-2 border rounded bg-gray-800 text-white" required>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Location</label>
                            <input type="text" name="location" class="w-full p-2 border rounded bg-gray-800 text-white" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Status</label>
                            <select name="status" class="w-full p-2 border rounded bg-gray-800 text-white">
                                <option value="upcoming">Upcoming</option>
                                <option value="ongoing">Ongoing</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Description</label>
                        <textarea name="description" class="w-full p-2 border rounded bg-gray-800 text-white" rows="3" required></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Event Image (Optional)</label>
                        <input type="file" name="event_image" accept="image/*" class="w-full p-2 border rounded bg-gray-800 text-white">
                        <p class="text-sm text-gray-400 mt-1">Accepted formats: JPG, PNG, GIF. Max size: 5MB. Will be automatically compressed.</p>
                    </div>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded">Add Event</button>
                </form>
            </div>
        </div>
        
        <!-- Current Events List -->
        <div class="card">
            <div class="card-header">
                <h2 class="text-xl font-bold">Current Events</h2>
            </div>
            <div class="card-body">
                <?php if (empty($all_events)): ?>
                <p class="text-gray-400 text-center py-8">No events found.</p>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-gray-700">
                                <th class="p-3">Image</th>
                                <th class="p-3">Title</th>
                                <th class="p-3">Date</th>
                                <th class="p-3">Location</th>
                                <th class="p-3">Status</th>
                                <th class="p-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_events as $event): ?>
                            <tr class="border-b border-gray-700 hover:bg-gray-800">
                                <td class="p-3">
                                    <?php if (!empty($event['image_url'])): ?>
                                        <?php echo displayImageSmart($event['image_url'], $event['title'], 'w-16 h-16 object-cover rounded'); ?>
                                    <?php else: ?>
                                        <div class="w-16 h-16 bg-gray-600 rounded flex items-center justify-center text-gray-400 text-xs">No Image</div>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3"><?php echo htmlspecialchars($event['title']); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($event['date']); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($event['location']); ?></td>
                                <td class="p-3">
                                    <span class="px-2 py-1 rounded text-xs <?php 
                                        echo $event['status'] === 'upcoming' ? 'bg-green-600' : 
                                            ($event['status'] === 'ongoing' ? 'bg-yellow-600' : 'bg-gray-600'); 
                                    ?>">
                                        <?php echo ucfirst($event['status']); ?>
                                    </span>
                                </td>
                                <td class="p-3">
                                    <div class="flex space-x-2">
                                        <button onclick="editEvent(<?php echo htmlspecialchars(json_encode($event)); ?>)" 
                                                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                                            Edit
                                        </button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this event?')">
                                            <input type="hidden" name="action" value="delete_event">
                                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        function editEvent(event) {
            // Simple edit functionality - you can expand this
            alert('Edit functionality for event: ' + event.title);
        }
    </script>
</body>
</html> 