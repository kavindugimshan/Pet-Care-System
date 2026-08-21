<?php
// ============================================================
// Pet Care System - Admin: Delete Service
// University Web Application Development Project
// Member 4: Admin Service & Appointment Management
// ============================================================

// Session guard
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';

// ── Validate the service ID ────────────────────────────────
// Accept from GET (confirmation page) or POST (actual delete)
$id = 0;
$rawId = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? ($_POST['service_id'] ?? 0)
    : ($_GET['id'] ?? 0);

if (ctype_digit((string) $rawId) && (int) $rawId > 0) {
    $id = (int) $rawId;
}

if ($id <= 0) {
    $_SESSION['error'] = 'Invalid service ID.';
    redirect('services.php');
}

// ── Fetch service details for confirmation display ─────────
$stmt = $conn->prepare("SELECT id, service_name FROM services WHERE id = ? LIMIT 1");
if ($stmt === false) {
    error_log('Prepare failed (service delete fetch): ' . $conn->error);
    $_SESSION['error'] = 'Unable to load the service.';
    $conn->close();
    redirect('services.php');
}
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$service = $result->fetch_assoc();
$stmt->close();

if (!$service) {
    $_SESSION['error'] = 'Service not found.';
    $conn->close();
    redirect('services.php');
}

$deleteError = '';

// ── Handle POST — perform actual deletion ──────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Re-verify the service exists server-side (do not trust hidden field blindly)
    $delStmt = $conn->prepare("DELETE FROM services WHERE id = ?");

    if ($delStmt === false) {
        error_log('Prepare failed (service delete): ' . $conn->error);
        $deleteError = 'Unable to delete the service. Please try again.';
    } else {
        $delStmt->bind_param('i', $id);
        $delStmt->execute();
        $affectedRows = $delStmt->affected_rows;
        $mysqlErrno = $delStmt->errno;
        $delStmt->close();

        if ($affectedRows > 0) {
            // Deletion successful
            $conn->close();
            $_SESSION['success'] = 'Service deleted successfully.';
            redirect('services.php');
        } elseif ($mysqlErrno === 1451) {
            // Foreign key constraint violation — service has existing appointments
            $deleteError = 'This service cannot be deleted because it has existing appointments. '
                . 'Please cancel or reassign those appointments first.';
        } else {
            error_log('Delete failed (service id=' . $id . '): errno=' . $mysqlErrno);
            $deleteError = 'Unable to delete the service. Please try again.';
        }
    }
}

$conn->close();
$pageTitle = 'Delete Service';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Delete Service - Pet Care System Admin">
    <title>
        <?php echo htmlspecialchars($pageTitle . ' | Pet Care System Admin'); ?>
    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="admin-layout">

        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="admin-sidebar-title">🐾 PCS Admin</div>
            <nav>
                <ul>
                    <li><a href="dashboard.php">📊 Dashboard</a></li>
                    <li><a href="services.php" class="active">🛎️ Manage Services</a></li>
                    <li><a href="appointments.php">📅 Appointments</a></li>
                </ul>
            </nav>
            <div class="admin-sidebar-footer">
                <a href="logout.php" class="btn btn-danger btn-sm btn-full">🔒 Logout</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">

            <!-- Mobile Nav -->
            <div class="mobile-nav-bar">
                <button class="hamburger" id="hamburgerBtn" aria-label="Open menu">&#9776;</button>
                <span>🗑️ Delete Service</span>
                <a href="logout.php" style="color:#fff; text-decoration:none; font-size:.85rem;">🔒</a>
            </div>

            <!-- Top Bar -->
            <div class="admin-top-bar">
                <div>
                    <h1 class="admin-welcome">Delete <span>Service</span></h1>
                    <p class="text-muted text-sm">Remove a pet care service from the system.</p>
                </div>
                <a href="logout.php" class="btn btn-secondary btn-sm">🔒 Logout</a>
            </div>

            <!-- Breadcrumb -->
            <div class="page-header">
                <div>
                    <h1>Delete Service</h1>
                    <p class="text-muted text-sm">
                        <a href="services.php" style="color:var(--admin-accent);">← Back to Services</a>
                    </p>
                </div>
            </div>

            <!-- Error from delete attempt -->
            <?php if ($deleteError): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($deleteError); ?>
                </div>
            <?php endif; ?>

            <!-- Confirmation Box -->
            <div class="confirm-box">
                <div class="confirm-icon">⚠️</div>
                <h2>Delete Service</h2>
                <p class="text-muted" style="margin-bottom:.5rem;">
                    Are you sure you want to permanently delete this service?
                    <strong>This action cannot be undone.</strong>
                </p>
                <span class="service-name">
                    <?php echo htmlspecialchars($service['service_name']); ?>
                </span>

                <div class="confirm-actions">
                    <a href="services.php" class="btn btn-secondary" id="cancelDeleteBtn">
                        ← Cancel
                    </a>
                    <!-- POST form for actual deletion — GET never deletes -->
                    <form method="POST" action="service-delete.php" style="display:inline;">
                        <input type="hidden" name="service_id" value="<?php echo $id; ?>">
                        <button type="submit" class="btn btn-danger" id="confirmDeleteBtn">
                            🗑️ Delete Service
                        </button>
                    </form>
                </div>
            </div>

        </main>
    </div>

    <script src="../assets/js/admin.js"></script>
</body>

</html>