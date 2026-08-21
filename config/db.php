<?php
/**
 * Database Connection
 * Pet Care System
 *
 * Provides a MySQLi connection ($conn) to the pet_care_system database.
 * Include this file at the top of any page that needs database access.
 *
 * Usage:
 *   require_once __DIR__ . '/../config/db.php';
 *   // $conn is now available
 */

// ── Database credentials ──────────────────────────────────────────────────────
define('DB_HOST',    'localhost');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_NAME',    'pet_care_system');
define('DB_CHARSET', 'utf8mb4');
// ─────────────────────────────────────────────────────────────────────────────

// Enable MySQLi to throw exceptions on errors (no raw error messages to users).
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset(DB_CHARSET);
} catch (mysqli_sql_exception $e) {
    // Log the real error server-side; show nothing sensitive to the user.
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(503);
    die('Unable to connect to the database. Please try again later.');
}
