<?php
// ============================================================
// Pet Care System - Shared Helper Functions
// University Web Application Development Project
// ============================================================

/**
 * Sanitize a string for safe HTML output (XSS prevention).
 * Primary function — used by Member 1 code.
 */
function sanitizeInput(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Alias of sanitizeInput() — used by Member 2 (service-details.php).
 */
function sanitize(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Format a float as a Sri Lankan Rupee price.
 * Example: formatPrice(3500) → "Rs. 3,500.00"
 */
function formatPrice(float $amount): string
{
    return 'Rs. ' . number_format($amount, 2);
}

/**
 * Redirect to a URL and stop execution.
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit();
}

/**
 * Return a sanitized GET parameter or a default value.
 */
function getQueryParam(string $key, string $default = ''): string
{
    return isset($_GET[$key]) ? sanitizeInput($_GET[$key]) : $default;
}

/**
 * Display a session flash message if set, then clear it.
 * $key can be 'success', 'error', or 'info'.
 */
function showFlash(string $key): void
{
    if (!empty($_SESSION[$key])) {
        $type    = in_array($key, ['success', 'error', 'info', 'warning']) ? $key : 'info';
        $message = sanitizeInput($_SESSION[$key]);
        echo '<div class="alert alert-' . $type . '">' . $message . '</div>';
        unset($_SESSION[$key]);
    }
}

/**
 * Check whether an admin session is active.
 */
function isAdminLoggedIn(): bool
{
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Truncate a string to $length characters with ellipsis.
 */
function truncateText(string $text, int $length = 100): string
{
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . '…';
}

/**
 * Resolve a service image path for display.
 * Used by service-details.php (Member 2).
 *
 * @param  string $image     The image filename stored in DB (e.g. "dog-grooming.jpg")
 * @param  string $basePath  Path prefix from current directory to root (e.g. "" or "../")
 * @return string            Full relative path to the image src
 */
function resolveServiceImage(string $image, string $basePath = ''): string
{
    if (empty(trim($image))) {
        return $basePath . 'assets/images/placeholder.svg';
    }
    $imagePath = $basePath . 'assets/images/' . basename($image);
    return $imagePath;
}
