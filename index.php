<?php
// ============================================================
// Pet Care System - Home Page (Customer Catalog)
// University Web Application Development Project
//
// NOTE: This page belongs to Member 2 (Service Catalog).
// Member 1 has created this minimal placeholder so the project
// runs without errors. Member 2 should replace the body content
// below with the full customer-facing service catalog.
// ============================================================

$pageTitle = 'Home';
require_once 'includes/header.php';
?>

<div class="container">
    <div class="page-heading text-center" style="padding: var(--space-2xl) 0;">
        <h1>🐾 Welcome to Pet Care System</h1>
        <p class="text-muted mt-md">
            Professional grooming, veterinary and boarding services for your beloved pets.
        </p>
        <p class="text-muted text-sm mt-sm">
            <em>Member 2 &mdash; Service Catalog page coming soon.</em>
        </p>
        <a href="admin/login.php" class="btn btn-primary mt-lg">Admin Login</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
