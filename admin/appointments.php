<?php
// ============================================================
// Pet Care System - Admin: Appointment Management
// University Web Application Development Project
// Member 4: Admin Service & Appointment Management
// ============================================================

// Session guard
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';

// ── Handle optional booking status update (POST) ───────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $apptId = 0;
    $allowedStatus = ['Pending', 'Confirmed', 'Completed', 'Cancelled'];

    if (isset($_POST['appointment_id']) && ctype_digit((string) $_POST['appointment_id'])) {
        $apptId = (int) $_POST['appointment_id'];
    }
    $newStatus = trim($_POST['booking_status'] ?? '');

    if ($apptId > 0 && in_array($newStatus, $allowedStatus, true)) {
        $stmt = $conn->prepare("UPDATE appointments SET booking_status = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param('si', $newStatus, $apptId);
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $_SESSION['success'] = 'Booking status updated successfully.';
            } else {
                $stmt->errno === 0
                    ? ($_SESSION['error'] = 'No change was made.')
                    : ($_SESSION['error'] = 'Unable to update booking status.');
                error_log('Status update failed: ' . $stmt->error);
            }
            $stmt->close();
        } else {
            error_log('Prepare failed (status update): ' . $conn->error);
            $_SESSION['error'] = 'Unable to update booking status.';
        }
    } else {
        $_SESSION['error'] = 'Invalid request.';
    }

    $conn->close();
    redirect('appointments.php');
}

// ── Filter by booking status (optional) ───────────────────
$allowedFilters = ['all', 'Pending', 'Confirmed', 'Completed', 'Cancelled'];
$filter = 'all';
if (isset($_GET['status']) && in_array($_GET['status'], $allowedFilters, true)) {
    $filter = $_GET['status'];
}

// ── Fetch appointments with JOIN ───────────────────────────
$appointments = [];
$fetchError = '';

$sql = "SELECT
            a.id,
            a.customer_name,
            a.customer_email,
            a.customer_phone,
            a.pet_name,
            a.breed,
            a.age,
            a.appointment_date,
            a.booking_status,
            a.created_at,
            s.service_name,
            s.price          AS service_price,
            p.payment_status,
            p.payment_method,
            p.amount         AS paid_amount,
            p.transaction_reference
        FROM appointments a
        JOIN services s
            ON a.service_id = s.id
        LEFT JOIN payments p
            ON a.id = p.appointment_id
        ORDER BY a.created_at DESC";

$result = $conn->query($sql);
if ($result === false) {
    error_log('Appointments query failed: ' . $conn->error);
    $fetchError = 'Unable to load appointments at the moment. Please try again later.';
} else {
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    $result->free();
}

$conn->close();

// ── Status badge helper ────────────────────────────────────
function bookingBadgeClass(string $status): string
{
    $map = [
        'Pending' => 'badge-pending',
        'Confirmed' => 'badge-confirmed',
        'Completed' => 'badge-completed',
        'Cancelled' => 'badge-cancelled',
    ];
    return $map[$status] ?? 'badge-default';
}

function paymentBadgeClass(?string $status): string
{
    if ($status === null)
        return 'badge-pending';
    if ($status === 'Paid')
        return 'badge-paid';
    if ($status === 'Failed')
        return 'badge-failed';
    return 'badge-pending';
}

function cardClass(string $status): string
{
    $map = [
        'Pending' => 'appt-card--pending',
        'Confirmed' => 'appt-card--confirmed',
        'Completed' => 'appt-card--completed',
        'Cancelled' => 'appt-card--cancelled',
    ];
    return $map[$status] ?? '';
}

$pageTitle = 'Appointments';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Admin Appointment Management - Pet Care System">
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
                    <li><a href="services.php">🛎️ Manage Services</a></li>
                    <li><a href="appointments.php" class="active">📅 Appointments</a></li>
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
                <span>📅 Appointments</span>
                <a href="logout.php" style="color:#fff; text-decoration:none; font-size:.85rem;">🔒</a>
            </div>

            <!-- Top Bar -->
            <div class="admin-top-bar">
                <div>
                    <h1 class="admin-welcome">Appointment <span>Management</span></h1>
                    <p class="text-muted text-sm">Monitor all incoming customer bookings and payment statuses.</p>
                </div>
                <a href="logout.php" class="btn btn-secondary btn-sm">🔒 Logout</a>
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
                    <h1>Appointments</h1>
                    <p class="text-muted text-sm">
                        <?php echo count($appointments); ?> total booking
                        <?php echo count($appointments) !== 1 ? 's' : ''; ?>
                    </p>
                </div>
            </div>

            <?php if (empty($appointments) && !$fetchError): ?>
                <!-- Empty State -->
                <div class="empty-state">
                    <div class="empty-icon">📅</div>
                    <h3>No Appointments Yet</h3>
                    <p>No appointments have been received yet. They will appear here once customers make bookings.</p>
                </div>

            <?php else: ?>
                <!-- Filter Bar (JS-powered client filter) -->
                <div class="filter-bar">
                    <span class="filter-label">Filter:</span>
                    <button class="filter-btn active" data-filter="all" id="filterAll">All</button>
                    <button class="filter-btn f-pending" data-filter="pending" id="filterPending">Pending</button>
                    <button class="filter-btn f-confirmed" data-filter="confirmed" id="filterConfirmed">Confirmed</button>
                    <button class="filter-btn f-completed" data-filter="completed" id="filterCompleted">Completed</button>
                    <button class="filter-btn f-cancelled" data-filter="cancelled" id="filterCancelled">Cancelled</button>
                </div>

                <!-- No results message after filter -->
                <div id="noFilterResults" class="alert alert-info" style="display:none;">
                    No appointments match the selected filter.
                </div>

                <!-- Appointment Cards Grid -->
                <div class="appt-grid" id="appointmentGrid">
                    <?php foreach ($appointments as $appt):
                        $statusLower = strtolower($appt['booking_status'] ?? 'pending');
                        $payStatus = $appt['payment_status'] ?? null;
                        ?>
                        <div class="appt-card <?php echo htmlspecialchars(cardClass($appt['booking_status'] ?? '')); ?>"
                            data-status="<?php echo htmlspecialchars($statusLower); ?>"
                            id="appt-<?php echo (int) $appt['id']; ?>">

                            <!-- Card Header -->
                            <div class="appt-header">
                                <span class="appt-id">Booking #
                                    <?php echo (int) $appt['id']; ?>
                                </span>
                                <span class="appt-date">
                                    📅
                                    <?php echo date('d M Y', strtotime($appt['appointment_date'])); ?>
                                </span>
                            </div>

                            <!-- Customer -->
                            <div class="appt-section">
                                <div class="appt-section-title">👤 Customer</div>
                                <div class="appt-detail">
                                    <span class="label">Name:</span>
                                    <span class="value">
                                        <?php echo htmlspecialchars($appt['customer_name']); ?>
                                    </span>
                                </div>
                                <div class="appt-detail">
                                    <span class="label">Email:</span>
                                    <span class="value">
                                        <?php echo htmlspecialchars($appt['customer_email']); ?>
                                    </span>
                                </div>
                                <div class="appt-detail">
                                    <span class="label">Phone:</span>
                                    <span class="value">
                                        <?php echo htmlspecialchars($appt['customer_phone']); ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Pet -->
                            <div class="appt-section">
                                <div class="appt-section-title">🐾 Pet Details</div>
                                <div class="appt-detail">
                                    <span class="label">Pet Name:</span>
                                    <span class="value">
                                        <?php echo htmlspecialchars($appt['pet_name']); ?>
                                    </span>
                                </div>
                                <div class="appt-detail">
                                    <span class="label">Breed:</span>
                                    <span class="value">
                                        <?php echo htmlspecialchars($appt['breed']); ?>
                                    </span>
                                </div>
                                <div class="appt-detail">
                                    <span class="label">Age:</span>
                                    <span class="value">
                                        <?php echo htmlspecialchars($appt['age']); ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Service -->
                            <div class="appt-section">
                                <div class="appt-section-title">🛎️ Service</div>
                                <div class="appt-detail">
                                    <span class="label">Service:</span>
                                    <span class="value">
                                        <?php echo htmlspecialchars($appt['service_name']); ?>
                                    </span>
                                </div>
                                <div class="appt-detail">
                                    <span class="label">Price:</span>
                                    <span class="value">
                                        <?php echo formatPrice((float) $appt['service_price']); ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Payment -->
                            <div class="appt-section">
                                <div class="appt-section-title">💳 Payment</div>
                                <div class="appt-detail">
                                    <span class="label">Status:</span>
                                    <span class="value">
                                        <span class="badge <?php echo paymentBadgeClass($payStatus); ?>">
                                            <?php echo htmlspecialchars($payStatus ?? 'Pending'); ?>
                                        </span>
                                    </span>
                                </div>
                                <?php if (!empty($appt['payment_method'])): ?>
                                    <div class="appt-detail">
                                        <span class="label">Method:</span>
                                        <span class="value">
                                            <?php echo htmlspecialchars($appt['payment_method']); ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($appt['paid_amount'])): ?>
                                    <div class="appt-detail">
                                        <span class="label">Paid:</span>
                                        <span class="value">
                                            <?php echo formatPrice((float) $appt['paid_amount']); ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($appt['transaction_reference'])): ?>
                                    <div class="appt-detail">
                                        <span class="label">Ref:</span>
                                        <span class="value">
                                            <?php echo htmlspecialchars($appt['transaction_reference']); ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Footer: Status + Update -->
                            <div class="appt-footer">
                                <div>
                                    <span class="badge <?php echo bookingBadgeClass($appt['booking_status'] ?? ''); ?>">
                                        <?php echo htmlspecialchars($appt['booking_status'] ?? 'Pending'); ?>
                                    </span>
                                    <span class="text-muted text-sm" style="margin-left:.5rem;">
                                        <?php echo date('d M Y', strtotime($appt['created_at'])); ?>
                                    </span>
                                </div>

                                <!-- Optional status update form -->
                                <form method="POST" action="appointments.php"
                                    style="display:flex; gap:.4rem; align-items:center; flex-wrap:wrap;">
                                    <input type="hidden" name="appointment_id" value="<?php echo (int) $appt['id']; ?>">
                                    <input type="hidden" name="update_status" value="1">
                                    <select name="booking_status" class="form-control"
                                        style="padding:.3rem .55rem; font-size:.8rem; width:auto; min-width:110px;"
                                        id="statusSelect<?php echo (int) $appt['id']; ?>" aria-label="Update booking status">
                                        <?php foreach (['Pending', 'Confirmed', 'Completed', 'Cancelled'] as $s): ?>
                                            <option value="<?php echo $s; ?>" <?php echo ($appt['booking_status'] === $s) ? 'selected' : ''; ?>>
                                                <?php echo $s; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-primary btn-sm"
                                        id="updateStatus<?php echo (int) $appt['id']; ?>">
                                        Update
                                    </button>
                                </form>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>

        </main>
    </div>

    <script src="../assets/js/admin.js"></script>
</body>

</html>