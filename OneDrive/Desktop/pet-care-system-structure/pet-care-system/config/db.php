<?php
// ============================================================
// Pet Care System - Database Connection
// University Web Application Development Project
// Member 1: Core Auth
//
// Usage: require_once '/path/to/config/db.php';
//        Then use $conn for all queries.
// ============================================================


$host        = 'localhost';
$db_user     = 'root';
$db_password = 'Awzplocha#2003';
$db_name     = 'pet_care_system';


// Create the connection
$conn = new mysqli($host, $db_user, $db_password, $db_name);

// Check for connection errors
if ($conn->connect_error) {
    // Log the detailed error server-side; show a generic message to the user
    error_log('Database connection failed: ' . $conn->connect_error);
    die('A database error occurred. Please try again later.');
}

// Set the character set to utf8mb4 (supports full Unicode including emoji)
if (!$conn->set_charset('utf8mb4')) {
    error_log('Failed to set charset utf8mb4: ' . $conn->error);
}
