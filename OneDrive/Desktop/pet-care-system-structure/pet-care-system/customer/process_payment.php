<?php
// ============================================================
// Pet Care System - Process Payment
// Member 3: Booking & Payment
// Accepts POST only. Re-fetches price from DB, prevents
// duplicate payments, uses DB transaction, generates
// unique transaction reference.
// ============================================================
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('payment.php');
}

// --- Validate appointment_id ---
$rawId = $_POST['appointment_id'] ?? '';
if (!is_numeric($rawId) || (int)$rawId <= 0) {
    redirect('../index.php');
}
$appointmentId = (int)$rawId;

// --- Re-fetch appointment + service price from DB ---
$stmt = $conn->prepare(
    'SELECT a.id, a.booking_status, s.price, s.service_name
     FROM appointments a
     JOIN services s ON a.service_id = s.id
     WHERE a.id = ? LIMIT 1'
);
if (!$stmt) {
    $conn->close();
    $_SESSION['payment_error'] = 'Payment could not be completed. Please try again.';
    redirect('payment.php?appointment_id=' . $appointmentId);
}
$stmt->bind_param('i', $appointmentId);
$stmt->execute();
$appt = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$appt) {
    $conn->close();
    redirect('../index.php');
}

// --- Prevent duplicate successful payment ---
$dupCheck = $conn->prepare(
    "SELECT id FROM payments WHERE appointment_id = ? AND payment_status = 'Paid' LIMIT 1"
);
$dupCheck->bind_param('i', $appointmentId);
$dupCheck->execute();
$dupCheck->store_result();
if ($dupCheck->num_rows > 0) {
    $dupCheck->close();
    $conn->close();
    redirect('booking-success.php?appointment_id=' . $appointmentId);
}
$dupCheck->close();

// --- Card details: validate only, never store sensitive data ---
$cardName   = trim($_POST['card_name']   ?? '');
$cardNumber = preg_replace('/\D/', '', $_POST['card_number'] ?? '');
$cardExpiry = trim($_POST['card_expiry'] ?? '');
$cardCvv    = trim($_POST['card_cvv']   ?? '');

$errors = [];
if (empty($cardName)) {
    $errors[] = 'Name on card is required.';
}
if (strlen($cardNumber) < 13 || strlen($cardNumber) > 19) {
    $errors[] = 'Please enter a valid 13–19 digit card number.';
}
if (!preg_match('/^\d{2}\/\d{2}$/', $cardExpiry)) {
    $errors[] = 'Expiry must be in MM/YY format.';
}
if (strlen($cardCvv) < 3 || strlen($cardCvv) > 4) {
    $errors[] = 'CVV must be 3 or 4 digits.';
}

// --- Immediately discard all card data --- card details are NOT stored ---
unset($cardName, $cardNumber, $cardExpiry, $cardCvv);

if (!empty($errors)) {
    $conn->close();
    $_SESSION['payment_error'] = implode(' ', $errors);
    redirect('payment.php?appointment_id=' . $appointmentId);
}

// --- Generate unique transaction reference ---
$txRef = 'PAY-' . date('Ymd') . '-' . date('His') . '-' . strtoupper(bin2hex(random_bytes(2)));

// --- Use DB transaction: insert payment + update appointment ---
$conn->begin_transaction();

try {
    $amount        = (float)$appt['price'];    // price always from DB
    $paymentMethod = 'Demo Card';
    $paymentStatus = 'Paid';
    $paidAt        = date('Y-m-d H:i:s');

    // Insert payment record
    $insPayment = $conn->prepare(
        'INSERT INTO payments (appointment_id, amount, payment_method, transaction_reference, payment_status, paid_at)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    if (!$insPayment) throw new Exception('Payment prepare failed: ' . $conn->error);

    $insPayment->bind_param('idssss',
        $appointmentId, $amount, $paymentMethod, $txRef, $paymentStatus, $paidAt
    );
    if (!$insPayment->execute()) throw new Exception('Payment insert failed: ' . $insPayment->error);
    $insPayment->close();

    // Update appointment status to Confirmed
    $updAppt = $conn->prepare(
        "UPDATE appointments SET booking_status = 'Confirmed' WHERE id = ?"
    );
    if (!$updAppt) throw new Exception('Appointment update prepare failed: ' . $conn->error);
    $updAppt->bind_param('i', $appointmentId);
    if (!$updAppt->execute()) throw new Exception('Appointment update failed: ' . $updAppt->error);
    $updAppt->close();

    $conn->commit();
    $conn->close();

    redirect('booking-success.php?appointment_id=' . $appointmentId);

} catch (Exception $e) {
    $conn->rollback();
    $conn->close();
    error_log('Payment transaction failed: ' . $e->getMessage());
    $_SESSION['payment_error'] = 'Payment could not be completed. Please try again.';
    redirect('payment.php?appointment_id=' . $appointmentId);
}
