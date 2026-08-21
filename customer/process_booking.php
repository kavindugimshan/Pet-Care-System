<?php
// ============================================================
// Pet Care System - Booking Processor
// University Web Application Development Project
// Member 3: Booking & Payment
// ============================================================

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../index.php');
}

// ── Collect and validate inputs ──────────────────────────────
$serviceId      = filter_var($_POST['service_id']      ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$customerName   = trim($_POST['customer_name']   ?? '');
$customerEmail  = trim($_POST['customer_email']  ?? '');
$customerPhone  = trim($_POST['customer_phone']  ?? '');
$petName        = trim($_POST['pet_name']        ?? '');
$breed          = trim($_POST['breed']           ?? '');
$ageRaw         = trim($_POST['age']             ?? '');
$appointmentDate = trim($_POST['appointment_date'] ?? '');

$errors = [];
$old    = [
    'customer_name'    => $customerName,
    'customer_email'   => $customerEmail,
    'customer_phone'   => $customerPhone,
    'pet_name'         => $petName,
    'breed'            => $breed,
    'age'              => $ageRaw,
    'appointment_date' => $appointmentDate,
];

// Validate service ID
if ($serviceId === false) {
    redirect('../index.php');
}

// Verify service exists
$stmt = $conn->prepare('SELECT id FROM services WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $serviceId);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    $stmt->close();
    $conn->close();
    redirect('../index.php');
}
$stmt->close();

// Validate owner info
if ($customerName === '')  { $errors[] = 'Full name is required.'; }
if ($customerEmail === '') { $errors[] = 'Email address is required.'; }
elseif (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Please enter a valid email address.'; }
if ($customerPhone === '') { $errors[] = 'Phone number is required.'; }
elseif (!preg_match('/^[0-9+\-\s]{7,20}$/', $customerPhone)) { $errors[] = 'Please enter a valid phone number.'; }

// Validate pet info
if ($petName === '') { $errors[] = 'Pet name is required.'; }
if ($breed   === '') { $errors[] = 'Breed is required.'; }

$age = filter_var($ageRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 30]]);
if ($age === false) { $errors[] = 'Age must be a number between 0 and 30.'; }

// Validate appointment date — must be today or future
if ($appointmentDate === '') {
    $errors[] = 'Appointment date is required.';
} else {
    $todayTs  = mktime(0,0,0, date('n'), date('j'), date('Y'));
    $apptTs   = strtotime($appointmentDate);
    if ($apptTs === false || $apptTs < $todayTs) {
        $errors[] = 'Appointment date must be today or a future date.';
    }
}

// Return to form on errors
if ($errors) {
    $_SESSION['booking_errors'] = $errors;
    $_SESSION['booking_old']    = $old;
    $conn->close();
    redirect('../customer/booking.php?service_id=' . $serviceId);
}

// ── Insert appointment ────────────────────────────────────────
$stmt = $conn->prepare(
    'INSERT INTO appointments
        (service_id, customer_name, customer_email, customer_phone, pet_name, breed, age, appointment_date, booking_status)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
);

$status = 'Pending';
$ageStr = (string)$age;
$stmt->bind_param('issssssss', $serviceId, $customerName, $customerEmail, $customerPhone, $petName, $breed, $ageStr, $appointmentDate, $status);

if (!$stmt->execute()) {
    error_log('Appointment insert failed: ' . $stmt->error);
    $stmt->close();
    $conn->close();
    $_SESSION['booking_errors'] = ['A database error occurred. Please try again.'];
    $_SESSION['booking_old']    = $old;
    redirect('../customer/booking.php?service_id=' . $serviceId);
}

$appointmentId = $conn->insert_id;
$stmt->close();
$conn->close();

// Redirect to payment
redirect('../customer/payment.php?appointment_id=' . $appointmentId);
