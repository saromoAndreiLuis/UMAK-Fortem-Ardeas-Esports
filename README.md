# UMAK Fortem Ardeas Esports - Event Carousel System

A simple and customizable event carousel system built with PHP, Bootstrap, and custom CSS.

## Files Structure

- `index.php` - Main homepage with carousel
- `index.html` - Original HTML version (kept for reference)
- `carousel_data.php` - Event data and carousel functions
- `admin_events.php` - Admin interface for managing events
- `style.css` - Custom styling
- `README.md` - This documentation

## How to Customize Events

### Method 1: Edit `carousel_data.php` (Recommended for developers)

1. Open `carousel_data.php`
2. Modify the `$events` array to add/edit/remove events:

```php
$events = [
    [
        'id' => 1,
        'title' => 'Your Event Title',
        'date' => '2025-01-15',
        'location' => 'Your Location',
        'description' => 'Event description here',
        'image' => 'imgs/your-event-image.jpg', // Optional
        'status' => 'upcoming' // upcoming, ongoing, completed
    ],
    // Add more events...
];
```

3. Customize carousel settings in `$carousel_config`:

```php
$carousel_config = [
    'auto_play' => true,
    'interval' => 5000, // 5 seconds between slides
    'pause_on_hover' => true,
    'show_indicators' => true,
    'show_controls' => true
];
```

### Method 2: Use Admin Interface (User-friendly)

1. Navigate to `admin_events.php` in your browser
2. Fill out the form to add new events
3. View and manage existing events
4. Delete events as needed

## Carousel Features

- **Automatic Slideshow**: Events cycle automatically
- **Manual Navigation**: Click dots or arrows to navigate
- **Responsive Design**: Works on all screen sizes
- **Custom Styling**: Matches your dark theme with cyan accents
- **Hover Effects**: Interactive elements with smooth transitions
- **PHP Integration**: Easy to connect with databases

## Future Database Integration

To connect with a database, modify `carousel_data.php`:

```php
// Example database connection
$pdo = new PDO("mysql:host=localhost;dbname=your_db", "username", "password");

// Fetch events from database
$stmt = $pdo->query("SELECT * FROM events WHERE status = 'upcoming' ORDER BY date ASC");
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

## Customization Options

### Styling
- Edit `style.css` to change colors, animations, and layout
- Modify carousel indicators, controls, and card styling
- Add custom animations and transitions

### Functionality
- Change slide interval in `$carousel_config`
- Enable/disable auto-play, pause on hover
- Show/hide indicators and controls
- Add more event fields (price, registration link, etc.)

### Layout
- Modify card structure in `generateCarouselItems()` function
- Change number of cards per slide
- Add different layouts for different screen sizes

## Browser Compatibility

- Chrome, Firefox, Safari, Edge (modern versions)
- Mobile responsive
- Works with Bootstrap 4.5.2

## Security Notes

- Always use `htmlspecialchars()` for output (already implemented)
- Validate and sanitize input data
- Use prepared statements for database queries
- Implement proper authentication for admin access

## Support

For questions or issues, check the code comments or modify the configuration variables in `carousel_data.php`. 