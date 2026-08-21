<?php
// ============================================================
// Pet Care System - Admin Services List
// University Web Application Development Project
// Member 4: Admin Management
// ============================================================

require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$result   = $conn->query('SELECT id, service_name, category, target_pet_type, price, image FROM services ORDER BY id DESC');
$services = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$conn->close();

$adminUsername = htmlspecialchars($_SESSION['admin_username'] ?? 'Admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Services | Pet Care Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
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

    <!-- Main -->
    <main class="admin-main">
        <div class="admin-top-bar">
            <div>
                <h1 class="admin-welcome">🛎️ Manage <span>Services</span></h1>
                <p class="text-muted text-sm"><?php echo count($services); ?> service<?php echo count($services) !== 1 ? 's' : ''; ?> in database</p>
            </div>
            <a href="service-add.php" class="btn btn-primary" id="addServiceBtn">+ Add New Service</a>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?php echo sanitize($flash['type']); ?>">
                <?php echo sanitize($flash['message']); ?>
            </div>
        <?php endif; ?>

        <div class="table-wrapper">
            <table id="servicesTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Service</th>
                        <th>Category</th>
                        <th>Pet Type</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($services)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted" style="padding:2rem;">
                                No services found. <a href="service-add.php">Add the first one →</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($services as $svc): ?>
                        <tr>
                            <td>#<?php echo (int)$svc['id']; ?></td>
                            <td><strong><?php echo sanitize($svc['service_name']); ?></strong></td>
                            <td><?php echo sanitize($svc['category']); ?></td>
                            <td><?php echo sanitize($svc['target_pet_type']); ?></td>
                            <td><?php echo formatPrice((float)$svc['price']); ?></td>
                            <td>
                                <div class="d-flex gap-sm">
                                    <a href="service-edit.php?id=<?php echo (int)$svc['id']; ?>"
                                       class="btn btn-secondary btn-sm"
                                       id="edit-<?php echo (int)$svc['id']; ?>">
                                        ✏️ Edit
                                    </a>
                                    <form action="service-delete.php" method="POST"
                                          onsubmit="return confirm('Delete \'<?php echo addslashes(htmlspecialchars($svc['service_name'])); ?>\'? This cannot be undone.');"
                                          style="display:inline;">
                                        <input type="hidden" name="id" value="<?php echo (int)$svc['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm"
                                                id="delete-<?php echo (int)$svc['id']; ?>">
                                            🗑️ Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
