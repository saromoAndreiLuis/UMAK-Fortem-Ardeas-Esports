<?php
// Carousel Configuration
$carousel_config = [
    'auto_play' => true,
    'interval' => 5000, // 5 seconds
    'pause_on_hover' => true,
    'show_indicators' => true,
    'show_controls' => false
];

// Event Data - Easy to modify and add new events
$events = [
    [
        'id' => 1,
        'title' => 'Esports Tournament 2025',
        'date' => '2025-08-15',
        'location' => 'Cebu',
        'description' => 'Join us for an epic gaming showdown!',
        'image' => 'imgs/logobg.jpg', // Optional: Add event images
        'status' => 'upcoming'
    ],
    [
        'id' => 2,
        'title' => 'Gaming Convention',
        'date' => '2025-09-10',
        'location' => 'Davao',
        'description' => 'Experience the latest in gaming technology!',
        'image' => 'imgs/event2.jpg',
        'status' => 'upcoming'
    ],
    [
        'id' => 3,
        'title' => 'Pro League Finals',
        'date' => '2025-07-15',
        'location' => 'Manila',
        'description' => 'Witness the ultimate battle of champions!',
        'image' => 'imgs/event3.jpg',
        'status' => 'upcoming'
    ]
];

// Function to generate carousel indicators
function generateCarouselIndicators($events) {
    $indicators = '';
    foreach ($events as $index => $event) {
        $active_class = ($index === 0) ? 'active' : '';
        $indicators .= '<li data-target="#eventCarousel" data-slide-to="' . $index . '" class="' . $active_class . '"></li>';
    }
    return $indicators;
}

// Function to generate carousel items
function generateCarouselItems($events) {
    $items = '';
    foreach ($events as $index => $event) {
        $active_class = ($index === 0) ? 'active' : '';
        
        $items .= '
        <div class="carousel-item ' . $active_class . '">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-body text-center">
                            <h5 class="card-title">' . htmlspecialchars($event['title']) . '</h5>
                            <p class="card-text">Date: ' . htmlspecialchars($event['date']) . '</p>
                            <p class="card-text">Location: ' . htmlspecialchars($event['location']) . '</p>
                            <p class="card-text">' . htmlspecialchars($event['description']) . '</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
    }
    return $items;
}

// Function to get carousel configuration as JSON for JavaScript
function getCarouselConfig($config) {
    return json_encode($config);
}
?> 