<?php
// ============================================================
// Pet Care System - Customer Booking Page
// University Web Application Development Project
// Member 3: Booking & Payment
// ============================================================

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Validate service_id from GET
$rawServiceId = isset($_GET['service_id']) ? $_GET['service_id'] : '';
$serviceId    = filter_var($rawServiceId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($serviceId === false) {
    redirect('../index.php');
}

// Fetch service
$stmt = $conn->prepare('SELECT id, service_name, category, target_pet_type, price FROM services WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $serviceId);
$stmt->execute();
$result  = $stmt->get_result();
$service = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$service) {
    redirect('../index.php');
}

// Re-populate form on validation error
$errors = $_SESSION['booking_errors'] ?? [];
$old    = $_SESSION['booking_old']    ?? [];
unset($_SESSION['booking_errors'], $_SESSION['booking_old']);

$pageTitle = 'Book ' . $service['service_name'];
require_once __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="/assets/css/booking.css">
<script src="/assets/js/booking.js" defer></script>

<div class="booking-wrapper">
    <div class="container">
        <div class="booking-layout">

            <!-- ── Booking Form ────────────────────────────────── -->
            <div class="booking-form-section">
                <div class="booking-form-card">
                    <h1 class="booking-form-title">📅 Book a Service</h1>
                    <p class="booking-form-subtitle">Fill in your details and select an appointment date.</p>

                    <?php if ($errors): ?>
                        <div class="alert alert-error" id="bookingErrors">
                            <strong>Please fix the following:</strong>
                            <ul style="margin-top:.5rem;padding-left:1.2rem;">
                                <?php foreach ($errors as $e): ?>
                                    <li><?php echo sanitize($e); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form id="bookingForm" action="/customer/process_booking.php" method="POST" novalidate>
                        <input type="hidden" name="service_id" value="<?php echo (int)$service['id']; ?>">

                        <fieldset class="booking-fieldset">
                            <legend class="booking-legend">👤 Owner Information</legend>

                            <div class="form-group">
                                <label class="form-label" for="customer_name">Full Name <span class="required">*</span></label>
                                <input type="text" id="customer_name" name="customer_name" class="form-control"
                                       placeholder="e.g. Kasun Silva"
                                       value="<?php echo sanitize($old['customer_name'] ?? ''); ?>"
                                       required>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label" for="customer_email">Email <span class="required">*</span></label>
                                    <input type="email" id="customer_email" name="customer_email" class="form-control"
                                           placeholder="kasun@example.com"
                                           value="<?php echo sanitize($old['customer_email'] ?? ''); ?>"
                                           required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="customer_phone">Phone <span class="required">*</span></label>
                                    <input type="tel" id="customer_phone" name="customer_phone" class="form-control"
                                           placeholder="0712345678"
                                           value="<?php echo sanitize($old['customer_phone'] ?? ''); ?>"
                                           required>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="booking-fieldset">
                            <legend class="booking-legend">🐾 Pet Information</legend>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label" for="pet_name">Pet Name <span class="required">*</span></label>
                                    <input type="text" id="pet_name" name="pet_name" class="form-control"
                                           placeholder="e.g. Rocky"
                                           value="<?php echo sanitize($old['pet_name'] ?? ''); ?>"
                                           required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="breed">Breed <span class="required">*</span></label>
                                    <input type="text" id="breed" name="breed" class="form-control"
                                           placeholder="e.g. Golden Retriever"
                                           value="<?php echo sanitize($old['breed'] ?? ''); ?>"
                                           required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label" for="age">Age (years) <span class="required">*</span></label>
                                    <input type="number" id="age" name="age" class="form-control"
                                           placeholder="e.g. 3" min="0" max="30"
                                           value="<?php echo sanitize($old['age'] ?? ''); ?>"
                                           required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="appointment_date">Appointment Date <span class="required">*</span></label>
                                    <input type="date" id="appointment_date" name="appointment_date" class="form-control"
                                           min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                           value="<?php echo sanitize($old['appointment_date'] ?? ''); ?>"
                                           required>
                                </div>
                            </div>
                        </fieldset>

                        <button type="submit" class="btn btn-primary btn-full btn-lg" id="submitBookingBtn">
                            Proceed to Payment →
                        </button>
                    </form>
                </div>
            </div>

            <!-- ── Service Summary ──────────────────────────────── -->
            <aside class="booking-summary-section">
                <div class="booking-summary-card">
                    <h2 class="booking-summary-title">📋 Service Summary</h2>
                    <div class="summary-service-name"><?php echo sanitize($service['service_name']); ?></div>
                    <div class="summary-row">
                        <span class="summary-label">Category</span>
                        <span class="summary-value"><?php echo sanitize($service['category']); ?></span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">For</span>
                        <span class="summary-value"><?php echo sanitize($service['target_pet_type']); ?></span>
                    </div>
                    <div class="summary-price-row">
                        <span class="summary-price-label">Service Fee</span>
                        <span class="summary-price-value"><?php echo formatPrice((float)$service['price']); ?></span>
                    </div>
                    <a href="/index.php" class="btn btn-ghost btn-full mt-md" id="backToCatalog">
                        ← Change Service
                    </a>
                </div>
            </aside>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
