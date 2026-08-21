<?php
// ============================================================
// Pet Care System - Session Authentication Guard
// University Web Application Development Project
// Member 1: Core Auth
//
// Usage: Add this at the very top of any admin-protected page:
//   require_once '../includes/auth.php';
//
// If the admin is not logged in, the visitor is redirected to
// the admin login page automatically.
// ============================================================

// Start the session only if it hasn't been started already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check whether a valid admin session exists
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    // Not authenticated — redirect to the login page
    // Path is relative from the admin/ directory where this is included
    header('Location: login.php');
    exit();
}
