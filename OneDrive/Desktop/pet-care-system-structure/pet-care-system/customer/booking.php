<?php
// ============================================================
// Pet Care System - Customer Booking Page
// Member 3: Booking & Payment
// ============================================================
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

// --- Validate service_id from URL ---
$rawId = $_GET['service_id'] ?? '';
if (!is_numeric($rawId) || (int)$rawId <= 0) {
    $_SESSION['booking_error'] = 'Invalid service selected. Please choose a service from the catalog.';
    redirect('../index.php');
}
$serviceId = (int)$rawId;

// --- Fetch service via prepared statement ---
$stmt = $conn->prepare('SELECT id, service_name, category, target_pet_type, description, price FROM services WHERE id = ? LIMIT 1');
if (!$stmt) { redirect('../index.php'); }
$stmt->bind_param('i', $serviceId);
$stmt->execute();
$result  = $stmt->get_result();
$service = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$service) {
    $_SESSION['booking_error'] = 'Service not found. Please choose a valid service.';
    redirect('../index.php');
}

// Repopulate form on validation error
$old    = $_SESSION['booking_old']    ?? [];
$errors = $_SESSION['booking_errors'] ?? [];
unset($_SESSION['booking_old'], $_SESSION['booking_errors']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Book a pet care appointment for <?php echo sanitizeInput($service['service_name']); ?>">
    <title>Book Appointment | Pet Care System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/booking.css">
</head>
<body>

<div class="booking-page">

    <!-- Header Bar -->
    <header class="booking-header">
        <a href="../index.php" class="booking-back-link">&#8592; Back to Services</a>
        <div class="booking-logo">&#128062; Pet Care System</div>
    </header>

    <main class="booking-main">

        <!-- Progress Steps -->
        <div class="booking-steps" aria-label="Booking progress">
            <div class="step active">
                <div class="step-circle">1</div>
                <div class="step-label">Your Details</div>
            </div>
            <div class="step-line"></div>
            <div class="step">
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

            <!-- Left: Booking Form -->
            <div class="booking-form-wrap">
                <h1 class="booking-title">Book Appointment</h1>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error" id="formErrorAlert" role="alert">
                        <strong>Please fix the following errors:</strong>
                        <ul>
                            <?php foreach ($errors as $e): ?>
                                <li><?php echo sanitizeInput($e); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form id="bookingForm" method="POST" action="process_booking.php" novalidate>
                    <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">

                    <!-- Owner Details -->
                    <fieldset class="form-fieldset">
                        <legend class="fieldset-legend">Owner Details</legend>

                        <div class="form-group <?php echo !empty($errors['customer_name']) ? 'has-error' : ''; ?>">
                            <label for="customer_name" class="form-label">Full Name <span class="required">*</span></label>
                            <input type="text" id="customer_name" name="customer_name" class="form-input"
                                   maxlength="100" autocomplete="name"
                                   value="<?php echo sanitizeInput($old['customer_name'] ?? ''); ?>"
                                   placeholder="e.g. Nimal Perera">
                            <span class="form-error" id="err_customer_name"></span>
                        </div>

                        <div class="form-group <?php echo !empty($errors['customer_email']) ? 'has-error' : ''; ?>">
                            <label for="customer_email" class="form-label">Email Address <span class="required">*</span></label>
                            <input type="email" id="customer_email" name="customer_email" class="form-input"
                                   maxlength="150" autocomplete="email"
                                   value="<?php echo sanitizeInput($old['customer_email'] ?? ''); ?>"
                                   placeholder="e.g. nimal@email.com">
                            <span class="form-error" id="err_customer_email"></span>
                        </div>

                        <div class="form-group <?php echo !empty($errors['customer_phone']) ? 'has-error' : ''; ?>">
                            <label for="customer_phone" class="form-label">Phone Number <span class="required">*</span></label>
                            <input type="tel" id="customer_phone" name="customer_phone" class="form-input"
                                   maxlength="20" autocomplete="tel"
                                   value="<?php echo sanitizeInput($old['customer_phone'] ?? ''); ?>"
                                   placeholder="e.g. 0771234567">
                            <span class="form-error" id="err_customer_phone"></span>
                        </div>
                    </fieldset>

                    <!-- Pet Details -->
                    <fieldset class="form-fieldset">
                        <legend class="fieldset-legend">Pet Details</legend>

                        <div class="form-row">
                            <div class="form-group <?php echo !empty($errors['pet_name']) ? 'has-error' : ''; ?>">
                                <label for="pet_name" class="form-label">Pet Name <span class="required">*</span></label>
                                <input type="text" id="pet_name" name="pet_name" class="form-input"
                                       maxlength="100"
                                       value="<?php echo sanitizeInput($old['pet_name'] ?? ''); ?>"
                                       placeholder="e.g. Rocky">
                                <span class="form-error" id="err_pet_name"></span>
                            </div>

                            <div class="form-group <?php echo !empty($errors['breed']) ? 'has-error' : ''; ?>">
                                <label for="breed" class="form-label">Breed <span class="required">*</span></label>
                                <input type="text" id="breed" name="breed" class="form-input"
                                       maxlength="100"
                                       value="<?php echo sanitizeInput($old['breed'] ?? ''); ?>"
                                       placeholder="e.g. Golden Retriever">
                                <span class="form-error" id="err_breed"></span>
                            </div>
                        </div>

                        <div class="form-group form-group--short <?php echo !empty($errors['age']) ? 'has-error' : ''; ?>">
                            <label for="age" class="form-label">Age (years) <span class="required">*</span></label>
                            <input type="number" id="age" name="age" class="form-input"
                                   min="0" max="30" step="0.1"
                                   value="<?php echo sanitizeInput($old['age'] ?? ''); ?>"
                                   placeholder="e.g. 3">
                            <span class="form-error" id="err_age"></span>
                        </div>
                    </fieldset>

                    <!-- Appointment Date -->
                    <fieldset class="form-fieldset">
                        <legend class="fieldset-legend">Appointment</legend>

                        <div class="form-group <?php echo !empty($errors['appointment_date']) ? 'has-error' : ''; ?>">
                            <label for="appointment_date" class="form-label">Preferred Date <span class="required">*</span></label>
                            <input type="date" id="appointment_date" name="appointment_date" class="form-input"
                                   value="<?php echo sanitizeInput($old['appointment_date'] ?? ''); ?>">
                            <span class="form-hint">Appointments available Monday &ndash; Saturday</span>
                            <span class="form-error" id="err_appointment_date"></span>
                        </div>
                    </fieldset>

                    <button type="submit" class="btn-book" id="submitBtn">
                        Continue to Payment &#8594;
                    </button>
                </form>
            </div>

            <!-- Right: Service Summary -->
            <aside class="booking-summary" aria-label="Selected service">
                <div class="summary-card">
                    <div class="summary-tag">Selected Service</div>
                    <h2 class="summary-service-name"><?php echo sanitizeInput($service['service_name']); ?></h2>

                    <div class="summary-details">
                        <div class="summary-row">
                            <span class="summary-label">Category</span>
                            <span class="summary-value"><?php echo sanitizeInput($service['category']); ?></span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">Suitable For</span>
                            <span class="summary-value pet-badge"><?php echo sanitizeInput($service['target_pet_type']); ?></span>
                        </div>
                        <div class="summary-divider"></div>
                        <div class="summary-row summary-price-row">
                            <span class="summary-label">Total Amount</span>
                            <span class="summary-price"><?php echo formatPrice($service['price']); ?></span>
                        </div>
                    </div>

                    <div class="summary-note">
                        &#128274; Price is fixed and confirmed at checkout
                    </div>
                </div>
            </aside>

        </div>
    </main>
</div>

<script src="../assets/js/booking.js"></script>
</body>
</html>
