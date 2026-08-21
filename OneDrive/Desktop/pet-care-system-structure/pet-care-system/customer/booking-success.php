<?php
// ============================================================
// Pet Care System - Booking Success / Confirmation Page
// Member 3: Booking & Payment
// ============================================================
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

// --- Validate appointment_id ---
$rawId = $_GET['appointment_id'] ?? '';
if (!is_numeric($rawId) || (int)$rawId <= 0) {
    redirect('../index.php');
}
$appointmentId = (int)$rawId;

// --- Fetch full booking + payment details from DB ---
$stmt = $conn->prepare(
    'SELECT a.id AS appointment_id, a.customer_name, a.customer_email, a.customer_phone,
            a.pet_name, a.breed, a.age, a.appointment_date, a.booking_status,
            s.service_name, s.category,
            p.amount, p.payment_method, p.transaction_reference, p.payment_status, p.paid_at
     FROM appointments a
     JOIN services s   ON a.service_id    = s.id
     LEFT JOIN payments p ON p.appointment_id = a.id AND p.payment_status = \'Paid\'
     WHERE a.id = ? LIMIT 1'
);
if (!$stmt) { redirect('../index.php'); }
$stmt->bind_param('i', $appointmentId);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$booking) {
    redirect('../index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Booking confirmation for your pet care appointment">
    <title>Booking Confirmed | Pet Care System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/booking.css">
</head>
<body>

<div class="booking-page">

    <header class="booking-header">
        <a href="../index.php" class="booking-back-link">&#8592; Back to Services</a>
        <div class="booking-logo">&#128062; Pet Care System</div>
    </header>

    <main class="booking-main">

        <!-- Progress Steps -->
        <div class="booking-steps" aria-label="Booking progress">
            <div class="step done">
                <div class="step-circle">&#10003;</div>
                <div class="step-label">Your Details</div>
            </div>
            <div class="step-line step-line--done"></div>
            <div class="step done">
                <div class="step-circle">&#10003;</div>
                <div class="step-label">Payment</div>
            </div>
            <div class="step-line step-line--done"></div>
            <div class="step active">
                <div class="step-circle">3</div>
                <div class="step-label">Confirmation</div>
            </div>
        </div>

        <!-- Confirmation Card -->
        <div class="success-container">

            <!-- Success Banner -->
            <div class="success-banner" role="status">
                <div class="success-icon" aria-hidden="true">&#10003;</div>
                <h1 class="success-title">Booking Confirmed!</h1>
                <p class="success-subtitle">
                    Your appointment has been successfully booked and payment received.
                    A confirmation has been sent to <strong><?php echo sanitizeInput($booking['customer_email']); ?></strong>.
                </p>
            </div>

            <!-- Booking Details Grid -->
            <div class="confirmation-grid">

                <!-- Booking Info -->
                <div class="confirm-section">
                    <h2 class="confirm-section-title">Booking Details</h2>
                    <div class="confirm-rows">
                        <div class="confirm-row">
                            <span class="confirm-label">Booking ID</span>
                            <span class="confirm-value booking-id">#<?php echo $booking['appointment_id']; ?></span>
                        </div>
                        <div class="confirm-row">
                            <span class="confirm-label">Service</span>
                            <span class="confirm-value"><?php echo sanitizeInput($booking['service_name']); ?></span>
                        </div>
                        <div class="confirm-row">
                            <span class="confirm-label">Category</span>
                            <span class="confirm-value"><?php echo sanitizeInput($booking['category']); ?></span>
                        </div>
                        <div class="confirm-row">
                            <span class="confirm-label">Appointment Date</span>
                            <span class="confirm-value">
                                <?php echo date('l, d F Y', strtotime($booking['appointment_date'])); ?>
                            </span>
                        </div>
                        <div class="confirm-row">
                            <span class="confirm-label">Booking Status</span>
                            <span class="confirm-value">
                                <span class="status-badge status-badge--confirmed">
                                    <?php echo sanitizeInput($booking['booking_status']); ?>
                                </span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Owner & Pet Info -->
                <div class="confirm-section">
                    <h2 class="confirm-section-title">Owner &amp; Pet</h2>
                    <div class="confirm-rows">
                        <div class="confirm-row">
                            <span class="confirm-label">Owner Name</span>
                            <span class="confirm-value"><?php echo sanitizeInput($booking['customer_name']); ?></span>
                        </div>
                        <div class="confirm-row">
                            <span class="confirm-label">Phone</span>
                            <span class="confirm-value"><?php echo sanitizeInput($booking['customer_phone']); ?></span>
                        </div>
                        <div class="confirm-row">
                            <span class="confirm-label">Pet Name</span>
                            <span class="confirm-value"><?php echo sanitizeInput($booking['pet_name']); ?></span>
                        </div>
                        <div class="confirm-row">
                            <span class="confirm-label">Breed</span>
                            <span class="confirm-value"><?php echo sanitizeInput($booking['breed']); ?></span>
                        </div>
                        <div class="confirm-row">
                            <span class="confirm-label">Age</span>
                            <span class="confirm-value"><?php echo sanitizeInput($booking['age']); ?> year(s)</span>
                        </div>
                    </div>
                </div>

                <!-- Payment Info -->
                <div class="confirm-section confirm-section--full">
                    <h2 class="confirm-section-title">Payment Summary</h2>
                    <div class="confirm-rows">
                        <div class="confirm-row">
                            <span class="confirm-label">Amount Paid</span>
                            <span class="confirm-value confirm-amount">
                                <?php echo $booking['amount'] ? formatPrice($booking['amount']) : formatPrice(0); ?>
                            </span>
                        </div>
                        <div class="confirm-row">
                            <span class="confirm-label">Payment Method</span>
                            <span class="confirm-value"><?php echo sanitizeInput($booking['payment_method'] ?? 'Demo Card'); ?></span>
                        </div>
                        <div class="confirm-row">
                            <span class="confirm-label">Payment Status</span>
                            <span class="confirm-value">
                                <span class="status-badge status-badge--paid">
                                    <?php echo sanitizeInput($booking['payment_status'] ?? 'Paid'); ?>
                                </span>
                            </span>
                        </div>
                        <div class="confirm-row">
                            <span class="confirm-label">Transaction Reference</span>
                            <span class="confirm-value tx-ref">
                                <?php echo sanitizeInput($booking['transaction_reference'] ?? 'N/A'); ?>
                            </span>
                        </div>
                        <?php if ($booking['paid_at']): ?>
                        <div class="confirm-row">
                            <span class="confirm-label">Paid At</span>
                            <span class="confirm-value">
                                <?php echo date('d M Y, H:i', strtotime($booking['paid_at'])); ?>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <!-- Actions -->
            <div class="success-actions">
                <a href="../index.php" class="btn-book">
                    &#128062; Back to Services
                </a>
                <button onclick="window.print()" class="btn-secondary-outline" id="printBtn">
                    &#128438; Print Receipt
                </button>
            </div>

        </div>
    </main>
</div>

</body>
</html>
