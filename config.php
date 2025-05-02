<?php
// Database Configuration
define('DB_TYPE', 'sqlite');
define('DB_FILE', __DIR__ . '/database/rentalbis.db');

// GPS API Configuration (Replace with actual API credentials)
define('GPS_API_URL', 'https://api.example.com/gps/v1/track');
define('GPS_API_KEY', 'your_api_key_here');

// Upload Directory Configuration
define('UPLOAD_DIR', 'uploads/vehicles/');
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes

// Allowed file types for vehicle images
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
