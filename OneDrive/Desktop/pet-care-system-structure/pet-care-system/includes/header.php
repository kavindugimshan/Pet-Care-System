<?php
// ============================================================
// Pet Care System - Common Header
// University Web Application Development Project
// Member 1: Core Auth
//
// Include at the top of every public-facing page:
//   require_once '/path/to/includes/header.php';
//
// The $pageTitle variable can be set before including this file
// to customise the browser tab title:
//   $pageTitle = 'Book a Service';
//   require_once '../includes/header.php';
// ============================================================

// Determine a base URL that works regardless of calling directory.
// Assumes the server is running from the project root.
$base = '/';

// Determine the current script path for nav highlighting
$currentPage = basename($_SERVER['PHP_SELF']);

// Use a custom title if set by the calling page, otherwise default
$siteTitle = 'Pet Care System';
$pageTitle  = isset($pageTitle) ? $pageTitle . ' | ' . $siteTitle : $siteTitle;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Pet Care System - Professional grooming, veterinary and boarding services for your beloved pets.">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Common stylesheet (all members use this) -->
    <link rel="stylesheet" href="<?php echo $base; ?>assets/css/common.css">
</head>
<body>

<!-- ΓöÇΓöÇ Main Navigation ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ -->
<header class="site-header">
    <nav class="navbar">
        <a href="<?php echo $base; ?>index.php" class="nav-brand">
            ≡ƒÉ╛ Pet Care System
        </a>

        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <ul class="nav-links" id="navLinks">
            <li>
                <a href="<?php echo $base; ?>index.php"
                   class="<?php echo ($currentPage === 'index.php') ? 'active' : ''; ?>">
                    Home
                </a>
            </li>
            <li>
                <a href="<?php echo $base; ?>index.php#services"
                   class="<?php echo ($currentPage === 'service-details.php') ? 'active' : ''; ?>">
                    Services
                </a>
            </li>
            <li>
                <a href="<?php echo $base; ?>admin/login.php"
                   class="<?php echo (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? 'active' : ''; ?>">
                    Admin
                </a>
            </li>
        </ul>
    </nav>
</header>

<!-- ΓöÇΓöÇ Page Content Starts Here ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ -->
<main class="main-content">

<script>
    // Mobile navigation toggle
    document.getElementById('navToggle').addEventListener('click', function () {
        document.getElementById('navLinks').classList.toggle('open');
    });
</script>
