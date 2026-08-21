<?php
/**
 * Shared Helper Functions
 * Pet Care System
 *
 * Reusable utility functions available across all pages.
 * Include via: require_once __DIR__ . '/../includes/functions.php';
 */

/**
 * Format a numeric price value as a Sri Lankan Rupee string.
 *
 * @param  float|int|string $amount  The numeric price value.
 * @return string                    Formatted string, e.g. "Rs. 3,500.00"
 */
function formatPrice($amount): string
{
    $numeric = is_numeric($amount) ? (float) $amount : 0.0;
    return 'Rs. ' . number_format($numeric, 2);
}

/**
 * Sanitize a string for safe HTML output.
 * Trims whitespace and applies htmlspecialchars with UTF-8 encoding.
 *
 * @param  string|null $value  The raw input string.
 * @return string              The sanitized string safe for HTML output.
 */
function sanitize(?string $value): string
{
    return htmlspecialchars(trim((string) $value), ENT_QUOTES, 'UTF-8');
}

/**
 * Truncate a string to a maximum number of characters, appending an ellipsis
 * if the original string exceeds the limit.
 *
 * @param  string $text   The input text.
 * @param  int    $limit  Maximum character count (default 120).
 * @return string
 */
function truncate(string $text, int $limit = 120): string
{
    $text = trim($text);
    if (mb_strlen($text) <= $limit) {
        return $text;
    }
    return mb_substr($text, 0, $limit) . '…';
}

/**
 * Check whether a service image file exists in the assets/images directory.
 * Returns the image path relative to the document root if found,
 * otherwise returns a fallback placeholder path.
 *
 * @param  string $imageFilename  Filename stored in services.image column.
 * @param  string $basePath       Document-root-relative base path.
 * @return string                 Path safe for use in <img src="...">.
 */
function resolveServiceImage(string $imageFilename, string $basePath = ''): string
{
    $filename    = basename($imageFilename); // prevent path traversal
    $relativeSrc = $basePath . 'assets/images/' . $filename;
    $absolutePath = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($relativeSrc, '/');

    if (!empty($filename) && file_exists($absolutePath)) {
        return $relativeSrc;
    }

    // Fallback placeholder served locally — no external dependency.
    return $basePath . 'assets/images/placeholder.svg';
}
