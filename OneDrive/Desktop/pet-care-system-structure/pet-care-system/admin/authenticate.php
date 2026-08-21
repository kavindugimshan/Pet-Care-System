<?php
// ============================================================
// Pet Care System - Admin Authentication Handler
// University Web Application Development Project
// Member 1: Core Auth
//
// Accepts POST from admin/login.php only.
// Uses a prepared statement + password_verify() ΓÇö never plain text.
// ============================================================

// Start the session before anything else
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

// Pull in the database connection and helper functions
require_once '../config/db.php';
require_once '../includes/functions.php';

// Trim inputs (do NOT sanitize yet ΓÇö we compare hash, not raw string in DB)
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

// Basic presence check ΓÇö avoid hitting the DB with obviously empty fields
if (empty($username) || empty($password)) {
    redirect('login.php?error=invalid');
}

// Fetch the admin record by username using a prepared statement
$stmt = $conn->prepare('SELECT id, username, password FROM admins WHERE username = ? LIMIT 1');
if (!$stmt) {
    error_log('Prepare failed in authenticate.php: ' . $conn->error);
    redirect('login.php?error=invalid');
}

$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$admin  = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Verify the password against the stored bcrypt hash
// Using password_verify() ΓÇö never compare plain text
if (!$admin || !password_verify($password, $admin['password'])) {
    // Do not reveal whether the username or the password was wrong
    redirect('login.php?error=invalid');
}

// ΓöÇΓöÇ Successful authentication ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ
// Regenerate session ID to prevent session fixation attacks
session_regenerate_id(true);

// Store only necessary admin info in the session
$_SESSION['admin_id']       = $admin['id'];
$_SESSION['admin_username'] = $admin['username'];

// Send the admin to the dashboard
redirect('dashboard.php');
