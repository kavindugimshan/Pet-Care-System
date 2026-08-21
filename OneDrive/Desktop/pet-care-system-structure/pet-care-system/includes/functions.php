<?php
// ============================================================
// Pet Care System - Shared Helper Functions
// University Web Application Development Project
// Member 1: Core Auth
//
// Usage: require_once '/path/to/includes/functions.php';
// ============================================================

/**
 * Sanitize user input to prevent XSS when output in HTML.
 * Use on any user-supplied string before echoing it to the page.
 *
 * @param  string $input  The raw input string.
 * @return string         HTML-safe string.
 */
function sanitizeInput(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Format a numeric value as a Sri Lankan Rupee price string.
 * Example: formatPrice(3500) ΓåÆ "Rs. 3,500.00"
 *
 * @param  float  $amount  The price amount.
 * @return string          Formatted price string.
 */
function formatPrice(float $amount): string
{
    return 'Rs. ' . number_format($amount, 2);
}

/**
 * Redirect to a given URL and stop script execution.
 * Supports relative paths from the document root.
 *
 * @param  string $url  The destination URL or path.
 * @return void
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit();
}

/**
 * Return a query-string parameter value safely, or a default.
 *
 * @param  string $key      The $_GET key to read.
 * @param  string $default  Value to return if the key is absent.
 * @return string
 */
function getQueryParam(string $key, string $default = ''): string
{
    return isset($_GET[$key]) ? sanitizeInput($_GET[$key]) : $default;
}

/**
 * Display a session flash message if one is set, then clear it.
 * Accepts 'success', 'error', or 'info' as the message type.
 *
 * @param  string $key  The session key that holds the flash message.
 * @return void
 */
function showFlash(string $key): void
{
    if (!empty($_SESSION[$key])) {
        $type    = ($key === 'success') ? 'success' : (($key === 'error') ? 'error' : 'info');
        $message = sanitizeInput($_SESSION[$key]);
        echo '<div class="alert alert-' . $type . '">' . $message . '</div>';
        unset($_SESSION[$key]);
    }
}

/**
 * Check whether the current admin session is active.
 *
 * @return bool
 */
function isAdminLoggedIn(): bool
{
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Truncate a string to a maximum length and append ellipsis if needed.
 *
 * @param  string $text    The input string.
 * @param  int    $length  Maximum number of characters.
 * @return string
 */
function truncateText(string $text, int $length = 100): string
{
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . 'ΓÇª';
}
