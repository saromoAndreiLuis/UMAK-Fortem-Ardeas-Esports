<?php
// Include database connection and functions
include 'database_connection.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_event':
                $title = trim($_POST['title'] ?? '');
                $date = $_POST['date'] ?? '';
                $location = trim($_POST['location'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $status = $_POST['status'] ?? 'upcoming';
                
                // Validation array to collect errors
                $validation_errors = [];
                
                // Validate title
                if (empty($title)) {
                    $validation_errors[] = "Event title is required.";
                }
                
                // Validate date
                if (empty($date)) {
                    $validation_errors[] = "Event date is required.";
                } else {
                    $event_date = new DateTime($date);
                    $today = new DateTime();
                    $max_date = new DateTime();
                    $max_date->add(new DateInterval('P10Y')); // 10 years from now
                    
                    if ($event_date < $today) {
                        $validation_errors[] = "Event date cannot be earlier than today.";
                    }
                    if ($event_date > $max_date) {
                        $validation_errors[] = "Event date cannot be more than 10 years in the future.";
                    }
                }
                
                // Validate location
                if (empty($location)) {
                    $validation_errors[] = "Event location is required.";
                }
                
                // Validate description
                if (empty($description)) {
                    $validation_errors[] = "Event description is required.";
                }
                
                // Validate status - admin cannot choose completed for new events
                if ($status === 'completed') {
                    $validation_errors[] = "Cannot create a new event with 'completed' status.";
                }
                
                // Validate image - now required
                $image_data = null;
                if (!isset($_FILES['event_image']) || $_FILES['event_image']['error'] === UPLOAD_ERR_NO_FILE) {
                    $validation_errors[] = "Event image is required.";
                } else {
                    try {
                        $image_data = handleImageUpload($_FILES['event_image']);
                    } catch (Exception $e) {
                        $validation_errors[] = "Image upload failed: " . $e->getMessage();
                    }
                }
                
                // If there are validation errors, display them and stop processing
                if (!empty($validation_errors)) {
                    $error_message = "Validation errors:<br>" . implode("<br>", $validation_errors);
                } else {
                    // All validations passed, add the event
                    if (addEvent($pdo, $title, $date, $location, $description, $status, $image_data)) {
                        $success_message = "Event added successfully!";
                    } else {
                        $error_message = "Failed to add event.";
                    }
                }
                break;
                
            case 'update_event':
                $id = $_POST['event_id'] ?? 0;
                $title = trim($_POST['title'] ?? '');
                $date = $_POST['date'] ?? '';
                $location = trim($_POST['location'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $status = $_POST['status'] ?? 'upcoming';
                
                // Validation array to collect errors
                $validation_errors = [];
                
                // Validate title
                if (empty($title)) {
                    $validation_errors[] = "Event title is required.";
                }
                
                // Validate date
                if (empty($date)) {
                    $validation_errors[] = "Event date is required.";
                } else {
                    $event_date = new DateTime($date);
                    $today = new DateTime();
                    $max_date = new DateTime();
                    $max_date->add(new DateInterval('P10Y')); // 10 years from now
                    
                    if ($event_date < $today) {
                        $validation_errors[] = "Event date cannot be earlier than today.";
                    }
                    if ($event_date > $max_date) {
                        $validation_errors[] = "Event date cannot be more than 10 years in the future.";
                    }
                }
                
                // Validate location
                if (empty($location)) {
                    $validation_errors[] = "Event location is required.";
                }
                
                // Validate description
                if (empty($description)) {
                    $validation_errors[] = "Event description is required.";
                }
                
                // Handle image upload (optional for updates)
                $image_data = null;
                if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                    try {
                        $image_data = handleImageUpload($_FILES['event_image']);
                    } catch (Exception $e) {
                        $validation_errors[] = "Image upload failed: " . $e->getMessage();
                    }
                }
                
                // If there are validation errors, display them and stop processing
                if (!empty($validation_errors)) {
                    $error_message = "Validation errors: " . implode("<br>", $validation_errors);
                } else {
                    // All validations passed, update the event
                    if (updateEvent($pdo, $id, $title, $date, $location, $description, $status, $image_data)) {
                        $success_message = "Event updated successfully!";
                    } else {
                        $error_message = "Failed to update event.";
                    }
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

// Get current date for min/max date validation
$today = date('Y-m-d');
$max_date = date('Y-m-d', strtotime('+10 years'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body class="bg-gray-900 font-poppins">
    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-white">Event Management</h1>
            <a href="index.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-center">Back to Home</a>
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
                <form method="POST" enctype="multipart/form-data" class="space-y-4" id="addEventForm" onsubmit="return validateForm()">
                    <input type="hidden" name="action" value="add_event">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Event Title <span class="text-red-500">*</span></label>
                            <input type="text" name="title" id="title" class="w-full p-2 border rounded bg-gray-800 text-white" required 
                                   value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                                   onblur="validateField('title', 'Title is required')">
                            <div id="title-error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Date <span class="text-red-500">*</span></label>
                            <input type="date" name="date" id="date" class="w-full p-2 border rounded bg-gray-800 text-white" required
                                   min="<?php echo $today; ?>" max="<?php echo $max_date; ?>"
                                   value="<?php echo htmlspecialchars($_POST['date'] ?? ''); ?>"
                                   onblur="validateDate()">
                            <div id="date-error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Location <span class="text-red-500">*</span></label>
                            <input type="text" name="location" id="location" class="w-full p-2 border rounded bg-gray-800 text-white" required
                                   value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>"
                                   onblur="validateField('location', 'Location is required')">
                            <div id="location-error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Status <span class="text-red-500">*</span></label>
                            <select name="status" id="status" class="w-full p-2 border rounded bg-gray-800 text-white" required>
                                <option value="upcoming" <?php echo ($_POST['status'] ?? 'upcoming') === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                                <option value="ongoing" <?php echo ($_POST['status'] ?? '') === 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                                <option value="completed" disabled>Completed (Not available for new events)</option>
                            </select>
                            <p class="text-sm text-gray-400 mt-1">Completed status is not available for new events</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Description <span class="text-red-500">*</span></label>
                        <textarea name="description" id="description" class="w-full p-2 border rounded bg-gray-800 text-white" rows="3" required
                                  onblur="validateField('description', 'Description is required')"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                        <div id="description-error" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Event Image <span class="text-red-500">*</span></label>
                        <input type="file" name="event_image" id="event_image" accept="image/*" class="w-full p-2 border rounded bg-gray-800 text-white" required
                               onchange="validateImage()">
                        <div id="image-error" class="text-red-500 text-sm mt-1 hidden"></div>
                        <div id="file-info" class="text-green-500 text-sm mt-1 hidden"></div>
                        <p class="text-sm text-gray-400 mt-1">Accepted formats: JPG, PNG, GIF. Max size: 5MB. <strong>Required for new events.</strong></p>
                    </div>
                    <button type="submit" class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded">Add Event</button>
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
                                <th class="p-3 hidden sm:table-cell">Image</th>
                                <th class="p-3">Title</th>
                                <th class="p-3 hidden md:table-cell">Date</th>
                                <th class="p-3 hidden lg:table-cell">Location</th>
                                <th class="p-3">Status</th>
                                <th class="p-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_events as $event): ?>
                            <tr class="border-b border-gray-700 hover:bg-gray-800">
                                <td class="p-3 hidden sm:table-cell">
                                    <?php if (!empty($event['image_url'])): ?>
                                        <?php echo displayImage($event['image_url'], $event['title'], 'w-16 h-16 object-cover rounded'); ?>
                                    <?php else: ?>
                                        <div class="w-16 h-16 bg-gray-600 rounded flex items-center justify-center text-gray-400 text-xs">No Image</div>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3">
                                    <div class="font-medium"><?php echo htmlspecialchars($event['title']); ?></div>
                                    <div class="text-sm text-gray-400 md:hidden">
                                        <?php echo htmlspecialchars($event['date']); ?> • <?php echo htmlspecialchars($event['location']); ?>
                                    </div>
                                </td>
                                <td class="p-3 hidden md:table-cell"><?php echo htmlspecialchars($event['date']); ?></td>
                                <td class="p-3 hidden lg:table-cell"><?php echo htmlspecialchars($event['location']); ?></td>
                                <td class="p-3">
                                    <span class="px-2 py-1 rounded text-xs <?php 
                                        echo $event['status'] === 'upcoming' ? 'bg-green-600' : 
                                            ($event['status'] === 'ongoing' ? 'bg-yellow-600' : 'bg-gray-600'); 
                                    ?>">
                                        <?php echo ucfirst($event['status']); ?>
                                    </span>
                                </td>
                                <td class="p-3">
                                    <div class="flex flex-col sm:flex-row gap-2">
                                        <button onclick="editEvent(<?php echo htmlspecialchars(json_encode($event)); ?>)" 
                                                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                                            Edit
                                        </button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this event?')">
                                            <input type="hidden" name="action" value="delete_event">
                                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                            <button type="submit" class="w-full sm:w-auto bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">Delete</button>
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
        
        <!-- Edit Event Modal -->
        <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
            <div class="bg-gray-800 p-4 sm:p-6 rounded-lg w-full max-w-md mx-auto max-h-screen overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold">Edit Event</h3>
                    <button onclick="closeEditModal()" class="text-gray-400 hover:text-white text-2xl">&times;</button>
                </div>
                <form method="POST" enctype="multipart/form-data" id="editForm">
                    <input type="hidden" name="action" value="update_event">
                    <input type="hidden" name="event_id" id="edit_event_id">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Event Title</label>
                            <input type="text" name="title" id="edit_title" class="w-full p-2 border rounded bg-gray-700 text-white" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Date</label>
                            <input type="date" name="date" id="edit_date" class="w-full p-2 border rounded bg-gray-700 text-white" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Location</label>
                            <input type="text" name="location" id="edit_location" class="w-full p-2 border rounded bg-gray-700 text-white" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Status</label>
                            <select name="status" id="edit_status" class="w-full p-2 border rounded bg-gray-700 text-white">
                                <option value="upcoming">Upcoming</option>
                                <option value="ongoing">Ongoing</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Description</label>
                            <textarea name="description" id="edit_description" class="w-full p-2 border rounded bg-gray-700 text-white" rows="3" required></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Current Image</label>
                            <div id="current_image" class="mb-2"></div>
                            <label class="block text-sm font-medium mb-2">New Image (Optional)</label>
                            <input type="file" name="event_image" accept="image/*" class="w-full p-2 border rounded bg-gray-700 text-white">
                            <p class="text-sm text-gray-400 mt-1">Leave empty to keep current image. Accepted formats: JPG, PNG, GIF. Max size: 5MB</p>
                        </div>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-4 mt-6">
                        <button type="submit" class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Update Event</button>
                        <button type="button" onclick="closeEditModal()" class="w-full sm:w-auto bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Validation functions
        function validateField(fieldId, errorMessage) {
            const field = document.getElementById(fieldId);
            const errorDiv = document.getElementById(fieldId + '-error');
            const value = field.value.trim();
            
            if (value === '') {
                errorDiv.textContent = errorMessage;
                errorDiv.classList.remove('hidden');
                field.classList.add('border-red-500');
                return false;
            } else {
                errorDiv.classList.add('hidden');
                field.classList.remove('border-red-500');
                return true;
            }
        }
        
        function validateDate() {
            const dateField = document.getElementById('date');
            const errorDiv = document.getElementById('date-error');
            const selectedDate = new Date(dateField.value);
            const today = new Date();
            const maxDate = new Date();
            maxDate.setFullYear(today.getFullYear() + 10); // 10 years from now
            
            // Reset time to compare only dates
            today.setHours(0, 0, 0, 0);
            maxDate.setHours(0, 0, 0, 0);
            selectedDate.setHours(0, 0, 0, 0);
            
            if (dateField.value === '') {
                errorDiv.textContent = 'Date is required';
                errorDiv.classList.remove('hidden');
                dateField.classList.add('border-red-500');
                return false;
            } else if (selectedDate < today) {
                errorDiv.textContent = 'Event date cannot be earlier than today';
                errorDiv.classList.remove('hidden');
                dateField.classList.add('border-red-500');
                return false;
            } else if (selectedDate > maxDate) {
                errorDiv.textContent = 'Event date cannot be more than 10 years in the future';
                errorDiv.classList.remove('hidden');
                dateField.classList.add('border-red-500');
                return false;
            } else {
                errorDiv.classList.add('hidden');
                dateField.classList.remove('border-red-500');
                return true;
            }
        }
        
        function validateImage() {
            const imageField = document.getElementById('event_image');
            const errorDiv = document.getElementById('image-error');
            const file = imageField.files[0];
            
            if (!file) {
                errorDiv.textContent = 'Event image is required';
                errorDiv.classList.remove('hidden');
                imageField.classList.add('border-red-500');
                return false;
            }
            
            // Check file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                errorDiv.textContent = 'Invalid file type. Only JPG, PNG, and GIF are allowed';
                errorDiv.classList.remove('hidden');
                imageField.classList.add('border-red-500');
                return false;
            }
            
            // Check file size (5MB)
            const maxSize = 5 * 1024 * 1024; // 5MB in bytes
            if (file.size > maxSize) {
                errorDiv.textContent = 'File size too large. Maximum size is 5MB';
                errorDiv.classList.remove('hidden');
                imageField.classList.add('border-red-500');
                return false;
            }
            
            errorDiv.classList.add('hidden');
            imageField.classList.remove('border-red-500');
            return true;
        }
        
        function validateForm() {
            let isValid = true;
            
            // Validate all required fields
            isValid = validateField('title', 'Title is required') && isValid;
            isValid = validateDate() && isValid;
            isValid = validateField('location', 'Location is required') && isValid;
            isValid = validateField('description', 'Description is required') && isValid;
            isValid = validateImage() && isValid;
            
            // Validate status (should not be completed for new events)
            const statusField = document.getElementById('status');
            if (statusField.value === 'completed') {
                alert('Cannot create a new event with completed status');
                return false;
            }
            
            if (!isValid) {
                alert('Please fix the validation errors before submitting');
                return false;
            }
            
            return true;
        }
        
        // Clear validation errors when user starts typing
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = ['title', 'location', 'description'];
            inputs.forEach(inputId => {
                const input = document.getElementById(inputId);
                if (input) {
                    input.addEventListener('input', function() {
                        const errorDiv = document.getElementById(inputId + '-error');
                        if (errorDiv) {
                            errorDiv.classList.add('hidden');
                        }
                        this.classList.remove('border-red-500');
                    });
                }
            });
            
            // Clear date validation on change
            const dateField = document.getElementById('date');
            if (dateField) {
                dateField.addEventListener('change', function() {
                    const errorDiv = document.getElementById('date-error');
                    if (errorDiv) {
                        errorDiv.classList.add('hidden');
                    }
                    this.classList.remove('border-red-500');
                });
            }
            
            // Clear image validation on change
            const imageField = document.getElementById('event_image');
            if (imageField) {
                imageField.addEventListener('change', function() {
                    const errorDiv = document.getElementById('image-error');
                    if (errorDiv) {
                        errorDiv.classList.add('hidden');
                    }
                    this.classList.remove('border-red-500');
                    
                    // Show selected file name
                    const file = this.files[0];
                    if (file) {
                        const fileInfo = document.getElementById('file-info');
                        if (fileInfo) {
                            fileInfo.textContent = `Selected: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
                            fileInfo.classList.remove('hidden');
                        }
                    }
                });
            }
            
            // Show validation errors on page load if form was submitted with errors
            <?php if (isset($error_message) && !empty($_POST)): ?>
            // Re-validate all fields to show errors
            setTimeout(function() {
                validateField('title', 'Title is required');
                validateDate();
                validateField('location', 'Location is required');
                validateField('description', 'Description is required');
                validateImage();
            }, 100);
            <?php endif; ?>
        });

        function editEvent(event) {
            document.getElementById('edit_event_id').value = event.id;
            document.getElementById('edit_title').value = event.title;
            document.getElementById('edit_date').value = event.date;
            document.getElementById('edit_location').value = event.location;
            document.getElementById('edit_status').value = event.status;
            document.getElementById('edit_description').value = event.description;
            
            // Display current image if exists
            const currentImageDiv = document.getElementById('current_image');
            if (event.image_url) {
                // Convert binary data to base64 for display
                const base64 = btoa(String.fromCharCode.apply(null, new Uint8Array(event.image_url)));
                currentImageDiv.innerHTML = `<img src="data:image/jpeg;base64,${base64}" alt="Current Image" class="w-32 h-32 object-cover rounded">`;
            } else {
                currentImageDiv.innerHTML = '<div class="w-32 h-32 bg-gray-600 rounded flex items-center justify-center text-gray-400 text-xs">No Image</div>';
            }
            
            document.getElementById('editModal').classList.remove('hidden');
            document.getElementById('editModal').classList.add('flex');
            preventBodyScroll();
        }
        
        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            document.getElementById('editModal').classList.remove('flex');
            allowBodyScroll();
        }
        
        // Close modal when clicking outside
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeEditModal();
            }
        });
        
        // Prevent body scroll when modal is open
        function preventBodyScroll() {
            document.body.style.overflow = 'hidden';
        }
        
        function allowBodyScroll() {
            document.body.style.overflow = 'auto';
        }
    </script>
</body>
</html> 