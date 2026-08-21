<?php
// ============================================================
// Pet Care System - Admin Appointments List
// University Web Application Development Project
// Member 4: Admin Management
// ============================================================

require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Fetch all appointments with service and payment info
$result = $conn->query(
    'SELECT
        a.id           AS booking_id,
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
        p.amount,
        p.payment_method,
        p.payment_status,
        p.transaction_reference
     FROM appointments a
     JOIN services s ON s.id = a.service_id
     LEFT JOIN payments p ON p.appointment_id = a.id
     ORDER BY a.id DESC'
);

$appointments = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments | Pet Care Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="admin-sidebar-title">🐾 PCS Admin</div>
        <nav><ul>
            <li><a href="dashboard.php">📊 Dashboard</a></li>
            <li><a href="services.php">🛎️ Manage Services</a></li>
            <li><a href="appointments.php" class="active">📅 Appointments</a></li>
        </ul></nav>
        <div class="admin-sidebar-footer">
            <a href="logout.php" class="btn btn-danger btn-sm btn-full">🔒 Logout</a>
        </div>
    </aside>
    <main class="admin-main">
        <div class="admin-top-bar">
            <div>
                <h1 class="admin-welcome">📅 <span>Appointments</span></h1>
                <p class="text-muted text-sm"><?php echo count($appointments); ?> appointment<?php echo count($appointments) !== 1 ? 's' : ''; ?> total</p>
            </div>
        </div>

        <div class="table-wrapper">
            <table id="appointmentsTable">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Customer</th>
                        <th>Pet</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Booking Status</th>
                        <th>Payment</th>
                        <th>Amount</th>
                        <th>Reference</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($appointments)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted" style="padding:2rem;">
                                No appointments yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($appointments as $appt): ?>
                        <tr>
                            <td><strong>#<?php echo (int)$appt['booking_id']; ?></strong></td>
                            <td>
                                <strong><?php echo sanitize($appt['customer_name']); ?></strong><br>
                                <small class="text-muted"><?php echo sanitize($appt['customer_email']); ?></small><br>
                                <small class="text-muted"><?php echo sanitize($appt['customer_phone']); ?></small>
                            </td>
                            <td>
                                <strong><?php echo sanitize($appt['pet_name']); ?></strong><br>
                                <small><?php echo sanitize($appt['breed']); ?>, <?php echo sanitize($appt['age']); ?> yr<?php echo $appt['age'] != 1 ? 's' : ''; ?></small>
                            </td>
                            <td><?php echo sanitize($appt['service_name']); ?></td>
                            <td><?php echo sanitize($appt['appointment_date']); ?></td>
                            <td>
                                <?php
                                $bs = $appt['booking_status'];
                                $bClass = match($bs) {
                                    'Confirmed' => 'badge-confirm',
                                    'Pending'   => 'badge-pending',
                                    default     => 'badge-pending',
                                };
                                ?>
                                <span class="badge <?php echo $bClass; ?>"><?php echo sanitize($bs); ?></span>
                            </td>
                            <td>
                                <?php
                                $ps = $appt['payment_status'] ?? 'Pending';
                                $pClass = $ps === 'Paid' ? 'badge-paid' : 'badge-pending';
                                ?>
                                <span class="badge <?php echo $pClass; ?>"><?php echo sanitize($ps); ?></span>
                                <?php if ($appt['payment_method']): ?>
                                    <br><small class="text-muted"><?php echo sanitize($appt['payment_method']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $appt['amount'] ? formatPrice((float)$appt['amount']) : '—'; ?></td>
                            <td>
                                <small><?php echo $appt['transaction_reference'] ? sanitize($appt['transaction_reference']) : '—'; ?></small>
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
