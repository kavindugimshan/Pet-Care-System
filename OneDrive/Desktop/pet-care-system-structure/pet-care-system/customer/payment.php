<?php
// ============================================================
// Pet Care System - Payment Page
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

// --- Fetch appointment + service via JOIN ---
$stmt = $conn->prepare(
    'SELECT a.id AS appointment_id, a.customer_name, a.customer_email, a.customer_phone,
            a.pet_name, a.breed, a.age, a.appointment_date, a.booking_status,
            s.service_name, s.category, s.price
     FROM appointments a
     JOIN services s ON a.service_id = s.id
     WHERE a.id = ? LIMIT 1'
);
if (!$stmt) { redirect('../index.php'); }
$stmt->bind_param('i', $appointmentId);
$stmt->execute();
$appt = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$appt) {
    $conn->close();
    redirect('../index.php');
}

// Check if already paid
$checkStmt = $conn->prepare(
    "SELECT id FROM payments WHERE appointment_id = ? AND payment_status = 'Paid' LIMIT 1"
);
$checkStmt->bind_param('i', $appointmentId);
$checkStmt->execute();
$checkStmt->store_result();
$alreadyPaid = $checkStmt->num_rows > 0;
$checkStmt->close();
$conn->close();

if ($alreadyPaid) {
    redirect('booking-success.php?appointment_id=' . $appointmentId);
}

$paymentError = $_SESSION['payment_error'] ?? '';
unset($_SESSION['payment_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Complete payment for your pet care appointment">
    <title>Payment | Pet Care System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/booking.css">
</head>
<body>

<div class="booking-page">

    <header class="booking-header">
        <a href="booking.php?service_id=<?php echo htmlspecialchars($_GET['from_service'] ?? '1'); ?>" class="booking-back-link">
            &#8592; Back
        </a>
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
            <div class="step active">
                <div class="step-circle">2</div>
                <div class="step-label">Payment</div>
            </div>
            <div class="step-line"></div>
            <div class="step">
                <div class="step-circle">3</div>
                <div class="step-label">Confirmation</div>
            </div>
        </div>

        <div class="booking-layout">

            <!-- Left: Payment Form -->
            <div class="booking-form-wrap">
                <h1 class="booking-title">Secure Checkout</h1>
                <p class="booking-subtitle">Demo payment simulation &mdash; no real transaction occurs</p>

                <?php if ($paymentError): ?>
                    <div class="alert alert-error" role="alert">
                        <?php echo sanitizeInput($paymentError); ?>
                    </div>
                <?php endif; ?>

                <form id="paymentForm" method="POST" action="process_payment.php" novalidate>
                    <input type="hidden" name="appointment_id" value="<?php echo $appointmentId; ?>">

                    <!-- Demo Card Details -->
                    <fieldset class="form-fieldset payment-card-fieldset">
                        <legend class="fieldset-legend">
                            &#128179; Demo Card Payment
                        </legend>
                        <div class="card-demo-notice">
                            <strong>Coursework Demo Only</strong> &mdash; Enter any values below.
                            Card details are NOT stored in the database.
                        </div>

                        <div class="form-group">
                            <label for="card_name" class="form-label">Name on Card <span class="required">*</span></label>
                            <input type="text" id="card_name" name="card_name" class="form-input"
                                   maxlength="100"
                                   value="<?php echo sanitizeInput($appt['customer_name']); ?>"
                                   placeholder="e.g. Nimal Perera" autocomplete="cc-name">
                            <span class="form-error" id="err_card_name"></span>
                        </div>

                        <div class="form-group">
                            <label for="card_number" class="form-label">Card Number <span class="required">*</span></label>
                            <input type="text" id="card_number" name="card_number" class="form-input card-number-input"
                                   maxlength="19" placeholder="1234 5678 9012 3456"
                                   autocomplete="off" inputmode="numeric">
                            <span class="form-error" id="err_card_number"></span>
                        </div>

                        <div class="form-row form-row--3col">
                            <div class="form-group">
                                <label for="card_expiry" class="form-label">Expiry <span class="required">*</span></label>
                                <input type="text" id="card_expiry" name="card_expiry" class="form-input"
                                       maxlength="5" placeholder="MM/YY"
                                       autocomplete="off">
                                <span class="form-error" id="err_card_expiry"></span>
                            </div>
                            <div class="form-group">
                                <label for="card_cvv" class="form-label">CVV <span class="required">*</span></label>
                                <input type="password" id="card_cvv" name="card_cvv" class="form-input"
                                       maxlength="4" placeholder="&#8226;&#8226;&#8226;"
                                       autocomplete="off">
                                <span class="form-error" id="err_card_cvv"></span>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Payment Method</label>
                                <div class="payment-method-badge">Demo Card</div>
                            </div>
                        </div>
                    </fieldset>

                    <button type="submit" class="btn-book btn-pay" id="payBtn">
                        &#128274; Pay <?php echo formatPrice($appt['price']); ?>
                    </button>

                    <p class="pay-disclaimer">
                        By clicking Pay, you confirm this is a demo booking for university coursework purposes.
                    </p>
                </form>
            </div>

            <!-- Right: Order Summary -->
            <aside class="booking-summary" aria-label="Order summary">
                <div class="summary-card">
                    <div class="summary-tag">Order Summary</div>

                    <div class="summary-details">
                        <div class="summary-row">
                            <span class="summary-label">Service</span>
                            <span class="summary-value"><?php echo sanitizeInput($appt['service_name']); ?></span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">Category</span>
                            <span class="summary-value"><?php echo sanitizeInput($appt['category']); ?></span>
                        </div>
                        <div class="summary-divider"></div>
                        <div class="summary-row">
                            <span class="summary-label">Pet</span>
                            <span class="summary-value"><?php echo sanitizeInput($appt['pet_name']); ?> (<?php echo sanitizeInput($appt['breed']); ?>)</span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">Owner</span>
                            <span class="summary-value"><?php echo sanitizeInput($appt['customer_name']); ?></span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">Appointment</span>
                            <span class="summary-value">
                                <?php echo date('d M Y', strtotime($appt['appointment_date'])); ?>
                            </span>
                        </div>
                        <div class="summary-divider"></div>
                        <div class="summary-row summary-price-row">
                            <span class="summary-label">Total</span>
                            <span class="summary-price"><?php echo formatPrice($appt['price']); ?></span>
                        </div>
                    </div>

                    <div class="summary-note">
                        &#128274; Secure simulated payment
                    </div>
                </div>

                <!-- Booking ID badge -->
                <div class="booking-id-badge">
                    Booking #<?php echo $appointmentId; ?>
                </div>
            </aside>

        </div>
    </main>
</div>

<script src="../assets/js/booking.js"></script>
</body>
</html>
