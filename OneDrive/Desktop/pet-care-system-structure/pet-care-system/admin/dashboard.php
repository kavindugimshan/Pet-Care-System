<?php
// ============================================================
// Pet Care System - Admin Dashboard
// University Web Application Development Project
// Member 1: Core Auth
//
// Protected page ΓÇö requires a valid admin session.
// ============================================================

// Session guard (redirects to login if not authenticated)
require_once '../includes/auth.php';

// Database connection and helpers
require_once '../config/db.php';
require_once '../includes/functions.php';

// ΓöÇΓöÇ Fetch Dashboard Statistics ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ
// Helper: safely run a COUNT query and return the integer result
function fetchCount(mysqli $conn, string $sql): int
{
    $result = $conn->query($sql);
    if (!$result) {
        error_log('Dashboard query failed: ' . $conn->error . ' | SQL: ' . $sql);
        return 0;
    }
    $row = $result->fetch_row();
    return isset($row[0]) ? (int) $row[0] : 0;
}

$totalServices     = fetchCount($conn, "SELECT COUNT(*) FROM services");
$totalAppointments = fetchCount($conn, "SELECT COUNT(*) FROM appointments");
$paidBookings      = fetchCount($conn, "SELECT COUNT(*) FROM payments WHERE payment_status = 'Paid'");
$pendingPayments   = fetchCount($conn, "SELECT COUNT(*) FROM payments WHERE payment_status = 'Pending'");

$conn->close();

// Admin username from session (already sanitized on login)
$adminUsername = htmlspecialchars($_SESSION['admin_username'] ?? 'Admin');

$pageTitle = 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Pet Care System Admin Dashboard">
    <title><?php echo htmlspecialchars($pageTitle . ' | Pet Care System Admin'); ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<!-- ΓöÇΓöÇ Admin Layout ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ -->
<div class="admin-layout">

    <!-- Sidebar Navigation -->
    <aside class="admin-sidebar">
        <div class="admin-sidebar-title">≡ƒÉ╛ PCS Admin</div>
        <nav>
            <ul>
                <li>
                    <a href="dashboard.php" class="active">
                        ≡ƒôè Dashboard
                    </a>
                </li>
                <li>
                    <!-- Member 4 will implement the logic for this page -->
                    <a href="services.php">
                        ≡ƒ¢Ä∩╕Å Manage Services
                    </a>
                </li>
                <li>
                    <!-- Member 4 will implement the logic for this page -->
                    <a href="appointments.php">
                        ≡ƒôà Appointments
                    </a>
                </li>
            </ul>
        </nav>
        <div class="admin-sidebar-footer">
            <a href="logout.php" class="btn btn-danger btn-sm btn-full" id="logoutBtn">
                ≡ƒöÆ Logout
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">

        <!-- Top Bar -->
        <div class="admin-top-bar">
            <div>
                <h1 class="admin-welcome">
                    Welcome back, <span><?php echo $adminUsername; ?></span>
                </h1>
                <p class="text-muted text-sm">
                    Pet Care Admin Dashboard &mdash;
                    <?php echo date('l, d F Y'); ?>
                </p>
            </div>
            <a href="logout.php" class="btn btn-secondary btn-sm" id="headerLogoutBtn">
                ≡ƒöÆ Logout
            </a>
        </div>

        <!-- Statistics Cards -->
        <section aria-label="Dashboard Statistics">
            <h2 class="mb-lg text-bold" style="font-size: 1.1rem; color: var(--clr-text-muted); text-transform: uppercase; letter-spacing: .05em;">
                Overview
            </h2>
            <div class="stats-grid">

                <!-- Total Services -->
                <div class="stat-card">
                    <div class="stat-icon">≡ƒ¢Ä∩╕Å</div>
                    <div class="stat-number" id="statServices">
                        <?php echo $totalServices; ?>
                    </div>
                    <div class="stat-label">Total Services</div>
                </div>

                <!-- Total Appointments -->
                <div class="stat-card stat-card--accent">
                    <div class="stat-icon">≡ƒôà</div>
                    <div class="stat-number" id="statAppointments">
                        <?php echo $totalAppointments; ?>
                    </div>
                    <div class="stat-label">Total Appointments</div>
                </div>

                <!-- Paid Bookings -->
                <div class="stat-card stat-card--success">
                    <div class="stat-icon">Γ£à</div>
                    <div class="stat-number" id="statPaid">
                        <?php echo $paidBookings; ?>
                    </div>
                    <div class="stat-label">Paid Bookings</div>
                </div>

                <!-- Pending Payments -->
                <div class="stat-card stat-card--error">
                    <div class="stat-icon">ΓÅ│</div>
                    <div class="stat-number" id="statPending">
                        <?php echo $pendingPayments; ?>
                    </div>
                    <div class="stat-label">Pending Payments</div>
                </div>

            </div>
        </section>

        <!-- Quick Links Section -->
        <section aria-label="Quick Access">
            <h2 class="mb-lg text-bold" style="font-size: 1.1rem; color: var(--clr-text-muted); text-transform: uppercase; letter-spacing: .05em;">
                Quick Access
            </h2>
            <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">

                <div class="card">
                    <div class="card-title">≡ƒ¢Ä∩╕Å Services</div>
                    <p class="text-muted text-sm mb-md">
                        Add, edit, or remove pet care services available to customers.
                    </p>
                    <!-- Member 4 implements this page -->
                    <a href="services.php" class="btn btn-primary btn-sm">Manage Services</a>
                </div>

                <div class="card">
                    <div class="card-title">≡ƒôà Appointments</div>
                    <p class="text-muted text-sm mb-md">
                        Review and update the status of customer appointment bookings.
                    </p>
                    <!-- Member 4 implements this page -->
                    <a href="appointments.php" class="btn btn-primary btn-sm">View Appointments</a>
                </div>

                <div class="card">
                    <div class="card-title">≡ƒîÉ Public Site</div>
                    <p class="text-muted text-sm mb-md">
                        View the customer-facing website with the service catalog.
                    </p>
                    <a href="../index.php" class="btn btn-secondary btn-sm">Go to Website</a>
                </div>

            </div>
        </section>

    </main>
</div>

<script>
    // Animate stat numbers counting up on page load
    function animateCount(elementId, target) {
        var el = document.getElementById(elementId);
        if (!el || target === 0) return;
        var start    = 0;
        var duration = 800;
        var step     = Math.ceil(target / (duration / 16));
        var timer    = setInterval(function () {
            start += step;
            if (start >= target) {
                el.textContent = target;
                clearInterval(timer);
            } else {
                el.textContent = start;
            }
        }, 16);
    }

    document.addEventListener('DOMContentLoaded', function () {
        animateCount('statServices',     <?php echo $totalServices; ?>);
        animateCount('statAppointments', <?php echo $totalAppointments; ?>);
        animateCount('statPaid',         <?php echo $paidBookings; ?>);
        animateCount('statPending',      <?php echo $pendingPayments; ?>);
    });
</script>

</body>
</html>
