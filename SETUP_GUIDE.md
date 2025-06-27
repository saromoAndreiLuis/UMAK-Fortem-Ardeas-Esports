# 🚀 Setup Guide - Database-Driven Carousel System

## Prerequisites
- XAMPP installed and running
- MySQL/MariaDB database
- PHP 7.4 or higher

## Step 1: Database Setup

1. **Open phpMyAdmin** (http://localhost/phpmyadmin)
2. **Create Database**: Click "New" and create a database named `umak_esports`
3. **Import SQL**: 
   - Click on the `umak_esports` database
   - Go to "Import" tab
   - Choose the `database_setup.sql` file
   - Click "Go" to execute

## Step 2: Configure Database Connection

1. **Edit `database_connection.php`**
2. **Update credentials** (if different from default):
   ```php
   $host = 'localhost';
   $dbname = 'umak_esports';
   $username = 'root';  // Your MySQL username
   $password = '';      // Your MySQL password
   ```

## Step 3: Test the System

1. **Test Database Connection**:
   - Visit: `http://localhost/lis/database_connection.php`
   - You should see "Database connected successfully!" and sample data

2. **View Main Page**:
   - Visit: `http://localhost/lis/index.php`
   - The carousel should display events from the database

3. **Access Admin Panel**:
   - Visit: `http://localhost/lis/admin_events.php`
   - Add, edit, or delete events

## Step 4: Add Your Own Events

### Method 1: Admin Interface (Recommended)
1. Go to `http://localhost/lis/admin_events.php`
2. Fill out the "Add New Event" form
3. Click "Add Event"

### Method 2: Direct SQL
```sql
INSERT INTO events (title, date, location, description, image_url, status) 
VALUES ('Your Event', '2025-01-15', 'Your Location', 'Description', 'imgs/event.jpg', 'upcoming');
```

## Step 5: Customize Carousel Settings

### Via Database:
```sql
UPDATE carousel_config SET setting_value = '3000' WHERE setting_name = 'interval';
UPDATE carousel_config SET setting_value = 'false' WHERE setting_name = 'auto_play';
```

### Available Settings:
- `auto_play`: true/false (enable automatic slideshow)
- `interval`: milliseconds between slides (default: 5000)
- `pause_on_hover`: true/false (pause on mouse hover)
- `show_indicators`: true/false (show navigation dots)
- `show_controls`: true/false (show arrow buttons)

## Troubleshooting

### Database Connection Issues:
- Check if MySQL is running in XAMPP Control Panel
- Verify database name, username, and password
- Ensure the `umak_esports` database exists

### Carousel Not Working:
- Check browser console for JavaScript errors
- Verify that events exist in the database
- Ensure PHP is properly configured

### Admin Panel Issues:
- Check file permissions
- Verify database functions are working
- Look for PHP error messages

## File Structure
```
lis/
├── index.php              # Main page with carousel
├── admin_events.php       # Admin interface
├── database_connection.php # Database functions
├── database_setup.sql     # Database structure
├── style.css              # Custom styling
├── SETUP_GUIDE.md         # This guide
└── imgs/                  # Event images folder
```

## Features Working Now:
✅ **Dynamic Events**: Carousel pulls from database  
✅ **Admin Interface**: Add/edit/delete events  
✅ **Auto-play**: Configurable slideshow timing  
✅ **Responsive Design**: Works on all devices  
✅ **Image Support**: Event images with hover effects  
✅ **Status Management**: Upcoming/Ongoing/Completed events  

## Next Steps:
- Add user authentication for admin panel
- Implement event registration system
- Add image upload functionality
- Create event categories/tags
- Add search and filtering

## Support:
If you encounter issues, check:
1. XAMPP error logs
2. Browser developer console
3. PHP error reporting
4. Database connection status 