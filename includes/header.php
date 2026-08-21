<?php
/**
 * Shared Header Include
 * Pet Care System
 *
 * Outputs the HTML <head> block and the site navigation bar.
 *
 * Variables that can be set before including this file:
 *   $pageTitle  (string) — used in <title> tag, defaults to "Pet Care System"
 *   $basePath   (string) — relative path prefix for assets/links (e.g. '../')
 *                          defaults to '' (for files in the project root)
 */

$pageTitle = isset($pageTitle) ? trim($pageTitle) : '';
$basePath  = isset($basePath)  ? $basePath         : '';
$siteTitle = 'Pet Care System';
$fullTitle = $pageTitle !== '' ? $pageTitle . ' | ' . $siteTitle : $siteTitle;

// Determine the active nav link based on the current script.
$currentScript = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Pet Care System — professional grooming, veterinary and boarding services for your beloved pets.">
    <title><?= htmlspecialchars($fullTitle, ENT_QUOTES, 'UTF-8') ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= $basePath ?>assets/css/common.css">

    <?php if (isset($extraCss)): ?>
        <?php foreach ((array)$extraCss as $css): ?>
            <link rel="stylesheet" href="<?= htmlspecialchars($basePath . $css, ENT_QUOTES, 'UTF-8') ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>

<!-- ═══════════════════════════════════════════════════════════ HEADER -->
<header class="site-header" role="banner">
    <div class="container header-inner">

        <!-- Brand / Logo -->
        <a href="<?= $basePath ?>index.php" class="site-logo" aria-label="Pet Care System — Home">
            <span class="logo-icon" aria-hidden="true">🐾</span>
            <span class="logo-text">Pet<strong>Care</strong></span>
        </a>

        <!-- Primary Navigation -->
        <nav class="site-nav" role="navigation" aria-label="Main navigation">
            <ul class="nav-list" role="list">
                <li>
                    <a href="<?= $basePath ?>index.php"
                       class="nav-link <?= ($currentScript === 'index.php') ? 'nav-link--active' : '' ?>">
                        Services
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>admin/login.php"
                       class="nav-link <?= ($currentScript === 'login.php') ? 'nav-link--active' : '' ?>">
                        Admin
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Mobile hamburger toggle (toggled by catalog.js) -->
        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation" aria-expanded="false">
            <span class="hamburger-bar"></span>
            <span class="hamburger-bar"></span>
            <span class="hamburger-bar"></span>
        </button>

    </div>
</header>
<!-- ══════════════════════════════════════════════════════════ /HEADER -->

<main id="main-content" role="main">
