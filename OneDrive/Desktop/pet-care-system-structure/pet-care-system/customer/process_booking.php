<?php
// ============================================================
// Pet Care System - Process Booking
// Member 3: Booking & Payment
// Accepts POST only. Validates all fields, inserts appointment,
// then redirects to payment.php
// ============================================================
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('booking.php');
}

// --- Collect & trim inputs ---
$rawServiceId    = $_POST['service_id']      ?? '';
$customerName    = trim($_POST['customer_name']    ?? '');
$customerEmail   = trim($_POST['customer_email']   ?? '');
$customerPhone   = trim($_POST['customer_phone']   ?? '');
$petName         = trim($_POST['pet_name']         ?? '');
$breed           = trim($_POST['breed']            ?? '');
$age             = trim($_POST['age']              ?? '');
$appointmentDate = trim($_POST['appointment_date'] ?? '');

// --- Validate service_id ---
if (!is_numeric($rawServiceId) || (int)$rawServiceId <= 0) {
    redirect('booking.php');
}
$serviceId = (int)$rawServiceId;

// --- Re-fetch service (never trust submitted price) ---
$stmt = $conn->prepare('SELECT id, service_name, price FROM services WHERE id = ? LIMIT 1');
if (!$stmt) { redirect('booking.php'); }
$stmt->bind_param('i', $serviceId);
$stmt->execute();
$service = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$service) {
    $_SESSION['booking_error'] = 'Selected service no longer exists.';
    redirect('../index.php');
}

// --- Server-side validation ---
$errors = [];

if (empty($customerName) || mb_strlen($customerName) > 100) {
    $errors['customer_name'] = 'Full name is required (max 100 characters).';
}
if (empty($customerEmail) || !filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
    $errors['customer_email'] = 'A valid email address is required.';
}
if (empty($customerPhone) || !preg_match('/^[0-9+\-\s()]{7,20}$/', $customerPhone)) {
    $errors['customer_phone'] = 'A valid phone number is required (7-20 digits).';
}
if (empty($petName) || mb_strlen($petName) > 100) {
    $errors['pet_name'] = 'Pet name is required.';
}
if (empty($breed) || mb_strlen($breed) > 100) {
    $errors['breed'] = 'Breed is required.';
}
if (!is_numeric($age) || (float)$age < 0 || (float)$age > 30) {
    $errors['age'] = 'Age must be a number between 0 and 30.';
}
if (empty($appointmentDate)) {
    $errors['appointment_date'] = 'Appointment date is required.';
} else {
    $dateObj = DateTime::createFromFormat('Y-m-d', $appointmentDate);
    $today   = new DateTime('today');
    if (!$dateObj || $dateObj->format('Y-m-d') !== $appointmentDate) {
        $errors['appointment_date'] = 'Invalid date format.';
    } elseif ($dateObj < $today) {
        $errors['appointment_date'] = 'Appointment date cannot be in the past.';
    }
}

// --- Return errors to booking form ---
if (!empty($errors)) {
    $_SESSION['booking_errors'] = $errors;
    $_SESSION['booking_old'] = [
        'customer_name'    => $customerName,
        'customer_email'   => $customerEmail,
        'customer_phone'   => $customerPhone,
        'pet_name'         => $petName,
        'breed'            => $breed,
        'age'              => $age,
        'appointment_date' => $appointmentDate,
    ];
    $conn->close();
    redirect('booking.php?service_id=' . $serviceId);
}

// --- Insert appointment with prepared statement ---
$bookingStatus = 'Pending';
$insert = $conn->prepare(
    'INSERT INTO appointments
     (service_id, customer_name, customer_email, customer_phone, pet_name, breed, age, appointment_date, booking_status)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
);

if (!$insert) {
    error_log('Appointment prepare failed: ' . $conn->error);
    $conn->close();
    $_SESSION['booking_error'] = 'Unable to process your booking at the moment. Please try again.';
    redirect('booking.php?service_id=' . $serviceId);
}

$insert->bind_param(
    'issssssss',
    $serviceId, $customerName, $customerEmail, $customerPhone,
    $petName, $breed, $age, $appointmentDate, $bookingStatus
);

if (!$insert->execute()) {
    error_log('Appointment insert failed: ' . $insert->error);
    $insert->close();
    $conn->close();
    $_SESSION['booking_error'] = 'Unable to process your booking at the moment. Please try again.';
    redirect('booking.php?service_id=' . $serviceId);
}

$appointmentId = $conn->insert_id;
$insert->close();
$conn->close();

redirect('payment.php?appointment_id=' . $appointmentId);
