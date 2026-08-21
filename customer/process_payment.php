<?php
// ============================================================
// Pet Care System - Payment Processor
// University Web Application Development Project
// Member 3: Booking & Payment
//
// NOTE: Simulated payment for coursework/demo purposes.
// No real financial transaction occurs.
// ============================================================

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../index.php');
}

$apptId        = filter_var($_POST['appointment_id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$paymentMethod = trim($_POST['payment_method'] ?? '');

if ($apptId === false || $paymentMethod === '') {
    $_SESSION['payment_errors'] = ['Invalid payment data. Please try again.'];
    if ($apptId !== false) {
        redirect('../customer/payment.php?appointment_id=' . $apptId);
    }
    redirect('../index.php');
}

// Allowed payment methods
$allowedMethods = ['Credit Card', 'Debit Card', 'Cash on Arrival', 'Bank Transfer'];
if (!in_array($paymentMethod, $allowedMethods)) {
    $_SESSION['payment_errors'] = ['Invalid payment method selected.'];
    redirect('../customer/payment.php?appointment_id=' . $apptId);
}

// ── Fetch trusted appointment + price from DB ─────────────────
$stmt = $conn->prepare(
    'SELECT a.id, a.booking_status, s.price
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

if (!$appt) {
    $conn->close();
    redirect('../index.php');
}

// ── Check for existing successful payment ─────────────────────
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

// ── Generate server-side transaction reference ────────────────
$transactionRef = 'PAY-' . date('Ymd') . '-' . date('His') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));

// ── Atomic transaction: insert payment + update appointment ───
$conn->begin_transaction();

try {
    // Insert payment record
    $amount        = (float)$appt['price'];
    $paymentStatus = 'Paid';
    $paidAt        = date('Y-m-d H:i:s');

    $insertPayment = $conn->prepare(
        'INSERT INTO payments (appointment_id, amount, payment_method, transaction_reference, payment_status, paid_at)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $insertPayment->bind_param('idssss', $apptId, $amount, $paymentMethod, $transactionRef, $paymentStatus, $paidAt);

    if (!$insertPayment->execute()) {
        throw new RuntimeException('Payment insert failed: ' . $insertPayment->error);
    }
    $insertPayment->close();

    // Update appointment booking status to Confirmed
    $updateAppt = $conn->prepare(
        "UPDATE appointments SET booking_status = 'Confirmed' WHERE id = ?"
    );
    $updateAppt->bind_param('i', $apptId);
    if (!$updateAppt->execute()) {
        throw new RuntimeException('Appointment update failed: ' . $updateAppt->error);
    }
    $updateAppt->close();

    $conn->commit();

} catch (RuntimeException $e) {
    $conn->rollback();
    $conn->close();
    error_log('Payment processing error: ' . $e->getMessage());
    $_SESSION['payment_errors'] = ['Payment processing failed. Please try again.'];
    redirect('../customer/payment.php?appointment_id=' . $apptId);
}

$conn->close();

// Redirect to booking confirmation
redirect('../customer/booking-success.php?appointment_id=' . $apptId);
