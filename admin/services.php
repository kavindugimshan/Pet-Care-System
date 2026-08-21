<?php
// ============================================================
// Pet Care System - Admin: Service Management Listing
// University Web Application Development Project
// Member 4: Admin Service & Appointment Management
// ============================================================

// Session guard — redirect to login if not authenticated
require_once '../includes/auth.php';

// Database connection and helpers
require_once '../config/db.php';
require_once '../includes/functions.php';

// ── Fetch all services ─────────────────────────────────────
$services = [];
$fetchError = '';

$result = $conn->query("SELECT id, service_name, category, target_pet_type, price, image, created_at FROM services ORDER BY created_at DESC");

if ($result === false) {
    error_log('Services listing query failed: ' . $conn->error);
    $fetchError = 'Unable to load services at the moment. Please try again later.';
} else {
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
    $result->free();
}

$conn->close();

$pageTitle = 'Service Management';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Admin Service Management - Pet Care System">
    <title>
        <?php echo htmlspecialchars($pageTitle . ' | Pet Care System Admin'); ?>
    </title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>

    <!-- Sidebar Overlay (mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- ── Admin Layout ──────────────────────────────────────── -->
    <div class="admin-layout">

        <!-- Sidebar Navigation -->
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
                <a href="logout.php" class="btn btn-danger btn-sm btn-full" id="logoutBtn">
                    🔒 Logout
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">

            <!-- Mobile Nav Bar -->
            <div class="mobile-nav-bar">
                <button class="hamburger" id="hamburgerBtn" aria-label="Open menu">&#9776;</button>
                <span>🛎️ Service Management</span>
                <a href="logout.php" style="color:#fff; text-decoration:none; font-size:.85rem;">🔒</a>
            </div>

            <!-- Top Bar -->
            <div class="admin-top-bar">
                <div>
                    <h1 class="admin-welcome">Service <span>Management</span></h1>
                    <p class="text-muted text-sm">Manage all pet care services available to customers.</p>
                </div>
                <a href="logout.php" class="btn btn-secondary btn-sm" id="headerLogoutBtn">🔒 Logout</a>
            </div>

            <!-- Flash Messages -->
            <?php showFlash('success');
            showFlash('error'); ?>

            <?php if ($fetchError): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($fetchError); ?>
                </div>
            <?php endif; ?>

            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1>Service Management</h1>
                    <p class="text-muted text-sm">
                        <?php echo count($services); ?> service
                        <?php echo count($services) !== 1 ? 's' : ''; ?> available
                    </p>
                </div>
                <a href="service-add.php" class="btn btn-primary" id="addServiceBtn">
                    ➕ Add New Service
                </a>
            </div>

            <!-- Services Table / Empty State -->
            <?php if (empty($services) && !$fetchError): ?>
                <div class="empty-state">
                    <div class="empty-icon">🛎️</div>
                    <h3>No Services Yet</h3>
                    <p>No services are currently available. Add your first pet care service to get started.</p>
                    <a href="service-add.php" class="btn btn-primary" id="emptyAddServiceBtn">➕ Add New Service</a>
                </div>
            <?php elseif (!empty($services)): ?>
                <div class="table-responsive">
                    <table id="servicesTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Service Name</th>
                                <th>Category</th>
                                <th>Pet Type</th>
                                <th>Price</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $service): ?>
                                <tr>
                                    <td class="td-muted">#
                                        <?php echo (int) $service['id']; ?>
                                    </td>
                                    <td>
                                        <span class="text-bold">
                                            <?php echo htmlspecialchars($service['service_name']); ?>
                                        </span>
                                        <?php if (!empty($service['image'])): ?>
                                            <br><span class="td-muted" style="font-size:.8rem;">📷
                                                <?php echo htmlspecialchars($service['image']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge badge-confirmed">
                                            <?php echo htmlspecialchars($service['category']); ?>
                                        </span></td>
                                    <td>
                                        <?php echo htmlspecialchars($service['target_pet_type']); ?>
                                    </td>
                                    <td class="text-bold">
                                        <?php echo formatPrice((float) $service['price']); ?>
                                    </td>
                                    <td class="td-muted">
                                        <?php echo date('d M Y', strtotime($service['created_at'])); ?>
                                    </td>
                                    <td>
                                        <div class="td-actions">
                                            <a href="service-edit.php?id=<?php echo (int) $service['id']; ?>"
                                                class="btn btn-warning btn-sm"
                                                id="editService<?php echo (int) $service['id']; ?>">
                                                ✏️ Edit
                                            </a>
                                            <a href="service-delete.php?id=<?php echo (int) $service['id']; ?>"
                                                class="btn btn-danger btn-sm"
                                                id="deleteService<?php echo (int) $service['id']; ?>">
                                                🗑️ Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        </main>
    </div>

    <script src="../assets/js/admin.js"></script>
</body>

</html>