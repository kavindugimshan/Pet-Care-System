<?php
// ============================================================
// Pet Care System - Booking Confirmation Page
// University Web Application Development Project
// Member 3: Booking & Payment
// ============================================================

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

$apptId = filter_var($_GET['appointment_id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($apptId === false) {
    redirect('../index.php');
}

// Fetch full booking + payment data from DB (trusted source)
$stmt = $conn->prepare(
    'SELECT
        a.id            AS booking_id,
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
        s.category,
        s.target_pet_type,
        p.amount,
        p.payment_method,
        p.transaction_reference,
        p.payment_status,
        p.paid_at
     FROM appointments a
     JOIN services s ON s.id = a.service_id
     LEFT JOIN payments p ON p.appointment_id = a.id
     WHERE a.id = ?
     LIMIT 1'
);
$stmt->bind_param('i', $apptId);
$stmt->execute();
$result  = $stmt->get_result();
$booking = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$booking) {
    redirect('../index.php');
}

$pageTitle = 'Booking Confirmed';
require_once __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="/assets/css/booking.css">

<div class="success-wrapper">
    <div class="container">

        <!-- Success Header -->
        <div class="success-header">
            <div class="success-icon" aria-hidden="true">✅</div>
            <h1 class="success-title">Booking Confirmed!</h1>
            <p class="success-subtitle">
                Thank you, <strong><?php echo sanitize($booking['customer_name']); ?></strong>!
                Your appointment has been successfully booked.
            </p>
        </div>

        <!-- Confirmation Card -->
        <div class="confirmation-card">

            <!-- Reference number -->
            <div class="confirmation-ref-bar">
                <span class="ref-label">Booking Reference</span>
                <span class="ref-number">#<?php echo str_pad($booking['booking_id'], 6, '0', STR_PAD_LEFT); ?></span>
            </div>

            <div class="confirmation-grid">

                <!-- Service Details -->
                <div class="confirmation-section">
                    <h2 class="confirmation-section-title">🛎️ Service</h2>
                    <div class="confirmation-row">
                        <span class="conf-label">Service</span>
                        <span class="conf-value"><?php echo sanitize($booking['service_name']); ?></span>
                    </div>
                    <div class="confirmation-row">
                        <span class="conf-label">Category</span>
                        <span class="conf-value"><?php echo sanitize($booking['category']); ?></span>
                    </div>
                    <div class="confirmation-row">
                        <span class="conf-label">For</span>
                        <span class="conf-value"><?php echo sanitize($booking['target_pet_type']); ?></span>
                    </div>
                    <div class="confirmation-row">
                        <span class="conf-label">Date</span>
                        <span class="conf-value"><?php echo sanitize($booking['appointment_date']); ?></span>
                    </div>
                    <div class="confirmation-row">
                        <span class="conf-label">Booking Status</span>
                        <span class="conf-value">
                            <span class="badge <?php echo $booking['booking_status'] === 'Confirmed' ? 'badge-confirm' : 'badge-pending'; ?>">
                                <?php echo sanitize($booking['booking_status']); ?>
                            </span>
                        </span>
                    </div>
                </div>

                <!-- Pet Details -->
                <div class="confirmation-section">
                    <h2 class="confirmation-section-title">🐾 Pet Details</h2>
                    <div class="confirmation-row">
                        <span class="conf-label">Pet Name</span>
                        <span class="conf-value"><?php echo sanitize($booking['pet_name']); ?></span>
                    </div>
                    <div class="confirmation-row">
                        <span class="conf-label">Breed</span>
                        <span class="conf-value"><?php echo sanitize($booking['breed']); ?></span>
                    </div>
                    <div class="confirmation-row">
                        <span class="conf-label">Age</span>
                        <span class="conf-value"><?php echo sanitize($booking['age']); ?> year<?php echo $booking['age'] != 1 ? 's' : ''; ?></span>
                    </div>
                    <div class="confirmation-row">
                        <span class="conf-label">Owner</span>
                        <span class="conf-value"><?php echo sanitize($booking['customer_name']); ?></span>
                    </div>
                    <div class="confirmation-row">
                        <span class="conf-label">Email</span>
                        <span class="conf-value"><?php echo sanitize($booking['customer_email']); ?></span>
                    </div>
                    <div class="confirmation-row">
                        <span class="conf-label">Phone</span>
                        <span class="conf-value"><?php echo sanitize($booking['customer_phone']); ?></span>
                    </div>
                </div>

                <!-- Payment Details -->
                <div class="confirmation-section confirmation-payment">
                    <h2 class="confirmation-section-title">💳 Payment</h2>
                    <div class="confirmation-row">
                        <span class="conf-label">Amount</span>
                        <span class="conf-value conf-amount">
                            <?php echo $booking['amount'] ? formatPrice((float)$booking['amount']) : 'Pending'; ?>
                        </span>
                    </div>
                    <div class="confirmation-row">
                        <span class="conf-label">Method</span>
                        <span class="conf-value"><?php echo sanitize($booking['payment_method'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="confirmation-row">
                        <span class="conf-label">Status</span>
                        <span class="conf-value">
                            <span class="badge <?php echo $booking['payment_status'] === 'Paid' ? 'badge-paid' : 'badge-pending'; ?>">
                                <?php echo sanitize($booking['payment_status'] ?? 'Pending'); ?>
                            </span>
                        </span>
                    </div>
                    <?php if ($booking['transaction_reference']): ?>
                    <div class="confirmation-row">
                        <span class="conf-label">Transaction</span>
                        <span class="conf-value conf-ref"><?php echo sanitize($booking['transaction_reference']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($booking['paid_at']): ?>
                    <div class="confirmation-row">
                        <span class="conf-label">Paid At</span>
                        <span class="conf-value"><?php echo sanitize($booking['paid_at']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>

            </div>

            <!-- Actions -->
            <div class="confirmation-actions">
                <a href="/index.php" class="btn btn-primary" id="bookAnotherBtn">
                    🐾 Book Another Service
                </a>
                <button onclick="window.print()" class="btn btn-secondary" id="printBtn">
                    🖨️ Print / Save
                </button>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
