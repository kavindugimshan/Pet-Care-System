<?php
// ============================================================
// Pet Care System - Payment Page (Simulated / Demo)
// University Web Application Development Project
// Member 3: Booking & Payment
//
// NOTE: This is a SIMULATED payment for coursework/demo purposes.
// No real card data is processed or stored.
// ============================================================

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Validate appointment ID
$apptId = filter_var($_GET['appointment_id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($apptId === false) {
    redirect('../index.php');
}

// Check if already paid — prevent double-payment screen
$checkStmt = $conn->prepare(
    "SELECT id FROM payments WHERE appointment_id = ? AND payment_status = 'Paid' LIMIT 1"
);
$checkStmt->bind_param('i', $apptId);
$checkStmt->execute();
$checkStmt->store_result();
if ($checkStmt->num_rows > 0) {
    $checkStmt->close();
    $conn->close();
    redirect('../customer/booking-success.php?appointment_id=' . $apptId);
}
$checkStmt->close();

// Fetch appointment + service (trusted from DB — never from browser)
$stmt = $conn->prepare(
    'SELECT a.id, a.customer_name, a.pet_name, a.breed, a.appointment_date, a.booking_status,
            s.service_name, s.price
     FROM   appointments a
     JOIN   services s ON s.id = a.service_id
     WHERE  a.id = ?
     LIMIT  1'
);
$stmt->bind_param('i', $apptId);
$stmt->execute();
$result = $stmt->get_result();
$appt   = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$appt) {
    redirect('../index.php');
}

$errors = $_SESSION['payment_errors'] ?? [];
unset($_SESSION['payment_errors']);

$pageTitle = 'Payment — ' . $appt['service_name'];
require_once __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="/assets/css/booking.css">

<div class="payment-wrapper">
    <div class="container">
        <div class="payment-layout">

            <!-- ── Payment Form ─────────────────────────────────── -->
            <div class="payment-form-section">
                <div class="payment-form-card">
                    <h1 class="payment-form-title">💳 Demo Payment</h1>
                    <div class="alert alert-info" style="margin-bottom:1.5rem;">
                        <strong>ℹ️ Simulated Payment</strong> — This is a coursework demo.
                        No real card data is stored or processed.
                    </div>

                    <?php if ($errors): ?>
                        <div class="alert alert-error">
                            <?php foreach ($errors as $e): ?>
                                <p><?php echo sanitize($e); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form id="paymentForm" action="/customer/process_payment.php" method="POST" novalidate>
                        <input type="hidden" name="appointment_id" value="<?php echo (int)$appt['id']; ?>">

                        <fieldset class="booking-fieldset">
                            <legend class="booking-legend">Payment Method</legend>

                            <div class="form-group">
                                <label class="form-label" for="payment_method">Select Method <span class="required">*</span></label>
                                <select id="payment_method" name="payment_method" class="form-control" required>
                                    <option value="">— Choose —</option>
                                    <option value="Credit Card">Credit Card (Demo)</option>
                                    <option value="Debit Card">Debit Card (Demo)</option>
                                    <option value="Cash on Arrival">Cash on Arrival</option>
                                    <option value="Bank Transfer">Bank Transfer (Demo)</option>
                                </select>
                            </div>

                            <!-- Demo card fields — cosmetic only, not saved -->
                            <div id="cardFields">
                                <div class="form-group">
                                    <label class="form-label" for="demo_card">Card Number (Demo)</label>
                                    <input type="text" id="demo_card" class="form-control"
                                           placeholder="•••• •••• •••• ••••"
                                           maxlength="19"
                                           autocomplete="off">
                                    <p class="form-text">🔒 Demo only — not stored or transmitted.</p>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label" for="demo_expiry">Expiry (Demo)</label>
                                        <input type="text" id="demo_expiry" class="form-control" placeholder="MM / YY" maxlength="7">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="demo_cvv">CVV (Demo)</label>
                                        <input type="text" id="demo_cvv" class="form-control" placeholder="•••" maxlength="3">
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <button type="submit" class="btn btn-primary btn-full btn-lg" id="payBtn">
                            🔒 Confirm Payment of <?php echo formatPrice((float)$appt['price']); ?>
                        </button>
                    </form>
                </div>
            </div>

            <!-- ── Booking Summary ──────────────────────────────── -->
            <aside class="payment-summary-section">
                <div class="booking-summary-card">
                    <h2 class="booking-summary-title">📋 Booking Summary</h2>
                    <div class="summary-service-name"><?php echo sanitize($appt['service_name']); ?></div>

                    <div class="summary-row">
                        <span class="summary-label">Customer</span>
                        <span class="summary-value"><?php echo sanitize($appt['customer_name']); ?></span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Pet</span>
                        <span class="summary-value"><?php echo sanitize($appt['pet_name']); ?></span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Breed</span>
                        <span class="summary-value"><?php echo sanitize($appt['breed']); ?></span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Appointment</span>
                        <span class="summary-value"><?php echo sanitize($appt['appointment_date']); ?></span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Booking ID</span>
                        <span class="summary-value">#<?php echo (int)$appt['id']; ?></span>
                    </div>

                    <div class="summary-price-row">
                        <span class="summary-price-label">Total Due</span>
                        <span class="summary-price-value"><?php echo formatPrice((float)$appt['price']); ?></span>
                    </div>
                </div>
            </aside>

        </div>
    </div>
</div>

<script>
    // Show/hide card fields based on payment method selection
    var methodSel  = document.getElementById('payment_method');
    var cardFields = document.getElementById('cardFields');
    function toggleCardFields() {
        var val = methodSel.value;
        cardFields.style.display = (val === 'Credit Card' || val === 'Debit Card') ? 'block' : 'none';
    }
    toggleCardFields();
    methodSel.addEventListener('change', toggleCardFields);

    // Format demo card number with spaces
    var demoCard = document.getElementById('demo_card');
    if (demoCard) {
        demoCard.addEventListener('input', function() {
            var v = this.value.replace(/\D/g, '').substring(0, 16);
            this.value = v.replace(/(.{4})/g, '$1 ').trim();
        });
    }

    // Prevent actual demo card data from being submitted
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        var method = document.getElementById('payment_method').value;
        if (!method) {
            e.preventDefault();
            alert('Please select a payment method.');
            return;
        }
        document.getElementById('payBtn').disabled = true;
        document.getElementById('payBtn').textContent = 'Processing…';
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
