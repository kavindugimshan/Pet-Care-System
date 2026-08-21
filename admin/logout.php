<?php
// ============================================================
// Pet Care System - Admin Logout
// University Web Application Development Project
// Member 1: Core Auth
//
// Destroys the admin session and redirects to the login page.
// No HTML output is produced — purely a redirect.
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Unset all session variables
$_SESSION = [];

// Optionally delete the session cookie from the browser
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destroy the session on the server side
session_destroy();

// Redirect to the login page
header('Location: login.php');
exit();
