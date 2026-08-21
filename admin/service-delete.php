<?php
// ============================================================
// Pet Care System - Admin Delete Service
// University Web Application Development Project
// Member 4: Admin Management
// ============================================================

require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Only accept POST for destructive action
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('services.php');
}

$id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false) {
    redirect('services.php');
}

// Get service name for flash message
$stmt = $conn->prepare('SELECT service_name FROM services WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$result  = $stmt->get_result();
$service = $result->fetch_assoc();
$stmt->close();

if (!$service) {
    $conn->close();
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Service not found.'];
    redirect('services.php');
}

// Attempt delete — may fail if appointments reference this service (FK RESTRICT)
$stmt = $conn->prepare('DELETE FROM services WHERE id = ?');
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    $_SESSION['flash'] = [
        'type'    => 'success',
        'message' => 'Service "' . htmlspecialchars($service['service_name']) . '" deleted successfully.'
    ];
} else {
    // Foreign key violation — existing appointments reference this service
    $stmt->close();
    $conn->close();
    $_SESSION['flash'] = [
        'type'    => 'error',
        'message' => 'Cannot delete "' . htmlspecialchars($service['service_name']) . '" because it has existing appointments. Remove the appointments first.'
    ];
}

redirect('services.php');
