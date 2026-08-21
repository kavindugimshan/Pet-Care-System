<?php
// ============================================================
// Pet Care System - Admin: Edit Service
// University Web Application Development Project
// Member 4: Admin Service & Appointment Management
// ============================================================

// Session guard
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';

$errors = [];
$service = null;

// Allowed values
$allowedCategories = ['Grooming', 'Veterinary', 'Boarding', 'Training', 'Other'];
$allowedPetTypes   = ['Dog', 'Cat', 'Bird', 'Rabbit', 'Other'];

// ── Validate the ID from GET ───────────────────────────────
$id = 0;
if (isset($_GET['id']) && ctype_digit((string) $_GET['id']) && (int) $_GET['id'] > 0) {
    $id = (int) $_GET['id'];
} elseif (isset($_POST['service_id']) && ctype_digit((string) $_POST['service_id']) && (int) $_POST['service_id'] > 0) {
    $id = (int) $_POST['service_id'];
}

if ($id <= 0) {
    $_SESSION['error'] = 'Invalid service ID.';
    redirect('services.php');
}

// ── Handle POST (update) ───────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $service_name    = trim($_POST['service_name']    ?? '');
    $category        = trim($_POST['category']        ?? '');
    $target_pet_type = trim($_POST['target_pet_type'] ?? '');
    $description     = trim($_POST['description']     ?? '');
    $price_raw       = trim($_POST['price']           ?? '');
    $image           = trim($_POST['image']           ?? '');

    // Backend validation
    if ($service_name === '') {
        $errors['service_name'] = 'Service name is required.';
    } elseif (mb_strlen($service_name) > 100) {
        $errors['service_name'] = 'Service name must not exceed 100 characters.';
    }

    if ($category === '') {
        $errors['category'] = 'Category is required.';
    }

    if ($target_pet_type === '') {
        $errors['target_pet_type'] = 'Target pet type is required.';
    }

    if ($description === '') {
        $errors['description'] = 'Description is required.';
    }

    if ($price_raw === '') {
        $errors['price'] = 'Price is required.';
    } elseif (!is_numeric($price_raw)) {
        $errors['price'] = 'Price must be a valid number.';
    } elseif ((float) $price_raw < 0) {
        $errors['price'] = 'Price cannot be negative.';
    }

    if ($image !== '' && mb_strlen($image) > 255) {
        $errors['image'] = 'Image filename is too long.';
    }

    if (empty($errors)) {
        $price     = (float) $price_raw;
        $image_val = $image !== '' ? $image : null;

        $stmt = $conn->prepare(
            "UPDATE services
             SET service_name = ?, category = ?, target_pet_type = ?, description = ?, price = ?, image = ?
             WHERE id = ?"
        );

        if ($stmt === false) {
            error_log('Prepare failed (service update): ' . $conn->error);
            $errors['general'] = 'Unable to update the service. Please try again.';
        } else {
            $stmt->bind_param('ssssdsi', $service_name, $category, $target_pet_type, $description, $price, $image_val, $id);

            if ($stmt->execute()) {
                $stmt->close();
                $conn->close();
                $_SESSION['success'] = 'Service updated successfully.';
                redirect('services.php');
            } else {
                error_log('Execute failed (service update): ' . $stmt->error);
                $errors['general'] = 'Unable to update the service. Please try again.';
                $stmt->close();
            }
        }
    }

    // Re-populate from POST on validation fail
    $service = [
        'id'              => $id,
        'service_name'    => $_POST['service_name']    ?? '',
        'category'        => $_POST['category']        ?? '',
        'target_pet_type' => $_POST['target_pet_type'] ?? '',
        'description'     => $_POST['description']     ?? '',
        'price'           => $_POST['price']           ?? '',
        'image'           => $_POST['image']           ?? '',
    ];
}

// ── Fetch existing service (GET or after failed POST) ──────
if ($service === null) {
    $stmt = $conn->prepare("SELECT * FROM services WHERE id = ? LIMIT 1");
    if ($stmt === false) {
        error_log('Prepare failed (service fetch): ' . $conn->error);
        $_SESSION['error'] = 'Unable to load the service.';
        $conn->close();
        redirect('services.php');
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $service = $result->fetch_assoc();
    $stmt->close();

    if (!$service) {
        $_SESSION['error'] = 'Service not found.';
        $conn->close();
        redirect('services.php');
    }
}

$conn->close();
$pageTitle = 'Edit Service';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Edit Service - Pet Care System Admin">
    <title><?php echo htmlspecialchars($pageTitle . ' | Pet Care System Admin'); ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="admin-layout">

    <!-- Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar-title">🐾 PCS Admin</div>
        <nav>
            <ul>
                <li><a href="dashboard.php">📊 Dashboard</a></li>
                <li><a href="services.php" class="active">🛎️ Manage Services</a></li>
                <li><a href="appointments.php">📅 Appointments</a></li>
            </ul>
        </nav>
        <div class="admin-sidebar-footer">
            <a href="logout.php" class="btn btn-danger btn-sm btn-full">🔒 Logout</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">

        <!-- Mobile Nav -->
        <div class="mobile-nav-bar">
            <button class="hamburger" id="hamburgerBtn" aria-label="Open menu">&#9776;</button>
            <span>✏️ Edit Service</span>
            <a href="logout.php" style="color:#fff; text-decoration:none; font-size:.85rem;">🔒</a>
        </div>

        <!-- Top Bar -->
        <div class="admin-top-bar">
            <div>
                <h1 class="admin-welcome">Edit <span>Service</span></h1>
                <p class="text-muted text-sm">Modify an existing pet care service.</p>
            </div>
            <a href="logout.php" class="btn btn-secondary btn-sm">🔒 Logout</a>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1>Edit Service #<?php echo $id; ?></h1>
                <p class="text-muted text-sm">
                    <a href="services.php" style="color:var(--admin-accent);">← Back to Services</a>
                </p>
            </div>
        </div>

        <!-- General Error -->
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($errors['general']); ?></div>
        <?php endif; ?>

        <!-- Edit Form -->
        <div class="form-section">
            <form method="POST" action="service-edit.php?id=<?php echo $id; ?>" id="adminServiceForm" novalidate>
                <!-- Hidden service ID for POST handling -->
                <input type="hidden" name="service_id" value="<?php echo $id; ?>">

                <div class="form-row">
                    <!-- Service Name -->
                    <div class="form-group">
                        <label for="service_name">Service Name <span class="required">*</span></label>
                        <input type="text"
                               class="form-control <?php echo isset($errors['service_name']) ? 'is-invalid' : ''; ?>"
                               id="service_name"
                               name="service_name"
                               value="<?php echo htmlspecialchars($service['service_name']); ?>"
                               maxlength="100"
                               required>
                        <div class="form-error <?php echo isset($errors['service_name']) ? 'visible' : ''; ?>"
                             id="service_name_error">
                            <?php echo htmlspecialchars($errors['service_name'] ?? ''); ?>
                        </div>
                    </div>

                    <!-- Category -->
                    <div class="form-group">
                        <label for="category">Category <span class="required">*</span></label>
                        <select class="form-control <?php echo isset($errors['category']) ? 'is-invalid' : ''; ?>"
                                id="category"
                                name="category"
                                required>
                            <option value="">— Select Category —</option>
                            <?php foreach ($allowedCategories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>"
                                    <?php echo ($service['category'] === $cat) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat); ?>
                                </option>
                            <?php endforeach; ?>
                            <?php
                            // If current value is not in allowed list, show it too
                            if (!in_array($service['category'], $allowedCategories, true) && $service['category'] !== ''):
                            ?>
                                <option value="<?php echo htmlspecialchars($service['category']); ?>" selected>
                                    <?php echo htmlspecialchars($service['category']); ?>
                                </option>
                            <?php endif; ?>
                        </select>
                        <div class="form-error <?php echo isset($errors['category']) ? 'visible' : ''; ?>"
                             id="category_error">
                            <?php echo htmlspecialchars($errors['category'] ?? ''); ?>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <!-- Target Pet Type -->
                    <div class="form-group">
                        <label for="target_pet_type">Target Pet Type <span class="required">*</span></label>
                        <select class="form-control <?php echo isset($errors['target_pet_type']) ? 'is-invalid' : ''; ?>"
                                id="target_pet_type"
                                name="target_pet_type"
                                required>
                            <option value="">— Select Pet Type —</option>
                            <?php foreach ($allowedPetTypes as $type): ?>
                                <option value="<?php echo htmlspecialchars($type); ?>"
                                    <?php echo ($service['target_pet_type'] === $type) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type); ?>
                                </option>
                            <?php endforeach; ?>
                            <?php
                            if (!in_array($service['target_pet_type'], $allowedPetTypes, true) && $service['target_pet_type'] !== ''):
                            ?>
                                <option value="<?php echo htmlspecialchars($service['target_pet_type']); ?>" selected>
                                    <?php echo htmlspecialchars($service['target_pet_type']); ?>
                                </option>
                            <?php endif; ?>
                        </select>
                        <div class="form-error <?php echo isset($errors['target_pet_type']) ? 'visible' : ''; ?>"
                             id="target_pet_type_error">
                            <?php echo htmlspecialchars($errors['target_pet_type'] ?? ''); ?>
                        </div>
                    </div>

                    <!-- Price -->
                    <div class="form-group">
                        <label for="price">Price (Rs.) <span class="required">*</span></label>
                        <input type="number"
                               class="form-control <?php echo isset($errors['price']) ? 'is-invalid' : ''; ?>"
                               id="price"
                               name="price"
                               value="<?php echo htmlspecialchars((string) $service['price']); ?>"
                               step="0.01"
                               min="0"
                               required>
                        <div class="form-error <?php echo isset($errors['price']) ? 'visible' : ''; ?>"
                             id="price_error">
                            <?php echo htmlspecialchars($errors['price'] ?? ''); ?>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label for="description">Description <span class="required">*</span></label>
                    <textarea class="form-control <?php echo isset($errors['description']) ? 'is-invalid' : ''; ?>"
                              id="description"
                              name="description"
                              rows="4"
                              required><?php echo htmlspecialchars($service['description']); ?></textarea>
                    <div class="form-error <?php echo isset($errors['description']) ? 'visible' : ''; ?>"
                         id="description_error">
                        <?php echo htmlspecialchars($errors['description'] ?? ''); ?>
                    </div>
                </div>

                <!-- Image -->
                <div class="form-group">
                    <label for="image">Image Filename <span class="text-muted text-sm">(optional)</span></label>
                    <input type="text"
                           class="form-control <?php echo isset($errors['image']) ? 'is-invalid' : ''; ?>"
                           id="image"
                           name="image"
                           value="<?php echo htmlspecialchars($service['image'] ?? ''); ?>"
                           maxlength="255"
                           placeholder="e.g. dog-grooming.jpg">
                    <p class="form-hint">Leave blank to remove the image. Images should be in <code>assets/images/</code>.</p>
                    <div class="form-error <?php echo isset($errors['image']) ? 'visible' : ''; ?>"
                         id="image_error">
                        <?php echo htmlspecialchars($errors['image'] ?? ''); ?>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-success" id="submitEditService">
                        💾 Update Service
                    </button>
                    <a href="services.php" class="btn btn-secondary" id="cancelEditService">Cancel</a>
                </div>

            </form>
        </div>

    </main>
</div>

<script src="../assets/js/admin.js"></script>
</body>
</html>
