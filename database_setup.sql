-- Database Setup for UMAK Fortem Ardeas Esports Events Carousel
-- Run these SQL statements in your MySQL/PostgreSQL database

-- Create the database (if it doesn't exist)
CREATE DATABASE IF NOT EXISTS umak_esports;
USE umak_esports;

-- Create events table
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    date DATE NOT NULL,
    location VARCHAR(255) NOT NULL,
    description TEXT,
    image_url VARCHAR(500),
    status ENUM('upcoming', 'ongoing', 'completed') DEFAULT 'upcoming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample events data
INSERT INTO events (title, date, location, description, image_url, status) VALUES
('Esports Tournament 2025', '2025-08-15', 'Cebu', 'Join us for an epic gaming showdown! Experience intense competition and amazing prizes.', 'imgs/event1.jpg', 'upcoming'),
('Gaming Convention', '2025-09-10', 'Davao', 'Experience the latest in gaming technology! Meet fellow gamers and discover new games.', 'imgs/event2.jpg', 'upcoming'),
('Pro League Finals', '2025-07-15', 'Manila', 'Witness the ultimate battle of champions! The best players compete for glory.', 'imgs/event3.jpg', 'upcoming'),
('Student Gaming Championship', '2025-10-20', 'Quezon City', 'Exclusive tournament for students. Show your skills and win scholarships!', 'imgs/event4.jpg', 'upcoming'),
('Esports Workshop', '2025-06-05', 'Makati', 'Learn from professional gamers. Improve your skills and strategies.', 'imgs/event5.jpg', 'upcoming'),
('Gaming Expo 2025', '2025-11-30', 'Cebu', 'The biggest gaming event of the year. Try new games and meet developers.', 'imgs/event6.jpg', 'upcoming');

-- Create carousel configuration table (optional - for dynamic settings)
CREATE TABLE IF NOT EXISTS carousel_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_name VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default carousel configuration
INSERT INTO carousel_config (setting_name, setting_value, description) VALUES
('auto_play', 'true', 'Enable automatic slideshow'),
('interval', '5000', 'Time between slides in milliseconds'),
('pause_on_hover', 'true', 'Pause slideshow when mouse hovers over carousel'),
('show_indicators', 'true', 'Show navigation dots'),
('show_controls', 'true', 'Show previous/next buttons');

-- Create users table (for future admin functionality)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'moderator', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Insert sample admin user (password: admin123)
INSERT INTO users (username, email, password_hash, role) VALUES
('admin', 'admin@umak-esports.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Create event registrations table (for future functionality)
CREATE TABLE IF NOT EXISTS event_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('registered', 'confirmed', 'cancelled') DEFAULT 'registered',
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX idx_events_date ON events(date);
CREATE INDEX idx_events_status ON events(status);
CREATE INDEX idx_events_location ON events(location);

-- Sample queries for the carousel system:

-- Get all upcoming events for carousel
-- SELECT * FROM events WHERE status = 'upcoming' AND date >= CURDATE() ORDER BY date ASC;

-- Get carousel configuration
-- SELECT setting_name, setting_value FROM carousel_config;

-- Get event with registration count
-- SELECT e.*, COUNT(er.id) as registration_count 
-- FROM events e 
-- LEFT JOIN event_registrations er ON e.id = er.event_id 
-- WHERE e.status = 'upcoming' 
-- GROUP BY e.id 
-- ORDER BY e.date ASC; 